<?php
require_once '../config/config.php';
require_once '../includes/auth.php';
SessionManager::requireAdmin();

$message = '';
$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = trim($_POST['username'] ?? '');
    $password = $_POST['password'] ?? '';
    $firstName = trim($_POST['firstName'] ?? '');
    $lastName = trim($_POST['lastName'] ?? '');
    $role = $_POST['role'] ?? 'Receptionist';
    $isDoctor = isset($_POST['isDoctor']) ? 1 : 0;
    $email = trim($_POST['email'] ?? '') ?: null;
    $phone = trim($_POST['phone'] ?? '') ?: null;

    if ($username === '' || $password === '' || $firstName === '' || $lastName === '') {
        $error = 'Username, password, first name, and last name are required.';
    } elseif (!in_array($role, ['Admin', 'Doctor', 'Receptionist'], true)) {
        $error = 'Please select a valid role.';
    } elseif (strlen($password) < 8) {
        $error = 'Password must contain at least 8 characters.';
    } else {
        try {
            $statement = $pdo->prepare(
                'INSERT INTO users (Username, PasswordHash, FirstName, LastName, Role, IsDoctor, Email, Phone)
                 VALUES (?, ?, ?, ?, ?, ?, ?, ?)'
            );
            $statement->execute([
                $username,
                password_hash($password, PASSWORD_DEFAULT),
                $firstName,
                $lastName,
                $role,
                $isDoctor,
                $email,
                $phone
            ]);
            $message = 'Staff account created successfully.';
        } catch (PDOException $exception) {
            $error = $exception->getCode() === '23000'
                ? 'That username or email is already in use.'
                : 'Unable to create the account.';
        }
    }
}

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
    <title>Staff Accounts</title>
</head>
<body class="h-screen flex bg-slate-200">
    <?php include_once '../includes/sidebar.php'; ?>
    <main class="flex-1 p-6 overflow-auto">
        <h1 class="text-2xl font-bold text-gray-800">Staff Accounts</h1>
        <p class="text-gray-500 mt-1">Create accounts for administrators, doctors, and reception staff.</p>

        <?php if ($message): ?><div class="mt-4 p-3 rounded bg-green-100 text-green-700"><?= htmlspecialchars($message) ?></div><?php endif; ?>
        <?php if ($error): ?><div class="mt-4 p-3 rounded bg-red-100 text-red-700"><?= htmlspecialchars($error) ?></div><?php endif; ?>

        <section class="bg-white rounded-lg shadow-sm p-6 mt-6 max-w-4xl">
            <h2 class="text-lg font-semibold mb-4">Add staff account</h2>
            <form method="post" class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <input name="username" placeholder="Username" required class="border rounded p-2">
                <input name="password" type="password" placeholder="Password (8+ characters)" required class="border rounded p-2">
                <input name="firstName" placeholder="First name" required class="border rounded p-2">
                <input name="lastName" placeholder="Last name" required class="border rounded p-2">
                <select name="role" class="border rounded p-2">
                    <option value="Receptionist">Receptionist</option>
                    <option value="Doctor">Doctor</option>
                    <option value="Admin">Admin</option>
                </select>
                <input name="email" type="email" placeholder="Email (optional)" class="border rounded p-2">
                <input name="phone" placeholder="Phone (optional)" class="border rounded p-2">
                <label class="flex items-center gap-2 p-2"><input name="isDoctor" type="checkbox" value="1"> Also a doctor</label>
                <button class="md:col-span-2 bg-blue-600 text-white rounded p-2 hover:bg-blue-700" type="submit">Create account</button>
            </form>
        </section>

        <section class="bg-white rounded-lg shadow-sm mt-6 overflow-x-auto">
            <table class="w-full text-left">
                <thead class="border-b text-sm text-gray-500"><tr><th class="p-4">Name</th><th class="p-4">Username</th><th class="p-4">Role</th><th class="p-4">Contact</th><th class="p-4">Status</th></tr></thead>
                <tbody><?php foreach ($staff as $account): ?><tr class="border-b last:border-0"><td class="p-4 font-medium"><?= htmlspecialchars($account['FirstName'] . ' ' . $account['LastName']) ?></td><td class="p-4"><?= htmlspecialchars($account['Username']) ?></td><td class="p-4"><?= htmlspecialchars($account['Role'] . ($account['IsDoctor'] ? ' / Doctor' : '')) ?></td><td class="p-4"><?= htmlspecialchars($account['Email'] ?: $account['Phone'] ?: '-') ?></td><td class="p-4"><?= htmlspecialchars($account['Status']) ?></td></tr><?php endforeach; ?></tbody>
            </table>
        </section>
    </main>
</body>
</html>
