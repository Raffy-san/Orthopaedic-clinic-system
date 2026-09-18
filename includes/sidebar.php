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
<div class="w-64 shrink-0 bg-[#0b1f0b] flex flex-col h-full">
    <div class="w-full border-b p-6 border-[#1e3a1e] flex items-center justify-center gap-4">
        <img src="../assets/img/icon-logo.ico" alt="SLOC Logo" class="w-12 h-12 object-contain rounded-full">
        <?php if ($currentFile !== 'admin-dashboard.php'): ?>
            <div>
                <h1 class="text-white text-md font-medium">SLOC System</h1>
                <h3 class="text-gray-300 text-sm">Orthopaedic Clinic</h3>
            </div>
        <?php endif; ?>
    </div>

    <div class="custom-scrollbar w-full p-6 overflow-y-auto flex-1 min-h-0">
        <h2 class="text-[#7fa05e] font-bold text-sm mb-4">MODULES</h2>

        <?php foreach ($navItems as $item): ?>
            <?php if (in_array($currentRole, $item['roles'], true)): ?>
                <?php $isActive = $currentFile === $item['file']; ?>
                <div
                    class="flex items-center p-3 rounded-xl gap-4 mb-1 <?= $isActive ? 'bg-gradient-to-r from-[#1e5c2e] to-[#2e7d3e] shadow-sm' : '' ?>">
                    <i
                        class="text-sm fa-solid <?= htmlspecialchars($item['icon']) ?> <?= $isActive ? 'text-white' : 'text-gray-300' ?>"></i>
                    <a href="<?= htmlspecialchars($item['href']) ?>"
                        class="text-sm font-medium <?= $isActive ? 'text-white' : 'text-gray-300 hover:text-white' ?>">
                        <?= htmlspecialchars($item['label']) ?>
                    </a>
                </div>
            <?php endif; ?>
        <?php endforeach; ?>
    </div>

    <div class="mt-auto">
        <div class="w-full border-t pt-4 pl-6 border-[#1e3a1e] flex items-center gap-4">
            <i class="text-sm fa-solid fa-user text-white p-4 bg-[#2e7d3e] rounded-full"></i>
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
            <a href="../logout.php" class="text-[#7fa05e] hover:text-red-400 font-medium text-sm">
                <i class="fa-solid fa-arrow-left"></i> Logout
            </a>
        </div>
    </div>
</div>