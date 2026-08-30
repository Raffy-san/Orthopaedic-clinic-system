<?php
require_once __DIR__ . '/../../config/config.php';
require_once __DIR__ . '/../../includes/auth.php';

SessionManager::requireLogin();

header('Content-Type: application/json');

// Verify CSRF token
$csrfToken = $_SESSION['csrf_token'] ?? '';
$requestData = json_decode(file_get_contents('php://input'), true);

if (empty($requestData['csrf_token']) || $requestData['csrf_token'] !== $csrfToken) {
    http_response_code(403);
    echo json_encode(['status' => 'error', 'message' => 'Invalid CSRF token']);
    exit;
}

try {
    $pdo->beginTransaction();

    $appointmentID = intval($requestData['appointment_id'] ?? 0);
    $patientID = intval($requestData['patient_id'] ?? 0);
    $diagnosis = $requestData['diagnosis'] ?? '';
    $treatment = $requestData['treatment'] ?? '';
    $notes = $requestData['notes'] ?? '';
    $consultationFee = floatval($requestData['consultation_fee'] ?? 0);
    $hasPrescription = $requestData['has_prescription'] ?? false;
    $prescriptionData = $requestData['prescription'] ?? null;

    // Get doctor ID from session
    $doctor = SessionManager::getUser($pdo);
    $doctorID = $doctor['UserID'] ?? 1;

    // 1. Insert consultation record
    $stmt = $pdo->prepare("
        INSERT INTO consultations (AppointmentID, PatientID, DoctorID, Diagnosis, Treatment, Notes, ConsultationFee, ConsultationDate)
        VALUES (:appointment_id, :patient_id, :doctor_id, :diagnosis, :treatment, :notes, :consultation_fee, NOW())
    ");

    $stmt->execute([
        ':appointment_id' => $appointmentID,
        ':patient_id' => $patientID,
        ':doctor_id' => $doctorID,
        ':diagnosis' => $diagnosis,
        ':treatment' => $treatment,
        ':notes' => $notes,
        ':consultation_fee' => $consultationFee
    ]);

    $consultationID = $pdo->lastInsertId();

    // 2. Insert prescription if provided
    if ($hasPrescription && $prescriptionData) {
        $stmt = $pdo->prepare("
            INSERT INTO prescriptions (ConsultationID, Medicine, Dosage, Frequency, Duration, Instructions)
            VALUES (:consultation_id, :medicine, :dosage, :frequency, :duration, :instructions)
        ");

        $stmt->execute([
            ':consultation_id' => $consultationID,
            ':medicine' => $prescriptionData['medicine'] ?? '',
            ':dosage' => $prescriptionData['dosage'] ?? '',
            ':frequency' => $prescriptionData['frequency'] ?? '',
            ':duration' => $prescriptionData['duration'] ?? '',
            ':instructions' => $prescriptionData['instructions'] ?? ''
        ]);
    }

    // 3. Insert billing record
    $stmt = $pdo->prepare("
        INSERT INTO billing (ConsultationID, PatientID, OriginalAmount, DiscountType, DiscountPercent, DiscountAmount, FinalAmount, Status)
        VALUES (:consultation_id, :patient_id, :original_amount, 'None', 0, 0, :original_amount, 'Unpaid')
    ");

    $stmt->execute([
        ':consultation_id' => $consultationID,
        ':patient_id' => $patientID,
        ':original_amount' => $consultationFee
    ]);

    // 4. Update appointment status to Completed
    $stmt = $pdo->prepare("
        UPDATE appointments 
        SET Status = 'Completed' 
        WHERE AppointmentID = :appointment_id
    ");

    $stmt->execute([':appointment_id' => $appointmentID]);

    $pdo->commit();

    // Regenerate CSRF token
    SessionManager::regenerateCsrfToken();

    echo json_encode([
        'status' => 'success',
        'message' => 'Consultation saved and passed to billing',
        'consultation_id' => $consultationID,
        'csrf_token' => $_SESSION['csrf_token']
    ]);

} catch (Exception $e) {
    $pdo->rollBack();
    http_response_code(500);
    echo json_encode([
        'status' => 'error',
        'message' => 'Error saving consultation: ' . $e->getMessage()
    ]);
}
?>
