let currentAppointmentData = null;
let csrfToken = window.csrfToken || "";
let currentConsultationIndex = 0;
let isSubmitting = false;
let lastSavedConsultation = null;

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
            // CHANGED — real status instead of index === 0
            const isActive = !!appointment.ConsultationStartTime;

            const statusBadge = isActive ?
                '<span class="text-xs bg-emerald-600 text-white px-2 py-1 rounded-full font-semibold">In Consultation</span>' :
                '<span class="text-xs bg-slate-200 text-slate-700 px-2 py-1 rounded-full font-semibold">' + (index === 0 ? 'Next' : 'Queue') + '</span>';

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

        document.querySelectorAll('.queue-btn').forEach(btn => {
            btn.addEventListener('click', function () {
                const appointmentData = appointments[parseInt(this.getAttribute('data-index'))];
                loadPatientData(appointmentData);
            });
        });

        if (appointments.length > 0) {
            loadPatientData(appointments[0]);
        }

    } catch (error) {
        console.error('Error:', error);
        document.getElementById('clinicQueueContainer').innerHTML = '<p class="text-sm text-red-600">Error loading queue</p>';
    }
}

function updateBodyScroll() {
    const anyModalOpen = document.querySelectorAll('.modal:not(.hidden)').length > 0;
    document.body.style.overflow = anyModalOpen ? 'hidden' : 'auto';
}

const openModal = (modal) => {
    modal.classList.remove("hidden");
    modal.classList.add("flex");
    updateBodyScroll();
};

const closeModal = (modal) => {
    modal.classList.add("hidden");
    updateBodyScroll();
};

function showMessage(title, message, type = "success", callback = null) {
    const modal = document.getElementById("messageModal");
    const titleElement = document.getElementById("messageTitle");
    const textElement = document.getElementById("messageText");

    titleElement.textContent = title;
    textElement.textContent = message;

    titleElement.classList.toggle("text-green-600", type === "success");
    titleElement.classList.toggle("text-red-600", type !== "success");

    openModal(modal);
    modal.classList.add('flex');

    document.getElementById("closeMessageBtn").onclick = () => {
        closeModal(modal);
        if (callback) callback();
    };
}

// Load patient data and show form
async function loadPatientData(appointmentData) {
    currentAppointmentData = appointmentData;

    document.getElementById('patientName').textContent = appointmentData.FirstName + ' ' + appointmentData.LastName;
    document.getElementById('patientInfo').textContent = appointmentData.PatientCode + ' - ' + (appointmentData.PatientID ? 'Existing Patient' : 'New Patient');
    document.getElementById('chiefComplaint').textContent = appointmentData.ChiefComplaint || 'Not recorded';
    document.getElementById('lastVisit').textContent = appointmentData.LastVisitDate ? new Date(appointmentData.LastVisitDate).toLocaleDateString() : 'First visit';
    document.getElementById('allergies').textContent = appointmentData.Allergies || 'None recorded';

    document.getElementById('appointmentID').value = appointmentData.AppointmentID;
    document.getElementById('patientID').value = appointmentData.PatientID;
    document.getElementById('consultationID').value = ''; // NEW — reset; only set once Start is clicked

    document.getElementById('patientHeaderContainer').classList.remove('hidden');
    document.getElementById('noPatientSelected').classList.add('hidden');

    // CHANGED — form stays hidden until Start Consultation is clicked
    document.getElementById('consultationForm').classList.add('hidden');

    // NEW — reset badge + button to "not started" state
    const badge = document.getElementById('patientStatusBadge');
    badge.textContent = 'Selected';
    badge.className = 'text-xs bg-slate-100 text-slate-600 px-3 py-1 rounded-full font-semibold';

    const startBtn = document.getElementById('startConsultationBtn');
    startBtn.classList.remove('hidden');
    startBtn.disabled = false;
    startBtn.textContent = 'Start Consultation';

    // Load consultation history
    await loadConsultationHistory(appointmentData.PatientID);

    // Reset form
    document.getElementById('consultationForm').reset();
    document.getElementById('prescriptionDetails').classList.add('hidden');

    isSubmitting = false;
    const submitBtn = document.querySelector('#consultationForm button[type="submit"]');
    submitBtn.disabled = false;
    submitBtn.textContent = 'Save & Pass to Billing';
    hideReprintBanner();
}

