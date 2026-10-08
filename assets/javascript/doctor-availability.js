// Relies on showMessage()/showConfirm() and window.csrfToken from appointment.js,
// loaded before this file.

document.addEventListener('DOMContentLoaded', () => {
    const addForm = document.getElementById('addUnavailabilityForm');
    const errorEl = document.getElementById('addUnavailabilityError');
    const listContainer = document.getElementById('unavailabilityList');

    if (addForm) {
        addForm.addEventListener('submit', async (e) => {
            e.preventDefault();
            errorEl.classList.add('hidden');

            const formData = new FormData(addForm);
            const submitBtn = addForm.querySelector('button[type="submit"]');
            submitBtn.disabled = true;

            try {
                const res = await fetch('../php/add/add-unavailability.php', {
                    method: 'POST',
                    body: formData
                });
                const data = await res.json();

                if (data.csrf_token) {
                    window.csrfToken = data.csrf_token;
                    addForm.querySelector('[name="csrf_token"]').value = data.csrf_token;
                }

                if (data.status === 'success') {
                    showMessage('Success', data.message, 'success', () => {
                        location.reload();
                    });
                } else {
                    errorEl.textContent = data.message || 'Unable to add this date.';
                    errorEl.classList.remove('hidden');
                }
            } catch (err) {
                errorEl.textContent = 'Something went wrong. Please try again.';
                errorEl.classList.remove('hidden');
            } finally {
                submitBtn.disabled = false;
            }
        });
    }

    if (listContainer) {
        listContainer.querySelectorAll('.delete-unavailability-btn').forEach((btn) => {
            btn.addEventListener('click', () => {
                const id = btn.dataset.id;

                showConfirm(
                    'Remove Unavailable Date',
                    'This date will become bookable again. Continue?',
                    async () => {
                        try {
                            const res = await fetch('../php/delete/delete-unavailability.php', {
                                method: 'POST',
                                headers: { 'Content-Type': 'application/json' },
                                body: JSON.stringify({
                                    unavailability_id: id,
                                    csrf_token: window.csrfToken
                                })
                            });
                            const data = await res.json();

                            if (data.status === 'success') {
                                showMessage('Success', data.message, 'success', () => {
                                    location.reload();
                                });
                            } else {
                                showMessage('Error', data.message, 'error');
                            }
                        } catch (err) {
                            showMessage('Error', 'Unable to remove this date.', 'error');
                        }
                    }
                );
            });
        });
    }
});