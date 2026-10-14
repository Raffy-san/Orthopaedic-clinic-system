<?php
require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/../includes/auth.php';

SessionManager::requireLogin();
SessionManager::requireAnyRole(['staff']);

$admin = SessionManager::getUser($pdo);
if (!$admin) {
    SessionManager::logout('../index.php');
}

function directoryEscape(mixed $value): string
{
    return htmlspecialchars((string) ($value ?? ''), ENT_QUOTES, 'UTF-8');
}

function directoryDate(mixed $value, string $format = 'M j, Y'): string
{
    if (!$value) {
        return 'Not provided';
    }

    try {
        $date = new DateTimeImmutable((string) $value);
        return $date->format($format);
    } catch (Exception) {
        return 'Not provided';
    }
}

function validDirectoryDate(string $value): bool
{
    $date = DateTimeImmutable::createFromFormat('!Y-m-d', $value);
    return $date !== false && $date->format('Y-m-d') === $value;
}

$search = trim((string) ($_GET['search'] ?? ''));
$patientType = trim((string) ($_GET['patient_type'] ?? ''));
$gender = trim((string) ($_GET['gender'] ?? ''));
$status = trim((string) ($_GET['status'] ?? ''));
$doctorId = trim((string) ($_GET['doctor_id'] ?? ''));
$registeredFrom = trim((string) ($_GET['registered_from'] ?? ''));
$registeredTo = trim((string) ($_GET['registered_to'] ?? ''));
$selectedPatientCode = trim((string) ($_GET['patient'] ?? ''));

$patientTypes = ['Regular', 'Senior Citizen', 'PWD'];
$genders = ['Male', 'Female', 'Other'];
$statuses = ['Active', 'Inactive', 'For follow-up'];

if (!in_array($patientType, $patientTypes, true)) {
    $patientType = '';
}
if (!in_array($gender, $genders, true)) {
    $gender = '';
}
if (!in_array($status, $statuses, true)) {
    $status = '';
}
if ($doctorId !== '' && !ctype_digit($doctorId)) {
    $doctorId = '';
}
if (!validDirectoryDate($registeredFrom)) {
    $registeredFrom = '';
}
if (!validDirectoryDate($registeredTo)) {
    $registeredTo = '';
}
if ($registeredFrom !== '' && $registeredTo !== '' && $registeredFrom > $registeredTo) {
    [$registeredFrom, $registeredTo] = [$registeredTo, $registeredFrom];
}

$doctors = [];
$patients = [];
$totalPatientCount = 0;
$pageError = null;

