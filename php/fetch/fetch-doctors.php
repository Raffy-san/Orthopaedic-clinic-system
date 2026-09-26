<?php
require_once __DIR__ . '/../../config/config.php';
require_once __DIR__ . '/../../includes/auth.php';

SessionManager::requireLogin();
header('Content-Type: application/json');

try {
    $stmt = $pdo->prepare(
        "SELECT UserID, FirstName, LastName
         FROM users
         WHERE IsDoctor = 1
         ORDER BY LastName, FirstName"
    );
    $stmt->execute();
    $doctors = $stmt->fetchAll(PDO::FETCH_ASSOC);

    echo json_encode(['status' => 'success', 'data' => $doctors]);

} catch (Exception $e) {
    error_log('Doctor list query error: ' . $e->getMessage());
    http_response_code(500);
    echo json_encode(['status' => 'error', 'message' => 'Unable to load doctors.']);
}