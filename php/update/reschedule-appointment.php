<?php
require_once __DIR__ . '/../../config/config.php';
require_once __DIR__ . '/../../includes/auth.php';
require_once __DIR__ . '/../../includes/crud.php';

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

$appointmentIdValue = trim((string) ($requestData['appointment_id'] ?? ''));
$appointmentId = ctype_digit($appointmentIdValue) ? (int) $appointmentIdValue : 0;
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

// Only allow the same half-hour slots offered by the booking calendar.
$parsedTime = DateTime::createFromFormat('!h:i A', $newTime);
$timeErrors = DateTime::getLastErrors();
if (
    !$parsedTime
    || ($timeErrors !== false && ($timeErrors['warning_count'] > 0 || $timeErrors['error_count'] > 0))
    || $parsedTime->format('h:i A') !== $newTime
    || !in_array($newTime, clinicSlotLabels(), true)
) {
    http_response_code(400);
    echo json_encode(['status' => 'error', 'message' => 'Please choose a valid appointment time.']);
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
    $pdo->beginTransaction();

    $dayOfWeek = (int) $parsedDate->format('N');
    $dayLock = $pdo->prepare('SELECT DayOfWeek FROM clinic_schedule WHERE DayOfWeek = ? FOR UPDATE');
    $dayLock->execute([$dayOfWeek]);

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

    // Lock the appointment being moved
    $appointmentStatement = $pdo->prepare(
        "SELECT a.PatientID, a.AppointmentDate, a.AppointmentTime, a.meridiem, a.Status, a.Purpose,
                p.PatientCode
         FROM appointments a
         INNER JOIN patients p ON p.PatientID = a.PatientID
         WHERE a.AppointmentID = :appointment_id
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

    if (countActiveAppointments($pdo, $newDate, $appointmentId) >= DAILY_PATIENT_LIMIT) {
        $fail(400, 'This date is already fully booked (' . DAILY_PATIENT_LIMIT . ' patients). Please choose another date.');
    }

    if (countSlotAppointments($pdo, $newDate, $timeOnly, $appointmentId) >= SLOT_CAPACITY) {
        $fail(400, 'That time slot is full. Please choose another.');
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

    $scheduledFollowUp = null;
    if ($current['Purpose'] === 'Follow-up check-up') {
        $followupLookup = $pdo->prepare("
            SELECT FollowUpID, FollowUpDate
            FROM followups
            WHERE AppointmentID = :appointment_id AND Status = 'Scheduled'
            LIMIT 1
            FOR UPDATE
        ");
        $followupLookup->execute([':appointment_id' => $appointmentId]);
        $scheduledFollowUp = $followupLookup->fetch(PDO::FETCH_ASSOC) ?: null;

        // Only update the follow-up tied to this appointment, not older records.
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

    $actorUser = SessionManager::getUser($pdo);
    $actorUserId = $actorUser['user_id'] ?? $actorUser['UserID'] ?? null;
    if ($actorUserId !== null && !ctype_digit((string) $actorUserId)) {
        $actorUserId = null;
    }
    $actorUserId = $actorUserId === null ? null : (int) $actorUserId;

    $oldDateTime = $current['AppointmentDate'] . ' at '
        . date('h:i A', strtotime($current['AppointmentTime']));
    $newDateTime = $newDate . ' at ' . $parsedTime->format('h:i A');

    // AUDIT: keep the old and new schedule visible in the audit log.
    logAudit(
        $pdo,
        $actorUserId,
        'UPDATE',
        'appointments',
        $appointmentId,
        'Rescheduled appointment (' . $current['PatientCode'] . ')',
        $oldDateTime,
        $newDateTime
    );

    if (
        $scheduledFollowUp
        && $scheduledFollowUp['FollowUpDate'] !== $newDate
    ) {
        logAudit(
            $pdo,
            $actorUserId,
            'UPDATE',
            'followups',
            (int) $scheduledFollowUp['FollowUpID'],
            'FollowUpDate',
            $scheduledFollowUp['FollowUpDate'],
            $newDate
        );
    }

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