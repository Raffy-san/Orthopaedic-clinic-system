<?php
function logAudit(
    PDO $pdo,
    ?int $actorUserId,
    string $action,
    string $tableAffected,
    int $recordId,
    ?string $fieldChanged = null,
    ?string $oldValue = null,
    ?string $newValue = null
): void {
    $ip = $_SERVER['REMOTE_ADDR'] ?? null;

    $stmt = $pdo->prepare(
        'INSERT INTO auditlogs (UserID, Action, TableAffected, RecordID, FieldChanged, OldValue, NewValue, IPAddress)
         VALUES (?, ?, ?, ?, ?, ?, ?, ?)'
    );
    $stmt->execute([
        $actorUserId,
        $action,
        $tableAffected,
        $recordId,
        $fieldChanged,
        $oldValue,
        $newValue,
        $ip
    ]);
}

function logFieldChanges(
    PDO $pdo,
    ?int $actorUserId,
    string $tableAffected,
    int $recordId,
    array $oldRow,
    array $newRow
): void {
    static $sensitiveFields = ['PasswordHash', 'Password'];

    foreach ($newRow as $field => $newValue) {
        if (in_array($field, $sensitiveFields, true)) {
            continue;
        }
        if (!array_key_exists($field, $oldRow)) {
            continue;
        }

        $oldValue = $oldRow[$field];

        // Normalize for comparison (avoid false positives from type differences, e.g. "1" vs 1)
        if ((string) $oldValue === (string) $newValue) {
            continue;
        }

        logAudit(
            $pdo,
            $actorUserId,
            'UPDATE',
            $tableAffected,
            $recordId,
            $field,
            $oldValue === null ? null : (string) $oldValue,
            $newValue === null ? null : (string) $newValue
        );
    }
}