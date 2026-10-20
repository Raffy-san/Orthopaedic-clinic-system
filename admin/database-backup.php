<?php
require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/../includes/auth.php';

SessionManager::requireLogin();
SessionManager::requireAnyRole(['admin']);

$csrfToken = $_SESSION['csrf_token'] ?? SessionManager::regenerateCsrfToken();
$message = null;
$messageType = 'success';

function backupQuoteIdentifier(string $identifier): string
{
    return '`' . str_replace('`', '``', $identifier) . '`';
}

function backupGetSchema(PDO $pdo): array
{
    $tables = [];
    $tableQuery = $pdo->query("SHOW FULL TABLES WHERE Table_type = 'BASE TABLE'");
    while ($row = $tableQuery->fetch(PDO::FETCH_NUM)) {
        $tableName = (string) $row[0];
        $columnQuery = $pdo->query('SHOW FULL COLUMNS FROM ' . backupQuoteIdentifier($tableName));
        $columns = [];
        while ($column = $columnQuery->fetch(PDO::FETCH_ASSOC)) {
            $columns[] = [
                'Field' => $column['Field'],
                'Type' => $column['Type'],
                'Collation' => $column['Collation'],
                'Null' => $column['Null'],
                'Key' => $column['Key'],
                'Default' => $column['Default'],
                'Extra' => $column['Extra'],
            ];
        }
        $tables[$tableName] = $columns;
    }

    ksort($tables);
    return $tables;
}

function backupWriteJson(mixed $value, $stream): void
{
    $encoded = json_encode($value, JSON_UNESCAPED_UNICODE | JSON_INVALID_UTF8_SUBSTITUTE);
    if ($encoded === false) {
        throw new RuntimeException('Unable to encode backup data.');
    }
    backupWrite($stream, $encoded);
}

function backupWrite($stream, string $contents): void
{
    $length = strlen($contents);
    $offset = 0;
    while ($offset < $length) {
        $written = fwrite($stream, substr($contents, $offset));
        if ($written === false || $written === 0) {
            throw new RuntimeException('Unable to write the database backup.');
        }
        $offset += $written;
    }
}

