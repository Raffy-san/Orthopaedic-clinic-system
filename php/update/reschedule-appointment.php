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

if (!$appointmentId || $newDate === '') {
    http_response_code(400);
    echo json_encode(['status' => 'error', 'message' => 'Missing appointment ID or date.']);
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

    $scheduleStatement = $pdo->prepare('SELECT IsOpen FROM clinic_schedule WHERE DayOfWeek = ?');
    $scheduleStatement->execute([$dayOfWeek]);
    $daySchedule = $scheduleStatement->fetch(PDO::FETCH_ASSOC);

    $unavailabilityStatement = $pdo->prepare('SELECT Reason FROM doctor_unavailability WHERE UnavailableDate = ?');
    $unavailabilityStatement->execute([$newDate]);
    $unavailability = $unavailabilityStatement->fetch(PDO::FETCH_ASSOC);

    if (!$daySchedule || !$daySchedule['IsOpen'] || $unavailability) {
        $fail(400, 'The clinic/doctor is unavailable on the selected date.');
    }

    // Lock the appointment being moved
    $appointmentStatement = $pdo->prepare(
        "SELECT a.PatientID, a.AppointmentDate, a.Status, a.Purpose,
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

    if ($current['AppointmentDate'] === $newDate) {
        $fail(400, 'The appointment is already scheduled for that date.');
    }

    if (countActiveAppointments($pdo, $newDate, $appointmentId) >= DAILY_PATIENT_LIMIT) {
        $fail(400, 'This date is already fully booked (' . DAILY_PATIENT_LIMIT . ' patients). Please choose another date.');
    }

    $stmt = $pdo->prepare("
        UPDATE appointments
        SET AppointmentDate = :new_date, AppointmentTime = NULL, meridiem = NULL
        WHERE AppointmentID = :appointment_id
    ");
    $stmt->execute([
        ':new_date' => $newDate,
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

    $oldDateTime = $current['AppointmentDate'];
    $newDateTime = $newDate;

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