(function () {
    let csrfToken = window.csrfToken || "";
    const printBtn = document.getElementById('printReportBtn');
    const modal = document.getElementById('printPasswordModal');
    const input = document.getElementById('printPasswordInput');
    const errorEl = document.getElementById('printPasswordError');
    const confirmBtn = document.getElementById('printPasswordConfirm');
    const cancelBtn = document.getElementById('printPasswordCancel');

    function openModal() {
        errorEl.classList.add('hidden');
        input.value = '';
        modal.classList.remove('hidden');
        modal.classList.add('flex');
        input.focus();
    }
    function closeModal() {
        modal.classList.add('hidden');
        modal.classList.remove('flex');
    }

    printBtn.addEventListener('click', function () {
        if (printBtn.dataset.reportType === 'financial') {
            openModal();
        } else {
            window.print();
        }
    });

    cancelBtn.addEventListener('click', closeModal);
    modal.addEventListener('click', function (e) {
        if (e.target === modal) closeModal();
    });

    async function attemptVerify() {
        const password = input.value;
        if (!password) {
            errorEl.textContent = 'Please enter your password.';
            errorEl.classList.remove('hidden');
            return;
        }
        confirmBtn.disabled = true;
        confirmBtn.textContent = 'Checking...';
        try {
            const res = await fetch('verify-password.php', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify({ password, csrf_token: csrfToken })
            });
            const data = await res.json();
            if (data.success) {
                closeModal();
                window.print();
            } else {
                errorEl.textContent = data.message || 'Incorrect password.';
                errorEl.classList.remove('hidden');
            }
        } catch (err) {
            errorEl.textContent = 'Something went wrong. Try again.';
            errorEl.classList.remove('hidden');
        } finally {
            confirmBtn.disabled = false;
            confirmBtn.textContent = 'Confirm & Print';
        }
    }

    confirmBtn.addEventListener('click', attemptVerify);
    input.addEventListener('keydown', function (e) {
        if (e.key === 'Enter') attemptVerify();
    });
})();