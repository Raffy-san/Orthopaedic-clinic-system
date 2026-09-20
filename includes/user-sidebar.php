<div class="w-64 shrink-0 bg-[#0b1f0b] flex flex-col h-full">
    <div class="w-full border-b p-6 border-[#1e3a1e] flex items-center gap-4">
        <img src="../assets/img/icon-logo.ico" alt="SLOC Logo" class="w-12 h-12 object-contain rounded-full">
        <div>
            <h1 class="text-white text-md font-medium">SLOC Patient</h1>
            <h3 class="text-gray-300 text-sm">Patient Portal</h3>
        </div>
    </div>
    <div class="custom-scrollbar w-full p-6 overflow-y-auto flex-1">
        <h2 class="text-[#7fa05e] font-bold text-sm mb-4">MODULES</h2>
        <div
            class="flex items-center p-3 rounded-xl gap-4 <?= basename($_SERVER['PHP_SELF']) == 'user-dashboard.php' ? 'bg-gradient-to-r from-[#1e5c2e] to-[#2e7d3e] shadow-sm' : '' ?>">
            <i class="text-sm fa-solid fa-gauge text-white <?= basename($_SERVER['PHP_SELF']) == 'user-dashboard.php' ? 'text-white' : '' ?>"></i>
            <a href="../users/user-dashboard.php"
                class="text-sm text-gray-300 hover:text-white font-medium <?= basename($_SERVER['PHP_SELF']) == 'user-dashboard.php' ? 'text-white' : '' ?>">Dashboard</a>
        </div>
        <div
            class="flex items-center p-3 rounded-xl gap-4 <?= basename($_SERVER['PHP_SELF']) == 'my-appointment.php' ? 'bg-gradient-to-r from-[#1e5c2e] to-[#2e7d3e] shadow-sm' : '' ?>">
            <i class="text-sm fa-solid fa-user-plus text-white <?= basename($_SERVER['PHP_SELF']) == 'my-appointment.php' ? 'text-white' : '' ?>"></i>
            <a href="../users/my-appointment.php" class="text-sm text-gray-300 hover:text-white font-medium <?= basename($_SERVER['PHP_SELF']) == 'my-appointment.php' ? 'text-white' : '' ?>">My
                Appointments</a>
        </div>
        <div
            class="flex items-center p-3 rounded-xl gap-4 <?= basename($_SERVER['PHP_SELF']) == 'my-records.php' ? 'bg-gradient-to-r from-[#1e5c2e] to-[#2e7d3e] shadow-sm' : '' ?>">
            <i class="text-sm fa-solid fa-stethoscope text-white <?= basename($_SERVER['PHP_SELF']) == 'my-records.php' ? 'text-white' : '' ?>"></i>
            <a href="../users/my-records.php" class="text-sm text-gray-300 hover:text-white font-medium <?= basename($_SERVER['PHP_SELF']) == 'my-records.php' ? 'text-white' : '' ?>">My
                Records</a>
        </div>
        <div
            class="flex items-center p-3 rounded-xl gap-4 <?= basename($_SERVER['PHP_SELF']) == 'my-profile.php' ? 'bg-gradient-to-r from-[#1e5c2e] to-[#2e7d3e] shadow-sm' : '' ?>">
            <i class="text-sm fa-solid fa-user text-white <?= basename($_SERVER['PHP_SELF']) == 'my-profile.php' ? 'text-white' : '' ?>"></i>
            <a href="../users/my-profile.php" class="text-sm text-gray-300 hover:text-white font-medium <?= basename($_SERVER['PHP_SELF']) == 'my-profile.php' ? 'text-white' : '' ?>">My Profile</a>
        </div>
    </div>
    <div class="mt-auto">
        <div class="w-full border-t pt-4 pl-6 border-[#1e3a1e] flex items-center gap-4">
            <i class="text-sm fa-solid fa-user text-white p-4 bg-[#2e7d3e] rounded-full"></i>
            <div>
                <?php if ($patient): ?>
                    <h1 class="text-white text-md font-medium"><?= htmlspecialchars($patient['first_name']) ?>
                        <?= htmlspecialchars($patient['last_name']) ?>
                    </h1>
                    <h3 class="text-gray-300 text-sm"><?= htmlspecialchars($patient['username']) ?></h3>
                <?php endif; ?>
            </div>
        </div>
        <div class="w-full p-4">
            <a href="../logout.php" class="text-[#7fa05e] hover:text-red-700 font-medium text-sm"><i
                    class="fa-solid fa-arrow-left"></i> Logout</a>
        </div>
    </div>
</div>