async function startConsultation() {
    const startBtn = document.getElementById('startConsultationBtn');
    startBtn.disabled = true;
    startBtn.textContent = 'Starting...';

    try {
        const response = await fetch('../php/add/start-consultation.php', {
            method: 'POST',
            headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
            body: new URLSearchParams({
                appointment_id: document.getElementById('appointmentID').value,
                patient_id: document.getElementById('patientID').value
            })
        });

        const result = await response.json();

        if (result.success) {
            document.getElementById('consultationID').value = result.consultation_id;

            const badge = document.getElementById('patientStatusBadge');
            badge.textContent = 'In Consultation';
            badge.className = 'text-xs bg-emerald-100 text-emerald-700 px-3 py-1 rounded-full font-semibold';

            startBtn.classList.add('hidden'); // hide the button now that it's started
            document.getElementById('consultationForm').classList.remove('hidden');
        } else {
            showMessage('Error', result.message || 'Unable to start consultation.', 'error');
            startBtn.disabled = false;
            startBtn.textContent = 'Start Consultation';
        }
    } catch (error) {
        console.error('Error starting consultation:', error);
        showMessage('Error', 'An error occurred while starting the consultation.', 'error');
        startBtn.disabled = false;
        startBtn.textContent = 'Start Consultation';
    }
}

document.getElementById('startConsultationBtn').addEventListener('click', startConsultation);

// Load consultation history for patient
async function loadConsultationHistory(patientID) {
    try {
        const response = await fetch('../php/fetch/fetch-consultation-history.php?patient_id=' + patientID);
        const result = await response.json();

        if (result.status === 'success') {
            const historyContainer = document.getElementById('historyListContainer');
            const visitCountBadge = document.getElementById('visitCountBadge');

            visitCountBadge.textContent = result.total_visits + ' visit' + (result.total_visits !== 1 ? 's' : '');

            if (result.data.length === 0) {
                historyContainer.innerHTML = '<p class="text-sm text-slate-500 text-center py-4">No previous consultations</p>';
                document.getElementById('consultationHistoryContainer').classList.add('hidden');
            } else {
                let historyHTML = '';
                result.data.forEach((consultation, index) => {
                    const date = new Date(consultation.ConsultationDate).toLocaleDateString('en-US', {
                        month: 'short',
                        day: 'numeric',
                        year: 'numeric'
                    });
                    const time = new Date(consultation.ConsultationDate).toLocaleTimeString('en-US', {
                        hour: '2-digit',
                        minute: '2-digit'
                    });

                    const doctorName = consultation.DoctorFirstName + ' ' + consultation.DoctorLastName;
                    const medicinesHTML = consultation.prescriptions.length > 0
                        ? consultation.prescriptions.map(m =>
                            `<div class="text-xs text-slate-600">💊 ${m.Medicine} (${m.Dosage}, ${m.Frequency})</div>`
                        ).join('')
                        : '<div class="text-xs text-slate-500 italic">No medicines prescribed</div>';

                    historyHTML += `
                        <div class="border border-slate-200 rounded-lg p-3 bg-slate-50">
                            <div class="flex justify-between items-start mb-2">
                                <div class="text-xs font-semibold text-slate-900">Visit ${result.total_visits - index}</div>
                                <span class="text-xs text-slate-500">${date} ${time}</span>
                            </div>
                            <div class="text-sm text-slate-700 mb-2">
                                <strong>Diagnosis:</strong> ${consultation.Diagnosis}
                            </div>
                            <div class="text-sm text-slate-700 mb-2">
                                <strong>Treatment:</strong> ${consultation.Treatment}
                            </div>
                            <div class="text-sm font-semibold text-slate-900 mb-1">Medicines:</div>
                            ${medicinesHTML}
                            <div class="text-xs text-slate-500 mt-2">Dr. ${doctorName}</div>
                        </div>
                    `;
                });
                historyContainer.innerHTML = historyHTML;
                document.getElementById('consultationHistoryContainer').classList.remove('hidden');
            }
        } else {
            document.getElementById('consultationHistoryContainer').classList.add('hidden');
        }
    } catch (error) {
        console.error('Error loading consultation history:', error);
        document.getElementById('consultationHistoryContainer').classList.add('hidden');
    }
}

