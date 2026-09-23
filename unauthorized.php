<?php
require_once __DIR__ . '/includes/auth.php';
SessionManager::requireLogin();
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="assets/css/output.css">
    <link rel="stylesheet" href="assets/css/global.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
    <title>Unauthorized Access</title>
</head>

<body class="min-h-screen bg-gray-50 flex items-center justify-center p-4">
    <div class="max-w-md w-full text-center">
        <!-- Simple lock icon -->
        <div class="mx-auto w-16 h-16 bg-red-100 rounded-full flex items-center justify-center mb-6">
            <svg class="w-8 h-8 text-red-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                    d="M12 15v2m-4-2V9a4 4 0 118 0v2.1" />
            </svg>
        </div>

        <!-- Title -->
        <h1 class="text-2xl font-semibold text-gray-900 mb-3">
            Unauthorized Access
        </h1>

        <!-- Message -->
        <p class="text-gray-600 mb-8">
            You do not have permission to view this page.
        </p>

        <!-- Login button -->
        <a href="logout.php"
            class="inline-flex items-center px-6 py-3 bg-blue-600 text-white font-medium rounded-lg hover:bg-blue-700 transition-colors">
            <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 16l-4-4m0 0l4-4m-4 4h14" />
            </svg>
            Go to Login
        </a>
    </div>
</body>

</html>