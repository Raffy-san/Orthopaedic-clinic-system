<?php
// Put in: php/add/save-medicine.php
// NOTE: match the require lines to your other add files (e.g. save-consultation.php).
require_once __DIR__ . '/../../config/config.php';
require_once __DIR__ . '/../../includes/auth.php';
require_once __DIR__ . '/../../includes/crud.php'; // or wherever you pasted the functions
SessionManager::requireLogin();
SessionManager::requireAnyRole(['admin', 'doctor']);

header('Content-Type: application/json');

$input = json_decode(file_get_contents('php://input'), true) ?: [];

// CSRF check. If save-consultation.php validates the token with a SessionManager
// helper, use that same helper here instead of this manual comparison.
if (empty($_SESSION['csrf_token']) || !hash_equals($_SESSION['csrf_token'], (string) ($input['csrf_token'] ?? ''))) {
    echo json_encode(['status' => 'error', 'message' => 'Invalid request token. Please refresh the page.']);
    exit;
}

$actorUserId = isset($_SESSION['user_id']) ? (int) $_SESSION['user_id'] : null;

switch ($input['action'] ?? '') {
    case 'add':
        $result = addMedicine($pdo, $input, $actorUserId);
        break;

    case 'update':
        $result = updateMedicine($pdo, $input, $actorUserId);
        break;

    case 'toggle':
        $result = setMedicineActive(
            $pdo,
            (int) ($input['medicine_id'] ?? 0),
            !empty($input['is_active']),
            $actorUserId
        );
        break;

    default:
        $result = ['status' => 'error', 'message' => 'Unknown action.'];
}

echo json_encode($result);