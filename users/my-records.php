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
    <title>My Records</title>
</head>

<body class="h-screen flex bg-slate-200">
    <?php include '../includes/user-sidebar.php'; ?>
    
     <section class="flex-1 p-6 overflow-auto">
        <div class="max-w-5xl mx-auto">
            <div class="mb-8">
                <h1 class="text-2xl font-bold">My Appointments</h1>
                <h3 class="text-md font-medium text-gray-500">
                    Scheduled and past clinic visits
                </h3>
            </div>
</body>

</html>