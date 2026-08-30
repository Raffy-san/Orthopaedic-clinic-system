<?php
require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/../includes/auth.php';
require_once __DIR__ . '/../includes/fetch.php';
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

<body class="h-screen flex bg-slate-200">
    <?php include_once '../includes/sidebar.php'; ?>

    <section class="flex-1 p-6 overflow-auto">
        <div class="mb-6 space-y-1">
            <h1 class="text-2xl font-bold">Consultation</h1>
            <h3 class="text-sm font-medium text-gray-500">Doctor's consultation workspace</h3>
        </div>

        <div class="grid grid-cols-12 gap-6">
            <!-- Left: Clinic Queue -->
            <div class="col-span-12 md:col-span-4">
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
            <div class="col-span-12 md:col-span-8">
                <div id="patientHeaderContainer" class="patient-header bg-white rounded-3xl shadow-sm p-6 border border-slate-100 mb-4 hidden">
                    <div class="flex items-start justify-between">
                        <div class="flex-1">
                            <h2 id="patientName" class="text-xl font-bold text-slate-900">-</h2>
                            <div id="patientInfo" class="text-sm text-slate-500 mt-1">-</div>

                            <div class="mt-4 grid grid-cols-1 sm:grid-cols-3 gap-3">
                                <div class="bg-slate-50 p-3 rounded-lg border border-slate-200">
                                    <div class="text-xs text-slate-500 font-semibold">Chief Complaint</div>
                                    <div id="chiefComplaint" class="text-sm text-slate-900 font-medium mt-1">-</div>
                                </div>
                                <div class="bg-slate-50 p-3 rounded-lg border border-slate-200">
                                    <div class="text-xs text-slate-500 font-semibold">Last Visit</div>
                                    <div id="lastVisit" class="text-sm text-slate-900 font-medium mt-1">-</div>
                                </div>
                                <div class="bg-slate-50 p-3 rounded-lg border border-slate-200">
                                    <div class="text-xs text-slate-500 font-semibold">Allergies</div>
                                    <div id="allergies" class="text-sm text-slate-900 font-medium mt-1">-</div>
                                </div>
                            </div>
                        </div>

                        <div class="ml-4">
                            <span class="text-xs bg-emerald-100 text-emerald-700 px-3 py-1 rounded-full font-semibold">In Consultation</span>
                        </div>
                    </div>
                </div>

                <div id="noPatientSelected" class="bg-slate-100 rounded-3xl p-8 text-center border border-slate-200 mb-4">
                    <p class="text-slate-500 text-sm">Select a patient from the queue to start consultation</p>
                </div>

                <form id="consultationForm" class="consultation-panel bg-white rounded-3xl shadow-sm p-6 border border-slate-100 hidden">
                    <input type="hidden" id="appointmentID" name="appointment_id">
                    <input type="hidden" id="patientID" name="patient_id">
                    <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($csrfToken, ENT_QUOTES, 'UTF-8') ?>">
                    <input type="hidden" id="consultationFee" name="consultation_fee" value="<?= $defaultConsultationFee ?>">

                    <h3 class="font-bold text-lg text-slate-900 mb-6">Consultation Notes</h3>

                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <div>
                            <label class="text-sm font-semibold text-slate-700">Diagnosis *</label>
                            <input type="text" name="diagnosis" class="w-full mt-2 border border-slate-300 rounded-lg px-4 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500" required
                                placeholder="e.g., Osteoarthritis, right knee">
                        </div>

                        <div>
                            <label class="text-sm font-semibold text-slate-700">Treatment Plan *</label>
                            <input type="text" name="treatment" class="w-full mt-2 border border-slate-300 rounded-lg px-4 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500" required
                                placeholder="e.g., PT, anti-inflammatory meds">
                        </div>
                    </div>

                    <div class="mt-4">
                        <label class="text-sm font-semibold text-slate-700">Notes</label>
                        <textarea name="notes" class="w-full mt-2 border border-slate-300 rounded-lg px-4 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500 h-28"
                            placeholder="Additional observations..."></textarea>
                    </div>

                    <div class="mt-6 flex flex-col gap-4">
                        <div class="text-sm font-semibold text-slate-900">Medication / Prescription Required?</div>
                        <div class="flex gap-2">
                            <button id="addPrescriptionBtn" type="button" class="inline-flex items-center justify-center rounded-lg border border-slate-300 bg-white px-4 py-2 text-sm font-semibold text-slate-700 hover:bg-slate-50 transition">Yes — Add Prescription</button>
                            <button id="skipPrescriptionBtn" type="button" class="inline-flex items-center justify-center rounded-lg border border-slate-300 bg-white px-4 py-2 text-sm font-semibold text-slate-700 hover:bg-slate-50 transition">No — Skip</button>
                        </div>

                        <div id="prescriptionDetails" class="hidden">
                            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                                <div>
                                    <label class="text-sm font-semibold text-slate-700">Medicine Name *</label>
                                    <input type="text" id="prescriptionMedicine" name="prescription[medicine]" class="w-full mt-2 border border-slate-300 rounded-lg px-4 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500"
                                        placeholder="e.g., Ibuprofen">
                                </div>
                                <div>
                                    <label class="text-sm font-semibold text-slate-700">Dosage *</label>
                                    <input type="text" id="prescriptionDosage" name="prescription[dosage]" class="w-full mt-2 border border-slate-300 rounded-lg px-4 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500"
                                        placeholder="e.g., 500mg">
                                </div>
                                <div>
                                    <label class="text-sm font-semibold text-slate-700">Frequency *</label>
                                    <input type="text" id="prescriptionFrequency" name="prescription[frequency]" class="w-full mt-2 border border-slate-300 rounded-lg px-4 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500"
                                        placeholder="e.g., 3x daily">
                                </div>
                                <div>
                                    <label class="text-sm font-semibold text-slate-700">Duration</label>
                                    <input type="text" id="prescriptionDuration" name="prescription[duration]" class="w-full mt-2 border border-slate-300 rounded-lg px-4 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500"
                                        placeholder="e.g., 7 days">
                                </div>
                            </div>
                            <div class="mt-4">
                                <label class="text-sm font-semibold text-slate-700">Instructions</label>
                                <textarea id="prescriptionInstructions" name="prescription[instructions]" class="w-full mt-2 border border-slate-300 rounded-lg px-4 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500"
                                    placeholder="e.g., Take with food, avoid alcohol..."></textarea>
                            </div>
                        </div>

                        <div>
                            <button type="submit" class="w-full inline-flex items-center justify-center rounded-lg bg-blue-600 px-4 py-2 text-sm font-semibold text-white hover:bg-blue-700 transition">Save & Pass to Billing</button>
                        </div>
                    </div>
                </form>
            </div>
    </section>
    </div>

    <script>
        let currentAppointmentData = null;
        let csrfToken = '<?= htmlspecialchars($csrfToken, ENT_QUOTES, 'UTF-8') ?>';
        let currentConsultationIndex = 0;

        // Load clinic queue on page load
        async function loadClinicQueue() {
            try {
                const response = await fetch('../php/fetch/fetch-clinic-queue.php');
                const data = await response.json();

                if (data.status !== 'success') {
                    document.getElementById('clinicQueueContainer').innerHTML = '<p class="text-sm text-red-600">Error loading queue: ' + data.message + '</p>';
                    return;
                }

                const appointments = data.data;
                if (appointments.length === 0) {
                    document.getElementById('clinicQueueContainer').innerHTML = '<p class="text-sm text-slate-500">No appointments scheduled for today</p>';
                    return;
                }

                let queueHTML = '';
                appointments.forEach((appointment, index) => {
                    const isActive = index === 0;
                    const statusBadge = isActive ? 
                        '<span class="text-xs bg-emerald-600 text-white px-2 py-1 rounded-full font-semibold">In Consultation</span>' :
                        '<span class="text-xs bg-slate-200 text-slate-700 px-2 py-1 rounded-full font-semibold">' + (index === 1 ? 'Next' : 'Queue') + '</span>';
                    
                    const bgClass = isActive ? 'bg-emerald-50 border-emerald-200' : 'bg-slate-50 border-slate-200 hover:bg-slate-100';

                    queueHTML += `
                        <div class="queue-item rounded-lg ${bgClass} p-4 border cursor-pointer transition queue-btn" data-index="${index}" data-appointment-id="${appointment.AppointmentID}" data-patient-id="${appointment.PatientID}">
                            <div class="flex items-start justify-between">
                                <div>
                                    <div class="font-semibold text-slate-900">${appointment.FirstName} ${appointment.LastName}</div>
                                    <div class="text-xs text-slate-500 mt-1">${appointment.PatientCode}</div>
                                </div>
                                ${statusBadge}
                            </div>
                        </div>
                    `;
                });

                document.getElementById('clinicQueueContainer').innerHTML = queueHTML;

                // Add click handlers to queue items
                document.querySelectorAll('.queue-btn').forEach(btn => {
                    btn.addEventListener('click', function() {
                        const appointmentData = appointments[parseInt(this.getAttribute('data-index'))];
                        loadPatientData(appointmentData);
                    });
                });

                // Load first patient automatically
                if (appointments.length > 0) {
                    loadPatientData(appointments[0]);
                }

            } catch (error) {
                console.error('Error:', error);
                document.getElementById('clinicQueueContainer').innerHTML = '<p class="text-sm text-red-600">Error loading queue</p>';
            }
        }

        // Load patient data and show form
        function loadPatientData(appointmentData) {
            currentAppointmentData = appointmentData;

            document.getElementById('patientName').textContent = appointmentData.FirstName + ' ' + appointmentData.LastName;
            document.getElementById('patientInfo').textContent = appointmentData.PatientCode + ' - ' + (appointmentData.PatientID ? 'Existing Patient' : 'New Patient');
            document.getElementById('chiefComplaint').textContent = appointmentData.ChiefComplaint || 'Not recorded';
            document.getElementById('lastVisit').textContent = appointmentData.LastVisitDate ? new Date(appointmentData.LastVisitDate).toLocaleDateString() : 'First visit';
            document.getElementById('allergies').textContent = '-'; // This should come from patient allergies table

            document.getElementById('appointmentID').value = appointmentData.AppointmentID;
            document.getElementById('patientID').value = appointmentData.PatientID;

            document.getElementById('patientHeaderContainer').classList.remove('hidden');
            document.getElementById('noPatientSelected').classList.add('hidden');
            document.getElementById('consultationForm').classList.remove('hidden');

            // Reset form
            document.getElementById('consultationForm').reset();
            document.getElementById('prescriptionDetails').classList.add('hidden');
        }

        // Handle prescription buttons
        document.getElementById('addPrescriptionBtn').addEventListener('click', () => {
            document.getElementById('prescriptionDetails').classList.remove('hidden');
        });

        document.getElementById('skipPrescriptionBtn').addEventListener('click', () => {
            document.getElementById('prescriptionDetails').classList.add('hidden');
            document.getElementById('prescriptionMedicine').value = '';
            document.getElementById('prescriptionDosage').value = '';
            document.getElementById('prescriptionFrequency').value = '';
            document.getElementById('prescriptionDuration').value = '';
            document.getElementById('prescriptionInstructions').value = '';
        });

        // Handle form submission
        document.getElementById('consultationForm').addEventListener('submit', async function(e) {
            e.preventDefault();

            const hasPrescription = !document.getElementById('prescriptionDetails').classList.contains('hidden');
            const prescriptionData = hasPrescription ? {
                medicine: document.getElementById('prescriptionMedicine').value,
                dosage: document.getElementById('prescriptionDosage').value,
                frequency: document.getElementById('prescriptionFrequency').value,
                duration: document.getElementById('prescriptionDuration').value,
                instructions: document.getElementById('prescriptionInstructions').value
            } : null;

            const consultationData = {
                appointment_id: document.getElementById('appointmentID').value,
                patient_id: document.getElementById('patientID').value,
                diagnosis: document.querySelector('[name="diagnosis"]').value,
                treatment: document.querySelector('[name="treatment"]').value,
                notes: document.querySelector('[name="notes"]').value,
                consultation_fee: document.getElementById('consultationFee').value,
                has_prescription: hasPrescription,
                prescription: prescriptionData,
                csrf_token: csrfToken
            };

            try {
                const response = await fetch('../php/add/save-consultation.php', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                    },
                    body: JSON.stringify(consultationData)
                });

                const result = await response.json();

                if (result.status === 'success') {
                    csrfToken = result.csrf_token;
                    alert('Consultation saved successfully!\nConsultation ID: ' + result.consultation_id);
                    loadClinicQueue(); // Refresh the queue
                } else {
                    alert('Error: ' + result.message);
                }
            } catch (error) {
                console.error('Error:', error);
                alert('An error occurred while saving the consultation.');
            }
        });

        // Load queue on page load
        document.addEventListener('DOMContentLoaded', loadClinicQueue);
    </script>
</body>

</html>