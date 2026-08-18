<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="../assets/css/output.css">
    <link rel="stylesheet" href="../assets/css/global.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
    <link rel="icon" href="../assets/img/rounded-logo.ico" type="image/x-icon">
    <title>Dashboard</title>
</head>

<body class="h-screen flex bg-slate-200">
    <?php include_once '../includes/sidebar.php'; ?>
    <section class="flex-1 p-6 overflow-auto">
        <div>
            <h1 class="text-2xl font-bold">Dashboard</h1>
            <h3 class="text-md font-medium text-gray-500">Wednesday, July 29, 2026 - Welcome, Admin</h3>
        </div>
        <div class="flex w-full gap-4 mt-6">
            <div class="bg-white p-6 rounded-lg shadow-md flex-1">
                <div class="flex justify-between items-center mb-4">
                    <h2 class="text-gray-500 text-md font-semibold">Patients Today</h2>
                    <div class="w-12 h-12 rounded-2xl bg-purple-100 flex items-center justify-center">
                        <i class="fas fa-user-injured text-purple-600"></i>
                    </div>
                </div>
                <p class="text-gray-800 text-3xl font-extrabold">58</p>
                <p class="text-gray-500 text-sm font-medium">+3 from yesterday</p>
            </div>
            <div class="bg-white p-6 rounded-lg shadow-md flex-1">
                <div class="flex justify-between items-center mb-4">
                    <h2 class="text-gray-500 text-md font-semibold">Appointments </h2>
                    <div class="w-12 h-12 rounded-2xl bg-cyan-100 flex items-center justify-center">
                        <i class="fas fa-calendar text-cyan-600"></i>
                    </div>
                </div>
                <p class="text-gray-800 text-3xl font-extrabold">18</p>
                <p class="text-gray-500 text-sm font-medium">6 pending · 12 completed</p>
            </div>
            <div class="bg-white p-6 rounded-lg shadow-md flex-1">
                <div class="flex justify-between items-center mb-4">
                    <h2 class="text-gray-500 text-md font-semibold">Consultations</h2>
                    <div class="w-12 h-12 rounded-2xl bg-orange-100 flex items-center justify-center">
                        <i class="fas fa-stethoscope text-orange-500"></i>
                    </div>
                </div>
                <p class="text-gray-800 text-3xl font-extrabold">11</p>
                <p class="text-gray-500 text-sm font-medium">5 in progress</p>
            </div>
            <div class="bg-white p-6 rounded-lg shadow-md flex-1">
                <div class="flex justify-between items-center mb-4">
                    <h2 class="text-gray-500 text-md font-semibold">Revenue Today</h2>
                    <div class="w-12 h-12 rounded-2xl bg-green-100 flex items-center justify-center">
                        <i class="fas fa-money-bill text-green-600"></i>
                    </div>
                </div>
                <p class="text-gray-800 text-3xl font-extrabold">₱12,345</p>
                <p class="text-gray-500 text-sm font-medium">vs ₱20,000 yesterday</p>
            </div>
        </div>
        <div class="grid grid-cols-3 gap-6 mt-8">

            <!-- Recent Patients -->
            <div class="col-span-2 bg-white rounded-2xl shadow-sm border border-gray-100">
                <div class="flex items-center justify-between px-6 py-5 border-b border-gray-100">
                    <h2 class="text-lg font-semibold text-gray-800">
                        Recent Patients
                    </h2>

                    <button class="text-sm text-blue-500 hover:text-blue-600 font-medium">
                        Today & Yesterday
                    </button>
                </div>

                <div class="overflow-x-auto">
                    <table class="w-full">
                        <thead class="text-left text-sm text-gray-500">
                            <tr class="border-b border-gray-100">
                                <th class="px-6 py-4 font-medium">Patient ID</th>
                                <th class="px-6 py-4 font-medium">Name</th>
                                <th class="px-6 py-4 font-medium">Date</th>
                                <th class="px-6 py-4 font-medium">Status</th>
                            </tr>
                        </thead>

                        <tbody class="text-sm">

                            <tr class="border-b border-gray-100 hover:bg-gray-50">
                                <td class="px-6 py-4 text-gray-500">
                                    PT-2026-0001
                                </td>

                                <td class="px-6 py-4 font-medium">
                                    Maria Santos
                                </td>

                                <td class="px-6 py-4 text-gray-500">
                                    Jul 30, 2026
                                </td>

                                <td class="px-6 py-4">
                                    <span class="bg-blue-100 text-blue-600 text-xs font-medium px-3 py-1 rounded-full">
                                        Registered
                                    </span>
                                </td>
                            </tr>

                            <tr class="border-b border-gray-100 hover:bg-gray-50">
                                <td class="px-6 py-4 text-gray-500">
                                    PT-2026-0002
                                </td>

                                <td class="px-6 py-4 font-medium">
                                    Juan Dela Cruz
                                </td>

                                <td class="px-6 py-4 text-gray-500">
                                    Jul 30, 2026
                                </td>

                                <td class="px-6 py-4">
                                    <span
                                        class="bg-yellow-100 text-yellow-700 text-xs font-medium px-3 py-1 rounded-full">
                                        Waiting
                                    </span>
                                </td>
                            </tr>

                            <tr class="border-b border-gray-100 hover:bg-gray-50">
                                <td class="px-6 py-4 text-gray-500">
                                    PT-2026-0003
                                </td>

                                <td class="px-6 py-4 font-medium">
                                    Roberto Lim
                                </td>

                                <td class="px-6 py-4 text-gray-500">
                                    Jul 29, 2026
                                </td>

                                <td class="px-6 py-4">
                                    <span
                                        class="bg-green-100 text-green-600 text-xs font-medium px-3 py-1 rounded-full">
                                        Completed
                                    </span>
                                </td>
                            </tr>

                            <tr class="hover:bg-gray-50">
                                <td class="px-6 py-4 text-gray-500">
                                    PT-2026-0004
                                </td>

                                <td class="px-6 py-4 font-medium">
                                    Ana Macaraeg
                                </td>

                                <td class="px-6 py-4 text-gray-500">
                                    Jul 29, 2026
                                </td>

                                <td class="px-6 py-4">
                                    <span
                                        class="bg-purple-100 text-purple-600 text-xs font-medium px-3 py-1 rounded-full">
                                        Billed
                                    </span>
                                </td>
                            </tr>

                        </tbody>
                    </table>
                </div>
            </div>

            <!-- Today's Schedule -->
            <div class="bg-white rounded-2xl shadow-sm border border-gray-100">

                <div class="px-6 py-5 border-b border-gray-100">
                    <h2 class="text-lg font-semibold text-gray-800">
                        Today's Schedule
                    </h2>
                </div>

                <div class="p-5 space-y-4">

                    <div class="flex items-start gap-4">
                        <div class="bg-blue-100 text-blue-600 rounded-xl px-3 py-2 text-sm font-semibold">
                            9:00
                        </div>

                        <div>
                            <h3 class="font-semibold">
                                Maria Santos
                            </h3>

                            <p class="text-sm text-gray-500">
                                Consultation
                            </p>
                        </div>
                    </div>

                    <div class="flex items-start gap-4">
                        <div class="bg-green-100 text-green-600 rounded-xl px-3 py-2 text-sm font-semibold">
                            10:30
                        </div>

                        <div>
                            <h3 class="font-semibold">
                                Juan Dela Cruz
                            </h3>

                            <p class="text-sm text-gray-500">
                                Follow-up
                            </p>
                        </div>
                    </div>

                    <div class="flex items-start gap-4">
                        <div class="bg-yellow-100 text-yellow-700 rounded-xl px-3 py-2 text-sm font-semibold">
                            1:00
                        </div>

                        <div>
                            <h3 class="font-semibold">
                                Roberto Lim
                            </h3>

                            <p class="text-sm text-gray-500">
                                X-Ray Review
                            </p>
                        </div>
                    </div>

                    <div class="flex items-start gap-4">
                        <div class="bg-purple-100 text-purple-600 rounded-xl px-3 py-2 text-sm font-semibold">
                            3:00
                        </div>

                        <div>
                            <h3 class="font-semibold">
                                Ana Macaraeg
                            </h3>

                            <p class="text-sm text-gray-500">
                                Billing
                            </p>
                        </div>
                    </div>

                </div>

            </div>

        </div>

    </section>
</body>

</html>