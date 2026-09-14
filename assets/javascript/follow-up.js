let csrfToken = window.csrfToken || "";

function updateBodyScroll() {
    const anyModalOpen = document.querySelectorAll('.modal:not(.hidden)').length > 0;
    document.body.style.overflow = anyModalOpen ? 'hidden' : 'auto';
}

const openModal = (modal) => {
    modal.classList.remove("hidden");
    modal.classList.add("flex");
    updateBodyScroll();
};

const closeModal = (modal) => {
    modal.classList.add("hidden");
    updateBodyScroll();
};

function showMessage(title, message, type = "success", callback = null) {
    const modal = document.getElementById("messageModal");
    const titleElement = document.getElementById("messageTitle");
    const textElement = document.getElementById("messageText");

    titleElement.textContent = title;
    textElement.textContent = message;

    titleElement.classList.toggle("text-green-600", type === "success");
    titleElement.classList.toggle("text-red-600", type !== "success");

    openModal(modal);

    document.getElementById("closeMessageBtn").onclick = () => {
        closeModal(modal);
        if (callback) callback();
    };
}

// Load follow-ups on page load
async function loadFollowups() {
    try {
        const response = await fetch('../php/fetch/fetch-followups.php');
        const result = await response.json();

        if (result.status !== 'success') {
            document.getElementById('followupsContainer').innerHTML = '<div class="bg-white rounded-lg p-8 text-center"><p class="text-sm text-red-600">Error loading follow-ups: ' + result.message + '</p></div>';
            return;
        }

        const followups = result.data;
        if (followups.length === 0) {
            document.getElementById('followupsContainer').innerHTML = '<div class="bg-white rounded-lg p-8 text-center"><p class="text-sm text-gray-500">No upcoming follow-ups scheduled</p></div>';
            return;
        }

        let html = '';
        followups.forEach(followup => {
            const date = new Date(followup.FollowUpDate);
            const formattedDate = date.toLocaleDateString('en-US', {
                month: 'short',
                day: 'numeric',
                year: 'numeric'
            });

            // Status badge
            let statusClass = 'text-teal-700 bg-teal-100';
            let statusText = 'Scheduled';
            if (followup.Status === 'Completed') {
                statusClass = 'text-green-700 bg-green-100';
                statusText = 'Completed';
            } else if (followup.Status === 'Cancelled') {
                statusClass = 'text-red-700 bg-red-100';
                statusText = 'Cancelled';
            }

            html += `
                        <div class="bg-white rounded-lg p-6 shadow-sm hover:shadow-md transition-shadow followup-card" data-followup-id="${followup.FollowUpID}">
                            <div class="flex justify-between items-start mb-4">
                                <div>
                                    <h3 class="text-lg font-bold text-gray-800">${followup.PatientFirstName} ${followup.PatientLastName}</h3>
                                    <p class="text-sm text-gray-500">Dr. ${followup.DoctorFirstName} ${followup.DoctorLastName} • ${formattedDate}</p>
                                </div>
                                <span class="inline-block px-3 py-1 text-xs font-semibold ${statusClass} rounded-full">${statusText}</span>
                            </div>
                            <p class="text-sm text-gray-600 mb-4">${followup.reason || 'Follow-up visit'}</p>
                            ${followup.Remarks ? '<p class="text-sm text-gray-500 mb-4 italic">' + followup.Remarks + '</p>' : ''}
                            <div class="flex gap-3">
                                <button class="px-4 py-2 text-sm font-medium text-purple-600 border border-purple-300 rounded-lg hover:bg-purple-50 transition notify-btn" data-followup-id="${followup.FollowUpID}">
                                    <i class="fa-solid fa-bell mr-2"></i>Notify Patient
                                </button>
                                <button class="px-4 py-2 text-sm font-medium text-white bg-blue-600 rounded-lg hover:bg-blue-700 transition confirm-btn" data-followup-id="${followup.FollowUpID}" data-status="${followup.Status}">
                                    Confirm & Update DB
                                </button>
                            </div>
                        </div>
                    `;
        });

        document.getElementById('followupsContainer').innerHTML = html;

        // Add event listeners
        document.querySelectorAll('.notify-btn').forEach(btn => {
            btn.addEventListener('click', notifyPatient);
        });

        document.querySelectorAll('.confirm-btn').forEach(btn => {
            btn.addEventListener('click', confirmFollowup);
        });

    } catch (error) {
        console.error('Error:', error);
        document.getElementById('followupsContainer').innerHTML = '<div class="bg-white rounded-lg p-8 text-center"><p class="text-sm text-red-600">Error loading follow-ups</p></div>';
    }
}

// Notify patient function
function notifyPatient(e) {
    const followupID = e.currentTarget.getAttribute('data-followup-id');
    showMessage('Notification Sent', 'Patient notification sent for follow-up ID: ' + followupID + '\n(SMS/Email feature to be implemented)', 'success');
}

// Confirm follow-up function
async function confirmFollowup(e) {
    const followupID = e.currentTarget.getAttribute('data-followup-id');
    const currentStatus = e.currentTarget.getAttribute('data-status');

    const newStatus = currentStatus === 'Scheduled' ? 'Completed' : currentStatus;

    try {
        const response = await fetch('../php/update/update-followup-status.php', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
            },
            body: JSON.stringify({
                followup_id: followupID,
                status: newStatus,
                csrf_token: csrfToken
            })
        });

        const result = await response.json();

        if (result.csrf_token) {
            csrfToken = result.csrf_token;
        }

        if (result.status === 'success') {
            showMessage('Status Updated', 'Follow-up status updated to: ' + newStatus, 'success', () => {
                loadFollowups(); // Reload the list
            });
        } else {
            showMessage('Error', 'Error: ' + result.message, 'error');
        }
    } catch (error) {
        console.error('Error:', error);
        showMessage('Error', 'An error occurred while updating the follow-up.', 'error');
    }
}

// Load follow-ups when page loads
document.addEventListener('DOMContentLoaded', loadFollowups);