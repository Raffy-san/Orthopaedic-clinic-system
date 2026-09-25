<?php
require_once __DIR__ . '/../../config/config.php';
require_once __DIR__ . '/../../includes/auth.php';

SessionManager::requireLogin();
header('Content-Type: application/json');

try {
    $patientId = $_GET['patient_id'] ?? null;
    if (!$patientId || !ctype_digit((string) $patientId)) {
        throw new InvalidArgumentException('Invalid or missing patient_id');
    }

    // Diagnosis & treatment history
    $stmt = $pdo->prepare("
        SELECT 
            c.PatientID,
            c.ConsultationID,
            c.AppointmentID,
            c.DoctorID,
            d.FirstName AS DoctorFirstName,
            d.LastName AS DoctorLastName,
            c.Diagnosis,
            c.Treatment,
            c.Notes,
            c.ConsultationDate,
            c.StartTime,
            c.Meridiem,
            c.EndTime
        FROM consultations c
        INNER JOIN users d ON c.DoctorID = d.UserID
        WHERE c.PatientID = ? AND c.IsCompleted = 1
        ORDER BY c.ConsultationDate DESC
    ");
    $stmt->execute([$patientId]);
    $consultations = $stmt->fetchAll(PDO::FETCH_ASSOC);

    if (empty($consultations)) {
        echo json_encode(['status' => 'success', 'data' => []]);
        exit;
    }

    // Medicine history for those consultations
    $consultationIds = array_column($consultations, 'ConsultationID');
    $placeholders = implode(',', array_fill(0, count($consultationIds), '?'));

    $stmt2 = $pdo->prepare("
        SELECT 
            PrescriptionID,
            ConsultationID,
            Medicine,
            Dosage,
            Frequency,
            Duration,
            Instructions
        FROM prescriptions
        WHERE ConsultationID IN ($placeholders)
        ORDER BY PrescriptionID ASC
    ");
    $stmt2->execute($consultationIds);
    $prescriptions = $stmt2->fetchAll(PDO::FETCH_ASSOC);

    // Group prescriptions by ConsultationID
    $byConsultation = [];
    foreach ($prescriptions as $rx) {
        $byConsultation[$rx['ConsultationID']][] = $rx;
    }

    foreach ($consultations as &$c) {
        $c['Medicines'] = $byConsultation[$c['ConsultationID']] ?? [];
    }
    unset($c);

    echo json_encode(['status' => 'success', 'data' => $consultations]);

} catch (InvalidArgumentException $e) {
    http_response_code(400);
    echo json_encode(['status' => 'error', 'message' => $e->getMessage()]);
} catch (Exception $e) {
    error_log('Patient history query error: ' . $e->getMessage());
    http_response_code(500);
    echo json_encode(['status' => 'error', 'message' => 'Unable to load patient history.']);
}