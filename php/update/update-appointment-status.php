<?php
require_once __DIR__ . '/../../config/config.php';
require_once __DIR__ . '/../../includes/auth.php';

SessionManager::requireLogin();

header('Content-Type: application/json');

// Verify CSRF token
$csrfToken = $_SESSION['csrf_token'] ?? '';
$requestData = json_decode(file_get_contents('php://input'), true);

if (empty($requestData['csrf_token']) || $requestData['csrf_token'] !== $csrfToken) {
    http_response_code(403);
    echo json_encode(['status' => 'error', 'message' => 'Invalid CSRF token']);
    exit;
}

$appointmentId = intval($requestData['appointment_id'] ?? 0);
$status = $requestData['status'] ?? '';

// Validate status
$validStatuses = ['Pending', 'Confirmed', 'Completed', 'Cancelled', 'Rescheduled'];
if (!in_array($status, $validStatuses)) {
    http_response_code(400);
    echo json_encode(['status' => 'error', 'message' => 'Invalid appointment status']);
    exit;
}

try {
    $stmt = $pdo->prepare("
        UPDATE appointments 
        SET Status = :status 
        WHERE AppointmentID = :appointment_id
    ");
    
    $stmt->execute([
        ':status' => $status,
        ':appointment_id' => $appointmentId
    ]);

    if ($stmt->rowCount() > 0) {
        // Regenerate CSRF token
        SessionManager::regenerateCsrfToken();
        
        echo json_encode([
            'status' => 'success', 
            'message' => 'Appointment ' . strtolower($status) . ' successfully',
            'csrf_token' => $_SESSION['csrf_token']
        ]);
    } else {
        http_response_code(404);
        echo json_encode(['status' => 'error', 'message' => 'Appointment not found']);
    }
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(['status' => 'error', 'message' => 'Database error: ' . $e->getMessage()]);
}
?>
