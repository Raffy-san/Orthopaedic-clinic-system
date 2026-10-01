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

    // --- Individual Patient Report modal ---
    const patientModal = document.getElementById('patientRecordModal');

    function openPatientModal() {
        patientModal.classList.remove('hidden');
        patientModal.classList.add('flex');
    }

    function closePatientModal() {
        patientModal.classList.add('hidden');
        patientModal.classList.remove('flex');
        document.body.classList.remove('printing-patient-report');
    }

    function money(value) {
        return '₱' + Number(value || 0).toLocaleString('en-PH', { minimumFractionDigits: 2, maximumFractionDigits: 2 });
    }

    function escapeHtml(value) {
        const div = document.createElement('div');
        div.textContent = value ?? '—';
        return div.innerHTML;
    }

    function renderPatientModal(data) {
        const p = data.patient;
        const billing = data.billing_summary || {};
        const consultations = data.consultations || [];

        const consultationRows = consultations.length
            ? consultations.map((c) => {
                const prescriptions = c.Prescriptions?.length
                    ? `<ul class="space-y-2">${c.Prescriptions.map((prescription) => `
                        <li>
                            <p class="font-medium">${escapeHtml(prescription.Medicine)}</p>
                            <p class="text-xs text-gray-500">${escapeHtml(prescription.Dosage)} · ${escapeHtml(prescription.Frequency)} · ${escapeHtml(prescription.Duration)}</p>
                            ${prescription.Instructions ? `<p class="text-xs text-gray-500">${escapeHtml(prescription.Instructions)}</p>` : ''}
                        </li>
                    `).join('')}</ul>`
                    : '<span class="text-gray-500">None recorded</span>';

                return `
                <tr class="border-b last:border-0">
                    <td class="py-2">${escapeHtml(c.ConsultationDate)}</td>
                    <td>${escapeHtml(c.DoctorName)}</td>
                    <td>${escapeHtml(c.Diagnosis)}</td>
                    <td>${escapeHtml(c.Treatment)}</td>
                    <td>${prescriptions}</td>
                    <td class="text-right">${money(c.ConsultationFee)}</td>
                </tr>
            `;
            }).join('')
            : `<tr><td colspan="6" class="py-4 text-center text-gray-500 text-sm">No consultation history yet.</td></tr>`;

        patientModal.innerHTML = `
            <div class="bg-white rounded-lg shadow-lg w-full max-w-2xl p-6 max-h-[90vh] overflow-y-auto" id="patientReportPrintArea">
                <div class="flex items-start justify-between gap-4 mb-4 print-hidden">
                    <h3 class="text-lg font-bold text-gray-800">Individual Patient Report</h3>
                    <div class="flex items-center gap-2">
                        <button type="button" id="printPatientReportBtn"
                            class="inline-flex items-center gap-2 px-3 py-2 bg-slate-700 text-white text-sm font-semibold rounded-lg hover:bg-slate-800">
                            <i class="fa-solid fa-print"></i> Print
                        </button>
                        <button type="button" id="closePatientReportBtn"
                            class="px-3 py-2 text-sm font-medium text-gray-600 hover:bg-gray-100 rounded-lg">Close</button>
                    </div>
                </div>

                <div class="border-b pb-4 mb-4">
                    <h2 class="text-xl font-bold text-gray-800">${escapeHtml(p.FirstName + ' ' + (p.MiddleName ? p.MiddleName + ' ' : '') + p.LastName)}</h2>
                    <p class="text-sm text-gray-500">${escapeHtml(p.PatientCode)} · ${escapeHtml(p.PatientType)}</p>
                </div>

                <div class="grid grid-cols-2 gap-4 mb-6 text-sm">
                    <div><span class="text-gray-500">Birth Date:</span> ${escapeHtml(p.BirthDate)}</div>
                    <div><span class="text-gray-500">Gender:</span> ${escapeHtml(p.Gender)}</div>
                    <div><span class="text-gray-500">Phone:</span> ${escapeHtml(p.Phone)}</div>
                    <div><span class="text-gray-500">Registered:</span> ${escapeHtml(p.CreatedAt)}</div>
                    <div class="col-span-2"><span class="text-gray-500">Address:</span> ${escapeHtml(p.Address)}</div>
                </div>

                <div class="grid grid-cols-3 gap-3 mb-6">
                    <div class="rounded-lg bg-slate-50 p-4">
                        <p class="text-xs text-gray-500">Total Visits</p>
                        <p class="text-lg font-bold text-gray-800 mt-1">${consultations.length}</p>
                    </div>
                    <div class="rounded-lg bg-slate-50 p-4">
                        <p class="text-xs text-gray-500">Total Billed</p>
                        <p class="text-lg font-bold text-gray-800 mt-1">${money(billing.total_billed)}</p>
                    </div>
                    <div class="rounded-lg bg-slate-50 p-4">
                        <p class="text-xs text-gray-500">Total Paid</p>
                        <p class="text-lg font-bold text-emerald-700 mt-1">${money(billing.total_paid)}</p>
                    </div>
                </div>

                <h4 class="font-semibold text-gray-800 mb-2 text-sm">Consultation History</h4>
                <div class="overflow-x-auto">
                    <table class="w-full text-sm text-left">
                        <thead class="text-xs uppercase text-gray-500 border-b">
                            <tr>
                                <th class="py-2">Date</th>
                                <th>Doctor</th>
                                <th>Diagnosis</th>
                                <th>Treatment</th>
                                <th>Prescriptions</th>
                                <th class="text-right">Fee</th>
                            </tr>
                        </thead>
                        <tbody>${consultationRows}</tbody>
                    </table>
                </div>
            </div>
        `;

        document.getElementById('closePatientReportBtn').addEventListener('click', closePatientModal);
        document.getElementById('printPatientReportBtn').addEventListener('click', function () {
            document.body.classList.add('printing-patient-report');
            window.print();
        });
    }

    document.querySelectorAll('.view-patient-report-btn').forEach((btn) => {
        btn.addEventListener('click', async function () {
            const patientCode = this.dataset.patientCode;
            if (!patientCode) return;

            patientModal.innerHTML = `
                <div class="bg-white rounded-lg shadow-lg w-full max-w-md p-6 text-center">
                    <p class="text-sm text-gray-500">Loading patient report...</p>
                </div>
            `;
            openPatientModal();

            try {
                const res = await fetch(`../php/fetch/fetch-patient-report.php?patient_code=${encodeURIComponent(patientCode)}`);
                const data = await res.json();

                if (data.status !== 'success') {
                    throw new Error(data.message || 'Unable to load the patient report.');
                }

                renderPatientModal(data.data);
            } catch (err) {
                patientModal.innerHTML = `
                    <div class="bg-white rounded-lg shadow-lg w-full max-w-md p-6 text-center">
                        <p class="text-sm text-red-600">${escapeHtml(err.message)}</p>
                        <button type="button" id="closePatientReportErrorBtn"
                            class="mt-4 px-3 py-2 text-sm font-medium text-gray-600 hover:bg-gray-100 rounded-lg">Close</button>
                    </div>
                `;
                document.getElementById('closePatientReportErrorBtn').addEventListener('click', closePatientModal);
            }
        });
    });

    // Close the patient modal on backdrop click
    patientModal.addEventListener('click', function (e) {
        if (e.target === patientModal) closePatientModal();
    });

    window.addEventListener('keydown', function (e) {
        if (e.key === 'Escape' && patientModal.classList.contains('flex')) closePatientModal();
    });

    window.addEventListener('afterprint', function () {
        document.body.classList.remove('printing-patient-report');
    });
})();