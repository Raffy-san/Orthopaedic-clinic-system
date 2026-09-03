<?php
include_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/../includes/auth.php';
require_once __DIR__ . '/../php/fetch/fetch.php';
SessionManager::requireLogin();

SessionManager::requireRole('patient');

$patient = SessionManager::getUser($pdo);

if (!$patient) {
    SessionManager::logout('../index.php'); // Force logout if user not found
}

$patientStatement = $pdo->prepare('SELECT PatientID FROM patients WHERE UserID = ?');
$patientStatement->execute([$patient['user_id']]);
$patientId = $patientStatement->fetchColumn();

$appointments = [];

if ($patientId) {
    $appointmentStatement = $pdo->prepare(
        "SELECT a.AppointmentID,
                a.AppointmentDate,
                a.AppointmentTime,
                a.meridiem,
                a.Purpose,
                a.Status,
                CONCAT(u.FirstName, ' ', u.LastName) AS doctor_name
         FROM appointments a
         LEFT JOIN users u ON u.UserID = a.DoctorID
         WHERE a.PatientID = ?
           AND a.Status <> 'Cancelled'
         ORDER BY a.AppointmentDate DESC, a.AppointmentTime DESC"
    );
    $appointmentStatement->execute([$patientId]);
    $appointments = $appointmentStatement->fetchAll(PDO::FETCH_ASSOC);
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
    <link rel="icon" href="../assets/img/rounded-logo.ico" type="image/x-icon">
    <title>My Appointments</title>
</head>

<body class="h-screen flex bg-slate-200">
    <?php include '../includes/user-sidebar.php'; ?>

    <section class="flex-1 p-6 overflow-auto">
        <div>
            <h1 class="text-2xl font-bold">My Appointments</h1>
            <h3 class="text-md font-medium text-gray-500">
                Scheduled and past clinic visits
            </h3>
        </div>

        <div class="w-full gap-4 mt-6">
            <?php if (empty($appointments)): ?>
                <div
                    class="rounded-2xl border border-slate-200 bg-white/80 px-6 py-12 text-center text-slate-500 shadow-sm">
                    No appointments found.
                </div>
            <?php else: ?>
                <div class="flex flex-col gap-4">
                    <?php foreach ($appointments as $appointment): ?>
                        <?php
                        $date = new DateTime($appointment['AppointmentDate']);
                        $month = date('M', $date->getTimestamp());
                        $day = date('d', $date->getTimestamp());
                        $year = date('Y', $date->getTimestamp());
                        $time = date('h:i', strtotime($appointment['AppointmentTime']));
                        $status = $appointment['Status'];

                        $statusClass = match ($status) {
                            'Confirmed' => 'border-emerald-200 bg-emerald-100 text-emerald-700',
                            'Pending' => 'border-orange-200 bg-orange-100 text-orange-700',
                            'Completed' => 'border-slate-200 bg-slate-100 text-slate-600',
                            default => 'border-slate-200 bg-slate-100 text-slate-600',
                        };
                        ?>

                        <article
                            class="flex min-h-[118px] items-center rounded-2xl border border-slate-200 bg-white/70 px-6 py-5 shadow-md">
                            <div
                                class="mr-5 flex min-w-[90px] flex-col items-center justify-center text-center leading-tight text-slate-600 border-r border-slate-300">
                                <div class="text-[0.8rem] font-semibold uppercase tracking-wide text-slate-500">
                                    <?= htmlspecialchars($month) ?>
                                </div>
                                <div class="my-1 text-[2.2rem] font-bold leading-none text-slate-800">
                                    <?= htmlspecialchars($day) ?>
                                </div>
                                <div class="text-[0.8rem] font-semibold uppercase tracking-wide text-slate-500">
                                    <?= htmlspecialchars($year) ?>
                                </div>
                            </div>

                            <div class="flex flex-1 flex-col justify-center">
                                <h2 class="m-0 text-[1.3rem] font-bold leading-tight text-slate-800">
                                    <?= htmlspecialchars($appointment['Purpose']) ?>
                                </h2>
                                <time class="text-base text-[1rem] text-slate-600"><?= htmlspecialchars($time) ?>
                                    <?= htmlspecialchars($appointment['meridiem']) ?> </time>
                            </div>

                            <div class="ml-auto flex min-w-[140px] justify-end">
                                <span
                                    class="inline-flex min-w-[104px] items-center justify-center rounded-xl border px-3 py-2 text-[0.92rem] font-semibold <?= htmlspecialchars($statusClass) ?>"><?= htmlspecialchars($status) ?></span>
                            </div>
                        </article>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>
        </div>
    </section>
</body>

</html>