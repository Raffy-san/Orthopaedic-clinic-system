<?php
require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/../includes/auth.php';
require_once __DIR__ . '/../php/fetch/fetch.php';
SessionManager::requireAdmin();
SessionManager::requireLogin();
SessionManager::requireAnyRole(['admin', 'doctor']);

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
    <title>Reports</title>
</head>

<body class="h-screen flex bg-slate-200">
    <?php include_once '../includes/sidebar.php'; ?>

    <section class="flex-1 p-6 overflow-auto">
        <div class="mb-8 space-y-1">
            <h1 class="text-2xl font-bold text-gray-800">Report Generation</h1>
            <p class="text-sm font-medium text-gray-500">Generate and export administrative reports</p>
        </div>

        <div class="grid grid-cols-3 gap-6">
            <!-- Left Column: Report Selection and Filters -->
            <div class="space-y-6 col-span-1">
                <!-- Report Type Selection -->
                <div class="bg-white rounded-lg p-4 shadow-sm">
                    <h2 class="text-base font-bold text-gray-800 mb-3">1. Select Report Type</h2>
                    <div class="space-y-2">
                        <!-- Patient Records Report -->
                        <button
                            class="w-full text-left px-3 py-2 rounded-lg border-2 border-blue-500 bg-blue-50 hover:bg-blue-100 transition">
                            <div class="flex items-center gap-2">
                                <i class="fa-solid fa-file-user text-blue-600 text-sm"></i>
                                <span class="font-medium text-blue-700 text-sm">Patient Records Report</span>
                            </div>
                        </button>

                        <!-- Financial Summary -->
                        <button
                            class="w-full text-left px-3 py-2 rounded-lg border border-gray-200 hover:bg-gray-50 transition">
                            <div class="flex items-center gap-2">
                                <i class="fa-solid fa-chart-pie text-orange-500 text-sm"></i>
                                <span class="font-medium text-gray-700 text-sm">Financial Summary</span>
                            </div>
                        </button>

                        <!-- Appointments Report -->
                        <button
                            class="w-full text-left px-3 py-2 rounded-lg border border-gray-200 hover:bg-gray-50 transition">
                            <div class="flex items-center gap-2">
                                <i class="fa-solid fa-calendar text-blue-500 text-sm"></i>
                                <span class="font-medium text-gray-700 text-sm">Appointments Report</span>
                            </div>
                        </button>

                        <!-- Consultation Log -->
                        <button
                            class="w-full text-left px-3 py-2 rounded-lg border border-gray-200 hover:bg-gray-50 transition">
                            <div class="flex items-center gap-2">
                                <i class="fa-solid fa-stethoscope text-purple-500 text-sm"></i>
                                <span class="font-medium text-gray-700 text-sm">Consultation Log</span>
                            </div>
                        </button>
                    </div>
                </div>

                <!-- Filter Criteria -->
                <div class="bg-white rounded-lg p-4 shadow-sm col-span-1">
                    <h2 class="text-base font-bold text-gray-800 mb-3">2. Filter Criteria</h2>
                    <div class="space-y-3">
                        <!-- From Date -->
                        <div>
                            <label class="block text-xs font-medium text-gray-700 mb-1">From Date</label>
                            <input type="date" value="01/07/2026"
                                class="w-full px-3 py-1 text-sm border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500">
                        </div>

                        <!-- To Date -->
                        <div>
                            <label class="block text-xs font-medium text-gray-700 mb-1">To Date</label>
                            <input type="date" value="25/07/2026"
                                class="w-full px-3 py-1 text-sm border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500">
                        </div>

                        <!-- Generate Report Button -->
                        <button
                            class="w-full mt-2 px-4 py-2 bg-blue-600 text-white font-semibold text-sm rounded-lg hover:bg-blue-700 transition">
                            Generate Report
                        </button>
                    </div>
                </div>
            </div>

            <!-- Right Column: Preview Area -->
            <div
                class="bg-white rounded-lg p-6 shadow-sm flex flex-col items-center justify-center min-h-96 col-span-2">
                <div class="text-center">
                    <div class="mb-4">
                        <i class="fa-solid fa-chart-bar text-gray-300 text-6xl"></i>
                    </div>
                    <p class="text-gray-500 text-sm">Select a report type and date range,</p>
                    <p class="text-gray-500 text-sm">then click Generate Report</p>
                </div>
            </div>
        </div>
    </section>
</body>

</html>