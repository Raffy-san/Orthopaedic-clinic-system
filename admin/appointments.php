<?php
require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/../includes/auth.php';
require_once __DIR__ . '/../includes/crud.php';
require_once __DIR__ . '/../php/fetch/fetch.php';

SessionManager::requireLogin();
SessionManager::requireAnyRole(['admin', 'doctor', 'staff']);
$csrfToken = $_SESSION['csrf_token'] ?? SessionManager::regenerateCsrfToken();
$currentRole = strtolower((string) (SessionManager::getCurrentRole() ?? ''));
$canApprove = in_array($currentRole, ['admin', 'doctor'], true);
$canUpdateAppointment = in_array($currentRole, ['staff'], true);

$admin = SessionManager::getUser($pdo);
expirePendingAppointments($pdo);
$patientsRaw = fetchAllData($pdo, "
    SELECT p.Province AS province, p.City AS city, p.Barangay AS barangay,
           TIMESTAMPDIFF(YEAR, p.BirthDate, CURDATE()) AS age, a.AppointmentDate as date,
           a.AppointmentTime as time, a.meridiem as meridiem,
           p.Gender AS gender, COALESCE(a.Status, 'Pending') AS status
    FROM patients p
    INNER JOIN appointments a ON a.AppointmentID = (
        SELECT MAX(a2.AppointmentID) FROM appointments a2
        WHERE a2.PatientID = p.PatientID AND a2.Status IN ('Confirmed', 'Pending')
    )
    WHERE p.Province IS NOT NULL AND p.City IS NOT NULL AND p.Barangay IS NOT NULL
    ORDER BY p.CreatedAt DESC
");

function slugifyProvinceName(string $name): string
{
    $slug = strtolower($name);
    $slug = preg_replace('/[^a-z0-9]+/', '-', $slug);
    return trim($slug, '-');
}

$boundaryCache = [];

function normalizeBarangayName(string $name): string
{
    // Boundary dataset doesn't include the "(Pob.)" poblacion marker that
    // PSGC dropdown data uses — strip it so names match.
    $name = preg_replace('/\s*\(Pob\.?\)\s*/i', '', $name);
    return trim($name);
}

function getBarangayCentroid(string $province, string $city, string $barangay, array &$cache): ?array
{
    $slug = slugifyProvinceName($province);

    if (!isset($cache[$slug])) {
        $path = __DIR__ . '/../boundaries/' . $slug . '.json';
        if (!file_exists($path)) {
            $cache[$slug] = null;
        } else {
            $cache[$slug] = json_decode(file_get_contents($path), true);
        }
    }

    $data = $cache[$slug];
    if (!$data) {
        return null;
    }

    $matchedCity = null;
    foreach ($data['cities'] as $c) {
        if (strcasecmp($c['name'], $city) === 0) {
            $matchedCity = $c;
            break;
        }
    }
    if (!$matchedCity) {
        return null;
    }

    $normalizedBarangay = normalizeBarangayName($barangay);

    foreach ($data['barangays'] as $b) {
        if (
            $b['cityCode'] === $matchedCity['code']
            && strcasecmp(normalizeBarangayName($b['name']), $normalizedBarangay) === 0
        ) {
            return $b['centroid'] ?? null;
        }
    }

    return null;
}

$patientsWithCoordinates = [];
foreach ($patientsRaw as $row) {
    $centroid = getBarangayCentroid($row['province'], $row['city'], $row['barangay'], $boundaryCache);
    if (!$centroid) {
        continue; // no matching boundary data — skip rather than plot a wrong/blank pin
    }

    $row['lat'] = $centroid['lat'];
    $row['lng'] = $centroid['lng'];
    $patientsWithCoordinates[] = $row;
}

$appointments = fetchAllData($pdo, "
    SELECT a.AppointmentID, a.AppointmentDate, a.AppointmentTime, a.Meridiem, a.Purpose, a.Status,
           p.PatientCode, p.FirstName AS patient_first_name, p.LastName AS patient_last_name
    FROM appointments a
    INNER JOIN patients p ON a.PatientID = p.PatientID
    ORDER BY a.AppointmentDate DESC, a.AppointmentTime DESC
");

if (!$admin) {
    SessionManager::logout('../index.php');
}
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="../assets/css/output.css">
    <link rel="stylesheet" href="../assets/css/global.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
    <link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css" />
    <link rel="icon" href="../assets/img/rounded-logo.ico" type="image/x-icon">
    <title>Appointments</title>
</head>

<body class="h-screen flex bg-slate-200 overflow-hidden">
    <?php include_once '../includes/sidebar.php'; ?>
    <section class="flex-1 min-h-0 flex flex-col overflow-hidden">
        <div
            class="flex items-center justify-between bg-gradient-to-r from-[#0b1f0b] via-[#1e6b34] to-[#2e8b47] px-6 py-4">
            <h1 class="text-2xl font-bold text-white">Southern Leyte Orthopaedic Clinic System</h1>
            <div class="flex flex-col gap-3 items-end">
                <h1 class="text-2xl font-bold text-white">Appointment Scheduling</h1>
                <h3 class="text-sm font-medium text-white">Manage and book patient appointments</h3>
            </div>
        </div>

        <div class="flex-1 min-h-0 overflow-auto p-6 space-y-4">
            <div class="grid grid-cols-1 gap-6 xl:grid-cols-2">
                <div class="space-y-6">
                    <div class="rounded-3xl bg-white p-6 shadow-sm border border-slate-100">
                        <h2 class="text-lg font-semibold text-slate-900">Choose an appointment date</h2>
                        <p class="mt-1 text-sm text-slate-500">Select a date to see which appointment times are
                            available.</p>
                        <label for="appointmentDate" class="mt-5 block text-sm font-semibold text-slate-700">Appointment
                            date</label>
                        <input type="date" id="appointmentDate" name="appointment_date"
                            class="mt-2 w-full border border-gray-300 bg-white rounded-lg px-4 py-3 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500">
                    </div>

                    <div id="appointmentTimeCalendar"
                        class="hidden rounded-3xl bg-white p-6 shadow-sm border border-slate-100">
                        <div class="mb-6 flex flex-col gap-4 border-b border-slate-100 pb-5">
                            <div class="flex items-start justify-between gap-3">
                                <div>
                                    <h2 class="text-lg font-semibold text-slate-900">Choose a time</h2>
                                    <p id="appointmentDateMessage" class="mt-1 text-sm text-slate-500">Choose an
                                        appointment date first.</p>
                                </div>
                                <span id="availableSlotCount"
                                    class="shrink-0 rounded-full bg-emerald-50 px-3 py-1 text-xs font-semibold text-emerald-700"></span>
                            </div>
                            <p class="text-xs text-slate-500">Select an available slot to continue booking. Red slots
                                are
                                already taken.</p>
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
                            <button type="button" data-time="08:00 AM"
                                class="time-slot rounded-3xl bg-slate-100 px-4 py-4 text-sm font-semibold text-slate-700 hover:bg-sky-100">08:00
                                AM</button>
                            <button type="button" data-time="08:30 AM"
                                class="time-slot rounded-3xl bg-slate-100 px-4 py-4 text-sm font-semibold text-slate-700 hover:bg-sky-100">08:30
                                AM</button>
                            <button type="button" data-time="09:00 AM"
                                class="time-slot rounded-3xl bg-slate-100 px-4 py-4 text-sm font-semibold text-slate-700 hover:bg-sky-100">09:00
                                AM</button>
                            <button type="button" data-time="09:30 AM"
                                class="time-slot rounded-3xl bg-slate-100 px-4 py-4 text-sm font-semibold text-slate-700 hover:bg-sky-100">09:30
                                AM</button>
                            <button type="button" data-time="10:00 AM"
                                class="time-slot rounded-3xl bg-slate-100 px-4 py-4 text-sm font-semibold text-slate-700 hover:bg-sky-100">10:00
                                AM</button>
                            <button type="button" data-time="10:30 AM"
                                class="time-slot rounded-3xl bg-slate-100 px-4 py-4 text-sm font-semibold text-slate-700 hover:bg-sky-100">10:30
                                AM</button>
                            <button type="button" data-time="11:00 AM"
                                class="time-slot rounded-3xl bg-slate-100 px-4 py-4 text-sm font-semibold text-slate-700 hover:bg-sky-100">11:00
                                AM</button>
                            <button type="button" data-time="11:30 AM"
                                class="time-slot rounded-3xl bg-slate-100 px-4 py-4 text-sm font-semibold text-slate-700 hover:bg-sky-100">11:30
                                AM</button>
                        </div>

                        <div class="mb-3 flex items-center justify-between">
                            <h3 class="text-xs font-bold uppercase tracking-wide text-slate-500">Afternoon</h3>
                            <span class="text-xs text-slate-400">2:00 PM - 5:00 PM</span>
                        </div>
                        <div class="grid grid-cols-2 gap-3 sm:grid-cols-3">
                            <button type="button" data-time="02:00 PM"
                                class="time-slot rounded-3xl bg-slate-100 px-4 py-4 text-sm font-semibold text-slate-700 hover:bg-sky-100">02:00
                                PM</button>
                            <button type="button" data-time="02:30 PM"
                                class="time-slot rounded-3xl bg-slate-100 px-4 py-4 text-sm font-semibold text-slate-700 hover:bg-sky-100">02:30
                                PM</button>
                            <button type="button" data-time="03:00 PM"
                                class="time-slot rounded-3xl bg-slate-100 px-4 py-4 text-sm font-semibold text-slate-700 hover:bg-sky-100">03:00
                                PM</button>
                            <button type="button" data-time="03:30 PM"
                                class="time-slot rounded-3xl bg-slate-100 px-4 py-4 text-sm font-semibold text-slate-700 hover:bg-sky-100">03:30
                                PM</button>
                            <button type="button" data-time="04:00 PM"
                                class="time-slot rounded-3xl bg-slate-100 px-4 py-4 text-sm font-semibold text-slate-700 hover:bg-sky-100">04:00
                                PM</button>
                            <button type="button" data-time="04:30 PM"
                                class="time-slot rounded-3xl bg-slate-100 px-4 py-4 text-sm font-semibold text-slate-700 hover:bg-sky-100">04:30
                                PM</button>
                        </div>
                    </div>
                </div>

                <!-- Patient Locations Map -->
                <div class="rounded-3xl bg-white p-6 shadow-sm border border-slate-100">
                    <div class="mb-6 flex flex-col gap-4 border-b border-slate-100 pb-5">
                        <div>
                            <h2 class="text-lg font-semibold text-slate-900">Patient locations</h2>
                            <p class="mt-1 text-sm text-slate-500">Use the age filters, then select a pin to view
                                appointment details.</p>
                        </div>

                        <div class="flex flex-wrap items-center gap-2" id="ageFilterGroup"
                            aria-label="Filter patients by age">
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
                    <div class="mt-4 flex flex-wrap items-center gap-x-4 gap-y-2 text-xs text-slate-500"
                        aria-label="Map status legend">
                        <span class="font-semibold text-slate-700">Appointment status:</span>
                        <span class="flex items-center gap-2"><span
                                class="h-3 w-3 rounded-full bg-emerald-500"></span>Confirmed</span>
                        <span class="flex items-center gap-2"><span
                                class="h-3 w-3 rounded-full bg-amber-500"></span>Pending</span>
                        <span class="flex items-center gap-2"><span
                                class="h-3 w-3 rounded-full bg-rose-500"></span>Cancelled</span>
                    </div>
                    <p class="mt-3 text-[11px] leading-relaxed text-slate-400">
                        Pins are anonymized for privacy — only age bracket, gender, and status are shown here.
                        Full patient records are available in the patient's file, not on this map.
                    </p>
                </div>
            </div>

            <div class="rounded-3xl bg-white p-6 shadow-sm border border-slate-100">
                <div class="flex items-center justify-between mb-6">
                    <h2 class="text-lg font-semibold text-slate-900">Pending Requests</h2>
                    <span
                        class="inline-flex items-center justify-center rounded-full bg-amber-100 px-3 py-1 text-xs font-bold text-amber-700">
                        <?php
                        $pendingCount = count(array_filter($appointments, fn($a) => $a['Status'] === 'Pending'));
                        echo $pendingCount;
                        ?>
                    </span>
                </div>

                <div class="space-y-3">
                    <?php
                    $pendingAppointments = array_filter($appointments, fn($a) => $a['Status'] === 'Pending');

                    if ($pendingAppointments) {
                        foreach ($pendingAppointments as $appointment) {
                            $appointmentDate = new DateTime($appointment['AppointmentDate']);
                            $formattedDate = $appointmentDate->format('M d');
                            $formattedTime = (new DateTime($appointment['AppointmentTime']))->format('g:i A');
                            ?>

                            <div class="rounded-lg bg-amber-50 p-4 border border-amber-100">
                                <div class="flex items-start justify-between">
                                    <div class="flex-1">
                                        <p class="text-sm font-semibold text-slate-900">
                                            <?= htmlspecialchars($appointment['patient_first_name'], ENT_QUOTES, 'UTF-8') ?>
                                            <?= htmlspecialchars($appointment['patient_last_name'], ENT_QUOTES, 'UTF-8') ?>
                                            <span class="text-slate-400 font-normal">
                                                · <?= htmlspecialchars($appointment['PatientCode'], ENT_QUOTES, 'UTF-8') ?>
                                            </span>
                                        </p>
                                        <p class="text-xs text-slate-500 mt-1">
                                            <?= $formattedDate ?> at <?= $formattedTime ?> ·
                                            <?= htmlspecialchars($appointment['Purpose'], ENT_QUOTES, 'UTF-8') ?>
                                        </p>
                                    </div>

                                    <div class="flex gap-2 ml-4 items-center">
                                        <?php if ($canApprove): ?>
                                            <button
                                                class="confirm-btn inline-flex items-center justify-center rounded-full bg-emerald-600 px-3 py-2 text-xs font-semibold text-white hover:bg-emerald-700 transition"
                                                data-appointment-id="<?= htmlspecialchars($appointment['AppointmentID'], ENT_QUOTES, 'UTF-8') ?>"
                                                title="Confirm">
                                                ✓ Confirm
                                            </button>
                                            <button
                                                class="decline-btn inline-flex items-center justify-center rounded-full bg-red-500 px-3 py-2 text-xs font-semibold text-white hover:bg-red-700 transition"
                                                data-appointment-id="<?= htmlspecialchars($appointment['AppointmentID'], ENT_QUOTES, 'UTF-8') ?>"
                                                title="Decline">
                                                ✕ Decline
                                            </button>
                                        <?php else: ?>
                                            <span
                                                class="text-xs font-semibold text-slate-500 bg-slate-100 px-3 py-2 rounded-full whitespace-nowrap">
                                                Admin/Doctor Approval Required
                                            </span>
                                        <?php endif; ?>
                                    </div>
                                </div>
                            </div>
                            <?php
                        }
                    } else {
                        echo '<p class="text-sm text-slate-500">No pending requests.</p>';
                    }
                    ?>
                </div>
            </div>

            <div class="rounded-3xl bg-white p-6 shadow-sm border border-slate-100">
                <div class="flex items-center justify-between mb-6">
                    <h2 class="text-lg font-semibold text-slate-900">Confirmed Schedule</h2>
                </div>

                <div class="space-y-3">
                    <?php

                    $confirmedAppointments = array_filter($appointments, fn($a) => $a['Status'] === 'Confirmed');

                    if ($confirmedAppointments) {
                        foreach ($confirmedAppointments as $appointment) {
                            $appointmentDate = new DateTime($appointment['AppointmentDate']);
                            $formattedDate = $appointmentDate->format('M d, Y');
                            $formattedTime = (new DateTime($appointment['AppointmentTime']))->format('h:i');
                            $statusStyle = $status[$appointment['Status']] ?? ['bgColor' => '#4FFFB0', 'textColor' => '#3CB371'];

                            ?>

                            <div
                                class="flex flex-col gap-3 rounded-3xl border border-slate-200 bg-slate-50 p-4 sm:flex-row sm:items-center sm:justify-between">
                                <div class="flex items-start gap-4">
                                    <div
                                        class="min-w-[90px] rounded-3xl bg-white px-3 py-2 text-sm font-semibold text-slate-700 shadow-sm">
                                        <?= htmlspecialchars($formattedTime, ENT_QUOTES, 'UTF-8') ?> <?= htmlspecialchars($appointment['Meridiem'], ENT_QUOTES, 'UTF-8') ?>
                                    </div>
                                    <div>
                                        <p class="font-semibold text-slate-900">
                                            <?= htmlspecialchars($appointment['patient_first_name'], ENT_QUOTES, 'UTF-8') ?>
                                            <?= htmlspecialchars($appointment['patient_last_name'], ENT_QUOTES, 'UTF-8') ?>
                                        </p>
                                        <p class="text-sm text-slate-500">
                                            <?= $formattedDate ?> -
                                            <?= htmlspecialchars($appointment['Purpose'], ENT_QUOTES, 'UTF-8') ?>
                                        </p>
                                    </div>
                                </div>
                                <div class="flex items-center gap-3">
                                    <span class="rounded-full px-3 py-1 text-xs font-semibold uppercase"
                                        style="background-color: <?= htmlspecialchars($statusStyle['bgColor'], ENT_QUOTES, 'UTF-8') ?>; color: <?= htmlspecialchars($statusStyle['textColor'], ENT_QUOTES, 'UTF-8') ?>;">
                                        <?= htmlspecialchars($appointment['Status'], ENT_QUOTES, 'UTF-8') ?>
                                    </span>
                                    <?php if ($canUpdateAppointment): ?>
                                        <button
                                            class="reschedule-btn inline-flex items-center justify-center rounded-full bg-sky-600 px-3 py-2 text-xs font-semibold text-white transition hover:bg-sky-700"
                                            data-appointment-id="<?= htmlspecialchars($appointment['AppointmentID'], ENT_QUOTES, 'UTF-8') ?>"
                                            data-current-date="<?= htmlspecialchars($appointment['AppointmentDate'], ENT_QUOTES, 'UTF-8') ?>"
                                            data-current-time="<?= htmlspecialchars($formattedTime, ENT_QUOTES, 'UTF-8') ?>"
                                            data-patient-name="<?= htmlspecialchars($appointment['patient_first_name'] . ' ' . $appointment['patient_last_name'], ENT_QUOTES, 'UTF-8') ?>"
                                            type="button" title="Reschedule appointment">
                                            Reschedule
                                        </button>
                                        <button
                                            class="cancel-confirmed-btn inline-flex items-center justify-center rounded-full bg-red-500 px-3 py-2 text-xs font-semibold text-white transition hover:bg-red-700"
                                            data-appointment-id="<?= htmlspecialchars($appointment['AppointmentID'], ENT_QUOTES, 'UTF-8') ?>"
                                            type="button" title="Cancel appointment">
                                            Cancel
                                        </button>
                                    <?php endif; ?>
                                </div>
                            </div>
                            <?php
                        }
                    } else {
                        echo '<p class="text-sm text-slate-500">No confirmed appointments.</p>';
                    }
                    ?>
                </div>
            </div>
        </div>

        <div id="bookAppointmentModal"
            class="modal fixed inset-0 bg-black bg-opacity-40 items-center justify-center z-50 hidden"
            style="background-color: rgba(0,0,0,0.4);">
            <div class="bg-white rounded-xl shadow-lg w-full max-w-sm p-6 relative">
                <div class="flex items-center justify-between mb-4">
                    <div>
                        <h3 class="text-lg font-semibold">Book Appointment for Patient</h3>
                    </div>
                </div>
                <form id="addAppointmentForm" class="flex flex-col" method="POST" action="appointments.php">
                    <input type="hidden" name="csrf_token"
                        value="<?= htmlspecialchars($csrfToken, ENT_QUOTES, 'UTF-8') ?>">
                    <div class="mb-4 w-auto">
                        <label class="block text-gray-700 mb-1 text-sm">Patient ID</label>
                        <select
                            class="w-full border border-gray-300 bg-white rounded-lg px-4 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500"
                            name="patient_id" id="patient" required>
                            <option value="" aria-readonly="true">Please Select Patient ID</option>

                            <?php
                            try {
                                $stmt = $pdo->query("SELECT PatientCode, FirstName, LastName FROM patients ORDER BY CreatedAt DESC");
                                while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
                                    $patientCode = htmlspecialchars($row['PatientCode'], ENT_QUOTES, 'UTF-8');
                                    $firstName = htmlspecialchars($row['FirstName'], ENT_QUOTES, 'UTF-8');
                                    $lastName = htmlspecialchars($row['LastName'], ENT_QUOTES, 'UTF-8');
                                    echo "<option value=\"$patientCode\">$patientCode - $firstName $lastName</option>";
                                }
                            } catch (PDOException $e) {
                                echo "<option value=\"\">Error fetching patients</option>";
                            }
                            ?>

                        </select>
                    </div>
                    <div class="mb-4 w-auto">
                        <label class="block text-gray-700 mb-1 text-sm">Purpose</label>
                        <input type="text" name="purpose" required
                            class="w-full border border-gray-300 bg-white rounded-lg px-4 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500"
                            placeholder="Enter the purpose of the appointment">
                    </div>
                    <div class="mb-4 w-auto">
                        <label class="block text-gray-700 mb-1 text-sm">Chief Complaint</label>
                        <input type="text" name="chief_complaint" required
                            class="w-full border border-gray-300 bg-white rounded-lg px-4 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500"
                            placeholder="Enter the chief complaint">
                    </div>
                    <input type="hidden" name="appointment_date" id="selectedAppointmentDate" required>
                    <div class="mb-4 w-auto">
                        <label class="block text-gray-700 mb-1 text-sm">Selected Slot</label>
                        <input type="text" name="appointment_time" id="selectedAppointmentTime" required readonly
                            class="w-full border border-gray-300 bg-white rounded-lg px-4 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500"
                            placeholder="Pick a slot from the grid above">
                    </div>
                    <div class="flex w-full">
                        <button type="button"
                            class="close w-full cursor-pointer mr-2 px-4 py-2 bg-gray-300 rounded hover:bg-gray-400 text-sm">Cancel</button>
                        <button type="submit" name="submit" id="confirmBooking"
                            class="cursor-pointer w-full px-4 py-2 bg-blue-700 text-white rounded hover:bg-blue-900 text-sm">Confirm
                            Booking
                        </button>
                    </div>
                </form>
            </div>
        </div>

        <!-- Reschedule Appointment Modal -->
        <div id="rescheduleAppointmentModal"
            class="modal fixed inset-0 bg-black bg-opacity-40 items-center justify-center z-50 hidden"
            style="background-color: rgba(0,0,0,0.4);">
            <div class="bg-white rounded-2xl shadow-lg w-full max-w-md p-6 relative max-h-[90vh] overflow-y-auto">
                <div class="flex items-center justify-between mb-1">
                    <h3 class="text-lg font-semibold text-slate-900">Reschedule Appointment</h3>
                </div>
                <p class="text-sm text-slate-500 mb-4">
                    Moving <span id="rescheduleModalPatientName" class="font-semibold text-slate-700"></span>'s
                    appointment — currently
                    <span id="rescheduleModalCurrentSlot" class="font-semibold text-slate-700"></span>.
                </p>

                <form id="rescheduleAppointmentForm" class="flex flex-col">
                    <input type="hidden" name="csrf_token"
                        value="<?= htmlspecialchars($csrfToken, ENT_QUOTES, 'UTF-8') ?>">
                    <input type="hidden" name="appointment_id" id="rescheduleAppointmentId">

                    <div class="mb-4">
                        <label class="block text-gray-700 mb-1 text-sm">New Date</label>
                        <input type="date" name="new_appointment_date" id="rescheduleDate" required
                            class="w-full border border-gray-300 bg-white rounded-lg px-4 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-sky-500">
                    </div>

                    <div class="mb-2">
                        <label class="block text-gray-700 mb-2 text-sm">New Time</label>
                        <p id="rescheduleTimeMessage" class="text-xs text-slate-500 mb-3">Choose a date first to see
                            available slots.</p>

                        <div id="rescheduleTimeCalendar" class="hidden">
                            <div class="mb-2 flex items-center justify-between">
                                <h4 class="text-xs font-bold uppercase tracking-wide text-slate-500">Morning</h4>
                                <span class="text-xs text-slate-400">8:00 AM - 12:00 PM</span>
                            </div>
                            <div class="mb-4 grid grid-cols-3 gap-2" id="rescheduleMorningSlots">
                                <button type="button" data-time="08:00 AM"
                                    class="reschedule-time-slot rounded-2xl bg-slate-100 px-3 py-3 text-xs font-semibold text-slate-700 hover:bg-sky-100">08:00
                                    AM</button>
                                <button type="button" data-time="08:30 AM"
                                    class="reschedule-time-slot rounded-2xl bg-slate-100 px-3 py-3 text-xs font-semibold text-slate-700 hover:bg-sky-100">08:30
                                    AM</button>
                                <button type="button" data-time="09:00 AM"
                                    class="reschedule-time-slot rounded-2xl bg-slate-100 px-3 py-3 text-xs font-semibold text-slate-700 hover:bg-sky-100">09:00
                                    AM</button>
                                <button type="button" data-time="09:30 AM"
                                    class="reschedule-time-slot rounded-2xl bg-slate-100 px-3 py-3 text-xs font-semibold text-slate-700 hover:bg-sky-100">09:30
                                    AM</button>
                                <button type="button" data-time="10:00 AM"
                                    class="reschedule-time-slot rounded-2xl bg-slate-100 px-3 py-3 text-xs font-semibold text-slate-700 hover:bg-sky-100">10:00
                                    AM</button>
                                <button type="button" data-time="10:30 AM"
                                    class="reschedule-time-slot rounded-2xl bg-slate-100 px-3 py-3 text-xs font-semibold text-slate-700 hover:bg-sky-100">10:30
                                    AM</button>
                                <button type="button" data-time="11:00 AM"
                                    class="reschedule-time-slot rounded-2xl bg-slate-100 px-3 py-3 text-xs font-semibold text-slate-700 hover:bg-sky-100">11:00
                                    AM</button>
                                <button type="button" data-time="11:30 AM"
                                    class="reschedule-time-slot rounded-2xl bg-slate-100 px-3 py-3 text-xs font-semibold text-slate-700 hover:bg-sky-100">11:30
                                    AM</button>
                            </div>

                            <div class="mb-2 flex items-center justify-between">
                                <h4 class="text-xs font-bold uppercase tracking-wide text-slate-500">Afternoon</h4>
                                <span class="text-xs text-slate-400">2:00 PM - 5:00 PM</span>
                            </div>
                            <div class="grid grid-cols-3 gap-2" id="rescheduleAfternoonSlots">
                                <button type="button" data-time="02:00 PM"
                                    class="reschedule-time-slot rounded-2xl bg-slate-100 px-3 py-3 text-xs font-semibold text-slate-700 hover:bg-sky-100">02:00
                                    PM</button>
                                <button type="button" data-time="02:30 PM"
                                    class="reschedule-time-slot rounded-2xl bg-slate-100 px-3 py-3 text-xs font-semibold text-slate-700 hover:bg-sky-100">02:30
                                    PM</button>
                                <button type="button" data-time="03:00 PM"
                                    class="reschedule-time-slot rounded-2xl bg-slate-100 px-3 py-3 text-xs font-semibold text-slate-700 hover:bg-sky-100">03:00
                                    PM</button>
                                <button type="button" data-time="03:30 PM"
                                    class="reschedule-time-slot rounded-2xl bg-slate-100 px-3 py-3 text-xs font-semibold text-slate-700 hover:bg-sky-100">03:30
                                    PM</button>
                                <button type="button" data-time="04:00 PM"
                                    class="reschedule-time-slot rounded-2xl bg-slate-100 px-3 py-3 text-xs font-semibold text-slate-700 hover:bg-sky-100">04:00
                                    PM</button>
                                <button type="button" data-time="04:30 PM"
                                    class="reschedule-time-slot rounded-2xl bg-slate-100 px-3 py-3 text-xs font-semibold text-slate-700 hover:bg-sky-100">04:30
                                    PM</button>
                            </div>
                        </div>
                    </div>

                    <input type="hidden" name="new_appointment_time" id="rescheduleSelectedTime" required>

                    <div class="flex w-full mt-4">
                        <button type="button" class="close w-full cursor-pointer mr-2 px-4 py-2 bg-gray-300 rounded hover:bg-gray-400 text-sm">Cancel</button>
                        <button type="submit" id="confirmReschedule"
                            class="cursor-pointer w-full px-4 py-2 bg-sky-600 text-white rounded hover:bg-sky-800 text-sm">
                            Confirm New Schedule
                        </button>
                    </div>
                </form>
            </div>
        </div>

        <?php include '../includes/message-modal.php' ?>
    </section>

    <!-- Leaflet map library -->
    <script>
        window.csrfToken = <?= json_encode($csrfToken, JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_AMP | JSON_HEX_QUOT) ?>;
        window.patientMapData = <?= json_encode($patientsWithCoordinates, JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_AMP | JSON_HEX_QUOT) ?>;
    </script>
    <script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"></script>
    <script src="../assets/javascript/mapping.js"></script>
    <script src="../assets/javascript/appointment.js"></script>
    <script src="../assets/javascript/reschedule.js"></script>
</body>

</html>