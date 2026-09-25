let csrfToken = window.csrfToken || "";

const updateBodyScroll = () => {
    const hasOpenModal = document.querySelector('.modal.flex');
    document.body.classList.toggle('overflow-hidden', Boolean(hasOpenModal));
};

const openModal = (modal) => {
    if (!modal) {
        return;
    }

    modal.classList.remove("hidden");
    modal.classList.add("flex");
    updateBodyScroll();
};

const closeModal = (modal) => {
    if (!modal) {
        return;
    }

    modal.classList.add("hidden");
    modal.classList.remove("flex");
    updateBodyScroll();
};

function showMessage(title, message, type = "success", callback = null) {
    const modal = document.getElementById("messageModal");
    if (!modal) {
        // Fallback so pages without the message modal markup don't lose the message entirely.
        console.warn("messageModal not found on this page:", title, message);
        if (callback) callback();
        return;
    }

    const titleElement = document.getElementById("messageTitle");
    const textElement = document.getElementById("messageText");
    const closeBtn = document.getElementById("closeMessageBtn");

    if (titleElement) {
        titleElement.textContent = title;
        titleElement.classList.toggle("text-green-600", type === "success");
        titleElement.classList.toggle("text-red-600", type !== "success");
    }
    if (textElement) {
        textElement.textContent = message;
    }

    openModal(modal);

    if (closeBtn) {
        closeBtn.onclick = () => {
            closeModal(modal);
            if (callback) callback();
        };
    }
}

function showConfirm(title, message, onConfirm) {
    const modal = document.getElementById("confirmModal");
    if (!modal) {
        // No confirm modal on this page — fall back to a native confirm so the action still works.
        console.warn("confirmModal not found on this page:", title, message);
        if (window.confirm(message) && onConfirm) onConfirm();
        return;
    }

    const titleElement = document.getElementById("confirmTitle");
    const textElement = document.getElementById("confirmText");
    const okBtn = document.getElementById("confirmOkBtn");
    const cancelBtn = document.getElementById("confirmCancelBtn");

    if (titleElement) titleElement.textContent = title;
    if (textElement) textElement.textContent = message;

    openModal(modal);

    if (okBtn) {
        okBtn.onclick = () => {
            closeModal(modal);
            if (onConfirm) onConfirm();
        };
    }

    if (cancelBtn) {
        cancelBtn.onclick = () => {
            closeModal(modal);
        };
    }
}

document.querySelectorAll('.open-modal').forEach((trigger) => {
    trigger.addEventListener('click', () => {
        openModal(document.getElementById(trigger.dataset.modal));
    });
});

// --- Booking modal / time-slot picker ---
// These elements only exist on pages that render the "Book Appointment" form
// (e.g. an appointments page). On pages like dashboard.php they won't be present,
// so every reference below is guarded.
const selectedAppointmentTime = document.getElementById('selectedAppointmentTime');
const appointmentDate = document.getElementById('appointmentDate');
const selectedAppointmentDate = document.getElementById('selectedAppointmentDate');
const appointmentTimeCalendar = document.getElementById('appointmentTimeCalendar');
const appointmentDateMessage = document.getElementById('appointmentDateMessage');
const availableSlotCount = document.getElementById('availableSlotCount');
const bookingModal = document.getElementById('bookAppointmentModal');
const addAppointmentForm = document.getElementById('addAppointmentForm');

const resetTimeSlots = () => {
    document.querySelectorAll('.time-slot').forEach((slot) => {
        slot.disabled = false;
        slot.classList.remove('bg-red-100', 'text-red-700', 'bg-sky-600', 'text-white');
        slot.classList.add('bg-slate-100', 'text-slate-700');
    });
    if (selectedAppointmentTime) {
        selectedAppointmentTime.value = '';
    }
};

const loadTimeSlots = async () => {
    resetTimeSlots();

    if (!appointmentDate) {
        return;
    }

    if (selectedAppointmentDate) {
        selectedAppointmentDate.value = appointmentDate.value;
    }

    if (!appointmentDate.value) {
        if (appointmentTimeCalendar) {
            appointmentTimeCalendar.classList.add('hidden');
        }
        return;
    }

    try {
        const response = await fetch(`../php/add/book-appointment.php?date=${encodeURIComponent(appointmentDate.value)}`);
        const data = await response.json();
        if (data.status !== 'success') {
            throw new Error(data.message || 'Unable to load appointment times.');
        }

        const takenTimes = new Set(data.taken_times);
        document.querySelectorAll('.time-slot').forEach((slot) => {
            if (takenTimes.has(slot.dataset.time)) {
                slot.disabled = true;
                slot.classList.remove('bg-slate-100', 'text-slate-700');
                slot.classList.add('bg-red-100', 'text-red-700');
            }
        });

        const availableCount = document.querySelectorAll('.time-slot:not(:disabled)').length;
        if (appointmentDateMessage) {
            appointmentDateMessage.textContent = `Available appointments for ${new Date(`${appointmentDate.value}T00:00:00`).toLocaleDateString()}.`;
        }
        if (availableSlotCount) {
            availableSlotCount.textContent = `${availableCount} available`;
        }
        if (appointmentTimeCalendar) {
            appointmentTimeCalendar.classList.remove('hidden');
        }
    } catch (error) {
        if (appointmentTimeCalendar) {
            appointmentTimeCalendar.classList.add('hidden');
        }
        showMessage('Error', error.message, 'error');
    }
};

