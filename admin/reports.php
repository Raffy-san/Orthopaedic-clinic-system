<?php
require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/../includes/auth.php';
require_once __DIR__ . '/../php/fetch/fetch.php';
SessionManager::requireAdmin();
SessionManager::requireLogin();
SessionManager::requireAnyRole(['admin', 'doctor']);

$admin = SessionManager::getUser($pdo);

if (!$admin) {
    SessionManager::logout('../index.php');
}

$csrfToken = $_SESSION['csrf_token'] ?? SessionManager::regenerateCsrfToken();

$reportTypes = [
    'patients' => 'Patient Records Report',
    'financial' => 'Financial Summary',
    'appointments' => 'Appointments Report',
    'consultations' => 'Consultation Log'
];
$reportType = $_GET['report_type'] ?? 'patients';
$reportType = array_key_exists($reportType, $reportTypes) ? $reportType : 'patients';
$fromDate = $_GET['from_date'] ?? date('Y-m-01');
$toDate = $_GET['to_date'] ?? date('Y-m-d');

if (!preg_match('/^\d{4}-\d{2}-\d{2}$/', $fromDate)) {
    $fromDate = date('Y-m-01');
}
if (!preg_match('/^\d{4}-\d{2}-\d{2}$/', $toDate)) {
    $toDate = date('Y-m-d');
}
if ($fromDate > $toDate) {
    [$fromDate, $toDate] = [$toDate, $fromDate];
}

$reportRows = [];
$reportSummary = [];
$reportError = null;

