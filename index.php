<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="./assets/css/output.css">
    <link rel="stylesheet" href="./assets/css/global.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
    <title>Login Page</title>
</head>

<body class="w-full min-h-screen overflow-y-auto">
    <main>
        <section class="flex h-screen">
            <div class="bg-blue-950 w-2/5 h-full flex justify-center items-center flex-col">
                <div class="flex flex-row justify-center items-center gap-6">
                    <img src="assets/img/logo.jpg" alt="Logo" class="w-32 h-32 mb-4 rounded-full object-cover">
                    <div>
                        <h1 class="text-white text-3xl font-bold">Orthopaedic Clinic</h1>
                        <p class="text-gray-300 mt-2">Your health, our priority</p>
                    </div>
                </div>
                <div class="mt-6 text-start px-20 text-white">
                    <p class="text-md italic">
                        Integrated clinic management for patient registration, appointments, consultations, billing, and
                        medical records.
                    </p>
                </div>
                <div class="mt-10 space-y-5">
                    <div class="flex items-center gap-3">
                        <div class="w-8 h-8 bg-teal-500 rounded-lg flex items-center justify-center">
                            <i class="fas fa-user-plus text-white text-sm"></i>
                        </div>
                        <div>
                            <h3 class="text-white font-semibold">Patient Registration</h3>
                        </div>
                    </div>

                    <div class="flex items-center gap-3">
                        <div class="w-8 h-8 bg-teal-500 rounded-lg flex items-center justify-center">
                            <i class="fas fa-calendar-check text-white text-sm"></i>
                        </div>
                        <div>
                            <h3 class="text-white font-semibold">Appointment Scheduling</h3>
                        </div>
                    </div>

                    <div class="flex items-center gap-3">
                        <div class="w-8 h-8 bg-teal-500 rounded-lg flex items-center justify-center">
                            <i class="fas fa-stethoscope text-white text-sm"></i>
                        </div>
                        <div>
                            <h3 class="text-white font-semibold">Consultation Records</h3>
                        </div>
                    </div>

                    <div class="flex items-center gap-3">
                        <div class="w-8 h-8 bg-teal-500 rounded-lg flex items-center justify-center">
                            <i class="fas fa-credit-card text-white text-sm"></i>
                        </div>
                        <div>
                            <h3 class="text-white font-semibold">Billing & Payments</h3>
                        </div>
                    </div>

                    <div class="flex items-center gap-3">
                        <div class="w-8 h-8 bg-teal-500 rounded-lg flex items-center justify-center">
                            <i class="fas fa-file-medical text-white text-sm"></i>
                        </div>
                        <div>
                            <h3 class="text-white font-semibold">Reports & Analytics</h3>
                        </div>
                    </div>
                </div>
            </div>
            <div class="bg-gray-200 w-3/5 flex justify-center items-center flex-col">
                <div class="w-full max-w-sm">
                    <div class="mb-6">
                        <h1 class="text-3xl font-bold">Welcome back</h1>
                        <h3 class="text-gray-500 mt-2">Sign in to your administrator account</h3>
                    </div>
                    <div class="w-full max-w-sm gap-4">
                        <form class="space-y-4" action="">
                            <div class="relative">
                                <label class="block text-gray-700 text-sm font-bold mb-2"
                                    for="username">Username</label>
                                <input
                                    class="bg-white rounded-xl p-2 w-full focus:outline-none focus:ring-2 focus:ring-blue-700"
                                    type="text" name="username" id="username" placeholder="Enter your username"
                                    autocomplete="username" required>
                            </div>
                            <div class="relative">
                                <label class="block text-gray-700 text-sm font-bold mb-2"
                                    for="password">Password</label>
                                <input
                                    class="bg-white rounded-xl p-2 w-full focus:outline-none focus:ring-2 focus:ring-blue-700"
                                    type="password" name="password" id="password" placeholder="Enter your password"
                                    autocomplete="current-password" required>
                                <i id="togglePassword"
                                    class="fa-solid fa-eye cursor-pointer absolute right-3 bottom-2 py-1 text-gray-600 text-sm sm:text-base">
                                </i>
                            </div>
                            <button class="bg-blue-800 text-white py-2 px-4 rounded-lg hover:bg-blue-900 w-full mt-3"
                                type="button" onclick="window.location.href='admin/admin-dashboard.php';">Sign
                                in</button>
                        </form>
                    </div>
                </div>
            </div>
        </section>
    </main>
    <script>
        document.getElementById('togglePassword').addEventListener('click', function () {
            const passwordInput = document.getElementById('password');
            const type = passwordInput.getAttribute('type') === 'password' ? 'text' : 'password';
            passwordInput.setAttribute('type', type);
            this.classList.toggle('fa-eye-slash');
        });
    </script>
</body>

</html>