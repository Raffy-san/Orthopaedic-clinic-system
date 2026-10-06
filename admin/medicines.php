<?php
// Put in: pages/medicines.php
require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/../includes/auth.php';
require_once __DIR__ . '/../php/fetch/fetch.php';
SessionManager::requireLogin();
SessionManager::requireAnyRole(['admin', 'doctor', 'staff']); // add 'staff' if they should manage medicines too

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
    <title>Medicine List</title>
</head>

<body class="h-screen flex bg-slate-200 overflow-hidden">
    <?php include_once '../includes/sidebar.php'; ?>
    <section class="flex-1 min-h-0 flex flex-col overflow-hidden">
        <div
            class="flex items-center justify-between bg-gradient-to-r from-[#0b1f0b] via-[#1e6b34] to-[#2e8b47] px-6 py-4">
            <h1 class="text-2xl font-bold text-white">Southern Leyte Orthopaedic Clinic System</h1>
            <div class="flex flex-col gap-3 items-end">
                <h1 class="text-2xl font-bold text-white">Medicine List</h1>
                <h3 class="text-sm font-medium text-white">Manage the medicines available for prescriptions</h3>
            </div>
        </div>

        <div class="grid grid-cols-12 gap-6 min-h-0 overflow-auto p-6 space-y-4">

            <!-- Add / Edit form -->
            <div class="col-span-12 md:col-span-4">
                <div class="bg-white rounded-3xl shadow-sm p-6 border border-slate-100">
                    <h2 id="formTitle" class="text-lg font-bold text-slate-700 mb-4">Add Medicine</h2>

                    <form id="medicineForm" class="space-y-3">
                        <input type="hidden" id="medicineId">

                        <div>
                            <label class="text-xs font-medium text-slate-500 mb-1 block">Medicine Name *</label>
                            <input type="text" id="mName" required
                                class="w-full rounded-lg border border-slate-300 px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-green-500">
                        </div>

                        <div>
                            <label class="text-xs font-medium text-slate-500 mb-1 block">Dosage</label>
                            <input type="text" id="mDosage" placeholder="e.g. 400mg"
                                class="w-full rounded-lg border border-slate-300 px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-green-500">
                        </div>

                        <p id="formMessage" class="text-xs hidden"></p>

                        <div class="flex gap-2 pt-1">
                            <button type="submit" id="saveBtn"
                                class="flex-1 bg-green-700 hover:bg-green-800 text-white text-sm font-semibold rounded-lg px-3 py-2 transition">
                                Add Medicine
                            </button>
                            <button type="button" id="cancelEditBtn"
                                class="hidden flex-1 bg-slate-200 hover:bg-slate-300 text-slate-700 text-sm font-semibold rounded-lg px-3 py-2 transition">
                                Cancel
                            </button>
                        </div>
                    </form>
                </div>
            </div>

            <!-- Medicine list -->
            <div class="col-span-12 md:col-span-8">
                <div class="bg-white rounded-3xl shadow-sm p-6 border border-slate-100">
                    <div class="mb-3">
                        <input type="search" id="medicineSearchInput"
                            class="w-full rounded-lg border border-slate-300 px-4 py-2 focus:outline-none focus:ring-2 focus:ring-green-500 focus:border-transparent"
                            placeholder="Search medicine">
                    </div>

                    <table class="w-full text-sm">
                        <thead>
                            <tr class="text-left text-slate-400 text-xs">
                                <th class="pb-2 font-medium">Medicine</th>
                                <th class="pb-2 font-medium">Dosage</th>
                                <th class="pb-2 font-medium">Status</th>
                                <th class="pb-2 font-medium"></th>
                            </tr>
                        </thead>
                        <tbody id="medicineTableBody"></tbody>
                    </table>
                    <p id="medicineEmpty" class="text-sm text-slate-500 text-center py-6 hidden">No medicines found</p>
                </div>
            </div>
        </div>
    </section>

    <script>
        window.csrfToken = <?= json_encode($csrfToken, JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_AMP | JSON_HEX_QUOT) ?>;
    </script>
    <script src="../assets/javascript/medicines.js"></script>
</body>

</html>