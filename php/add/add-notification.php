<?php
require_once __DIR__ . '/../../config/config.php';
require_once __DIR__ . '/../../includes/auth.php';
require_once __DIR__ . '/../../includes/notifications.php';

SessionManager::requireLogin();
SessionManager::requireAnyRole(['admin', 'doctor', 'staff']);

header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['status' => 'error', 'message' => 'Invalid request method.']);
    exit;
}

$requestData = json_decode(file_get_contents('php://input'), true) ?: [];
$followupId = (int) ($requestData['followup_id'] ?? 0);
$csrfToken = $requestData['csrf_token'] ?? '';

if (!hash_equals($_SESSION['csrf_token'] ?? '', $csrfToken)) {
    http_response_code(403);
    echo json_encode(['status' => 'error', 'message' => 'Invalid CSRF token.']);
    exit;
}

if ($followupId < 1) {
    echo json_encode(['status' => 'error', 'message' => 'Follow-up ID is required.']);
    exit;
}

try {
    $statement = $pdo->prepare(
        'SELECT PatientID, FollowUpDate FROM followups WHERE FollowUpID = ?'
    );
    $statement->execute([$followupId]);
    $followup = $statement->fetch(PDO::FETCH_ASSOC);

    if (!$followup) {
        echo json_encode(['status' => 'error', 'message' => 'Follow-up not found.']);
        exit;
    }

    createPatientNotification(
        $pdo,
        (int) $followup['PatientID'],
        'Follow-up reminder',
        'Please remember your follow-up check-up on ' . date('F j, Y', strtotime($followup['FollowUpDate'])) . '.',
        'followup',
        $followupId
    );

    echo json_encode(['status' => 'success', 'message' => 'In-system notification sent to the patient.']);
} catch (PDOException $e) {
    error_log('Add notification failed: ' . $e->getMessage());
    http_response_code(500);
    echo json_encode(['status' => 'error', 'message' => 'Unable to send the notification.']);
}