let activeConsultationId = null;
let searchDebounce = null;
let completedConsultationData = null;

function formatDate(dateStr) {
    const d = new Date(dateStr);
    return d.toLocaleDateString('en-US', { month: 'short', day: 'numeric', year: 'numeric' });
}

function statusBadge(isCompleted) {
    return isCompleted == 1
        ? '<span class="text-xs font-semibold px-3 py-1 rounded-full bg-emerald-100 text-emerald-700">Completed</span>'
        : '<span class="text-xs font-semibold px-3 py-1 rounded-full bg-amber-100 text-amber-700">Ongoing</span>';
}

/**
 * Collects both the basic search box and every advanced filter field
 * into one query string for fetch-consultation-list.php
 */
function getSearchParams() {
    const params = new URLSearchParams();

    const search = document.getElementById('recordSearchInput').value.trim();
    const diagnosis = document.getElementById('filterDiagnosis').value.trim();
    const dateFrom = document.getElementById('filterDateFrom').value;
    const dateTo = document.getElementById('filterDateTo').value;
    const doctorId = document.getElementById('filterDoctor').value;

    if (search) params.set('search', search);
    if (diagnosis) params.set('diagnosis', diagnosis);
    if (dateFrom) params.set('date_from', dateFrom);
    if (dateTo) params.set('date_to', dateTo);
    if (doctorId) params.set('doctor_id', doctorId);

    return params;
}

async function loadRecordsList() {
    const container = document.getElementById('recordListContainer');
    try {
        const params = getSearchParams();
        const res = await fetch(`../php/fetch/fetch-consultation-list.php?${params.toString()}`);
        const data = await res.json();

        if (data.status !== 'success') {
            container.innerHTML = `<p class="text-sm text-red-600">${data.message}</p>`;
            return;
        }

        const records = data.data;
        if (records.length === 0) {
            container.innerHTML = '<p class="text-sm text-slate-400 text-center py-8">No records found</p>';
            return;
        }

        container.innerHTML = records.map(r => `
            <div class="record-item cursor-pointer overflow-hidden rounded-xl border p-4 transition ${r.ConsultationID == activeConsultationId
                ? 'bg-blue-50 border-slate-200 border-l-4 border-l-blue-600'
                : 'border-slate-200 hover:bg-slate-50'
            }" data-consultation-id="${r.ConsultationID}" data-patient-id="${r.PatientID}">
                <div class="flex items-start justify-between gap-2">
                    <div>
                        <div class="font-semibold text-slate-900">${r.FirstName} ${r.LastName}</div>
                        <div class="text-xs text-slate-400 mt-0.5">${r.PatientCode}</div>
                        <div class="text-xs text-slate-400 mt-1">${formatDate(r.ConsultationDate)}</div>
                        ${r.DoctorFirstName ? `<div class="text-xs text-slate-400">Dr. ${r.DoctorFirstName} ${r.DoctorLastName}</div>` : ''}
                    </div>
                    ${statusBadge(r.IsCompleted)}
                </div>
            </div>
        `).join('');

        container.querySelectorAll('.record-item').forEach((el, i) => {
            el.addEventListener('click', () => selectRecord(el, records[i]));
        });

    } catch (err) {
        console.error(err);
        container.innerHTML = '<p class="text-sm text-red-600">Failed to load records.</p>';
    }
}

async function loadDoctorOptions() {
    const select = document.getElementById('filterDoctor');
    try {
        const res = await fetch('../php/fetch/fetch-doctors.php');
        const data = await res.json();

        if (data.status === 'success') {
            data.data.forEach(doc => {
                const opt = document.createElement('option');
                opt.value = doc.UserID;
                opt.textContent = `${doc.FirstName} ${doc.LastName}`;
                select.appendChild(opt);
            });
        }
    } catch (err) {
        console.error('Failed to load doctors:', err);
    }
}

function selectRecord(el, recordData) {
    activeConsultationId = el.dataset.consultationId;

    document.querySelectorAll('.record-item').forEach(item => {
        item.classList.remove('bg-blue-50', 'border-l-4', 'border-l-blue-600');
        item.classList.add('border-slate-200');
    });
    el.classList.add('bg-blue-50', 'border-l-4', 'border-l-blue-600');

    document.dispatchEvent(new CustomEvent('recordSelected', {
        detail: {
            consultationId: el.dataset.consultationId,
            patientId: el.dataset.patientId,
            recordSummary: recordData,
        }
    }));
}

