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
        .billing-print-card {
            width: 100%;
            max-width: 820px;
            background: #f7f7f5;
            border: 1px solid #d9d7d2;
            padding: 16px 24px 20px;
            color: #2f2f2f;
            box-sizing: border-box;
            font-family: Arial, Helvetica, sans-serif;
        }

        .billing-print-card .clinic-header {
            text-align: center;
            margin-bottom: 10px;
        }

        .billing-print-card .clinic-brand {
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 10px;
        }

        .billing-print-card .clinic-logo {
            width: 44px;
            height: 50px;
            object-fit: contain;
        }

        .billing-print-card .clinic-name {
            font-size: 20px;
            font-weight: 700;
            letter-spacing: 0.02em;
            margin: 0;
            text-transform: uppercase;
        }

        .billing-print-card .doctor-line,
        .billing-print-card .contact-line {
            font-size: 13px;
            color: #1d1d1d;
            margin-top: 4px;
        }

        .billing-print-card .billing-divider {
            border-top: 2px solid #2f2f2f;
            margin: 12px 0 18px;
        }

        .billing-print-card .statement-title {
            text-align: center;
            font-size: 22px;
            font-weight: 800;
            margin: 8px 0 20px;
            letter-spacing: 0.04em;
            text-transform: uppercase;
        }

        .billing-print-card .statement-meta {
            display: flex;
            justify-content: space-between;
            gap: 20px;
            font-size: 15px;
            font-weight: 700;
            margin-bottom: 10px;
        }

        .billing-print-card .statement-meta .meta-field {
            display: flex;
            align-items: center;
            gap: 10px;
            flex: 1;
            min-width: 0;
        }

        .billing-print-card .statement-meta .meta-field span:last-child {
            flex: 1;
            border-bottom: 1px solid #787878;
            min-height: 18px;
            display: inline-block;
            padding-bottom: 2px;
            white-space: nowrap;
            overflow: hidden;
            text-overflow: ellipsis;
        }

        .billing-print-card .statement-diagnosis {
            font-size: 15px;
            font-weight: 700;
            margin-bottom: 13px;
            display: flex;
            align-items: center;
            gap: 10px;
        }

        .billing-print-card .statement-diagnosis span:last-child {
            display: inline-block;
            flex: 1;
            border-bottom: 1px solid #787878;
            min-height: 18px;
            white-space: nowrap;
            overflow: hidden;
            text-overflow: ellipsis;
        }

        .billing-print-card .statement-table {
            width: 100%;
            border-collapse: collapse;
            font-size: 15px;
            margin-bottom: 10px;
        }

        .billing-print-card .statement-table th,
        .billing-print-card .statement-table td {
            border: 1px solid #747474;
            padding: 8px 10px;
            vertical-align: middle;
        }

        .billing-print-card .statement-table thead th {
            background: #4a5660;
            color: #ffffff;
            text-align: left;
            font-weight: 700;
        }

        .billing-print-card .statement-table thead th:last-child {
            text-align: center;
        }

        .billing-print-card .statement-table td:last-child {
            text-align: right;
            width: 28%;
            min-width: 150px;
        }

        .billing-print-card .statement-table tbody tr td:nth-child(1) {
            font-weight: 500;
        }

        .billing-print-card .statement-table tbody tr td:nth-child(2) {
            min-width: 140px;
        }

        .billing-print-card .totals {
            width: 300px;
            margin-left: auto;
            font-size: 13px;
        }

        .billing-print-card .total-row {
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: 3px 0;
            margin-bottom: 3px;
        }

        .billing-print-card .total-row span:last-child {
            display: inline-block;
            width: 115px;
            min-height: 16px;
            border: 1px solid #a9a9a9;
            text-align: right;
            padding: 3px 6px;
            background: #f3f3f3;
        }

        .billing-print-card .grand-total {
            font-weight: 800;
            font-size: 16px;
            margin-top: 6px;
        }

        .billing-print-card .grand-total span:last-child {
            font-size: 16px;
            font-weight: 800;
        }

        .billing-print-card .paid-row {
            margin-top: 14px;
            justify-content: flex-end;
            text-align: right;
            font-size: 13px;
            font-weight: 700;
            display: flex;
            align-items: center;
            gap: 8px;
        }

        .billing-print-card .paid-row span {
            display: inline-block;
            min-width: 92px;
            border-bottom: 1px solid #6c6c6c;
            text-align: right;
            padding-bottom: 2px;
        }

        .billing-print-card .doctor-signature {
            margin-top: 28px;
            text-align: center;
            font-weight: 700;
            font-size: 15px;
            border-top: 1.5px solid #2f2f2f;
            padding-top: 6px;
            width: 40%;
            margin-left: auto;
            margin-right: 0;
        }

        @media print {
            @page {
                margin: 0;
            }

            body * {
                visibility: hidden;
            }

            #billing-statement-card,
            #billing-statement-card * {
                visibility: visible;
            }

            #billing-statement-card {
                position: absolute;
                inset: 0;
                width: 100%;
                margin: 0;
                box-shadow: none;
                border: 0;
                background: #fff;
                visibility: visible;
            }

            #billing-statement-card .print-hidden,
            .print-hidden {
                display: none !important;
            }
        }
    </style>
