<?php
include_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/../includes/auth.php';
require_once __DIR__ . '/../includes/fetch.php';

SessionManager::requireLogin();
SessionManager::requireAnyRole(['admin', 'doctor']);

$admin = SessionManager::getUser($pdo);

if (!$admin) {
    SessionManager::logout('../index.php');
}

$today = date('l, F j, Y');
$displayName = trim(($admin['first_name'] ?? '') . ' ' . ($admin['last_name'] ?? ''));
$displayName = $displayName !== '' ? $displayName : ($admin['username'] ?? 'User');

$patients = fetchAllData($pdo, "SELECT * FROM patients ORDER BY userID DESC LIMIT 5");
$appointments = fetchAllData($pdo, "SELECT * FROM appointments");
$pendingAppointments = fetchAllData($pdo, "SELECT * FROM appointments WHERE status = 'Pending'");
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

<body class="h-screen flex bg-slate-200">
    <?php include_once '../includes/sidebar.php'; ?>
    <section class="flex-1 p-6 overflow-auto">
        <div>
            <h1 class="text-2xl font-bold">Dashboard</h1>
            <h3 class="text-md font-medium text-gray-500">
                <?= htmlspecialchars($today) ?> - Welcome, <?= htmlspecialchars($displayName) ?>
            </h3>
        </div>
        <div class="flex w-full gap-4 mt-6">
            <div class="bg-white p-6 rounded-lg shadow-md flex-1">
                <div class="flex justify-between items-center mb-4">
                    <h2 class="text-gray-500 text-md font-semibold">Patients Today</h2>
                    <div class="w-12 h-12 rounded-2xl bg-purple-100 flex items-center justify-center">
                        <i class="fas fa-user-injured text-purple-600"></i>
                    </div>
                </div>
                <p class="text-gray-800 text-3xl font-extrabold"><?php echo count($patients); ?></p>
                <p class="text-gray-500 text-sm font-medium">+3 from yesterday</p>
            </div>
            <div class="bg-white p-6 rounded-lg shadow-md flex-1">
                <div class="flex justify-between items-center mb-4">
                    <h2 class="text-gray-500 text-md font-semibold">Appointments </h2>
                    <div class="w-12 h-12 rounded-2xl bg-cyan-100 flex items-center justify-center">
                        <i class="fas fa-calendar text-cyan-600"></i>
                    </div>
                </div>
                <p class="text-gray-800 text-3xl font-extrabold"><?php echo count($appointments); ?></p>
                <p class="text-gray-500 text-sm font-medium"><?php echo count($pendingAppointments); ?> pending · <?php echo count($appointments) - count($pendingAppointments); ?> completed</p>
            </div>
            <div class="bg-white p-6 rounded-lg shadow-md flex-1">
                <div class="flex justify-between items-center mb-4">
                    <h2 class="text-gray-500 text-md font-semibold">Consultations</h2>
                    <div class="w-12 h-12 rounded-2xl bg-orange-100 flex items-center justify-center">
                        <i class="fas fa-stethoscope text-orange-500"></i>
                    </div>
                </div>
                <p class="text-gray-800 text-3xl font-extrabold">11</p>
                <p class="text-gray-500 text-sm font-medium">5 in progress</p>
            </div>
            <div class="bg-white p-6 rounded-lg shadow-md flex-1">
                <div class="flex justify-between items-center mb-4">
                    <h2 class="text-gray-500 text-md font-semibold">Revenue Today</h2>
                    <div class="w-12 h-12 rounded-2xl bg-green-100 flex items-center justify-center">
                        <i class="fas fa-money-bill text-green-600"></i>
                    </div>
                </div>
                <p class="text-gray-800 text-3xl font-extrabold">₱12,345</p>
                <p class="text-gray-500 text-sm font-medium">vs ₱20,000 yesterday</p>
            </div>
        </div>
        <div class="grid grid-cols-3 gap-6 mt-8">

            <!-- Recent Patients -->
            <div class="col-span-2 bg-white rounded-2xl shadow-sm border border-gray-100">
                <div class="flex items-center justify-between px-6 py-5 border-b border-gray-100">
                    <h2 class="text-lg font-semibold text-gray-800">
                        Recent Patients
                    </h2>

                    <button class="text-sm text-blue-500 hover:text-blue-600 font-medium">
                        Today & Yesterday
                    </button>
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
                            GROUP BY p.PatientID ORDER BY p.CreatedAt DESC
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

            <!-- Today's Schedule -->
            <div class="bg-white rounded-2xl shadow-sm border border-gray-100">

                <div class="px-6 py-5 border-b border-gray-100">
                    <h2 class="text-lg font-semibold text-gray-800">
                        Today's Schedule
                    </h2>
                </div>

                <div class="p-5 space-y-4">

                    <div class="flex items-start gap-4">
                        <div class="bg-blue-100 text-blue-600 rounded-xl px-3 py-2 text-sm font-semibold">
                            9:00
                        </div>

                        <div>
                            <h3 class="font-semibold">
                                Maria Santos
                            </h3>

                            <p class="text-sm text-gray-500">
                                Consultation
                            </p>
                        </div>
                    </div>

                    <div class="flex items-start gap-4">
                        <div class="bg-green-100 text-green-600 rounded-xl px-3 py-2 text-sm font-semibold">
                            10:30
                        </div>

                        <div>
                            <h3 class="font-semibold">
                                Juan Dela Cruz
                            </h3>

                            <p class="text-sm text-gray-500">
                                Follow-up
                            </p>
                        </div>
                    </div>

                    <div class="flex items-start gap-4">
                        <div class="bg-yellow-100 text-yellow-700 rounded-xl px-3 py-2 text-sm font-semibold">
                            1:00
                        </div>

                        <div>
                            <h3 class="font-semibold">
                                Roberto Lim
                            </h3>

                            <p class="text-sm text-gray-500">
                                X-Ray Review
                            </p>
                        </div>
                    </div>

                    <div class="flex items-start gap-4">
                        <div class="bg-purple-100 text-purple-600 rounded-xl px-3 py-2 text-sm font-semibold">
                            3:00
                        </div>

                        <div>
                            <h3 class="font-semibold">
                                Ana Macaraeg
                            </h3>

                            <p class="text-sm text-gray-500">
                                Billing
                            </p>
                        </div>
                    </div>

                </div>

            </div>

        </div>

    </section>
</body>

</html>