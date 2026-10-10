<?php
require_once __DIR__ . '/../../config/config.php';
require_once __DIR__ . '/../../includes/auth.php';
require_once __DIR__ . '/../../includes/audit.php';

SessionManager::requireLogin();
SessionManager::requireAnyRole(['staff']); // matches who sees the Reschedule button; add 'admin', 'doctor' if they need it

header('Content-Type: application/json');

// Verify CSRF token
$csrfToken = $_SESSION['csrf_token'] ?? '';
$requestData = json_decode(file_get_contents('php://input'), true);

if (
    empty($requestData['csrf_token'])
    || $csrfToken === ''
    || !hash_equals($csrfToken, (string) $requestData['csrf_token'])
) {
    http_response_code(403);
    echo json_encode(['status' => 'error', 'message' => 'Invalid CSRF token']);
    exit;
}

$appointmentId = intval($requestData['appointment_id'] ?? 0);
$newDate = trim($requestData['new_appointment_date'] ?? '');
$newTime = trim($requestData['new_appointment_time'] ?? ''); // e.g. "09:00 AM"

if (!$appointmentId || $newDate === '' || $newTime === '') {
    http_response_code(400);
    echo json_encode(['status' => 'error', 'message' => 'Missing appointment ID, date, or time.']);
    exit;
}

$parsedDate = DateTime::createFromFormat('!Y-m-d', $newDate);
$dateErrors = DateTime::getLastErrors();
if (
    !$parsedDate
    || ($dateErrors !== false && ($dateErrors['warning_count'] > 0 || $dateErrors['error_count'] > 0))
    || $parsedDate->format('Y-m-d') !== $newDate
) {
    http_response_code(400);
    echo json_encode(['status' => 'error', 'message' => 'Please choose a valid appointment date.']);
    exit;
}

if ($parsedDate < new DateTime('today')) {
    http_response_code(400);
    echo json_encode(['status' => 'error', 'message' => 'Cannot reschedule an appointment to a past date.']);
    exit;
}

// "09:00 AM" -> real 24-hour TIME ("09:00:00"), which is what the slot picker reads.
$parsedTime = DateTime::createFromFormat('h:i A', $newTime);
if (!$parsedTime) {
    http_response_code(400);
    echo json_encode(['status' => 'error', 'message' => 'Invalid time format.']);
    exit;
}
$timeOnly = $parsedTime->format('H:i:s');
$meridiem = $parsedTime->format('A');

// Rolls back, sends an error and stops
$fail = function (int $code, string $message) use ($pdo): void {
    if ($pdo->inTransaction()) {
        $pdo->rollBack();
    }
    http_response_code($code);
    echo json_encode(['status' => 'error', 'message' => $message]);
    exit;
};

try {
    $dayOfWeek = (int) $parsedDate->format('N');
    $scheduleStatement = $pdo->prepare('SELECT IsOpen, StartTime, EndTime FROM clinic_schedule WHERE DayOfWeek = ?');
    $scheduleStatement->execute([$dayOfWeek]);
    $daySchedule = $scheduleStatement->fetch(PDO::FETCH_ASSOC);

    $unavailabilityStatement = $pdo->prepare('SELECT Reason FROM doctor_unavailability WHERE UnavailableDate = ?');
    $unavailabilityStatement->execute([$newDate]);
    $unavailability = $unavailabilityStatement->fetch(PDO::FETCH_ASSOC);

    if (!$daySchedule || !$daySchedule['IsOpen'] || $unavailability) {
        $fail(400, 'The clinic/doctor is unavailable on the selected date.');
    }

    if ($timeOnly < $daySchedule['StartTime'] || $timeOnly >= $daySchedule['EndTime']) {
        $fail(400, 'The selected time is outside the clinic hours for this date.');
    }

    $pdo->beginTransaction();

    // Lock the appointment being moved
    $appointmentStatement = $pdo->prepare(
        "SELECT AppointmentDate, AppointmentTime, meridiem, Status, Purpose
         FROM appointments
         WHERE AppointmentID = :appointment_id
         LIMIT 1
         FOR UPDATE"
    );
    $appointmentStatement->execute([':appointment_id' => $appointmentId]);
    $current = $appointmentStatement->fetch(PDO::FETCH_ASSOC);

    if (!$current) {
        $fail(404, 'Appointment not found');
    }

    if ($current['Status'] !== 'Confirmed') {
        $fail(400, 'Only confirmed appointments can be rescheduled.');
    }

    if ($current['AppointmentDate'] === $newDate && $current['AppointmentTime'] === $timeOnly) {
        $fail(400, 'The appointment is already scheduled for that date and time.');
    }

    // Is the new slot taken by a DIFFERENT appointment? Locked so two requests can't both pass.
    // (meridiem is not part of the check: the 24-hour time already decides AM/PM,
    // and older rows may have a NULL meridiem.)
    $conflictStatement = $pdo->prepare(
        "SELECT AppointmentID FROM appointments
         WHERE AppointmentDate = :new_date AND AppointmentTime = :new_time
           AND Status NOT IN ('Cancelled', 'Rescheduled')
           AND AppointmentID <> :appointment_id
         LIMIT 1
         FOR UPDATE"
    );
    $conflictStatement->execute([
        ':new_date' => $newDate,
        ':new_time' => $timeOnly,
        ':appointment_id' => $appointmentId
    ]);

    if ($conflictStatement->fetchColumn()) {
        $fail(409, 'That time slot is already taken. Please choose another.');
    }

    $stmt = $pdo->prepare("
        UPDATE appointments
        SET AppointmentDate = :new_date, AppointmentTime = :new_time, meridiem = :meridiem
        WHERE AppointmentID = :appointment_id
    ");
    $stmt->execute([
        ':new_date' => $newDate,
        ':new_time' => $timeOnly,
        ':meridiem' => $meridiem,
        ':appointment_id' => $appointmentId
    ]);

    // If this appointment is a follow-up visit, keep its followups record in sync.
    // Limited to follow-up appointments so older followups rows (which point at the
    // ORIGINAL appointment) are not changed by mistake.
    if ($current['Purpose'] === 'Follow-up check-up') {
        $followupStmt = $pdo->prepare("
            UPDATE followups
            SET FollowUpDate = :new_date
            WHERE AppointmentID = :appointment_id AND Status = 'Scheduled'
        ");
        $followupStmt->execute([
            ':new_date' => $newDate,
            ':appointment_id' => $appointmentId
        ]);
    }

    // AUDIT: log the date/time change
    $actorUser = SessionManager::getUser($pdo);
    $actorUserId = $actorUser['UserID'] ?? null;

    $oldDateTime = $current['AppointmentDate'] . ' ' . $current['AppointmentTime'] . ' ' . $current['meridiem'];
    $newDateTime = $newDate . ' ' . $timeOnly . ' ' . $meridiem;

    logAudit(
        $pdo,
        $actorUserId ? (int) $actorUserId : null,
        'UPDATE',
        'appointments',
        $appointmentId,
        'AppointmentDate/AppointmentTime',
        $oldDateTime,
        $newDateTime
    );

    $pdo->commit();

    SessionManager::regenerateCsrfToken();

    echo json_encode([
        'status' => 'success',
        'message' => 'Appointment rescheduled successfully',
        'csrf_token' => $_SESSION['csrf_token']
    ]);
} catch (Exception $e) {
    if ($pdo->inTransaction()) {
        $pdo->rollBack();
    }
    error_log('reschedule-appointment failed: ' . $e->getMessage());
    http_response_code(500);
    echo json_encode(['status' => 'error', 'message' => 'Unable to reschedule the appointment. Please try again.']);
}