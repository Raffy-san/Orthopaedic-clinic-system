<?php
require_once '../../config/config.php';
require_once '../../includes/auth.php';
require_once '../../php/fetch/fetch.php';

SessionManager::requireLogin();
SessionManager::requireAnyRole(['admin', 'doctor', 'staff']);
header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['status' => 'error', 'message' => 'Invalid request method.']);
    exit;
}

$input = json_decode(file_get_contents('php://input'), true);

if (!isset($input['billing_id']) || !isset($input['amount_paid'])) {
    echo json_encode(['status' => 'error', 'message' => 'Missing required fields.']);
    exit;
}

$billingID = intval($input['billing_id']);
$amountPaid = floatval($input['amount_paid']);
$paymentMethod = 'Cash'; 
$referenceNo = null;

// Get current billing record
$billing = fetchOneData(
    $pdo,
    'SELECT BillingID, ConsultationID, PatientID, OriginalAmount, DiscountAmount, FinalAmount, Status FROM billing WHERE BillingID = ?',
    [$billingID]
);

if (!$billing) {
    echo json_encode(['status' => 'error', 'message' => 'Billing record not found.']);
    exit;
}

try {
    $pdo->beginTransaction();

    // Record the payment
    $stmt = $pdo->prepare(
        'INSERT INTO payments (BillingID, AmountPaid, ReferenceNo, PaymentDate, ReceivedBy)
         VALUES (?, ?, ?, NOW(), ?)'
    );
    $stmt->execute([
        $billingID,
        $amountPaid,
        $referenceNo,
        SessionManager::getUser($pdo)['UserID'] ?? null
    ]);

    $paymentID = $pdo->lastInsertId();

    // Calculate new billing status
    $finalAmount = floatval($billing['FinalAmount']);
    $totalAmountPaid = $amountPaid;
    
    // Check if there are previous payments
    $previousPayments = fetchAllData(
        $pdo,
        'SELECT SUM(AmountPaid) as TotalPaid FROM payments WHERE BillingID = ? AND PaymentID != ?',
        [$billingID, $paymentID]
    );
    
    if (!empty($previousPayments) && $previousPayments[0]['TotalPaid']) {
        $totalAmountPaid += floatval($previousPayments[0]['TotalPaid']);
    }

    // Update billing status
    if ($totalAmountPaid >= $finalAmount) {
        $newStatus = 'Paid';
    } else {
        $newStatus = 'Partially Paid';
    }

    $updateStmt = $pdo->prepare('UPDATE billing SET Status = ? WHERE BillingID = ?');
    $updateStmt->execute([$newStatus, $billingID]);

    // Generate receipt number
    $receiptNo = 'OR-2026-' . str_pad($paymentID, 5, '0', STR_PAD_LEFT);

    $pdo->commit();

    echo json_encode([
        'status' => 'success',
        'message' => 'Payment recorded successfully.',
        'payment_id' => $paymentID,
        'receipt_no' => $receiptNo,
        'billing_status' => $newStatus,
        'total_paid' => $totalAmountPaid,
        'amount_due' => max(0, $finalAmount - $totalAmountPaid)
    ]);

} catch (Exception $e) {
    $pdo->rollBack();
    echo json_encode(['status' => 'error', 'message' => 'Error recording payment: ' . $e->getMessage()]);
}
