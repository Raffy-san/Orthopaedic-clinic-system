<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="../assets/css/output.css">
    <link rel="stylesheet" href="../assets/css/global.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
    <link rel="icon" href="../assets/img/rounded-logo.ico" type="image/x-icon">
    <title>Consultation</title>
</head>

<body class="h-screen flex bg-slate-200">
    <?php include_once '../includes/sidebar.php'; ?>

    <section class="flex-1 p-6 overflow-auto">
        <div class="mb-6 space-y-1">
            <h1 class="text-2xl font-bold">Consultation</h1>
            <h3 class="text-sm font-medium text-gray-500">Doctor's consultation workspace</h3>
        </div>

        <div class="grid grid-cols-12 gap-6">
            <!-- Left: Clinic Queue -->
            <div class="col-span-12 md:col-span-4">
                <div class="bg-white rounded-lg shadow-sm p-4">
                    <h2 class="text-lg font-semibold mb-4">Clinic Queue</h2>
                    <div class="space-y-3">
                        <div class="queue-item">
                            <div>
                                <div class="font-medium">Jose dela Cruz</div>
                                <div class="text-xs text-gray-400">PT-2026-0080</div>
                            </div>
                            <span class="text-xs bg-yellow-100 text-yellow-800 px-2 py-1 rounded">Wait: 18 min</span>
                        </div>

                        <div class="queue-item active">
                            <div>
                                <div class="font-medium">Maria Santos</div>
                                <div class="text-xs text-gray-400">PT-2026-0081</div>
                            </div>
                            <span class="text-xs bg-yellow-100 text-yellow-800 px-2 py-1 rounded">Wait: 5 min</span>
                        </div>

                        <div class="queue-item">
                            <div>
                                <div class="font-medium">Carmen Reyes</div>
                                <div class="text-xs text-gray-400">PT-2026-0082</div>
                            </div>
                            <span class="text-xs bg-gray-100 text-gray-600 px-2 py-1 rounded">Wait: Next</span>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Right: Patient details + Consultation form -->
            <div class="col-span-12 md:col-span-8">
                <div class="patient-header p-4 mb-4">
                    <div class="flex items-start justify-between">
                        <div class="flex-1">
                            <h2 class="text-xl font-semibold">Jose dela Cruz</h2>
                            <div class="text-sm text-gray-500">PT-2026-0080 - Existing Patient</div>

                            <div class="mt-4 grid grid-cols-1 sm:grid-cols-3 gap-3">
                                <div class="bg-gray-50 p-3 rounded">
                                    <div class="text-xs text-gray-400">Chief Complaint</div>
                                    <div class="text-sm text-gray-700">Right knee pain</div>
                                </div>
                                <div class="bg-gray-50 p-3 rounded">
                                    <div class="text-xs text-gray-400">Last Visit</div>
                                    <div class="text-sm text-gray-700">Jun 10, 2026</div>
                                </div>
                                <div class="bg-gray-50 p-3 rounded">
                                    <div class="text-xs text-gray-400">Allergies</div>
                                    <div class="text-sm text-gray-700">Penicillin</div>
                                </div>
                            </div>
                        </div>

                        <div class="ml-4">
                            <span class="text-xs bg-orange-100 text-orange-800 px-3 py-1 rounded">In Consultation</span>
                        </div>
                    </div>
                </div>

                <div class="consultation-panel p-6">
                    <h3 class="font-semibold mb-4">Consultation Notes</h3>

                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <div>
                            <label class="text-sm text-gray-600">Diagnosis</label>
                            <input type="text" class="w-full mt-2 input-style"
                                placeholder="e.g., Osteoarthritis, right knee">
                        </div>

                        <div>
                            <label class="text-sm text-gray-600">Treatment Plan</label>
                            <input type="text" class="w-full mt-2 input-style"
                                placeholder="e.g., PT, anti-inflammatory meds">
                        </div>
                    </div>

                    <div class="mt-4">
                        <label class="text-sm text-gray-600">Notes</label>
                        <textarea class="w-full mt-2 textarea-style h-28"
                            placeholder="Additional observations..."></textarea>
                    </div>

                    <div class="mt-4 flex flex-col gap-4">
                        <div class="text-sm text-gray-700">Medication / Prescription Required?</div>
                        <div class="flex gap-2">
                            <button id="addPrescriptionBtn" type="button" class="btn-outline">Yes — Add
                                Prescription</button>
                            <button id="skipPrescriptionBtn" type="button" class="btn-outline">No — Skip</button>
                        </div>

                        <div id="prescriptionDetails" class="hidden mt-4">
                            <label class="text-sm text-gray-600">Prescription Details</label>
                            <input id="prescriptionInput" type="text" class="w-full mt-2 input-style"
                                placeholder="Drug name, dosage, frequency, duration...">
                        </div>

                        <div class="">
                            <button class="btn-primary">Save & Pass to Billing</button>
                        </div>
                    </div>
                </div>
            </div>
    </section>
    </div>

    <script>
        const addPrescriptionBtn = document.getElementById('addPrescriptionBtn');
        const skipPrescriptionBtn = document.getElementById('skipPrescriptionBtn');
        const prescriptionDetails = document.getElementById('prescriptionDetails');

        addPrescriptionBtn.addEventListener('click', () => {
            prescriptionDetails.classList.remove('hidden');
        });

        skipPrescriptionBtn.addEventListener('click', () => {
            prescriptionDetails.classList.add('hidden');
        });
    </script>
</body>

</html>