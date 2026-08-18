<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="../assets/css/output.css">
    <link rel="stylesheet" href="../assets/css/global.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
    <!-- Leaflet map library (free, no API key required) -->
    <link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css" />
    <link rel="icon" href="../assets/img/rounded-logo.ico" type="image/x-icon">
    <title>Appointments</title>
    <style>
        /* Keep popup styling consistent with the rest of the UI */
        .leaflet-popup-content-wrapper {
            border-radius: 1rem;
        }

        .patient-popup h3 {
            font-weight: 700;
            color: #0f172a;
            margin-bottom: 2px;
        }

        .patient-popup p {
            font-size: 0.8rem;
            color: #64748b;
            margin: 0;
        }

        .patient-popup .status {
            display: inline-block;
            margin-top: 6px;
            font-size: 0.7rem;
            font-weight: 600;
            text-transform: uppercase;
            padding: 2px 8px;
            border-radius: 9999px;
        }
    </style>
</head>

<body class="h-screen flex bg-slate-200">
    <?php include_once '../includes/sidebar.php'; ?>
    <section class="flex-1 p-6 overflow-auto">
        <div class="flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between mb-6">
            <div>
                <h1 class="text-2xl font-bold text-slate-900">Appointment Scheduling</h1>
                <p class="mt-1 text-sm text-slate-500">Manage and book patient appointments</p>
            </div>
            <button
                class="inline-flex items-center justify-center rounded-3xl bg-blue-600 px-5 py-3 text-sm font-semibold text-white shadow-sm transition hover:bg-blue-700">
                + Book Appointment
            </button>
        </div>

        <div class="space-y-6">
            <div class="rounded-3xl bg-white p-6 shadow-sm border border-slate-100">
                <div class="flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between mb-6">
                    <div>
                        <p class="text-sm font-semibold text-slate-900">Available Slots <span
                                class="font-normal text-slate-500">— July 25, 2026</span></p>
                    </div>
                    <div class="flex flex-wrap items-center gap-4 text-sm text-slate-500">
                        <span class="flex items-center gap-2"><span
                                class="h-3 w-3 rounded-full bg-red-100 border border-red-200"></span>Taken</span>
                        <span class="flex items-center gap-2"><span
                                class="h-3 w-3 rounded-full bg-slate-100 border border-slate-200"></span>Available</span>
                        <span class="flex items-center gap-2"><span
                                class="h-3 w-3 rounded-full bg-sky-600"></span>Selected</span>
                    </div>
                </div>

                <div class="grid grid-cols-2 sm:grid-cols-4 xl:grid-cols-6 gap-3">
                    <button
                        class="rounded-3xl bg-slate-100 px-4 py-4 text-sm font-semibold text-slate-700">08:00</button>
                    <button class="rounded-3xl bg-red-100 px-4 py-4 text-sm font-semibold text-red-700">08:30</button>
                    <button
                        class="rounded-3xl bg-slate-100 px-4 py-4 text-sm font-semibold text-slate-700">09:00</button>
                    <button class="rounded-3xl bg-red-100 px-4 py-4 text-sm font-semibold text-red-700">09:30</button>
                    <button
                        class="rounded-3xl bg-slate-100 px-4 py-4 text-sm font-semibold text-slate-700">10:00</button>
                    <button class="rounded-3xl bg-red-100 px-4 py-4 text-sm font-semibold text-red-700">10:30</button>
                    <button
                        class="rounded-3xl bg-slate-100 px-4 py-4 text-sm font-semibold text-slate-700">11:00</button>
                    <button class="rounded-3xl bg-red-100 px-4 py-4 text-sm font-semibold text-red-700">11:30</button>
                    <button class="rounded-3xl bg-red-100 px-4 py-4 text-sm font-semibold text-red-700">14:00</button>
                    <button
                        class="rounded-3xl bg-slate-100 px-4 py-4 text-sm font-semibold text-slate-700">14:30</button>
                    <button
                        class="rounded-3xl bg-slate-100 px-4 py-4 text-sm font-semibold text-slate-700">15:00</button>
                    <button
                        class="rounded-3xl bg-slate-100 px-4 py-4 text-sm font-semibold text-slate-700">15:30</button>
                    <button
                        class="rounded-3xl bg-slate-100 px-4 py-4 text-sm font-semibold text-slate-700">16:00</button>
                    <button
                        class="rounded-3xl bg-slate-100 px-4 py-4 text-sm font-semibold text-slate-700">16:30</button>
                </div>
            </div>

            <!-- Patient Locations Map -->
            <div class="rounded-3xl bg-white p-6 shadow-sm border border-slate-100">
                <div class="flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between mb-6">
                    <div>
                        <h2 class="text-lg font-semibold text-slate-900">Patient Locations</h2>
                        <p class="mt-1 text-sm text-slate-500">Click a pin to view that patient's appointment details
                        </p>
                    </div>
                </div>
                <div id="patientMap" class="w-full rounded-3xl overflow-hidden border border-slate-200"
                    style="height: 420px;"></div>
            </div>

            <div class="rounded-3xl bg-white p-6 shadow-sm border border-slate-100">
                <div class="flex items-center justify-between mb-6">
                    <h2 class="text-lg font-semibold text-slate-900">Today's Appointments</h2>
                </div>

                <div class="space-y-3">
                    <div
                        class="flex flex-col gap-3 rounded-3xl border border-slate-200 bg-slate-50 p-4 sm:flex-row sm:items-center sm:justify-between">
                        <div class="flex items-start gap-4">
                            <div
                                class="min-w-[90px] rounded-3xl bg-white px-3 py-2 text-sm font-semibold text-slate-700 shadow-sm">
                                11:30 AM</div>
                            <div>
                                <p class="font-semibold text-slate-900">Carmen Reyes</p>
                                <p class="text-sm text-slate-500">Dr. Reyes · Follow-up</p>
                            </div>
                        </div>
                        <span
                            class="rounded-full bg-emerald-100 px-3 py-1 text-xs font-semibold uppercase text-emerald-700">Confirmed</span>
                    </div>

                    <div
                        class="flex flex-col gap-3 rounded-3xl border border-slate-200 bg-slate-50 p-4 sm:flex-row sm:items-center sm:justify-between">
                        <div class="flex items-start gap-4">
                            <div
                                class="min-w-[90px] rounded-3xl bg-white px-3 py-2 text-sm font-semibold text-slate-700 shadow-sm">
                                02:00 PM</div>
                            <div>
                                <p class="font-semibold text-slate-900">Diego Morales</p>
                                <p class="text-sm text-slate-500">Dr. Villanueva · X-Ray Review</p>
                            </div>
                        </div>
                        <span
                            class="rounded-full bg-emerald-100 px-3 py-1 text-xs font-semibold uppercase text-emerald-700">Confirmed</span>
                    </div>

                    <div
                        class="flex flex-col gap-3 rounded-3xl border border-slate-200 bg-slate-50 p-4 sm:flex-row sm:items-center sm:justify-between">
                        <div class="flex items-start gap-4">
                            <div
                                class="min-w-[90px] rounded-3xl bg-white px-3 py-2 text-sm font-semibold text-slate-700 shadow-sm">
                                03:30 PM</div>
                            <div>
                                <p class="font-semibold text-slate-900">Elena Castro</p>
                                <p class="text-sm text-slate-500">Dr. Fuentes · New Patient</p>
                            </div>
                        </div>
                        <span
                            class="rounded-full bg-amber-100 px-3 py-1 text-xs font-semibold uppercase text-amber-700">Pending</span>
                    </div>

                    <div
                        class="flex flex-col gap-3 rounded-3xl border border-slate-200 bg-slate-50 p-4 sm:flex-row sm:items-center sm:justify-between">
                        <div class="flex items-start gap-4">
                            <div
                                class="min-w-[90px] rounded-3xl bg-white px-3 py-2 text-sm font-semibold text-slate-700 shadow-sm">
                                04:00 PM</div>
                            <div>
                                <p class="font-semibold text-slate-900">Ricardo Bautista</p>
                                <p class="text-sm text-slate-500">Dr. Reyes · Consultation</p>
                            </div>
                        </div>
                        <span
                            class="rounded-full bg-amber-100 px-3 py-1 text-xs font-semibold uppercase text-amber-700">Pending</span>
                    </div>

                    <div
                        class="flex flex-col gap-3 rounded-3xl border border-slate-200 bg-slate-50 p-4 sm:flex-row sm:items-center sm:justify-between">
                        <div class="flex items-start gap-4">
                            <div
                                class="min-w-[90px] rounded-3xl bg-white px-3 py-2 text-sm font-semibold text-slate-700 shadow-sm">
                                04:30 PM</div>
                            <div>
                                <p class="font-semibold text-slate-900">Marisol Ramos</p>
                                <p class="text-sm text-slate-500">Dr. Fuentes · Follow-up</p>
                            </div>
                        </div>
                        <span
                            class="rounded-full bg-rose-100 px-3 py-1 text-xs font-semibold uppercase text-rose-700">Cancelled</span>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- Leaflet map library -->
    <script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"></script>
    <script>
        /*
         * MOCK DATA — replace this array with a PHP loop or a fetch() call to your
         * backend once patient addresses/coordinates exist in the database, e.g.:
         *
         * const patients = <?php echo json_encode($patientsWithCoordinates); ?>;
         *
         * Each patient record needs: name, doctor, reason, time, status, city, lat, lng.
         */
        const patients = [
            { name: "Carmen Reyes", doctor: "Dr. Reyes", reason: "Follow-up", time: "11:30 AM", status: "Confirmed", city: "Imus, Cavite", lat: 14.4297, lng: 120.9367 },
            { name: "Diego Morales", doctor: "Dr. Villanueva", reason: "X-Ray Review", time: "02:00 PM", status: "Confirmed", city: "Quezon City", lat: 14.6760, lng: 121.0437 },
            { name: "Elena Castro", doctor: "Dr. Fuentes", reason: "New Patient", time: "03:30 PM", status: "Pending", city: "Makati City", lat: 14.5547, lng: 121.0244 },
            { name: "Ricardo Bautista", doctor: "Dr. Reyes", reason: "Consultation", time: "04:00 PM", status: "Pending", city: "Dasmariñas, Cavite", lat: 14.3294, lng: 120.9367 },
            { name: "Marisol Ramos", doctor: "Dr. Fuentes", reason: "Follow-up", time: "04:30 PM", status: "Cancelled", city: "Manila", lat: 14.5995, lng: 120.9842 }
        ];

        const statusColors = {
            Confirmed: "#10b981", // emerald
            Pending: "#f59e0b",   // amber
            Cancelled: "#f43f5e"  // rose
        };

        // Center the map roughly over Metro Manila / Cavite
        const map = L.map('patientMap').setView([14.55, 121.0], 10);

        L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
            maxZoom: 18,
            attribution: '&copy; OpenStreetMap contributors'
        }).addTo(map);

        patients.forEach(p => {
            const marker = L.circleMarker([p.lat, p.lng], {
                radius: 9,
                fillColor: statusColors[p.status] || "#64748b",
                color: "#ffffff",
                weight: 2,
                fillOpacity: 0.9
            }).addTo(map);

            const popupHtml = `
                <div class="patient-popup">
                    <h3>${p.name}</h3>
                    <p>${p.doctor} · ${p.reason}</p>
                    <p>${p.time} — ${p.city}</p>
                    <span class="status" style="background:${statusColors[p.status]}22; color:${statusColors[p.status]};">${p.status}</span>
                </div>
            `;
            marker.bindPopup(popupHtml);
        });
    </script>
</body>

</html>