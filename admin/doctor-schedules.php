<?php
require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/../includes/auth.php';
require_once __DIR__ . '/../php/fetch/fetch.php';

SessionManager::requireLogin();
SessionManager::requireAnyRole(['admin', 'doctor']);

$admin = SessionManager::getUser($pdo);
if (!$admin) {
    SessionManager::logout('../index.php');
}

$csrfToken = $_SESSION['csrf_token'] ?? SessionManager::regenerateCsrfToken();

$unavailableDates = fetchAllData(
    $pdo,
    'SELECT UnavailabilityID, UnavailableDate, Reason, CreatedAt
     FROM doctor_unavailability
     WHERE UnavailableDate >= CURDATE()
     ORDER BY UnavailableDate ASC'
);

$weeklySchedule = fetchAllData(
    $pdo,
    'SELECT DayOfWeek, IsOpen, StartTime, EndTime FROM clinic_schedule ORDER BY DayOfWeek ASC'
);
$dayNames = [1 => 'Monday', 2 => 'Tuesday', 3 => 'Wednesday', 4 => 'Thursday', 5 => 'Friday', 6 => 'Saturday', 7 => 'Sunday'];
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
    <title>Doctor Availability</title>
</head>

<body class="h-screen flex bg-slate-200 overflow-hidden">
    <?php include_once '../includes/sidebar.php'; ?>
    <section class="flex-1 min-h-0 flex flex-col overflow-hidden">
        <div
            class="flex items-center justify-between bg-gradient-to-r from-[#0b1f0b] via-[#1e6b34] to-[#2e8b47] px-6 py-4">
            <h1 class="text-2xl font-bold text-white">Southern Leyte Orthopaedic Clinic System</h1>
            <div class="flex flex-col gap-3 items-end">
                <h1 class="text-2xl font-bold text-white">Doctor Availability</h1>
                <h3 class="text-sm font-medium text-white">Manage clinic hours and leave dates</h3>
            </div>
        </div>

        <div class="grid grid-cols-12 gap-6 min-h-0 overflow-auto p-6 space-y-4">

            <!-- Weekly schedule (read-only reference) -->
            <div class="col-span-12 lg:col-span-5">
                <div class="bg-white rounded-3xl shadow-sm p-6 border border-slate-100">
                    <h2 class="text-lg font-semibold text-slate-900 mb-1">Weekly Clinic Hours</h2>
                    <p class="text-sm text-slate-500 mb-4">Reference only — edit directly in <code>clinic_schedule</code> for now.</p>

                    <div class="space-y-2">
                        <?php foreach ($weeklySchedule as $day): ?>
                            <div class="flex items-center justify-between rounded-lg border border-slate-200 px-4 py-2 text-sm">
                                <span class="font-medium text-slate-700"><?= htmlspecialchars($dayNames[$day['DayOfWeek']] ?? '') ?></span>
                                <?php if ($day['IsOpen']): ?>
                                    <span class="text-emerald-700 font-semibold">
                                        <?= htmlspecialchars(date('g:i A', strtotime($day['StartTime']))) ?> –
                                        <?= htmlspecialchars(date('g:i A', strtotime($day['EndTime']))) ?>
                                    </span>
                                <?php else: ?>
                                    <span class="text-slate-400 font-semibold">Closed</span>
                                <?php endif; ?>
                            </div>
                        <?php endforeach; ?>
                    </div>
                </div>
            </div>

            <!-- Unavailability management -->
            <div class="col-span-12 lg:col-span-7 space-y-6">

                <!-- Add new unavailable date -->
                <div class="bg-white rounded-3xl shadow-sm p-6 border border-slate-100">
                    <h2 class="text-lg font-semibold text-slate-900 mb-4">Mark a Date Unavailable</h2>
                    <form id="addUnavailabilityForm" class="flex flex-col sm:flex-row gap-3 items-end">
                        <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($csrfToken, ENT_QUOTES, 'UTF-8') ?>">
                        <div class="flex-1 w-full">
                            <label class="block text-sm text-slate-700 mb-1">Date</label>
                            <input type="date" name="unavailable_date" required
                                class="w-full border border-gray-300 bg-white rounded-lg px-4 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-emerald-500">
                        </div>
                        <div class="flex-1 w-full">
                            <label class="block text-sm text-slate-700 mb-1">Reason (optional)</label>
                            <input type="text" name="reason" placeholder="e.g. Medical conference"
                                class="w-full border border-gray-300 bg-white rounded-lg px-4 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-emerald-500">
                        </div>
                        <button type="submit"
                            class="w-full sm:w-auto px-5 py-2 bg-emerald-600 text-white text-sm font-semibold rounded-lg hover:bg-emerald-700">
                            Add
                        </button>
                    </form>
                    <p id="addUnavailabilityError" class="text-xs text-red-600 mt-2 hidden"></p>
                </div>

                <!-- List of upcoming unavailable dates -->
                <div class="bg-white rounded-3xl shadow-sm p-6 border border-slate-100">
                    <h2 class="text-lg font-semibold text-slate-900 mb-4">Upcoming Unavailable Dates</h2>

                    <div id="unavailabilityList" class="space-y-2">
                        <?php if (empty($unavailableDates)): ?>
                            <p class="text-sm text-slate-500">No upcoming unavailable dates.</p>
                        <?php else: ?>
                            <?php foreach ($unavailableDates as $row): ?>
                                <div class="flex items-center justify-between rounded-lg border border-slate-200 px-4 py-3"
                                    data-row-id="<?= (int) $row['UnavailabilityID'] ?>">
                                    <div>
                                        <p class="text-sm font-semibold text-slate-900">
                                            <?= htmlspecialchars(date('F j, Y (l)', strtotime($row['UnavailableDate']))) ?>
                                        </p>
                                        <?php if (!empty($row['Reason'])): ?>
                                            <p class="text-xs text-slate-500"><?= htmlspecialchars($row['Reason']) ?></p>
                                        <?php endif; ?>
                                    </div>
                                    <button type="button" class="delete-unavailability-btn text-xs font-semibold text-red-600 hover:text-red-700"
                                        data-id="<?= (int) $row['UnavailabilityID'] ?>">
                                        <i class="fa-solid fa-trash"></i> Remove
                                    </button>
                                </div>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </div>
                </div>

            </div>
        </div>
    </section>

    <?php include '../includes/message-modal.php' ?>

    <script>window.csrfToken = <?= json_encode($csrfToken, JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_AMP | JSON_HEX_QUOT) ?>;</script>
    <script src="../assets/javascript/appointment.js"></script>
    <script src="../assets/javascript/doctor-availability.js"></script>
</body>

</html>