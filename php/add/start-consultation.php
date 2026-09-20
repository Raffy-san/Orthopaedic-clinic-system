<?php
date_default_timezone_set('Asia/Manila');
require_once __DIR__ . '/../../config/config.php';
require_once __DIR__ . '/../../includes/auth.php';
SessionManager::requireLogin();
SessionManager::requireAnyRole(['admin', 'doctor']);

header('Content-Type: application/json');

$appointmentId = $_POST['appointment_id'] ?? null;
$patientId = $_POST['patient_id'] ?? null;
$doctorId = SessionManager::getUser($pdo)['user_id'] ?? null;
$now = date('H:i:s');
$meridiem = strtolower(date('a'));

error_log('DEBUG - appointmentId: ' . var_export($appointmentId, true));
error_log('DEBUG - patientId: ' . var_export($patientId, true));
error_log('DEBUG - doctorId: ' . var_export($doctorId, true));

if (!$appointmentId || !$patientId || !$doctorId) {
    http_response_code(400);
    echo json_encode(['success' => false, 'message' => 'Missing required fields']);
    exit;
}

// Prevent duplicate consultation rows if the button is clicked twice
$check = $pdo->prepare("SELECT ConsultationID FROM consultations WHERE AppointmentID = ?");
$check->execute([$appointmentId]);
$existing = $check->fetch();

if ($existing) {
    echo json_encode(['success' => true, 'consultation_id' => $existing['ConsultationID']]);
    exit;
}

$stmt = $pdo->prepare("
    INSERT INTO consultations (AppointmentID, PatientID, DoctorID, StartTime, Meridiem, IsCompleted, ConsultationFee)
    VALUES (?, ?, ?, ?, ?, 0, ?)
");
$stmt->execute([$appointmentId, $patientId, $doctorId, $now, $meridiem, $defaultConsultationFee ?? 500]);

echo json_encode(['success' => true, 'consultation_id' => $pdo->lastInsertId()]);