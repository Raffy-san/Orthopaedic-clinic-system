<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="../assets/css/output.css">
    <link rel="stylesheet" href="../assets/css/global.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
    <title>Billing and Payment</title>
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
                        <div class="py-2 border-b border-slate-100 flex justify-between">
                            <dt class="text-slate-400">Prescription</dt>
                            <dd id="consult-prescription">—</dd>
                        </div>
                    </dl>

                    <div class="mt-4">
                        <label class="text-slate-500 text-sm">Discount Eligibility</label>
                        <div class="mt-2 flex gap-2">
                            <button class="px-3 py-1 rounded-lg border text-slate-600 text-sm">None</button>
                            <button class="px-3 py-1 rounded-lg border text-slate-600 text-sm">Senior Citizen
                                (20%)</button>
                            <button class="px-3 py-1 rounded-lg border text-slate-600 text-sm">PWD (20%)</button>
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

                    <button id="record-btn"
                        class="mt-6 w-full bg-green-600 hover:bg-green-700 text-white font-semibold py-3 rounded-lg">Record
                        Payment & Print Receipt</button>
                </div>
            </div>
        </div>

        <script>
            (function () {
                const loadBtn = document.getElementById('load-btn');
                const consultationCard = document.getElementById('consultation-card');
                const receiptCard = document.getElementById('receipt-card');

                loadBtn.addEventListener('click', function () {
                    const pid = document.getElementById('patient-lookup').value || 'PT-2026-0000';

                    // Populate sample values (replace with real fetch later)
                    document.getElementById('patient-name').innerText = 'Jose dela Cruz';
                    document.getElementById('consult-date').innerText = new Date().toLocaleDateString();
                    document.getElementById('consult-doctor').innerText = 'Dr. Villanueva';
                    document.getElementById('consult-diagnosis').innerText = 'Lumbar strain, Grade II';
                    document.getElementById('consult-treatment').innerText = 'Physical therapy + NSAIDs';
                    document.getElementById('consult-prescription').innerText = 'Ibuprofen 400mg · 3x/day · 7 days';

                    document.getElementById('consult-fee').innerText = '₱1,200.00';
                    document.getElementById('total-due').innerText = '₱1,200.00';
                    document.getElementById('or-number').innerText = 'OR-2026-00142';

                    // Show cards
                    consultationCard.classList.remove('hidden');
                    receiptCard.classList.remove('hidden');

                    // Optionally focus the record button
                    document.getElementById('record-btn').focus();
                });
            })();
        </script>
    </section>
</body>

</html>