</head>

<body class="h-screen flex bg-slate-200 overflow-hidden">
    <?php include_once '../includes/sidebar.php'; ?>

    <section class="flex-1 min-h-0 flex flex-col overflow-hidden">
        <div
            class="flex items-center justify-between bg-gradient-to-r from-[#0b1f0b] via-[#1e6b34] to-[#2e8b47] px-6 py-4">
            <h1 class="text-2xl font-bold text-white">Southern Leyte Orthopaedic Clinic System</h1>
            <div class="flex flex-col gap-3 items-end">
                <h1 class="text-2xl font-bold text-white">Billing & Payment</h1>
                <h3 class="text-sm font-medium text-white">Fee computation, discounts, and billing statement generation</h3>
            </div>
        </div>

        <div class="grid grid-cols-12 gap-6 min-h-0 overflow-auto p-6 space-y-4">
            <div class="col-span-6">
                <div class="bg-white rounded-2xl p-6 shadow-md border border-slate-100">
                    <label for="patient-lookup" class="block text-lg font-semibold text-slate-800">Patient
                        Lookup</label>

                    <div class="mt-4 flex items-center gap-4">
                        <select
                            class="w-full border border-gray-300 bg-white rounded-lg px-4 py-3 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500"
                            name="patient_id" id="patient-lookup" required>
                            <option value="" aria-readonly="true">Please Select Patient ID</option>

                            <?php
                            try {
                                $stmt = $pdo->query("SELECT PatientCode, FirstName, LastName FROM patients ORDER BY CreatedAt DESC");
                                while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
                                    $patientCode = htmlspecialchars($row['PatientCode'], ENT_QUOTES, 'UTF-8');
                                    $firstName = htmlspecialchars($row['FirstName'], ENT_QUOTES, 'UTF-8');
                                    $lastName = htmlspecialchars($row['LastName'], ENT_QUOTES, 'UTF-8');
                                    echo "<option value=\"$patientCode\">$patientCode - $firstName $lastName</option>";
                                }
                            } catch (PDOException $e) {
                                echo "<option value=\"\">Error fetching patients</option>";
                            }
                            ?>

                        </select>

                        <button id="load-btn" type="button"
                            class="ml-2 inline-flex items-center justify-center bg-blue-600 hover:bg-blue-700 text-white font-semibold px-5 py-3 rounded-lg shadow-sm">Load</button>
                    </div>

                    <div id="bill-picker" class="hidden mt-4 space-y-2"></div>
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
                            <dt class="text-slate-400">Notes</dt>
                            <dd id="consult-notes" class="whitespace-pre-wrap break-words">—</dd>
                        </div>
                        <div class="py-2 border-b border-slate-100" id="prescription-section" style="display:none;">
                            <dt class="text-slate-400 mb-2">Prescription</dt>
                            <dd id="consult-prescription" class="text-xs space-y-2">—</dd>
                        </div>
                    </dl>
                </div>
            </div>

            <div class="col-span-6">
                <div id="billing-statement-card" class="hidden billing-print-card">
                    <div class="clinic-header">
                        <div class="clinic-brand">
                            <img class="clinic-logo" src="../assets/img/logo3.png" alt="Southern Leyte Orthopaedic Clinic logo">
                            <h2 class="clinic-name">Southern Leyte Orthopaedic Clinic</h2>
                        </div>
                        <div class="doctor-line">Dr. Thyam Tiu Fookson, DPBO, FPOA</div>
                        <div class="contact-line">0956 304 2109 | sl_ortho@yahoo.com</div>
                    </div>

                    <div class="billing-divider"></div>

                    <div class="statement-title">Billing Statement</div>

                    <div class="statement-meta">
                        <div class="meta-field">
                            <span>Patient:</span>
                            <span id="statement-patient">________________</span>
                        </div>
                        <div class="meta-field">
                            <span>Date:</span>
                            <span id="statement-date">________________</span>
                        </div>
                    </div>

                    <div class="statement-diagnosis">
                        <span>Diagnosis:</span>
                        <span id="statement-diagnosis">________________</span>
                    </div>

                    <table class="statement-table">
                        <thead>
                            <tr>
                                <th>Description</th>
                                <th>Amount (PHP)</th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr>
                                <td>Consultation Fee</td>
                                <td id="consult-fee">₱0.00</td>
                            </tr>
                            <tr>
                                <td>Medication</td>
                                <td></td>
                            </tr>
                            <tr>
                                <td>Others</td>
                                <td></td>
                            </tr>
                        </tbody>
                    </table>

                    <div class="totals">
                        <div class="total-row">
                            <span>Subtotal</span>
                            <span id="statement-subtotal">₱0.00</span>
                        </div>
                        <div class="total-row">
                            <span>Less: Discount (20%)</span>
                            <span id="statement-discount">₱0.00</span>
                        </div>
                        <div class="total-row grand-total">
                            <span>TOTAL</span>
                            <span id="total-due">₱0.00</span>
                        </div>
                        <div class="paid-row">
                            <span>Paid to Date</span>
                            <span id="statement-paid">₱0.00</span>
                        </div>
                        <div class="total-row">
                            <span>Balance Due</span>
                            <span id="statement-balance-due">₱0.00</span>
                        </div>
                    </div>

                    <div class="doctor-signature">Dr. Thyam T. Fookson, M.D.</div>

                    <div class="print-hidden mt-6" id="amount-paid-field">
                        <label for="amount-paid" class="block text-sm font-semibold text-slate-700 mb-2">Payment
                            Amount</label>
                        <p class="text-xs text-slate-500 mb-2">Partial payments are accepted. Enter any amount up to the outstanding balance.</p>
                        <input type="number" id="amount-paid" placeholder="Enter payment amount" min="0.01" step="0.01"
                            class="w-full py-2 px-3 rounded-lg border border-slate-200 text-gray-600" />
                        <p id="payment-balance-hint" class="mt-2 text-sm font-medium text-slate-600" aria-live="polite"></p>
                    </div>

                    <div id="paid-amount" class="hidden print-hidden mt-6 flex justify-between text-sm font-semibold">
                        <span>Total Paid to Date</span>
                        <span>—</span>
                    </div>

                    <button id="record-btn" class="print-hidden
                        mt-6 w-full bg-green-600 hover:bg-green-700 text-white font-semibold py-3 rounded-lg disabled:opacity-50 disabled:cursor-not-allowed">Record
                        Payment</button>
                    <button id="print-btn" type="button"
                        class="print-hidden hidden mt-3 w-full bg-slate-700 hover:bg-slate-800 text-white font-semibold py-3 rounded-lg">
                        <i class="fas fa-print mr-2" aria-hidden="true"></i>Print Billing Statement</button>
                </div>
            </div>
        </div>

        <?php include '../includes/message-modal.php' ?>
    </section>

    <script>
        window.csrfToken = <?= json_encode($csrfToken, JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_AMP | JSON_HEX_QUOT) ?>;
    </script>
    <script src="../assets/javascript/billing-payment.js"></script>
</body>

</html>