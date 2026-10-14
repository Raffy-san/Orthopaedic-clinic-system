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
$patientName = trim($_GET['patient_name'] ?? '');
if ($patientCode === '' || $patientName === '') {
    echo json_encode(['status' => 'error', 'message' => 'Please enter both the Patient ID and full patient name.']);
    exit;
}

$patient = fetchOneData(
    $pdo,
    'SELECT PatientCode, FirstName, MiddleName, LastName, BirthDate, Gender, Phone, Email,PatientType, Address, Province, City, Barangay, IdNumber
     FROM patients WHERE PatientCode = ?',
    [$patientCode]
);

if (!$patient) {
    echo json_encode(['status' => 'error', 'message' => 'No patient found with that ID.']);
    exit;
}

$normalizeName = static function (string $name): string {
    $trimmedName = trim($name);
    $normalizedName = preg_replace('/\s+/u', ' ', $trimmedName) ?? $trimmedName;
    return function_exists('mb_strtolower')
        ? mb_strtolower($normalizedName, 'UTF-8')
        : strtolower($normalizedName);
};
$expectedName = $normalizeName(implode(' ', array_filter([
    $patient['FirstName'],
    $patient['MiddleName'],
    $patient['LastName'],
], static fn($part): bool => trim((string) $part) !== '')));

if (strcasecmp($normalizeName($patientName), $expectedName) !== 0) {
    http_response_code(422);
    echo json_encode([
        'status' => 'error',
        'message' => 'The name entered does not match this Patient ID. The name registered to this ID is ' . $expectedName . '.',
    ]);
    exit;
}

echo json_encode(['status' => 'success', 'patient' => $patient]);