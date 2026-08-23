<?php
require_once '../../config/config.php';
require_once '../../includes/auth.php';
require_once '../../includes/crud.php';

SessionManager::requireAdmin();
header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
	http_response_code(405);
	echo json_encode(['status' => 'error', 'message' => 'Invalid request method.']);
	exit;
}

$sessionToken = $_SESSION['csrf_token'] ?? '';
$submittedToken = $_POST['csrf_token'] ?? '';
if ($sessionToken === '' || $submittedToken === '' || !hash_equals($sessionToken, $submittedToken)) {
	http_response_code(403);
	echo json_encode([
		'status' => 'error',
		'message' => 'Your session token is invalid. Refresh the page and try again.',
		'csrf_token' => SessionManager::regenerateCsrfToken()
	]);
	exit;
}

$data = [
	'username' => trim($_POST['username'] ?? ''),
	'password' => $_POST['password'] ?? '',
	'firstName' => trim($_POST['firstName'] ?? ''),
	'lastName' => trim($_POST['lastName'] ?? ''),
	'role' => $_POST['role'] ?? 'Receptionist',
	'isDoctor' => isset($_POST['isDoctor']) ? 1 : 0,
	'email' => trim($_POST['email'] ?? ''),
	'phone' => trim($_POST['phone'] ?? ''),
];

if ($data['username'] === '' || $data['firstName'] === '' || $data['lastName'] === '' || $data['password'] === '') {
	echo json_encode(['status' => 'error', 'message' => 'Username, password, first name, and last name are required.']);
	exit;
}

if (strlen($data['password']) < 8) {
	echo json_encode(['status' => 'error', 'message' => 'Password must contain at least 8 characters.']);
	exit;
}

if (!in_array($data['role'], ['Admin', 'Doctor', 'Receptionist'], true)) {
	echo json_encode(['status' => 'error', 'message' => 'Please select a valid role.']);
	exit;
}

$result = addStaff($pdo, $data);
$result['csrf_token'] = SessionManager::regenerateCsrfToken();
echo json_encode($result);