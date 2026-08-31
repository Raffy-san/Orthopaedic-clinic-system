<?php
require_once '../../config/config.php';
require_once '../../includes/auth.php';
require_once '../../includes/crud.php';

SessionManager::requireLogin();
SessionManager::requireAnyRole(['admin', 'doctor', 'staff']);
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

$patientType = $_POST['patientType'] ?? 'Regular';
$allowedGenders = ['Male', 'Female', 'Other'];
$allowedPatientTypes = ['Regular', 'Senior Citizen', 'PWD'];
$data = [
	'firstName' => trim($_POST['firstName'] ?? ''),
	'lastName' => trim($_POST['lastName'] ?? ''),
	'birthDate' => trim($_POST['birthDate'] ?? ''),
	'gender' => ucfirst(strtolower(trim($_POST['gender'] ?? ''))),
	'phone' => trim($_POST['phone'] ?? ''),
	'address' => trim($_POST['address'] ?? ''),
	'password' => $_POST['password'] ?? '',
	'patientType' => $patientType
];

if ($data['firstName'] === '' || $data['lastName'] === '' || $data['birthDate'] === '' || $data['password'] === '') {
	echo json_encode(['status' => 'error', 'message' => 'First name, last name, date of birth, and password are required.']);
	exit;
}

if (strlen($data['password']) < 8) {
	echo json_encode(['status' => 'error', 'message' => 'Password must contain at least 8 characters.']);
	exit;
}

if (!in_array($data['gender'], $allowedGenders, true)) {
	echo json_encode(['status' => 'error', 'message' => 'Please select a valid gender.']);
	exit;
}

if (!in_array($data['patientType'], $allowedPatientTypes, true)) {
	echo json_encode(['status' => 'error', 'message' => 'Please select a valid patient type.']);
	exit;
}

$result = addPatient($pdo, $data);
$result['csrf_token'] = SessionManager::regenerateCsrfToken();
echo json_encode($result);