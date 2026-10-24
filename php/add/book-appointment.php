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

        $now = new DateTimeImmutable();
        $today = $now->setTime(0, 0);
        if (
            $today->format('Y-m') === $month
            && $now >= $today->setTime(12, 0)
            && !isset($closedDates[$today->format('Y-m-d')])
        ) {
            $closedDates[$today->format('Y-m-d')] = [
                'type' => 'closed',
                'reason' => 'Doctor consultations end at 12:00 PM. Same-day appointments are no longer available.'
            ];
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

    $dayOfWeek = (int) $parsedDate->format('N');
    $scheduleStmt = $pdo->prepare('SELECT IsOpen FROM clinic_schedule WHERE DayOfWeek = ?');
    $scheduleStmt->execute([$dayOfWeek]);
    $daySchedule = $scheduleStmt->fetch(PDO::FETCH_ASSOC);

    $unavailabilityStmt = $pdo->prepare('SELECT Reason FROM doctor_unavailability WHERE UnavailableDate = ?');
    $unavailabilityStmt->execute([$date]);
    $unavailability = $unavailabilityStmt->fetch(PDO::FETCH_ASSOC);

    $bookedCount = countActiveAppointments($pdo, $date);
    $now = new DateTimeImmutable();
    $today = $now->setTime(0, 0);
    if ($date === $today->format('Y-m-d') && $now >= $today->setTime(12, 0)) {
        echo json_encode([
            'status' => 'success',
            'limit' => DAILY_PATIENT_LIMIT,
            'booked_count' => $bookedCount,
            'closed' => true,
            'closed_message' => 'Doctor consultations end at 12:00 PM. Same-day appointments are no longer available.'
        ]);
        exit;
    }

    if (!$daySchedule || !$daySchedule['IsOpen'] || $unavailability) {
        $reason = $unavailability['Reason'] ?? null;
        echo json_encode([
            'status' => 'success',
            'limit' => DAILY_PATIENT_LIMIT,
            'booked_count' => $bookedCount,
            'closed' => true,
            'closed_message' => $reason
                ? "The doctor is unavailable on this date ({$reason})."
                : 'The clinic is closed on this date.'
        ]);
        exit;
    }

    echo json_encode([
        'status' => 'success',
        'limit' => DAILY_PATIENT_LIMIT,
        'booked_count' => $bookedCount,
        'full' => $bookedCount >= DAILY_PATIENT_LIMIT,
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
    'purpose' => trim($_POST['purpose'] ?? 'General consultation'),
    'chiefComplaint' => trim($_POST['chief_complaint'] ?? ''),
];

$userId = $_SESSION['user_id'] ?? $_SESSION['userId'] ?? $_SESSION['user']['UserID'] ?? null;
if ($data['patientCode'] === '' || $data['appointmentDate'] === '' || $userId === null) {
    bookingError('Please provide a valid patient and appointment date.');
}

// Strict date validation (a malformed date must not skip the checks below)
$submittedDate = DateTime::createFromFormat('!Y-m-d', $data['appointmentDate']);
$dateErrors = DateTime::getLastErrors();
if (
    !$submittedDate
    || ($dateErrors !== false && ($dateErrors['warning_count'] > 0 || $dateErrors['error_count'] > 0))
    || $submittedDate->format('Y-m-d') !== $data['appointmentDate']
) {
    bookingError('Please choose a valid appointment date.');
}
if ($submittedDate < new DateTime('today')) {
    bookingError('Cannot book an appointment in the past.');
}
$now = new DateTimeImmutable();
$today = $now->setTime(0, 0);
if (
    $submittedDate->format('Y-m-d') === $today->format('Y-m-d')
    && $now >= $today->setTime(12, 0)
) {
    bookingError('Doctor consultations end at 12:00 PM. Same-day appointments are no longer available.');
}

// Re-validate availability server-side (the GET check above is only a UI aid)
$dayOfWeek = (int) $submittedDate->format('N');

$scheduleStmt = $pdo->prepare('SELECT IsOpen FROM clinic_schedule WHERE DayOfWeek = ?');
$scheduleStmt->execute([$dayOfWeek]);
$daySchedule = $scheduleStmt->fetch(PDO::FETCH_ASSOC);

$unavailabilityStmt = $pdo->prepare('SELECT Reason FROM doctor_unavailability WHERE UnavailableDate = ?');
$unavailabilityStmt->execute([$data['appointmentDate']]);
$unavailability = $unavailabilityStmt->fetch(PDO::FETCH_ASSOC);

if (!$daySchedule || !$daySchedule['IsOpen'] || $unavailability) {
    bookingError('The clinic/doctor is unavailable on the selected date.');
}

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
    $result = bookAppointment($pdo, $data, (int) $userId);
} catch (Throwable $e) {
    error_log('book-appointment failed: ' . $e->getMessage());
    $result = ['status' => 'error', 'message' => 'Unable to book the appointment. Please try again.'];
}

$result['csrf_token'] = SessionManager::regenerateCsrfToken();
echo json_encode($result);