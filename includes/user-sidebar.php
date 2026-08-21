<div class="w-64 shrink-0 bg-blue-950 flex flex-col h-full">
    <div class="w-full border-b p-6 border-gray-600 flex items-center gap-4">
        <img src="../assets/img/icon-logo.ico" alt="SLOC Logo" class="w-12 h-12 object-contain rounded-full">
        <div>
            <h1 class="text-white text-md font-medium">SLOC Patient</h1>
            <h3 class="text-gray-300 text-sm">Patient Portal</h3>
        </div>
    </div>
    <div class="custom-scrollbar w-full p-6 overflow-y-auto flex-1">
        <h2 class="text-gray-500 font-bold text-sm mb-4">MODULES</h2>
        <div
            class="flex items-center p-3 rounded-lg gap-4 <?= basename($_SERVER['PHP_SELF']) == 'user-dashboard.php' ? 'active' : '' ?>">
            <i class="text-sm fa-solid fa-gauge text-white"></i>
            <a href="../users/user-dashboard.php"
                class="text-sm text-gray-300 hover:text-white font-medium">My Dashboard</a>
        </div>
        <div
            class="flex items-center p-3 rounded-lg gap-4 <?= basename($_SERVER['PHP_SELF']) == 'book-appointment.php' ? 'active' : '' ?>">
            <i class="text-sm fa-solid fa-user-plus text-white"></i>
            <a href="../users/book-appointment.php"
                class="text-sm text-gray-300 hover:text-white font-medium">Book Appointment</a>
        </div>
        <div
            class="flex items-center p-3 rounded-lg gap-4 <?= basename($_SERVER['PHP_SELF']) == 'my-appointment.php' ? 'active' : '' ?>">
            <i class="text-sm fa-solid fa-calendar text-white"></i>
            <a href="../users/my-appointment.php"
                class="text-sm text-gray-300 hover:text-white font-medium">My Appointments</a>
        </div>
        <div
            class="flex items-center p-3 rounded-lg gap-4 <?= basename($_SERVER['PHP_SELF']) == 'my-profile.php' ? 'active' : '' ?>">
            <i class="text-sm fa-solid fa-stethoscope text-white"></i>
            <a href="../users/my-profile.php"
                class="text-sm text-gray-300 hover:text-white font-medium">My Profile</a>
        </div>
    </div>
    <div class="mt-auto">
        <div class="w-full border-t pt-4 pl-6 border-gray-600 flex items-center gap-4">
            <i class="text-sm fa-solid fa-user text-white p-4 bg-teal-600 rounded-full"></i>
            <div>
                <h1 class="text-white text-md font-medium">Jose dela Cruz</h1>
                <h3 class="text-gray-300 text-sm">PT-2026-0080</h3>
            </div>
        </div>
        <div class="w-full p-4">
            <a href="../logout.php" class="text-gray-500 hover:text-red-700 font-medium text-sm"><i
                    class="fa-solid fa-arrow-left"></i> Logout</a>
        </div>
    </div>
</div>