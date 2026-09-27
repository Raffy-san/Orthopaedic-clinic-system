<?php
require_once '../../config/config.php';
require_once '../../includes/auth.php';
require_once '../../php/fetch/fetch.php';
require_once '../../includes/audit.php';

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

// AUDIT: who is performing this action.
// NOTE: SessionManager::getUser() returns 'user_id' (lowercase), not 'UserID' —
// this was previously read with the wrong key on the ReceivedBy field below too.
$actorUserId = SessionManager::getUser($pdo)['user_id'] ?? null;

// Server-side rate lookup — never trust a client-supplied percent
$discountRates = [
    'None' => 0,
    'Senior Citizen' => 20,
    'PWD' => 20,
];
$discountPercent = $discountRates[$discountType] ?? 0;

// Fetch the FULL old billing row (not just a few columns) so we can audit-diff every field later
$billing = fetchOneData(
    $pdo,
    'SELECT * FROM billing WHERE BillingID = ?',
    [$billingID]
);

if (!$billing) {
    echo json_encode(['status' => 'error', 'message' => 'Billing record not found.']);
    exit;
}

$patient = fetchOneData($pdo, 'SELECT PatientType FROM patients WHERE PatientID = ?', [$billing['PatientID']]);

$patientType = trim((string) ($patient['PatientType'] ?? 'Regular'));
$requiredDiscountType = match (strtolower($patientType)) {
    'senior citizen' => 'Senior Citizen',
    'pwd' => 'PWD',
    default => 'None',
};

if ($discountType !== $requiredDiscountType) {
    echo json_encode([
        'status' => 'error',
        'message' => 'Wrong discount type. This patient is registered as ' . $patientType . ' and must use ' . $requiredDiscountType . '.'
    ]);
    exit;
}

$originalAmount = floatval($billing['OriginalAmount']);
$discountAmount = round($originalAmount * $discountPercent / 100, 2);
$finalAmount = $originalAmount - $discountAmount;

try {
    $pdo->beginTransaction();

    $paidSummary = fetchOneData(
        $pdo,
        'SELECT COALESCE(SUM(AmountPaid), 0) AS TotalPaid FROM payments WHERE BillingID = ?',
        [$billingID]
    );
    $totalPaidBeforePayment = (float) ($paidSummary['TotalPaid'] ?? 0);
    $remainingBalance = max(0, $finalAmount - $totalPaidBeforePayment);

    if ($amountPaid > $remainingBalance) {
        $pdo->rollBack();
        echo json_encode([
            'status' => 'error',
            'message' => 'Payment exceeds the remaining balance of ₱' . number_format($remainingBalance, 2) . '.'
        ]);
        exit;
    }

    // Persist the discount + recomputed FinalAmount before comparing payment
    $updateBilling = $pdo->prepare(
        'UPDATE billing SET DiscountType = ?, DiscountPercent = ?, DiscountAmount = ?, FinalAmount = ? WHERE BillingID = ?'
    );
    $updateBilling->execute([$discountType, $discountPercent, $discountAmount, $finalAmount, $billingID]);

    // AUDIT: log any changed billing fields (discount type/percent/amount, final amount)
    $updatedBillingRow = fetchOneData($pdo, 'SELECT * FROM billing WHERE BillingID = ?', [$billingID]);
    logFieldChanges($pdo, $actorUserId, 'billing', $billingID, $billing, $updatedBillingRow);

    // Record the payment
    $stmt = $pdo->prepare(
        'INSERT INTO payments (BillingID, AmountPaid, ReferenceNo, PaymentDate, ReceivedBy)
         VALUES (?, ?, ?, NOW(), ?)'
    );
    $stmt->execute([
        $billingID,
        $amountPaid,
        null,
        $actorUserId
    ]);

    $paymentID = $pdo->lastInsertId();

    // AUDIT: payment recorded
    logAudit(
        $pdo,
        $actorUserId,
        'CREATE',
        'payments',
        (int) $paymentID,
        null,
        null,
        json_encode([
            'BillingID' => $billingID,
            'AmountPaid' => $amountPaid,
            'ReceivedBy' => $actorUserId,
        ])
    );

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

    $previousStatus = $billing['Status'];

    $updateStmt = $pdo->prepare('UPDATE billing SET Status = ? WHERE BillingID = ?');
    $updateStmt->execute([$newStatus, $billingID]);

    // AUDIT: billing status change (Unpaid -> Partially Paid -> Paid)
    if ($previousStatus !== $newStatus) {
        logAudit($pdo, $actorUserId, 'UPDATE', 'billing', $billingID, 'Status', $previousStatus, $newStatus);
    }

    // Count payments on this bill (including the one we just inserted) to build a sequence number
    $paymentCount = fetchOneData(
        $pdo,
        'SELECT COUNT(*) as cnt FROM payments WHERE BillingID = ?',
        [$billingID]
    );
    $sequence = intval($paymentCount['cnt'] ?? 1);

    $receiptNo = 'OR-' . date('Y') . '-' . str_pad($billingID, 5, '0', STR_PAD_LEFT) . '-' . $sequence;

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