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
    <title>Follow-up Check-ups</title>
</head>

<body class="h-screen flex bg-slate-200">
    <?php include_once '../includes/sidebar.php'; ?>

    <section class="flex-1 p-6 overflow-auto">
        <div class="mb-8 space-y-1">
            <h1 class="text-2xl font-bold text-gray-800">Follow-up Check-ups</h1>
            <p class="text-sm font-medium text-gray-500">Manage and track patient follow-up appointments</p>
        </div>

        <!-- Appointments List -->
        <div id="followupsContainer" class="space-y-4">
            <div class="bg-white rounded-lg p-8 text-center">
                <p class="text-sm text-gray-500">Loading follow-up appointments...</p>
            </div>
        </div>
    </section>

    <script>
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
            alert('Patient notification sent for follow-up ID: ' + followupID + '\n(SMS/Email feature to be implemented)');
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
                        status: newStatus
                    })
                });

                const result = await response.json();

                if (result.status === 'success') {
                    alert('Follow-up status updated to: ' + newStatus);
                    loadFollowups(); // Reload the list
                } else {
                    alert('Error: ' + result.message);
                }
            } catch (error) {
                console.error('Error:', error);
                alert('An error occurred while updating the follow-up.');
            }
        }

        // Load follow-ups when page loads
        document.addEventListener('DOMContentLoaded', loadFollowups);
    </script>

</body>

</html>