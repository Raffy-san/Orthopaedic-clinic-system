(function () {
    let csrfToken = window.csrfToken || "";
    const printBtn = document.getElementById('printReportBtn');
    const unlockBtn = document.getElementById('unlockFinancialReportBtn');
    const financialModal = document.getElementById('financialPasswordModal');
    const financialInput = document.getElementById('financialPasswordInput');
    const financialError = document.getElementById('financialPasswordError');
    const financialConfirm = document.getElementById('financialPasswordConfirm');
    const financialCancel = document.getElementById('financialPasswordCancel');

    function openFinancialModal() {
        financialError.classList.add('hidden');
        financialInput.value = '';
        financialModal.classList.remove('hidden');
        financialModal.classList.add('flex');
        financialInput.focus();
    }

    function closeFinancialModal() {
        financialModal.classList.add('hidden');
        financialModal.classList.remove('flex');
    }

    printBtn.addEventListener('click', function () {
        window.print();
    });

    async function unlockFinancialReport() {
        const password = financialInput.value;
        if (!password) {
            financialError.textContent = 'Please enter your password.';
            financialError.classList.remove('hidden');
            return;
        }

        financialConfirm.disabled = true;
        financialConfirm.textContent = 'Checking...';
        try {
            const res = await fetch('verify-password.php', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify({ password, purpose: 'financial_report', csrf_token: csrfToken })
            });
            const data = await res.json();
            if (data.success) {
                const url = new URL(window.location.href);
                url.searchParams.set('financial_unlock', data.unlock_token);
                window.location.href = url.toString();
            } else {
                financialError.textContent = data.message || 'Incorrect password.';
                financialError.classList.remove('hidden');
            }
        } catch (err) {
            financialError.textContent = 'Something went wrong. Try again.';
            financialError.classList.remove('hidden');
        } finally {
            financialConfirm.disabled = false;
            financialConfirm.textContent = 'Unlock';
        }
    }

    if (unlockBtn) unlockBtn.addEventListener('click', openFinancialModal);
    if (financialCancel) financialCancel.addEventListener('click', closeFinancialModal);
    if (financialConfirm) financialConfirm.addEventListener('click', unlockFinancialReport);
    if (financialInput) financialInput.addEventListener('keydown', function (e) {
        if (e.key === 'Enter') unlockFinancialReport();
    });
})();