try {
    $totalPatientCount = (int) $pdo->query('SELECT COUNT(*) FROM patients')->fetchColumn();
    $doctorStatement = $pdo->query(
        'SELECT UserID, FirstName, LastName
         FROM users
         WHERE IsDoctor = 1
         ORDER BY LastName, FirstName'
    );
    $doctors = $doctorStatement->fetchAll(PDO::FETCH_ASSOC);

    $conditions = [];
    $params = [];

    if ($search !== '') {
        $conditions[] = '(directory.PatientName LIKE ? OR directory.PatientCode LIKE ?
            OR directory.Phone LIKE ? OR directory.Email LIKE ? OR directory.PatientAddress LIKE ?)';
        $searchTerm = '%' . $search . '%';
        array_push($params, $searchTerm, $searchTerm, $searchTerm, $searchTerm, $searchTerm);
    }
    if ($patientType !== '') {
        $conditions[] = 'directory.PatientType = ?';
        $params[] = $patientType;
    }
    if ($gender !== '') {
        $conditions[] = 'directory.Gender = ?';
        $params[] = $gender;
    }
    if ($status !== '') {
        $conditions[] = 'directory.PatientStatus = ?';
        $params[] = $status;
    }
    if ($doctorId !== '') {
        $conditions[] = 'directory.AssignedDoctorID = ?';
        $params[] = (int) $doctorId;
    }
    if ($registeredFrom !== '') {
        $conditions[] = 'DATE(directory.CreatedAt) >= ?';
        $params[] = $registeredFrom;
    }
    if ($registeredTo !== '') {
        $conditions[] = 'DATE(directory.CreatedAt) <= ?';
        $params[] = $registeredTo;
    }

    $where = $conditions ? 'WHERE ' . implode(' AND ', $conditions) : '';
    $statement = $pdo->prepare(
        "SELECT directory.*
         FROM (
             SELECT p.PatientID, p.PatientCode, p.FirstName, p.MiddleName, p.LastName,
                    CONCAT_WS(' ', p.FirstName, NULLIF(p.MiddleName, ''), p.LastName) AS PatientName,
                    p.BirthDate, TIMESTAMPDIFF(YEAR, p.BirthDate, CURDATE()) AS Age,
                    p.Gender, p.Address, p.Province, p.City, p.Barangay,
                    p.Phone, p.Email,
                    p.PatientType, p.CreatedAt,
                    COALESCE(NULLIF(p.Address, ''),
                        CONCAT_WS(', ', NULLIF(p.Barangay, ''), NULLIF(p.City, ''), NULLIF(p.Province, ''))
                    ) AS PatientAddress,
                    (SELECT COUNT(*)
                     FROM consultations visit_count
                     WHERE visit_count.PatientID = p.PatientID
                       AND visit_count.IsCompleted = 1) AS VisitCount,
                    (SELECT visit_doctor.DoctorID
                     FROM consultations visit_doctor
                     WHERE visit_doctor.PatientID = p.PatientID
                       AND visit_doctor.IsCompleted = 1
                     ORDER BY visit_doctor.ConsultationDate DESC, visit_doctor.ConsultationID DESC
                     LIMIT 1) AS AssignedDoctorID,
                    (SELECT CONCAT('Dr. ', doctor.FirstName, ' ', doctor.LastName)
                     FROM consultations doctor_visit
                     INNER JOIN users doctor ON doctor.UserID = doctor_visit.DoctorID
                     WHERE doctor_visit.PatientID = p.PatientID
                       AND doctor_visit.IsCompleted = 1
                     ORDER BY doctor_visit.ConsultationDate DESC, doctor_visit.ConsultationID DESC
                     LIMIT 1) AS AssignedDoctor,
                    (SELECT latest_visit.Diagnosis
                     FROM consultations latest_visit
                     WHERE latest_visit.PatientID = p.PatientID
                       AND latest_visit.IsCompleted = 1
                     ORDER BY latest_visit.ConsultationDate DESC, latest_visit.ConsultationID DESC
                     LIMIT 1) AS LatestDiagnosis,
                    (SELECT next_followup.FollowUpDate
                     FROM followups next_followup
                     WHERE next_followup.PatientID = p.PatientID
                       AND next_followup.Status = 'Scheduled'
                       AND next_followup.FollowUpDate >= CURDATE()
                     ORDER BY next_followup.FollowUpDate ASC, next_followup.FollowUpID ASC
                     LIMIT 1) AS NextFollowUp,
                    CASE
                        WHEN EXISTS (
                            SELECT 1
                            FROM followups scheduled_followup
                            WHERE scheduled_followup.PatientID = p.PatientID
                              AND scheduled_followup.Status = 'Scheduled'
                              AND scheduled_followup.FollowUpDate >= CURDATE()
                        ) THEN 'For follow-up'
                        WHEN EXISTS (
                            SELECT 1
                            FROM consultations previous_visit
                            WHERE previous_visit.PatientID = p.PatientID
                              AND previous_visit.IsCompleted = 1
                        ) THEN 'Active'
                        ELSE 'Inactive'
                    END AS PatientStatus
             FROM patients p
         ) AS directory
         $where
         ORDER BY directory.LastName, directory.FirstName, directory.PatientID"
    );
    $statement->execute($params);
    $patients = $statement->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $exception) {
    error_log('Patient directory query failed: ' . $exception->getMessage());
    $pageError = 'Unable to load the patient directory. Please try again.';
}

$selectedPatient = null;
if ($patients) {
    foreach ($patients as $patient) {
        if ($patient['PatientCode'] === $selectedPatientCode) {
            $selectedPatient = $patient;
            break;
        }
    }
    $selectedPatient ??= $patients[0];
}

