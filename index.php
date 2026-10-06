<?php
require_once __DIR__ . '/config/config.php';
require_once __DIR__ . '/includes/auth.php';

if (SessionManager::isLoggedIn()) {
    $role = SessionManager::getCurrentRole();

    if ($role !== null && in_array(strtolower($role), ['admin', 'doctor', 'staff'], true)) {
        header('Location: admin/admin-dashboard.php');
        exit;
    }

    header('Location: unauthorized.php');
    exit;
}

$loginError = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = trim($_POST['username'] ?? '');
    $password = $_POST['password'] ?? '';
    $loginType = ($_POST['loginType'] ?? 'staff') === 'admin' ? 'admin' : 'staff';

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
        : $userRole === 'staff';

    if (!$user) {
        $loginError = 'Username not found. Check the username and try again.';
    } elseif (!$isActive) {
        $loginError = 'This account is inactive. Contact an administrator.';
    } elseif (!$validRole) {
        $loginError = $loginType === 'admin'
            ? 'This account is not an administrator or doctor account.'
            : 'This account is not registered as staff.';
    } elseif (!password_verify($password, $user['PasswordHash'])) {
        $loginError = 'Incorrect password. Check your password and try again.';
    } else {
        unset($user['PasswordHash']);
        session_regenerate_id(true);
        $_SESSION['user'] = $user;
        $_SESSION['user_id'] = $user['UserID'];
        $_SESSION['access_type'] = $user['Role'];
        header('Location: admin/admin-dashboard.php');
        exit;
    }
}

/* ---------- Reusable pieces ---------- */

// Left side: logo, clinic name and tagline card
function renderBrandPanel(): void
{ ?>
        <div class="brand">
            <img src="assets/img/logo3.png" alt="Southern Leyte Orthopaedic Clinic logo" class="brand-logo">
            <h1 class="brand-name">Southern Leyte<br><span>Orthopaedic Clinic</span></h1>
            <div class="tagline-card">
                <p>Integrated clinic management — patients can book appointments while staff manage consultations, billing, and reports.</p>
                <ul>
                    <li><i class="fa-solid fa-circle-check"></i> Appointment Booking</li>
                    <li><i class="fa-solid fa-circle-check"></i> Consultation &amp; Billing</li>
                    <li><i class="fa-solid fa-circle-check"></i> Report Generation</li>
                </ul>
            </div>
        </div>
<?php }

// Right side: login form card for a given role
function renderLoginCard(string $type, string $title, string $subtitle, string $idLabel, string $idPlaceholder, string $idIcon, string $button, string $loginError): void
{
    $cap = ucfirst($type); ?>
        <div class="card">
            <button type="button" onclick="backToRoleSelection()" class="back-btn">
                <i class="fa-solid fa-arrow-left"></i> Back
            </button>
            <h2 class="card-title"><?= htmlspecialchars($title) ?></h2>
            <p class="card-hint"><?= htmlspecialchars($subtitle) ?></p>

            <?php if ($loginError && ($_POST['loginType'] ?? '') === $type): ?>
                    <p class="alert" role="alert"><?= htmlspecialchars($loginError) ?></p>
            <?php endif; ?>

            <form method="post" class="form">
                <input type="hidden" name="loginType" value="<?= $type ?>">
                <label for="<?= $type ?>Username"><?= $idLabel ?></label>
                <div class="field">
                    <i class="fa-solid <?= $idIcon ?>"></i>
                    <input type="text" name="username" id="<?= $type ?>Username" placeholder="<?= $idPlaceholder ?>" required>
                </div>

                <label for="<?= $type ?>Password">Password</label>
                <div class="field">
                    <i class="fa-solid fa-lock"></i>
                    <input type="password" name="password" id="<?= $type ?>Password" placeholder="Enter your password" required>
                    <i id="toggle<?= $cap ?>Password" class="fa-solid fa-eye toggle-eye"></i>
                </div>

                <button type="submit" class="btn-primary"><?= htmlspecialchars($button) ?></button>
            </form>
            <?php if ($type === 'patient'): ?>
                    <p class="register">Not registered yet? <a href="#">Contact the clinic to register</a></p>
            <?php endif; ?>
        </div>
<?php }

