<?php
declare(strict_types=1);

require_once __DIR__ . '/../../includes/functions.php';
requireAuth();

$input = getJsonInput();
verifyCsrfToken($input['csrf_token'] ?? null);

$customerId = (int) ($input['customer_id'] ?? 0);
$amount     = (float) ($input['amount'] ?? 0);
$note       = clean($input['note'] ?? '');
$saleId     = !empty($input['sale_id']) ? (int) $input['sale_id'] : null;
$paymentDate = clean($input['payment_date'] ?? date('Y-m-d'));

if ($customerId <= 0 || $amount <= 0) {
    jsonResponse(false, 'Invalid payment amount.');
}

try {
    $pdo = getDB();
    $pdo->beginTransaction();

    // Lock customer row to prevent race conditions on concurrent payments
    $custStmt = $pdo->prepare('SELECT total_due FROM customers WHERE id = :id FOR UPDATE');
    $custStmt->execute(['id' => $customerId]);
    $customer = $custStmt->fetch();

    if (!$customer) {
        $pdo->rollBack();
        jsonResponse(false, 'Customer not found.');
    }

    if ($amount > (float) $customer['total_due']) {
        $pdo->rollBack();
        jsonResponse(false, 'Payment amount cannot exceed customer due amount.');
    }

    // 1. Insert Payment Record
    $insertPayment = $pdo->prepare('
        INSERT INTO customer_payments (customer_id, sale_id, amount, payment_date, note)
        VALUES (:customer_id, :sale_id, :amount, :payment_date, :note)
    ');
    $insertPayment->execute([
        'customer_id'  => $customerId,
        'sale_id'      => $saleId,
        'amount'       => $amount,
        'payment_date' => $paymentDate,
        'note'         => $note,
    ]);
    $paymentId = (int) $pdo->lastInsertId();

    // 2. Reduce Customer Due
    $updateCustomer = $pdo->prepare('UPDATE customers SET total_due = total_due - :amount WHERE id = :id');
    $updateCustomer->execute(['amount' => $amount, 'id' => $customerId]);

    // 3. Add Cash Transaction (Cash In)
    $insertCash = $pdo->prepare('
        INSERT INTO cash_transactions (type, direction, amount, reference_id, reference_table, note, transaction_date, created_by)
        VALUES ("customer_payment", "in", :amount, :ref_id, "customer_payments", :note, :date, :created_by)
    ');
    $insertCash->execute([
        'amount'     => $amount,
        'ref_id'     => $paymentId,
        'note'       => "Payment received from customer #{$customerId}",
        'date'       => $paymentDate,
        'created_by' => $_SESSION['user_id'],
    ]);

    $pdo->commit();
    jsonResponse(true, 'Payment recorded successfully.');

} catch (PDOException $e) {
    $pdo->rollBack();
    jsonResponse(false, 'Failed to process payment.');
}