const medicinePresets = [
    { name: 'Ibuprofen', dosage: '400mg', frequency: '3x daily', duration: '5 days', instructions: 'Take with food.' },
    { name: 'Naproxen', dosage: '500mg', frequency: '2x daily', duration: '7 days', instructions: 'Take with food.' },
    { name: 'Celecoxib', dosage: '200mg', frequency: '1x daily', duration: '7 days', instructions: 'Take with food.' },
    { name: 'Diclofenac', dosage: '50mg', frequency: '2x daily', duration: '5 days', instructions: 'Take with food.' },
    { name: 'Tramadol', dosage: '50mg', frequency: 'Every 6 hours as needed', duration: '5 days', instructions: 'May cause drowsiness. Avoid driving.' },
    { name: 'Paracetamol', dosage: '500mg', frequency: 'Every 6 hours as needed', duration: '5 days', instructions: 'Do not exceed 4g per day.' },
    { name: 'Methylcobalamin', dosage: '500mcg', frequency: '1x daily', duration: '30 days', instructions: '' },
    { name: 'Calcium + Vitamin D3', dosage: '600mg/400IU', frequency: '1x daily', duration: '30 days', instructions: '' },
    { name: 'Tolperisone', dosage: '150mg', frequency: '3x daily', duration: '5 days', instructions: 'Muscle relaxant — may cause drowsiness.' },
];

const frequencyOptions = ['1x daily', '2x daily', '3x daily', '4x daily', 'Every 6 hours as needed', 'Every 8 hours as needed', 'At bedtime'];
const durationOptions = ['3 days', '5 days', '7 days', '10 days', '14 days', '30 days', 'Until finished'];
const instructionOptions = ['Take with food.', 'Take on an empty stomach.', 'May cause drowsiness. Avoid driving.', 'Do not exceed recommended dose.', 'Stop if rash or discomfort occurs.'];

function buildChipRow(container, options, hiddenInput, multiFill) {
    container.innerHTML = '';
    options.forEach(option => {
        const chip = document.createElement('button');
        chip.type = 'button';
        chip.textContent = option;
        chip.className = 'chip-btn text-xs px-3 py-1.5 rounded-full border border-slate-300 text-slate-600 hover:bg-blue-50 hover:border-blue-400 transition';
        chip.addEventListener('click', () => {
            if (multiFill) {
                const current = hiddenInput.value.trim();
                if (current.includes(option)) {
                    hiddenInput.value = current.split(option).join('').replace(/\s{2,}/g, ' ').trim();
                    chip.classList.remove('bg-blue-100', 'border-blue-500', 'text-blue-700');
                    chip.classList.add('border-slate-300', 'text-slate-600');
                } else {
                    hiddenInput.value = current ? current + ' ' + option : option;
                    chip.classList.add('bg-blue-100', 'border-blue-500', 'text-blue-700');
                    chip.classList.remove('border-slate-300', 'text-slate-600');
                }
            } else {
                container.querySelectorAll('.chip-btn').forEach(c => {
                    c.classList.remove('bg-blue-100', 'border-blue-500', 'text-blue-700');
                    c.classList.add('border-slate-300', 'text-slate-600');
                });
                chip.classList.add('bg-blue-100', 'border-blue-500', 'text-blue-700');
                chip.classList.remove('border-slate-300', 'text-slate-600');
                hiddenInput.value = option;
            }
        });
        container.appendChild(chip);
    });
}