$statusClasses = [
    'Active' => 'bg-emerald-100 text-emerald-700',
    'Inactive' => 'bg-slate-100 text-slate-600',
    'For follow-up' => 'bg-orange-100 text-orange-700',
];
$patientName = $selectedPatient
    ? trim(implode(' ', array_filter([
        $selectedPatient['FirstName'],
        $selectedPatient['MiddleName'],
        $selectedPatient['LastName'],
    ], static fn($part): bool => trim((string) $part) !== '')))
    : '';
$patientAddress = $selectedPatient
    ? trim((string) ($selectedPatient['PatientAddress'] ?? ''))
    : '';
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="../assets/css/output.css">
    <link rel="stylesheet" href="../assets/css/global.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
    <link rel="icon" href="../assets/img/rounded-logo.ico" type="image/x-icon">
    <title>Patient Directory</title>
</head>

<body class="h-screen flex bg-slate-100 overflow-hidden">
    <?php include_once '../includes/sidebar.php'; ?>

    <main class="flex-1 min-w-0 min-h-0 flex flex-col overflow-hidden">
        <header class="flex items-center justify-between bg-gradient-to-r from-[#0b1f0b] via-[#1e6b34] to-[#2e8b47] px-6 py-4">
            <h1 class="text-xl font-bold text-white">Southern Leyte Orthopaedic Clinic System</h1>
            <div class="text-right">
                <h2 class="text-xl font-bold text-white">Patient Directory</h2>
                <p class="text-sm text-white/80">Search and review registered patient information</p>
            </div>
        </header>

        <div class="flex-1 min-h-0 overflow-auto p-4 sm:p-6 space-y-5">
            <div class="flex items-center justify-between">
                <div>
                    <h2 class="text-xl font-bold text-slate-800">Patient Directory</h2>
                    <p class="text-sm text-slate-500">Search and review all registered patient information</p>
                </div>
                <span class="rounded-md bg-blue-100 px-3 py-1 text-xs font-medium text-blue-700">
                    <?= $totalPatientCount ?> registered <?= $totalPatientCount === 1 ? 'patient' : 'patients' ?>
                </span>
            </div>

            <section class="rounded-2xl border border-slate-200 bg-white p-5 shadow-sm">
                <h3 class="text-sm font-bold text-slate-800">Advanced patient search</h3>
                <p class="mt-1 text-xs text-slate-500">
                    Search contact details, demographics, registration date, or assigned doctor
                </p>
                <form method="get" class="mt-4 grid grid-cols-1 gap-3 sm:grid-cols-2 xl:grid-cols-4">
                    <div class="xl:col-span-2">
                        <label for="directory-search" class="mb-1 block text-xs font-medium text-slate-600">
                            Name, patient ID, phone, email, or address
                        </label>
                        <input id="directory-search" name="search" type="search"
                            value="<?= directoryEscape($search) ?>"
                            placeholder="Search patient information..."
                            class="w-full rounded-lg border border-slate-200 px-3 py-2 text-sm focus:border-teal-500 focus:outline-none focus:ring-2 focus:ring-teal-100">
                    </div>
                    <div>
                        <label for="patient-type" class="mb-1 block text-xs font-medium text-slate-600">Patient type</label>
                        <select id="patient-type" name="patient_type"
                            class="w-full rounded-lg border border-slate-200 bg-white px-3 py-2 text-sm focus:border-teal-500 focus:outline-none">
                            <option value="">All patient types</option>
                            <?php foreach ($patientTypes as $option): ?>
                                <option value="<?= directoryEscape($option) ?>" <?= $patientType === $option ? 'selected' : '' ?>>
                                    <?= directoryEscape($option) ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div>
                        <label for="doctor" class="mb-1 block text-xs font-medium text-slate-600">Assigned doctor</label>
                        <select id="doctor" name="doctor_id"
                            class="w-full rounded-lg border border-slate-200 bg-white px-3 py-2 text-sm focus:border-teal-500 focus:outline-none">
                            <option value="">All doctors</option>
                            <?php foreach ($doctors as $doctor): ?>
                                <option value="<?= (int) $doctor['UserID'] ?>"
                                    <?= $doctorId === (string) $doctor['UserID'] ? 'selected' : '' ?>>
                                    <?= directoryEscape('Dr. ' . $doctor['FirstName'] . ' ' . $doctor['LastName']) ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div>
                        <label for="gender" class="mb-1 block text-xs font-medium text-slate-600">Gender</label>
                        <select id="gender" name="gender"
                            class="w-full rounded-lg border border-slate-200 bg-white px-3 py-2 text-sm focus:border-teal-500 focus:outline-none">
                            <option value="">All genders</option>
                            <?php foreach ($genders as $option): ?>
                                <option value="<?= directoryEscape($option) ?>" <?= $gender === $option ? 'selected' : '' ?>>
                                    <?= directoryEscape($option) ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div>
                        <label for="status" class="mb-1 block text-xs font-medium text-slate-600">Patient status</label>
                        <select id="status" name="status"
                            class="w-full rounded-lg border border-slate-200 bg-white px-3 py-2 text-sm focus:border-teal-500 focus:outline-none">
                            <option value="">All statuses</option>
                            <?php foreach ($statuses as $option): ?>
                                <option value="<?= directoryEscape($option) ?>" <?= $status === $option ? 'selected' : '' ?>>
                                    <?= directoryEscape($option) ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div>
                        <label for="registered-from" class="mb-1 block text-xs font-medium text-slate-600">Registered from</label>
                        <input id="registered-from" name="registered_from" type="date"
                            value="<?= directoryEscape($registeredFrom) ?>"
                            class="w-full rounded-lg border border-slate-200 px-3 py-2 text-sm focus:border-teal-500 focus:outline-none">
                    </div>
                    <div>
                        <label for="registered-to" class="mb-1 block text-xs font-medium text-slate-600">Registered to</label>
                        <input id="registered-to" name="registered_to" type="date"
                            value="<?= directoryEscape($registeredTo) ?>"
                            class="w-full rounded-lg border border-slate-200 px-3 py-2 text-sm focus:border-teal-500 focus:outline-none">
                    </div>
                    <?php if ($selectedPatient): ?>
                        <input type="hidden" name="patient" value="<?= directoryEscape($selectedPatient['PatientCode']) ?>">
                    <?php endif; ?>
                    <div class="flex items-end gap-2">
                        <button type="submit"
                            class="rounded-lg bg-teal-600 px-4 py-2 text-sm font-semibold text-white hover:bg-teal-700">
                            Search
                        </button>
                        <a href="patient-directory.php"
                            class="rounded-lg border border-slate-200 px-4 py-2 text-sm font-semibold text-slate-600 hover:bg-slate-50">
                            Clear
                        </a>
                    </div>
                </form>
                <p class="mt-3 text-xs text-slate-400">
                    <?= count($patients) ?> of <?= $totalPatientCount ?> patients found
                </p>
            </section>

            <?php if ($pageError): ?>
                <div role="alert" class="rounded-xl border border-red-200 bg-red-50 p-4 text-sm text-red-700">
                    <?= directoryEscape($pageError) ?>
                </div>
            <?php else: ?>
                <div class="grid min-w-0 grid-cols-1 gap-4 xl:grid-cols-[1.35fr_0.9fr]">
                    <section class="min-w-0 overflow-hidden rounded-2xl border border-slate-200 bg-white shadow-sm">
                        <div class="border-b border-slate-100 px-5 py-4">
                            <h3 class="text-sm font-bold text-slate-800">Registered patients</h3>
                        </div>
                        <?php if (!$patients): ?>
                            <p class="p-8 text-center text-sm text-slate-500">No patients found for these filters.</p>
                        <?php else: ?>
                            <div class="max-h-[32rem] overflow-auto custom-scrollbar">
                                <table class="w-full min-w-[400px] text-left text-xs">
                                    <thead class="sticky top-0 z-10 bg-slate-50 text-slate-400">
                                        <tr>
                                            <th class="px-4 py-3 font-semibold">Patient</th>
                                            <th class="px-3 py-3 font-semibold">Doctor</th>
                                            <th class="px-3 py-3 font-semibold">Registered</th>
                                            <th class="px-3 py-3 font-semibold">Status</th>
                                        </tr>
                                    </thead>
                                    <tbody class="divide-y divide-slate-100">
                                        <?php foreach ($patients as $patient): ?>
                                            <?php
                                            $isSelected = $selectedPatient
                                                && $selectedPatient['PatientCode'] === $patient['PatientCode'];
                                            $rowQuery = $_GET;
                                            $rowQuery['patient'] = $patient['PatientCode'];
                                            ?>
                                            <tr
                                                class="cursor-pointer <?= $isSelected ? 'bg-blue-50' : 'hover:bg-slate-50' ?> focus:outline-none focus-visible:ring-2 focus-visible:ring-inset focus-visible:ring-teal-500"
                                                data-patient-href="?<?= directoryEscape(http_build_query($rowQuery)) ?>"
                                                role="link"
                                                tabindex="0"
                                                aria-current="<?= $isSelected ? 'page' : 'false' ?>">
                                                <td class="px-4 py-3">
                                                    <a href="?<?= directoryEscape(http_build_query($rowQuery)) ?>"
                                                        class="font-semibold text-slate-800 hover:text-teal-700">
                                                        <?= directoryEscape(trim($patient['FirstName'] . ' ' . $patient['LastName'])) ?>
                                                    </a>
                                                    <div class="mt-1 text-[11px] text-slate-400">
                                                        <?= directoryEscape($patient['PatientCode']) ?>
                                                    </div>
                                                </td>
                                                <td class="px-3 py-3 text-slate-600"><?= directoryEscape($patient['AssignedDoctor'] ?: '—') ?></td>
                                                <td class="px-3 py-3 text-slate-600"><?= directoryEscape(directoryDate($patient['CreatedAt'])) ?></td>
                                                <td class="px-3 py-3">
                                                    <span class="inline-flex rounded-md px-2 py-1 text-[10px] font-semibold <?= $statusClasses[$patient['PatientStatus']] ?? $statusClasses['Inactive'] ?>">
                                                        <?= directoryEscape($patient['PatientStatus']) ?>
                                                    </span>
                                                </td>
                                            </tr>
                                        <?php endforeach; ?>
                                    </tbody>
                                </table>
                            </div>
                        <?php endif; ?>
                    </section>

                    <aside class="min-w-0 self-start rounded-2xl border border-slate-200 bg-white p-5 shadow-sm">
                        <?php if (!$selectedPatient): ?>
                            <div class="py-12 text-center text-sm text-slate-500">
                                Select a patient to review their information.
                            </div>
                        <?php else: ?>
                            <div class="flex items-center gap-3 border-b border-slate-100 pb-4">
                                <div class="flex h-12 w-12 shrink-0 items-center justify-center rounded-xl bg-teal-50 text-sm font-bold text-teal-700">
                                    <?= directoryEscape(strtoupper(substr($selectedPatient['FirstName'], 0, 1) . substr($selectedPatient['LastName'], 0, 1))) ?>
                                </div>
                                <div class="min-w-0">
                                    <h3 class="truncate text-base font-bold text-slate-800"><?= directoryEscape($patientName) ?></h3>
                                    <p class="mt-0.5 text-xs text-slate-400"><?= directoryEscape($selectedPatient['PatientCode']) ?></p>
                                    <span class="mt-1 inline-flex rounded-md bg-blue-50 px-2 py-1 text-[10px] font-semibold text-blue-700">
                                        <?= directoryEscape($selectedPatient['PatientType']) ?>
                                    </span>
                                </div>
                            </div>

                            <div class="grid grid-cols-3 gap-2 border-b border-slate-100 py-4 text-center">
                                <div class="rounded-xl bg-slate-50 px-2 py-3">
                                    <p class="text-lg font-bold text-slate-800"><?= directoryEscape($selectedPatient['Age']) ?></p>
                                    <p class="text-[11px] text-slate-400">Age</p>
                                </div>
                                <div class="rounded-xl bg-slate-50 px-2 py-3">
                                    <p class="text-lg font-bold text-slate-800"><?= (int) $selectedPatient['VisitCount'] ?></p>
                                    <p class="text-[11px] text-slate-400">Visits</p>
                                </div>
                                <div class="rounded-xl bg-slate-50 px-2 py-3">
                                    <p class="text-md font-bold text-slate-800"><?= directoryEscape($selectedPatient['PatientStatus']) ?></p>
                                    <p class="text-[11px] text-slate-400">Status</p>
                                </div>
                            </div>

                            <section class="border-b border-slate-100 py-4">
                                <h4 class="mb-3 text-xs font-bold text-slate-700">Patient information</h4>
                                <dl class="grid grid-cols-1 gap-x-4 gap-y-3 text-xs sm:grid-cols-2">
                                    <div>
                                        <dt class="text-slate-400">Birth date</dt>
                                        <dd class="mt-1 font-medium text-slate-700"><?= directoryEscape(directoryDate($selectedPatient['BirthDate'])) ?></dd>
                                    </div>
                                    <div>
                                        <dt class="text-slate-400">Gender</dt>
                                        <dd class="mt-1 font-medium text-slate-700"><?= directoryEscape($selectedPatient['Gender'] ?: 'Not provided') ?></dd>
                                    </div>
                                    <div>
                                        <dt class="text-slate-400">Phone</dt>
                                        <dd class="mt-1 break-words font-medium text-slate-700"><?= directoryEscape($selectedPatient['Phone'] ?: 'Not provided') ?></dd>
                                    </div>
                                    <div class="sm:col-span-2">
                                        <dt class="text-slate-400">Email</dt>
                                        <dd class="mt-1 break-words font-medium text-slate-700"><?= directoryEscape($selectedPatient['Email'] ?: 'Not provided') ?></dd>
                                    </div>
                                    <div class="sm:col-span-2">
                                        <dt class="text-slate-400">Address</dt>
                                        <dd class="mt-1 font-medium text-slate-700"><?= directoryEscape($patientAddress ?: 'Not provided') ?></dd>
                                    </div>
                                    <div>
                                        <dt class="text-slate-400">Assigned doctor</dt>
                                        <dd class="mt-1 font-medium text-slate-700"><?= directoryEscape($selectedPatient['AssignedDoctor'] ?: 'Not assigned') ?></dd>
                                    </div>
                                    <div>
                                        <dt class="text-slate-400">Registered</dt>
                                        <dd class="mt-1 font-medium text-slate-700"><?= directoryEscape(directoryDate($selectedPatient['CreatedAt'])) ?></dd>
                                    </div>
                                    <?php if ($selectedPatient['NextFollowUp']): ?>
                                        <div>
                                            <dt class="text-slate-400">Next follow-up</dt>
                                            <dd class="mt-1 font-medium text-slate-700"><?= directoryEscape(directoryDate($selectedPatient['NextFollowUp'])) ?></dd>
                                        </div>
                                    <?php endif; ?>
                                </dl>
                            </section>

                            <div class="flex items-start justify-between gap-3 pt-4">
                                <div class="min-w-0">
                                    <h4 class="text-xs font-bold text-slate-700">Latest medical record</h4>
                                    <p class="mt-1 break-words text-xs text-slate-500">
                                        <?= directoryEscape($selectedPatient['LatestDiagnosis'] ?: 'No medical records yet') ?>
                                    </p>
                                </div>
                                <span class="shrink-0 rounded-md bg-teal-50 px-2 py-1 text-[10px] font-semibold text-teal-700">
                                    <?= (int) $selectedPatient['VisitCount'] ?> <?= (int) $selectedPatient['VisitCount'] === 1 ? 'visit' : 'visits' ?>
                                </span>
                            </div>
                        <?php endif; ?>
                    </aside>
                </div>
            <?php endif; ?>
        </div>
    </main>
<script>
    document.querySelectorAll('[data-patient-href]').forEach((row) => {
        row.addEventListener('click', (event) => {
            if (event.target.closest('a, button, input, select, textarea')) {
                return;
            }

            window.location.href = row.dataset.patientHref;
        });

        row.addEventListener('keydown', (event) => {
            if (event.key === 'Enter' || event.key === ' ') {
                event.preventDefault();
                window.location.href = row.dataset.patientHref;
            }
        });
    });
</script>
</body>
</html>