if ($_SERVER['REQUEST_METHOD'] === 'GET' && ($_GET['action'] ?? '') === 'download') {
    $backupStream = fopen('php://temp/maxmemory:2097152', 'w+b');
    if ($backupStream === false) {
        http_response_code(500);
        exit('Unable to prepare the database backup. Please try again.');
    }

    try {
        $pdo->exec('SET TRANSACTION ISOLATION LEVEL REPEATABLE READ');
        $pdo->beginTransaction();
        $database = (string) $pdo->query('SELECT DATABASE()')->fetchColumn();
        $schema = backupGetSchema($pdo);

        backupWrite($backupStream, '{"format":"orthopaedic-clinic-database-backup","version":1,"database":');
        backupWriteJson($database, $backupStream);
        backupWrite($backupStream, ',"created_at":');
        backupWriteJson(date(DATE_ATOM), $backupStream);
        backupWrite($backupStream, ',"schema":');
        backupWriteJson($schema, $backupStream);
        backupWrite($backupStream, ',"tables":{');

        $firstTable = true;
        foreach ($schema as $tableName => $columns) {
            if (!$firstTable) {
                backupWrite($backupStream, ',');
            }
            $firstTable = false;
            backupWriteJson($tableName, $backupStream);
            backupWrite($backupStream, ':{"rows":[');

            $columnNames = array_column($columns, 'Field');
            $select = $pdo->query('SELECT * FROM ' . backupQuoteIdentifier($tableName));
            $firstRow = true;
            while ($row = $select->fetch(PDO::FETCH_ASSOC)) {
                if (!$firstRow) {
                    backupWrite($backupStream, ',');
                }
                $firstRow = false;
                $orderedRow = [];
                foreach ($columnNames as $columnName) {
                    $orderedRow[] = $row[$columnName];
                }
                backupWriteJson($orderedRow, $backupStream);
            }
            backupWrite($backupStream, ']}');
        }
        backupWrite($backupStream, '}}');
        $pdo->commit();

        rewind($backupStream);
        header('Content-Type: application/json; charset=utf-8');
        header('Content-Disposition: attachment; filename="clinic-backup-' . date('Y-m-d-His') . '.json"');
        header('Cache-Control: no-store, private');
        header('X-Content-Type-Options: nosniff');
        fpassthru($backupStream);
        fclose($backupStream);
        exit;
    } catch (Throwable $e) {
        if ($pdo->inTransaction()) {
            $pdo->rollBack();
        }
        error_log('Database backup download failed: ' . $e->getMessage());
        fclose($backupStream);
        if (!headers_sent()) {
            http_response_code(500);
            header('Content-Type: text/plain; charset=utf-8');
        }
        exit('Unable to create the database backup. Please try again.');
    }
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $submittedToken = $_POST['csrf_token'] ?? '';
    if ($csrfToken === '' || $submittedToken === '' || !hash_equals($csrfToken, $submittedToken)) {
        http_response_code(403);
        $message = 'Your session token is invalid. Refresh the page and try again.';
        $messageType = 'error';
    } elseif (($_POST['restore_confirmation'] ?? '') !== 'RESTORE') {
        $message = 'Type RESTORE exactly to confirm replacing the current database data.';
        $messageType = 'error';
    } elseif (!isset($_FILES['backup_file']) || $_FILES['backup_file']['error'] !== UPLOAD_ERR_OK) {
        $uploadError = $_FILES['backup_file']['error'] ?? UPLOAD_ERR_NO_FILE;
        $message = match ($uploadError) {
            UPLOAD_ERR_INI_SIZE, UPLOAD_ERR_FORM_SIZE => 'The backup file exceeds the server upload limit.',
            UPLOAD_ERR_NO_FILE => 'Choose a backup file to restore.',
            default => 'The backup file could not be uploaded. Please try again.',
        };
        $messageType = 'error';
    } elseif (strtolower(pathinfo($_FILES['backup_file']['name'], PATHINFO_EXTENSION)) !== 'json') {
        $message = 'Choose a valid clinic JSON backup file.';
        $messageType = 'error';
    } elseif ($_FILES['backup_file']['size'] > 100 * 1024 * 1024) {
        $message = 'The backup file must be 100 MB or smaller.';
        $messageType = 'error';
    } else {
        $contents = file_get_contents($_FILES['backup_file']['tmp_name']);
        $backup = $contents === false ? null : json_decode($contents, true);
        $database = (string) $pdo->query('SELECT DATABASE()')->fetchColumn();
        $currentSchema = backupGetSchema($pdo);

        if (
            !is_array($backup)
            || ($backup['format'] ?? null) !== 'orthopaedic-clinic-database-backup'
            || ($backup['version'] ?? null) !== 1
            || ($backup['database'] ?? null) !== $database
            || ($backup['schema'] ?? null) !== $currentSchema
            || !isset($backup['tables'])
            || !is_array($backup['tables'])
            || array_keys($backup['tables']) !== array_keys($currentSchema)
        ) {
            $message = 'This backup is invalid or does not match the current database schema.';
            $messageType = 'error';
        } else {
            $isValidRows = true;
            foreach ($currentSchema as $tableName => $columns) {
                $rows = $backup['tables'][$tableName]['rows'] ?? null;
                if (!is_array($rows)) {
                    $isValidRows = false;
                    break;
                }
                foreach ($rows as $row) {
                    if (!is_array($row) || count($row) !== count($columns)) {
                        $isValidRows = false;
                        break 2;
                    }
                    foreach ($row as $value) {
                        if (!is_null($value) && !is_scalar($value)) {
                            $isValidRows = false;
                            break 3;
                        }
                    }
                }
            }

            if (!$isValidRows) {
                $message = 'The backup contains invalid table data and was not restored.';
                $messageType = 'error';
            } else {
                try {
                    $pdo->exec('SET FOREIGN_KEY_CHECKS = 0');
                    $pdo->beginTransaction();

                    foreach (array_keys($currentSchema) as $tableName) {
                        $pdo->exec('DELETE FROM ' . backupQuoteIdentifier($tableName));
                    }

                    foreach ($currentSchema as $tableName => $columns) {
                        $columnNames = array_column($columns, 'Field');
                        if ($columnNames === []) {
                            continue;
                        }
                        $quotedColumns = implode(', ', array_map('backupQuoteIdentifier', $columnNames));
                        $placeholders = implode(', ', array_fill(0, count($columnNames), '?'));
                        $insert = $pdo->prepare(
                            'INSERT INTO ' . backupQuoteIdentifier($tableName)
                            . ' (' . $quotedColumns . ') VALUES (' . $placeholders . ')'
                        );
                        foreach ($backup['tables'][$tableName]['rows'] as $row) {
                            $insert->execute(array_values($row));
                        }
                    }

                    $pdo->commit();
                    $message = 'Database restored successfully from the selected backup.';
                } catch (Throwable $e) {
                    if ($pdo->inTransaction()) {
                        $pdo->rollBack();
                    }
                    error_log('Database restore failed: ' . $e->getMessage());
                    $message = 'The restore failed and the database changes were rolled back.';
                    $messageType = 'error';
                } finally {
                    $pdo->exec('SET FOREIGN_KEY_CHECKS = 1');
                }
            }
        }
    }
}

