<?php
require_once __DIR__ . '/../../config/config.php';
require_once __DIR__ . '/../../includes/auth.php';

SessionManager::requireLogin();
SessionManager::requireAnyRole(['admin', 'doctor']);
header('Content-Type: application/json');

try {
    $perPage = 25;
    $page = max(1, (int) ($_GET['page'] ?? 1));
    $offset = ($page - 1) * $perPage;

    $userId = trim($_GET['user_id'] ?? '');
    $action = strtoupper(trim($_GET['action'] ?? ''));
    $table = trim($_GET['table'] ?? '');
    $dateFrom = trim($_GET['date_from'] ?? '');
    $dateTo = trim($_GET['date_to'] ?? '');
    $search = trim($_GET['search'] ?? '');

    $conditions = [];
    $params = [];

    if ($userId !== '' && ctype_digit($userId)) {
        $conditions[] = 'a.UserID = ?';
        $params[] = (int) $userId;
    }
    if (in_array($action, ['CREATE', 'UPDATE', 'DELETE'], true)) {
        $conditions[] = 'a.Action = ?';
        $params[] = $action;
    }
    if ($table !== '') {
        $conditions[] = 'a.TableAffected = ?';
        $params[] = $table;
    }
    if ($dateFrom !== '' && DateTime::createFromFormat('Y-m-d', $dateFrom)) {
        $conditions[] = 'a.LogDate >= ?';
        $params[] = $dateFrom . ' 00:00:00';
    }
    if ($dateTo !== '' && DateTime::createFromFormat('Y-m-d', $dateTo)) {
        $conditions[] = 'a.LogDate < DATE_ADD(?, INTERVAL 1 DAY)';
        $params[] = $dateTo;
    }
    if ($search !== '') {
        if (ctype_digit($search)) {
            $conditions[] = '(a.RecordID = ? OR a.FieldChanged LIKE ? OR a.OldValue LIKE ? OR a.NewValue LIKE ?)';
            $params[] = (int) $search;
        } else {
            $conditions[] = '(a.FieldChanged LIKE ? OR a.OldValue LIKE ? OR a.NewValue LIKE ?)';
        }
        $like = '%' . $search . '%';
        array_push($params, $like, $like, $like);
    }

    $where = $conditions ? 'WHERE ' . implode(' AND ', $conditions) : '';

    $countStmt = $pdo->prepare("SELECT COUNT(*) FROM auditlogs a $where");
    $countStmt->execute($params);
    $total = (int) $countStmt->fetchColumn();

    // $perPage and $offset are integers, safe to place in the query
    $stmt = $pdo->prepare("
        SELECT a.LogID, a.UserID,
               NULLIF(CONCAT_WS(' ', u.FirstName, u.LastName), '') AS UserName,
               a.Action, a.TableAffected, a.RecordID, a.FieldChanged,
               a.OldValue, a.NewValue, a.LogDate, a.IPAddress
        FROM auditlogs a
        LEFT JOIN users u ON u.UserID = a.UserID
        $where
        ORDER BY a.LogDate DESC, a.LogID DESC
        LIMIT $perPage OFFSET $offset
    ");
    $stmt->execute($params);

    $response = [
        'status' => 'success',
        'data' => $stmt->fetchAll(PDO::FETCH_ASSOC),
        'total' => $total,
        'page' => $page,
        'per_page' => $perPage,
    ];

    // Dropdown options, only requested on the first load
    if (isset($_GET['meta'])) {
        $response['tables'] = $pdo->query('SELECT DISTINCT TableAffected FROM auditlogs ORDER BY TableAffected')
            ->fetchAll(PDO::FETCH_COLUMN);
        $response['users'] = $pdo->query(
            "SELECT UserID, CONCAT_WS(' ', FirstName, LastName) AS Name FROM users ORDER BY FirstName, LastName"
        )->fetchAll(PDO::FETCH_ASSOC);
    }

    echo json_encode($response);
} catch (Exception $e) {
    error_log('Audit log query error: ' . $e->getMessage());
    http_response_code(500);
    echo json_encode(['status' => 'error', 'message' => 'Unable to load audit logs.']);
}