function highlightMatchingChip(container, value) {
    container.querySelectorAll('.chip-btn').forEach(chip => {
        const match = chip.textContent === value;
        chip.classList.toggle('bg-blue-100', match);
        chip.classList.toggle('border-blue-500', match);
        chip.classList.toggle('text-blue-700', match);
        chip.classList.toggle('border-slate-300', !match);
        chip.classList.toggle('text-slate-600', !match);
    });
}

function addPrescriptionRow() {
    const template = document.getElementById('prescriptionRowTemplate');
    const row = template.content.firstElementChild.cloneNode(true);
    document.getElementById('prescriptionList').appendChild(row);

    const medicineInput = row.querySelector('.rx-medicine');
    const dosageInput = row.querySelector('.rx-dosage');
    const frequencyHidden = row.querySelector('.rx-frequency');
    const durationHidden = row.querySelector('.rx-duration');
    const instructionsTextarea = row.querySelector('.rx-instructions');

    buildChipRow(row.querySelector('.frequency-chips'), frequencyOptions, frequencyHidden, false);
    buildChipRow(row.querySelector('.duration-chips'), durationOptions, durationHidden, false);
    buildChipRow(row.querySelector('.instruction-chips'), instructionOptions, instructionsTextarea, true);

    const presetGrid = row.querySelector('.medicine-preset-grid');
    medicinePresets.forEach(preset => {
        const card = document.createElement('button');
        card.type = 'button';
        card.className = 'preset-btn text-left text-sm border border-slate-200 rounded-lg px-3 py-2 hover:bg-blue-50 hover:border-blue-400 transition';
        card.innerHTML = `<div class="font-semibold text-slate-800">${preset.name}</div><div class="text-xs text-slate-500">${preset.dosage} · ${preset.frequency}</div>`;
        card.addEventListener('click', () => {
            medicineInput.value = preset.name;
            dosageInput.value = preset.dosage;
            frequencyHidden.value = preset.frequency;
            durationHidden.value = preset.duration;
            instructionsTextarea.value = preset.instructions;

            presetGrid.querySelectorAll('.preset-btn').forEach(c => c.classList.remove('bg-blue-100', 'border-blue-500'));
            card.classList.add('bg-blue-100', 'border-blue-500');

            highlightMatchingChip(row.querySelector('.frequency-chips'), preset.frequency);
            highlightMatchingChip(row.querySelector('.duration-chips'), preset.duration);
        });
        presetGrid.appendChild(card);
    });

    // Show "Remove" on every row except when it's the only one
    updateRemoveButtons();

    row.querySelector('.remove-row-btn').addEventListener('click', () => {
        row.remove();
        updateRemoveButtons();
    });
}

function updateRemoveButtons() {
    const rows = document.querySelectorAll('.prescription-row');
    rows.forEach(row => {
        row.querySelector('.remove-row-btn').classList.toggle('hidden', rows.length <= 1);
    });
}

document.getElementById('addAnotherMedicineBtn').addEventListener('click', addPrescriptionRow);

// ── Prescription Yes/No buttons ──────────────────────────
document.getElementById('addPrescriptionBtn').addEventListener('click', () => {
    document.getElementById('prescriptionDetails').classList.remove('hidden');
    if (document.getElementById('prescriptionList').children.length === 0) {
        addPrescriptionRow(); // start with one card
    }
});

document.getElementById('skipPrescriptionBtn').addEventListener('click', () => {
    document.getElementById('prescriptionDetails').classList.add('hidden');
    document.getElementById('prescriptionList').innerHTML = '';
});

