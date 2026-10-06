<?php
// Put in: php/fetch/fetch-medicines.php
// NOTE: paths assume this file is in php/fetch/. Match the require lines your
// other fetch files use if they differ.
require_once __DIR__ . '/../../config/config.php';
require_once __DIR__ . '/../../includes/auth.php';
SessionManager::requireLogin();
SessionManager::requireAnyRole(['admin', 'doctor', 'staff']);

header('Content-Type: application/json');

try {
    // ?all=1 includes hidden medicines (used by the medicine page)
    $includeInactive = isset($_GET['all']) && $_GET['all'] === '1';

    $sql = 'SELECT MedicineID, Name, DefaultDosage, IsActive FROM Medicines';
    if (!$includeInactive) {
        $sql .= ' WHERE IsActive = 1';
    }
    $sql .= ' ORDER BY Name ASC';

    $stmt = $pdo->query($sql);
    echo json_encode(['status' => 'success', 'data' => $stmt->fetchAll(PDO::FETCH_ASSOC)]);
} catch (Exception $e) {
    error_log($e->getMessage());
    echo json_encode(['status' => 'error', 'message' => 'Unable to load medicines.']);
}