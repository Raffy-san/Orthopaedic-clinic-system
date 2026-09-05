<?php
require_once '../../config/config.php';
require_once '../../includes/auth.php';
require_once '../../includes/crud.php';

SessionManager::requireLogin();
SessionManager::requireAnyRole(['admin', 'doctor', 'staff']);
header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] === 'GET') {
    $date = $_GET['date'] ?? '';
    $parsedDate = DateTime::createFromFormat('!Y-m-d', $date);
    $dateErrors = DateTime::getLastErrors();
    if (!$parsedDate || ($dateErrors !== false && ($dateErrors['warning_count'] > 0 || $dateErrors['error_count'] > 0)) || $parsedDate->format('Y-m-d') !== $date) {
        http_response_code(400);
        echo json_encode(['status' => 'error', 'message' => 'Please choose a valid appointment date.']);
        exit;
    }

    $statement = $pdo->prepare("SELECT TIME_FORMAT(AppointmentTime, '%h:%i %p') AS appointment_time
        FROM appointments WHERE AppointmentDate = ? AND Status <> 'Cancelled'");
    $statement->execute([$date]);
    echo json_encode([
        'status' => 'success',
        'taken_times' => array_column($statement->fetchAll(PDO::FETCH_ASSOC), 'appointment_time')
    ]);
    exit;
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['status' => 'error', 'message' => 'Invalid request method.']);
    exit;
}

$sessionToken = $_SESSION['csrf_token'] ?? '';
$submittedToken = $_POST['csrf_token'] ?? '';
if ($sessionToken === '' || $submittedToken === '' || !hash_equals($sessionToken, $submittedToken)) {
    http_response_code(403);
    echo json_encode([
        'status' => 'error',
        'message' => 'Your session token is invalid. Refresh the page and try again.',
        'csrf_token' => SessionManager::regenerateCsrfToken()
    ]);
    exit;
}

$data = [
    'patientCode' => trim($_POST['patient_id'] ?? ''),
    'appointmentDate' => $_POST['appointment_date'] ?? '',
    'appointmentTime' => $_POST['appointment_time'] ?? '',
    'purpose' => trim($_POST['purpose'] ?? 'General consultation'),
    'chiefComplaint' => trim($_POST['chief_complaint'] ?? ''),
];

$userId = $_SESSION['user_id'] ?? $_SESSION['userId'] ?? $_SESSION['user']['UserID'] ?? null;
$appointmentTime = DateTime::createFromFormat('h:i A', $data['appointmentTime']);
if ($data['patientCode'] === '' || $data['appointmentDate'] === '' || !$appointmentTime || $userId === null) {
    echo json_encode(['status' => 'error', 'message' => 'Please provide a valid patient, date, and time.']);
    exit;
}

$patientStatement = $pdo->prepare('SELECT PatientID FROM patients WHERE PatientCode = ?');
$patientStatement->execute([$data['patientCode']]);
$patientId = $patientStatement->fetchColumn();
if (!$patientId) {
    echo json_encode(['status' => 'error', 'message' => 'Patient code not found.']);
    exit;
}

$data['patientId'] = $patientId;
$data['doctorId'] = $userId;
$data['meridiem'] = $appointmentTime->format('A');
$data['appointmentTime'] = $appointmentTime->format('H:i:s');

$result = bookAppointment($pdo, $data);
$result['csrf_token'] = SessionManager::regenerateCsrfToken();
echo json_encode($result);