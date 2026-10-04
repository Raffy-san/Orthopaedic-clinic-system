<?php
require_once '../../config/config.php';
require_once '../../includes/auth.php';
require_once '../../php/fetch/fetch.php';
require_once '../../includes/audit.php';

// Never print PHP warnings/errors into the response; they would break the JSON.
ini_set('display_errors', '0');
error_reporting(E_ALL);

SessionManager::requireLogin();
SessionManager::requireAnyRole(['admin', 'doctor', 'staff']);
header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['status' => 'error', 'message' => 'Invalid request method.']);
    exit;
}

$input = json_decode(file_get_contents('php://input'), true);

if (!is_array($input) || !isset($input['billing_id']) || !isset($input['amount_paid'])) {
    echo json_encode(['status' => 'error', 'message' => 'Missing required fields.']);
    exit;
}

$billingID = intval($input['billing_id']);
$amountPaid = round(floatval($input['amount_paid']), 2);

if ($billingID <= 0 || $amountPaid <= 0) {
    echo json_encode(['status' => 'error', 'message' => 'Please enter a valid amount.']);
    exit;
}

// AUDIT: who is performing this action (getUser() returns 'user_id', lowercase)
$actorUserId = SessionManager::getUser($pdo)['user_id'] ?? null;

try {
    $pdo->beginTransaction();

    // Lock the billing row so two simultaneous payments can't both pass the balance check.
    // Fetch the FULL row so we can audit-diff every field later.
    $stmt = $pdo->prepare('SELECT * FROM billing WHERE BillingID = ? FOR UPDATE');
    $stmt->execute([$billingID]);
    $billing = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$billing) {
        $pdo->rollBack();
        echo json_encode(['status' => 'error', 'message' => 'Billing record not found.']);
        exit;
    }

    if ($billing['Status'] === 'Paid') {
        $pdo->rollBack();
        echo json_encode(['status' => 'error', 'message' => 'This bill has already been fully paid.']);
        exit;
    }

    // Determine the discount from the patient's registered type (never trust the client)
    $stmt = $pdo->prepare('SELECT PatientType FROM patients WHERE PatientID = ?');
    $stmt->execute([$billing['PatientID']]);
    $patient = $stmt->fetch(PDO::FETCH_ASSOC);

    $patientType = trim((string) ($patient['PatientType'] ?? 'Regular'));
    $discountType = match (strtolower($patientType)) {
        'senior citizen' => 'Senior Citizen',
        'pwd' => 'PWD',
        default => 'None',
    };

    // $discountType is now defined BEFORE the rate lookup (this was the original bug)
    $discountRates = [
        'None' => 0,
        'Senior Citizen' => 20,
        'PWD' => 20,
    ];
    $discountPercent = $discountRates[$discountType] ?? 0;

    $originalAmount = floatval($billing['OriginalAmount']);
    $discountAmount = round($originalAmount * $discountPercent / 100, 2);
    $finalAmount = round($originalAmount - $discountAmount, 2);

    // Total already paid on this bill
    $stmt = $pdo->prepare('SELECT COALESCE(SUM(AmountPaid), 0) AS TotalPaid FROM payments WHERE BillingID = ?');
    $stmt->execute([$billingID]);
    $totalPaidBeforePayment = round((float) $stmt->fetchColumn(), 2);
    $remainingBalance = max(0, round($finalAmount - $totalPaidBeforePayment, 2));

    if ($amountPaid > $remainingBalance) {
        $pdo->rollBack();
        echo json_encode([
            'status' => 'error',
            'message' => 'Payment exceeds the remaining balance of ₱' . number_format($remainingBalance, 2) . '.'
        ]);
        exit;
    }

    // Persist the discount + recomputed FinalAmount
    $updateBilling = $pdo->prepare(
        'UPDATE billing SET DiscountType = ?, DiscountPercent = ?, DiscountAmount = ?, FinalAmount = ? WHERE BillingID = ?'
    );
    $updateBilling->execute([$discountType, $discountPercent, $discountAmount, $finalAmount, $billingID]);

    // AUDIT: log any changed billing fields
    $stmt = $pdo->prepare('SELECT * FROM billing WHERE BillingID = ?');
    $stmt->execute([$billingID]);
    $updatedBillingRow = $stmt->fetch(PDO::FETCH_ASSOC);
    logFieldChanges($pdo, $actorUserId, 'billing', $billingID, $billing, $updatedBillingRow);

    // Record the payment
    $stmt = $pdo->prepare(
        'INSERT INTO payments (BillingID, AmountPaid, ReferenceNo, PaymentDate, ReceivedBy)
         VALUES (?, ?, ?, NOW(), ?)'
    );
    $stmt->execute([$billingID, $amountPaid, null, $actorUserId]);
    $paymentID = (int) $pdo->lastInsertId();

    // AUDIT: payment recorded
    logAudit(
        $pdo,
        $actorUserId,
        'CREATE',
        'payments',
        $paymentID,
        null,
        null,
        json_encode([
            'BillingID' => $billingID,
            'AmountPaid' => $amountPaid,
            'ReceivedBy' => $actorUserId,
        ])
    );

    // Totals after this payment (reuses the sum we already computed)
    $totalAmountPaid = round($totalPaidBeforePayment + $amountPaid, 2);
    $amountDue = max(0, round($finalAmount - $totalAmountPaid, 2));

    $newStatus = ($totalAmountPaid >= $finalAmount) ? 'Paid' : 'Partially Paid';
    $previousStatus = $billing['Status'];

    $updateStmt = $pdo->prepare('UPDATE billing SET Status = ? WHERE BillingID = ?');
    $updateStmt->execute([$newStatus, $billingID]);

    // AUDIT: billing status change (Unpaid -> Partially Paid -> Paid)
    if ($previousStatus !== $newStatus) {
        logAudit($pdo, $actorUserId, 'UPDATE', 'billing', $billingID, 'Status', $previousStatus, $newStatus);
    }

    // Sequence number = number of payments on this bill (including the one just inserted)
    $stmt = $pdo->prepare('SELECT COUNT(*) FROM payments WHERE BillingID = ?');
    $stmt->execute([$billingID]);
    $sequence = max(1, (int) $stmt->fetchColumn());

    $receiptNo = 'OR-' . date('Y') . '-' . str_pad((string) $billingID, 5, '0', STR_PAD_LEFT) . '-' . $sequence;

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
        'amount_due' => $amountDue
    ]);

} catch (Throwable $e) {
    if ($pdo->inTransaction()) {
        $pdo->rollBack();
    }
    error_log('record-payment failed: ' . $e->getMessage());
    echo json_encode(['status' => 'error', 'message' => 'Error recording payment. Please try again.']);
}