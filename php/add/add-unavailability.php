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

$unavailableDate = trim($_POST['unavailable_date'] ?? '');
$reason = trim($_POST['reason'] ?? '');

$parsedDate = DateTime::createFromFormat('!Y-m-d', $unavailableDate);
$dateErrors = DateTime::getLastErrors();
if (!$parsedDate || ($dateErrors !== false && ($dateErrors['warning_count'] > 0 || $dateErrors['error_count'] > 0)) || $parsedDate->format('Y-m-d') !== $unavailableDate) {
    echo json_encode(['status' => 'error', 'message' => 'Please provide a valid date.']);
    exit;
}

if ($parsedDate < new DateTime('today')) {
    echo json_encode(['status' => 'error', 'message' => 'Cannot mark a past date as unavailable.']);
    exit;
}

$actorUserId = SessionManager::getUser($pdo)['user_id'] ?? null;

try {
    $stmt = $pdo->prepare(
        'INSERT INTO doctor_unavailability (UnavailableDate, Reason, CreatedBy) VALUES (?, ?, ?)'
    );
    $stmt->execute([$unavailableDate, $reason ?: null, $actorUserId]);
    $unavailabilityId = (int) $pdo->lastInsertId();

    // AUDIT
    logAudit(
        $pdo,
        $actorUserId,
        'CREATE',
        'doctor_unavailability',
        $unavailabilityId,
        null,
        null,
        json_encode(['UnavailableDate' => $unavailableDate, 'Reason' => $reason ?: null])
    );

    echo json_encode([
        'status' => 'success',
        'message' => 'Date marked as unavailable.',
        'csrf_token' => SessionManager::regenerateCsrfToken()
    ]);
} catch (PDOException $e) {
    $message = $e->getCode() === '23000'
        ? 'This date is already marked unavailable.'
        : 'Unable to save this date. Please try again.';
    error_log('add-unavailability failed: ' . $e->getMessage());
    echo json_encode(['status' => 'error', 'message' => $message]);
}