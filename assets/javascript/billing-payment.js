(function () {
    let csrfToken = window.csrfToken || "";
    const loadBtn = document.getElementById('load-btn');
    const patientLookupInput = document.getElementById('patient-lookup');
    const consultationCard = document.getElementById('consultation-card');
    const receiptCard = document.getElementById('billing-statement-card');
    const prescriptionSection = document.getElementById('prescription-section');
    const recordBtn = document.getElementById('record-btn');
    const printBtn = document.getElementById('print-btn');
    const billPicker = document.getElementById('bill-picker');

    let currentBillingData = {};
    let currentConsultationData = {};
    let currentPatientData = {};

    printBtn.addEventListener('click', function () {
        window.print();
    });

    function escapeHtml(value) {
        const div = document.createElement('div');
        div.textContent = value ?? '';
        return div.innerHTML;
    }

    function updateBodyScroll() {
        const anyModalOpen = document.querySelectorAll('.modal:not(.hidden)').length > 0;
        document.body.style.overflow = anyModalOpen ? 'hidden' : 'auto';
    }

    const openModal = (modal) => {
        modal.classList.remove("hidden");
        modal.classList.add("flex");
        updateBodyScroll();
    };

    const closeModal = (modal) => {
        modal.classList.add("hidden");
        updateBodyScroll();
    };

    function showMessage(title, message, type = "success", callback = null) {
        const modal = document.getElementById("messageModal");
        const titleElement = document.getElementById("messageTitle");
        const textElement = document.getElementById("messageText");

        titleElement.textContent = title;
        textElement.textContent = message;

        titleElement.classList.toggle("text-green-600", type === "success");
        titleElement.classList.toggle("text-red-600", type !== "success");

        openModal(modal);
        modal.classList.add('flex');

        document.getElementById("closeMessageBtn").onclick = () => {
            closeModal(modal);
            if (callback) callback();
        };
    }

    function hideBillCards() {
        consultationCard.classList.add('hidden');
        receiptCard.classList.add('hidden');
        printBtn.classList.add('hidden');
    }

    // ---- Bill picker (patients with more than one bill) ----
    function renderBillPicker(bills, patientCode) {
        billPicker.innerHTML = `
            <p class="text-sm text-slate-600">This patient has ${bills.length} bills. Choose one to load:</p>
            ${bills.map(b => `
                <button type="button" data-billing-id="${b.BillingID}"
                    class="bill-option w-full text-left rounded-xl border border-slate-200 bg-white px-4 py-3 hover:bg-slate-50">
                    <div class="flex justify-between items-start gap-3">
                        <span class="font-semibold text-slate-800">
                            ${new Date(b.ConsultationDate).toLocaleDateString('en-US', { month: 'short', day: 'numeric', year: 'numeric' })}
                            · ${escapeHtml(b.Diagnosis || 'No diagnosis')}
                        </span>
                        <span class="shrink-0 text-xs font-semibold px-3 py-1 rounded-full ${b.Status === 'Paid'
                ? 'bg-green-100 text-green-700'
                : b.Status === 'Partially Paid'
                    ? 'bg-amber-100 text-amber-700'
                    : 'bg-red-100 text-red-700'}">${escapeHtml(b.Status)}</span>
                    </div>
                    <div class="text-xs text-slate-500 mt-1">
                        ₱${parseFloat(b.FinalAmount).toFixed(2)} · OR-2026-${String(b.BillingID).padStart(5, '0')}
                    </div>
                </button>
            `).join('')}
        `;
        billPicker.classList.remove('hidden');

        billPicker.querySelectorAll('.bill-option').forEach(btn => {
            btn.addEventListener('click', () => {
                billPicker.classList.add('hidden');
                loadBill(patientCode, btn.dataset.billingId);
            });
        });
    }

    // ---- Load one bill (latest, or a specific billingId) ----
    async function loadBill(patientCode, billingId = null) {
        loadBtn.disabled = true;
        loadBtn.innerText = 'Loading...';

        try {
            let url = '../php/fetch/fetch-billing-consultation.php?patient_code=' + encodeURIComponent(patientCode);
            if (billingId) url += '&billing_id=' + encodeURIComponent(billingId);

            const response = await fetch(url);
            const data = await response.json();

            if (data.status === 'error') {
                showMessage('Error', data.message, 'error');
                hideBillCards();
                return;
            }

            currentBillingData = data.billing;
            currentConsultationData = data.consultation;
            currentPatientData = data.patient;

            const patientName = data.patient.FirstName + ' ' + data.patient.LastName;
            const doctorName = data.consultation.DoctorFirstName && data.consultation.DoctorLastName
                ? 'Dr. ' + data.consultation.DoctorFirstName + ' ' + data.consultation.DoctorLastName
                : 'Not Assigned';
            const consultDate = new Date(data.consultation.ConsultationDate).toLocaleDateString();

            document.getElementById('patient-name').innerText = patientName;
            document.getElementById('consult-date').innerText = consultDate;
            document.getElementById('consult-doctor').innerText = doctorName;
            document.getElementById('consult-diagnosis').innerText = data.consultation.Diagnosis || '—';
            document.getElementById('consult-treatment').innerText = data.consultation.Treatment || '—';

            if (data.prescriptions && data.prescriptions.length > 0) {
                let prescText = '';
                data.prescriptions.forEach(rx => {
                    prescText += `<div class="mb-2 p-2 bg-blue-50 rounded">
                                    <strong>${escapeHtml(rx.Medicine)}</strong><br/>
                                    ${escapeHtml(rx.Dosage)} · ${escapeHtml(rx.Frequency)}${rx.Duration ? ' · ' + escapeHtml(rx.Duration) : ''} · ${escapeHtml(rx.Quantity)}
                                    ${rx.Instructions ? '<br/><em>' + escapeHtml(rx.Instructions) + '</em>' : ''}
                                </div>`;
                });
                document.getElementById('consult-prescription').innerHTML = prescText;
                prescriptionSection.style.display = 'block';
            } else {
                prescriptionSection.style.display = 'none';
            }

            const consultFee = parseFloat(data.billing.OriginalAmount) || 0;
            document.getElementById('consult-fee').innerText = '₱' + consultFee.toFixed(2);
            document.getElementById('total-due').innerText = '₱' + parseFloat(data.billing.FinalAmount).toFixed(2);
            document.getElementById('or-number').innerText = 'OR-2026-' + String(data.billing.BillingID).padStart(5, '0');

            const balanceRow = document.getElementById('balance-remaining-row');
            if (data.billing.Status === 'Partially Paid') {
                document.getElementById('balance-remaining').innerText = '₱' + parseFloat(data.billing.AmountDue).toFixed(2);
                balanceRow.classList.remove('hidden');
            } else {
                balanceRow.classList.add('hidden');
            }

            consultationCard.classList.remove('hidden');
            receiptCard.classList.remove('hidden');

            if (data.billing.Status === 'Paid') {
                document.getElementById('amount-paid-field').classList.add('hidden');
                recordBtn.classList.add('hidden');
                printBtn.classList.remove('hidden');

                document.querySelector('#paid-amount span:last-child').innerText =
                    '₱' + parseFloat(data.billing.AmountPaid ?? data.billing.FinalAmount).toFixed(2);
                document.getElementById('paid-amount').classList.remove('hidden');

                document.getElementById('amount-paid').value = '';
            } else {
                document.getElementById('amount-paid-field').classList.remove('hidden');
                recordBtn.classList.remove('hidden');
                printBtn.classList.add('hidden');
                document.getElementById('paid-amount').classList.add('hidden');

                document.getElementById('amount-paid').value = parseFloat(data.billing.AmountDue).toFixed(2);
                document.getElementById('amount-paid').focus();
            }

            applyRegisteredDiscount(data.patient.PatientType);
        } catch (error) {
            console.error('Error:', error);
            showMessage('Error', 'Error loading consultation data. Please try again.', 'error');
        } finally {
            loadBtn.disabled = false;
            loadBtn.innerText = 'Load';
        }
    }

    // ---- Load button: list the patient's bills first ----
    loadBtn.addEventListener('click', async function () {
        const patientCode = patientLookupInput.value.trim();
        billPicker.classList.add('hidden');

        if (!patientCode) {
            showMessage('Missing Information', 'Please enter a Patient ID', 'error');
            return;
        }

        loadBtn.disabled = true;
        loadBtn.innerText = 'Loading...';

        try {
            const response = await fetch('../php/fetch/fetch-patient-bills.php?patient_code=' + encodeURIComponent(patientCode));
            const result = await response.json();

            if (result.status !== 'success') {
                showMessage('Error', result.message, 'error');
                hideBillCards();
                return;
            }

            if (result.data.length === 0) {
                showMessage('Error', 'No billing records found for this patient.', 'error');
                hideBillCards();
                return;
            }

            if (result.data.length === 1) {
                // loadBill manages the button state itself
                loadBtn.disabled = false;
                loadBtn.innerText = 'Load';
                loadBill(patientCode, result.data[0].BillingID);
                return;
            }

            hideBillCards();
            renderBillPicker(result.data, patientCode);
        } catch (error) {
            console.error('Error:', error);
            showMessage('Error', 'Error loading billing records. Please try again.', 'error');
        } finally {
            if (loadBtn.innerText === 'Loading...' && !loadBtn.disabled === false) {
                loadBtn.innerText = 'Load';
            }
            loadBtn.disabled = false;
            loadBtn.innerText = 'Load';
        }
    });

    function applyRegisteredDiscount(patientType) {
        const normalizedPatientType = (patientType || 'Regular').trim().toLowerCase();
        const discountType = normalizedPatientType === 'senior citizen'
            ? 'Senior Citizen'
            : normalizedPatientType === 'pwd' ? 'PWD' : 'None';
        const discountPercent = discountType === 'None' ? 0 : 20;

        const originalAmount = parseFloat(currentBillingData.OriginalAmount) || 0;
        const discountAmount = Math.round((originalAmount * discountPercent / 100) * 100) / 100;
        const finalAmount = originalAmount - discountAmount;
        const totalPaidSoFar = parseFloat(currentBillingData.TotalPaid) || 0;
        const remainingBalance = Math.max(0, finalAmount - totalPaidSoFar);

        currentBillingData.DiscountType = discountType;
        currentBillingData.DiscountPercent = discountPercent;
        currentBillingData.DiscountAmount = discountAmount;
        currentBillingData.FinalAmount = finalAmount;

        document.getElementById('consult-fee').innerText = '₱' + originalAmount.toFixed(2);
        if (discountPercent > 0) {
            document.getElementById('consult-fee').innerText =
                `₱${originalAmount.toFixed(2)} - ₱${discountAmount.toFixed(2)} (${discountPercent}%)`;
        }
        document.getElementById('total-due').innerText = '₱' + finalAmount.toFixed(2);

        const balanceRow = document.getElementById('balance-remaining-row');
        if (currentBillingData.Status === 'Partially Paid') {
            document.getElementById('balance-remaining').innerText = '₱' + remainingBalance.toFixed(2);
            balanceRow.classList.remove('hidden');
        } else {
            balanceRow.classList.add('hidden');
        }

        if (currentBillingData.Status !== 'Paid') {
            document.getElementById('amount-paid').value = remainingBalance.toFixed(2);
        }
    }

    // Record payment handler (unchanged)
    recordBtn.addEventListener('click', async function () {
        const amountPaid = parseFloat(document.getElementById('amount-paid').value);

        if (!amountPaid || amountPaid <= 0) {
            showMessage('Missing Information', 'Please enter a valid amount', 'error');
            return;
        }

        recordBtn.disabled = true;
        recordBtn.innerText = 'Processing...';

        try {
            const response = await fetch('../php/add/record-payment.php', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify({
                    billing_id: currentBillingData.BillingID,
                    amount_paid: amountPaid,
                    csrf_token: csrfToken
                })
            });

            const result = await response.json();

            if (result.csrf_token) {
                csrfToken = result.csrf_token;
            }

            if (result.status === 'error') {
                showMessage('Error', 'Error: ' + result.message, 'error');
                recordBtn.disabled = false;
                recordBtn.innerText = 'Record Payment';
                return;
            }

            showMessage(
                'Payment Recorded',
                `Receipt No: ${result.receipt_no}\nStatus: ${result.billing_status}\nAmount Paid: ₱${amountPaid.toFixed(2)}\nAmount Due: ₱${result.amount_due.toFixed(2)}`,
                'success',
                () => {
                    currentBillingData.Status = result.billing_status;
                    document.getElementById('or-number').innerText = result.receipt_no;
                    document.querySelector('#paid-amount span:last-child').innerText = '₱' + parseFloat(result.total_paid).toFixed(2);
                    document.getElementById('paid-amount').classList.remove('hidden');
                    printBtn.classList.remove('hidden');

                    const balanceRow = document.getElementById('balance-remaining-row');
                    if (result.billing_status === 'Partially Paid') {
                        document.getElementById('balance-remaining').innerText = '₱' + parseFloat(result.amount_due).toFixed(2);
                        balanceRow.classList.remove('hidden');
                    } else {
                        balanceRow.classList.add('hidden');
                    }

                    if (result.billing_status === 'Paid') {
                        document.getElementById('amount-paid-field').classList.add('hidden');
                        recordBtn.classList.add('hidden');
                    } else {
                        document.getElementById('amount-paid-field').classList.remove('hidden');
                        recordBtn.classList.remove('hidden');
                        document.getElementById('amount-paid').value = parseFloat(result.amount_due).toFixed(2);
                        document.getElementById('amount-paid').focus();
                    }

                    if (result.billing_status === 'Paid') {
                        patientLookupInput.value = '';
                        document.getElementById('amount-paid').value = '';
                        consultationCard.classList.add('hidden');
                    }
                }
            );
        } catch (error) {
            console.error('Error:', error);
            showMessage('Error', 'Error processing payment. Please try again.', 'error');
        } finally {
            recordBtn.disabled = false;
            recordBtn.innerText = 'Record Payment';
        }
    });
})();