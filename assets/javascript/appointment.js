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

document.querySelectorAll('.open-modal').forEach((trigger) => {
    trigger.addEventListener('click', () => {
        openModal(document.getElementById(trigger.dataset.modal));
    });
});

const selectedAppointmentTime = document.getElementById('selectedAppointmentTime');
const appointmentDate = document.getElementById('appointmentDate');
const selectedAppointmentDate = document.getElementById('selectedAppointmentDate');
const appointmentTimeCalendar = document.getElementById('appointmentTimeCalendar');
const appointmentDateMessage = document.getElementById('appointmentDateMessage');
const availableSlotCount = document.getElementById('availableSlotCount');
const bookingModal = document.getElementById('bookAppointmentModal');

document.getElementById('startBookingButton').addEventListener('click', () => {
    appointmentDate.focus();
    appointmentDate.scrollIntoView({ behavior: 'smooth', block: 'center' });
});

const resetTimeSlots = () => {
    document.querySelectorAll('.time-slot').forEach((slot) => {
        slot.disabled = false;
        slot.classList.remove('bg-red-100', 'text-red-700', 'bg-sky-600', 'text-white');
        slot.classList.add('bg-slate-100', 'text-slate-700');
    });
    selectedAppointmentTime.value = '';
};

const loadTimeSlots = async () => {
    resetTimeSlots();
    selectedAppointmentDate.value = appointmentDate.value;
    if (!appointmentDate.value) {
        appointmentTimeCalendar.classList.add('hidden');
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
        appointmentDateMessage.textContent = `Available appointments for ${new Date(`${appointmentDate.value}T00:00:00`).toLocaleDateString()}.`;
        availableSlotCount.textContent = `${availableCount} available`;
        appointmentTimeCalendar.classList.remove('hidden');
    } catch (error) {
        appointmentTimeCalendar.classList.add('hidden');
        alert(error.message);
    }
};

appointmentDate.addEventListener('change', loadTimeSlots);

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
        selectedAppointmentTime.value = slot.dataset.time;
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

document.getElementById("addAppointmentForm").addEventListener("submit", (event) => {
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
                form.querySelector('[name="csrf_token"]').value = csrfToken;
            }
            if (data.status === "success") {
                alert(data.message);
                location.reload();
            } else {
                alert(data.message);
            }
        })
        .catch(error => {
            console.error("Error:", error);
            alert("An error occurred while booking the appointment. Please try again.");
        });
});