<?php
require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/../includes/auth.php';
require_once __DIR__ . '/../php/fetch/fetch.php';
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
    <link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css" />
    <link rel="icon" href="../assets/img/rounded-logo.ico" type="image/x-icon">
    <title>Medical Records</title>
</head>

<body class="h-screen flex bg-slate-200 overflow-hidden">
    <?php include_once '../includes/sidebar.php'; ?>
    <section class="flex-1 min-h-0 flex flex-col overflow-hidden">
        <div
            class="flex items-center justify-between bg-gradient-to-r from-[#0b1f0b] via-[#1e6b34] to-[#2e8b47] px-6 py-4">
            <h1 class="text-2xl font-bold text-white">Southern Leyte Orthopaedic Clinic System</h1>
            <div class="flex flex-col gap-3 items-end">
                <h1 class="text-2xl font-bold text-white">Medical Records</h1>
                <h3 class="text-sm font-medium text-white">Diagnosis history, treatment plans, and medicine records</h3>
            </div>
        </div>

        <div class="grid grid-cols-12 gap-6 min-h-0 overflow-auto p-6 space-y-4">
            <div class="col-span-12 md:col-span-5">
                <div class="bg-white rounded-3xl shadow-sm p-6 border border-slate-100">

                    <!-- Basic search -->
                    <div class="mb-3">
                        <input type="search" id="recordSearchInput"
                            class="w-full rounded-lg border border-slate-300 px-4 py-2 focus:outline-none focus:ring-2 focus:ring-green-500 focus:border-transparent"
                            placeholder="Search by patient name or ID">
                    </div>

                    <!-- Advanced search toggle -->
                    <button type="button" id="toggleAdvancedSearch"
                        class="text-xs font-semibold text-green-700 hover:text-green-800 mb-3 flex items-center gap-1">
                        <i class="fa-solid fa-sliders"></i>
                        Advanced Search
                        <i class="fa-solid fa-chevron-down text-[10px]" id="advancedSearchChevron"></i>
                    </button>

                    <!-- Advanced search panel -->
                    <div id="advancedSearchPanel"
                        class="hidden mb-4 space-y-3 bg-slate-50 rounded-xl p-4 border border-slate-200">
                        <div>
                            <label class="text-xs font-medium text-slate-500 mb-1 block">Diagnosis</label>
                            <input type="text" id="filterDiagnosis"
                                class="w-full rounded-lg border border-slate-300 px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-green-500"
                                placeholder="e.g. fracture, sprain">
                        </div>

                        <div class="grid grid-cols-2 gap-3">
                            <div>
                                <label class="text-xs font-medium text-slate-500 mb-1 block">Date From</label>
                                <input type="date" id="filterDateFrom"
                                    class="w-full rounded-lg border border-slate-300 px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-green-500">
                            </div>
                            <div>
                                <label class="text-xs font-medium text-slate-500 mb-1 block">Date To</label>
                                <input type="date" id="filterDateTo"
                                    class="w-full rounded-lg border border-slate-300 px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-green-500">
                            </div>
                        </div>

                        <div>
                            <label class="text-xs font-medium text-slate-500 mb-1 block">Doctor</label>
                            <select id="filterDoctor"
                                class="w-full rounded-lg border border-slate-300 px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-green-500">
                                <option value="">All Doctors</option>
                            </select>
                        </div>

                        <div class="flex gap-2 pt-1">
                            <button type="button" id="applyFiltersBtn"
                                class="flex-1 bg-green-700 hover:bg-green-800 text-white text-sm font-semibold rounded-lg px-3 py-2 transition">
                                Apply Filters
                            </button>
                            <button type="button" id="clearFiltersBtn"
                                class="flex-1 bg-slate-200 hover:bg-slate-300 text-slate-700 text-sm font-semibold rounded-lg px-3 py-2 transition">
                                Clear
                            </button>
                        </div>
                    </div>

                    <div id="recordListContainer" class="space-y-3">
                        <div class="text-center py-8">
                            <p class="text-sm text-slate-500">Loading patient records...</p>
                        </div>
                    </div>
                </div>
            </div>

            <div class="col-span-12 md:col-span-7">
                <div id="patientRecordsContainer"
                    class="patient-header bg-white rounded-3xl shadow-sm p-6 border border-slate-100 mb-4 hidden">

                    <div class="mb-4">
                        <h2 id="patientName" class="text-lg font-bold text-slate-700"></h2>
                        <p class="text-sm text-slate-500">
                            <span id="patientCode"></span> ·
                            <span id="consultationDate"></span> ·
                            Dr. <span id="doctorName"></span>
                        </p>
                    </div>

                    <input type="hidden" id="patientID">
                    <input type="hidden" id="consultationID">

                    <div class="grid grid-cols-2 gap-4">
                        <div class="bg-slate-50 rounded-xl p-4">
                            <p class="text-xs text-slate-400 mb-1">Diagnosis</p>
                            <p id="currentDiagnosis" class="font-semibold text-slate-800"></p>
                        </div>
                        <div class="bg-slate-50 rounded-xl p-4">
                            <p class="text-xs text-slate-400 mb-1">Treatment Plan</p>
                            <p id="currentTreatment" class="font-semibold text-slate-800"></p>
                        </div>
                    </div>
                </div>

                <div id="prescribedMedicinesContainer"
                    class="bg-white rounded-3xl shadow-sm p-6 border border-slate-100 mb-4 hidden">
                    <p class="font-semibold text-slate-800 mb-4">Prescribed Medicines</p>
                    <table class="w-full text-sm">
                        <thead>
                            <tr class="text-left text-slate-400 text-xs">
                                <th class="pb-2 font-medium">Medicine</th>
                                <th class="pb-2 font-medium">Dosage</th>
                                <th class="pb-2 font-medium">Frequency</th>
                                <th class="pb-2 font-medium">Duration</th>
                            </tr>
                        </thead>
                        <tbody id="prescribedMedicinesBody"></tbody>
                    </table>
                </div>

                <div id="diagnosisHistoryContainer"
                    class="bg-white rounded-3xl shadow-sm p-6 border border-slate-100 mb-4 hidden">
                    <p class="font-semibold text-slate-800 mb-4">Diagnosis History — <span
                            id="historyPatientName"></span></p>
                    <div id="diagnosisHistoryList" class="space-y-4"></div>
                </div>

                <div id="noRecordsContainer"
                    class="bg-slate-100 rounded-3xl p-8 text-center border border-slate-200 mb-4">
                    <p class="text-slate-500 text-sm">No records found</p>
                </div>
            </div>
        </div>
    </section>
    <script src="../assets/javascript/records.js"></script>
</body>

</html>