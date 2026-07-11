<?php
declare(strict_types=1);

require_once __DIR__ . '/../../includes/functions.php';
requireAuth();

$input = getJsonInput();
verifyCsrfToken($input['csrf_token'] ?? null);

$supplierId  = (int) ($input['supplier_id'] ?? 0);
$amount      = (float) ($input['amount'] ?? 0);
$note        = clean($input['note'] ?? '');
$purchaseId  = !empty($input['purchase_id']) ? (int) $input['purchase_id'] : null;
$paymentDate = clean($input['payment_date'] ?? date('Y-m-d'));

if ($supplierId <= 0 || $amount <= 0) {
    jsonResponse(false, 'Invalid payment amount.');
}

try {
    $pdo = getDB();
    $pdo->beginTransaction();

    // Lock supplier row to prevent race conditions
    $suppStmt = $pdo->prepare('SELECT total_due FROM suppliers WHERE id = :id FOR UPDATE');
    $suppStmt->execute(['id' => $supplierId]);
    $supplier = $suppStmt->fetch();

    if (!$supplier) {
        $pdo->rollBack();
        jsonResponse(false, 'Supplier not found.');
    }

    if ($amount > (float) $supplier['total_due']) {
        $pdo->rollBack();
        jsonResponse(false, 'Payment amount cannot exceed supplier due amount.');
    }

    // 1. Insert Payment Record
    $insertPayment = $pdo->prepare('
        INSERT INTO supplier_payments (supplier_id, purchase_id, amount, payment_date, note)
        VALUES (:supplier_id, :purchase_id, :amount, :payment_date, :note)
    ');
    $insertPayment->execute([
        'supplier_id'  => $supplierId,
        'purchase_id'  => $purchaseId,
        'amount'       => $amount,
        'payment_date' => $paymentDate,
        'note'         => $note,
    ]);
    $paymentId = (int) $pdo->lastInsertId();

    // 2. Reduce Supplier Due
    $updateSupplier = $pdo->prepare('UPDATE suppliers SET total_due = total_due - :amount WHERE id = :id');
    $updateSupplier->execute(['amount' => $amount, 'id' => $supplierId]);

    // 3. Add Cash Transaction (Cash OUT — we are paying the supplier)
    $insertCash = $pdo->prepare('
        INSERT INTO cash_transactions (type, direction, amount, reference_id, reference_table, note, transaction_date, created_by)
        VALUES ("supplier_payment", "out", :amount, :ref_id, "supplier_payments", :note, :date, :created_by)
    ');
    $insertCash->execute([
        'amount'     => $amount,
        'ref_id'     => $paymentId,
        'note'       => "Payment made to supplier #{$supplierId}",
        'date'       => $paymentDate,
        'created_by' => $_SESSION['user_id'],
    ]);

    $pdo->commit();
    jsonResponse(true, 'Payment made successfully.');

} catch (PDOException $e) {
    $pdo->rollBack();
    jsonResponse(false, 'Failed to process payment.');
}