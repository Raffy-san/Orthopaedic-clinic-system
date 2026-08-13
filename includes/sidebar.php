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
        <div
            class="flex items-center p-3 rounded-lg gap-4 <?= basename($_SERVER['PHP_SELF']) == 'admin-dashboard.php' ? 'active' : '' ?>">
            <i class="text-sm fa-solid fa-gauge text-white"></i>
            <a href="../admin/admin-dashboard.php" class="text-sm text-gray-300 hover:text-white font-medium">Dashboard</a>
        </div>
        <div
            class="flex items-center p-3 rounded-lg gap-4 <?= basename($_SERVER['PHP_SELF']) == 'patient-registration.php' ? 'active' : '' ?>">
            <i class="text-sm fa-solid fa-user-plus text-white"></i>
            <a href="../admin/patient-registration.php" class="text-sm text-gray-300 hover:text-white font-medium">Patient
                Registration</a>
        </div>
        <div
            class="flex items-center p-3 rounded-lg gap-4 <?= basename($_SERVER['PHP_SELF']) == 'appointments.php' ? 'active' : '' ?>">
            <i class="text-sm fa-solid fa-calendar text-white"></i>
            <a href="../admin/appointments.php" class="text-sm text-gray-300 hover:text-white font-medium">Appointments</a>
        </div>
        <div
            class="flex items-center p-3 rounded-lg gap-4 <?= basename($_SERVER['PHP_SELF']) == 'consultations.php' ? 'active' : '' ?>">
            <i class="text-sm fa-solid fa-stethoscope text-white"></i>
            <a href="../admin/consultations.php" class="text-sm text-gray-300 hover:text-white font-medium">Consultations</a>
        </div>
        <div
            class="flex items-center p-3 rounded-lg gap-4 <?= basename($_SERVER['PHP_SELF']) == 'billing-payment.php' ? 'active' : '' ?>">
            <i class="text-sm fa-solid fa-credit-card text-white"></i>
            <a href="../admin/billing-payment.php" class="text-sm text-gray-300 hover:text-white font-medium">Billing and
                Payment</a>
        </div>
        <div
            class="flex items-center p-3 rounded-lg gap-4 <?= basename($_SERVER['PHP_SELF']) == 'follow-up-checkups.php' ? 'active' : '' ?>">
            <i class="text-sm fa-solid fa-heart-pulse text-white"></i>
            <a href="../admin/follow-up-checkups.php" class="text-sm text-gray-300 hover:text-white font-medium">Follow-up
                Check-ups</a>
        </div>
        <div
            class="flex items-center p-3 rounded-lg gap-4 <?= basename($_SERVER['PHP_SELF']) == 'reports.php' ? 'active' : '' ?>">
            <i class="text-sm fa-solid fa-file-lines text-white"></i>
            <a href="../admin/reports.php" class="text-sm text-gray-300 hover:text-white font-medium">Reports</a>
        </div>
    </div>
    <div class="mt-auto">
        <div class="w-full border-t pt-4 pl-6 border-gray-600 flex items-center gap-4">
            <i class="text-sm fa-solid fa-user text-white p-4 bg-teal-600 rounded-full"></i>
            <div>
                <h1 class="text-white text-md font-medium">Admin</h1>
                <h3 class="text-gray-300 text-sm">Administrator</h3>
            </div>
        </div>
        <div class="w-full p-4">
            <a href="../index.php" class="text-gray-500 hover:text-red-700 font-medium text-sm"><i
                    class="fa-solid fa-arrow-left"></i> Logout</a>
        </div>
    </div>
</div>