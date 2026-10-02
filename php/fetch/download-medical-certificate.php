<?php
require_once __DIR__ . '/../../config/config.php';
require_once __DIR__ . '/../../includes/auth.php';
require_once __DIR__ . '/../../php/fetch/fetch.php';

SessionManager::requireLogin();
SessionManager::requireAnyRole(['admin', 'doctor', 'staff']);

$certificateId = intval($_GET['id'] ?? 0);
if (!$certificateId) {
    http_response_code(400);
    exit('Invalid certificate ID.');
}

$certificate = fetchOneData(
    $pdo,
    'SELECT FilePath FROM medical_certificates WHERE CertificateID = ?',
    [$certificateId]
);

if (!$certificate) {
    http_response_code(404);
    exit('Certificate not found.');
}

$fullPath = __DIR__ . '/../../' . $certificate['FilePath'];
if (!file_exists($fullPath)) {
    http_response_code(404);
    exit('Certificate file is missing.');
}

header('Content-Type: application/pdf');
header('Content-Disposition: inline; filename="' . basename($fullPath) . '"');
header('Content-Length: ' . filesize($fullPath));
readfile($fullPath);
exit;