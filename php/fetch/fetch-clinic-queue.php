<?php
require_once __DIR__ . '/../../config/config.php';
require_once __DIR__ . '/../../includes/auth.php';

SessionManager::requireLogin();

header('Content-Type: application/json');

try {
    // Get today's confirmed appointments
    $stmt = $pdo->prepare("
        SELECT 
            a.AppointmentID,
            a.AppointmentDate,
            a.AppointmentTime,
            a.meridiem,
            a.Purpose,
            a.Status,
            p.PatientID,
            p.PatientCode,
            p.FirstName,
            p.LastName,
            p.Gender,
            p.BloodType,
            p.EmergencyContact,
            p.EmergencyPhone,
            TIMESTAMPDIFF(YEAR, p.BirthDate, CURDATE()) AS Age,
            c.ChiefComplaint,
            MAX(a2.AppointmentDate) AS LastVisitDate
        FROM appointments a
        INNER JOIN patients p ON a.PatientID = p.PatientID
        LEFT JOIN (
            SELECT PatientID, AppointmentDate, ChiefComplaint 
            FROM appointments 
            WHERE Status = 'Completed'
            ORDER BY AppointmentDate DESC 
            LIMIT 1
        ) c ON a.PatientID = c.PatientID
        LEFT JOIN appointments a2 ON a.PatientID = a2.PatientID AND a2.Status = 'Completed' AND a2.AppointmentID != a.AppointmentID
        WHERE DATE(a.AppointmentDate) = CURDATE() 
        AND a.Status = 'Confirmed'
        GROUP BY a.AppointmentID
        ORDER BY a.AppointmentTime ASC
    ");
    
    $stmt->execute();
    $appointments = $stmt->fetchAll(PDO::FETCH_ASSOC);

    echo json_encode([
        'status' => 'success',
        'data' => $appointments
    ]);

} catch (Exception $e) {
    http_response_code(500);
    echo json_encode([
        'status' => 'error',
        'message' => 'Database error: ' . $e->getMessage()
    ]);
}
?>
