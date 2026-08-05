<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="../assets/css/output.css">
    <link rel="stylesheet" href="../assets/css/global.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
    <title>Document</title>
</head>

<body class="h-screen flex bg-slate-200">
    <?php include_once '../includes/sidebar.php'; ?>
    <section class="flex-1 p-4 overflow-auto">
        <div class="mb-4 space-y-1">
            <h1 class="text-xl font-bold">Patient Registration</h1>
            <h3 class="text-sm font-medium text-gray-500">Register new patients or update existing records</h3>
        </div>

        <div class="space-y-4">
            <div class="flex flex-col gap-3 lg:flex-row lg:items-center lg:justify-between">
                <div class="rounded-full bg-white px-3 py-2 shadow-sm text-xs text-slate-600 w-full">
                    <div class="flex flex-wrap items-center gap-2">
                        <span id="progressStart" class="rounded-full bg-teal-600 px-3 py-1.5 text-white">Start</span>
                        <span class="text-slate-400">→</span>
                        <span id="progressPatientType" class="rounded-full bg-blue-600 px-3 py-1.5 text-white">Patient Type</span>
                        <span class="text-slate-400">→</span>
                        <span id="progressEnterInfo" class="rounded-full bg-slate-200 px-3 py-1.5 text-slate-500">Enter Info / Validate ID</span>
                        <span class="text-slate-400">→</span>
                        <span id="progressSaveInfo" class="rounded-full bg-slate-200 px-3 py-1.5 text-slate-500">Save Info</span>
                        <span class="text-slate-400">→</span>
                        <span id="progressLoadProfile" class="rounded-full bg-slate-200 px-3 py-1.5 text-slate-500">Load Profile</span>
                        <span class="text-slate-400">→</span>
                        <span id="progressCreateRecord" class="rounded-full bg-slate-200 px-3 py-1.5 text-slate-500">Create Record</span>
                        <span class="text-slate-400">→</span>
                        <span id="progressEnd" class="rounded-full bg-slate-200 px-3 py-1.5 text-slate-500">End</span>
                    </div>
                </div>
            </div>

            <div class="grid gap-4 xl:grid-cols-[1.05fr_1.35fr_0.85fr]">
                <div class="rounded-3xl bg-white p-4 shadow-sm">
                    <div class="mb-4">
                        <h2 class="text-lg font-semibold">Step 1 — Patient Type</h2>
                        <p class="mt-1 text-xs text-slate-500">Is this a new or existing patient?</p>
                    </div>
                    <div class="space-y-3">
                        <button id="newPatientBtn"
                            class="patient-toggle flex w-full items-center justify-center gap-3 rounded-3xl border border-slate-200 bg-slate-50 px-4 py-3 text-slate-700 text-sm font-semibold transition hover:border-slate-300"
                            data-type="new">
                            <i class="fa-solid fa-user text-violet-600"></i>
                            New Patient
                        </button>
                        <button id="existingPatientBtn"
                            class="patient-toggle flex w-full items-center justify-center gap-3 rounded-3xl border border-slate-200 bg-slate-50 px-4 py-3 text-slate-700 text-sm font-semibold transition hover:border-slate-300"
                            data-type="existing">
                            <i class="fa-solid fa-magnifying-glass text-sky-600"></i>
                            Existing Patient
                        </button>
                    </div>
                </div>
                <div class="rounded-3xl bg-white p-4 shadow-sm">
                    <div class="mb-4">
                        <h2 id="step2Title" class="text-lg font-semibold">Step 2 — Patient Information</h2>
                        <p id="step2Description" class="mt-1 text-xs text-slate-500">Select patient type first</p>
                    </div>
                    <div id="step2Content" class="space-y-3">
                        <div class="rounded-3xl border border-slate-200 bg-slate-50 p-3">
                            <label class="text-xs text-slate-600">Full Name</label>
                            <input type="text"
                                class="mt-2 w-full rounded-2xl border border-slate-200 bg-white px-3 py-2 text-slate-700 focus:border-teal-500 focus:outline-none"
                                placeholder="Enter full name" disabled>
                        </div>
                        <div class="rounded-3xl border border-slate-200 bg-slate-50 p-3">
                            <label class="text-xs text-slate-600">Patient ID</label>
                            <input type="text"
                                class="mt-2 w-full rounded-2xl border border-slate-200 bg-white px-3 py-2 text-slate-700 focus:border-teal-500 focus:outline-none"
                                placeholder="Enter patient ID" disabled>
                        </div>
                    </div>
                </div>
                <div class="rounded-3xl bg-white p-4 shadow-sm">
                    <div class="mb-4">
                        <h2 class="text-lg font-semibold">Step 3 — Save & Confirm</h2>
                        <p class="mt-1 text-xs text-slate-500">Review and finalize the registration record</p>
                    </div>
                    <div class="space-y-3 text-sm text-slate-600">
                        <div
                            class="flex items-center justify-between rounded-2xl border border-slate-200 bg-slate-50 px-3 py-2">
                            <span>Patient Type</span>
                            <span id="confirmPatientType" class="text-slate-500">—</span>
                        </div>
                        <div
                            class="flex items-center justify-between rounded-2xl border border-slate-200 bg-slate-50 px-3 py-2">
                            <span>Name</span>
                            <span id="confirmName" class="text-slate-500">—</span>
                        </div>
                        <div
                            class="flex items-center justify-between rounded-2xl border border-slate-200 bg-slate-50 px-3 py-2">
                            <span>Status</span>
                            <span
                                class="rounded-full bg-slate-100 px-3 py-1 text-xs font-semibold text-slate-600">Pending</span>
                        </div>
                    </div>
                    <button
                        class="mt-4 w-full rounded-3xl bg-teal-400 px-4 py-3 text-sm font-semibold text-white transition hover:bg-teal-500"
                        id="saveRecordBtn" disabled>
                        Save & Create Record
                    </button>
                </div>
            </div>
        </div>
    </section>

    <script>
        const newPatientBtn = document.getElementById('newPatientBtn');
        const existingPatientBtn = document.getElementById('existingPatientBtn');
        const step2Title = document.getElementById('step2Title');
        const step2Description = document.getElementById('step2Description');
        const step2Content = document.getElementById('step2Content');
        const saveRecordBtn = document.getElementById('saveRecordBtn');
        const confirmPatientType = document.getElementById('confirmPatientType');
        const confirmName = document.getElementById('confirmName');
        const progressEnterInfo = document.getElementById('progressEnterInfo');
        const progressSaveInfo = document.getElementById('progressSaveInfo');
        const progressLoadProfile = document.getElementById('progressLoadProfile');
        const progressCreateRecord = document.getElementById('progressCreateRecord');
        const progressEnd = document.getElementById('progressEnd');

        let currentPatientType = '';
        let currentPatientName = '';

        function setProgressActive(element) {
            element.classList.remove('bg-slate-200', 'text-slate-500');
            element.classList.add('bg-blue-600', 'text-white');
        }

        function activateEnterInfoStep() {
            setProgressActive(progressEnterInfo);
        }

        function activateCompletionSteps() {
            [progressSaveInfo, progressLoadProfile, progressCreateRecord, progressEnd].forEach(setProgressActive);
        }

        function updateConfirmationStep() {
            confirmPatientType.textContent = currentPatientType || '—';
            confirmName.textContent = currentPatientName || '—';
        }

        function bindNameInputs() {
            const firstNameInput = document.getElementById('firstNameInput');
            const lastNameInput = document.getElementById('lastNameInput');
            const existingNameInput = document.getElementById('existingNameInput');

            if (firstNameInput || lastNameInput) {
                const updateName = () => {
                    const firstName = firstNameInput?.value.trim() || '';
                    const lastName = lastNameInput?.value.trim() || '';
                    currentPatientName = [firstName, lastName].filter(Boolean).join(' ');
                    updateConfirmationStep();
                };

                firstNameInput?.addEventListener('input', updateName);
                lastNameInput?.addEventListener('input', updateName);
            }

            if (existingNameInput) {
                existingNameInput.addEventListener('input', () => {
                    currentPatientName = existingNameInput.value.trim();
                    updateConfirmationStep();
                });
            }
        }

        function setActivePatientType(type) {
            currentPatientType = type === 'new' ? 'New Patient' : 'Existing Patient';
            currentPatientName = '';
            updateConfirmationStep();

            newPatientBtn.classList.remove('border-slate-200', 'bg-slate-50', 'text-slate-700');
            newPatientBtn.classList.add('border-slate-200', 'bg-slate-50', 'text-slate-700');
            existingPatientBtn.classList.remove('border-slate-200', 'bg-slate-50', 'text-slate-700');
            existingPatientBtn.classList.add('border-slate-200', 'bg-slate-50', 'text-slate-700');

            if (type === 'new') {
                newPatientBtn.classList.add('border-blue-500', 'bg-blue-50', 'text-blue-700');
                existingPatientBtn.classList.remove('border-blue-500', 'bg-blue-50', 'text-blue-700');
                step2Title.textContent = 'Step 2 — Patient Information';
                step2Description.textContent = 'Enter basic patient information';
                step2Content.innerHTML = `
                    <div class="rounded-2xl border border-slate-200 bg-slate-50 p-2.5">
                        <label class="text-[11px] text-slate-600">First Name</label>
                        <input id="firstNameInput" type="text" class="mt-1 w-full rounded-xl border border-slate-200 bg-white px-2.5 py-1.5 text-xs text-slate-700 focus:border-teal-500 focus:outline-none" placeholder="Enter first name">
                    </div>
                    <div class="rounded-2xl border border-slate-200 bg-slate-50 p-2.5">
                        <label class="text-[11px] text-slate-600">Last Name</label>
                        <input id="lastNameInput" type="text" class="mt-1 w-full rounded-xl border border-slate-200 bg-white px-2.5 py-1.5 text-xs text-slate-700 focus:border-teal-500 focus:outline-none" placeholder="Enter last name">
                    </div>
                    <div class="rounded-2xl border border-slate-200 bg-slate-50 p-2.5">
                        <label class="text-[11px] text-slate-600">Date of Birth</label>
                        <input type="date" class="mt-1 w-full rounded-xl border border-slate-200 bg-white px-2.5 py-1.5 text-xs text-slate-700 focus:border-teal-500 focus:outline-none">
                    </div>
                    <div class="rounded-2xl border border-slate-200 bg-slate-50 p-2.5">
                        <label class="text-[11px] text-slate-600">Phone</label>
                        <input type="tel" class="mt-1 w-full rounded-xl border border-slate-200 bg-white px-2.5 py-1.5 text-xs text-slate-700 focus:border-teal-500 focus:outline-none" placeholder="Enter phone number">
                    </div>
                    <div class="rounded-2xl border border-slate-200 bg-slate-50 p-2.5">
                        <label class="text-[11px] text-slate-600">Gender</label>
                        <select class="mt-1 w-full rounded-xl border border-slate-200 bg-white px-2.5 py-1.5 text-xs text-slate-700 focus:border-teal-500 focus:outline-none">
                            <option value="">Select...</option>
                            <option value="male">Male</option>
                            <option value="female">Female</option>
                            <option value="other">Other</option>
                        </select>
                    </div>
                `;
                saveRecordBtn.disabled = false;
            } else {
                existingPatientBtn.classList.add('border-blue-500', 'bg-blue-50', 'text-blue-700');
                newPatientBtn.classList.remove('border-blue-500', 'bg-blue-50', 'text-blue-700');
                step2Title.textContent = 'Step 2 — Validate Identity';
                step2Description.textContent = 'Enter Patient ID to look up record';
                step2Content.innerHTML = `
                    <div class="rounded-2xl border border-slate-200 bg-slate-50 p-2.5">
                        <label class="text-[11px] text-slate-600">Patient ID</label>
                        <input id="existingPatientIdInput" type="text" class="mt-1 w-full rounded-xl border border-slate-200 bg-white px-2.5 py-1.5 text-xs text-slate-700 focus:border-teal-500 focus:outline-none" placeholder="PT-YYYY-XXXX">
                    </div>
                    <div class="rounded-2xl border border-slate-200 bg-slate-50 p-2.5">
                        <label class="text-[11px] text-slate-600">Patient Name</label>
                        <input id="existingNameInput" type="text" class="mt-1 w-full rounded-xl border border-slate-200 bg-white px-2.5 py-1.5 text-xs text-slate-700 focus:border-teal-500 focus:outline-none" placeholder="Type existing patient name">
                    </div>
                    <button class="mt-2 w-full rounded-2xl bg-sky-400 px-3 py-2 text-xs text-white font-semibold hover:bg-sky-500" type="button">Validate & Load Profile</button>
                `;
                saveRecordBtn.disabled = false;
            }

            bindNameInputs();
            activateEnterInfoStep();
        }

        newPatientBtn.addEventListener('click', () => setActivePatientType('new'));
        existingPatientBtn.addEventListener('click', () => setActivePatientType('existing'));
        saveRecordBtn.addEventListener('click', activateCompletionSteps);
    </script>
</body>

</html>