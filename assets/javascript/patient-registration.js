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
                    <form id="addPatientForm" class="grid grid-cols-1 gap-3 md:grid-cols-2">
                        <input type="hidden" name="csrf_token" value="${csrfToken}">
                        <div class="rounded-2xl border border-slate-200 bg-slate-50 p-2.5">
                            <label class="text-[11px] text-slate-600">First Name</label>
                            <input id="firstNameInput" name="firstName" type="text" required class="mt-1 w-full rounded-xl border border-slate-200 bg-white px-2.5 py-1.5 text-xs text-slate-700 focus:border-teal-500 focus:outline-none" placeholder="Enter first name">
                        </div>
                        <div class="rounded-2xl border border-slate-200 bg-slate-50 p-2.5">
                            <label class="text-[11px] text-slate-600">Last Name</label>
                            <input id="lastNameInput" name="lastName" type="text" required class="mt-1 w-full rounded-xl border border-slate-200 bg-white px-2.5 py-1.5 text-xs text-slate-700 focus:border-teal-500 focus:outline-none" placeholder="Enter last name">
                        </div>
                        <div class="rounded-2xl border border-slate-200 bg-slate-50 p-2.5">
                            <label class="text-[11px] text-slate-600">Date of Birth</label>
                            <input name="birthDate" type="date" required class="mt-1 w-full rounded-xl border border-slate-200 bg-white px-2.5 py-1.5 text-xs text-slate-700 focus:border-teal-500 focus:outline-none">
                        </div>
                        <div class="rounded-2xl border border-slate-200 bg-slate-50 p-2.5">
                            <label class="text-[11px] text-slate-600">Phone</label>
                            <input name="phone" type="tel" class="mt-1 w-full rounded-xl border border-slate-200 bg-white px-2.5 py-1.5 text-xs text-slate-700 focus:border-teal-500 focus:outline-none" placeholder="Enter phone number">
                        </div>
                        <div class="rounded-2xl border border-slate-200 bg-slate-50 p-2.5">
                            <label class="text-[11px] text-slate-600">Address</label>
                            <input name="address" type="text" class="mt-1 w-full rounded-xl border border-slate-200 bg-white px-2.5 py-1.5 text-xs text-slate-700 focus:border-teal-500 focus:outline-none" placeholder="Enter address">
                        </div>
                        <div class="rounded-2xl border border-slate-200 bg-slate-50 p-2.5">
                            <label class="text-[11px] text-slate-600">Initial Password</label>
                            <input name="password" type="password" minlength="8" required class="mt-1 w-full rounded-xl border border-slate-200 bg-white px-2.5 py-1.5 text-xs text-slate-700 focus:border-teal-500 focus:outline-none" placeholder="At least 8 characters">
                        </div>
                        <div class="rounded-2xl border border-slate-200 bg-slate-50 p-2.5">
                            <label class="text-[11px] text-slate-600">Gender</label>
                            <select name="gender" required class="mt-1 w-full rounded-xl border border-slate-200 bg-white px-2.5 py-1.5 text-xs text-slate-700 focus:border-teal-500 focus:outline-none">
                                <option value="">Select...</option>
                                <option value="Male">Male</option>
                                <option value="Female">Female</option>
                                <option value="Other">Other</option>
                            </select>
                        </div>
                        <div class="rounded-2xl border border-slate-200 bg-slate-50 p-2.5">
                            <label class="text-[11px] text-slate-600">Patient Type</label>
                            <select name="patientType" class="mt-1 w-full rounded-xl border border-slate-200 bg-white px-2.5 py-1.5 text-xs text-slate-700 focus:border-teal-500 focus:outline-none">
                                <option value="Regular">Regular</option>
                                <option value="Senior Citizen">Senior Citizen</option>
                                <option value="PWD">PWD</option>
                            </select>
                        </div>
                    </form>
                `;
        saveRecordBtn.disabled = false;
    } else {
        existingPatientBtn.classList.add('border-blue-500', 'bg-blue-50', 'text-blue-700');
        newPatientBtn.classList.remove('border-blue-500', 'bg-blue-50', 'text-blue-700');
        step2Title.textContent = 'Step 2 — Validate Identity';
        step2Description.textContent = 'Enter Patient ID to look up record';
        step2Content.innerHTML = `
                <div class="grid grid-cols-1 gap-3 md:grid-cols-2">
                    <div class="rounded-2xl border border-slate-200 bg-slate-50 p-2.5">
                        <label class="text-[11px] text-slate-600">Patient ID</label>
                        <input id="existingPatientIdInput" type="text" class="mt-1 w-full rounded-xl border border-slate-200 bg-white px-2.5 py-1.5 text-xs text-slate-700 focus:border-teal-500 focus:outline-none" placeholder="PT-YYYY-XXXX">
                    </div>
                    <div class="rounded-2xl border border-slate-200 bg-slate-50 p-2.5">
                        <label class="text-[11px] text-slate-600">Patient Name</label>
                        <input id="existingNameInput" type="text" class="mt-1 w-full rounded-xl border border-slate-200 bg-white px-2.5 py-1.5 text-xs text-slate-700 focus:border-teal-500 focus:outline-none" placeholder="Type existing patient name">
                    </div>
                    <button class="col-span-1 mt-2 w-full rounded-2xl bg-sky-400 px-3 py-2 text-xs text-white font-semibold hover:bg-sky-500 md:col-span-2" type="button">Validate & Load Profile</button>
                    </div>
                `;
        saveRecordBtn.disabled = true;
    }

    bindNameInputs();
    activateEnterInfoStep();
}

newPatientBtn.addEventListener('click', () => setActivePatientType('new'));
existingPatientBtn.addEventListener('click', () => setActivePatientType('existing'));
let csrfToken = window.csrfToken || "";

saveRecordBtn.addEventListener('click', () => {
    const newPatientForm = document.getElementById('addPatientForm');
    const updatePatientForm = document.getElementById('updatePatientForm');
    
    if (newPatientForm) {
        newPatientForm.requestSubmit();
    } else if (updatePatientForm) {
        updatePatientForm.requestSubmit();
    }
});

step2Content.addEventListener('submit', (event) => {
    if (event.target.id === 'addPatientForm') {
        event.preventDefault();
        saveRecordBtn.disabled = true;
        saveRecordBtn.textContent = 'Saving...';

        const formData = new FormData(event.target);
        formData.set('csrf_token', csrfToken);

        fetch('../php/add/add-patient.php', {
            method: 'POST',
            body: formData,
            headers: { 'X-Requested-With': 'XMLHttpRequest' }
        })
            .then(response => response.json())
            .then(data => {
                if (data.csrf_token) {
                    csrfToken = data.csrf_token;
                }
                if (data.status !== 'success') {
                    throw new Error(data.message || 'Registration failed.');
                }
                alert(`${data.message} Patient ID: ${data.patient_code}`);
                activateCompletionSteps();
                event.target.reset();
            })
            .catch(error => alert(error.message || 'Registration failed.'))
            .finally(() => {
                saveRecordBtn.disabled = false;
                saveRecordBtn.textContent = 'Save & Create Record';
            });
    } else if (event.target.id === 'updatePatientForm') {
        event.preventDefault();
        saveRecordBtn.disabled = true;
        saveRecordBtn.textContent = 'Saving...';

        const formData = new FormData(event.target);
        const data = {
            csrf_token: csrfToken,
            patient_code: formData.get('patient_code'),
            firstName: formData.get('firstName'),
            lastName: formData.get('lastName'),
            birthDate: formData.get('birthDate'),
            phone: formData.get('phone'),
            address: formData.get('address'),
            gender: formData.get('gender'),
            patientType: formData.get('patientType')
        };

        fetch('../php/add/update-existing-patient.php', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-Requested-With': 'XMLHttpRequest'
            },
            body: JSON.stringify(data)
        })
            .then(response => response.json())
            .then(data => {
                if (data.csrf_token) {
                    csrfToken = data.csrf_token;
                }
                if (data.status !== 'success') {
                    throw new Error(data.message || 'Update failed.');
                }
                alert(data.message);
                activateCompletionSteps();
                event.target.reset();
            })
            .catch(error => alert(error.message || 'Update failed.'))
            .finally(() => {
                saveRecordBtn.disabled = false;
                saveRecordBtn.textContent = 'Save & Update Record';
            });
    }
});

step2Content.addEventListener('click', (event) => {
    if (event.target.textContent === 'Validate & Load Profile') {
        event.preventDefault();
        validateExistingPatient();
    }
});

function validateExistingPatient() {
    const patientIdInput = document.getElementById('existingPatientIdInput');
    const patientId = patientIdInput?.value.trim();

    if (!patientId) {
        alert('Please enter a Patient ID');
        return;
    }

    fetch(`../php/fetch/get-patient.php?patient_code=${encodeURIComponent(patientId)}`, {
        method: 'GET',
        headers: { 'X-Requested-With': 'XMLHttpRequest' }
    })
        .then(response => response.json())
        .then(data => {
            if (data.status !== 'success') {
                throw new Error(data.message || 'Patient not found');
            }

            const patient = data.patient;
            currentPatientName = `${patient.FirstName} ${patient.LastName}`;
            updateConfirmationStep();

            step2Content.innerHTML = `
                <form id="updatePatientForm" class="grid grid-cols-1 gap-3 md:grid-cols-2">
                    <input type="hidden" name="csrf_token" value="${csrfToken}">
                    <input type="hidden" name="patient_code" value="${patient.PatientCode}">
                    
                    <div class="rounded-2xl border border-slate-200 bg-slate-50 p-2.5">
                        <label class="text-[11px] text-slate-600">Patient ID</label>
                        <input type="text" disabled value="${patient.PatientCode}" class="mt-1 w-full rounded-xl border border-slate-200 bg-white px-2.5 py-1.5 text-xs text-slate-700">
                    </div>
                    
                    <div class="rounded-2xl border border-slate-200 bg-slate-50 p-2.5">
                        <label class="text-[11px] text-slate-600">First Name</label>
                        <input id="existingFirstNameInput" name="firstName" type="text" value="${patient.FirstName}" class="mt-1 w-full rounded-xl border border-slate-200 bg-white px-2.5 py-1.5 text-xs text-slate-700 focus:border-teal-500 focus:outline-none">
                    </div>
                    
                    <div class="rounded-2xl border border-slate-200 bg-slate-50 p-2.5">
                        <label class="text-[11px] text-slate-600">Last Name</label>
                        <input id="existingLastNameInput" name="lastName" type="text" value="${patient.LastName}" class="mt-1 w-full rounded-xl border border-slate-200 bg-white px-2.5 py-1.5 text-xs text-slate-700 focus:border-teal-500 focus:outline-none">
                    </div>
                    
                    <div class="rounded-2xl border border-slate-200 bg-slate-50 p-2.5">
                        <label class="text-[11px] text-slate-600">Date of Birth</label>
                        <input name="birthDate" type="date" value="${patient.BirthDate}" class="mt-1 w-full rounded-xl border border-slate-200 bg-white px-2.5 py-1.5 text-xs text-slate-700 focus:border-teal-500 focus:outline-none">
                    </div>
                    
                    <div class="rounded-2xl border border-slate-200 bg-slate-50 p-2.5">
                        <label class="text-[11px] text-slate-600">Phone</label>
                        <input name="phone" type="tel" value="${patient.Phone || ''}" class="mt-1 w-full rounded-xl border border-slate-200 bg-white px-2.5 py-1.5 text-xs text-slate-700 focus:border-teal-500 focus:outline-none">
                    </div>
                    
                    <div class="rounded-2xl border border-slate-200 bg-slate-50 p-2.5">
                        <label class="text-[11px] text-slate-600">Address</label>
                        <input name="address" type="text" value="${patient.Address || ''}" class="mt-1 w-full rounded-xl border border-slate-200 bg-white px-2.5 py-1.5 text-xs text-slate-700 focus:border-teal-500 focus:outline-none">
                    </div>
                    
                    <div class="rounded-2xl border border-slate-200 bg-slate-50 p-2.5">
                        <label class="text-[11px] text-slate-600">Gender</label>
                        <select name="gender" class="mt-1 w-full rounded-xl border border-slate-200 bg-white px-2.5 py-1.5 text-xs text-slate-700 focus:border-teal-500 focus:outline-none">
                            <option value="">Select...</option>
                            <option value="Male" ${patient.Gender === 'Male' ? 'selected' : ''}>Male</option>
                            <option value="Female" ${patient.Gender === 'Female' ? 'selected' : ''}>Female</option>
                            <option value="Other" ${patient.Gender === 'Other' ? 'selected' : ''}>Other</option>
                        </select>
                    </div>
                    
                    <div class="rounded-2xl border border-slate-200 bg-slate-50 p-2.5">
                        <label class="text-[11px] text-slate-600">Patient Type</label>
                        <select name="patientType" class="mt-1 w-full rounded-xl border border-slate-200 bg-white px-2.5 py-1.5 text-xs text-slate-700 focus:border-teal-500 focus:outline-none">
                            <option value="Regular" ${patient.PatientType === 'Regular' ? 'selected' : ''}>Regular</option>
                            <option value="Senior Citizen" ${patient.PatientType === 'Senior Citizen' ? 'selected' : ''}>Senior Citizen</option>
                            <option value="PWD" ${patient.PatientType === 'PWD' ? 'selected' : ''}>PWD</option>
                        </select>
                    </div>
                </form>
            `;

            saveRecordBtn.disabled = false;
            saveRecordBtn.textContent = 'Save & Update Record';

            bindNameInputs();
            activateEnterInfoStep();
        })
        .catch(error => alert(error.message || 'Failed to load patient'));
}