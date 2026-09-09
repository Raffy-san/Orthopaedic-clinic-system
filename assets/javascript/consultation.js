let currentAppointmentData = null;
let csrfToken = window.csrfToken || "";
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
            btn.addEventListener('click', function () {
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
async function loadPatientData(appointmentData) {
    currentAppointmentData = appointmentData;

    document.getElementById('patientName').textContent = appointmentData.FirstName + ' ' + appointmentData.LastName;
    document.getElementById('patientInfo').textContent = appointmentData.PatientCode + ' - ' + (appointmentData.PatientID ? 'Existing Patient' : 'New Patient');
    document.getElementById('chiefComplaint').textContent = appointmentData.ChiefComplaint || 'Not recorded';
    document.getElementById('lastVisit').textContent = appointmentData.LastVisitDate ? new Date(appointmentData.LastVisitDate).toLocaleDateString() : 'First visit';
    document.getElementById('allergies').textContent = appointmentData.Allergies || 'None recorded';

    document.getElementById('appointmentID').value = appointmentData.AppointmentID;
    document.getElementById('patientID').value = appointmentData.PatientID;

    document.getElementById('patientHeaderContainer').classList.remove('hidden');
    document.getElementById('noPatientSelected').classList.add('hidden');
    document.getElementById('consultationForm').classList.remove('hidden');

    // Load consultation history
    await loadConsultationHistory(appointmentData.PatientID);

    // Reset form
    document.getElementById('consultationForm').reset();
    document.getElementById('prescriptionDetails').classList.add('hidden');
}

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

// Handle follow-up buttons
document.getElementById('addFollowupBtn').addEventListener('click', () => {
    document.getElementById('followupDetails').classList.remove('hidden');
});

document.getElementById('skipFollowupBtn').addEventListener('click', () => {
    document.getElementById('followupDetails').classList.add('hidden');
    document.getElementById('followupDate').value = '';
    document.getElementById('followupRemarks').value = '';
});

document.getElementById('consultationForm').addEventListener('submit', async function (e) {
    e.preventDefault();

    const hasPrescription = !document.getElementById('prescriptionDetails').classList.contains('hidden');
    const prescriptionData = hasPrescription ? {
        medicine: document.getElementById('prescriptionMedicine').value,
        dosage: document.getElementById('prescriptionDosage').value,
        frequency: document.getElementById('prescriptionFrequency').value,
        duration: document.getElementById('prescriptionDuration').value,
        instructions: document.getElementById('prescriptionInstructions').value
    } : null;

    // NEW: follow-up data
    const hasFollowup = !document.getElementById('followupDetails').classList.contains('hidden');
    const followupDate = document.getElementById('followupDate').value;

    if (hasFollowup && !followupDate) {
        alert('Please select a follow-up date, or click "No — Skip".');
        return;
    }

    const followupData = hasFollowup ? {
        date: followupDate,
        remarks: document.getElementById('followupRemarks').value
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
        has_followup: hasFollowup,       // NEW
        followup: followupData,          // NEW
        csrf_token: csrfToken
    };

    try {
        const response = await fetch('../php/add/save-consultation.php', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify(consultationData)
        });

        const result = await response.json();

        if (result.status === 'success') {
            csrfToken = result.csrf_token;
            alert('Consultation saved successfully!\nConsultation ID: ' + result.consultation_id);

            if (hasPrescription) {
                printPrescription(result.consultation_id, consultationData);
            }

            loadClinicQueue();
        } else {
            alert('Error: ' + result.message);
        }
    } catch (error) {
        console.error('Error:', error);
        alert('An error occurred while saving the consultation.');
    }
});

// Function to print prescription
function printPrescription(consultationID, consultationData) {
    const patientName = document.getElementById('patientName').textContent;
    const patientInfo = document.getElementById('patientInfo').textContent;
    const diagnosis = consultationData.diagnosis;
    const treatment = consultationData.treatment;
    const prescription = consultationData.prescription;

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
                <div class="prescription-item">
                    <div class="medicine-name">💊 ${prescription.medicine}</div>
                    <div class="prescription-details">
                        <div><strong>Dosage:</strong> ${prescription.dosage}</div>
                        <div><strong>Frequency:</strong> ${prescription.frequency}</div>
                        <div><strong>Duration:</strong> ${prescription.duration || 'As needed'}</div>
                        ${prescription.instructions ? '<div><strong>Instructions:</strong> ' + prescription.instructions + '</div>' : ''}
                    </div>
                </div>
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

    // Wait a moment for the content to load, then print
    setTimeout(function () {
        printWindow.print();
    }, 250);
}

// Load queue on page load
document.addEventListener('DOMContentLoaded', loadClinicQueue);