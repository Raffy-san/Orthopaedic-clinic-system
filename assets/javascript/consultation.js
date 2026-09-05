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
function loadPatientData(appointmentData) {
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