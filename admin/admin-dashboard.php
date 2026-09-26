<?php
include_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/../includes/auth.php';
require_once __DIR__ . '/../includes/crud.php';
require_once __DIR__ . '/../php/fetch/fetch.php';
require_once __DIR__ . '/../includes/functions.php';

SessionManager::requireLogin();
SessionManager::requireAnyRole(['admin', 'doctor', 'staff']);
$csrfToken = $_SESSION['csrf_token'] ?? SessionManager::regenerateCsrfToken();
$currentRole = strtolower((string) (SessionManager::getCurrentRole() ?? ''));
$canApprove = in_array($currentRole, ['admin', 'doctor'], true);
$admin = SessionManager::getUser($pdo);
expirePendingAppointments($pdo);

if (!$admin) {
    SessionManager::logout('../index.php');
}

$totalStmt = $pdo->query("SELECT COUNT(*) FROM patients");
$totalPatients = (int) $totalStmt->fetchColumn();

$yesterdayStmt = $pdo->prepare("
    SELECT COUNT(*) FROM patients 
    WHERE CreatedAt < CURDATE()
");
$yesterdayStmt->execute();
$totalAsOfYesterday = (int) $yesterdayStmt->fetchColumn();
$diff = $totalPatients - $totalAsOfYesterday;

if ($diff > 0) {
    $changeText = "+{$diff} from yesterday";
    $changeColor = "text-green-600";
} elseif ($diff < 0) {
    $changeText = "{$diff} from yesterday";
    $changeColor = "text-red-600";
} else {
    $changeText = "No change from yesterday";
    $changeColor = "text-gray-500";
}

$totalConsultStmt = $pdo->query("SELECT COUNT(*) FROM consultations");
$totalConsultations = (int) $totalConsultStmt->fetchColumn();

$consultYesterdayStmt = $pdo->prepare("
    SELECT COUNT(*) FROM consultations 
    WHERE ConsultationDate < CURDATE()
");
$consultYesterdayStmt->execute();
$totalConsultAsOfYesterday = (int) $consultYesterdayStmt->fetchColumn();

$consultDiff = $totalConsultations - $totalConsultAsOfYesterday;


if ($consultDiff > 0) {
    $consultChangeText = "+{$consultDiff} from yesterday";
    $consultChangeColor = "text-green-600";
} elseif ($consultDiff < 0) {
    $consultChangeText = "{$consultDiff} from yesterday";
    $consultChangeColor = "text-red-600";
} else {
    $consultChangeText = "No change from yesterday";
    $consultChangeColor = "text-gray-500";
}

$pendingappointments = fetchAllData($pdo, "
    SELECT a.AppointmentID, a.AppointmentDate, a.AppointmentTime, a.Purpose, a.Status,
           p.PatientCode, p.FirstName AS patient_first_name, p.LastName AS patient_last_name
    FROM appointments a
    INNER JOIN patients p ON a.PatientID = p.PatientID
    ORDER BY a.AppointmentDate DESC, a.AppointmentTime DESC
");

$patients = fetchAllData($pdo, "SELECT * FROM patients ORDER BY userID DESC LIMIT 5");
$appointments = fetchAllData($pdo, "SELECT * FROM appointments");
$pendingAppointments = fetchAllData($pdo, "SELECT * FROM appointments WHERE status = 'Pending'");
$completedAppointments = fetchAllData($pdo, "SELECT * FROM appointments WHERE status = 'Completed'");
$consultations = fetchAllData($pdo, "SELECT * FROM consultations");
$confirmedConsultations = fetchAllData($pdo, "SELECT * FROM appointments WHERE status = 'Confirmed'");

// --- Today's Schedule (Requirement #8) ---
// NOTE: assumes AppointmentTime is a proper TIME column so ORDER BY sorts correctly.
// If AppointmentTime is stored as plain 12-hour text without a sortable format,
// let me know and I'll adjust the ORDER BY to combine it with the meridiem column.
$todaySchedule = fetchAllData($pdo, "
    SELECT
        a.AppointmentID,
        a.AppointmentTime,
        a.meridiem,
        a.Purpose,
        a.Status,
        p.FirstName,
        p.LastName,
        CASE WHEN c.ConsultationID IS NOT NULL AND c.IsCompleted = 0 THEN 1 ELSE 0 END AS InProgress
    FROM appointments a
    JOIN patients p ON p.PatientID = a.PatientID
    LEFT JOIN consultations c ON c.AppointmentID = a.AppointmentID
    WHERE DATE(a.AppointmentDate) = CURDATE() AND a.Status <> 'Cancelled'
    ORDER BY a.AppointmentTime ASC
");
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="../assets/css/output.css">
    <link rel="stylesheet" href="../assets/css/global.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
    <link rel="icon" href="../assets/img/rounded-logo.ico" type="image/x-icon">
    <title>Dashboard</title>
</head>

<body class="h-screen flex bg-slate-200 overflow-hidden">
    <?php include_once '../includes/sidebar.php'; ?>
    <section class="flex-1 min-h-0 flex flex-col overflow-hidden">
        <div
            class="flex items-center justify-between bg-gradient-to-r from-[#0b1f0b] via-[#1e6b34] to-[#2e8b47] px-6 py-4">
            <h1 class="text-2xl font-bold text-white">Southern Leyte Orthopaedic Clinic System</h1>
            <div class="flex items-center gap-4">
                <h1 class="text-lg font-semibold text-white">The College of Maasin</h1>
                <img src="../assets/img/cmlogo.png" alt="College Logo" class="w-16 h-16 object-contain">
            </div>
        </div>

        <div class="flex-1 min-h-0 overflow-auto p-6">
            <div class="flex w-full gap-4">
                <div class="bg-white p-6 rounded-2xl shadow-md flex-1">
                    <div class="flex justify-between items-center mb-4">
                        <h2 class="text-gray-500 text-md font-semibold">Patients Today</h2>
                        <div class="w-12 h-12 rounded-2xl bg-purple-100 flex items-center justify-center">
                            <i class="fas fa-user-injured text-purple-600"></i>
                        </div>
                    </div>
                    <p class="text-gray-800 text-3xl font-extrabold"><?= count($patients) ?></p>
                    <p class="<?= $changeColor ?> text-sm font-medium"><?= htmlspecialchars($changeText) ?></p>
                </div>
                <div class="bg-white p-6 rounded-2xl shadow-md flex-1">
                    <div class="flex justify-between items-center mb-4">
                        <h2 class="text-gray-500 text-md font-semibold">Appointments </h2>
                        <div class="w-12 h-12 rounded-2xl bg-cyan-100 flex items-center justify-center">
                            <i class="fas fa-calendar text-cyan-600"></i>
                        </div>
                    </div>
                    <p class="text-gray-800 text-3xl font-extrabold"><?php echo count($appointments); ?></p>
                    <p class="text-gray-500 text-sm font-medium"><?php echo count($pendingAppointments); ?> pending ·
                        <?php echo count($completedAppointments); ?> completed
                    </p>
                </div>
                <div class="bg-white p-6 rounded-2xl shadow-md flex-1">
                    <div class="flex justify-between items-center mb-4">
                        <h2 class="text-gray-500 text-md font-semibold">Consultations</h2>
                        <div class="w-12 h-12 rounded-2xl bg-orange-100 flex items-center justify-center">
                            <i class="fas fa-stethoscope text-orange-500"></i>
                        </div>
                    </div>
                    <p class="text-gray-800 text-3xl font-extrabold"><?php echo count($consultations); ?></p>
                    <p class="<?= $consultChangeColor ?> text-sm font-medium">
                        <?php echo htmlspecialchars($consultChangeText); ?>
                    </p>
                    </p>
                </div>
            </div>

            <div class="rounded-3xl bg-white p-6 shadow-sm border border-slate-100 mt-8">
                <div class="flex items-center justify-between mb-6">
                    <h2 class="text-lg font-semibold text-slate-900">Pending Requests</h2>
                    <span
                        class="inline-flex items-center justify-center rounded-full bg-amber-100 px-3 py-1 text-xs font-bold text-amber-700">
                        <?php
                        $pendingCount = count(array_filter($appointments, fn($a) => $a['Status'] === 'Pending'));
                        echo $pendingCount;
                        ?>
                    </span>
                </div>

                <div class="space-y-3">
                    <?php
                    $pendingAppointments = array_filter($pendingappointments, fn($a) => $a['Status'] === 'Pending');

                    if ($pendingAppointments) {
                        foreach ($pendingAppointments as $appointment) {
                            $appointmentDate = new DateTime($appointment['AppointmentDate']);
                            $formattedDate = $appointmentDate->format('M d');
                            $formattedTime = (new DateTime($appointment['AppointmentTime']))->format('g:i A');
                            ?>

                            <div class="rounded-lg bg-amber-50 p-4 border border-amber-100">
                                <div class="flex items-start justify-between">
                                    <div class="flex-1">
                                        <p class="text-sm font-semibold text-slate-900">
                                            <?= htmlspecialchars($appointment['patient_first_name'], ENT_QUOTES, 'UTF-8') ?>
                                            <?= htmlspecialchars($appointment['patient_last_name'], ENT_QUOTES, 'UTF-8') ?>
                                            <span class="text-slate-400 font-normal">
                                                · <?= htmlspecialchars($appointment['PatientCode'], ENT_QUOTES, 'UTF-8') ?>
                                            </span>
                                        </p>
                                        <p class="text-xs text-slate-500 mt-1">
                                            <?= $formattedDate ?> at <?= $formattedTime ?> ·
                                            <?= htmlspecialchars($appointment['Purpose'], ENT_QUOTES, 'UTF-8') ?>
                                        </p>
                                    </div>

                                    <div class="flex gap-2 ml-4 items-center">
                                        <?php if ($canApprove): ?>
                                            <button
                                                class="confirm-btn inline-flex items-center justify-center rounded-full bg-emerald-600 px-3 py-2 text-xs font-semibold text-white hover:bg-emerald-700 transition"
                                                data-appointment-id="<?= htmlspecialchars($appointment['AppointmentID'], ENT_QUOTES, 'UTF-8') ?>"
                                                title="Confirm">
                                                ✓ Confirm
                                            </button>
                                            <button
                                                class="decline-btn inline-flex items-center justify-center rounded-full bg-red-500 px-3 py-2 text-xs font-semibold text-white hover:bg-red-700 transition"
                                                data-appointment-id="<?= htmlspecialchars($appointment['AppointmentID'], ENT_QUOTES, 'UTF-8') ?>"
                                                title="Decline">
                                                ✕ Decline
                                            </button>
                                        <?php else: ?>
                                            <span
                                                class="text-xs font-semibold text-slate-500 bg-slate-100 px-3 py-2 rounded-full whitespace-nowrap">
                                                Admin/Doctor Approval Required
                                            </span>
                                        <?php endif; ?>
                                    </div>
                                </div>
                            </div>
                            <?php
                        }
                    } else {
                        echo '<p class="text-sm text-slate-500">No pending requests.</p>';
                    }
                    ?>
                </div>
            </div>

            <div class="grid grid-cols-3 gap-6 mt-8 mb-8">
                <!-- Completed -->
                <div class="bg-white rounded-2xl shadow-sm border border-gray-100 col-span-1">

                    <div class="px-6 py-5 border-b border-gray-100">
                        <h2 class="text-lg font-semibold text-gray-800">
                            Completed
                        </h2>
                    </div>

                    <div class="p-5 space-y-3 max-h-96 overflow-y-auto">
                        <?php
                        // NOTE: adjust column names (StartTime/EndTime) to match your actual `consultations` table
                        $completedToday = fetchAllData($pdo, "
                                SELECT c.ConsultationID, c.StartTime, c.EndTime, p.FirstName, p.LastName
                                FROM consultations c
                                JOIN patients p ON p.PatientID = c.PatientID
                                WHERE c.IsCompleted = 1 AND DATE(c.ConsultationDate) = CURDATE()
                                ORDER BY c.EndTime DESC
                            ");

                        if (empty($completedToday)) {
                            echo '<p class="text-sm text-gray-500">No completed consultations yet today.</p>';
                        } else {
                            foreach ($completedToday as $c) {
                                $patientName = trim($c['FirstName'] . ' ' . $c['LastName']);
                                $start = date('g:i A', strtotime($c['StartTime']));
                                $end = date('g:i A', strtotime($c['EndTime']));
                                ?>
                                <div class="flex items-center justify-between rounded-xl border border-gray-100 px-4 py-3">
                                    <div>
                                        <h3 class="font-semibold text-sm"><?= htmlspecialchars($patientName) ?></h3>
                                        <p class="text-xs text-gray-500"><?= $start ?> – <?= $end ?></p>
                                    </div>
                                    <span class="text-xs px-3 py-1 rounded-full font-semibold bg-gray-100 text-gray-500">
                                        Completed
                                    </span>
                                </div>
                                <?php
                            }
                        }
                        ?>
                    </div>
                </div>

                <!-- Waiting Queue -->
                <div class="bg-white rounded-2xl shadow-sm border border-gray-100 col-span-2">

                    <div class="px-6 py-5 border-b border-gray-100">
                        <h2 class="text-lg font-semibold text-gray-800">
                            Waiting Queue
                        </h2>
                    </div>

                    <div class="overflow-x-auto max-h-96 overflow-y-auto">
                        <table class="w-full">
                            <thead class="text-left text-sm text-gray-500 bg-emerald-50 sticky top-0">
                                <tr class="border-b border-gray-100">
                                    <th class="px-6 py-4 font-medium">Name</th>
                                    <th class="px-6 py-4 font-medium">Waiting Time</th>
                                    <th class="px-6 py-4 font-medium">Minutes (with Doctor)</th>
                                    <th class="px-6 py-4 font-medium">Status</th>
                                </tr>
                            </thead>

                            <tbody class="text-sm">
                                <?php
                                $inProgress = fetchAllData($pdo, "
                                        SELECT c.ConsultationID, c.StartTime, c.Meridiem, p.FirstName, p.LastName
                                        FROM consultations c
                                        JOIN patients p ON p.PatientID = c.PatientID
                                        WHERE c.IsCompleted = 0 AND c.StartTime IS NOT NULL AND DATE(c.ConsultationDate) = CURDATE()
                                        ORDER BY c.StartTime ASC
                                    ");

                                $stillWaiting = fetchAllData($pdo, "
                                        SELECT a.AppointmentID, a.AppointmentTime, a.meridiem, p.FirstName, p.LastName
                                        FROM appointments a
                                        JOIN patients p ON p.PatientID = a.PatientID
                                        WHERE a.Status = 'Confirmed' AND DATE(a.AppointmentDate) = CURDATE()
                                        AND a.AppointmentID NOT IN (
                                            SELECT AppointmentID FROM consultations WHERE IsCompleted = 0
                                        )
                                        ORDER BY a.AppointmentTime ASC
                                    ");

                                if (empty($inProgress) && empty($stillWaiting)) {
                                    echo '<tr><td colspan="4" class="px-6 py-6 text-center text-gray-500">No patients in the queue.</td></tr>';
                                } else {
                                    // In-progress patients first
                                    foreach ($inProgress as $w) {
                                        $patientName = trim($w['FirstName'] . ' ' . $w['LastName']);
                                        $startDisplay = date('g:i', strtotime($w['StartTime']));
                                        $minutesWithDoctor = elapsedTime(date('Y-m-d') . ' ' . $w['StartTime']);
                                        ?>
                                        <tr class="border-b border-gray-100 hover:bg-gray-50">
                                            <td class="px-6 py-4">
                                                <h3 class="font-medium"><?= htmlspecialchars($patientName) ?></h3>
                                                <p class="text-xs text-gray-500">
                                                    <?= htmlspecialchars($startDisplay) ?>
                                                    <?= strtoupper(htmlspecialchars($w['Meridiem'])) ?>
                                                </p>
                                            </td>
                                            <td class="px-6 py-4 text-gray-400">-</td>
                                            <td class="px-6 py-4 font-semibold text-emerald-600">
                                                <?= htmlspecialchars($minutesWithDoctor) ?>
                                            </td>
                                            <td class="px-6 py-4">
                                                <span
                                                    class="text-xs px-3 py-1 rounded-full font-semibold bg-emerald-100 text-emerald-700">
                                                    In Progress
                                                </span>
                                            </td>
                                        </tr>
                                        <?php
                                    }

                                    // Then everyone still waiting
                                    foreach ($stillWaiting as $w) {
                                        $patientName = trim($w['FirstName'] . ' ' . $w['LastName']);
                                        $appointmentDisplay = date('g:i', strtotime($w['AppointmentTime']));
                                        $waitingTime = elapsedTime(date('Y-m-d') . ' ' . $w['AppointmentTime']);
                                        ?>
                                        <tr class="border-b border-gray-100 hover:bg-gray-50">
                                            <td class="px-6 py-4">
                                                <h3 class="font-medium"><?= htmlspecialchars($patientName) ?></h3>
                                                <p class="text-xs text-gray-500">
                                                    <?= htmlspecialchars($appointmentDisplay) ?>
                                                    <?= strtoupper(htmlspecialchars($w['meridiem'])) ?>
                                                </p>
                                            </td>
                                            <td class="px-6 py-4">
                                                <span
                                                    class="text-xs px-2 py-1 rounded-full font-semibold bg-blue-100 text-blue-600">
                                                    <i class="fas fa-clock mr-1"></i><?= htmlspecialchars($waitingTime) ?>
                                                </span>
                                            </td>
                                            <td class="px-6 py-4 text-gray-400">-</td>
                                            <td class="px-6 py-4">
                                                <span
                                                    class="text-xs px-3 py-1 rounded-full font-semibold bg-gray-100 text-gray-500">
                                                    Waiting
                                                </span>
                                            </td>
                                        </tr>
                                        <?php
                                    }
                                }
                                ?>
                            </tbody>
                        </table>
                    </div>

                </div>
            </div>

            <!-- Recent Patients + Today's Schedule -->
            <div class="grid grid-cols-3 gap-6 mb-8">

                <!-- Recent Patients -->
                <div class="col-span-2 bg-white rounded-2xl shadow-sm border border-gray-100">
                    <div class="flex items-center justify-between px-6 py-5 border-b border-gray-100">
                        <h2 class="text-lg font-semibold text-gray-800">
                            Recent Patients
                        </h2>
                    </div>

                    <div class="overflow-x-auto">
                        <table class="w-full">
                            <thead class="text-left text-sm text-gray-500">
                                <tr class="border-b border-gray-100">
                                    <th class="px-6 py-4 font-medium">Patient ID</th>
                                    <th class="px-6 py-4 font-medium">Name</th>
                                    <th class="px-6 py-4 font-medium">Date</th>
                                </tr>
                            </thead>

                            <tbody class="text-sm">
                                <?php
                                $patients = fetchAllData($pdo, "SELECT 
                                    p.PatientID AS PatientID, 
                                    p.PatientCode,
                                    p.FirstName, 
                                    p.MiddleName,
                                    p.LastName,
                                    p.CreatedAt
                                FROM patients p
                                GROUP BY p.PatientID ORDER BY p.CreatedAt DESC LIMIT 5
                                ");

                                foreach ($patients as $patient) {
                                    echo ' <tr class="border-b border-gray-100 hover:bg-gray-50">';
                                    echo '<td class="px-6 py-4 text-gray-500">'
                                        . htmlspecialchars($patient['PatientCode']) .
                                        '</td>';
                                    echo '<td class="px-6 py-4 font-medium">'
                                        . htmlspecialchars($patient['FirstName'] . ' ' . $patient['MiddleName'] . ' ' . $patient['LastName']) .
                                        '</td>';
                                    echo '<td class="px-6 py-4 text-gray-500">'
                                        . htmlspecialchars(date('M d, Y', strtotime($patient['CreatedAt']))) .
                                        '</td>';
                                }
                                ?>
                            </tbody>
                        </table>
                    </div>
                </div>

                <!-- Today's Schedule (Requirement #8) -->
                <div class="col-span-1 bg-white rounded-2xl shadow-sm border border-gray-100">
                    <div class="px-6 py-5 border-b border-gray-100">
                        <h2 class="text-lg font-semibold text-gray-800">Today's Schedule</h2>
                    </div>

                    <div class="p-5 space-y-4 max-h-96 overflow-y-auto">
                        <?php
                        if (empty($todaySchedule)) {
                            echo '<p class="text-sm text-gray-500">No appointments scheduled for today.</p>';
                        } else {
                            foreach ($todaySchedule as $slot) {
                                $patientName = trim($slot['FirstName'] . ' ' . $slot['LastName']);
                                $timeDisplay = date('g:i', strtotime($slot['AppointmentTime']));
                                $meridiem = strtoupper($slot['meridiem'] ?? '');
                                $rowClasses = $slot['InProgress']
                                    ? 'bg-blue-50 border border-blue-100'
                                    : 'border border-transparent';
                                ?>
                                <div class="flex items-start gap-4 rounded-xl px-3 py-2 <?= $rowClasses ?>">
                                    <div class="text-xs font-semibold text-gray-400 w-14 pt-0.5">
                                        <?= htmlspecialchars($timeDisplay) ?><br>
                                        <span class="font-normal"><?= htmlspecialchars($meridiem) ?></span>
                                    </div>
                                    <div>
                                        <p class="text-sm font-semibold <?= $slot['InProgress'] ? 'text-blue-900' : 'text-gray-800' ?>">
                                            <?= htmlspecialchars($patientName) ?>
                                        </p>
                                        <p class="text-xs <?= $slot['InProgress'] ? 'text-blue-700' : 'text-gray-500' ?>">
                                            <?= htmlspecialchars($slot['Purpose']) ?>
                                        </p>
                                    </div>
                                </div>
                                <?php
                            }
                        }
                        ?>
                    </div>
                </div>

            </div>

        </div>

    </section>
    <script>window.csrfToken = <?= json_encode($csrfToken, JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_AMP | JSON_HEX_QUOT) ?>;</script>
    <script src="../assets/javascript/appointment.js"></script>
</body>

</html>