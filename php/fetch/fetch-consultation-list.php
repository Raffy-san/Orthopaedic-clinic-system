<?php
require_once __DIR__ . '/../../config/config.php';
require_once __DIR__ . '/../../includes/auth.php';

SessionManager::requireLogin();
header('Content-Type: application/json');

try {
    // Existing simple search (patient name / code)
    $search = trim($_GET['search'] ?? '');

    // Advanced search filters
    $diagnosis = trim($_GET['diagnosis'] ?? '');
    $dateFrom  = trim($_GET['date_from'] ?? '');
    $dateTo    = trim($_GET['date_to'] ?? '');
    $doctorId  = trim($_GET['doctor_id'] ?? '');

    $sql = "
        SELECT
            c.ConsultationID,
            c.PatientID,
            c.ConsultationDate,
            c.IsCompleted,
            c.Diagnosis,
            p.PatientCode,
            p.FirstName,
            p.LastName,
            a.DoctorID,
            u.FirstName AS DoctorFirstName,
            u.LastName  AS DoctorLastName
        FROM consultations c
        INNER JOIN patients p      ON p.PatientID = c.PatientID
        LEFT JOIN appointments a   ON a.AppointmentID = c.AppointmentID
        LEFT JOIN users u          ON u.UserID = a.DoctorID
    ";

    $conditions = [];
    $params = [];

    // Patient name / ID search (matches existing behavior)
    if ($search !== '') {
        $conditions[] = "(p.FirstName LIKE ? OR p.LastName LIKE ? OR p.PatientCode LIKE ?)";
        $like = "%{$search}%";
        $params[] = $like;
        $params[] = $like;
        $params[] = $like;
    }

    // Diagnosis filter
    if ($diagnosis !== '') {
        $conditions[] = "c.Diagnosis LIKE ?";
        $params[] = "%{$diagnosis}%";
    }

    // Date range filter (accepts either just date_from, just date_to, or both)
    if ($dateFrom !== '' && $dateTo !== '') {
        $conditions[] = "DATE(c.ConsultationDate) BETWEEN ? AND ?";
        $params[] = $dateFrom;
        $params[] = $dateTo;
    } elseif ($dateFrom !== '') {
        $conditions[] = "DATE(c.ConsultationDate) >= ?";
        $params[] = $dateFrom;
    } elseif ($dateTo !== '') {
        $conditions[] = "DATE(c.ConsultationDate) <= ?";
        $params[] = $dateTo;
    }

    // Doctor filter
    if ($doctorId !== '' && ctype_digit($doctorId)) {
        $conditions[] = "a.DoctorID = ?";
        $params[] = (int) $doctorId;
    }

    if (!empty($conditions)) {
        $sql .= " WHERE " . implode(' AND ', $conditions);
    }

    $sql .= " ORDER BY c.ConsultationDate DESC ";

    $stmt = $pdo->prepare($sql);
    $stmt->execute($params);
    $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);

    echo json_encode(['status' => 'success', 'data' => $rows]);

} catch (Exception $e) {
    error_log('Consultations list query error: ' . $e->getMessage());
    http_response_code(500);
    echo json_encode(['status' => 'error', 'message' => 'Unable to load records.']);
}