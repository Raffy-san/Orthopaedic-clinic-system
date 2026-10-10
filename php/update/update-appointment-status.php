<?php
require_once __DIR__ . '/../../config/config.php';
require_once __DIR__ . '/../../includes/auth.php';

SessionManager::requireLogin();
SessionManager::requireAnyRole(['admin', 'doctor', 'staff']);

header('Content-Type: application/json');

// Verify CSRF token
$csrfToken = $_SESSION['csrf_token'] ?? '';
$requestData = json_decode(file_get_contents('php://input'), true);

if (
    empty($requestData['csrf_token'])
    || $csrfToken === ''
    || !hash_equals($csrfToken, (string) $requestData['csrf_token'])
) {
    http_response_code(403);
    echo json_encode(['status' => 'error', 'message' => 'Invalid CSRF token']);
    exit;
}

$appointmentId = intval($requestData['appointment_id'] ?? 0);
$status = $requestData['status'] ?? '';

// Validate status
$validStatuses = ['Pending', 'Confirmed', 'Completed', 'Cancelled', 'Rescheduled'];
if (!in_array($status, $validStatuses, true)) {
    http_response_code(400);
    echo json_encode(['status' => 'error', 'message' => 'Invalid appointment status']);
    exit;
}

try {
    $pdo->beginTransaction();

    // Lock the row so the status check and the update can't race
    $appointmentStatement = $pdo->prepare(
        "SELECT Status FROM appointments
         WHERE AppointmentID = :appointment_id
         LIMIT 1
         FOR UPDATE"
    );
    $appointmentStatement->execute([':appointment_id' => $appointmentId]);
    $currentStatus = $appointmentStatement->fetchColumn();

    if ($currentStatus === false) {
        $pdo->rollBack();
        http_response_code(404);
        echo json_encode(['status' => 'error', 'message' => 'Appointment not found']);
        exit;
    }

    if ($status === 'Cancelled' && !in_array($currentStatus, ['Pending', 'Confirmed'], true)) {
        $pdo->rollBack();
        http_response_code(400);
        echo json_encode(['status' => 'error', 'message' => 'Only pending or confirmed appointments can be cancelled.']);
        exit;
    }

    $stmt = $pdo->prepare("
        UPDATE appointments
        SET Status = :status
        WHERE AppointmentID = :appointment_id
    ");
    $stmt->execute([
        ':status' => $status,
        ':appointment_id' => $appointmentId
    ]);

    // Keep the linked follow-up record in sync (frees nothing by itself,
    // the slot is freed because the appointment is now Cancelled)
    if ($status === 'Cancelled') {
        $followupStmt = $pdo->prepare("
            UPDATE followups
            SET Status = 'Cancelled'
            WHERE AppointmentID = :appointment_id AND Status = 'Scheduled'
        ");
        $followupStmt->execute([':appointment_id' => $appointmentId]);
    }

    $pdo->commit();

    SessionManager::regenerateCsrfToken();

    echo json_encode([
        'status' => 'success',
        'message' => 'Appointment ' . strtolower($status) . ' successfully',
        'csrf_token' => $_SESSION['csrf_token']
    ]);
} catch (Exception $e) {
    if ($pdo->inTransaction()) {
        $pdo->rollBack();
    }
    error_log('update-appointment-status failed: ' . $e->getMessage());
    http_response_code(500);
    echo json_encode(['status' => 'error', 'message' => 'Unable to update the appointment. Please try again.']);
}
?>