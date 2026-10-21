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

const appointmentDate = document.getElementById('appointmentDate');
const selectedAppointmentDate = document.getElementById('selectedAppointmentDate');
const appointmentDateMessage = document.getElementById('appointmentDateMessage');
const bookingModal = document.getElementById('bookAppointmentModal');
const addAppointmentForm = document.getElementById('addAppointmentForm');

let appointmentDateRequest = 0;
if (appointmentDate) {
    appointmentDate.addEventListener('change', async () => {
        const date = appointmentDate.value;
        const requestId = ++appointmentDateRequest;
        if (selectedAppointmentDate) {
            selectedAppointmentDate.value = date;
        }
        if (!date) {
            return;
        }

        if (appointmentDateMessage) {
            appointmentDateMessage.textContent = 'Checking date availability...';
            appointmentDateMessage.classList.remove('text-red-600');
        }

        try {
            const response = await fetch(`../php/add/book-appointment.php?date=${encodeURIComponent(date)}`);
            const data = await response.json();
            if (!response.ok || data.status !== 'success') {
                throw new Error(data.message || 'Unable to check date availability.');
            }
            if (requestId !== appointmentDateRequest || date !== appointmentDate.value) {
                return;
            }

            const isFull = Number(data.booked_count) >= Number(data.limit);
            if (data.closed || isFull) {
                if (appointmentDateMessage) {
                    appointmentDateMessage.textContent = data.closed
                        ? (data.closed_message || 'The clinic is closed on this date.')
                        : `This date is fully booked (${data.limit} appointments). Please choose another date.`;
                    appointmentDateMessage.classList.add('text-red-600');
                }
                return;
            }

            if (appointmentDateMessage) {
                appointmentDateMessage.textContent = `Selected date: ${new Date(`${date}T00:00:00`).toLocaleDateString()}.`;
            }
            openModal(bookingModal);
        } catch (error) {
            if (requestId === appointmentDateRequest) {
                if (appointmentDateMessage) {
                    appointmentDateMessage.textContent = error.message || 'Unable to check date availability.';
                    appointmentDateMessage.classList.add('text-red-600');
                }
                showMessage('Error', error.message, 'error');
            }
        }
    });
}

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