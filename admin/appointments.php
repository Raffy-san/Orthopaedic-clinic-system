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

        .age-filter-btn {
            background-color: #f1f5f9;
            /* slate-100 */
            color: #475569;
            /* slate-600 */
        }

        .age-filter-btn:hover {
            background-color: #e2e8f0;
        }

        .age-filter-btn.active-filter {
            background-color: #2563eb;
            /* blue-600 */
            color: #ffffff;
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
            <div class="grid grid-cols-1 gap-6 xl:grid-cols-2">
                <div class="rounded-3xl bg-white p-6 shadow-sm border border-slate-100">
                    <div class="mb-6 flex flex-col gap-4 border-b border-slate-100 pb-5">
                        <div class="flex items-start justify-between gap-3">
                            <div>
                                <h2 class="text-lg font-semibold text-slate-900">Choose a time</h2>
                                <p class="mt-1 text-sm text-slate-500">Available appointments for Saturday, July 25, 2026</p>
                            </div>
                            <span class="shrink-0 rounded-full bg-emerald-50 px-3 py-1 text-xs font-semibold text-emerald-700">9 available</span>
                        </div>
                        <p class="text-xs text-slate-500">Select an available slot to continue booking. Red slots are already taken.</p>
                        <div class="flex flex-wrap items-center gap-x-4 gap-y-2 text-xs text-slate-500">
                            <span class="flex items-center gap-2"><span
                                    class="h-3 w-3 rounded-full bg-red-100 border border-red-200"></span>Taken</span>
                            <span class="flex items-center gap-2"><span
                                    class="h-3 w-3 rounded-full bg-slate-100 border border-slate-200"></span>Available</span>
                            <span class="flex items-center gap-2"><span
                                    class="h-3 w-3 rounded-full bg-sky-600"></span>Selected</span>
                        </div>
                    </div>

                    <div class="mb-3 flex items-center justify-between">
                        <h3 class="text-xs font-bold uppercase tracking-wide text-slate-500">Morning</h3>
                        <span class="text-xs text-slate-400">8:00 AM - 12:00 PM</span>
                    </div>
                    <div class="mb-6 grid grid-cols-2 gap-3 sm:grid-cols-3">
                    <button
                        class="rounded-3xl bg-slate-100 px-4 py-4 text-sm font-semibold text-slate-700">08:00 AM</button>
                    <button class="rounded-3xl bg-red-100 px-4 py-4 text-sm font-semibold text-red-700">08:30 AM</button>
                    <button
                        class="rounded-3xl bg-slate-100 px-4 py-4 text-sm font-semibold text-slate-700">09:00 AM</button>
                    <button class="rounded-3xl bg-red-100 px-4 py-4 text-sm font-semibold text-red-700">09:30 AM</button>
                    <button
                        class="rounded-3xl bg-slate-100 px-4 py-4 text-sm font-semibold text-slate-700">10:00 AM</button>
                    <button class="rounded-3xl bg-red-100 px-4 py-4 text-sm font-semibold text-red-700">10:30 AM</button>
                    <button
                        class="rounded-3xl bg-slate-100 px-4 py-4 text-sm font-semibold text-slate-700">11:00 AM</button>
                    <button class="rounded-3xl bg-red-100 px-4 py-4 text-sm font-semibold text-red-700">11:30 AM</button>
                    </div>

                    <div class="mb-3 flex items-center justify-between">
                        <h3 class="text-xs font-bold uppercase tracking-wide text-slate-500">Afternoon</h3>
                        <span class="text-xs text-slate-400">2:00 PM - 5:00 PM</span>
                    </div>
                    <div class="grid grid-cols-2 gap-3 sm:grid-cols-3">
                    <button class="rounded-3xl bg-red-100 px-4 py-4 text-sm font-semibold text-red-700">02:00 PM</button>
                    <button
                        class="rounded-3xl bg-slate-100 px-4 py-4 text-sm font-semibold text-slate-700">02:30 PM</button>
                    <button
                        class="rounded-3xl bg-slate-100 px-4 py-4 text-sm font-semibold text-slate-700">03:00 PM</button>
                    <button
                        class="rounded-3xl bg-slate-100 px-4 py-4 text-sm font-semibold text-slate-700">03:30 PM</button>
                    <button
                        class="rounded-3xl bg-slate-100 px-4 py-4 text-sm font-semibold text-slate-700">04:00 PM</button>
                    <button
                        class="rounded-3xl bg-slate-100 px-4 py-4 text-sm font-semibold text-slate-700">04:30 PM</button>
                    </div>
                </div>

                <!-- Patient Locations Map -->
                <div class="rounded-3xl bg-white p-6 shadow-sm border border-slate-100">
                    <div class="mb-6 flex flex-col gap-4 border-b border-slate-100 pb-5">
                        <div>
                            <h2 class="text-lg font-semibold text-slate-900">Patient locations</h2>
                            <p class="mt-1 text-sm text-slate-500">Use the age filters, then select a pin to view appointment details.</p>
                        </div>

                        <div class="flex flex-wrap items-center gap-2" id="ageFilterGroup" aria-label="Filter patients by age">
                            <span class="mr-1 text-xs font-semibold text-slate-500">Show:</span>
                        <button data-age="all"
                            class="age-filter-btn active-filter rounded-full px-4 py-2 text-xs font-semibold transition">
                            All Ages
                        </button>
                        <button data-age="under30"
                            class="age-filter-btn rounded-full px-4 py-2 text-xs font-semibold transition">
                            Below 30
                        </button>
                        <button data-age="30to59"
                            class="age-filter-btn rounded-full px-4 py-2 text-xs font-semibold transition">
                            30 - 59
                        </button>
                        <button data-age="60plus"
                            class="age-filter-btn rounded-full px-4 py-2 text-xs font-semibold transition">
                            60 Above
                        </button>
                        </div>
                    </div>
                <div id="patientMap" class="w-full rounded-3xl overflow-hidden border border-slate-200"
                    style="height: 420px;"></div>
                <div class="mt-4 flex flex-wrap items-center gap-x-4 gap-y-2 text-xs text-slate-500" aria-label="Map status legend">
                    <span class="font-semibold text-slate-700">Appointment status:</span>
                    <span class="flex items-center gap-2"><span class="h-3 w-3 rounded-full bg-emerald-500"></span>Confirmed</span>
                    <span class="flex items-center gap-2"><span class="h-3 w-3 rounded-full bg-amber-500"></span>Pending</span>
                    <span class="flex items-center gap-2"><span class="h-3 w-3 rounded-full bg-rose-500"></span>Cancelled</span>
                </div>
                <p class="mt-3 text-[11px] leading-relaxed text-slate-400">
                    Pins are anonymized for privacy — only age bracket, gender, and status are shown here.
                    Full patient records are available in the patient's file, not on this map.
                </p>
                </div>
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
     * backend once patient addresses/coordinates/ages exist in the database, e.g.:
     *
     * const patients = <?php echo json_encode($patientsWithCoordinates); ?>;
     *
     * Each record needs: lat, lng, age, gender, status (and city, only for internal
     * grouping — it is not shown to the viewer). Do NOT include name, doctor, or
     * reason here — those stay in the patient's actual record/file and are pulled
     * up separately (e.g. only after the analyst clicks through with proper access),
     * not exposed on this map view.
     */
        const patients = [
            { city: "Imus, Cavite", lat: 14.4297, lng: 120.9367, age: 65, gender: "Female", status: "Confirmed" },
            { city: "Quezon City", lat: 14.6760, lng: 121.0437, age: 42, gender: "Male", status: "Confirmed" },
            { city: "Makati City", lat: 14.5547, lng: 121.0244, age: 27, gender: "Female", status: "Pending" },
            { city: "Dasmariñas, Cavite", lat: 14.3294, lng: 120.9367, age: 58, gender: "Male", status: "Pending" },
            { city: "Manila", lat: 14.5995, lng: 120.9842, age: 71, gender: "Female", status: "Cancelled" }
        ];

        const statusColors = {
            Confirmed: "#10b981", // emerald
            Pending: "#f59e0b",   // amber
            Cancelled: "#f43f5e"  // rose
        };

        // Buckets which each age falls into, so a single patient can match "all" + their own bucket
        function getAgeGroup(age) {
            if (age < 30) return "under30";
            if (age <= 59) return "30to59";
            return "60plus";
        }

        // Human-readable bracket label for the popup — never the exact age, just the range
        function getAgeLabel(age) {
            if (age < 30) return "Below 30";
            if (age <= 59) return "30 - 59";
            return "60 Above";
        }

        // Center the map roughly over Metro Manila / Cavite
        const map = L.map('patientMap').setView([14.55, 121.0], 10);

        L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
            maxZoom: 18,
            attribution: '&copy; OpenStreetMap contributors'
        }).addTo(map);

        // Keep a reference to each marker alongside its patient data so we can filter later
        const markers = patients.map((p, index) => {
            const marker = L.circleMarker([p.lat, p.lng], {
                radius: 9,
                fillColor: statusColors[p.status] || "#64748b",
                color: "#ffffff",
                weight: 2,
                fillOpacity: 0.9
            });

            // Anonymized popup: generic "Patient N" label, age bracket + gender in
            // parentheses, city for general area only, and status. No name, no
            // doctor, no visit reason — that stays in the actual patient file.
            const popupHtml = `
            <div class="patient-popup">
                <h3>Patient ${index + 1}</h3>
                <p>(${getAgeLabel(p.age)}, ${p.gender})</p>
                <p>${p.city}</p>
                <span class="status" style="background:${statusColors[p.status]}22; color:${statusColors[p.status]};">${p.status}</span>
            </div>
        `;
            marker.bindPopup(popupHtml);

            return { marker, ageGroup: getAgeGroup(p.age) };
        });

        // All markers visible by default
        markers.forEach(m => m.marker.addTo(map));

        // Filter button behavior
        const filterButtons = document.querySelectorAll('.age-filter-btn');
        filterButtons.forEach(btn => {
            btn.addEventListener('click', () => {
                const selected = btn.dataset.age;

                // toggle active styling
                filterButtons.forEach(b => b.classList.remove('active-filter'));
                btn.classList.add('active-filter');

                markers.forEach(({ marker, ageGroup }) => {
                    const shouldShow = selected === "all" || ageGroup === selected;
                    if (shouldShow) {
                        if (!map.hasLayer(marker)) marker.addTo(map);
                    } else {
                        if (map.hasLayer(marker)) map.removeLayer(marker);
                    }
                });
            });
        });
    </script>   
</body>

</html>