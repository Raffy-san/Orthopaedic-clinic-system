// Reschedule Appointment modal logic.
// Reuses the shared openModal/closeModal/showMessage helpers and CSRF token
// defined in appointment.js — load this file AFTER appointment.js.

const rescheduleModal = document.getElementById('rescheduleAppointmentModal');
const rescheduleForm = document.getElementById('rescheduleAppointmentForm');
const rescheduleDateInput = document.getElementById('rescheduleDate');

document.querySelectorAll('.reschedule-btn').forEach((button) => {
    button.addEventListener('click', () => {
        document.getElementById('rescheduleAppointmentId').value = button.dataset.appointmentId;
        document.getElementById('rescheduleModalPatientName').textContent = button.dataset.patientName;

        if (rescheduleDateInput) {
            rescheduleDateInput.value = '';
            rescheduleDateInput.dispatchEvent(new Event('change', { bubbles: true }));
        }

        openModal(rescheduleModal);
    });
});

if (rescheduleForm) {
    rescheduleForm.addEventListener('submit', async (event) => {
        event.preventDefault();

        const appointmentId = document.getElementById('rescheduleAppointmentId').value;
        const newDate = rescheduleDateInput ? rescheduleDateInput.value : '';
        if (!newDate) {
            showMessage('Error', 'Please select a new date.', 'error');
            return;
        }

        try {
            const response = await fetch('../php/update/reschedule-appointment.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                },
                body: JSON.stringify({
                    appointment_id: appointmentId,
                    new_appointment_date: newDate,
                    csrf_token: window.csrfToken
                })
            });
            const data = await response.json();

            if (!response.ok || data.status !== 'success') {
                throw new Error(data.message || 'Unable to reschedule the appointment.');
            }

            closeModal(rescheduleModal);
            showMessage('Success', data.message, 'success', () => {
                location.reload();
            });
        } catch (error) {
            showMessage('Error', error.message || 'An error occurred while rescheduling the appointment.', 'error');
        }
    });
}