// Handle follow-up buttons
document.getElementById('addFollowupBtn').addEventListener('click', () => {
    document.getElementById('followupDetails').classList.remove('hidden');
});

document.getElementById('skipFollowupBtn').addEventListener('click', () => {
    document.getElementById('followupDetails').classList.add('hidden');
    document.getElementById('followupDate').value = '';
    document.getElementById('followupRemarks').value = '';
    document.getElementById('followupAlternatives').classList.add('hidden');
});

document.getElementById('followupAlternativeDate').addEventListener('change', function () {
    if (this.value) {
        document.getElementById('followupDate').value = this.value;
    }
});

function showFollowupAlternatives(dates) {
    const alternatives = document.getElementById('followupAlternatives');
    const select = document.getElementById('followupAlternativeDate');
    select.innerHTML = '<option value="">Select an alternative date</option>';

    dates.forEach(dateValue => {
        const option = document.createElement('option');
        option.value = dateValue;
        option.textContent = new Date(dateValue + 'T00:00:00').toLocaleDateString('en-US', {
            weekday: 'long', month: 'long', day: 'numeric', year: 'numeric'
        });
        select.appendChild(option);
    });

    alternatives.classList.toggle('hidden', dates.length === 0);
}
document.getElementById('consultationForm').addEventListener('submit', async function (e) {
    e.preventDefault();

    if (isSubmitting) return; // NEW — block double-clicks

    const hasPrescription = !document.getElementById('prescriptionDetails').classList.contains('hidden');

    let prescriptions = [];
    if (hasPrescription) {
        const rows = document.querySelectorAll('.prescription-row');
        for (const row of rows) {
            const medicine = row.querySelector('.rx-medicine').value.trim();
            const dosage = row.querySelector('.rx-dosage').value.trim();
            const frequency = row.querySelector('.rx-frequency').value.trim();

            if (!medicine || !dosage || !frequency) {
                showMessage('Missing Information', 'Please fill in medicine, dosage, and frequency for each prescription (or remove the empty card).', 'error');
                return;
            }

            prescriptions.push({
                medicine,
                dosage,
                frequency,
                duration: row.querySelector('.rx-duration').value.trim(),
                instructions: row.querySelector('.rx-instructions').value.trim()
            });
        }
    }

    const hasFollowup = !document.getElementById('followupDetails').classList.contains('hidden');
    const followupDate = document.getElementById('followupDate').value;

    if (hasFollowup && !followupDate) {
        showMessage('Missing Information', 'Please select a follow-up date, or click "No — Skip".', 'error');
        return;
    }

    const followupData = hasFollowup ? {
        date: followupDate,
        remarks: document.getElementById('followupRemarks').value
    } : null;

    const consultationData = {
        appointment_id: document.getElementById('appointmentID').value,
        patient_id: document.getElementById('patientID').value,
        consultation_id: document.getElementById('consultationID').value,
        diagnosis: document.querySelector('[name="diagnosis"]').value,
        treatment: document.querySelector('[name="treatment"]').value,
        notes: document.querySelector('[name="notes"]').value,
        consultation_fee: document.getElementById('consultationFee').value,
        has_prescription: hasPrescription,
        prescriptions: prescriptions,
        has_followup: hasFollowup,
        followup: followupData,
        csrf_token: csrfToken
    };

    // NEW — lock the button before sending
    isSubmitting = true;
    const submitBtn = this.querySelector('button[type="submit"]');
    submitBtn.disabled = true;
    submitBtn.textContent = 'Saving...';

    try {
        const response = await fetch('../php/add/save-consultation.php', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify(consultationData)
        });

        const result = await response.json();

        // NEW — always resync the token, success or failure
        if (result.csrf_token) {
            csrfToken = result.csrf_token;
        }

        if (result.status === 'success') {
            lastSavedConsultation = { id: result.consultation_id, data: consultationData }; // NEW

            showMessage('Success', 'Consultation saved successfully! Consultation ID: ' + result.consultation_id, 'success', () => {
                if (hasPrescription) {
                    printPrescription(result.consultation_id, consultationData);
                    showReprintBanner(); // NEW
                }
                loadClinicQueue();
            });
            // NOTE: button stays disabled — it's re-enabled in loadPatientData() when the next patient loads
        } else {
            if (result.code === 'followup_date_unavailable') {
                showFollowupAlternatives(result.available_dates || []);
                showMessage('Date Unavailable', result.message + ' Please select an available alternative date and submit again.', 'error');
            } else {
                showMessage('Error', 'Error: ' + result.message, 'error');
            }
            // NEW — unlock so the doctor can retry (e.g. transient network error)
            isSubmitting = false;
            submitBtn.disabled = false;
            submitBtn.textContent = 'Save & Pass to Billing';
        }
    } catch (error) {
        console.error('Error:', error);
        showMessage('Error', 'An error occurred while saving the consultation.', 'error');
        // NEW — unlock on network/JS error too
        isSubmitting = false;
        submitBtn.disabled = false;
        submitBtn.textContent = 'Save & Pass to Billing';
    }
});

