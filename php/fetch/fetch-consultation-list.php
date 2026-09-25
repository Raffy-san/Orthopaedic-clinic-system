<?php
require_once __DIR__ . '/../../config/config.php';
require_once __DIR__ . '/../../includes/auth.php';

SessionManager::requireLogin();
header('Content-Type: application/json');

try {
    $search = trim($_GET['search'] ?? '');

    $sql = "
        SELECT
            c.ConsultationID,
            c.PatientID,
            c.ConsultationDate,
            c.IsCompleted,
            p.PatientCode,
            p.FirstName,
            p.LastName
        FROM consultations c
        INNER JOIN patients p ON p.PatientID = c.PatientID
    ";

    $params = [];

    if ($search !== '') {
        $sql .= " WHERE p.FirstName LIKE ? OR p.LastName LIKE ? OR p.PatientCode LIKE ? ";
        $like = "%{$search}%";
        $params = [$like, $like, $like];
    }

    $sql .= " ORDER BY c.ConsultationDate DESC ";

    $stmt = $pdo->prepare($sql);
    $stmt->execute($params);
    $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);

    echo json_encode(['status' => 'success', 'data' => $rows]);

} catch (Exception $e) {
    error_log('Consultations list query error: ' . $e->getMessage());
    http_response_code(500);
    echo json_encode(['status' => 'error', 'message' => 'Unable to load records.']);
}