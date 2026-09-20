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

$consultationRecords = [];

if ($patientId) {
    $consultationStatement = $pdo->prepare(
        "SELECT c.consultationID,
                c.Diagnosis,
                c.Treatment,
                c.IsCompleted,
                c.ConsultationDate,
                p.Medicine,
                p.Dosage,
                p.Frequency,
                p.Duration,
                p.Instructions,
                CONCAT(u.FirstName, ' ', u.LastName) AS doctor_name
         FROM consultations c
         LEFT JOIN users u ON u.UserID = c.DoctorID
         LEFT JOIN prescriptions p ON p.ConsultationID = c.consultationID
         WHERE c.PatientID = ?
            ORDER BY c.ConsultationDate DESC"
    );
    $consultationStatement->execute([$patientId]);
    $consultationRecords = $consultationStatement->fetchAll(PDO::FETCH_ASSOC);
}

// Helper to pick a badge color based on status label
function statusBadgeClass($status)
{
    switch (strtolower($status ?? '')) {
        case 'completed':
            return 'bg-emerald-100 text-emerald-700';
        case 'pending':
            return 'bg-amber-100 text-amber-700';
        case 'cancelled':
            return 'bg-rose-100 text-rose-700';
        default:
            return 'bg-slate-100 text-slate-600';
    }
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
    <title>My Records</title>
</head>

<body class="h-screen flex bg-slate-200 overflow-hidden">
    <?php include '../includes/user-sidebar.php'; ?>

    <section class="flex-1 min-h-0 flex flex-col overflow-hidden">
        <div
            class="flex items-center justify-between bg-gradient-to-r from-[#0b1f0b] via-[#1e6b34] to-[#2e8b47] px-6 py-4">
            <h1 class="text-2xl font-bold text-white">Southern Leyte Orthopaedic Clinic System</h1>
            <div class="flex flex-col gap-3 items-end">
                <h1 class="text-2xl font-bold text-white">My Records</h1>
                <h3 class="text-sm font-medium text-white">Consultation history and prescriptions</h3>
            </div>
        </div>

        <div class="flex-1 min-h-0 overflow-auto p-6 space-y-4">
            <?php if (empty($consultationRecords)): ?>
                <div
                    class="rounded-2xl border border-slate-200 bg-white/80 px-6 py-12 text-center text-slate-500 shadow-sm">
                    No Records found.
                </div>
            <?php else: ?>

                <div class="flex flex-col gap-4">
                    <?php foreach ($consultationRecords as $record): ?>
                        <?php $statusLabel = $record['IsCompleted'] ? 'Completed' : 'Pending'; ?>
                        <div class="rounded-2xl border border-slate-200 bg-white px-6 py-5 shadow-sm">

                            <div class="flex justify-between items-start mb-4">
                                <div>
                                    <h4 class="text-lg font-semibold text-slate-800">
                                        <?php
                                        $ts = strtotime($record['ConsultationDate']);
                                        echo $ts ? htmlspecialchars(date('M j, Y', $ts)) : htmlspecialchars($record['ConsultationDate']);
                                        ?>
                                    </h4>
                                    <p class="text-sm text-slate-400">
                                        Dr. <?php echo htmlspecialchars($record['doctor_name'] ?? 'N/A'); ?>
                                    </p>
                                </div>
                                <span
                                    class="text-xs font-medium px-3 py-1 rounded-full <?php echo statusBadgeClass($statusLabel); ?>">
                                    <?php echo htmlspecialchars($statusLabel); ?>
                                </span>
                            </div>

                            <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                                <div class="bg-slate-50 rounded-xl px-4 py-3">
                                    <p class="text-xs font-medium text-slate-400 mb-1">Diagnosis</p>
                                    <p class="text-sm text-slate-700">
                                        <?php echo htmlspecialchars($record['Diagnosis'] ?? '—'); ?>
                                    </p>
                                </div>

                                <div class="bg-slate-50 rounded-xl px-4 py-3">
                                    <p class="text-xs font-medium text-slate-400 mb-1">Treatment</p>
                                    <p class="text-sm text-slate-700">
                                        <?php echo htmlspecialchars($record['Treatment'] ?? '—'); ?>
                                    </p>
                                </div>

                                <div class="bg-slate-50 rounded-xl px-4 py-3">
                                    <p class="text-xs font-medium text-slate-400 mb-1">Prescription</p>
                                    <p class="text-sm text-slate-700">
                                        <?php if (!empty($record['Medicine'])): ?>
                                            <?php echo htmlspecialchars($record['Medicine']); ?>
                                            <?php echo $record['Dosage'] ? ' · ' . htmlspecialchars($record['Dosage']) : ''; ?>
                                            <?php echo $record['Frequency'] ? ' · ' . htmlspecialchars($record['Frequency']) : ''; ?>
                                            <?php echo $record['Duration'] ? ' for ' . htmlspecialchars($record['Duration']) : ''; ?>
                                        <?php else: ?>
                                            None prescribed
                                        <?php endif; ?>
                                    </p>
                                </div>
                            </div>

                            <?php if (!empty($record['Instructions'])): ?>
                                <p class="text-sm text-slate-500 mt-3">
                                    <strong class="text-slate-600">Instructions:</strong>
                                    <?php echo htmlspecialchars($record['Instructions']); ?>
                                </p>
                            <?php endif; ?>

                        </div>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>

        </div>
    </section>
</body>

</html>