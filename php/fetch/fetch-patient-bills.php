<?php
require_once __DIR__ . '/../../config/config.php';
require_once __DIR__ . '/../../includes/auth.php';

SessionManager::requireLogin();
SessionManager::requireAnyRole(['admin', 'doctor', 'staff']);
header('Content-Type: application/json');

try {
    $patientCode = trim($_GET['patient_code'] ?? '');
    if ($patientCode === '') {
        throw new InvalidArgumentException('Patient code is required.');
    }

    $stmt = $pdo->prepare("
        SELECT b.BillingID, b.FinalAmount, b.Status,
               c.ConsultationID, c.ConsultationDate, c.Diagnosis
        FROM billing b
        INNER JOIN patients p      ON p.PatientID = b.PatientID
        INNER JOIN consultations c ON c.ConsultationID = b.ConsultationID
        WHERE p.PatientCode = ? AND c.IsCompleted = 1
        ORDER BY (b.Status = 'Paid') ASC, c.ConsultationDate DESC, b.BillingID DESC
    ");
    $stmt->execute([$patientCode]);

    echo json_encode(['status' => 'success', 'data' => $stmt->fetchAll(PDO::FETCH_ASSOC)]);
} catch (InvalidArgumentException $e) {
    http_response_code(400);
    echo json_encode(['status' => 'error', 'message' => $e->getMessage()]);
} catch (Exception $e) {
    error_log('Patient bills query error: ' . $e->getMessage());
    http_response_code(500);
    echo json_encode(['status' => 'error', 'message' => 'Unable to load bills.']);
}