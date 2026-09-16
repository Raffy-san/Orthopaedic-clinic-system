<?php
require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/../includes/auth.php';

SessionManager::requireLogin();
SessionManager::requireAnyRole(['admin', 'doctor']);

header('Content-Type: application/json');

const PASSWORD_COLUMN = 'PasswordHash';

$input = json_decode(file_get_contents('php://input'), true) ?? [];
$csrf = $input['csrf_token'] ?? '';
$password = $input['password'] ?? '';

if (!hash_equals($_SESSION['csrf_token'] ?? '', $csrf)) {
    http_response_code(403);
    echo json_encode(['success' => false, 'message' => 'Invalid session, please refresh.']);
    exit;
}

if ($password === '') {
    echo json_encode(['success' => false, 'message' => 'Password is required.']);
    exit;
}

if (!SessionManager::isLoggedIn()) {
    http_response_code(401);
    echo json_encode(['success' => false, 'message' => 'Not authenticated.']);
    exit;
}

$userId = $_SESSION['user_id'] ?? $_SESSION['userId'] ?? $_SESSION['user']['UserID'] ?? null;
if ($userId === null) {
    http_response_code(401);
    echo json_encode(['success' => false, 'message' => 'Not authenticated.']);
    exit;
}

// Basic throttling to slow down brute-force attempts on this endpoint
$_SESSION['pw_attempts'] = $_SESSION['pw_attempts'] ?? ['count' => 0, 'last' => 0];
if ($_SESSION['pw_attempts']['count'] >= 5 && (time() - $_SESSION['pw_attempts']['last']) < 60) {
    http_response_code(429);
    echo json_encode(['success' => false, 'message' => 'Too many attempts. Try again in a minute.']);
    exit;
}

$stmt = $pdo->prepare('SELECT ' . PASSWORD_COLUMN . ' AS pw_hash FROM users WHERE UserID = ?');
$stmt->execute([$userId]);
$row = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$row || !password_verify($password, $row['pw_hash'])) {
    $_SESSION['pw_attempts']['count']++;
    $_SESSION['pw_attempts']['last'] = time();
    echo json_encode(['success' => false, 'message' => 'Incorrect password.']);
    exit;
}

$_SESSION['pw_attempts'] = ['count' => 0, 'last' => 0];
echo json_encode(['success' => true]);