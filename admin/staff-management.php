<?php
require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/../includes/auth.php';
require_once __DIR__ . '/../php/fetch/fetch.php';
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
$activeAccounts = count(array_filter($staff, static fn(array $account): bool => $account['Status'] === 'Active'));
$doctorAccounts = count(array_filter($staff, static fn(array $account): bool => $account['Role'] === 'Doctor' || (int) $account['IsDoctor'] === 1));
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

<body class="h-screen flex bg-slate-100">
    <?php include_once '../includes/sidebar.php'; ?>
    <main class="flex-1 p-6 overflow-auto">
        <div class="max-w-7xl mx-auto">
            <div class="flex flex-col gap-4 md:flex-row md:items-end md:justify-between">
                <div>
                    <p class="text-sm font-semibold uppercase tracking-wide text-blue-800">Team directory</p>
                    <h1 class="text-3xl font-bold text-slate-800 mt-1">Staff Management</h1>
                    <p class="text-slate-500 mt-2">Create and review accounts for the clinic team.</p>
                </div>
                <div class="flex items-center gap-2 text-sm text-slate-500">
                    <i class="fa-solid fa-shield-halved text-blue-600"></i>
                    <span>Admin access</span>
                </div>
            </div>

            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4 mt-6 lg:grid-cols-3">
                <div class="bg-white rounded-xl border border-slate-200 p-5 shadow-sm">
                    <div class="flex items-center justify-between">
                        <p class="text-sm font-medium text-slate-500">Total accounts</p>
                        <i class="fa-solid fa-users text-blue-600"></i>
                    </div>
                    <p class="text-3xl font-bold text-slate-800 mt-3"><?= count($staff) ?></p>
                </div>
                <div class="bg-white rounded-xl border border-slate-200 p-5 shadow-sm">
                    <div class="flex items-center justify-between">
                        <p class="text-sm font-medium text-slate-500">Active accounts</p>
                        <i class="fa-solid fa-circle-check text-green-600"></i>
                    </div>
                    <p class="text-3xl font-bold text-slate-800 mt-3"><?= $activeAccounts ?></p>
                </div>
                <div class="bg-white rounded-xl border border-slate-200 p-5 shadow-sm">
                    <div class="flex items-center justify-between">
                        <p class="text-sm font-medium text-slate-500">Doctor access</p>
                        <i class="fa-solid fa-user-doctor text-blue-600"></i>
                    </div>
                    <p class="text-3xl font-bold text-slate-800 mt-3"><?= $doctorAccounts ?></p>
                </div>
            </div>

            <div id="staffMessage" class="mt-6 hidden rounded-lg p-3"></div>

            <section class="bg-white rounded-xl border border-slate-200 shadow-sm p-6 mt-6">
                <div class="flex items-start gap-3 mb-5">
                    <div class="w-10 h-10 rounded-lg bg-blue-50 flex items-center justify-center shrink-0">
                        <i class="fa-solid fa-user-plus text-blue-600"></i>
                    </div>
                    <div>
                        <h2 class="text-lg font-semibold text-slate-800">Add staff account</h2>
                        <p class="text-sm text-slate-500 mt-1">Set up login details and access level for a team member.</p>
                    </div>
                </div>
                <form id="addStaffForm" class="grid grid-cols-1 md:grid-cols-2 xl:grid-cols-4 gap-4">
                <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($csrfToken) ?>">
                <label class="text-sm font-medium text-slate-700">Username<input name="username" placeholder="e.g. maria.staff" required class="mt-2 w-full border border-slate-200 rounded-lg p-2.5 focus:border-blue-500 focus:outline-none"></label>
                <label class="text-sm font-medium text-slate-700">Password<input name="password" type="password" placeholder="8+ characters" required class="mt-2 w-full border border-slate-200 rounded-lg p-2.5 focus:border-blue-500 focus:outline-none"></label>
                <label class="text-sm font-medium text-slate-700">First name<input name="firstName" placeholder="First name" required class="mt-2 w-full border border-slate-200 rounded-lg p-2.5 focus:border-blue-500 focus:outline-none"></label>
                <label class="text-sm font-medium text-slate-700">Last name<input name="lastName" placeholder="Last name" required class="mt-2 w-full border border-slate-200 rounded-lg p-2.5 focus:border-blue-500 focus:outline-none"></label>
                <label class="text-sm font-medium text-slate-700">Role<select name="role" class="mt-2 w-full border border-slate-200 rounded-lg p-2.5 bg-white focus:border-blue-500 focus:outline-none">
                    <option value="Staff">Staff</option>
                    <option value="Doctor">Doctor</option>
                    <option value="Admin">Admin</option>
                </select></label>
                <label class="text-sm font-medium text-slate-700">Email<input name="email" type="email" placeholder="Optional email" class="mt-2 w-full border border-slate-200 rounded-lg p-2.5 focus:border-blue-500 focus:outline-none"></label>
                <label class="text-sm font-medium text-slate-700">Phone<input name="phone" placeholder="Optional phone" class="mt-2 w-full border border-slate-200 rounded-lg p-2.5 focus:border-blue-500 focus:outline-none"></label>
                <label class="flex items-center gap-3 self-end min-h-11 px-3 rounded-lg bg-slate-50 border border-slate-200 text-sm text-slate-700"><input name="isDoctor" type="checkbox" value="1" class="h-4 w-4 accent-blue-600"> Also a doctor</label>
                <button class="md:col-span-2 xl:col-span-4 bg-blue-800 text-white rounded-lg p-2.5 font-semibold hover:bg-blue-900 transition" type="submit"><i class="fa-solid fa-plus mr-2"></i>Create account</button>
                </form>
            </section>

            <section class="bg-white rounded-xl border border-slate-200 shadow-sm mt-6 overflow-hidden">
                <div class="px-6 py-5 border-b border-slate-200">
                    <h2 class="text-lg font-semibold text-slate-800">Account directory</h2>
                    <p class="text-sm text-slate-500 mt-1">Review roles, contact details, and account status.</p>
                </div>
                <div class="overflow-x-auto">
                    <table class="w-full text-left">
                <thead class="border-b border-slate-200 bg-slate-50 text-xs uppercase tracking-wide text-slate-500">
                    <tr>
                        <th class="px-6 py-3">Name</th>
                        <th class="px-6 py-3">Username</th>
                        <th class="px-6 py-3">Role</th>
                        <th class="px-6 py-3">Contact</th>
                        <th class="px-6 py-3">Status</th>
                    </tr>
                </thead>
                <tbody><?php foreach ($staff as $account): ?>
                        <tr class="border-b last:border-0 border-slate-100 hover:bg-slate-50 transition">
                            <td class="px-6 py-4 font-medium text-slate-800">
                                <?= htmlspecialchars($account['FirstName'] . ' ' . $account['LastName']) ?>
                            </td>
                            <td class="px-6 py-4 text-sm text-slate-600"><?= htmlspecialchars($account['Username']) ?></td>
                            <td class="px-6 py-4 text-sm text-slate-600">
                                <?= htmlspecialchars($account['Role'] . ($account['IsDoctor'] ? ' / Doctor' : '')) ?>
                            </td>
                            <td class="px-6 py-4 text-sm text-slate-600"><?= htmlspecialchars($account['Email'] ?: $account['Phone'] ?: '-') ?></td>
                            <td class="px-6 py-4"><span class="inline-flex rounded-full px-2.5 py-1 text-xs font-semibold <?= $account['Status'] === 'Active' ? 'bg-green-100 text-green-700' : 'bg-slate-100 text-slate-600' ?>"><?= htmlspecialchars($account['Status']) ?></span></td>
                        </tr><?php endforeach; ?>
                </tbody>
            </table>
                </div>
            </section>
        </div>
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