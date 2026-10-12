<?php
require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/../includes/auth.php';

SessionManager::requireLogin();
SessionManager::requireAnyRole(['admin', 'doctor']);

$admin = SessionManager::getUser($pdo);
if (!$admin) {
    SessionManager::logout('../index.php');
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
    <link rel="icon" href="../assets/img/rounded-logo.ico" type="image/x-icon">
    <title>Audit Logs</title>
</head>

<body class="h-screen flex bg-slate-200 overflow-hidden">
    <?php include_once '../includes/sidebar.php'; ?>

    <section class="flex-1 min-h-0 flex flex-col overflow-hidden">
        <div
            class="flex items-center justify-between bg-gradient-to-r from-[#0b1f0b] via-[#1e6b34] to-[#2e8b47] px-6 py-4">
            <h1 class="text-2xl font-bold text-white">Southern Leyte Orthopaedic Clinic System</h1>
            <div class="flex flex-col gap-3 items-end">
                <h1 class="text-2xl font-bold text-white">Audit Logs</h1>
                <h3 class="text-sm font-medium text-white">Read-only record of changes made in the system</h3>
            </div>
        </div>

        <div class="flex-1 min-h-0 overflow-auto p-6 space-y-4">
            <!-- Filters -->
            <div class="rounded-3xl bg-white p-6 shadow-sm border border-slate-100">
                <div class="grid grid-cols-1 gap-4 md:grid-cols-3 xl:grid-cols-6">
                    <div>
                        <label for="filterUser" class="text-xs font-semibold text-slate-500">User</label>
                        <select id="filterUser"
                            class="mt-1 w-full border border-slate-300 rounded-lg px-3 py-2 text-sm bg-white">
                            <option value="">All users</option>
                        </select>
                    </div>
                    <div>
                        <label for="filterAction" class="text-xs font-semibold text-slate-500">Action</label>
                        <select id="filterAction"
                            class="mt-1 w-full border border-slate-300 rounded-lg px-3 py-2 text-sm bg-white">
                            <option value="">All actions</option>
                            <option value="CREATE">Create</option>
                            <option value="UPDATE">Update</option>
                            <option value="DELETE">Delete</option>
                        </select>
                    </div>
                    <div>
                        <label for="filterTable" class="text-xs font-semibold text-slate-500">Table</label>
                        <select id="filterTable"
                            class="mt-1 w-full border border-slate-300 rounded-lg px-3 py-2 text-sm bg-white">
                            <option value="">All tables</option>
                        </select>
                    </div>
                    <div>
                        <label for="filterDateFrom" class="text-xs font-semibold text-slate-500">From</label>
                        <input type="date" id="filterDateFrom"
                            class="mt-1 w-full border border-slate-300 rounded-lg px-3 py-2 text-sm">
                    </div>
                    <div>
                        <label for="filterDateTo" class="text-xs font-semibold text-slate-500">To</label>
                        <input type="date" id="filterDateTo"
                            class="mt-1 w-full border border-slate-300 rounded-lg px-3 py-2 text-sm">
                    </div>
                    <div>
                        <label for="filterSearch" class="text-xs font-semibold text-slate-500">Record ID / text</label>
                        <input type="text" id="filterSearch" placeholder="e.g. 12 or Status"
                            class="mt-1 w-full border border-slate-300 rounded-lg px-3 py-2 text-sm">
                    </div>
                </div>
                <div class="mt-4 flex gap-2">
                    <button id="applyFiltersBtn" type="button"
                        class="rounded-lg bg-blue-600 px-4 py-2 text-sm font-semibold text-white hover:bg-blue-700">Apply</button>
                    <button id="clearFiltersBtn" type="button"
                        class="rounded-lg border border-slate-300 bg-white px-4 py-2 text-sm font-semibold text-slate-700 hover:bg-slate-50">Clear</button>
                </div>
            </div>

            <!-- Table -->
            <div class="rounded-3xl bg-white p-6 shadow-sm border border-slate-100">
                <div class="overflow-x-auto">
                    <table class="w-full text-left text-sm">
                        <thead>
                            <tr class="border-b border-slate-200 text-xs uppercase tracking-wide text-slate-500">
                                <th class="py-2 pr-4">Date</th>
                                <th class="py-2 pr-4">User</th>
                                <th class="py-2 pr-4">Action</th>
                                <th class="py-2 pr-4">Table</th>
                                <th class="py-2 pr-4">Record</th>
                                <th class="py-2 pr-4">Field</th>
                                <th class="py-2 pr-4">Old value</th>
                                <th class="py-2 pr-4">New value</th>
                                <th class="py-2">IP</th>
                            </tr>
                        </thead>
                        <tbody id="auditLogBody">
                            <tr>
                                <td colspan="9" class="py-8 text-center text-slate-400">Loading...</td>
                            </tr>
                        </tbody>
                    </table>
                </div>

                <div class="mt-4 flex items-center justify-between text-sm text-slate-500">
                    <span id="auditSummary"></span>
                    <div class="flex gap-2">
                        <button id="prevPageBtn" type="button"
                            class="rounded-lg border border-slate-300 px-3 py-1.5 font-semibold hover:bg-slate-50 disabled:opacity-40 disabled:cursor-not-allowed">Previous</button>
                        <button id="nextPageBtn" type="button"
                            class="rounded-lg border border-slate-300 px-3 py-1.5 font-semibold hover:bg-slate-50 disabled:opacity-40 disabled:cursor-not-allowed">Next</button>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <script src="../assets/javascript/audit-logs.js"></script>
</body>

</html>