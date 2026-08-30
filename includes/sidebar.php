<?php
$navItems = [
    [
        'label' => 'Dashboard',
        'icon' => 'fa-gauge',
        'file' => 'admin-dashboard.php',
        'href' => '../admin/admin-dashboard.php',
        'roles' => ['admin', 'staff'],
    ],
    [
        'label' => 'Patient Registration',
        'icon' => 'fa-user-plus',
        'file' => 'patient-registration.php',
        'href' => '../admin/patient-registration.php',
        'roles' => ['admin', 'staff'],
    ],
    [
        'label' => 'Appointments',
        'icon' => 'fa-calendar',
        'file' => 'appointments.php',
        'href' => '../admin/appointments.php',
        'roles' => ['admin', 'staff'],
    ],
    [
        'label' => 'Consultations',
        'icon' => 'fa-stethoscope',
        'file' => 'consultations.php',
        'href' => '../admin/consultations.php',
        'roles' => ['admin'],
    ],
    [
        'label' => 'Billing and Payment',
        'icon' => 'fa-credit-card',
        'file' => 'billing-payment.php',
        'href' => '../admin/billing-payment.php',
        'roles' => ['admin', 'staff'],
    ],
    [
        'label' => 'Follow-up Check-ups',
        'icon' => 'fa-heart-pulse',
        'file' => 'follow-up-checkups.php',
        'href' => '../admin/follow-up-checkups.php',
        'roles' => ['admin', 'staff'],
    ],
    [
        'label' => 'Reports',
        'icon' => 'fa-file-lines',
        'file' => 'reports.php',
        'href' => '../admin/reports.php',
        'roles' => ['admin'],
    ],
    [
        'label' => 'Staff Accounts',
        'icon' => 'fa-users-gear',
        'file' => 'staff-management.php',
        'href' => '../admin/staff-management.php',
        'roles' => ['admin'],
    ],
];

$currentRole = strtolower((string) (SessionManager::getCurrentRole() ?? 'staff'));
$currentFile = basename($_SERVER['PHP_SELF']);

?>
<div class="w-64 shrink-0 bg-blue-950 flex flex-col h-full">
    <div class="w-full border-b p-6 border-gray-600 flex items-center gap-4">
        <img src="../assets/img/icon-logo.ico" alt="SLOC Logo" class="w-12 h-12 object-contain rounded-full">
        <div>
            <h1 class="text-white text-md font-medium">SLOC System</h1>
            <h3 class="text-gray-300 text-sm">Orthopaedic Clinic</h3>
        </div>
    </div>

    <div class="custom-scrollbar w-full p-6 overflow-y-auto flex-1">
        <h2 class="text-gray-500 font-bold text-sm mb-4">MODULES</h2>

        <?php foreach ($navItems as $item): ?>
            <?php if (in_array($currentRole, $item['roles'], true)): ?>
                <div class="flex items-center p-3 rounded-lg gap-4 <?= $currentFile === $item['file'] ? 'active' : '' ?>">
                    <i class="text-sm fa-solid <?= htmlspecialchars($item['icon']) ?> text-white"></i>
                    <a href="<?= htmlspecialchars($item['href']) ?>" class="text-sm text-gray-300 hover:text-white font-medium">
                        <?= htmlspecialchars($item['label']) ?>
                    </a>
                </div>
            <?php endif; ?>
        <?php endforeach; ?>
    </div>

    <div class="mt-auto">
        <div class="w-full border-t pt-4 pl-6 border-gray-600 flex items-center gap-4">
            <i class="text-sm fa-solid fa-user text-white p-4 bg-teal-600 rounded-full"></i>
            <div>
                <?php if ($admin): ?>
                    <h1 class="text-white text-md font-medium">
                        <?= htmlspecialchars($admin['first_name']) ?>     <?= htmlspecialchars($admin['last_name']) ?>
                    </h1>
                    <h3 class="text-gray-300 text-sm"><?= htmlspecialchars($admin['role']) ?></h3>
                <?php endif; ?>
            </div>
        </div>
        <div class="w-full p-4">
            <a href="../logout.php" class="text-gray-500 hover:text-red-700 font-medium text-sm">
                <i class="fa-solid fa-arrow-left"></i> Logout
            </a>
        </div>
    </div>
</div>