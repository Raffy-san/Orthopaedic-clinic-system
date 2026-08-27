<?php
require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/../includes/auth.php';

SessionManager::requireLogin();
SessionManager::requireAnyRole(['staff', 'receptionist']);

$staff = SessionManager::getUser($pdo);
if (!$staff) {
    SessionManager::logout('../index.php');
}

$displayName = trim(($staff['first_name'] ?? '') . ' ' . ($staff['last_name'] ?? ''));
$displayName = $displayName !== '' ? $displayName : ($staff['username'] ?? 'Staff');
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
    <title>Dashboard</title>
</head>

<body class="h-screen bg-slate-200">
    <main class="min-h-screen p-6">
        <div class="max-w-5xl mx-auto">
            <div class="flex items-center justify-between mb-6">
                <div>
                    <h1 class="text-2xl font-bold text-gray-800">Staff Dashboard</h1>
                    <p class="text-gray-500 mt-1">Welcome, <?= htmlspecialchars($displayName) ?></p>
                </div>
                <a href="../logout.php" class="text-sm font-medium text-gray-500 hover:text-red-700">
                    <i class="fa-solid fa-arrow-right-from-bracket mr-1"></i> Sign out
                </a>
            </div>
            <section class="bg-white rounded-xl shadow-md p-6">
                <h2 class="text-lg font-semibold text-gray-800">Staff access</h2>
                <p class="text-gray-500 mt-2">You are signed in with limited staff access.</p>
            </section>
        </div>
    </main>
</body>

</html>