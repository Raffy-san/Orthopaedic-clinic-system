<?php
include_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/../includes/auth.php';
require_once __DIR__ . '/../php/fetch/fetch.php';
SessionManager::requireLogin();

SessionManager::requireRole('patient');

$patient = SessionManager::getUser($pdo);

if (!$patient) {
    SessionManager::logout('../index.php'); // Force logout if user not found
}

// Fetch full profile details.
// NOTE: column names below are assumed based on the fields shown in the design.
// Adjust to match your actual `patients` table schema.
$profileStatement = $pdo->prepare(
    "SELECT u.FirstName,
            u.LastName,
            u.Email,
            p.PatientCode,
            p.PatientType,
            p.BirthDate,
            p.Gender,
            p.Phone,
            p.Allergies,
            p.Address
     FROM patients p
     LEFT JOIN users u ON u.UserID = p.UserID
     WHERE p.UserID = ?
     LIMIT 1"
);
$profileStatement->execute([$patient['user_id']]);
$profile = $profileStatement->fetch(PDO::FETCH_ASSOC) ?: [];

$fullName = trim(($profile['FirstName'] ?? '') . ' ' . ($profile['LastName'] ?? ''));
$initials = '';
if (!empty($profile['FirstName'])) {
    $initials .= strtoupper($profile['FirstName'][0]);
}
if (!empty($profile['LastName'])) {
    $initials .= strtolower($profile['LastName'][0]);
}

function formatDob($dob)
{
    if (empty($dob)) {
        return 'N/A';
    }
    $ts = strtotime($dob);
    return $ts ? date('F j, Y', $ts) : htmlspecialchars($dob);
}
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
    <title>Profile</title>
</head>

<body class="h-screen flex bg-slate-200 overflow-hidden">
    <?php include '../includes/user-sidebar.php'; ?>

    <section class="flex-1 min-h-0 flex flex-col overflow-hidden">
        <div
            class="flex items-center justify-between bg-gradient-to-r from-[#0b1f0b] via-[#1e6b34] to-[#2e8b47] px-6 py-4">
            <h1 class="text-2xl font-bold text-white">Southern Leyte Orthopaedic Clinic System</h1>
            <div class="flex flex-col gap-3 items-end">
                <h1 class="text-2xl font-bold text-white">My Profile</h1>
                <h3 class="text-sm font-medium text-white">Personal information on file</h3>
            </div>
        </div>

        <div class="flex-1 min-h-0 overflow-auto p-6 space-y-4">

            <div class="grid grid-cols-1 md:grid-cols-12 gap-6">

                <!-- Left: avatar card -->
                <div class="md:col-span-4">
                    <div
                        class="bg-white rounded-3xl shadow-sm border border-slate-100 p-6 flex flex-col items-center text-center">
                        <div
                            class="w-20 h-20 rounded-full bg-teal-600 text-white flex items-center justify-center text-2xl font-bold mb-4">
                            <?php echo htmlspecialchars($initials ?: 'PT'); ?>
                        </div>
                        <h2 class="text-lg font-bold text-slate-900">
                            <?php echo htmlspecialchars($fullName ?: 'N/A'); ?>
                        </h2>
                        <p class="text-sm text-slate-400 mt-1">
                            <?php echo htmlspecialchars($profile['PatientCode'] ?? 'N/A'); ?>
                        </p>
                        <span class="mt-3 text-xs font-semibold px-4 py-1.5 rounded-full bg-blue-100 text-blue-700">
                            <?php echo htmlspecialchars($profile['PatientType'] ?? 'Regular'); ?>
                        </span>
                    </div>
                </div>

                <!-- Right: personal information -->
                <div class="md:col-span-8">
                    <div class="bg-white rounded-3xl shadow-sm border border-slate-100 p-6">
                        <h3 class="text-lg font-bold text-slate-900 mb-4">Personal Information</h3>

                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                            <div class="bg-slate-50 rounded-xl px-4 py-3">
                                <p class="text-xs font-medium text-slate-400 mb-1">Full Name</p>
                                <p class="text-sm font-semibold text-slate-800">
                                    <?php echo htmlspecialchars($fullName ?: 'N/A'); ?>
                                </p>
                            </div>

                            <div class="bg-slate-50 rounded-xl px-4 py-3">
                                <p class="text-xs font-medium text-slate-400 mb-1">Date of Birth</p>
                                <p class="text-sm font-semibold text-slate-800">
                                    <?php echo formatDob($profile['BirthDate'] ?? null); ?>
                                </p>
                            </div>

                            <div class="bg-slate-50 rounded-xl px-4 py-3">
                                <p class="text-xs font-medium text-slate-400 mb-1">Gender</p>
                                <p class="text-sm font-semibold text-slate-800">
                                    <?php echo htmlspecialchars($profile['Gender'] ?? 'N/A'); ?>
                                </p>
                            </div>

                            <div class="bg-slate-50 rounded-xl px-4 py-3">
                                <p class="text-xs font-medium text-slate-400 mb-1">Phone</p>
                                <p class="text-sm font-semibold text-slate-800">
                                    <?php echo htmlspecialchars($profile['Phone'] ?? 'N/A'); ?>
                                </p>
                            </div>

                            <div class="bg-slate-50 rounded-xl px-4 py-3">
                                <p class="text-xs font-medium text-slate-400 mb-1">Allergies</p>
                                <p class="text-sm font-semibold text-slate-800">
                                    <?php echo htmlspecialchars($profile['Allergies'] ?? 'N/A'); ?>
                                </p>
                            </div>

                            <div class="bg-slate-50 rounded-xl px-4 py-3">
                                <p class="text-xs font-medium text-slate-400 mb-1">Address</p>
                                <p class="text-sm font-semibold text-slate-800">
                                    <?php echo htmlspecialchars($profile['Address'] ?? 'N/A'); ?>
                                </p>
                            </div>
                        </div>
                    </div>
                </div>

            </div>
        </div>
    </section>
</body>

</html>