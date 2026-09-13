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
$discountType = $input['discount_type'] ?? 'None';

// Server-side rate lookup — never trust a client-supplied percent
$discountRates = [
    'None' => 0,
    'Senior Citizen' => 20,
    'PWD' => 20,
];
$discountPercent = $discountRates[$discountType] ?? 0;

$billing = fetchOneData(
    $pdo,
    'SELECT BillingID, ConsultationID, PatientID, OriginalAmount, Status FROM billing WHERE BillingID = ?',
    [$billingID]
);

if (!$billing) {
    echo json_encode(['status' => 'error', 'message' => 'Billing record not found.']);
    exit;
}

$patient = fetchOneData($pdo, 'SELECT PatientType FROM patients WHERE PatientID = ?', [$billing['PatientID']]);

if ($discountType !== 'None' && strtolower($patient['PatientType']) !== strtolower($discountType)) {
    echo json_encode(['status' => 'error', 'message' => 'Discount type does not match patient records.']);
    exit;
}

$originalAmount = floatval($billing['OriginalAmount']);
$discountAmount = round($originalAmount * $discountPercent / 100, 2);
$finalAmount = $originalAmount - $discountAmount;

try {
    $pdo->beginTransaction();

    // Persist the discount + recomputed FinalAmount before comparing payment
    $updateBilling = $pdo->prepare(
        'UPDATE billing SET DiscountType = ?, DiscountPercent = ?, DiscountAmount = ?, FinalAmount = ? WHERE BillingID = ?'
    );
    $updateBilling->execute([$discountType, $discountPercent, $discountAmount, $finalAmount, $billingID]);

    // Record the payment (unchanged)
    $stmt = $pdo->prepare(
        'INSERT INTO payments (BillingID, AmountPaid, ReferenceNo, PaymentDate, ReceivedBy)
         VALUES (?, ?, ?, NOW(), ?)'
    );
    $stmt->execute([
        $billingID,
        $amountPaid,
        null,
        SessionManager::getUser($pdo)['UserID'] ?? null
    ]);

    $paymentID = $pdo->lastInsertId();

    $totalAmountPaid = $amountPaid;
    $previousPayments = fetchAllData(
        $pdo,
        'SELECT SUM(AmountPaid) as TotalPaid FROM payments WHERE BillingID = ? AND PaymentID != ?',
        [$billingID, $paymentID]
    );
    if (!empty($previousPayments) && $previousPayments[0]['TotalPaid']) {
        $totalAmountPaid += floatval($previousPayments[0]['TotalPaid']);
    }

    // Now compares against the freshly recalculated $finalAmount, not the stale 500
    $newStatus = ($totalAmountPaid >= $finalAmount) ? 'Paid' : 'Partially Paid';

    $updateStmt = $pdo->prepare('UPDATE billing SET Status = ? WHERE BillingID = ?');
    $updateStmt->execute([$newStatus, $billingID]);

    $receiptNo = 'OR-' . date('Y') . '-' . str_pad($paymentID, 5, '0', STR_PAD_LEFT);

    $updateRef = $pdo->prepare('UPDATE payments SET ReferenceNo = ? WHERE PaymentID = ?');
    $updateRef->execute([$receiptNo, $paymentID]);

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
