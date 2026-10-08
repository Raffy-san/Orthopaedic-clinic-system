<?php
require_once __DIR__ . '/../../config/config.php';
require_once __DIR__ . '/../../includes/auth.php';
require_once __DIR__ . '/../../includes/audit.php';

SessionManager::requireLogin();
SessionManager::requireAnyRole(['admin', 'doctor']);
header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['status' => 'error', 'message' => 'Invalid request method.']);
    exit;
}

$input = json_decode(file_get_contents('php://input'), true) ?? [];

$sessionToken = $_SESSION['csrf_token'] ?? '';
$submittedToken = $input['csrf_token'] ?? '';
if ($sessionToken === '' || $submittedToken === '' || $submittedToken !== $sessionToken) {
    http_response_code(403);
    echo json_encode(['status' => 'error', 'message' => 'Invalid CSRF token']);
    exit;
}

$unavailabilityId = intval($input['unavailability_id'] ?? 0);
if (!$unavailabilityId) {
    echo json_encode(['status' => 'error', 'message' => 'Missing unavailability ID.']);
    exit;
}

$actorUserId = SessionManager::getUser($pdo)['user_id'] ?? null;

try {
    // Fetch before deleting, for the audit log
    $existing = $pdo->prepare('SELECT UnavailableDate, Reason FROM doctor_unavailability WHERE UnavailabilityID = ?');
    $existing->execute([$unavailabilityId]);
    $row = $existing->fetch(PDO::FETCH_ASSOC);

    if (!$row) {
        http_response_code(404);
        echo json_encode(['status' => 'error', 'message' => 'This entry no longer exists.']);
        exit;
    }

    $stmt = $pdo->prepare('DELETE FROM doctor_unavailability WHERE UnavailabilityID = ?');
    $stmt->execute([$unavailabilityId]);

    // AUDIT
    logAudit(
        $pdo,
        $actorUserId,
        'DELETE',
        'doctor_unavailability',
        $unavailabilityId,
        null,
        json_encode($row),
        null
    );

    echo json_encode(['status' => 'success', 'message' => 'Date removed. It is bookable again.']);
} catch (PDOException $e) {
    error_log('delete-unavailability failed: ' . $e->getMessage());
    echo json_encode(['status' => 'error', 'message' => 'Unable to remove this date. Please try again.']);
}