<?php
require_once __DIR__ . '/../../config/config.php';
require_once __DIR__ . '/../../includes/auth.php';
require_once __DIR__ . '/../../php/fetch/fetch.php';

SessionManager::requireLogin();
SessionManager::requireAnyRole(['admin', 'doctor', 'staff']);
header('Content-Type: application/json');

$patientCode = trim($_GET['patient_code'] ?? '');
if ($patientCode === '') {
    http_response_code(400);
    echo json_encode(['status' => 'error', 'message' => 'Patient code is required.']);
    exit;
}

try {
    $patient = fetchOneData(
        $pdo,
        'SELECT PatientID, PatientCode, FirstName, MiddleName, LastName, BirthDate, Gender,
                Phone, PatientType, Address, Province, City, Barangay, CreatedAt
         FROM patients WHERE PatientCode = ?',
        [$patientCode]
    );

    if (!$patient) {
        http_response_code(404);
        echo json_encode(['status' => 'error', 'message' => 'Patient not found.']);
        exit;
    }

    $patientId = $patient['PatientID'];

    // Visit / consultation summary
    $consultations = fetchAllData(
        $pdo,
        "SELECT c.ConsultationID, c.ConsultationDate, c.Diagnosis, c.Treatment, c.ConsultationFee,
                CONCAT(u.FirstName, ' ', u.LastName) AS DoctorName
         FROM consultations c
         LEFT JOIN appointments a ON a.AppointmentID = c.AppointmentID
         LEFT JOIN users u ON u.UserID = a.DoctorID
         WHERE c.PatientID = ?
         ORDER BY c.ConsultationDate DESC",
        [$patientId]
    );

    $prescriptionsByConsultation = [];
    if (!empty($consultations)) {
        $consultationIds = array_column($consultations, 'ConsultationID');
        $placeholders = implode(',', array_fill(0, count($consultationIds), '?'));
        $prescriptions = fetchAllData(
            $pdo,
            "SELECT ConsultationID, Medicine, Dosage, Frequency, Duration, Quantity, Instructions
             FROM prescriptions
             WHERE ConsultationID IN ($placeholders)
             ORDER BY PrescriptionID ASC",
            $consultationIds
        );

        foreach ($prescriptions as $prescription) {
            $prescriptionsByConsultation[$prescription['ConsultationID']][] = $prescription;
        }
    }

    foreach ($consultations as &$consultation) {
        $consultation['Prescriptions'] = $prescriptionsByConsultation[$consultation['ConsultationID']] ?? [];
    }
    unset($consultation);

    // Billing summary
    $billingSummary = fetchOneData(
        $pdo,
        "SELECT COUNT(*) AS total_bills,
                COALESCE(SUM(FinalAmount), 0) AS total_billed,
                COALESCE(SUM(CASE WHEN Status = 'Paid' THEN FinalAmount ELSE 0 END), 0) AS total_paid
         FROM billing WHERE PatientID = ?",
        [$patientId]
    );

    echo json_encode([
        'status' => 'success',
        'data' => [
            'patient' => $patient,
            'consultations' => $consultations,
            'billing_summary' => $billingSummary,
        ]
    ]);

} catch (PDOException $e) {
    error_log('fetch-patient-report failed: ' . $e->getMessage());
    http_response_code(500);
    echo json_encode(['status' => 'error', 'message' => 'Unable to load the patient report.']);
}