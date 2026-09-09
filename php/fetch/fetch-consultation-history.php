<?php
require_once __DIR__ . '/../../config/config.php';
require_once __DIR__ . '/../../includes/auth.php';

SessionManager::requireLogin();
SessionManager::requireAnyRole(['admin', 'doctor', 'staff']);

header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] !== 'GET') {
    http_response_code(405);
    echo json_encode(['status' => 'error', 'message' => 'Invalid request method.']);
    exit;
}

$patientID = intval($_GET['patient_id'] ?? 0);

if ($patientID === 0) {
    echo json_encode(['status' => 'error', 'message' => 'Patient ID is required.']);
    exit;
}

try {
    // Fetch all consultations for the patient
    $stmt = $pdo->prepare("
        SELECT 
            c.ConsultationID,
            c.Diagnosis,
            c.Treatment,
            c.Notes,
            c.ConsultationFee,
            c.ConsultationDate,
            u.FirstName as DoctorFirstName,
            u.LastName as DoctorLastName
        FROM consultations c
        LEFT JOIN users u ON c.DoctorID = u.UserID
        WHERE c.PatientID = ?
        ORDER BY c.ConsultationDate DESC
    ");
    $stmt->execute([$patientID]);
    $consultations = $stmt->fetchAll(PDO::FETCH_ASSOC);

    // For each consultation, fetch prescriptions
    $result = [];
    foreach ($consultations as $consultation) {
        $prescStmt = $pdo->prepare("
            SELECT Medicine, Dosage, Frequency, Duration, Instructions
            FROM prescriptions
            WHERE ConsultationID = ?
            ORDER BY PrescriptionID
        ");
        $prescStmt->execute([$consultation['ConsultationID']]);
        $prescriptions = $prescStmt->fetchAll(PDO::FETCH_ASSOC);

        $consultation['prescriptions'] = $prescriptions;
        $result[] = $consultation;
    }

    echo json_encode([
        'status' => 'success',
        'data' => $result,
        'total_visits' => count($result)
    ]);

} catch (PDOException $e) {
    error_log('Fetch consultation history failed: ' . $e->getMessage());
    http_response_code(500);
    echo json_encode(['status' => 'error', 'message' => 'Unable to fetch consultation history.']);
}
?>
