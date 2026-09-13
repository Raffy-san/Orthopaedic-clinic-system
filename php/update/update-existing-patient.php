<?php
require_once __DIR__ . '/../../config/config.php';
require_once __DIR__ . '/../../includes/auth.php';
require_once __DIR__ . '/../../includes/crud.php';

SessionManager::requireLogin();
SessionManager::requireAnyRole(['admin', 'doctor', 'staff']);

header('Content-Type: application/json');

// Verify CSRF token
$csrfToken = $_SESSION['csrf_token'] ?? '';
$requestData = json_decode(file_get_contents('php://input'), true);

if (empty($requestData['csrf_token']) || $requestData['csrf_token'] !== $csrfToken) {
    http_response_code(403);
    echo json_encode(['status' => 'error', 'message' => 'Invalid CSRF token']);
    exit;
}

// Call the updatePatient function from crud.php
$result = updatePatient($pdo, $requestData);

// Regenerate CSRF token
SessionManager::regenerateCsrfToken();

// Add csrf token to response
$result['csrf_token'] = $_SESSION['csrf_token'];

// Set response code based on status
if ($result['status'] === 'error') {
    http_response_code(500);
}

echo json_encode($result);
?>
