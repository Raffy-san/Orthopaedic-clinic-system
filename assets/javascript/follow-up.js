function escapeHtml(value) {
    const div = document.createElement('div');
    div.textContent = value ?? '';
    return div.innerHTML;
}

function formatAppointmentTime(appointmentTime, meridiem) {
    if (!appointmentTime) {
        return '';
    }

    const [timePart, minutePart] = String(appointmentTime).split(':');
    const hour = Number(timePart);
    const minutes = Number(minutePart ?? '0');

    if (Number.isNaN(hour) || Number.isNaN(minutes)) {
        return appointmentTime;
    }

    const normalized = (meridiem || '').toUpperCase();
    const displayMeridiem = (normalized === 'AM' || normalized === 'PM') ? normalized : (hour >= 12 ? 'PM' : 'AM');
    let displayHour = hour;
    if (displayHour > 12) displayHour -= 12;
    else if (displayHour === 0) displayHour = 12;

    return `${displayHour}:${String(minutes).padStart(2, '0')} ${displayMeridiem}`;
}

async function loadFollowups() {
    const container = document.getElementById('followupsContainer');

    try {
        const response = await fetch('../php/fetch/fetch-followups.php');
        const result = await response.json();

        if (result.status !== 'success') {
            container.innerHTML = '<div class="bg-white rounded-lg p-8 text-center"><p class="text-sm text-red-600">Error loading follow-ups: ' + escapeHtml(result.message) + '</p></div>';
            return;
        }

        const followups = result.data;
        if (followups.length === 0) {
            container.innerHTML = '<div class="bg-white rounded-lg p-8 text-center"><p class="text-sm text-gray-500">No upcoming follow-ups scheduled</p></div>';
            return;
        }

        let html = `
            <p class="text-xs text-slate-500">
                To cancel or reschedule a follow-up, use the Appointments page.
            </p>`;

        followups.forEach(followup => {
            const formattedDate = new Date(followup.FollowUpDate + 'T00:00:00').toLocaleDateString('en-US', {
                month: 'short',
                day: 'numeric',
                year: 'numeric'
            });

            let statusClass = 'text-teal-700 bg-teal-100';
            let statusText = 'Scheduled';
            if (followup.Status === 'Completed') {
                statusClass = 'text-green-700 bg-green-100';
                statusText = 'Completed';
            } else if (followup.Status === 'Cancelled') {
                statusClass = 'text-red-700 bg-red-100';
                statusText = 'Cancelled';
            }

            const formattedTime = formatAppointmentTime(followup.AppointmentTime, followup.AppointmentMeridiem);
            const when = formattedTime ? `${formattedDate} - ${formattedTime}` : `${formattedDate} - No time recorded`;
            const patientName = `${followup.PatientFirstName} ${followup.PatientLastName}`;
            const doctorName = followup.DoctorFirstName
                ? `Dr. ${followup.DoctorFirstName} ${followup.DoctorLastName}`
                : '';

            const contactHtml = followup.Status === 'Scheduled'
                ? (followup.PatientPhone
                    ? `<p class="text-sm text-gray-600"><i class="fa-solid fa-phone mr-2"></i>Contact: <span class="font-semibold select-all">${escapeHtml(followup.PatientPhone)}</span></p>`
                    : '<p class="text-sm text-amber-600">No phone number on file for this patient.</p>')
                : '';

            html += `
                <div class="bg-white rounded-lg p-6 shadow-sm followup-card" data-followup-id="${followup.FollowUpID}">
                    <div class="flex justify-between items-start mb-4">
                        <div>
                            <h3 class="text-lg font-bold text-gray-800">${escapeHtml(patientName)}</h3>
                            <p class="text-sm text-gray-500">${escapeHtml(doctorName)}${doctorName ? ' • ' : ''}${escapeHtml(when)}</p>
                        </div>
                        <span class="inline-block px-3 py-1 text-xs font-semibold ${statusClass} rounded-full">${statusText}</span>
                    </div>
                    ${followup.Remarks ? '<p class="text-sm text-gray-500 mb-4 italic">' + escapeHtml(followup.Remarks) + '</p>' : ''}
                    ${contactHtml}
                </div>
            `;
        });

        container.innerHTML = html;
    } catch (error) {
        console.error('Error:', error);
        container.innerHTML = '<div class="bg-white rounded-lg p-8 text-center"><p class="text-sm text-red-600">Error loading follow-ups</p></div>';
    }
}

document.addEventListener('DOMContentLoaded', loadFollowups);