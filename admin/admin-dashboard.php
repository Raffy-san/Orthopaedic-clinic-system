<?php
include_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/../includes/auth.php';
require_once __DIR__ . '/../php/fetch/fetch.php';

SessionManager::requireLogin();
SessionManager::requireAnyRole(['admin', 'doctor', 'staff']);

$admin = SessionManager::getUser($pdo);

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

$patients = fetchAllData($pdo, "SELECT * FROM patients ORDER BY userID DESC LIMIT 5");
$appointments = fetchAllData($pdo, "SELECT * FROM appointments");
$pendingAppointments = fetchAllData($pdo, "SELECT * FROM appointments WHERE status = 'Pending'");
$completedAppointments = fetchAllData($pdo, "SELECT * FROM appointments WHERE status = 'Completed'");
$consultations = fetchAllData($pdo, "SELECT * FROM consultations");
$confirmedConsultations = fetchAllData($pdo, "SELECT * FROM appointments WHERE status = 'Confirmed'");
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


            <div class="grid grid-cols-2 gap-6 mt-8 mb-8">

                <!-- Completed -->
                <div class="bg-white rounded-2xl shadow-sm border border-gray-100">

                    <div class="px-6 py-5 border-b border-gray-100">
                        <h2 class="text-lg font-semibold text-gray-800">
                            Completed
                        </h2>
                    </div>

                    <div class="p-5 space-y-3 max-h-96 overflow-y-auto">
                        <?php
                        // NOTE: adjust column names (StartTime/EndTime) to match your actual `consultations` table
                        $completedToday = fetchAllData($pdo, "
                SELECT 
                    c.ConsultationID,
                    c.StartTime,
                    c.EndTime,
                    p.FirstName,
                    p.LastName
                FROM consultations c
                JOIN patients p ON p.PatientID = c.PatientID
                WHERE DATE(c.ConsultationDate) = CURDATE()
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
                <div class="bg-white rounded-2xl shadow-sm border border-gray-100">

                    <div class="px-6 py-5 border-b border-gray-100">
                        <h2 class="text-lg font-semibold text-gray-800">
                            Waiting Queue
                        </h2>
                    </div>

                    <div class="p-5 space-y-3 max-h-96 overflow-y-auto">
                        <?php
                        // Confirmed appointments today that haven't been completed yet
                        $waitingQueue = fetchAllData($pdo, "
                SELECT 
                    a.AppointmentID,
                    a.AppointmentTime,
                    a.Meridiem,
                    p.FirstName,
                    p.LastName
                FROM appointments a
                JOIN patients p ON p.PatientID = a.PatientID
                WHERE a.Status = 'Confirmed' AND DATE(a.AppointmentDate) = CURDATE()
                ORDER BY a.AppointmentTime ASC
            ");

                        if (empty($waitingQueue)) {
                            echo '<p class="text-sm text-gray-500">No patients waiting.</p>';
                        } else {
                            foreach ($waitingQueue as $i => $w) {
                                $patientName = trim($w['FirstName'] . ' ' . $w['LastName']);
                                $time = date('g:i', strtotime($w['AppointmentTime']));
                                $isFirst = ($i === 0);
                                ?>
                                <div
                                    class="flex items-center justify-between rounded-xl border <?= $isFirst ? 'border-emerald-200 bg-emerald-50' : 'border-gray-100' ?> px-4 py-3">
                                    <div>
                                        <h3 class="font-semibold text-sm">
                                            <?= htmlspecialchars($patientName) ?>
                                        </h3>
                                        <p class="text-xs text-gray-500">
                                            <?= htmlspecialchars($time) ?>
                                            <?= htmlspecialchars($w['Meridiem']) ?>
                                        </p>
                                    </div>
                                    <span
                                        class="text-xs px-3 py-1 rounded-full font-semibold <?= $isFirst ? 'bg-emerald-100 text-emerald-700' : 'bg-gray-100 text-gray-500' ?>">
                                        <?= $isFirst ? 'In Progress' : 'Waiting' ?>
                                    </span>
                                </div>
                                <?php
                            }
                        }
                        ?>
                    </div>

                </div>

            </div>

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

        </div>

        </div>

    </section>
</body>

</html>