<?php
require_once __DIR__ . '/../../config/config.php';
require_once __DIR__ . '/../../includes/auth.php';

SessionManager::requireLogin();
SessionManager::requireAnyRole(['admin', 'doctor', 'staff']);

header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] !== 'GET') {
    http_response_code(405);
    echo json_encode(['status' => 'error', 'message' => 'Invalid request method.']);
    exit;
}

try {
    // Fetch all follow-ups with patient and doctor information
    $stmt = $pdo->prepare("
        SELECT 
            f.FollowUpID,
            f.PatientID,
            f.DoctorID,
            f.FollowUpDate,
            f.Status,
            f.Remarks,
            f.AppointmentID,
            p.FirstName as PatientFirstName,
            p.LastName as PatientLastName,
            p.PatientCode,
            u.FirstName as DoctorFirstName,
            u.LastName as DoctorLastName
        FROM followups f
        LEFT JOIN patients p ON f.PatientID = p.PatientID
        LEFT JOIN users u ON f.DoctorID = u.UserID
        WHERE f.FollowUpDate >= CURDATE()
        ORDER BY f.FollowUpDate ASC, f.Status ASC
    ");
    $stmt->execute();
    $followups = $stmt->fetchAll(PDO::FETCH_ASSOC);

    // For each follow-up, fetch the consultation info to get the reason
    foreach ($followups as &$followup) {
        if ($followup['AppointmentID']) {
            $consultStmt = $pdo->prepare("
                SELECT c.Diagnosis, c.Treatment
                FROM consultations c
                WHERE c.AppointmentID = ?
                LIMIT 1
            ");
            $consultStmt->execute([$followup['AppointmentID']]);
            $consultation = $consultStmt->fetch(PDO::FETCH_ASSOC);
            $followup['reason'] = $consultation ? $consultation['Diagnosis'] : 'Follow-up visit';
        } else {
            $followup['reason'] = 'Follow-up visit';
        }
    }

    echo json_encode([
        'status' => 'success',
        'data' => $followups,
        'total' => count($followups)
    ]);

} catch (PDOException $e) {
    error_log('Fetch follow-ups failed: ' . $e->getMessage());
    http_response_code(500);
    echo json_encode(['status' => 'error', 'message' => 'Unable to fetch follow-ups.']);
}
?>