if (appointmentDate) {
    appointmentDate.addEventListener('change', loadTimeSlots);
}

document.querySelectorAll('.time-slot').forEach((slot) => {
    slot.addEventListener('click', () => {
        document.querySelectorAll('.time-slot').forEach((availableSlot) => {
            if (availableSlot.disabled) {
                return;
            }

            availableSlot.classList.remove('bg-sky-600', 'text-white');
            availableSlot.classList.add('bg-slate-100', 'text-slate-700');
        });

        slot.classList.remove('bg-slate-100', 'text-slate-700');
        slot.classList.add('bg-sky-600', 'text-white');

        // Convert 24-hour format to 12-hour format
        const { time12, meridiem } = convertTo12HourFormat(slot.dataset.time);
        if (selectedAppointmentTime) {
            selectedAppointmentTime.value = time12;
        }

        // Set meridiem field if it exists
        const meridiem_field = document.getElementById('meridiem') || document.querySelector('[name="meridiem"]');
        if (meridiem_field) {
            meridiem_field.value = meridiem;
        }

        openModal(bookingModal);
    });
});

document.querySelectorAll('.modal').forEach((modal) => {
    modal.querySelectorAll('.close').forEach((closeButton) => {
        closeButton.addEventListener('click', () => closeModal(modal));
    });

    modal.addEventListener('click', (event) => {
        if (event.target === modal) {
            closeModal(modal);
        }
    });
});

document.addEventListener('keydown', (event) => {
    if (event.key === 'Escape') {
        document.querySelectorAll('.modal.flex').forEach(closeModal);
    }
});

if (addAppointmentForm) {
    addAppointmentForm.addEventListener("submit", (event) => {
        event.preventDefault();

        const form = event.currentTarget;
        const formData = new FormData(form);
        formData.set("csrf_token", csrfToken);

        fetch("../php/add/book-appointment.php", {
            method: "POST",
            body: formData
        })
            .then(response => response.json())
            .then(data => {
                if (data.csrf_token) {
                    csrfToken = data.csrf_token;
                    const csrfField = form.querySelector('[name="csrf_token"]');
                    if (csrfField) {
                        csrfField.value = csrfToken;
                    }
                }
                if (data.status === "success") {
                    showMessage('Success', data.message, 'success', () => {
                        location.reload();
                    });
                } else {
                    showMessage('Error', data.message, 'error');
                }
            })
            .catch(error => {
                console.error("Error:", error);
                showMessage('Error', 'An error occurred while booking the appointment. Please try again.', 'error');
            });
    });
}

// --- Pending appointment approve/decline buttons (used on dashboard.php) ---
document.querySelectorAll('.confirm-btn').forEach(btn => {
    btn.addEventListener('click', async function () {
        const appointmentId = this.getAttribute('data-appointment-id');
        await updateAppointmentStatus(appointmentId, 'Confirmed');
    });
});

document.querySelectorAll('.decline-btn').forEach(btn => {
    btn.addEventListener('click', async function () {
        const appointmentId = this.getAttribute('data-appointment-id');
        await updateAppointmentStatus(appointmentId, 'Cancelled');
    });
});

document.querySelectorAll('.cancel-confirmed-btn').forEach(btn => {
    btn.addEventListener('click', function () {
        const appointmentId = this.getAttribute('data-appointment-id');

        showConfirm(
            'Cancel Appointment',
            'Cancel this confirmed appointment?',
            async () => {
                await updateAppointmentStatus(appointmentId, 'Cancelled');
            }
        );
    });
});

async function updateAppointmentStatus(appointmentId, status) {
    try {
        const response = await fetch('../php/update/update-appointment-status.php', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
            },
            body: JSON.stringify({
                appointment_id: appointmentId,
                status: status,
                csrf_token: window.csrfToken
            })
        });

        const data = await response.json();

        if (data.status === 'success') {
            showMessage('Success', data.message, 'success', () => {
                location.reload();
            });
        } else {
            showMessage('Error', 'Error: ' + data.message, 'error');
        }
    } catch (error) {
        console.error('Error:', error);
        showMessage('Error', 'An error occurred while updating the appointment.', 'error');
    }
}