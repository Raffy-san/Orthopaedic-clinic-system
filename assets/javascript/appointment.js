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

const initializeAvailabilityCalendars = () => {
    document.querySelectorAll('[data-availability-calendar]').forEach((calendar) => {
        const dateInput = document.getElementById(calendar.dataset.inputId);
        const monthLabel = calendar.querySelector('[data-calendar-month]');
        const daysContainer = calendar.querySelector('[data-calendar-days]');
        const errorElement = calendar.querySelector('[data-calendar-error]');
        const previousButton = calendar.querySelector('[data-calendar-prev]');
        const nextButton = calendar.querySelector('[data-calendar-next]');
        const today = new Date();
        today.setHours(0, 0, 0, 0);
        let displayedMonth = new Date(today.getFullYear(), today.getMonth(), 1);
        let closedDates = {};
        let requestId = 0;

        const dateToIso = (date) => {
            const year = date.getFullYear();
            const month = String(date.getMonth() + 1).padStart(2, '0');
            const day = String(date.getDate()).padStart(2, '0');
            return `${year}-${month}-${day}`;
        };

        const renderDays = () => {
            monthLabel.textContent = displayedMonth.toLocaleDateString(undefined, {
                month: 'long',
                year: 'numeric'
            });
            daysContainer.replaceChildren();

            const firstDayOffset = (displayedMonth.getDay() + 6) % 7;
            const daysInMonth = new Date(
                displayedMonth.getFullYear(),
                displayedMonth.getMonth() + 1,
                0
            ).getDate();

            for (let blank = 0; blank < firstDayOffset; blank += 1) {
                const spacer = document.createElement('span');
                spacer.setAttribute('aria-hidden', 'true');
                daysContainer.append(spacer);
            }

            for (let day = 1; day <= daysInMonth; day += 1) {
                const date = new Date(displayedMonth.getFullYear(), displayedMonth.getMonth(), day);
                const isoDate = dateToIso(date);
                const closure = closedDates[isoDate];
                const isPast = date < today;
                const isSelected = dateInput.value === isoDate;
                const button = document.createElement('button');

                button.type = 'button';
                button.textContent = String(day);
                button.className = 'relative rounded-lg px-1 py-2 text-sm font-medium transition';
                button.setAttribute('role', 'gridcell');
                button.setAttribute('aria-label', date.toLocaleDateString(undefined, {
                    weekday: 'long',
                    year: 'numeric',
                    month: 'long',
                    day: 'numeric'
                }));

                if (closure) {
                    button.disabled = true;
                    button.title = closure.reason || (closure.type === 'unavailable'
                        ? 'Doctor unavailable'
                        : 'Clinic closed');
                    button.setAttribute('aria-label', `${button.getAttribute('aria-label')}. ${button.title}`);
                    if (closure.type === 'unavailable') {
                        button.classList.add('bg-red-100', 'text-red-700', 'cursor-not-allowed');
                    } else {
                        button.classList.add('bg-slate-100', 'text-slate-400', 'cursor-not-allowed');
                    }
                } else if (isPast) {
                    button.disabled = true;
                    button.title = 'Past date';
                    button.classList.add('text-slate-300', 'cursor-not-allowed');
                } else if (isSelected) {
                    button.classList.add('bg-sky-600', 'text-white');
                    button.setAttribute('aria-pressed', 'true');
                } else {
                    button.classList.add('bg-white', 'text-slate-700', 'hover:bg-emerald-50', 'border', 'border-emerald-100');
                }

                if (dateToIso(date) === dateToIso(today)) {
                    button.classList.add('ring-2', 'ring-sky-300');
                }

                button.addEventListener('click', () => {
                    if (button.disabled) {
                        return;
                    }
                    dateInput.value = isoDate;
                    renderDays();
                    dateInput.dispatchEvent(new Event('change', { bubbles: true }));
                });
                daysContainer.append(button);
            }

            const currentMonth = new Date(today.getFullYear(), today.getMonth(), 1);
            previousButton.disabled = displayedMonth <= currentMonth;
            previousButton.classList.toggle('opacity-40', previousButton.disabled);
            previousButton.classList.toggle('cursor-not-allowed', previousButton.disabled);
        };

        const loadMonth = async () => {
            const currentRequestId = ++requestId;
            const yearMonth = `${displayedMonth.getFullYear()}-${String(displayedMonth.getMonth() + 1).padStart(2, '0')}`;
            errorElement.classList.add('hidden');
            errorElement.textContent = '';
            daysContainer.replaceChildren();

            try {
                const response = await fetch(`../php/add/book-appointment.php?month=${encodeURIComponent(yearMonth)}`);
                const data = await response.json();
                if (!response.ok || data.status !== 'success') {
                    throw new Error(data.message || 'Unable to load doctor availability.');
                }
                if (currentRequestId !== requestId) {
                    return;
                }
                closedDates = data.closed_dates || {};
                renderDays();
            } catch (error) {
                if (currentRequestId !== requestId) {
                    return;
                }
                errorElement.textContent = error.message || 'Unable to load doctor availability.';
                errorElement.classList.remove('hidden');
            }
        };

        previousButton.addEventListener('click', () => {
            displayedMonth = new Date(displayedMonth.getFullYear(), displayedMonth.getMonth() - 1, 1);
            loadMonth();
        });
        nextButton.addEventListener('click', () => {
            displayedMonth = new Date(displayedMonth.getFullYear(), displayedMonth.getMonth() + 1, 1);
            loadMonth();
        });
        dateInput.addEventListener('change', renderDays);

        loadMonth();
    });
};

initializeAvailabilityCalendars();

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

        const availableTimes = new Set(data.available_times || []);
        document.querySelectorAll('.time-slot').forEach((slot) => {
            slot.classList.toggle('hidden', !availableTimes.has(slot.dataset.time));
        });
        document.querySelectorAll('[data-time-slot-group]').forEach((group) => {
            group.classList.toggle('hidden', !group.querySelector('.time-slot:not(.hidden)'));
        });

        // The clinic/doctor is fully closed on this date — show a clear message
        // instead of just quietly marking every slot as taken one by one.
        if (data.closed) {
            document.querySelectorAll('.time-slot').forEach((slot) => {
                slot.disabled = true;
            });
            if (appointmentDateMessage) {
                appointmentDateMessage.textContent = data.closed_message || 'The clinic is closed on this date.';
                appointmentDateMessage.classList.add('text-red-600');
            }
            if (availableSlotCount) {
                availableSlotCount.textContent = '0 available';
            }
            if (appointmentTimeCalendar) {
                appointmentTimeCalendar.classList.remove('hidden');
            }
            return;
        }

        if (appointmentDateMessage) {
            appointmentDateMessage.classList.remove('text-red-600');
        }

        const takenTimes = new Set(data.taken_times || []);
        document.querySelectorAll('.time-slot').forEach((slot) => {
            if (!slot.classList.contains('hidden') && takenTimes.has(slot.dataset.time)) {
                slot.disabled = true;
                slot.classList.remove('bg-slate-100', 'text-slate-700');
                slot.classList.add('bg-red-100', 'text-red-700');
            }
        });

        const availableCount = [...document.querySelectorAll('.time-slot')]
            .filter((slot) => !slot.classList.contains('hidden') && !slot.disabled).length;
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