try {
    switch ($reportType) {
        case 'financial':
            $reportSummary = fetchOneData($pdo, '
                  SELECT COUNT(*) AS bills,
                      COALESCE(SUM(FinalAmount), 0) AS billed,
                      (SELECT COALESCE(SUM(AmountPaid), 0) FROM payments
                       WHERE DATE(PaymentDate) BETWEEN ? AND ?) AS collected
                  FROM billing
                  WHERE DATE(BillingDate) BETWEEN ? AND ?', [$fromDate, $toDate, $fromDate, $toDate]);
            $reportRows = fetchAllData($pdo, '
                SELECT p.PaymentDate, p.ReferenceNo, p.AmountPaid,
                       CONCAT(pt.FirstName, \' \', pt.LastName) AS PatientName,
                       b.Status
                FROM payments p
                INNER JOIN billing b ON b.BillingID = p.BillingID
                INNER JOIN patients pt ON pt.PatientID = b.PatientID
                WHERE DATE(p.PaymentDate) BETWEEN ? AND ?
                ORDER BY p.PaymentDate DESC', [$fromDate, $toDate]);
            break;

        case 'appointments':
            $reportRows = fetchAllData($pdo, '
                SELECT a.AppointmentDate, a.AppointmentTime,
                       CONCAT(p.FirstName, \' \', p.LastName) AS PatientName,
                       CONCAT(u.FirstName, \' \', u.LastName) AS DoctorName,
                       a.Purpose, a.Status
                FROM appointments a
                INNER JOIN patients p ON p.PatientID = a.PatientID
                LEFT JOIN users u ON u.UserID = a.DoctorID
                WHERE a.AppointmentDate BETWEEN ? AND ?
                ORDER BY a.AppointmentDate DESC, a.AppointmentTime DESC', [$fromDate, $toDate]);
            break;

        case 'consultations':
            $reportRows = fetchAllData($pdo, '
                SELECT c.ConsultationDate,
                       CONCAT(p.FirstName, \' \', p.LastName) AS PatientName,
                       CONCAT(u.FirstName, \' \', u.LastName) AS DoctorName,
                       c.Diagnosis, c.Treatment, c.ConsultationFee
                FROM consultations c
                INNER JOIN patients p ON p.PatientID = c.PatientID
                LEFT JOIN users u ON u.UserID = c.DoctorID
                WHERE DATE(c.ConsultationDate) BETWEEN ? AND ?
                ORDER BY c.ConsultationDate DESC', [$fromDate, $toDate]);
            break;

        case 'patients':
        default:
            $reportRows = fetchAllData($pdo, '
                SELECT PatientCode, CONCAT(FirstName, \' \', LastName) AS PatientName,
                       BirthDate, Gender, PatientType, Phone, CreatedAt
                FROM patients
                WHERE DATE(CreatedAt) BETWEEN ? AND ?
                ORDER BY CreatedAt DESC', [$fromDate, $toDate]);
            break;
    }
} catch (PDOException $e) {
    error_log('Reports query failed: ' . $e->getMessage());
    $reportError = 'Unable to load the selected report.';
}

function reportValue(mixed $value): string
{
    return htmlspecialchars((string) ($value ?? '—'), ENT_QUOTES, 'UTF-8');
}

function reportMoney(mixed $value): string
{
    return '₱' . number_format((float) $value, 2);
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
    <title>Reports</title>
</head>

<body class="h-screen flex bg-slate-200">
    <?php include_once '../includes/sidebar.php'; ?>

    <section class="flex-1 p-6 overflow-auto">
        <div class="mb-8 space-y-1">
            <h1 class="text-2xl font-bold text-gray-800">Report Generation</h1>
            <p class="text-sm font-medium text-gray-500">Generate and export administrative reports</p>
        </div>

        <div class="grid grid-cols-3 gap-6">
            <!-- Left Column: Report Selection and Filters -->
            <div class="space-y-6 col-span-1">
                <!-- Report Type Selection -->
                <div class="bg-white rounded-lg p-4 shadow-sm">
                    <h2 class="text-base font-bold text-gray-800 mb-3">1. Select Report Type</h2>
                    <div class="space-y-2">
                        <a href="?report_type=patients&from_date=<?= urlencode($fromDate) ?>&to_date=<?= urlencode($toDate) ?>"
                            class="block w-full text-left px-3 py-2 rounded-lg border-2 <?= $reportType === 'patients' ? 'border-blue-500 bg-blue-50' : 'border-gray-200 hover:bg-gray-50' ?> transition">
                            <div class="flex items-center gap-2">
                                <i class="fa-solid fa-file-user text-blue-600 text-sm"></i>
                                <span class="font-medium <?= $reportType === 'patients' ? 'text-blue-700' : 'text-gray-700' ?> text-sm">Patient Records Report</span>
                            </div>
                        </a>

                        <a href="?report_type=financial&from_date=<?= urlencode($fromDate) ?>&to_date=<?= urlencode($toDate) ?>"
                            class="block w-full text-left px-3 py-2 rounded-lg border-2 <?= $reportType === 'financial' ? 'border-blue-500 bg-blue-50' : 'border-gray-200 hover:bg-gray-50' ?> transition">
                            <div class="flex items-center gap-2">
                                <i class="fa-solid fa-chart-pie text-orange-500 text-sm"></i>
                                <span class="font-medium <?= $reportType === 'financial' ? 'text-blue-700' : 'text-gray-700' ?> text-sm">Financial Summary</span>
                            </div>
                        </a>

                        <a href="?report_type=appointments&from_date=<?= urlencode($fromDate) ?>&to_date=<?= urlencode($toDate) ?>"
                            class="block w-full text-left px-3 py-2 rounded-lg border-2 <?= $reportType === 'appointments' ? 'border-blue-500 bg-blue-50' : 'border-gray-200 hover:bg-gray-50' ?> transition">
                            <div class="flex items-center gap-2">
                                <i class="fa-solid fa-calendar text-blue-500 text-sm"></i>
                                <span class="font-medium <?= $reportType === 'appointments' ? 'text-blue-700' : 'text-gray-700' ?> text-sm">Appointments Report</span>
                            </div>
                        </a>

                        <a href="?report_type=consultations&from_date=<?= urlencode($fromDate) ?>&to_date=<?= urlencode($toDate) ?>"
                            class="block w-full text-left px-3 py-2 rounded-lg border-2 <?= $reportType === 'consultations' ? 'border-blue-500 bg-blue-50' : 'border-gray-200 hover:bg-gray-50' ?> transition">
                            <div class="flex items-center gap-2">
                                <i class="fa-solid fa-stethoscope text-purple-500 text-sm"></i>
                                <span class="font-medium <?= $reportType === 'consultations' ? 'text-blue-700' : 'text-gray-700' ?> text-sm">Consultation Log</span>
                            </div>
                        </a>
                    </div>
                </div>

                <!-- Filter Criteria -->
                <div class="bg-white rounded-lg p-4 shadow-sm col-span-1">
                    <h2 class="text-base font-bold text-gray-800 mb-3">2. Filter Criteria</h2>
                    <form method="get" class="space-y-3">
                        <input type="hidden" name="report_type" value="<?= reportValue($reportType) ?>">
                        <!-- From Date -->
                        <div>
                            <label class="block text-xs font-medium text-gray-700 mb-1">From Date</label>
                            <input type="date" name="from_date" value="<?= reportValue($fromDate) ?>"
                                class="w-full px-3 py-1 text-sm border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500">
                        </div>

                        <!-- To Date -->
                        <div>
                            <label class="block text-xs font-medium text-gray-700 mb-1">To Date</label>
                            <input type="date" name="to_date" value="<?= reportValue($toDate) ?>"
                                class="w-full px-3 py-1 text-sm border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500">
                        </div>

                        <!-- Generate Report Button -->
                        <button type="submit"
                            class="w-full mt-2 px-4 py-2 bg-blue-600 text-white font-semibold text-sm rounded-lg hover:bg-blue-700 transition">
                            Generate Report
                        </button>
                    </form>
                </div>
            </div>

            <!-- Right Column: Preview Area -->
            <div id="report-preview" class="bg-white rounded-lg p-6 shadow-sm col-span-2">
                <div class="flex items-start justify-between gap-4 mb-5">
                    <div>
                        <h2 class="text-lg font-bold text-gray-800"><?= reportValue($reportTypes[$reportType]) ?></h2>
                        <p class="text-xs text-gray-500 mt-1"><?= reportValue($fromDate) ?> to <?= reportValue($toDate) ?></p>
                    </div>
                    <button type="button" onclick="window.print()"
                        class="print-hidden inline-flex items-center gap-2 px-3 py-2 bg-slate-700 text-white text-sm font-semibold rounded-lg hover:bg-slate-800">
                        <i class="fa-solid fa-print"></i> Print
                    </button>
                </div>

                <?php if ($reportError): ?>
                    <p class="text-sm text-red-600"><?= reportValue($reportError) ?></p>
                <?php elseif ($reportType === 'financial'): ?>
                    <div class="grid grid-cols-3 gap-3 mb-6">
                        <div class="rounded-lg bg-slate-50 p-4">
                            <p class="text-xs text-gray-500">Bills</p>
                            <p class="text-xl font-bold text-gray-800 mt-1"><?= reportValue($reportSummary['bills'] ?? 0) ?></p>
                        </div>
                        <div class="rounded-lg bg-slate-50 p-4">
                            <p class="text-xs text-gray-500">Billed</p>
                            <p class="text-xl font-bold text-gray-800 mt-1"><?= reportMoney($reportSummary['billed'] ?? 0) ?></p>
                        </div>
                        <div class="rounded-lg bg-slate-50 p-4">
                            <p class="text-xs text-gray-500">Collected</p>
                            <p class="text-xl font-bold text-emerald-700 mt-1"><?= reportMoney($reportSummary['collected'] ?? 0) ?></p>
                        </div>
                    </div>
                    <?php if (empty($reportRows)): ?>
                        <p class="text-sm text-gray-500">No payments found for this date range.</p>
                    <?php else: ?>
                        <div class="overflow-x-auto"><table class="w-full text-sm text-left">
                            <thead class="text-xs uppercase text-gray-500 border-b"><tr><th class="py-3">Date</th><th>Patient</th><th>Reference</th><th>Status</th><th class="text-right">Amount</th></tr></thead>
                            <tbody><?php foreach ($reportRows as $row): ?><tr class="border-b last:border-0"><td class="py-3"><?= reportValue($row['PaymentDate']) ?></td><td><?= reportValue($row['PatientName']) ?></td><td><?= reportValue($row['ReferenceNo']) ?></td><td><?= reportValue($row['Status']) ?></td><td class="text-right"><?= reportMoney($row['AmountPaid']) ?></td></tr><?php endforeach; ?></tbody>
                        </table></div>
                    <?php endif; ?>
                <?php else: ?>
                    <?php if (empty($reportRows)): ?>
                        <p class="text-sm text-gray-500">No records found for this date range.</p>
                    <?php else: ?>
                        <div class="overflow-x-auto"><table class="w-full text-sm text-left">
                            <thead class="text-xs uppercase text-gray-500 border-b">
                                <tr>
                                    <?php if ($reportType === 'patients'): ?><th class="py-3">Patient ID</th><th>Name</th><th>Birth Date</th><th>Gender</th><th>Type</th><th>Phone</th>
                                    <?php elseif ($reportType === 'appointments'): ?><th class="py-3">Date</th><th>Patient</th><th>Doctor</th><th>Purpose</th><th>Status</th>
                                    <?php else: ?><th class="py-3">Date</th><th>Patient</th><th>Doctor</th><th>Diagnosis</th><th>Fee</th><?php endif; ?>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($reportRows as $row): ?>
                                    <tr class="border-b last:border-0">
                                        <?php if ($reportType === 'patients'): ?><td class="py-3"><?= reportValue($row['PatientCode']) ?></td><td><?= reportValue($row['PatientName']) ?></td><td><?= reportValue($row['BirthDate']) ?></td><td><?= reportValue($row['Gender']) ?></td><td><?= reportValue($row['PatientType']) ?></td><td><?= reportValue($row['Phone']) ?></td>
                                        <?php elseif ($reportType === 'appointments'): ?><td class="py-3"><?= reportValue($row['AppointmentDate'] . ' ' . $row['AppointmentTime']) ?></td><td><?= reportValue($row['PatientName']) ?></td><td><?= reportValue($row['DoctorName']) ?></td><td><?= reportValue($row['Purpose']) ?></td><td><?= reportValue($row['Status']) ?></td>
                                        <?php else: ?><td class="py-3"><?= reportValue($row['ConsultationDate']) ?></td><td><?= reportValue($row['PatientName']) ?></td><td><?= reportValue($row['DoctorName']) ?></td><td><?= reportValue($row['Diagnosis']) ?></td><td><?= reportMoney($row['ConsultationFee']) ?></td><?php endif; ?>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table></div>
                    <?php endif; ?>
                <?php endif; ?>
            </div>
        </div>
    </section>

    <style>
        @media print {
            body * { visibility: hidden; }
            #report-preview, #report-preview * { visibility: visible; }
            #report-preview { position: absolute; inset: 0; width: 100%; box-shadow: none; }
            .print-hidden { display: none !important; }
        }
    </style>
</body>

</html>