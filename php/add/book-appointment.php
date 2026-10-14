<?php
require_once '../../config/config.php';
require_once '../../includes/auth.php';
require_once '../../includes/crud.php';

SessionManager::requireLogin();
SessionManager::requireAnyRole(['admin', 'doctor', 'staff']);
header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] === 'GET') {
    // ---- Calendar: closed dates for a month ----
    if (isset($_GET['month'])) {
        $month = $_GET['month'];
        $parsedMonth = DateTime::createFromFormat('!Y-m', $month);
        $monthErrors = DateTime::getLastErrors();
        if (
            !$parsedMonth
            || ($monthErrors !== false && ($monthErrors['warning_count'] > 0 || $monthErrors['error_count'] > 0))
            || $parsedMonth->format('Y-m') !== $month
        ) {
            http_response_code(400);
            echo json_encode(['status' => 'error', 'message' => 'Please choose a valid calendar month.']);
            exit;
        }

        $monthEnd = (clone $parsedMonth)->modify('+1 month');
        $scheduleStatement = $pdo->query('SELECT DayOfWeek, IsOpen FROM clinic_schedule');
        $scheduleByDay = [];
        foreach ($scheduleStatement->fetchAll(PDO::FETCH_ASSOC) as $schedule) {
            $scheduleByDay[(int) $schedule['DayOfWeek']] = (bool) $schedule['IsOpen'];
        }

        $unavailabilityStatement = $pdo->prepare(
            'SELECT UnavailableDate, Reason
             FROM doctor_unavailability
             WHERE UnavailableDate >= ? AND UnavailableDate < ?'
        );
        $unavailabilityStatement->execute([$parsedMonth->format('Y-m-d'), $monthEnd->format('Y-m-d')]);
        $unavailableByDate = [];
        foreach ($unavailabilityStatement->fetchAll(PDO::FETCH_ASSOC) as $unavailableDate) {
            $unavailableByDate[$unavailableDate['UnavailableDate']] = $unavailableDate['Reason'];
        }

        $closedDates = [];
        $period = new DatePeriod($parsedMonth, new DateInterval('P1D'), $monthEnd);
        foreach ($period as $day) {
            $date = $day->format('Y-m-d');
            $dayOfWeek = (int) $day->format('N');
            if (array_key_exists($date, $unavailableByDate)) {
                $closedDates[$date] = [
                    'type' => 'unavailable',
                    'reason' => $unavailableByDate[$date]
                ];
            } elseif (empty($scheduleByDay[$dayOfWeek])) {
                $closedDates[$date] = ['type' => 'closed', 'reason' => 'Clinic is closed on this day.'];
            }
        }

        echo json_encode(['status' => 'success', 'closed_dates' => $closedDates]);
        exit;
    }

    // ---- Availability for one date ----
    $date = $_GET['date'] ?? '';
    $parsedDate = DateTime::createFromFormat('!Y-m-d', $date);
    $dateErrors = DateTime::getLastErrors();
    if (!$parsedDate || ($dateErrors !== false && ($dateErrors['warning_count'] > 0 || $dateErrors['error_count'] > 0)) || $parsedDate->format('Y-m-d') !== $date) {
        http_response_code(400);
        echo json_encode(['status' => 'error', 'message' => 'Please choose a valid appointment date.']);
        exit;
    }

    $dayOfWeek = (int) $parsedDate->format('N'); // 1 = Monday ... 7 = Sunday
    $excludeAppointmentId = 0;
    if (isset($_GET['exclude_appointment_id'])) {
        if (!ctype_digit((string) $_GET['exclude_appointment_id']) || (int) $_GET['exclude_appointment_id'] < 1) {
            http_response_code(400);
            echo json_encode(['status' => 'error', 'message' => 'Invalid appointment ID.']);
            exit;
        }

        $excludeAppointmentId = (int) $_GET['exclude_appointment_id'];
        $rescheduleStatement = $pdo->prepare(
            "SELECT COUNT(*) FROM appointments
             WHERE AppointmentID = ? AND Status = 'Confirmed'"
        );
        $rescheduleStatement->execute([$excludeAppointmentId]);
        if ((int) $rescheduleStatement->fetchColumn() !== 1) {
            http_response_code(400);
            echo json_encode(['status' => 'error', 'message' => 'Only confirmed appointments can be rescheduled.']);
            exit;
        }
    }

    $scheduleStmt = $pdo->prepare('SELECT IsOpen, StartTime, EndTime FROM clinic_schedule WHERE DayOfWeek = ?');
    $scheduleStmt->execute([$dayOfWeek]);
    $daySchedule = $scheduleStmt->fetch(PDO::FETCH_ASSOC);

    $unavailabilityStmt = $pdo->prepare('SELECT Reason FROM doctor_unavailability WHERE UnavailableDate = ?');
    $unavailabilityStmt->execute([$date]);
    $unavailability = $unavailabilityStmt->fetch(PDO::FETCH_ASSOC);

    $availableTimes = [];
    if ($daySchedule && $daySchedule['IsOpen']) {
        foreach (clinicSlotLabels() as $slotLabel) {
            $slotTime = DateTime::createFromFormat('h:i A', $slotLabel)->format('H:i:s');
            if ($slotTime >= $daySchedule['StartTime'] && $slotTime < $daySchedule['EndTime']) {
                $availableTimes[] = $slotLabel;
            }
        }
    }

    $bookedCount = countActiveAppointments($pdo, $date, $excludeAppointmentId ?: null);

    if (!$daySchedule || !$daySchedule['IsOpen'] || $unavailability) {
        $reason = $unavailability['Reason'] ?? null;
        echo json_encode([
            'status' => 'success',
            'available_times' => $availableTimes,
            'slot_counts' => (object) [],
            'slot_capacity' => SLOT_CAPACITY,
            'limit' => DAILY_PATIENT_LIMIT,
            'booked_count' => $bookedCount,
            'closed' => true,
            'closed_message' => $reason
                ? "The doctor is unavailable on this date ({$reason})."
                : 'The clinic is closed on this date.'
        ]);
        exit;
    }

    // How many patients are already in each time slot, e.g. {"09:00 AM": 3}
    $slotSql = "SELECT TIME_FORMAT(AppointmentTime, '%h:%i %p') AS t, COUNT(*) AS c
        FROM appointments
        WHERE AppointmentDate = ? AND Status NOT IN ('Cancelled', 'Rescheduled')";
    $slotParams = [$date];
    if ($excludeAppointmentId > 0) {
        $slotSql .= ' AND AppointmentID <> ?';
        $slotParams[] = $excludeAppointmentId;
    }
    $slotSql .= ' GROUP BY AppointmentTime';
    $slotStmt = $pdo->prepare($slotSql);
    $slotStmt->execute($slotParams);
    $slotCounts = [];
    foreach ($slotStmt->fetchAll(PDO::FETCH_ASSOC) as $row) {
        $slotCounts[$row['t']] = (int) $row['c'];
    }

    echo json_encode([
        'status' => 'success',
        'available_times' => $availableTimes,
        'slot_counts' => (object) $slotCounts,
        'slot_capacity' => SLOT_CAPACITY,
        'limit' => DAILY_PATIENT_LIMIT,
        'booked_count' => $bookedCount,
        'closed' => false
    ]);
    exit;
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['status' => 'error', 'message' => 'Invalid request method.']);
    exit;
}

