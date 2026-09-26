// Reschedule Appointment modal logic.
// Reuses the shared openModal/closeModal/showMessage/showConfirm helpers
// and CSRF token defined in appointment.js — load this file AFTER appointment.js.

let rescheduleSelectedTime = null;

const rescheduleModal = document.getElementById('rescheduleAppointmentModal');
const rescheduleForm = document.getElementById('rescheduleAppointmentForm');
const rescheduleDateInput = document.getElementById('rescheduleDate');
const rescheduleTimeCalendar = document.getElementById('rescheduleTimeCalendar');
const rescheduleTimeMessage = document.getElementById('rescheduleTimeMessage');
const rescheduleSelectedTimeInput = document.getElementById('rescheduleSelectedTime');

const resetRescheduleTimeSlots = () => {
    document.querySelectorAll('.reschedule-time-slot').forEach((slot) => {
        slot.disabled = false;
        slot.classList.remove('bg-red-100', 'text-red-700', 'bg-sky-600', 'text-white');
        slot.classList.add('bg-slate-100', 'text-slate-700');
    });
    rescheduleSelectedTime = null;
    if (rescheduleSelectedTimeInput) {
        rescheduleSelectedTimeInput.value = '';
    }
};

// Open the modal when a Reschedule button is clicked, pre-filling context
document.querySelectorAll('.reschedule-btn').forEach((btn) => {
    btn.addEventListener('click', () => {
        resetRescheduleTimeSlots();

        document.getElementById('rescheduleAppointmentId').value = btn.dataset.appointmentId;
        document.getElementById('rescheduleModalPatientName').textContent = btn.dataset.patientName;
        document.getElementById('rescheduleModalCurrentSlot').textContent =
            `${btn.dataset.currentDate} at ${btn.dataset.currentTime}`;

        if (rescheduleDateInput) {
            rescheduleDateInput.value = '';
        }
        if (rescheduleTimeCalendar) {
            rescheduleTimeCalendar.classList.add('hidden');
        }
        if (rescheduleTimeMessage) {
            rescheduleTimeMessage.textContent = 'Choose a date first to see available slots.';
            rescheduleTimeMessage.classList.remove('hidden');
        }

        openModal(rescheduleModal);
    });
});

// When a new date is picked, load which slots are already taken
// (reuses the same endpoint the booking calendar uses)
if (rescheduleDateInput) {
    rescheduleDateInput.addEventListener('change', async () => {
        const date = rescheduleDateInput.value;
        resetRescheduleTimeSlots();

        if (!date) {
            return;
        }

        if (rescheduleTimeMessage) {
            rescheduleTimeMessage.textContent = 'Loading availability...';
        }
        if (rescheduleTimeCalendar) {
            rescheduleTimeCalendar.classList.add('hidden');
        }

        try {
            const response = await fetch(`../php/add/book-appointment.php?date=${encodeURIComponent(date)}`);
            const data = await response.json();

            if (data.status !== 'success') {
                throw new Error(data.message || 'Unable to load availability.');
            }

            const takenTimes = new Set(data.taken_times);
            document.querySelectorAll('.reschedule-time-slot').forEach((slot) => {
                if (takenTimes.has(slot.dataset.time)) {
                    slot.disabled = true;
                    slot.classList.remove('bg-slate-100', 'text-slate-700');
                    slot.classList.add('bg-red-100', 'text-red-700');
                }
            });

            if (rescheduleTimeMessage) {
                rescheduleTimeMessage.classList.add('hidden');
            }
            if (rescheduleTimeCalendar) {
                rescheduleTimeCalendar.classList.remove('hidden');
            }
        } catch (error) {
            if (rescheduleTimeCalendar) {
                rescheduleTimeCalendar.classList.add('hidden');
            }
            showMessage('Error', error.message, 'error');
        }
    });
}

// Slot selection
document.querySelectorAll('.reschedule-time-slot').forEach((slot) => {
    slot.addEventListener('click', () => {
        if (slot.disabled) return;

        document.querySelectorAll('.reschedule-time-slot').forEach((s) => {
            if (s.disabled) return;
            s.classList.remove('bg-sky-600', 'text-white');
            s.classList.add('bg-slate-100', 'text-slate-700');
        });

        slot.classList.remove('bg-slate-100', 'text-slate-700');
        slot.classList.add('bg-sky-600', 'text-white');

        rescheduleSelectedTime = slot.dataset.time; // e.g. "08:00 AM"
        if (rescheduleSelectedTimeInput) {
            rescheduleSelectedTimeInput.value = rescheduleSelectedTime;
        }
    });
});

// Submit
if (rescheduleForm) {
    rescheduleForm.addEventListener('submit', (event) => {
        event.preventDefault();

        if (!rescheduleSelectedTime) {
            showMessage('Error', 'Please select a new time slot.', 'error');
            return;
        }

        const appointmentId = document.getElementById('rescheduleAppointmentId').value;
        const newDate = rescheduleDateInput ? rescheduleDateInput.value : '';

        if (!newDate) {
            showMessage('Error', 'Please select a new date.', 'error');
            return;
        }

        fetch('../php/update/reschedule-appointment.php', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
            },
            body: JSON.stringify({
                appointment_id: appointmentId,
                new_appointment_date: newDate,
                new_appointment_time: rescheduleSelectedTime,
                csrf_token: window.csrfToken
            })
        })
            .then((response) => response.json())
            .then((data) => {
                if (data.status === 'success') {
                    closeModal(rescheduleModal);
                    showMessage('Success', data.message, 'success', () => {
                        location.reload();
                    });
                } else {
                    showMessage('Error', data.message, 'error');
                }
            })
            .catch((error) => {
                console.error('Error:', error);
                showMessage('Error', 'An error occurred while rescheduling the appointment.', 'error');
            });
    });
}