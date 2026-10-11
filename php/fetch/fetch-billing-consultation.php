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

$requestedBillingId = trim($_GET['billing_id'] ?? '');
if ($requestedBillingId !== '' && !ctype_digit($requestedBillingId)) {
    http_response_code(400);
    echo json_encode(['status' => 'error', 'message' => 'Invalid billing ID.']);
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

$consultationSelect = 'SELECT c.ConsultationID, c.Diagnosis, c.Treatment, c.Notes, c.ConsultationFee, c.ConsultationDate,
            u.FirstName as DoctorFirstName, u.LastName as DoctorLastName
     FROM consultations c
     LEFT JOIN users u ON c.DoctorID = u.UserID';

$billing = null;

if ($requestedBillingId !== '') {
    // A specific bill was chosen: it must belong to THIS patient
    $billing = fetchOneData(
        $pdo,
        'SELECT BillingID, ConsultationID, OriginalAmount, DiscountType, DiscountPercent, DiscountAmount, FinalAmount, Status
         FROM billing
         WHERE BillingID = ? AND PatientID = ?',
        [(int) $requestedBillingId, $patient['PatientID']]
    );

    if (!$billing) {
        echo json_encode(['status' => 'error', 'message' => 'That bill was not found for this patient.']);
        exit;
    }

    $consultation = fetchOneData(
        $pdo,
        $consultationSelect . ' WHERE c.ConsultationID = ? AND c.PatientID = ?',
        [$billing['ConsultationID'], $patient['PatientID']]
    );
} else {
    // No bill chosen: latest consultation, as before
    $consultation = fetchOneData(
        $pdo,
        $consultationSelect . ' WHERE c.PatientID = ? ORDER BY c.ConsultationDate DESC LIMIT 1',
        [$patient['PatientID']]
    );
}

if (!$consultation) {
    echo json_encode(['status' => 'error', 'message' => 'No consultation record found for this patient.']);
    exit;
}

// Get prescription details if they exist
$prescriptions = fetchAllData(
    $pdo,
    'SELECT Medicine, Dosage, Frequency, Duration, Quantity, Instructions
     FROM prescriptions
     WHERE ConsultationID = ?
     ORDER BY PrescriptionID',
    [$consultation['ConsultationID']]
);

// Get or create billing record (only needed when no specific bill was chosen)
if (!$billing) {
    $billing = fetchOneData(
        $pdo,
        'SELECT BillingID, OriginalAmount, DiscountType, DiscountPercent, DiscountAmount, FinalAmount, Status
         FROM billing
         WHERE ConsultationID = ?',
        [$consultation['ConsultationID']]
    );
}

if (!$billing) {
    $originalAmount = $consultation['ConsultationFee'] ?? 1200.00;
    $patientType = trim((string) ($patient['PatientType'] ?? 'Regular'));
    $discountType = match (strtolower($patientType)) {
        'senior citizen' => 'Senior Citizen',
        'pwd' => 'PWD',
        default => 'None',
    };
    $discountPercent = $discountType === 'None' ? 0 : 20;
    $discountAmount = round($originalAmount * $discountPercent / 100, 2);
    $finalAmount = $originalAmount - $discountAmount;

    try {
        $stmt = $pdo->prepare(
            'INSERT INTO billing (ConsultationID, PatientID, OriginalAmount, DiscountType, DiscountPercent, DiscountAmount, FinalAmount, Status)
             VALUES (?, ?, ?, ?, ?, ?, ?, ?)'
        );
        $stmt->execute([
            $consultation['ConsultationID'],
            $patient['PatientID'],
            $originalAmount,
            $discountType,
            $discountPercent,
            $discountAmount,
            $finalAmount,
            'Unpaid'
        ]);

        $billing = [
            'BillingID' => $pdo->lastInsertId(),
            'OriginalAmount' => $originalAmount,
            'DiscountType' => $discountType,
            'DiscountPercent' => $discountPercent,
            'DiscountAmount' => $discountAmount,
            'FinalAmount' => $finalAmount,
            'Status' => 'Unpaid'
        ];
    } catch (Exception $e) {
        error_log('Billing create failed: ' . $e->getMessage());
        echo json_encode(['status' => 'error', 'message' => 'Error creating billing record.']);
        exit;
    }
}

$paymentSummary = fetchOneData(
    $pdo,
    'SELECT COALESCE(SUM(AmountPaid), 0) AS TotalPaid
     FROM payments WHERE BillingID = ?',
    [$billing['BillingID']]
);
$billing['TotalPaid'] = (float) ($paymentSummary['TotalPaid'] ?? 0);
$billing['AmountDue'] = max(0, (float) $billing['FinalAmount'] - $billing['TotalPaid']);

echo json_encode([
    'status' => 'success',
    'patient' => $patient,
    'consultation' => $consultation,
    'billing' => $billing,
    'prescriptions' => $prescriptions
]);