// Sends an error with a fresh CSRF token so the page can retry
function bookingError(string $message, int $httpCode = 200): void
{
    if ($httpCode !== 200) {
        http_response_code($httpCode);
    }
    echo json_encode([
        'status' => 'error',
        'message' => $message,
        'csrf_token' => SessionManager::regenerateCsrfToken()
    ]);
    exit;
}

$sessionToken = $_SESSION['csrf_token'] ?? '';
$submittedToken = $_POST['csrf_token'] ?? '';
if ($sessionToken === '' || $submittedToken === '' || !hash_equals($sessionToken, $submittedToken)) {
    bookingError('Your session token is invalid. Refresh the page and try again.', 403);
}

$data = [
    'patientCode' => trim($_POST['patient_id'] ?? ''),
    'appointmentDate' => $_POST['appointment_date'] ?? '',
    'appointmentTime' => $_POST['appointment_time'] ?? '',
    'purpose' => trim($_POST['purpose'] ?? 'General consultation'),
    'chiefComplaint' => trim($_POST['chief_complaint'] ?? ''),
];

$userId = $_SESSION['user_id'] ?? $_SESSION['userId'] ?? $_SESSION['user']['UserID'] ?? null;
$appointmentTime = DateTime::createFromFormat('h:i A', $data['appointmentTime']);
if ($data['patientCode'] === '' || $data['appointmentDate'] === '' || !$appointmentTime || $userId === null) {
    bookingError('Please provide a valid patient, date, and time.');
}

