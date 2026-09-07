<?php
require_once '../../config/config.php';
require_once '../../includes/auth.php';
require_once '../../php/fetch/fetch.php';

SessionManager::requireLogin();
SessionManager::requireAnyRole(['admin', 'doctor', 'staff']);
header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] !== 'GET') {
    http_response_code(405);
    echo json_encode(['status' => 'error', 'message' => 'Invalid request method.']);
    exit;
}

$patientCode = trim($_GET['patient_code'] ?? '');
if ($patientCode === '') {
    echo json_encode(['status' => 'error', 'message' => 'Please enter a Patient ID.']);
    exit;
}

// Get patient info
$patient = fetchOneData(
    $pdo,
    'SELECT PatientID, PatientCode, FirstName, LastName, PatientType, BirthDate, Phone, Email
     FROM patients WHERE PatientCode = ?',
    [$patientCode]
);

if (!$patient) {
    echo json_encode(['status' => 'error', 'message' => 'No patient found with that ID.']);
    exit;
}

// Get latest consultation record for this patient
$consultation = fetchOneData(
    $pdo,
    'SELECT c.ConsultationID, c.Diagnosis, c.Treatment, c.Notes, c.ConsultationFee, c.ConsultationDate,
            u.FirstName as DoctorFirstName, u.LastName as DoctorLastName
     FROM consultations c
     LEFT JOIN users u ON c.DoctorID = u.UserID
     WHERE c.PatientID = ?
     ORDER BY c.ConsultationDate DESC
     LIMIT 1',
    [$patient['PatientID']]
);

if (!$consultation) {
    echo json_encode(['status' => 'error', 'message' => 'No consultation record found for this patient.']);
    exit;
}

// Get prescription details if they exist
$prescriptions = fetchAllData(
    $pdo,
    'SELECT Medicine, Dosage, Frequency, Duration, Instructions
     FROM prescriptions
     WHERE ConsultationID = ?
     ORDER BY PrescriptionID',
    [$consultation['ConsultationID']]
);

// Get or create billing record
$billing = fetchOneData(
    $pdo,
    'SELECT BillingID, OriginalAmount, DiscountType, DiscountPercent, DiscountAmount, FinalAmount, Status
     FROM billing
     WHERE ConsultationID = ?',
    [$consultation['ConsultationID']]
);

// If no billing record exists, create one
if (!$billing) {
    $originalAmount = $consultation['ConsultationFee'] ?? 1200.00;
    try {
        $stmt = $pdo->prepare(
            'INSERT INTO billing (ConsultationID, PatientID, OriginalAmount, DiscountType, FinalAmount, Status)
             VALUES (?, ?, ?, ?, ?, ?)'
        );
        $stmt->execute([
            $consultation['ConsultationID'],
            $patient['PatientID'],
            $originalAmount,
            'None',
            $originalAmount,
            'Unpaid'
        ]);
        
        $billing = [
            'BillingID' => $pdo->lastInsertId(),
            'OriginalAmount' => $originalAmount,
            'DiscountType' => 'None',
            'DiscountPercent' => 0.00,
            'DiscountAmount' => 0.00,
            'FinalAmount' => $originalAmount,
            'Status' => 'Unpaid'
        ];
    } catch (Exception $e) {
        echo json_encode(['status' => 'error', 'message' => 'Error creating billing record.']);
        exit;
    }
}

echo json_encode([
    'status' => 'success',
    'patient' => $patient,
    'consultation' => $consultation,
    'billing' => $billing,
    'prescriptions' => $prescriptions
]);
