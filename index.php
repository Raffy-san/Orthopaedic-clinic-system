<?php
require_once __DIR__ . '/config/config.php';
require_once __DIR__ . '/includes/auth.php';

if (SessionManager::isLoggedIn()) {
    $role = $_SESSION['access_type'] ?? $_SESSION['user']['Role'] ?? null;

    if (in_array($role, ['Admin', 'Doctor'], true)) {
        header('Location: admin/admin-dashboard.php');
        exit;
    } elseif (in_array(strtolower((string) $role), ['staff', 'receptionist'], true)) {
        header('Location: staff/staff-dashboard.php');
        exit;
    } else {
        header('Location: users/user-dashboard.php');
        exit;
    }
}

$loginError = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = trim($_POST['username'] ?? '');
    $password = $_POST['password'] ?? '';
    $loginType = $_POST['loginType'] ?? 'staff';

    $statement = $pdo->prepare(
        "SELECT UserID, Username, PasswordHash, FirstName, LastName, Role, IsDoctor, Status
         FROM users WHERE Username = ? LIMIT 1"
    );
    $statement->execute([$username]);
    $user = $statement->fetch(PDO::FETCH_ASSOC);
    $userRole = $user ? strtolower(trim((string) $user['Role'])) : '';
    $isDoctor = $user && (int) $user['IsDoctor'] === 1;
    $isActive = $user && ($user['Status'] ?? '') === 'Active';
    $validRole = $loginType === 'admin'
        ? $userRole === 'admin' || $userRole === 'doctor' || $isDoctor
        : ($loginType === 'patient'
            ? $userRole === 'patient'
            : in_array($userRole, ['doctor', 'staff', 'receptionist'], true) || $isDoctor);

    if (!$user) {
        $loginError = 'Username not found. Check the username and try again.';
    } elseif (!$isActive) {
        $loginError = 'This account is inactive. Contact an administrator.';
    } elseif (!$validRole) {
        $loginError = $loginType === 'admin'
            ? 'This account is not an administrator or doctor account.'
            : ($loginType === 'patient'
                ? 'This is not a patient account. Choose the correct login type.'
                : 'This account is not registered as staff or doctor.');
    } elseif (!password_verify($password, $user['PasswordHash'])) {
        $loginError = 'Incorrect password. Check your password and try again.';
    } else {
        unset($user['PasswordHash']);
        session_regenerate_id(true);
        $_SESSION['user'] = $user;
        $_SESSION['user_id'] = $user['UserID'];
        $_SESSION['access_type'] = $user['Role'];
        $redirect = $userRole === 'patient'
            ? 'users/user-dashboard.php'
            : (in_array($userRole, ['staff', 'receptionist'], true)
                ? 'staff/staff-dashboard.php'
                : 'admin/admin-dashboard.php');
        header('Location: ' . $redirect);
        exit;
    }
}
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="./assets/css/output.css">
    <link rel="stylesheet" href="./assets/css/global.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
    <link rel="icon" href="./assets/img/rounded-logo.ico" type="image/x-icon">
    <title>Login Page</title>
</head>

