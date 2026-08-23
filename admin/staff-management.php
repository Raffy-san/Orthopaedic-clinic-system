<?php
require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/../includes/auth.php';
require_once __DIR__ . '/../includes/fetch.php';
SessionManager::requireAdmin();
SessionManager::requireLogin();
SessionManager::requireAnyRole(['admin', 'doctor']);

$admin = SessionManager::getUser($pdo);

if (!$admin) {
    SessionManager::logout('../index.php');
}

$csrfToken = $_SESSION['csrf_token'] ?? SessionManager::regenerateCsrfToken();

$staff = $pdo->query(
    'SELECT UserID, Username, FirstName, LastName, Role, IsDoctor, Email, Phone, Status, CreatedAt
     FROM users ORDER BY CreatedAt DESC'
)->fetchAll(PDO::FETCH_ASSOC);
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
    <title>Staff Accounts</title>
</head>

<body class="h-screen flex bg-slate-200">
    <?php include_once '../includes/sidebar.php'; ?>
    <main class="flex-1 p-6 overflow-auto">
        <h1 class="text-2xl font-bold text-gray-800">Staff Accounts</h1>
        <p class="text-gray-500 mt-1">Create accounts for administrators, doctors, and reception staff.</p>

        <div id="staffMessage" class="mt-4 hidden rounded p-3"></div>

        <section class="bg-white rounded-lg shadow-sm p-6 mt-6 max-w-4xl">
            <h2 class="text-lg font-semibold mb-4">Add staff account</h2>
            <form id="addStaffForm" class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($csrfToken) ?>">
                <input name="username" placeholder="Username" required class="border border-slate-200 rounded p-2">
                <input name="password" type="password" placeholder="Password (8+ characters)" required
                    class="border border-slate-200 rounded p-2">
                <input name="firstName" placeholder="First name" required class="border border-slate-200 rounded p-2">
                <input name="lastName" placeholder="Last name" required class="border border-slate-200 rounded p-2">
                <select name="role" class="border border-slate-200 rounded p-2">
                    <option value="Receptionist">Receptionist</option>
                    <option value="Doctor">Doctor</option>
                    <option value="Admin">Admin</option>
                </select>
                <input name="email" type="email" placeholder="Email (optional)"
                    class="border border-slate-200 rounded p-2">
                <input name="phone" placeholder="Phone (optional)" class="border border-slate-200 rounded p-2">
                <label class="flex items-center gap-2 p-2"><input name="isDoctor" type="checkbox" value="1"> Also a
                    doctor</label>
                <button class="md:col-span-2 bg-blue-600 text-white rounded p-2 hover:bg-blue-700" type="submit">Create
                    account</button>
            </form>
        </section>

        <section class="bg-white rounded-lg shadow-sm mt-6 overflow-x-auto">
            <table class="w-full text-left">
                <thead class="border-b border-slate-200 text-sm text-gray-500">
                    <tr>
                        <th class="p-4">Name</th>
                        <th class="p-4">Username</th>
                        <th class="p-4">Role</th>
                        <th class="p-4">Contact</th>
                        <th class="p-4">Status</th>
                    </tr>
                </thead>
                <tbody><?php foreach ($staff as $account): ?>
                        <tr class="border-b last:border-0 border-slate-200">
                            <td class="p-4 font-medium">
                                <?= htmlspecialchars($account['FirstName'] . ' ' . $account['LastName']) ?>
                            </td>
                            <td class="p-4"><?= htmlspecialchars($account['Username']) ?></td>
                            <td class="p-4">
                                <?= htmlspecialchars($account['Role'] . ($account['IsDoctor'] ? ' / Doctor' : '')) ?>
                            </td>
                            <td class="p-4"><?= htmlspecialchars($account['Email'] ?: $account['Phone'] ?: '-') ?></td>
                            <td class="p-4"><?= htmlspecialchars($account['Status']) ?></td>
                        </tr><?php endforeach; ?>
                </tbody>
            </table>
        </section>
    </main>
    <script>
        let csrfToken = <?= json_encode($csrfToken) ?>;
        const form = document.getElementById('addStaffForm');
        const message = document.getElementById('staffMessage');
        const submitButton = form.querySelector('button[type="submit"]');

        form.addEventListener('submit', async (event) => {
            event.preventDefault();
            submitButton.disabled = true;
            submitButton.textContent = 'Creating...';
            message.className = 'mt-4 rounded p-3 hidden';

            const formData = new FormData(form);
            formData.set('csrf_token', csrfToken);

            try {
                const response = await fetch('../php/add/add-staff.php', {
                    method: 'POST',
                    body: formData,
                    headers: { 'X-Requested-With': 'XMLHttpRequest' }
                });
                const data = await response.json();
                if (data.csrf_token) {
                    csrfToken = data.csrf_token;
                }
                if (data.status !== 'success') {
                    throw new Error(data.message || 'Unable to create the account.');
                }
                message.textContent = data.message;
                message.className = 'mt-4 rounded bg-green-100 p-3 text-green-700';
                form.reset();
            } catch (error) {
                message.textContent = error.message;
                message.className = 'mt-4 rounded bg-red-100 p-3 text-red-700';
            } finally {
                submitButton.disabled = false;
                submitButton.textContent = 'Create account';
            }
        });
    </script>
</body>

</html>