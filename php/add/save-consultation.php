<?php
require_once __DIR__ . '/../../config/config.php';
require_once __DIR__ . '/../../includes/auth.php';
require_once __DIR__ . '/../../includes/crud.php';
require_once __DIR__ . '/../../includes/notifications.php';

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

// Get doctor ID from session
$doctor = SessionManager::getUser($pdo);
$doctorID = (int) ($doctor['user_id'] ?? $doctor['UserID'] ?? 0);

if ($doctorID < 1) {
    http_response_code(401);
    echo json_encode(['status' => 'error', 'message' => 'Unable to identify the logged-in doctor.']);
    exit;
}

// Call the saveConsultation function from crud.php
$result = saveConsultation($pdo, $requestData, $doctorID);

if (($result['code'] ?? '') === 'followup_date_unavailable' && !empty($result['patient_id'])) {
    createPatientNotification(
        $pdo,
        (int) $result['patient_id'],
        'Follow-up date unavailable',
        'The doctor is unavailable on ' . date('F j, Y', strtotime($result['requested_date'])) . '. Please select an available alternative date in the patient portal or contact the clinic.',
        'followup'
    );
}

// Regenerate CSRF token
SessionManager::regenerateCsrfToken();

// Add csrf token to response
$result['csrf_token'] = $_SESSION['csrf_token'];

// Set response code based on status
if ($result['status'] === 'error') {
    http_response_code($result['code'] === 'followup_date_unavailable' ? 409 : 500);
}

echo json_encode($result);
?>