document.addEventListener('recordSelected', async (e) => {
    const { consultationId, patientId, recordSummary } = e.detail;

    try {
        const res = await fetch(`../php/fetch/fetch-patient-records.php?patient_id=${encodeURIComponent(patientId)}`);
        const data = await res.json();

        if (data.status === 'success') {
            loadPatientRecords(data.data, consultationId, recordSummary);
        } else {
            console.error(data.message);
        }
    } catch (err) {
        console.error(err);
    }
});

function loadPatientRecords(consultationHistory, currentConsultationId, recordSummary) {
    completedConsultationData = consultationHistory;

    const current = consultationHistory.find(c => c.ConsultationID == currentConsultationId);
    if (!current) return;

    document.getElementById('patientName').textContent = `${recordSummary.FirstName} ${recordSummary.LastName}`;
    document.getElementById('patientCode').textContent = recordSummary.PatientCode;
    document.getElementById('consultationDate').textContent = formatDate(current.ConsultationDate);
    document.getElementById('doctorName').textContent = current.DoctorFirstName && current.DoctorLastName ? `${current.DoctorFirstName} ${current.DoctorLastName}` : 'N/A';
    document.getElementById('historyPatientName').textContent = `${recordSummary.FirstName} ${recordSummary.LastName}`;

    document.getElementById('patientID').value = recordSummary.PatientID ?? '';
    document.getElementById('consultationID').value = current.ConsultationID;

    document.getElementById('currentDiagnosis').textContent = current.Diagnosis;
    document.getElementById('currentTreatment').textContent = current.Treatment;

    const medsBody = document.getElementById('prescribedMedicinesBody');
    medsBody.innerHTML = current.Medicines.map(m => `
        <tr class="border-t border-slate-100">
            <td class="py-2">${m.Medicine}</td>
            <td class="py-2">${m.Dosage}</td>
            <td class="py-2">${m.Frequency}</td>
            <td class="py-2">${m.Duration}</td>
        </tr>
    `).join('');

    const historyList = document.getElementById('diagnosisHistoryList');
    historyList.innerHTML = consultationHistory.map(c => `
        <div class="flex gap-3">
            <span class="mt-1.5 h-2 w-2 flex-shrink-0 rounded-full ${c.ConsultationID == currentConsultationId ? 'bg-blue-600' : 'bg-slate-300'}"></span>
            <div>
                <p class="text-xs text-slate-400">${formatDate(c.ConsultationDate)} · Dr. ${c.DoctorName ?? ''}</p>
                <p class="font-medium text-slate-800">${c.Diagnosis}</p>
                ${c.ConsultationID == currentConsultationId ? '<a href="#" class="text-xs text-blue-600">← Current visit</a>' : ''}
            </div>
        </div>
    `).join('');

    document.getElementById('patientRecordsContainer').classList.remove('hidden');
    document.getElementById('prescribedMedicinesContainer').classList.remove('hidden');
    document.getElementById('diagnosisHistoryContainer').classList.remove('hidden');
    document.getElementById('noRecordsContainer').classList.add('hidden');
}

document.addEventListener('DOMContentLoaded', () => {
    loadRecordsList();
    loadDoctorOptions();

    // Basic search box — debounced as before
    document.getElementById('recordSearchInput').addEventListener('input', () => {
        clearTimeout(searchDebounce);
        searchDebounce = setTimeout(loadRecordsList, 300);
    });

    // Advanced search panel toggle
    const panel = document.getElementById('advancedSearchPanel');
    const chevron = document.getElementById('advancedSearchChevron');
    document.getElementById('toggleAdvancedSearch').addEventListener('click', () => {
        panel.classList.toggle('hidden');
        chevron.classList.toggle('fa-chevron-down');
        chevron.classList.toggle('fa-chevron-up');
    });

    // Apply filters button
    document.getElementById('applyFiltersBtn').addEventListener('click', loadRecordsList);

    // Clear filters button
    document.getElementById('clearFiltersBtn').addEventListener('click', () => {
        document.getElementById('filterDiagnosis').value = '';
        document.getElementById('filterDateFrom').value = '';
        document.getElementById('filterDateTo').value = '';
        document.getElementById('filterDoctor').value = '';
        loadRecordsList();
    });
});