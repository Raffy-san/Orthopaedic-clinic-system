<?php
include_once __DIR__ . '/../../config/config.php';
require_once __DIR__ . '/../../includes/auth.php';


header('Content-Type: application/json');
try {
    $stmt = $pdo->query("SELECT PatientCode, FirstName, LastName FROM patients ORDER BY CreatedAt DESC");
    $patients = $stmt->fetchAll(PDO::FETCH_ASSOC);
    echo json_encode(['status' => 'success', 'patients' => $patients]);
} catch (PDOException $e) {
    echo json_encode(['status' => 'error', 'message' => 'Error fetching patients']);
}