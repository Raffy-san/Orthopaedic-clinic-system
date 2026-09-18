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
                        <select
                            class="w-full border border-gray-300 bg-white rounded-lg px-4 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500"
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
                            <button class="discount-btn px-3 py-1 rounded-lg border text-slate-600 text-sm"
                                data-discount="None" data-percent="0">None</button>
                            <button class="discount-btn px-3 py-1 rounded-lg border text-slate-600 text-sm"
                                data-discount="Senior Citizen" data-percent="20">Senior Citizen (20%)</button>
                            <button class="discount-btn px-3 py-1 rounded-lg border text-slate-600 text-sm"
                                data-discount="PWD" data-percent="20">PWD (20%)</button>
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
                        <label for="amount-paid" class="block text-sm font-semibold text-slate-700 mb-2">Amount
                            Paid</label>
                        <input type="number" id="amount-paid" placeholder="0.00" step="0.01"
                            class="w-full py-2 px-3 rounded-lg border border-slate-200 text-gray-600" />
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

        <?php include '../includes/message-modal.php' ?>
    </section>

    <script>
        window.csrfToken = <?= json_encode($csrfToken, JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_AMP | JSON_HEX_QUOT) ?>;
    </script>
    <script src="../assets/javascript/billing-payment.js"></script>
</body>

</html>