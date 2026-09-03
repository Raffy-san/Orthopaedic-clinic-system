<?php
function fetchUserDashboardData(PDO $pdo, int $userId): array
{
    $patientStatement = $pdo->prepare('SELECT PatientID FROM patients WHERE UserID = ?');
    $patientStatement->execute([$userId]);
    $patientId = $patientStatement->fetchColumn();

    $upcomingAppointments = [];
    $pastConsultations = [];
    $recentVisits = [];
    $nextAppointment = null;

    if ($patientId) {
        $upcomingStatement = $pdo->prepare(
            "SELECT a.AppointmentDate, a.AppointmentTime, a.meridiem, a.Purpose, a.Status,
                    u.FirstName AS DoctorFirstName, u.LastName AS DoctorLastName
             FROM appointments a
             LEFT JOIN users u ON u.UserID = a.DoctorID
             WHERE a.PatientID = ? AND a.AppointmentDate >= CURDATE() AND a.Status <> 'Cancelled'
             ORDER BY a.AppointmentDate ASC, a.AppointmentTime ASC"
        );
        $upcomingStatement->execute([$patientId]);
        $upcomingAppointments = $upcomingStatement->fetchAll(PDO::FETCH_ASSOC);
        $nextAppointment = $upcomingAppointments[0] ?? null;

        $consultationStatement = $pdo->prepare(
            'SELECT c.ConsultationDate, c.Diagnosis, c.Treatment
             FROM consultations c
             WHERE c.PatientID = ?
             ORDER BY c.ConsultationDate DESC'
        );
        $consultationStatement->execute([$patientId]);
        $pastConsultations = $consultationStatement->fetchAll(PDO::FETCH_ASSOC);

        $recentVisitsStatement = $pdo->prepare(
            'SELECT a.Purpose, a.AppointmentDate, a.AppointmentTime, a.Status
             FROM appointments a
             WHERE a.PatientID = ? AND a.Status = \'Completed\'
             ORDER BY a.AppointmentDate DESC, a.AppointmentTime DESC
             LIMIT 5'
        );
        $recentVisitsStatement->execute([$patientId]);
        $recentVisits = $recentVisitsStatement->fetchAll(PDO::FETCH_ASSOC);
    }

    return [
        'upcomingAppointments' => $upcomingAppointments,
        'pastConsultations' => $pastConsultations,
        'recentVisits' => $recentVisits,
        'nextAppointment' => $nextAppointment,
    ];
}

function formatDashboardDate(?string $date, ?string $time = null): string
{
    if (!$date) {
        return 'None scheduled';
    }

    $formatted = date('M j, Y', strtotime($date));
    return $time ? $formatted . ' - ' . date('g:i A', strtotime($time)) : $formatted;
}
