const initializeAvailabilityCalendars = () => {
    document.querySelectorAll('[data-availability-calendar]').forEach((calendar) => {
        const dateInput = document.getElementById(calendar.dataset.inputId);
        const monthLabel = calendar.querySelector('[data-calendar-month]');
        const daysContainer = calendar.querySelector('[data-calendar-days]');
        const errorElement = calendar.querySelector('[data-calendar-error]');
        const previousButton = calendar.querySelector('[data-calendar-prev]');
        const nextButton = calendar.querySelector('[data-calendar-next]');
        if (!dateInput || !monthLabel || !daysContainer || !errorElement || !previousButton || !nextButton) {
            return;
        }

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

                if (isoDate === dateToIso(today)) {
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

        dateInput.addEventListener('change', () => {
            if (dateInput.value) {
                const [y, m] = dateInput.value.split('-').map(Number);
                if (y !== displayedMonth.getFullYear() || m - 1 !== displayedMonth.getMonth()) {
                    displayedMonth = new Date(y, m - 1, 1);
                    loadMonth();
                    return;
                }
            }
            renderDays();
        });

        loadMonth();
    });
};

initializeAvailabilityCalendars();