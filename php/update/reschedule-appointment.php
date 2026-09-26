<?php
require_once __DIR__ . '/../../config/config.php';
require_once __DIR__ . '/../../includes/auth.php';
require_once __DIR__ . '/../../includes/audit.php';

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

// Only an admin or doctor can reschedule (matches who sees the Reschedule button)
//if (!in_array(strtolower((string) SessionManager::getCurrentRole()), ['admin', 'doctor'], true)) {
//    http_response_code(403);
//    echo json_encode(['status' => 'error', 'message' => 'Only an admin or doctor can reschedule appointments.']);
//    exit;
//}

$appointmentId = intval($requestData['appointment_id'] ?? 0);
$newDate = trim($requestData['new_appointment_date'] ?? '');
$newTime = trim($requestData['new_appointment_time'] ?? ''); // e.g. "08:00 AM"

if (!$appointmentId || $newDate === '' || $newTime === '') {
    http_response_code(400);
    echo json_encode(['status' => 'error', 'message' => 'Missing appointment ID, date, or time.']);
    exit;
}

// Convert the 12-hour "hh:mm AM/PM" slot label into a proper 24-hour TIME value.
// (book-appointment.php's own GET query derives AM/PM from this stored value alone,
// via TIME_FORMAT(...,'%h:%i %p') — it does NOT look at the meridiem column — so this
// must be stored as real 24-hour time, e.g. "02:00 PM" -> "14:00:00", or PM slots will
// silently fail to show as taken.)
$parsedTime = DateTime::createFromFormat('h:i A', $newTime);
if (!$parsedTime) {
    http_response_code(400);
    echo json_encode(['status' => 'error', 'message' => 'Invalid time format.']);
    exit;
}
$timeOnly = $parsedTime->format('H:i:s');
$meridiem = $parsedTime->format('A');

try {
    $appointmentStatement = $pdo->prepare(
        "SELECT AppointmentDate, AppointmentTime, meridiem, Status
         FROM appointments
         WHERE AppointmentID = :appointment_id
         LIMIT 1"
    );
    $appointmentStatement->execute([':appointment_id' => $appointmentId]);
    $current = $appointmentStatement->fetch(PDO::FETCH_ASSOC);

    if (!$current) {
        http_response_code(404);
        echo json_encode(['status' => 'error', 'message' => 'Appointment not found']);
        exit;
    }

    if ($current['Status'] !== 'Confirmed') {
        http_response_code(400);
        echo json_encode(['status' => 'error', 'message' => 'Only confirmed appointments can be rescheduled.']);
        exit;
    }

    // Conflict check: is the new slot already taken by a DIFFERENT appointment?
    $conflictStatement = $pdo->prepare(
        "SELECT AppointmentID FROM appointments
         WHERE AppointmentDate = :new_date AND AppointmentTime = :new_time AND meridiem = :meridiem
           AND Status <> 'Cancelled' AND AppointmentID <> :appointment_id
         LIMIT 1"
    );
    $conflictStatement->execute([
        ':new_date' => $newDate,
        ':new_time' => $timeOnly,
        ':meridiem' => $meridiem,
        ':appointment_id' => $appointmentId
    ]);

    if ($conflictStatement->fetchColumn()) {
        http_response_code(409);
        echo json_encode(['status' => 'error', 'message' => 'That time slot is already taken. Please choose another.']);
        exit;
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

    if ($stmt->rowCount() > 0) {
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

        // Regenerate CSRF token
        SessionManager::regenerateCsrfToken();

        echo json_encode([
            'status' => 'success',
            'message' => 'Appointment rescheduled successfully',
            'csrf_token' => $_SESSION['csrf_token']
        ]);
    } else {
        http_response_code(404);
        echo json_encode(['status' => 'error', 'message' => 'Appointment not found']);
    }
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(['status' => 'error', 'message' => 'Database error: ' . $e->getMessage()]);
}