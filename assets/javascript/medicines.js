// Put in: assets/javascript/medicines.js
let csrfToken = window.csrfToken || "";
let medicines = [];

const esc = s => String(s ?? '').replace(/[&<>"']/g, c => ({
    '&': '&amp;', '<': '&lt;', '>': '&gt;', '"': '&quot;', "'": '&#39;'
}[c]));

async function loadMedicineList() {
    try {
        const response = await fetch('../php/fetch/fetch-medicines.php?all=1');
        const result = await response.json();

        if (result.status === 'success') {
            medicines = result.data;
            renderTable();
        }
    } catch (error) {
        console.error('Error loading medicines:', error);
    }
}

function renderTable() {
    const term = document.getElementById('medicineSearchInput').value.trim().toLowerCase();
    const body = document.getElementById('medicineTableBody');

    const rows = medicines
        .map((m, i) => ({ m, i }))
        .filter(({ m }) => m.Name.toLowerCase().includes(term));

    document.getElementById('medicineEmpty').classList.toggle('hidden', rows.length > 0);

    body.innerHTML = rows.map(({ m, i }) => {
        const active = Number(m.IsActive) === 1;
        return `
            <tr class="border-t border-slate-100 ${active ? '' : 'opacity-50'}">
                <td class="py-2 font-semibold text-slate-800">${esc(m.Name)}</td>
                <td class="py-2 text-slate-600">${esc(m.DefaultDosage)}</td>
                <td class="py-2">
                    <span class="text-xs px-2 py-1 rounded-full font-semibold ${active ? 'bg-emerald-100 text-emerald-700' : 'bg-slate-200 text-slate-600'}">
                        ${active ? 'Active' : 'Hidden'}
                    </span>
                </td>
                <td class="py-2 whitespace-nowrap text-right">
                    <button type="button" class="edit-btn text-xs font-semibold text-green-700 hover:text-green-800 mr-3" data-index="${i}">Edit</button>
                    <button type="button" class="toggle-btn text-xs font-semibold text-slate-600 hover:text-slate-800" data-id="${m.MedicineID}" data-active="${active ? 0 : 1}">
                        ${active ? 'Hide' : 'Show'}
                    </button>
                </td>
            </tr>
        `;
    }).join('');
}

async function postMedicine(payload) {
    const response = await fetch('../php/add/save-medicine.php', {
        method: 'POST',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify({ ...payload, csrf_token: csrfToken })
    });
    const result = await response.json();
    if (result.csrf_token) csrfToken = result.csrf_token; // resync if the server rotates it
    return result;
}

function showFormMessage(text, ok) {
    const el = document.getElementById('formMessage');
    el.textContent = text;
    el.className = 'text-xs ' + (ok ? 'text-green-600' : 'text-red-600');
}

function resetForm() {
    document.getElementById('medicineForm').reset();
    document.getElementById('medicineId').value = '';
    document.getElementById('formTitle').textContent = 'Add Medicine';
    document.getElementById('saveBtn').textContent = 'Add Medicine';
    document.getElementById('cancelEditBtn').classList.add('hidden');
}

function editMedicine(index) {
    const m = medicines[index];
    document.getElementById('medicineId').value = m.MedicineID;
    document.getElementById('mName').value = m.Name;
    document.getElementById('mDosage').value = m.DefaultDosage || '';
    document.getElementById('formTitle').textContent = 'Edit Medicine';
    document.getElementById('saveBtn').textContent = 'Update Medicine';
    document.getElementById('cancelEditBtn').classList.remove('hidden');
    document.getElementById('formMessage').className = 'text-xs hidden';
}

document.getElementById('medicineTableBody').addEventListener('click', async e => {
    const editBtn = e.target.closest('.edit-btn');
    const toggleBtn = e.target.closest('.toggle-btn');

    if (editBtn) {
        editMedicine(parseInt(editBtn.dataset.index, 10));
    } else if (toggleBtn) {
        const result = await postMedicine({
            action: 'toggle',
            medicine_id: toggleBtn.dataset.id,
            is_active: toggleBtn.dataset.active
        });
        if (result.status === 'success') loadMedicineList();
        else showFormMessage(result.message, false);
    }
});

document.getElementById('medicineSearchInput').addEventListener('input', renderTable);
document.getElementById('cancelEditBtn').addEventListener('click', resetForm);

document.getElementById('medicineForm').addEventListener('submit', async e => {
    e.preventDefault();

    const id = document.getElementById('medicineId').value;
    const saveBtn = document.getElementById('saveBtn');
    saveBtn.disabled = true;

    try {
        const result = await postMedicine({
            action: id ? 'update' : 'add',
            medicine_id: id,
            name: document.getElementById('mName').value,
            dosage: document.getElementById('mDosage').value
        });

        if (result.status === 'success') {
            resetForm();
            showFormMessage(id ? 'Medicine updated.' : 'Medicine added.', true);
            loadMedicineList();
        } else {
            showFormMessage(result.message || 'Unable to save medicine.', false);
        }
    } catch (error) {
        console.error('Error saving medicine:', error);
        showFormMessage('An error occurred while saving.', false);
    } finally {
        saveBtn.disabled = false;
    }
});

document.addEventListener('DOMContentLoaded', loadMedicineList);