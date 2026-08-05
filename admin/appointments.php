<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="../assets/css/output.css">
    <link rel="stylesheet" href="../assets/css/global.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
    <title>Appointments</title>
</head>

<body class="h-screen flex bg-slate-200">
    <?php include_once '../includes/sidebar.php'; ?>
    <section class="flex-1 p-6 overflow-auto">
        <div class="flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between mb-6">
            <div>
                <h1 class="text-2xl font-bold text-slate-900">Appointment Scheduling</h1>
                <p class="mt-1 text-sm text-slate-500">Manage and book patient appointments</p>
            </div>
            <button class="inline-flex items-center justify-center rounded-3xl bg-blue-600 px-5 py-3 text-sm font-semibold text-white shadow-sm transition hover:bg-blue-700">
                + Book Appointment
            </button>
        </div>

        <div class="space-y-6">
            <div class="rounded-3xl bg-white p-6 shadow-sm border border-slate-100">
                <div class="flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between mb-6">
                    <div>
                        <p class="text-sm font-semibold text-slate-900">Available Slots <span class="font-normal text-slate-500">— July 25, 2026</span></p>
                    </div>
                    <div class="flex flex-wrap items-center gap-4 text-sm text-slate-500">
                        <span class="flex items-center gap-2"><span class="h-3 w-3 rounded-full bg-red-100 border border-red-200"></span>Taken</span>
                        <span class="flex items-center gap-2"><span class="h-3 w-3 rounded-full bg-slate-100 border border-slate-200"></span>Available</span>
                        <span class="flex items-center gap-2"><span class="h-3 w-3 rounded-full bg-sky-600"></span>Selected</span>
                    </div>
                </div>

                <div class="grid grid-cols-2 sm:grid-cols-4 xl:grid-cols-6 gap-3">
                    <button class="rounded-3xl bg-slate-100 px-4 py-4 text-sm font-semibold text-slate-700">08:00</button>
                    <button class="rounded-3xl bg-red-100 px-4 py-4 text-sm font-semibold text-red-700">08:30</button>
                    <button class="rounded-3xl bg-slate-100 px-4 py-4 text-sm font-semibold text-slate-700">09:00</button>
                    <button class="rounded-3xl bg-red-100 px-4 py-4 text-sm font-semibold text-red-700">09:30</button>
                    <button class="rounded-3xl bg-slate-100 px-4 py-4 text-sm font-semibold text-slate-700">10:00</button>
                    <button class="rounded-3xl bg-red-100 px-4 py-4 text-sm font-semibold text-red-700">10:30</button>
                    <button class="rounded-3xl bg-slate-100 px-4 py-4 text-sm font-semibold text-slate-700">11:00</button>
                    <button class="rounded-3xl bg-red-100 px-4 py-4 text-sm font-semibold text-red-700">11:30</button>
                    <button class="rounded-3xl bg-red-100 px-4 py-4 text-sm font-semibold text-red-700">14:00</button>
                    <button class="rounded-3xl bg-slate-100 px-4 py-4 text-sm font-semibold text-slate-700">14:30</button>
                    <button class="rounded-3xl bg-slate-100 px-4 py-4 text-sm font-semibold text-slate-700">15:00</button>
                    <button class="rounded-3xl bg-slate-100 px-4 py-4 text-sm font-semibold text-slate-700">15:30</button>
                    <button class="rounded-3xl bg-slate-100 px-4 py-4 text-sm font-semibold text-slate-700">16:00</button>
                    <button class="rounded-3xl bg-slate-100 px-4 py-4 text-sm font-semibold text-slate-700">16:30</button>
                </div>
            </div>

            <div class="rounded-3xl bg-white p-6 shadow-sm border border-slate-100">
                <div class="flex items-center justify-between mb-6">
                    <h2 class="text-lg font-semibold text-slate-900">Today's Appointments</h2>
                </div>

                <div class="space-y-3">
                    <div class="flex flex-col gap-3 rounded-3xl border border-slate-200 bg-slate-50 p-4 sm:flex-row sm:items-center sm:justify-between">
                        <div class="flex items-start gap-4">
                            <div class="min-w-[90px] rounded-3xl bg-white px-3 py-2 text-sm font-semibold text-slate-700 shadow-sm">11:30 AM</div>
                            <div>
                                <p class="font-semibold text-slate-900">Carmen Reyes</p>
                                <p class="text-sm text-slate-500">Dr. Reyes · Follow-up</p>
                            </div>
                        </div>
                        <span class="rounded-full bg-emerald-100 px-3 py-1 text-xs font-semibold uppercase text-emerald-700">Confirmed</span>
                    </div>

                    <div class="flex flex-col gap-3 rounded-3xl border border-slate-200 bg-slate-50 p-4 sm:flex-row sm:items-center sm:justify-between">
                        <div class="flex items-start gap-4">
                            <div class="min-w-[90px] rounded-3xl bg-white px-3 py-2 text-sm font-semibold text-slate-700 shadow-sm">02:00 PM</div>
                            <div>
                                <p class="font-semibold text-slate-900">Diego Morales</p>
                                <p class="text-sm text-slate-500">Dr. Villanueva · X-Ray Review</p>
                            </div>
                        </div>
                        <span class="rounded-full bg-emerald-100 px-3 py-1 text-xs font-semibold uppercase text-emerald-700">Confirmed</span>
                    </div>

                    <div class="flex flex-col gap-3 rounded-3xl border border-slate-200 bg-slate-50 p-4 sm:flex-row sm:items-center sm:justify-between">
                        <div class="flex items-start gap-4">
                            <div class="min-w-[90px] rounded-3xl bg-white px-3 py-2 text-sm font-semibold text-slate-700 shadow-sm">03:30 PM</div>
                            <div>
                                <p class="font-semibold text-slate-900">Elena Castro</p>
                                <p class="text-sm text-slate-500">Dr. Fuentes · New Patient</p>
                            </div>
                        </div>
                        <span class="rounded-full bg-amber-100 px-3 py-1 text-xs font-semibold uppercase text-amber-700">Pending</span>
                    </div>

                    <div class="flex flex-col gap-3 rounded-3xl border border-slate-200 bg-slate-50 p-4 sm:flex-row sm:items-center sm:justify-between">
                        <div class="flex items-start gap-4">
                            <div class="min-w-[90px] rounded-3xl bg-white px-3 py-2 text-sm font-semibold text-slate-700 shadow-sm">04:00 PM</div>
                            <div>
                                <p class="font-semibold text-slate-900">Ricardo Bautista</p>
                                <p class="text-sm text-slate-500">Dr. Reyes · Consultation</p>
                            </div>
                        </div>
                        <span class="rounded-full bg-amber-100 px-3 py-1 text-xs font-semibold uppercase text-amber-700">Pending</span>
                    </div>

                    <div class="flex flex-col gap-3 rounded-3xl border border-slate-200 bg-slate-50 p-4 sm:flex-row sm:items-center sm:justify-between">
                        <div class="flex items-start gap-4">
                            <div class="min-w-[90px] rounded-3xl bg-white px-3 py-2 text-sm font-semibold text-slate-700 shadow-sm">04:30 PM</div>
                            <div>
                                <p class="font-semibold text-slate-900">Marisol Ramos</p>
                                <p class="text-sm text-slate-500">Dr. Fuentes · Follow-up</p>
                            </div>
                        </div>
                        <span class="rounded-full bg-rose-100 px-3 py-1 text-xs font-semibold uppercase text-rose-700">Cancelled</span>
                    </div>
                </div>
            </div>
        </div>
    </section>
</body>

</html>