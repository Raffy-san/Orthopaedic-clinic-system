<?php
require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/../includes/auth.php';
require_once __DIR__ . '/../includes/fetch.php';
SessionManager::requireLogin();
SessionManager::requireAnyRole(['admin', 'doctor', 'staff']);

$admin = SessionManager::getUser($pdo);

if (!$admin) {
    SessionManager::logout('../index.php');
}

$csrfToken = $_SESSION['csrf_token'] ?? SessionManager::regenerateCsrfToken();
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
    <title>Follow-up Check-ups</title>
</head>

<body class="h-screen flex bg-slate-200">
    <?php include_once '../includes/sidebar.php'; ?>

    <section class="flex-1 p-6 overflow-auto">
        <div class="mb-8 space-y-1">
            <h1 class="text-2xl font-bold text-gray-800">Follow-up Check-ups</h1>
            <p class="text-sm font-medium text-gray-500">Manage and track patient follow-up appointments</p>
        </div>

        <!-- Appointments List -->
        <div class="space-y-4">
            <!-- Appointment Card 1 -->
            <div class="bg-white rounded-lg p-6 shadow-sm hover:shadow-md transition-shadow">
                <div class="flex justify-between items-start mb-4">
                    <div>
                        <h3 class="text-lg font-bold text-gray-800">Roberto Lim</h3>
                        <p class="text-sm text-gray-500">Dr. Reyes • Aug 1, 2026</p>
                    </div>
                    <span
                        class="inline-block px-3 py-1 text-xs font-semibold text-teal-700 bg-teal-100 rounded-full">Confirmed</span>
                </div>
                <p class="text-sm text-gray-600 mb-4">Post-op knee check</p>
                <div class="flex gap-3">
                    <button
                        class="px-4 py-2 text-sm font-medium text-purple-600 border border-purple-300 rounded-lg hover:bg-purple-50 transition">
                        <i class="fa-solid fa-bell mr-2"></i>Notify Patient
                    </button>
                    <button
                        class="px-4 py-2 text-sm font-medium text-white bg-blue-600 rounded-lg hover:bg-blue-700 transition">
                        Confirm & Update DB
                    </button>
                </div>
            </div>

            <!-- Appointment Card 2 -->
            <div class="bg-white rounded-lg p-6 shadow-sm hover:shadow-md transition-shadow">
                <div class="flex justify-between items-start mb-4">
                    <div>
                        <h3 class="text-lg font-bold text-gray-800">Lourdes Tan</h3>
                        <p class="text-sm text-gray-500">Dr. Villareuva • Aug 3, 2026</p>
                    </div>
                    <span
                        class="inline-block px-3 py-1 text-xs font-semibold text-blue-700 bg-blue-100 rounded-full">Rescheduled</span>
                </div>
                <p class="text-sm text-gray-600 mb-4">Lumbar therapy progress</p>

                <!-- Alert Box -->
                <div class="bg-yellow-50 border border-yellow-200 rounded-lg p-3 mb-4 flex items-start gap-2">
                    <i class="fa-solid fa-triangle-exclamation text-yellow-600 text-sm mt-0.5"></i>
                    <div>
                        <p class="text-xs text-yellow-700 font-medium">Doctor unavailable on original date.</p>
                        <p class="text-xs text-yellow-600">Alternative dates: Aug 4 • Aug 6 • Aug 7</p>
                    </div>
                </div>

                <div class="flex gap-3">
                    <button
                        class="px-4 py-2 text-sm font-medium text-purple-600 border border-purple-300 rounded-lg hover:bg-purple-50 transition">
                        <i class="fa-solid fa-bell mr-2"></i>Notify Patient
                    </button>
                    <button
                        class="px-4 py-2 text-sm font-medium text-white bg-blue-600 rounded-lg hover:bg-blue-700 transition">
                        Confirm & Update DB
                    </button>
                </div>
            </div>

            <!-- Appointment Card 3 -->
            <div class="bg-white rounded-lg p-6 shadow-sm hover:shadow-md transition-shadow">
                <div class="flex justify-between items-start mb-4">
                    <div>
                        <h3 class="text-lg font-bold text-gray-800">Ana Macaraeg</h3>
                        <p class="text-sm text-gray-500">Dr. Fuentes • Aug 5, 2026</p>
                    </div>
                    <span
                        class="inline-block px-3 py-1 text-xs font-semibold text-orange-700 bg-orange-100 rounded-full">Pending</span>
                </div>
                <p class="text-sm text-gray-600 mb-4">Fracture healing review</p>
                <div class="flex gap-3">
                    <button
                        class="px-4 py-2 text-sm font-medium text-purple-600 border border-purple-300 rounded-lg hover:bg-purple-50 transition">
                        <i class="fa-solid fa-bell mr-2"></i>Notify Patient
                    </button>
                    <button
                        class="px-4 py-2 text-sm font-medium text-white bg-blue-600 rounded-lg hover:bg-blue-700 transition">
                        Confirm & Update DB
                    </button>
                </div>
            </div>
        </div>
    </section>

</body>

</html>