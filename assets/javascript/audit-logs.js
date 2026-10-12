let currentPage = 1;
let totalRecords = 0;
const PER_PAGE = 25;

function escapeHtml(value) {
    const div = document.createElement('div');
    div.textContent = value ?? '';
    return div.innerHTML;
}

function formatLogDate(value) {
    const d = new Date(String(value).replace(' ', 'T'));
    return Number.isNaN(d.getTime())
        ? value
        : d.toLocaleString('en-US', { month: 'short', day: 'numeric', year: 'numeric', hour: 'numeric', minute: '2-digit' });
}

function actionBadge(action) {
    const styles = {
        CREATE: 'bg-emerald-100 text-emerald-700',
        UPDATE: 'bg-amber-100 text-amber-700',
        DELETE: 'bg-red-100 text-red-700'
    };
    return `<span class="rounded-full px-3 py-1 text-xs font-semibold ${styles[action] || 'bg-slate-100 text-slate-600'}">${escapeHtml(action)}</span>`;
}

// Short values show inline; long ones (like JSON) expand on click
function valueCell(value) {
    if (value === null || value === '') {
        return '<span class="text-slate-300">—</span>';
    }
    const text = String(value);
    if (text.length <= 60) {
        return escapeHtml(text);
    }
    return `<details><summary class="cursor-pointer text-blue-600">${escapeHtml(text.slice(0, 60))}…</summary>
        <pre class="mt-1 max-w-xs whitespace-pre-wrap break-words text-xs text-slate-600">${escapeHtml(text)}</pre></details>`;
}

function getFilterParams() {
    const params = new URLSearchParams();
    const map = {
        user_id: 'filterUser',
        action: 'filterAction',
        table: 'filterTable',
        date_from: 'filterDateFrom',
        date_to: 'filterDateTo',
        search: 'filterSearch'
    };
    Object.entries(map).forEach(([key, id]) => {
        const value = document.getElementById(id).value.trim();
        if (value) params.set(key, value);
    });
    return params;
}

async function loadLogs(page = 1, withMeta = false) {
    const body = document.getElementById('auditLogBody');
    const params = getFilterParams();
    params.set('page', page);
    if (withMeta) params.set('meta', '1');

    try {
        const response = await fetch(`../php/fetch/fetch-audit-logs.php?${params.toString()}`);
        const result = await response.json();

        if (result.status !== 'success') {
            body.innerHTML = `<tr><td colspan="9" class="py-8 text-center text-red-600">${escapeHtml(result.message)}</td></tr>`;
            return;
        }

        if (withMeta) {
            const tableSelect = document.getElementById('filterTable');
            (result.tables || []).forEach(t => {
                const opt = document.createElement('option');
                opt.value = t;
                opt.textContent = t;
                tableSelect.appendChild(opt);
            });
            const userSelect = document.getElementById('filterUser');
            (result.users || []).forEach(u => {
                const opt = document.createElement('option');
                opt.value = u.UserID;
                opt.textContent = u.Name;
                userSelect.appendChild(opt);
            });
        }

        currentPage = result.page;
        totalRecords = result.total;

        if (result.data.length === 0) {
            body.innerHTML = '<tr><td colspan="9" class="py-8 text-center text-slate-400">No log entries found.</td></tr>';
        } else {
            body.innerHTML = result.data.map(row => `
                <tr class="border-b border-slate-100 align-top">
                    <td class="py-3 pr-4 whitespace-nowrap text-slate-600">${escapeHtml(formatLogDate(row.LogDate))}</td>
                    <td class="py-3 pr-4">${row.UserName ? escapeHtml(row.UserName) : '<span class="text-slate-400">System / unknown</span>'}</td>
                    <td class="py-3 pr-4">${actionBadge(row.Action)}</td>
                    <td class="py-3 pr-4">${escapeHtml(row.TableAffected)}</td>
                    <td class="py-3 pr-4">#${escapeHtml(row.RecordID)}</td>
                    <td class="py-3 pr-4">${row.FieldChanged ? escapeHtml(row.FieldChanged) : '<span class="text-slate-300">—</span>'}</td>
                    <td class="py-3 pr-4">${valueCell(row.OldValue)}</td>
                    <td class="py-3 pr-4">${valueCell(row.NewValue)}</td>
                    <td class="py-3 text-slate-500">${escapeHtml(row.IPAddress || '')}</td>
                </tr>
            `).join('');
        }

        const from = totalRecords === 0 ? 0 : (currentPage - 1) * PER_PAGE + 1;
        const to = Math.min(currentPage * PER_PAGE, totalRecords);
        document.getElementById('auditSummary').textContent = `Showing ${from}–${to} of ${totalRecords}`;
        document.getElementById('prevPageBtn').disabled = currentPage <= 1;
        document.getElementById('nextPageBtn').disabled = currentPage * PER_PAGE >= totalRecords;
    } catch (error) {
        console.error('Error:', error);
        body.innerHTML = '<tr><td colspan="9" class="py-8 text-center text-red-600">Failed to load audit logs.</td></tr>';
    }
}

document.addEventListener('DOMContentLoaded', () => {
    loadLogs(1, true);

    document.getElementById('applyFiltersBtn').addEventListener('click', () => loadLogs(1));
    document.getElementById('clearFiltersBtn').addEventListener('click', () => {
        ['filterUser', 'filterAction', 'filterTable', 'filterDateFrom', 'filterDateTo', 'filterSearch']
            .forEach(id => { document.getElementById(id).value = ''; });
        loadLogs(1);
    });
    document.getElementById('filterSearch').addEventListener('keydown', (e) => {
        if (e.key === 'Enter') loadLogs(1);
    });
    document.getElementById('prevPageBtn').addEventListener('click', () => loadLogs(currentPage - 1));
    document.getElementById('nextPageBtn').addEventListener('click', () => loadLogs(currentPage + 1));
});