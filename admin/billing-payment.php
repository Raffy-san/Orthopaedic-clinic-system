<?php
require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/../includes/auth.php';
require_once __DIR__ . '/../php/fetch/fetch.php';
SessionManager::requireLogin();
SessionManager::requireAnyRole(['admin', 'doctor', 'staff']);

$admin = SessionManager::getUser($pdo);

if (!$admin) {
    SessionManager::logout('../index.php');
}

$csrfToken = $_SESSION['csrf_token'] ?? SessionManager::regenerateCsrfToken();
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="../assets/css/output.css">
    <link rel="stylesheet" href="../assets/css/global.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
    <link rel="icon" href="../assets/img/rounded-logo.ico" type="image/x-icon">
    <title>Billing and Payment</title>
    <style>
        @media print {
            body * {
                visibility: hidden;
            }

            #receipt-card,
            #receipt-card * {
                visibility: visible;
            }

            #receipt-card {
                position: absolute;
                inset: 0;
                width: 100%;
                margin: 0;
                box-shadow: none;
                border: 0;
            }

            #receipt-card .print-hidden {
                display: none;
            }
        }
    </style>
</head>

<body class="h-screen flex bg-slate-200">
    <?php include_once '../includes/sidebar.php'; ?>

    <section class="flex-1 p-6 overflow-auto">
        <div class="mb-6 space-y-1">
            <h1 class="text-2xl font-bold">Billing & Payment</h1>
            <h3 class="text-sm font-medium text-gray-500">Fee computation, discounts, and receipt generation</h3>
        </div>

        <div class="grid grid-cols-12 gap-6">
            <div class="col-span-6">
                <div class="bg-white rounded-2xl p-6 shadow-md border border-slate-100">
                    <label for="patient-lookup" class="block text-lg font-semibold text-slate-800">Patient
                        Lookup</label>

                    <div class="mt-4 flex items-center gap-4">
                        <input type="text" id="patient-lookup" placeholder="PT-YYYY-XXXX"
                            class="flex-1 py-3 px-4 rounded-lg border border-slate-200 text-gray-600 placeholder:text-gray-300" />

                        <button id="load-btn" type="button"
                            class="ml-2 inline-flex items-center justify-center bg-blue-600 hover:bg-blue-700 text-white font-semibold px-5 py-3 rounded-lg shadow-sm">Load</button>
                    </div>
                </div>

                <div id="consultation-card"
                    class="hidden mt-6 bg-white rounded-2xl p-6 shadow-md border border-slate-100">
                    <h2 class="font-semibold text-slate-800">Consultation Record</h2>

                    <dl class="mt-4 text-sm text-slate-600">
                        <div class="py-2 border-b border-slate-100 flex justify-between">
                            <dt class="text-slate-400">Patient</dt>
                            <dd id="patient-name">—</dd>
                        </div>
                        <div class="py-2 border-b border-slate-100 flex justify-between">
                            <dt class="text-slate-400">Date</dt>
                            <dd id="consult-date">—</dd>
                        </div>
                        <div class="py-2 border-b border-slate-100 flex justify-between">
                            <dt class="text-slate-400">Doctor</dt>
                            <dd id="consult-doctor">—</dd>
                        </div>
                        <div class="py-2 border-b border-slate-100 flex justify-between">
                            <dt class="text-slate-400">Diagnosis</dt>
                            <dd id="consult-diagnosis">—</dd>
                        </div>
                        <div class="py-2 border-b border-slate-100 flex justify-between">
                            <dt class="text-slate-400">Treatment</dt>
                            <dd id="consult-treatment">—</dd>
                        </div>
                        <div class="py-2 border-b border-slate-100" id="prescription-section" style="display:none;">
                            <dt class="text-slate-400 mb-2">Prescription</dt>
                            <dd id="consult-prescription" class="text-xs space-y-2">—</dd>
                        </div>
                    </dl>

                    <div class="mt-4">
                        <label class="text-slate-500 text-sm">Discount Eligibility</label>
                        <div class="mt-2 flex gap-2">
                            <button class="discount-btn px-3 py-1 rounded-lg border text-slate-600 text-sm" data-discount="None" data-percent="0">None</button>
                            <button class="discount-btn px-3 py-1 rounded-lg border text-slate-600 text-sm" data-discount="Senior Citizen" data-percent="20">Senior Citizen (20%)</button>
                            <button class="discount-btn px-3 py-1 rounded-lg border text-slate-600 text-sm" data-discount="PWD" data-percent="20">PWD (20%)</button>
                        </div>
                    </div>
                </div>
            </div>

            <div class="col-span-6">
                <div id="receipt-card" class="hidden bg-white rounded-2xl p-6 shadow-md border border-slate-100">
                    <div class="flex justify-between items-start">
                        <h3 class="text-sm text-slate-500">Official Receipt</h3>
                        <small id="or-number" class="text-xs text-slate-400">OR-2026-00000</small>
                    </div>

                    <div class="mt-4 border border-slate-100 rounded-lg p-4">
                        <div class="flex justify-between text-sm text-slate-500">
                            <div>Consultation Fee</div>
                            <div id="consult-fee">—</div>
                        </div>

                        <div class="mt-3 flex justify-between items-center">
                            <div class="text-sm font-semibold">Total Due</div>
                            <div id="total-due" class="text-lg font-bold">—</div>
                        </div>
                    </div>

                    <div class="print-hidden mt-6">
                        <label for="amount-paid" class="block text-sm font-semibold text-slate-700 mb-2">Amount Paid</label>
                        <input type="number" id="amount-paid" placeholder="0.00" step="0.01" class="w-full py-2 px-3 rounded-lg border border-slate-200 text-gray-600" />
                    </div>

                    <div id="paid-amount" class="hidden mt-6 flex justify-between text-sm font-semibold">
                        <span>Amount Paid</span>
                        <span>—</span>
                    </div>

                    <button id="record-btn"
                        class="mt-6 w-full bg-green-600 hover:bg-green-700 text-white font-semibold py-3 rounded-lg disabled:opacity-50 disabled:cursor-not-allowed">Record
                        Payment</button>
                    <button id="print-btn" type="button"
                        class="print-hidden hidden mt-3 w-full bg-slate-700 hover:bg-slate-800 text-white font-semibold py-3 rounded-lg">
                        <i class="fas fa-print mr-2" aria-hidden="true"></i>Print Receipt</button>
                </div>
            </div>
        </div>

        <script>
            (function () {
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

                loadBtn.addEventListener('click', async function () {
                    const patientCode = patientLookupInput.value.trim();
                    
                    if (!patientCode) {
                        alert('Please enter a Patient ID');
                        return;
                    }

                    loadBtn.disabled = true;
                    loadBtn.innerText = 'Loading...';

                    try {
                        const response = await fetch('../php/fetch/fetch-billing-consultation.php?patient_code=' + encodeURIComponent(patientCode));
                        const data = await response.json();

                        if (data.status === 'error') {
                            alert(data.message);
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
                        alert('Error loading consultation data. Please try again.');
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
                        alert('Please enter a valid amount');
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
                                amount_paid: amountPaid
                            })
                        });

                        const result = await response.json();

                        if (result.status === 'error') {
                            alert('Error: ' + result.message);
                            recordBtn.disabled = false;
                            recordBtn.innerText = 'Record Payment';
                            return;
                        }

                        // Show success message with payment details
                        alert(`Payment recorded successfully!\n\nReceipt No: ${result.receipt_no}\nStatus: ${result.billing_status}\nAmount Paid: ₱${amountPaid.toFixed(2)}\nAmount Due: ₱${result.amount_due.toFixed(2)}`);

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

                    } catch (error) {
                        console.error('Error:', error);
                        alert('Error processing payment. Please try again.');
                    } finally {
                        recordBtn.disabled = false;
                        recordBtn.innerText = 'Record Payment';
                    }
                });
            })();
        </script>
    </section>
</body>

</html>