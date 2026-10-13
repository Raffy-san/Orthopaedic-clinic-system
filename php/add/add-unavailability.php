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

$startDate = trim($_POST['start_date'] ?? '');
$endDate = trim($_POST['end_date'] ?? '');
$reason = trim($_POST['reason'] ?? '');

$parsedStartDate = DateTime::createFromFormat('!Y-m-d', $startDate);
$startDateErrors = DateTime::getLastErrors();
$parsedEndDate = DateTime::createFromFormat('!Y-m-d', $endDate);
$endDateErrors = DateTime::getLastErrors();
if (
    !$parsedStartDate
    || ($startDateErrors !== false && ($startDateErrors['warning_count'] > 0 || $startDateErrors['error_count'] > 0))
    || $parsedStartDate->format('Y-m-d') !== $startDate
    || !$parsedEndDate
    || ($endDateErrors !== false && ($endDateErrors['warning_count'] > 0 || $endDateErrors['error_count'] > 0))
    || $parsedEndDate->format('Y-m-d') !== $endDate
) {
    echo json_encode(['status' => 'error', 'message' => 'Please provide a valid start and end date.']);
    exit;
}

if ($parsedStartDate < new DateTime('today') || $parsedEndDate < new DateTime('today')) {
    echo json_encode(['status' => 'error', 'message' => 'Cannot mark a past date as unavailable.']);
    exit;
}

if ($parsedEndDate < $parsedStartDate) {
    echo json_encode(['status' => 'error', 'message' => 'The end date must be on or after the start date.']);
    exit;
}

$actorUserId = SessionManager::getUser($pdo)['user_id'] ?? null;

try {
    $pdo->beginTransaction();

    $existingDates = $pdo->prepare(
        'SELECT UnavailableDate
         FROM doctor_unavailability
         WHERE UnavailableDate BETWEEN ? AND ?
         ORDER BY UnavailableDate ASC
         FOR UPDATE'
    );
    $existingDates->execute([$startDate, $endDate]);
    $existingDate = $existingDates->fetchColumn();
    if ($existingDate) {
        $pdo->rollBack();
        echo json_encode([
            'status' => 'error',
            'message' => 'The range includes a date already marked unavailable (' . date('F j, Y', strtotime($existingDate)) . '). No dates were added.'
        ]);
        exit;
    }

    $insertDate = $pdo->prepare(
        'INSERT INTO doctor_unavailability (UnavailableDate, Reason, CreatedBy) VALUES (?, ?, ?)'
    );
    $periodEnd = (clone $parsedEndDate)->modify('+1 day');
    $datePeriod = new DatePeriod($parsedStartDate, new DateInterval('P1D'), $periodEnd);
    foreach ($datePeriod as $unavailableDate) {
        $date = $unavailableDate->format('Y-m-d');
        $insertDate->execute([$date, $reason ?: null, $actorUserId]);
        $unavailabilityId = (int) $pdo->lastInsertId();

        logAudit(
            $pdo,
            $actorUserId,
            'CREATE',
            'doctor_unavailability',
            $unavailabilityId,
            null,
            null,
            json_encode(['UnavailableDate' => $date, 'Reason' => $reason ?: null])
        );
    }

    $pdo->commit();

    echo json_encode([
        'status' => 'success',
        'message' => 'Doctor marked unavailable from ' . date('F j, Y', strtotime($startDate)) . ' through ' . date('F j, Y', strtotime($endDate)) . '.',
        'csrf_token' => SessionManager::regenerateCsrfToken()
    ]);
} catch (PDOException $e) {
    if ($pdo->inTransaction()) {
        $pdo->rollBack();
    }
    $message = $e->getCode() === '23000'
        ? 'A date in this range is already marked unavailable. No dates were added.'
        : 'Unable to save this date range. Please try again.';
    error_log('add-unavailability failed: ' . $e->getMessage());
    echo json_encode(['status' => 'error', 'message' => $message]);
}