<body class="w-full min-h-screen bg-gray-50">
    <main>
        <!-- Role Selection Screen -->
        <section id="roleSelection" class="flex h-screen">
            <!-- Left Section -->
            <div
                class="hidden lg:flex lg:w-2/5 bg-gradient-to-br from-blue-950 to-blue-900 flex-col justify-center px-12 py-8">
                <div class="max-w-md">
                    <div class="flex gap-6 items-center mb-4">
                        <img src="assets/img/logo.jpg" alt="" class="w-18 h-18 rounded-full">
                        <h1 class="text-white text-3xl font-bold leading-tight">Southern
                            Leyte<br>Orthopaedic Clinic</h1>
                    </div>
                    <p class="text-blue-100 text-base leading-relaxed mb-8">
                        Integrated clinic management — patients can book appointments online while staff manage
                        consultations, billing, and reports.
                    </p>
                    <div class="space-y-4">
                        <div class="flex items-start gap-3">
                            <span class="w-1.5 h-1.5 bg-teal-400 rounded-full mt-2 flex-shrink-0"></span>
                            <span class="text-blue-100 text-sm">Online Appointment Booking</span>
                        </div>
                        <div class="flex items-start gap-3">
                            <span class="w-1.5 h-1.5 bg-teal-400 rounded-full mt-2 flex-shrink-0"></span>
                            <span class="text-blue-100 text-sm">Patient Self-Service Portal</span>
                        </div>
                        <div class="flex items-start gap-3">
                            <span class="w-1.5 h-1.5 bg-teal-400 rounded-full mt-2 flex-shrink-0"></span>
                            <span class="text-blue-100 text-sm">Consultation & Billing</span>
                        </div>
                        <div class="flex items-start gap-3">
                            <span class="w-1.5 h-1.5 bg-teal-400 rounded-full mt-2 flex-shrink-0"></span>
                            <span class="text-blue-100 text-sm">Report Generation</span>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Right Section -->
            <div class="w-full lg:w-3/5 flex flex-col justify-center items-center px-6 py-8">
                <div class="w-full max-w-md">
                    <!-- Header -->
                    <div class="mb-6 text-center">
                        <h2 class="text-gray-800 font-semibold text-lg">SLOC System</h2>
                        <p class="text-gray-500 text-sm">Orthopaedic Clinic</p>
                    </div>

                    <!-- Role Selection -->
                    <div class="space-y-4">
                        <div class="text-center mb-8">
                            <h1 class="text-2xl font-bold text-gray-800">How would you like to login?</h1>
                            <p class="text-gray-500 text-sm mt-2">Select your role to continue</p>
                        </div>

                        <!-- Admin Button -->
                        <button onclick="selectRole('admin')"
                            class="w-full p-4 border-2 border-gray-300 rounded-lg hover:border-blue-500 hover:bg-blue-50 transition group">
                            <div class="flex items-center gap-4">
                                <div
                                    class="w-12 h-12 bg-blue-100 rounded-lg flex items-center justify-center group-hover:bg-blue-200 transition">
                                    <i class="fa-solid fa-user-tie text-blue-600 text-xl"></i>
                                </div>
                                <div class="text-left">
                                    <h3 class="font-bold text-gray-800 text-lg">Admin</h3>
                                    <p class="text-gray-500 text-sm">Manage clinic operations and patient records</p>
                                </div>
                                <i
                                    class="fa-solid fa-arrow-right text-blue-600 ml-auto opacity-0 group-hover:opacity-100 transition"></i>
                            </div>
                        </button>

                        <!-- Staff Button -->
                        <button onclick="selectRole('staff')"
                            class="w-full p-4 border-2 border-gray-300 rounded-lg hover:border-violet-500 hover:bg-violet-50 transition group">
                            <div class="flex items-center gap-4">
                                <div
                                    class="w-12 h-12 bg-blue-100 rounded-lg flex items-center justify-center group-hover:bg-violet-200 transition">
                                    <i class="fa-solid fa-user-tie text-violet-600 text-xl"></i>
                                </div>
                                <div class="text-left">
                                    <h3 class="font-bold text-gray-800 text-lg">Staff</h3>
                                    <p class="text-gray-500 text-sm">Access staff-only features and manage patient
                                        appointments</p>
                                </div>
                                <i
                                    class="fa-solid fa-arrow-right text-violet-600 ml-auto opacity-0 group-hover:opacity-100 transition"></i>
                            </div>
                        </button>

                        <!-- Patient Button -->
                        <button onclick="selectRole('patient')"
                            class="w-full p-4 border-2 border-gray-300 rounded-lg hover:border-teal-500 hover:bg-teal-50 transition group">
                            <div class="flex items-center gap-4">
                                <div
                                    class="w-12 h-12 bg-teal-100 rounded-lg flex items-center justify-center group-hover:bg-teal-200 transition">
                                    <i class="fa-solid fa-user text-teal-600 text-xl"></i>
                                </div>
                                <div class="text-left">
                                    <h3 class="font-bold text-gray-800 text-lg">Patient</h3>
                                    <p class="text-gray-500 text-sm">Book appointments and manage your health records
                                    </p>
                                </div>
                                <i
                                    class="fa-solid fa-arrow-right text-teal-600 ml-auto opacity-0 group-hover:opacity-100 transition"></i>
                            </div>
                        </button>
                    </div>
                </div>
            </div>
        </section>

        <!-- Admin Login Screen -->
        <section id="adminScreen" class="flex h-screen hidden">
            <!-- Left Section -->
            <div
                class="hidden lg:flex lg:w-2/5 bg-gradient-to-br from-blue-950 to-blue-900 flex-col justify-center px-12 py-8">
                <div class="max-w-md">
                    <div class="flex gap-6 items-center mb-4">
                        <img src="assets/img/logo.jpg" alt="" class="w-18 h-18 rounded-full">
                        <h1 class="text-white text-3xl font-bold leading-tight">Southern
                            Leyte<br>Orthopaedic Clinic</h1>
                    </div>
                    <p class="text-blue-100 text-base leading-relaxed mb-8">
                        Integrated clinic management — patients can book appointments online while staff manage
                        consultations, billing, and reports.
                    </p>
                    <div class="space-y-4">
                        <div class="flex items-start gap-3">
                            <span class="w-1.5 h-1.5 bg-teal-400 rounded-full mt-2 flex-shrink-0"></span>
                            <span class="text-blue-100 text-sm">Online Appointment Booking</span>
                        </div>
                        <div class="flex items-start gap-3">
                            <span class="w-1.5 h-1.5 bg-teal-400 rounded-full mt-2 flex-shrink-0"></span>
                            <span class="text-blue-100 text-sm">Patient Self-Service Portal</span>
                        </div>
                        <div class="flex items-start gap-3">
                            <span class="w-1.5 h-1.5 bg-teal-400 rounded-full mt-2 flex-shrink-0"></span>
                            <span class="text-blue-100 text-sm">Consultation & Billing</span>
                        </div>
                        <div class="flex items-start gap-3">
                            <span class="w-1.5 h-1.5 bg-teal-400 rounded-full mt-2 flex-shrink-0"></span>
                            <span class="text-blue-100 text-sm">Report Generation</span>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Right Section -->
            <div class="w-full lg:w-3/5 flex flex-col justify-center items-center px-6 py-8">
                <div class="w-full max-w-md">
                    <!-- Back Button -->
                    <button onclick="backToRoleSelection()"
                        class="mb-6 text-gray-600 hover:text-gray-800 flex items-center gap-2 text-sm">
                        <i class="fa-solid fa-arrow-left"></i>Back
                    </button>

                    <!-- Header -->
                    <div class="mb-8">
                        <h1 class="text-2xl font-bold text-gray-800">Welcome Back</h1>
                        <p class="text-gray-500 text-sm mt-2">Sign in to your administrator account</p>
                    </div>

                    <?php if ($loginError && ($_POST['loginType'] ?? '') === 'admin'): ?>
                        <p class="mb-4 p-3 rounded bg-red-100 text-red-700"><?= htmlspecialchars($loginError) ?></p>
                    <?php endif; ?>
                    <!-- Admin Login Form -->
                    <form class="space-y-5" method="post">
                        <input type="hidden" name="loginType" value="admin">
                        <div>
                            <label class="block text-gray-700 text-sm font-semibold mb-2"
                                for="username">Username</label>
                            <div class="relative">
                                <i class="fa-solid fa-user absolute left-3 top-3.5 text-gray-400 text-sm"></i>
                                <input
                                    class="bg-white rounded-lg pl-10 pr-4 py-2.5 w-full border border-gray-300 hover:border-gray-400 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent transition"
                                    type="text" name="username" id="adminUsername" placeholder="Enter your username"
                                    required>
                            </div>
                        </div>

                        <div>
                            <label class="block text-gray-700 text-sm font-semibold mb-2"
                                for="adminPassword">Password</label>
                            <div class="relative">
                                <i class="fa-solid fa-lock absolute left-3 top-3.5 text-gray-400 text-sm"></i>
                                <input
                                    class="bg-white rounded-lg pl-10 pr-10 py-2.5 w-full border border-gray-300 hover:border-gray-400 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent transition"
                                    type="password" name="password" id="adminPassword" placeholder="Enter your password"
                                    required>
                                <i id="toggleAdminPassword"
                                    class="fa-solid fa-eye cursor-pointer absolute right-3 top-3.5 text-gray-400 hover:text-gray-600 text-sm transition">
                                </i>
                            </div>
                        </div>

                        <button
                            class="bg-blue-600 text-white py-2.5 px-4 rounded-lg hover:bg-blue-700 active:bg-blue-800 w-full mt-8 font-semibold transition shadow-md hover:shadow-lg"
                            type="submit">Sign in</button>
                    </form>
                </div>
            </div>
        </section>

        <!-- Staff Login Screen -->
        <section id="staffScreen" class="flex h-screen hidden">
            <!-- Left Section -->
            <div
                class="hidden lg:flex lg:w-2/5 bg-gradient-to-br from-blue-950 to-blue-900 flex-col justify-center px-12 py-8">
                <div class="max-w-md">
                    <div class="flex gap-6 items-center mb-4">
                        <img src="assets/img/logo.jpg" alt="" class="w-18 h-18 rounded-full">
                        <h1 class="text-white text-3xl font-bold leading-tight">Southern
                            Leyte<br>Orthopaedic Clinic</h1>
                    </div>
                    <p class="text-blue-100 text-base leading-relaxed mb-8">
                        Integrated clinic management — patients can book appointments online while staff manage
                        consultations, billing, and reports.
                    </p>
                    <div class="space-y-4">
                        <div class="flex items-start gap-3">
                            <span class="w-1.5 h-1.5 bg-teal-400 rounded-full mt-2 flex-shrink-0"></span>
                            <span class="text-blue-100 text-sm">Online Appointment Booking</span>
                        </div>
                        <div class="flex items-start gap-3">
                            <span class="w-1.5 h-1.5 bg-teal-400 rounded-full mt-2 flex-shrink-0"></span>
                            <span class="text-blue-100 text-sm">Patient Self-Service Portal</span>
                        </div>
                        <div class="flex items-start gap-3">
                            <span class="w-1.5 h-1.5 bg-teal-400 rounded-full mt-2 flex-shrink-0"></span>
                            <span class="text-blue-100 text-sm">Consultation & Billing</span>
                        </div>
                        <div class="flex items-start gap-3">
                            <span class="w-1.5 h-1.5 bg-teal-400 rounded-full mt-2 flex-shrink-0"></span>
                            <span class="text-blue-100 text-sm">Report Generation</span>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Right Section -->
            <div class="w-full lg:w-3/5 flex flex-col justify-center items-center px-6 py-8">
                <div class="w-full max-w-md">
                    <!-- Back Button -->
                    <button onclick="backToRoleSelection()"
                        class="mb-6 text-gray-600 hover:text-gray-800 flex items-center gap-2 text-sm">
                        <i class="fa-solid fa-arrow-left"></i>Back
                    </button>

                    <!-- Header -->
                    <div class="mb-8">
                        <h1 class="text-2xl font-bold text-gray-800">Welcome Back</h1>
                        <p class="text-gray-500 text-sm mt-2">Sign in to your staff account</p>
                    </div>

                    <?php if ($loginError && ($_POST['loginType'] ?? '') === 'staff'): ?>
                        <p class="mb-4 p-3 rounded bg-red-100 text-red-700"><?= htmlspecialchars($loginError) ?></p>
                    <?php endif; ?>
                    <!-- Staff Login Form -->
                    <form class="space-y-5" method="post">
                        <input type="hidden" name="loginType" value="staff">
                        <div>
                            <label class="block text-gray-700 text-sm font-semibold mb-2"
                                for="username">Username</label>
                            <div class="relative">
                                <i class="fa-solid fa-user absolute left-3 top-3.5 text-gray-400 text-sm"></i>
                                <input
                                    class="bg-white rounded-lg pl-10 pr-4 py-2.5 w-full border border-gray-300 hover:border-gray-400 focus:outline-none focus:ring-2 focus:ring-violet-500 focus:border-transparent transition"
                                    type="text" name="username" id="staffUsername" placeholder="Enter your username"
                                    required>
                            </div>
                        </div>

                        <div>
                            <label class="block text-gray-700 text-sm font-semibold mb-2"
                                for="staffPassword">Password</label>
                            <div class="relative">
                                <i class="fa-solid fa-lock absolute left-3 top-3.5 text-gray-400 text-sm"></i>
                                <input
                                    class="bg-white rounded-lg pl-10 pr-10 py-2.5 w-full border border-gray-300 hover:border-gray-400 focus:outline-none focus:ring-2 focus:ring-violet-500 focus:border-transparent transition"
                                    type="password" name="password" id="staffPassword" placeholder="Enter your password"
                                    required>
                                <i id="toggleStaffPassword"
                                    class="fa-solid fa-eye cursor-pointer absolute right-3 top-3.5 text-gray-400 hover:text-gray-600 text-sm transition">
                                </i>
                            </div>
                        </div>

                        <button
                            class="bg-violet-600 text-white py-2.5 px-4 rounded-lg hover:bg-violet-700 active:bg-violet-800 w-full mt-8 font-semibold transition shadow-md hover:shadow-lg"
                            type="submit">Sign in</button>
                    </form>
                </div>
            </div>
        </section>

        <!-- Patient Login Screen -->
        <section id="patientScreen" class="flex h-screen hidden">
            <!-- Left Section -->
            <div
                class="hidden lg:flex lg:w-2/5 bg-gradient-to-br from-blue-950 to-blue-900 flex-col justify-center px-12 py-8">
                <div class="max-w-md">
                    <div class="flex gap-6 items-center mb-4">
                        <img src="assets/img/logo.jpg" alt="" class="w-18 h-18 rounded-full">
                        <h1 class="text-white text-3xl font-bold leading-tight">Southern
                            Leyte<br>Orthopaedic Clinic</h1>
                    </div>
                    <p class="text-blue-100 text-base leading-relaxed mb-8">
                        Integrated clinic management — patients can book appointments online while staff manage
                        consultations, billing, and reports.
                    </p>
                    <div class="space-y-4">
                        <div class="flex items-start gap-3">
                            <span class="w-1.5 h-1.5 bg-teal-400 rounded-full mt-2 flex-shrink-0"></span>
                            <span class="text-blue-100 text-sm">Online Appointment Booking</span>
                        </div>
                        <div class="flex items-start gap-3">
                            <span class="w-1.5 h-1.5 bg-teal-400 rounded-full mt-2 flex-shrink-0"></span>
                            <span class="text-blue-100 text-sm">Patient Self-Service Portal</span>
                        </div>
                        <div class="flex items-start gap-3">
                            <span class="w-1.5 h-1.5 bg-teal-400 rounded-full mt-2 flex-shrink-0"></span>
                            <span class="text-blue-100 text-sm">Consultation & Billing</span>
                        </div>
                        <div class="flex items-start gap-3">
                            <span class="w-1.5 h-1.5 bg-teal-400 rounded-full mt-2 flex-shrink-0"></span>
                            <span class="text-blue-100 text-sm">Report Generation</span>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Right Section -->
            <div class="w-full lg:w-3/5 flex flex-col justify-center items-center px-6 py-8">
                <div class="w-full max-w-md">
                    <!-- Back Button -->
                    <button onclick="backToRoleSelection()"
                        class="mb-6 text-gray-600 hover:text-gray-800 flex items-center gap-2 text-sm">
                        <i class="fa-solid fa-arrow-left"></i>Back
                    </button>

                    <!-- Header -->
                    <div class="mb-8">
                        <h1 class="text-2xl font-bold text-gray-800">Patient Login</h1>
                        <p class="text-gray-500 text-sm mt-2">Sign in to book and manage your appointments</p>
                    </div>

                    <?php if ($loginError && ($_POST['loginType'] ?? '') === 'patient'): ?>
                        <p class="mb-4 p-3 rounded bg-red-100 text-red-700"><?= htmlspecialchars($loginError) ?></p>
                    <?php endif; ?>

                    <!-- Patient Login Form -->
                    <form class="space-y-5" method="post">
                        <input type="hidden" name="loginType" value="patient">
                        <div>
                            <label class="block text-gray-700 text-sm font-semibold mb-2" for="patientId">Patient
                                ID</label>
                            <div class="relative">
                                <i class="fa-solid fa-id-card absolute left-3 top-3.5 text-gray-400 text-sm"></i>
                                <input
                                    class="bg-white rounded-lg pl-10 pr-4 py-2.5 w-full border border-gray-300 hover:border-gray-400 focus:outline-none focus:ring-2 focus:ring-teal-500 focus:border-transparent transition"
                                    type="text" name="username" id="patientId" placeholder="PT-YYYY-XXXX" required>
                            </div>
                        </div>

                        <div>
                            <label class="block text-gray-700 text-sm font-semibold mb-2"
                                for="patientPassword">Password</label>
                            <div class="relative">
                                <i class="fa-solid fa-lock absolute left-3 top-3.5 text-gray-400 text-sm"></i>
                                <input
                                    class="bg-white rounded-lg pl-10 pr-10 py-2.5 w-full border border-gray-300 hover:border-gray-400 focus:outline-none focus:ring-2 focus:ring-teal-500 focus:border-transparent transition"
                                    type="password" name="password" id="patientPassword"
                                    placeholder="Enter your password" required>
                                <i id="togglePatientPassword"
                                    class="fa-solid fa-eye cursor-pointer absolute right-3 top-3.5 text-gray-400 hover:text-gray-600 text-sm transition">
                                </i>
                            </div>
                        </div>

                        <button
                            class="bg-teal-600 text-white py-2.5 px-4 rounded-lg hover:bg-teal-700 active:bg-teal-800 w-full mt-8 font-semibold transition shadow-md hover:shadow-lg"
                            type="submit">Sign In as
                            Patient</button>
                    </form>

                    <!-- Register Link -->
                    <p class="text-center text-gray-600 text-sm mt-6">
                        Not registered yet? <a href="#"
                            class="text-teal-600 font-semibold hover:text-teal-700 transition">Contact the clinic to
                            register</a>
                    </p>
                </div>
            </div>
        </section>
    </main>
    <script>
        // Role Selection
        function selectRole(role) {
            const roleSelection = document.getElementById('roleSelection');
            const adminScreen = document.getElementById('adminScreen');
            const patientScreen = document.getElementById('patientScreen');
            const staffScreen = document.getElementById('staffScreen');

            roleSelection.classList.add('hidden');

            if (role === 'admin') {
                adminScreen.classList.remove('hidden');
            } else if (role === 'patient') {
                patientScreen.classList.remove('hidden');
            } else {
                staffScreen.classList.remove('hidden');
            }
        }

        function backToRoleSelection() {
            const roleSelection = document.getElementById('roleSelection');
            const adminScreen = document.getElementById('adminScreen');
            const patientScreen = document.getElementById('patientScreen');
            const staffScreen = document.getElementById('staffScreen');

            roleSelection.classList.remove('hidden');
            adminScreen.classList.add('hidden');
            patientScreen.classList.add('hidden');
            staffScreen.classList.add('hidden');
        }

        // Toggle Admin Password Visibility
        document.getElementById('toggleAdminPassword').addEventListener('click', function () {
            const passwordInput = document.getElementById('adminPassword');
            const type = passwordInput.getAttribute('type') === 'password' ? 'text' : 'password';
            passwordInput.setAttribute('type', type);
            this.classList.toggle('fa-eye-slash');
        });

        // Toggle Staff Password Visibility
        document.getElementById('toggleStaffPassword').addEventListener('click', function () {
            const passwordInput = document.getElementById('staffPassword');
            const type = passwordInput.getAttribute('type') === 'password' ? 'text' : 'password';
            passwordInput.setAttribute('type', type);
            this.classList.toggle('fa-eye-slash');
        });

        // Toggle Patient Password Visibility
        document.getElementById('togglePatientPassword').addEventListener('click', function () {
            const passwordInput = document.getElementById('patientPassword');
            const type = passwordInput.getAttribute('type') === 'password' ? 'text' : 'password';
            passwordInput.setAttribute('type', type);
            this.classList.toggle('fa-eye-slash');
        });
    </script>
</body>

</html>