function printPrescription(consultationID, consultationData) {
    const patientName = document.getElementById('patientName').textContent;
    const patientInfo = document.getElementById('patientInfo').textContent;
    const diagnosis = consultationData.diagnosis;
    const treatment = consultationData.treatment;
    const prescriptions = consultationData.prescriptions; // now an array

    // Build one <div class="prescription-item"> block per medicine
    const prescriptionItemsHTML = prescriptions.map(prescription => `
        <div class="prescription-item">
            <div class="medicine-name">💊 ${prescription.medicine}</div>
            <div class="prescription-details">
                <div><strong>Dosage:</strong> ${prescription.dosage}</div>
                <div><strong>Frequency:</strong> ${prescription.frequency}</div>
                <div><strong>Duration:</strong> ${prescription.duration || 'As needed'}</div>
                ${prescription.instructions ? '<div><strong>Instructions:</strong> ' + prescription.instructions + '</div>' : ''}
            </div>
        </div>
    `).join('');

    const printWindow = window.open('', '', 'height=600,width=800');
    const printContent = `
        <!DOCTYPE html>
        <html>
        <head>
            <title>Prescription</title>
            <style>
                body {
                    font-family: Arial, sans-serif;
                    padding: 40px;
                    background-color: #fff;
                }
                .header {
                    text-align: center;
                    border-bottom: 2px solid #333;
                    padding-bottom: 20px;
                    margin-bottom: 30px;
                }
                .clinic-name {
                    font-size: 24px;
                    font-weight: bold;
                    color: #333;
                }
                .clinic-subtitle {
                    font-size: 12px;
                    color: #666;
                    margin-top: 5px;
                }
                .patient-section {
                    margin-bottom: 25px;
                    padding: 10px;
                    background-color: #f5f5f5;
                    border-radius: 5px;
                }
                .patient-section h3 {
                    margin: 0 0 10px 0;
                    font-size: 14px;
                    color: #333;
                }
                .patient-info {
                    font-size: 12px;
                    color: #666;
                    line-height: 1.6;
                }
                .diagnosis-treatment {
                    margin-bottom: 25px;
                    padding: 10px;
                    background-color: #f9f9f9;
                    border-left: 3px solid #0066cc;
                }
                .diagnosis-treatment strong {
                    display: block;
                    margin-bottom: 5px;
                    color: #333;
                }
                .diagnosis-treatment span {
                    font-size: 13px;
                    color: #555;
                }
                .prescription-section {
                    margin-top: 30px;
                }
                .prescription-section h2 {
                    font-size: 16px;
                    color: #333;
                    border-bottom: 2px solid #0066cc;
                    padding-bottom: 10px;
                    margin-bottom: 15px;
                }
                .prescription-item {
                    background-color: #fff;
                    border: 1px solid #ddd;
                    padding: 15px;
                    margin-bottom: 15px;
                    border-radius: 5px;
                }
                .prescription-item .medicine-name {
                    font-size: 15px;
                    font-weight: bold;
                    color: #333;
                    margin-bottom: 5px;
                }
                .prescription-details {
                    font-size: 12px;
                    color: #666;
                    line-height: 1.6;
                }
                .prescription-details div {
                    margin-bottom: 5px;
                }
                .prescription-details strong {
                    color: #333;
                }
                .footer {
                    margin-top: 40px;
                    text-align: right;
                    border-top: 1px solid #ddd;
                    padding-top: 20px;
                    font-size: 12px;
                    color: #666;
                }
                .signature-area {
                    margin-top: 40px;
                    text-align: right;
                }
                .signature-line {
                    border-top: 1px solid #333;
                    width: 200px;
                    margin-left: auto;
                    margin-top: 30px;
                }
                .doctor-sig {
                    font-size: 12px;
                    color: #333;
                    font-weight: bold;
                }
            </style>
        </head>
        <body>
            <div class="header">
                <div class="clinic-name">Orthopaedic Clinic</div>
                <div class="clinic-subtitle">Medical Prescription</div>
            </div>

            <div class="patient-section">
                <h3>PATIENT INFORMATION</h3>
                <div class="patient-info">
                    <div><strong>Name:</strong> ${patientName}</div>
                    <div><strong>Patient ID:</strong> ${patientInfo}</div>
                    <div><strong>Date:</strong> ${new Date().toLocaleDateString('en-US', { year: 'numeric', month: 'long', day: 'numeric' })}</div>
                    <div><strong>Time:</strong> ${new Date().toLocaleTimeString('en-US', { hour: '2-digit', minute: '2-digit' })}</div>
                </div>
            </div>

            <div class="diagnosis-treatment">
                <strong>DIAGNOSIS</strong>
                <span>${diagnosis}</span>
            </div>

            <div class="diagnosis-treatment">
                <strong>TREATMENT PLAN</strong>
                <span>${treatment}</span>
            </div>

            <div class="prescription-section">
                <h2>PRESCRIPTION</h2>
                ${prescriptionItemsHTML}
            </div>

            <div class="signature-area">
                <div class="doctor-sig">Doctor's Signature</div>
                <div class="signature-line"></div>
            </div>

            <div class="footer">
                <div>Consultation ID: ${consultationID}</div>
                <div style="margin-top: 5px;">Printed on: ${new Date().toLocaleString()}</div>
            </div>
        </body>
        </html>
    `;

    printWindow.document.write(printContent);
    printWindow.document.close();

    setTimeout(function () {
        printWindow.print();
    }, 250);
}

function showReprintBanner() {
    hideReprintBanner();
    const banner = document.createElement('div');
    banner.id = 'reprintBanner';
    banner.className = 'fixed bottom-6 right-6 bg-white shadow-lg border border-slate-200 rounded-xl p-4 flex items-center gap-3 z-50';
    banner.innerHTML = `
        <span class="text-sm text-slate-700">Consultation saved.</span>
        <button id="reprintBtn" type="button" class="text-sm font-semibold text-blue-600 hover:text-blue-800">
            <i class="fa-solid fa-print"></i> Print Prescription
        </button>
    `;
    document.body.appendChild(banner);
    document.getElementById('reprintBtn').addEventListener('click', () => {
        if (lastSavedConsultation) {
            printPrescription(lastSavedConsultation.id, lastSavedConsultation.data);
        }
    });
}

function hideReprintBanner() {
    const existing = document.getElementById('reprintBanner');
    if (existing) existing.remove();
}

// Load queue on page load
document.addEventListener('DOMContentLoaded', loadClinicQueue);