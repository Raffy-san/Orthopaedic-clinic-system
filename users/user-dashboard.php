<?php
include_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/../includes/auth.php';
require_once __DIR__ . '/../php/fetch/fetch.php';
SessionManager::requireLogin();

SessionManager::requireRole('patient');

$patient = SessionManager::getUser($pdo);

if (!$patient) {
    SessionManager::logout('../index.php'); // Force logout if user not found
}



?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="../assets/css/output.css">
    <link rel="stylesheet" href="../assets/css/global.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
    <title>Dashboard</title>
</head>

<body class="h-screen flex bg-slate-200">
    <?php include '../includes/user-sidebar.php'; ?>
    <section class="flex-1 p-6 overflow-auto">
        <div>
            <h1 class="text-2xl font-bold">My Dashboard</h1>
            <h3 class="text-md font-medium text-gray-500">
                Your health summary
            </h3>
        </div>

        <div class="flex w-full gap-4 mt-6">
            <div class="bg-white p-6 rounded-xl shadow-md flex-1">
                <div class="flex justify-between items-center mb-4">
                    <h2 class="text-gray-500 text-md font-semibold">Upcoming Appointments</h2>
                    <div class="w-12 h-12 rounded-2xl bg-purple-100 flex items-center justify-center">
                        <i class="fas fa-user-injured text-purple-600"></i>
                    </div>
                </div>
                <p class="text-gray-800 text-3xl font-extrabold">1</p>
                <p class="text-gray-500 text-sm font-medium">Next: August 1, 2026</p>
            </div>
            <div class="bg-white p-6 rounded-xl shadow-md flex-1">
                <div class="flex justify-between items-center mb-4">
                    <h2 class="text-gray-500 text-md font-semibold">Past Consultations</h2>
                    <div class="w-12 h-12 rounded-2xl bg-purple-100 flex items-center justify-center">
                        <i class="fas fa-user-injured text-purple-600"></i>
                    </div>
                </div>
                <p class="text-gray-800 text-3xl font-extrabold">2</p>
                <p class="text-gray-500 text-sm font-medium">Last: July 28, 2026</p>
            </div>
            <div class="bg-white p-6 rounded-xl shadow-md flex-1">
                <div class="flex justify-between items-center mb-4">
                    <h2 class="text-gray-500 text-md font-semibold">Follow-up Check-ups</h2>
                    <div class="w-12 h-12 rounded-2xl bg-purple-100 flex items-center justify-center">
                        <i class="fas fa-user-injured text-purple-600"></i>
                    </div>
                </div>
                <p class="text-gray-800 text-3xl font-extrabold">5</p>
                <p class="text-gray-500 text-sm font-medium">Scheduled: August 15, 2026</p>
            </div>
        </div>

        <div class="flex w-full gap-4 mt-6">
            <div class="bg-white p-6 rounded-xl shadow-md flex-1">
                <h1 class="font-semibold mb-4">Next Appointment</h1>
                <div class="bg-blue-100 p-4 rounded-xl flex flex-1 justify-between w-full items-center">
                    <div>
                        <h3 class="font-semibold text-md">Follow-up Check-up</h3>
                        <h5 class="text-gray-500 text-sm font-medium">August 15, 2026 - 10:00 AM</h5>
                        <div class="w-auto">
                            <h5
                                class="rounded-xl p-1 px-2 mt-3 inline-block text-green-500 bg-green-200 text-sm font-semibold">
                                Confirmed</h5>
                        </div>
                    </div>
                    <h1 class="text-5xl">📋</h1>
                </div>
            </div>
        </div>

        <div class="flex w-full gap-4 mt-6">
            <div class="bg-white p-6 rounded-xl shadow-md flex-1">
                <h1 class="font-semibold mb-4">Recent Visits</h1>
                <div class="divide-y divide-gray-200">
                    <div class="flex items-center justify-between py-3 first:pt-0 last:pb-0">
                        <div>
                            <h3 class="font-semibold text-md">Consultation</h3>
                            <p class="text-gray-500 text-sm">July 25, 2026 - 10:00 AM</p>
                        </div>
                        <span class="rounded-md px-2 py-1 text-green-600 bg-green-100 text-sm font-semibold">Completed</span>
                    </div>
                    <div class="flex items-center justify-between py-3 first:pt-0 last:pb-0">
                        <div>
                            <h3 class="font-semibold text-md">Follow-up</h3>
                            <p class="text-gray-500 text-sm">June 10, 2026 - 2:00 PM</p>
                        </div>
                        <span class="rounded-md px-2 py-1 text-green-600 bg-green-100 text-sm font-semibold">Completed</span>
                    </div>
                    <div class="flex items-center justify-between py-3 first:pt-0 last:pb-0">
                        <div>
                            <h3 class="font-semibold text-md">New Patient</h3>
                            <p class="text-gray-500 text-sm">May 3, 2026 - 10:00 AM</p>
                        </div>
                        <span class="rounded-md px-2 py-1 text-green-600 bg-green-100 text-sm font-semibold">Completed</span>
                    </div>
                </div>
            </div>
        </div>


    </section>
</body>

</html>