// One role button on the selection card
function renderRoleButton(string $role, string $label, string $desc, string $icon): void
{ ?>
        <button type="button" onclick="selectRole('<?= $role ?>')" class="role-btn">
            <span class="role-icon"><i class="fa-solid <?= $icon ?>"></i></span>
            <span class="role-text">
                <strong><?= $label ?></strong>
                <small><?= $desc ?></small>
            </span>
            <i class="fa-solid fa-chevron-right role-arrow"></i>
        </button>
<?php }
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="./assets/css/output.css">
    <link rel="stylesheet" href="./assets/css/global.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="stylesheet" href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&display=swap">
    <link rel="icon" href="./assets/img/rounded-logo.ico" type="image/x-icon">
    <title>Login Page</title>
    <style>
        :root {
            --teal: #17807a;
            --teal-dark: #0f6661;
            --teal-soft: #d9efec;
            --ink: #12343b;
            --muted: #5d7479;
            --line: #d7e3e5;
        }

        * { box-sizing: border-box; }

        body {
            margin: 0;
            min-height: 100vh;
            font-family: 'Plus Jakarta Sans', system-ui, -apple-system, 'Segoe UI', sans-serif;
            color: var(--ink);
            background: #e9f2f3 url('assets/img/1.png') center / cover fixed no-repeat;
        }

        /* Soft white wash so the photo background stays light, like the mockup */
        .overlay { min-height: 100vh; background: linear-gradient(100deg, rgba(240, 248, 248, .88), rgba(240, 248, 248, .6) 55%, rgba(240, 248, 248, .8)); }

        .screen { display: flex; min-height: 100vh; align-items: center; justify-content: center; gap: 4rem; padding: 2rem 3rem; }
        .screen.hidden { display: none; }

        /* ---------- Left: brand ---------- */
        .brand { flex: 1 1 0; max-width: 520px; text-align: center; }
        .brand-logo { width: 150px; height: 150px; object-fit: contain; border-radius: 50%; margin: 0 auto .25rem; display: block; }
        .brand-name {
            margin: 0 0 1.75rem;
            font-size: 1.85rem; line-height: 1.15; font-weight: 800; letter-spacing: .06em;
            color: var(--teal); text-transform: uppercase;
        }
        .brand-name span { display: inline-block; margin-top: .5rem; padding-top: .5rem; border-top: 2px solid var(--teal); }

        .tagline-card {
            text-align: left; padding: 1.5rem 1.75rem;
            background: rgba(255, 255, 255, .8); border: 1px solid rgba(255, 255, 255, .9);
            border-radius: 1.25rem; backdrop-filter: blur(8px);
            box-shadow: 0 8px 30px rgba(18, 52, 59, .08);
        }
        .tagline-card p { margin: 0 0 1rem; font-size: .95rem; font-weight: 500; line-height: 1.45; }
        .tagline-card ul { list-style: none; margin: 0; padding: 0; display: grid; gap: .6rem; }
        .tagline-card li { display: flex; align-items: center; gap: .50rem; font-size: .80rem; }
        .tagline-card li i { color: var(--teal); font-size: 1rem; }

        /* ---------- Right: card ---------- */
        .card {
            flex: 0 0 400px; max-width: 100%;
            background: rgba(255, 255, 255, .96); border: 1px solid #fff;
            border-radius: 1.25rem; padding: 1.5rem;
            box-shadow: 0 20px 50px rgba(18, 52, 59, .15);
        }
        .card-eyebrow { margin: 0; text-align: center; font-weight: 700; color: var(--teal); font-size: 1rem; }
        .card-sub { margin: 0 0 1.25rem; text-align: center; color: var(--muted); font-size: .9rem; }
        .card-title { margin: 0; text-align: center; font-size: 1.5rem; font-weight: 800; }
        .card-hint { margin: .4rem 0 1.5rem; text-align: center; color: var(--muted); font-size: .95rem; }

        /* Role buttons */
        .role-btn {
            display: flex; align-items: center; gap: 1rem; width: 100%;
            margin-bottom: .85rem; padding: .9rem 1rem; text-align: left; cursor: pointer;
            font: inherit; color: inherit; background: #fff;
            border: 1.5px solid var(--line); border-radius: .85rem;
            transition: border-color .15s, background .15s, box-shadow .15s;
        }
        .role-btn:last-child { margin-bottom: 0; }
        .role-btn:hover, .role-btn:focus-visible { border-color: var(--teal); background: #f2faf9; box-shadow: 0 4px 14px rgba(23, 128, 122, .12); outline: none; }
        .role-icon { flex: none; width: 3.4rem; height: 3.4rem; display: grid; place-items: center; background: var(--teal-soft); color: var(--teal); border-radius: .7rem; font-size: 1.3rem; }
        .role-text { flex: 1; display: grid; gap: .15rem; }
        .role-text strong { font-size: 1.05rem; }
        .role-text small { color: var(--muted); font-size: .8rem; line-height: 1.35; }
        .role-arrow { color: #7b9094; font-size: .85rem; }

        /* Forms */
        .back-btn { margin: 0 0 1rem; padding: 0; background: none; border: 0; cursor: pointer; font: inherit; font-size: .85rem; color: var(--muted); display: inline-flex; gap: .4rem; align-items: center; }
        .back-btn:hover { color: var(--ink); }
        .form label { display: block; margin: 1rem 0 .4rem; font-size: .85rem; font-weight: 600; }
        .field { position: relative; }
        .field > i { position: absolute; left: .9rem; top: 50%; transform: translateY(-50%); color: #8ca0a4; font-size: .85rem; }
        .field input {
            width: 100%; padding: .7rem 2.4rem; font: inherit; color: var(--ink);
            background: #fff; border: 1.5px solid var(--line); border-radius: .65rem;
        }
        .field input:focus { outline: none; border-color: var(--teal); box-shadow: 0 0 0 3px rgba(23, 128, 122, .18); }
        .toggle-eye { left: auto !important; right: .9rem; cursor: pointer; }
        .toggle-eye:hover { color: var(--ink) !important; }
        .btn-primary {
            width: 100%; margin-top: 1.75rem; padding: .8rem 1rem; cursor: pointer;
            font: inherit; font-weight: 700; color: #fff; background: var(--teal);
            border: 0; border-radius: .65rem; transition: background .15s;
        }
        .btn-primary:hover { background: var(--teal-dark); }
        .btn-primary:focus-visible { outline: 3px solid rgba(23, 128, 122, .35); outline-offset: 2px; }
        .alert { margin: 0 0 .5rem; padding: .7rem .9rem; font-size: .9rem; color: #9b1c1c; background: #fde8e8; border-radius: .6rem; }
        .register { margin: 1.25rem 0 0; text-align: center; font-size: .9rem; color: var(--muted); }
        .register a { color: var(--teal); font-weight: 600; text-decoration: none; }
        .register a:hover { text-decoration: underline; }

        /* ---------- Responsive ---------- */
        @media (max-width: 1023px) {
            .screen { flex-direction: column; gap: 1.5rem; padding: 1.5rem 1rem; }
            .brand { max-width: 440px; }
            .brand-logo { width: 104px; height: 104px; margin-bottom: .25rem; }
            .brand-name { font-size: 1.4rem; margin-bottom: 1rem; }
            .tagline-card { display: none; }
            .card { flex: none; width: 100%; max-width: 400px; padding: 1.25rem; }
        }
    </style>
</head>

<body>
    <div class="overlay">
        <main>
            <!-- Role Selection Screen -->
            <section id="roleSelection" class="screen">
                <?php renderBrandPanel(); ?>
                <div class="card">
                    <h2 class="card-title">How would you like to login?</h2>
                    <p class="card-hint">Select your role to continue</p>

                    <?php renderRoleButton('admin', 'Admin', 'Manage clinic operations and patient records', 'fa-user-tie'); ?>
                    <?php renderRoleButton('staff', 'Staff', 'Access staff-only features and manage patient appointments', 'fa-user-group'); ?>
                </div>
            </section>

            <!-- Admin Login Screen -->
            <section id="adminScreen" class="screen hidden">
                <?php renderBrandPanel(); ?>
                <?php renderLoginCard('admin', 'Welcome Back', 'Sign in to your administrator account', 'Username', 'Enter your username', 'fa-user', 'Sign in', $loginError); ?>
            </section>

            <!-- Staff Login Screen -->
            <section id="staffScreen" class="screen hidden">
                <?php renderBrandPanel(); ?>
                <?php renderLoginCard('staff', 'Welcome Back', 'Sign in to your staff account', 'Username', 'Enter your username', 'fa-user', 'Sign in', $loginError); ?>
            </section>

            <!-- Patient Login Screen -->
            <section id="patientScreen" class="screen hidden">
                <?php renderBrandPanel(); ?>
                <?php renderLoginCard('patient', 'Patient Login', 'Sign in to book and manage your appointments', 'Patient ID', 'PT-YYYY-XXXX', 'fa-id-card', 'Sign In as Patient', $loginError); ?>
            </section>
        </main>
    </div>

    <script src="assets/javascript/login.js"></script>
    <script>
        document.addEventListener('DOMContentLoaded', function () {
            <?php if ($loginError && !empty($_POST['loginType'])): ?>
                    selectRole('<?= htmlspecialchars($_POST['loginType'], ENT_QUOTES) ?>');
            <?php endif; ?>
        });
    </script>
</body>

</html>