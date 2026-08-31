<?php
include_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/../includes/auth.php';

if (isset($_GET['query'])) {
    $search = "%" . $_GET['query'] . "%";

    $stmt = $pdo->prepare("SELECT id,  name, phone, email FROM patients WHERE name LIKE ? LIMIT 5");
    $stmt->execute([$search]);

    $patients = $stmt->fetchAll(PDO::FETCH_ASSOC);

    echo json_encode($patients);
}
