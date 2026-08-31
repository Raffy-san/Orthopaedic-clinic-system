<?php
require_once '../../config/config.php';
require_once '../../includes/auth.php';
require_once '../../php/fetch/fetch.php';

SessionManager::requireLogin();
SessionManager::requireAnyRole(['admin', 'doctor', 'staff']);
header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] !== 'GET') {
    http_response_code(405);
    echo json_encode(['status' => 'error', 'message' => 'Invalid request method.']);
    exit;
}

$patientCode = trim($_GET['patient_code'] ?? '');
if ($patientCode === '') {
    echo json_encode(['status' => 'error', 'message' => 'Please enter a Patient ID.']);
    exit;
}

$patient = fetchOneData(
    $pdo,
    'SELECT PatientCode, FirstName, LastName, BirthDate, Gender, Phone, PatientType, Address
     FROM patients WHERE PatientCode = ?',
    [$patientCode]
);

if (!$patient) {
    echo json_encode(['status' => 'error', 'message' => 'No patient found with that ID.']);
    exit;
}

echo json_encode(['status' => 'success', 'patient' => $patient]);