<?php
require_once __DIR__ . '/../../config/config.php';
require_once __DIR__ . '/../../includes/auth.php';

SessionManager::requireLogin();
SessionManager::requireAnyRole(['admin', 'doctor', 'staff']);

header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['status' => 'error', 'message' => 'Invalid request method.']);
    exit;
}

$requestData = json_decode(file_get_contents('php://input'), true);
$followupID = intval($requestData['followup_id'] ?? 0);
$status = $requestData['status'] ?? 'Scheduled';

if ($followupID === 0) {
    echo json_encode(['status' => 'error', 'message' => 'Follow-up ID is required.']);
    exit;
}

// Validate status
$validStatuses = ['Scheduled', 'Completed', 'Cancelled'];
if (!in_array($status, $validStatuses)) {
    echo json_encode(['status' => 'error', 'message' => 'Invalid status.']);
    exit;
}

try {
    $stmt = $pdo->prepare("
        UPDATE followups 
        SET Status = ? 
        WHERE FollowUpID = ?
    ");
    $stmt->execute([$status, $followupID]);

    echo json_encode([
        'status' => 'success',
        'message' => 'Follow-up status updated successfully.',
        'new_status' => $status
    ]);

} catch (PDOException $e) {
    error_log('Update follow-up status failed: ' . $e->getMessage());
    http_response_code(500);
    echo json_encode(['status' => 'error', 'message' => 'Unable to update follow-up status.']);
}
?>
