(function () {
    let csrfToken = window.csrfToken || "";
    const loadBtn = document.getElementById('load-btn');
    const patientLookupInput = document.getElementById('patient-lookup');
    const consultationCard = document.getElementById('consultation-card');
    const receiptCard = document.getElementById('receipt-card');
    const prescriptionSection = document.getElementById('prescription-section');
    const recordBtn = document.getElementById('record-btn');
    const printBtn = document.getElementById('print-btn');

    let currentBillingData = {};
    let currentConsultationData = {};
    let currentPatientData = {};

    printBtn.addEventListener('click', function () {
        window.print();
    });

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

    loadBtn.addEventListener('click', async function () {
        const patientCode = patientLookupInput.value.trim();

        if (!patientCode) {
            showMessage('Missing Information', 'Please enter a Patient ID', 'error');
            return;
        }

        loadBtn.disabled = true;
        loadBtn.innerText = 'Loading...';

        try {
            const response = await fetch('../php/fetch/fetch-billing-consultation.php?patient_code=' + encodeURIComponent(patientCode));
            const data = await response.json();

            if (data.status === 'error') {
                showMessage('Error', data.message, 'error');
                consultationCard.classList.add('hidden');
                receiptCard.classList.add('hidden');
                printBtn.classList.add('hidden');
                loadBtn.disabled = false;
                loadBtn.innerText = 'Load';
                return;
            }

            // Store data for later use
            currentBillingData = data.billing;
            currentConsultationData = data.consultation;
            currentPatientData = data.patient;

            // Populate consultation details
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

            // Display prescriptions if available
            if (data.prescriptions && data.prescriptions.length > 0) {
                let prescText = '';
                data.prescriptions.forEach(rx => {
                    prescText += `<div class="mb-2 p-2 bg-blue-50 rounded">
                                    <strong>${rx.Medicine}</strong><br/>
                                    ${rx.Dosage} · ${rx.Frequency}${rx.Duration ? ' · ' + rx.Duration : ''}
                                    ${rx.Instructions ? '<br/><em>' + rx.Instructions + '</em>' : ''}
                                </div>`;
                });
                document.getElementById('consult-prescription').innerHTML = prescText;
                prescriptionSection.style.display = 'block';
            } else {
                prescriptionSection.style.display = 'none';
            }

            // Update receipt with billing info
            const consultFee = parseFloat(data.billing.OriginalAmount) || 0;
            document.getElementById('consult-fee').innerText = '₱' + consultFee.toFixed(2);
            document.getElementById('total-due').innerText = '₱' + parseFloat(data.billing.FinalAmount).toFixed(2);
            document.getElementById('or-number').innerText = 'OR-2026-' + String(data.billing.BillingID).padStart(5, '0');
            document.getElementById('amount-paid').value = parseFloat(data.billing.FinalAmount).toFixed(2);

            // Show cards
            consultationCard.classList.remove('hidden');
            receiptCard.classList.remove('hidden');
            printBtn.classList.add('hidden');
            document.getElementById('paid-amount').classList.add('hidden');

            // Configure discount buttons based on patient eligibility
            setupDiscountEligibility(data.patient.PatientType);

            // Optionally focus the amount paid field
            document.getElementById('amount-paid').focus();
        } catch (error) {
            console.error('Error:', error);
            showMessage('Error', 'Error loading consultation data. Please try again.', 'error');
        } finally {
            loadBtn.disabled = false;
            loadBtn.innerText = 'Load';
        }
    });

    // Setup discount eligibility - all options available for staff selection
    function setupDiscountEligibility(patientType) {
        const discountBtns = document.querySelectorAll('.discount-btn');

        // All discount buttons are enabled for staff to select
        discountBtns.forEach(btn => {
            btn.disabled = false;
            btn.style.opacity = '1';
            btn.style.cursor = 'pointer';
            btn.title = 'Click to apply this discount';
        });

        // Select "None" by default
        applyDiscount(document.querySelector('[data-discount="None"]'));
    }

    // Discount button handlers - staff selects appropriate discount
    document.querySelectorAll('.discount-btn').forEach(btn => {
        btn.addEventListener('click', function () {
            applyDiscount(this);
        });
    });

    function applyDiscount(btn) {
        const discountType = btn.dataset.discount;
        const discountPercent = parseFloat(btn.dataset.percent) || 0;

        // Update UI - highlight selected button
        document.querySelectorAll('.discount-btn').forEach(b => {
            b.classList.remove('bg-blue-100', 'border-blue-500', 'text-blue-700');
            b.classList.add('border-slate-200', 'text-slate-600');
        });
        btn.classList.add('bg-blue-100', 'border-blue-500', 'text-blue-700');

        // Calculate final amount with discount
        const originalAmount = parseFloat(currentBillingData.OriginalAmount) || 0;
        const discountAmount = (originalAmount * discountPercent) / 100;
        const finalAmount = originalAmount - discountAmount;

        // Update billing data
        currentBillingData.DiscountType = discountType;
        currentBillingData.DiscountPercent = discountPercent;
        currentBillingData.DiscountAmount = discountAmount;
        currentBillingData.FinalAmount = finalAmount;

        // Update receipt display
        document.getElementById('consult-fee').innerText = '₱' + originalAmount.toFixed(2);
        if (discountPercent > 0) {
            const feeDisplay = `₱${originalAmount.toFixed(2)} - ₱${discountAmount.toFixed(2)} (${discountPercent}%)`;
            document.getElementById('consult-fee').innerText = feeDisplay;
        }
        document.getElementById('total-due').innerText = '₱' + finalAmount.toFixed(2);
        document.getElementById('amount-paid').value = finalAmount.toFixed(2);
    }

    // Record payment handler
    recordBtn.addEventListener('click', async function () {
        const amountPaid = parseFloat(document.getElementById('amount-paid').value);

        // Validation
        if (!amountPaid || amountPaid <= 0) {
            showMessage('Missing Information', 'Please enter a valid amount', 'error');
            return;
        }

        recordBtn.disabled = true;
        recordBtn.innerText = 'Processing...';

        try {
            const response = await fetch('../php/add/record-payment.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json'
                },
                body: JSON.stringify({
                    billing_id: currentBillingData.BillingID,
                    amount_paid: amountPaid,
                    discount_type: currentBillingData.DiscountType,
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

            // Show success message with payment details
            showMessage(
                'Payment Recorded',
                `Receipt No: ${result.receipt_no}\nStatus: ${result.billing_status}\nAmount Paid: ₱${amountPaid.toFixed(2)}\nAmount Due: ₱${result.amount_due.toFixed(2)}`,
                'success',
                () => {
                    // Update billing data with new status
                    currentBillingData.Status = result.billing_status;
                    document.getElementById('or-number').innerText = result.receipt_no;
                    document.querySelector('#paid-amount span:last-child').innerText = '₱' + amountPaid.toFixed(2);
                    document.getElementById('paid-amount').classList.remove('hidden');
                    printBtn.classList.remove('hidden');

                    // Reset form
                    patientLookupInput.value = '';
                    document.getElementById('amount-paid').value = '';
                    consultationCard.classList.add('hidden');
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