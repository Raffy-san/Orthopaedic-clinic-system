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

// Set default consultation fee
$defaultConsultationFee = 500; // PHP

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
    <title>Consultation</title>
</head>

<body class="h-screen flex bg-slate-200 overflow-hidden">
    <?php include_once '../includes/sidebar.php'; ?>

    <section class="flex-1 min-h-0 flex flex-col overflow-hidden">
        <div
            class="flex items-center justify-between bg-gradient-to-r from-[#0b1f0b] via-[#1e6b34] to-[#2e8b47] px-6 py-4">
            <h1 class="text-2xl font-bold text-white">Southern Leyte Orthopaedic Clinic System</h1>
            <div class="flex flex-col gap-3 items-end">
                <h1 class="text-2xl font-bold text-white">Consultation</h1>
                <h3 class="text-sm font-medium text-white">Doctor's consultation workspace</h3>
            </div>
        </div>

        <div class="grid grid-cols-12 gap-6 min-h-0 overflow-auto p-6 space-y-4">
            <!-- Left: Clinic Queue -->
            <div class="col-span-12 md:col-span-5">
                <div class="bg-white rounded-3xl shadow-sm p-6 border border-slate-100">
                    <h2 class="text-lg font-semibold mb-4 text-slate-900">Clinic Queue</h2>
                    <div id="clinicQueueContainer" class="space-y-3">
                        <div class="text-center py-8">
                            <p class="text-sm text-slate-500">Loading queue...</p>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Right: Patient details + Consultation form -->
            <div class="col-span-12 md:col-span-7">
                <div id="patientHeaderContainer"
                    class="patient-header bg-white rounded-3xl shadow-sm p-6 border border-slate-100 mb-4 hidden">
                    <div class="flex items-start justify-between">
                        <div class="flex-1">
                            <div class="flex justify-between">
                                <div>
                                    <h2 id="patientName" class="text-xl font-bold text-slate-900">-</h2>
                                    <div id="patientInfo" class="text-sm text-slate-500 mt-1">-</div>
                                </div>

                                <!-- Replaces the old hardcoded "In Consultation" badge -->
                                <div class="ml-4 flex flex-col items-end gap-2">
                                    <span id="patientStatusBadge"
                                        class="text-xs bg-slate-100 text-slate-600 px-3 py-1 rounded-full font-semibold">Selected</span>
                                    <button id="startConsultationBtn" type="button"
                                        class="inline-flex items-center justify-center rounded-lg bg-emerald-600 px-4 py-2 text-sm font-semibold text-white hover:bg-emerald-700 transition">
                                        Start Consultation
                                    </button>
                                </div>
                            </div>

                            <div class="mt-4 grid grid-cols-1 sm:grid-cols-3 gap-3">
                                <div class="bg-slate-50 p-3 rounded-lg border border-slate-200">
                                    <div class="text-xs text-slate-500 font-semibold">Chief Complaint</div>
                                    <div id="chiefComplaint" class="text-sm text-slate-900 font-medium mt-1">-</div>
                                </div>
                                <div class="bg-slate-50 p-3 rounded-lg border border-slate-200">
                                    <div class="text-xs text-slate-500 font-semibold">Last Visit</div>
                                    <div id="lastVisit" class="text-sm text-slate-900 font-medium mt-1">-</div>
                                </div>
                            </div>
                        </div>

                    </div>
                </div>

                <div id="noPatientSelected"
                    class="bg-slate-100 rounded-3xl p-8 text-center border border-slate-200 mb-4">
                    <p class="text-slate-500 text-sm">Select a patient from the queue to start consultation</p>
                </div>

                <!-- Patient Consultation History -->
                <div id="consultationHistoryContainer"
                    class="bg-white rounded-3xl shadow-sm p-6 border border-slate-100 mb-4 hidden max-h-64 overflow-y-auto">
                    <div class="flex items-center justify-between mb-4">
                        <h3 class="font-bold text-lg text-slate-900">Patient History</h3>
                        <span id="visitCountBadge"
                            class="text-xs bg-blue-100 text-blue-700 px-3 py-1 rounded-full font-semibold">0
                            visits</span>
                    </div>
                    <div id="historyListContainer" class="space-y-4">
                        <div class="text-center py-4">
                            <p class="text-sm text-slate-500">Loading history...</p>
                        </div>
                    </div>
                </div>

                <form id="consultationForm"
                    class="consultation-panel bg-white rounded-3xl shadow-sm p-6 border border-slate-100 hidden">
                    <input type="hidden" id="consultationID" name="consultation_id">
                    <input type="hidden" id="appointmentID" name="appointment_id">
                    <input type="hidden" id="patientID" name="patient_id">
                    <input type="hidden" name="csrf_token"
                        value="<?= htmlspecialchars($csrfToken, ENT_QUOTES, 'UTF-8') ?>">
                    <input type="hidden" id="consultationFee" name="consultation_fee"
                        value="<?= $defaultConsultationFee ?>">

                    <h3 class="font-bold text-lg text-slate-900 mb-6">Consultation Notes</h3>

                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <div>
                            <label class="text-sm font-semibold text-slate-700">Diagnosis *</label>
                            <input type="text" name="diagnosis"
                                class="w-full mt-2 border border-slate-300 rounded-lg px-4 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500"
                                required placeholder="e.g., Osteoarthritis, right knee">
                        </div>

                        <div>
                            <label class="text-sm font-semibold text-slate-700">Treatment Plan *</label>
                            <input type="text" name="treatment"
                                class="w-full mt-2 border border-slate-300 rounded-lg px-4 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500"
                                required placeholder="e.g., PT, anti-inflammatory meds">
                        </div>
                    </div>

                    <div class="mt-4">
                        <label class="text-sm font-semibold text-slate-700">Notes</label>
                        <textarea name="notes"
                            class="w-full mt-2 border border-slate-300 rounded-lg px-4 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500 h-28"
                            placeholder="Additional observations..."></textarea>
                    </div>

                    <div class="mt-6 flex flex-col gap-4">
                        <div class="text-sm font-semibold text-slate-900">Medication / Prescription Required?</div>
                        <div class="flex gap-2">
                            <button id="addPrescriptionBtn" type="button"
                                class="inline-flex items-center justify-center rounded-lg border border-slate-300 bg-white px-4 py-2 text-sm font-semibold text-slate-700 hover:bg-slate-50 transition">Yes
                                — Add Prescription</button>
                            <button id="skipPrescriptionBtn" type="button"
                                class="inline-flex items-center justify-center rounded-lg border border-slate-300 bg-white px-4 py-2 text-sm font-semibold text-slate-700 hover:bg-slate-50 transition">No
                                — Skip</button>
                        </div>

                        <div id="prescriptionDetails" class="hidden">
                            <div id="prescriptionList" class="space-y-4"></div>

                            <button id="addAnotherMedicineBtn" type="button"
                                class="mt-3 inline-flex items-center gap-2 text-sm font-semibold text-blue-600 hover:text-blue-800">
                                <i class="fa-solid fa-plus"></i> Add another medicine
                            </button>
                        </div>

                        <!-- Hidden template — cloned by JS, never shown directly -->
                        <template id="prescriptionRowTemplate">
                            <div class="prescription-row border border-slate-200 rounded-xl p-4 bg-slate-50 relative">
                                <button type="button"
                                    class="remove-row-btn absolute top-3 right-3 text-xs text-red-500 hover:text-red-700 font-semibold hidden">
                                    <i class="fa-solid fa-xmark"></i> Remove
                                </button>

                                <label class="text-sm font-semibold text-slate-700">Quick pick a medicine</label>
                                <div class="medicine-preset-grid grid grid-cols-2 md:grid-cols-3 gap-2 mt-2"></div>

                                <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mt-4">
                                    <div>
                                        <label class="text-sm font-semibold text-slate-700">Medicine Name *</label>
                                        <input type="text"
                                            class="rx-medicine w-full mt-2 border border-slate-300 rounded-lg px-4 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500"
                                            placeholder="e.g., Ibuprofen">
                                    </div>
                                    <div>
                                        <label class="text-sm font-semibold text-slate-700">Dosage *</label>
                                        <input type="text"
                                            class="rx-dosage w-full mt-2 border border-slate-300 rounded-lg px-4 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500"
                                            placeholder="e.g., 500mg">
                                    </div>
                                </div>

                                <div class="mt-4">
                                    <label class="text-sm font-semibold text-slate-700">Frequency *</label>
                                    <div class="frequency-chips flex flex-wrap gap-2 mt-2"></div>
                                    <input type="hidden" class="rx-frequency">
                                </div>

                                <div class="mt-4">
                                    <label class="text-sm font-semibold text-slate-700">Duration</label>
                                    <div class="duration-chips flex flex-wrap gap-2 mt-2"></div>
                                    <input type="hidden" class="rx-duration">
                                </div>


                                <div class="mt-4">
                                    <label class="text-xs text-slate-600">Quantity</label>
                                    <input type="number" min="1" step="1"
                                        class="rx-quantity mt-1 w-full rounded-xl border border-slate-200 px-2.5 py-1.5 text-xs"
                                        placeholder="e.g. 21">
                                    <div class="quantity-chips flex flex-wrap gap-2 mt-2"></div>
                                </div>

                                <div class="mt-4">
                                    <label class="text-sm font-semibold text-slate-700">Instructions</label>
                                    <div class="instruction-chips flex flex-wrap gap-2 mt-2"></div>
                                    <textarea
                                        class="rx-instructions w-full mt-2 border border-slate-300 rounded-lg px-4 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500"
                                        placeholder="Tap chips above, or type your own..." rows="2"></textarea>
                                </div>
                            </div>
                        </template>

                        <div class="mt-6 flex flex-col gap-4 border-t border-slate-100 pt-6">
                            <div class="text-sm font-semibold text-slate-900">Schedule a Follow-Up?</div>
                            <div class="flex gap-2">
                                <button id="addFollowupBtn" type="button"
                                    class="inline-flex items-center justify-center rounded-lg border border-slate-300 bg-white px-4 py-2 text-sm font-semibold text-slate-700 hover:bg-slate-50 transition">Yes
                                    — Schedule</button>
                                <button id="skipFollowupBtn" type="button"
                                    class="inline-flex items-center justify-center rounded-lg border border-slate-300 bg-white px-4 py-2 text-sm font-semibold text-slate-700 hover:bg-slate-50 transition">No
                                    — Skip</button>
                            </div>

                            <div id="followupDetails" class="hidden">
                                <p id="followupDateLabel" class="text-sm font-semibold text-slate-700">Follow-up Date *
                                </p>
                                <input type="hidden" id="followupDate" name="followup[date]">
                                <div class="mt-2 rounded-xl border border-slate-200 p-4" data-availability-calendar
                                    data-input-id="followupDate" aria-labelledby="followupDateLabel">
                                    <div class="mb-4 flex items-center justify-between">
                                        <button type="button" data-calendar-prev aria-label="Previous month"
                                            class="rounded-lg px-3 py-2 text-slate-600 hover:bg-slate-100">&larr;</button>
                                        <h3 data-calendar-month class="font-semibold text-slate-900"></h3>
                                        <button type="button" data-calendar-next aria-label="Next month"
                                            class="rounded-lg px-3 py-2 text-slate-600 hover:bg-slate-100">&rarr;</button>
                                    </div>
                                    <div class="mb-2 grid grid-cols-7 gap-1 text-center text-xs font-semibold text-slate-500"
                                        aria-hidden="true">
                                        <span>Mon</span><span>Tue</span><span>Wed</span><span>Thu</span><span>Fri</span><span>Sat</span><span>Sun</span>
                                    </div>
                                    <div data-calendar-days class="grid grid-cols-7 gap-1" role="grid"
                                        aria-label="Follow-up dates"></div>
                                    <p data-calendar-error class="mt-3 hidden text-xs text-red-600" role="alert"></p>
                                    <div class="mt-4 flex flex-wrap gap-x-4 gap-y-2 text-xs text-slate-500">
                                        <span class="flex items-center gap-2"><span
                                                class="h-3 w-3 rounded-full border border-red-200 bg-red-100"></span>Doctor
                                            unavailable</span>
                                        <span class="flex items-center gap-2"><span
                                                class="h-3 w-3 rounded-full border border-slate-200 bg-slate-100"></span>Clinic
                                            closed</span>
                                        <span class="flex items-center gap-2"><span
                                                class="h-3 w-3 rounded-full border border-emerald-200 bg-white"></span>Available</span>
                                    </div>
                                </div>

                                <div class="mt-4">
                                    <p class="text-sm font-semibold text-slate-700">Follow-up Time *</p>
                                    <input type="hidden" id="followupTime" name="followup[time]">
                                    <p id="followupTimeMessage" class="mt-1 text-xs text-slate-500">Choose a date first
                                        to see available slots.</p>
                                    <div id="followupTimeSlots"
                                        class="mt-3 hidden grid grid-cols-2 gap-3 sm:grid-cols-3">
                                        <?php foreach (['09:00', '09:30', '10:00', '10:30', '11:00', '11:30'] as $t): ?>
                                            <button type="button" data-time="<?= $t ?> AM"
                                                class="followup-time-slot rounded-3xl bg-slate-100 px-4 py-4 text-sm font-semibold text-slate-700 hover:bg-sky-100"><?= $t ?>
                                                AM</button>
                                        <?php endforeach; ?>
                                    </div>
                                </div>

                                <div id="followupAlternatives" class="hidden mt-3">
                                    <label for="followupAlternativeDate"
                                        class="text-sm font-semibold text-slate-700">Alternative available dates</label>
                                    <select id="followupAlternativeDate"
                                        class="w-full mt-2 border border-slate-300 rounded-lg px-4 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500">
                                        <option value="">Select an alternative date</option>
                                    </select>
                                </div>

                                <label class="text-sm font-semibold text-slate-700 mt-3 block">Remarks</label>
                                <textarea id="followupRemarks" name="followup[remarks]"
                                    class="w-full mt-2 border border-slate-300 rounded-lg px-4 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500"
                                    placeholder="e.g., Check healing progress, review X-ray"></textarea>
                            </div>

                            <div>
                                <button type="submit"
                                    class="w-full inline-flex items-center justify-center rounded-lg bg-blue-600 px-4 py-2 text-sm font-semibold text-white hover:bg-blue-700 transition">Save
                                    & Pass to Billing</button>
                            </div>
                        </div>
                </form>
            </div>

            <?php include '../includes/message-modal.php' ?>
    </section>

    <script>
        window.csrfToken = <?= json_encode($csrfToken, JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_AMP | JSON_HEX_QUOT) ?>;
    </script>
    <script src="../assets/javascript/availability-calendar.js"></script>
    <script src="../assets/javascript/print-prescription.js"></script>
    <script src="../assets/javascript/consultation.js"></script>
</body>

</html>