// Strict date validation (a malformed date must not skip the checks below)
$submittedDate = DateTime::createFromFormat('!Y-m-d', $data['appointmentDate']);
if (!$submittedDate || $submittedDate->format('Y-m-d') !== $data['appointmentDate']) {
    bookingError('Please choose a valid appointment date.');
}
if ($submittedDate < new DateTime('today')) {
    bookingError('Cannot book an appointment in the past.');
}

// Re-validate availability server-side (the GET check above is only a UI aid)
$dayOfWeek = (int) $submittedDate->format('N');

$scheduleStmt = $pdo->prepare('SELECT IsOpen, StartTime, EndTime FROM clinic_schedule WHERE DayOfWeek = ?');
$scheduleStmt->execute([$dayOfWeek]);
$daySchedule = $scheduleStmt->fetch(PDO::FETCH_ASSOC);

$unavailabilityStmt = $pdo->prepare('SELECT Reason FROM doctor_unavailability WHERE UnavailableDate = ?');
$unavailabilityStmt->execute([$data['appointmentDate']]);
$unavailability = $unavailabilityStmt->fetch(PDO::FETCH_ASSOC);

if (!$daySchedule || !$daySchedule['IsOpen'] || $unavailability) {
    bookingError('The clinic/doctor is unavailable on the selected date.');
}

$submittedTime24 = $appointmentTime->format('H:i:s');
if ($submittedTime24 < $daySchedule['StartTime'] || $submittedTime24 >= $daySchedule['EndTime']) {
    bookingError('The selected time is outside the doctor\'s working hours for this date.');
}

// NOTE: the old "slot already taken" check is gone. bookAppointment() now enforces
// both the 20-per-day cap and the per-slot capacity inside a transaction.

$patientStatement = $pdo->prepare('SELECT PatientID FROM patients WHERE PatientCode = ?');
$patientStatement->execute([$data['patientCode']]);
$patientId = $patientStatement->fetchColumn();
if (!$patientId) {
    bookingError('Patient code not found.');
}

try {
    $data['patientId'] = $patientId;
    $data['doctorId'] = getClinicDoctorId($pdo);   // the clinic's doctor
    $data['createdBy'] = $userId;                  // the staff member who booked
    $data['meridiem'] = $appointmentTime->format('A');
    $data['appointmentTime'] = $submittedTime24;

    $result = bookAppointment($pdo, $data, (int) $userId);
} catch (Throwable $e) {
    error_log('book-appointment failed: ' . $e->getMessage());
    $result = ['status' => 'error', 'message' => 'Unable to book the appointment. Please try again.'];
}

$result['csrf_token'] = SessionManager::regenerateCsrfToken();
echo json_encode($result);