$admin = SessionManager::getUser($pdo);
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="../assets/css/output.css">
    <link rel="stylesheet" href="../assets/css/global.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
    <link rel="icon" href="../assets/img/rounded-logo.ico" type="image/x-icon">
    <title>Database Backup &amp; Restore</title>
</head>

<body class="h-screen flex bg-slate-200 overflow-hidden">
    <?php include_once '../includes/sidebar.php'; ?>

    <section class="flex-1 min-h-0 flex flex-col overflow-hidden">
        <div class="flex items-center justify-between bg-gradient-to-r from-[#0b1f0b] via-[#1e6b34] to-[#2e8b47] px-6 py-4">
            <div>
                <h1 class="text-2xl font-bold text-white">Database Backup &amp; Restore</h1>
                <p class="text-sm text-white">Protect and recover clinic records</p>
            </div>
        </div>

        <main class="min-h-0 overflow-auto p-6">
            <div class="max-w-3xl space-y-6">
                <?php if ($message): ?>
                    <div role="status"
                        class="rounded-lg border p-4 text-sm <?= $messageType === 'error' ? 'border-red-200 bg-red-50 text-red-700' : 'border-green-200 bg-green-50 text-green-700' ?>">
                        <?= htmlspecialchars($message, ENT_QUOTES, 'UTF-8') ?>
                    </div>
                <?php endif; ?>

                <section class="rounded-lg bg-white p-6 shadow-sm">
                    <div class="flex items-start gap-4">
                        <i class="fa-solid fa-download mt-1 text-2xl text-green-700"></i>
                        <div class="flex-1">
                            <h2 class="text-lg font-bold text-gray-800">Create a full backup</h2>
                            <p class="mt-2 text-sm text-gray-600">
                                Download all clinic database records, including patient records, appointments,
                                consultations, billing, payments, users, and audit logs.
                            </p>
                            <a href="?action=download"
                                class="mt-4 inline-flex items-center gap-2 rounded-lg bg-green-700 px-4 py-2 text-sm font-semibold text-white hover:bg-green-800">
                                <i class="fa-solid fa-file-arrow-down"></i> Download Database Backup
                            </a>
                        </div>
                    </div>
                </section>

                <section class="rounded-lg border border-red-200 bg-white p-6 shadow-sm">
                    <div class="flex items-start gap-4">
                        <i class="fa-solid fa-triangle-exclamation mt-1 text-2xl text-red-600"></i>
                        <div class="flex-1">
                            <h2 class="text-lg font-bold text-gray-800">Restore from a backup</h2>
                            <p class="mt-2 text-sm text-red-700">
                                Restoring replaces all current database records with the records in the selected backup.
                                Download a backup of the current data first. Only a valid backup created for this
                                database with the matching schema can be restored.
                            </p>
                            <form method="post" enctype="multipart/form-data" class="mt-5 space-y-4"
                                onsubmit="return confirm('This will replace all current clinic database records. Continue only if you have selected the correct backup.');">
                                <input type="hidden" name="csrf_token"
                                    value="<?= htmlspecialchars($csrfToken, ENT_QUOTES, 'UTF-8') ?>">
                                <div>
                                    <label for="backup_file" class="mb-1 block text-sm font-medium text-gray-700">
                                        Clinic backup file (.json, maximum 100 MB)
                                    </label>
                                    <input id="backup_file" name="backup_file" type="file" accept=".json,application/json"
                                        required
                                        class="block w-full rounded-lg border border-gray-300 p-2 text-sm text-gray-700">
                                </div>
                                <div>
                                    <label for="restore_confirmation" class="mb-1 block text-sm font-medium text-gray-700">
                                        Type RESTORE to confirm
                                    </label>
                                    <input id="restore_confirmation" name="restore_confirmation" type="text"
                                        autocomplete="off" required
                                        class="w-full rounded-lg border border-gray-300 px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-red-500">
                                </div>
                                <button type="submit"
                                    class="inline-flex items-center gap-2 rounded-lg bg-red-700 px-4 py-2 text-sm font-semibold text-white hover:bg-red-800">
                                    <i class="fa-solid fa-rotate-left"></i> Restore Database
                                </button>
                            </form>
                        </div>
                    </div>
                </section>

                <p class="text-xs text-gray-600">
                    Backups contain sensitive clinic and patient information. Store downloaded files securely and keep
                    copies somewhere separate from this computer.
                </p>
            </div>
        </main>
    </section>
</body>

</html>
