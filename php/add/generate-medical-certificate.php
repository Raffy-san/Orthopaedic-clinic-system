<?php
require_once __DIR__ . '/../../config/config.php';
require_once __DIR__ . '/../../includes/auth.php';
require_once __DIR__ . '/../../php/fetch/fetch.php';
require_once __DIR__ . '/../../includes/audit.php';
require_once __DIR__ . '/../../vendor/autoload.php'; // Composer autoloader for dompdf

use Dompdf\Dompdf;
use Dompdf\Options;

SessionManager::requireLogin();
SessionManager::requireAnyRole(['admin', 'doctor', 'staff']); // only medical staff should issue certificates
header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['status' => 'error', 'message' => 'Invalid request method.']);
    exit;
}

$input = json_decode(file_get_contents('php://input'), true) ?? [];

$sessionToken = $_SESSION['csrf_token'] ?? '';
$submittedToken = $input['csrf_token'] ?? '';
if ($sessionToken === '' || $submittedToken === '' || !hash_equals($sessionToken, $submittedToken)) {
    http_response_code(403);
    echo json_encode(['status' => 'error', 'message' => 'Invalid session. Please refresh and try again.']);
    exit;
}

$patientCode = trim($input['patient_code'] ?? '');
$consultationId = !empty($input['consultation_id']) ? (int) $input['consultation_id'] : null;
$diagnosis = trim($input['diagnosis'] ?? '');
$remarks = trim($input['remarks'] ?? '');

if ($patientCode === '' || $diagnosis === '') {
    echo json_encode(['status' => 'error', 'message' => 'Patient and diagnosis are required.']);
    exit;
}

$actorUserId = SessionManager::getUser($pdo)['user_id'] ?? null;

try {
    // NOTE: 'Age' isn't a stored column — computed below from BirthDate instead.
    // NOTE: 'Sex' may also be wrong — your other CRUD functions (addPatient/updatePatient)
    // use 'Gender', so I've used that here instead. Tell me if your schema actually has 'Sex'.
    $patient = fetchOneData(
        $pdo,
        'SELECT PatientID, PatientCode, FirstName, LastName, Gender, Address, BirthDate FROM patients WHERE PatientCode = ?',
        [$patientCode]
    );

    if (!$patient) {
        echo json_encode(['status' => 'error', 'message' => 'Patient not found.']);
        exit;
    }

    // Compute age from BirthDate at the time the certificate is issued
    $age = null;
    if (!empty($patient['BirthDate'])) {
        $birthDate = new DateTime($patient['BirthDate']);
        $age = $birthDate->diff(new DateTime('today'))->y;
    }

    $doctor = SessionManager::getUser($pdo);
    $doctorName = $doctor ? ($doctor['first_name'] . ' ' . $doctor['last_name']) : 'Attending Physician';
    $issuedDate = date('F j, Y');

    // Embed the clinic logo as a base64 data URI — dompdf needs this since isRemoteEnabled
    // is off and relative file paths from the HTML string don't resolve reliably.
    $logoPath = __DIR__ . '/../../assets/img/logo4.png';
    $logoContents = file_get_contents($logoPath);
    if ($logoContents === false) {
        throw new RuntimeException('Unable to read the clinic logo.');
    }
    $logoDataUri = 'data:image/png;base64,' . base64_encode($logoContents);

    // Fetch the actual visit date, if this certificate is tied to a specific consultation.
    // Scoped to the same patient so a stray/mismatched consultation_id can't leak another patient's date.
    $consultationDate = null;
    if ($consultationId) {
        $consultation = fetchOneData(
            $pdo,
            'SELECT ConsultationDate FROM consultations WHERE ConsultationID = ? AND PatientID = ?',
            [$consultationId, $patient['PatientID']]
        );
        if ($consultation) {
            $consultationDate = date('F j, Y', strtotime($consultation['ConsultationDate']));
        }
    }

    // Render the certificate HTML
    ob_start();
    include __DIR__ . '/../../includes/medical-certificate-template.php';
    $html = ob_get_clean();

    // Generate the PDF
    $options = new Options();
    $options->set('isRemoteEnabled', false);
    $dompdf = new Dompdf($options);
    $dompdf->loadHtml($html);
    $dompdf->setPaper('A4', 'portrait');
    $dompdf->render();

    // Save to disk
    $storageDir = __DIR__ . '/../../storage/medical-certificates';
    if (!is_dir($storageDir)) {
        mkdir($storageDir, 0755, true);
    }
    $fileName = 'medcert-' . $patient['PatientCode'] . '-' . date('YmdHis') . '.pdf';
    $filePath = $storageDir . '/' . $fileName;
    file_put_contents($filePath, $dompdf->output());

    // Record it in the database
    $stmt = $pdo->prepare(
        'INSERT INTO medical_certificates (PatientID, ConsultationID, IssuedBy, Diagnosis, Remarks, FilePath)
         VALUES (?, ?, ?, ?, ?, ?)'
    );
    $stmt->execute([
        $patient['PatientID'],
        $consultationId,
        $actorUserId,
        $diagnosis,
        $remarks ?: null,
        'storage/medical-certificates/' . $fileName // relative path for later retrieval
    ]);
    $certificateId = (int) $pdo->lastInsertId();

    // AUDIT: certificate issued
    logAudit(
        $pdo,
        $actorUserId,
        'CREATE',
        'medical_certificates',
        $certificateId,
        null,
        null,
        json_encode([
            'PatientID' => $patient['PatientID'],
            'Diagnosis' => $diagnosis,
            'IssuedBy' => $actorUserId,
        ])
    );

    SessionManager::regenerateCsrfToken();

    echo json_encode([
        'status' => 'success',
        'message' => 'Medical certificate generated successfully.',
        'certificate_id' => $certificateId,
        'download_url' => 'download-medical-certificate.php?id=' . $certificateId,
        'csrf_token' => $_SESSION['csrf_token']
    ]);

} catch (Exception $e) {
    error_log('generate-medical-certificate failed: ' . $e->getMessage());
    echo json_encode(['status' => 'error', 'message' => 'Unable to generate the medical certificate.']);
}