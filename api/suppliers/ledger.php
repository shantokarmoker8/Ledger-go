<?php
declare(strict_types=1);

require_once __DIR__ . '/../../includes/functions.php';
requireAuth();

$id = (int) ($_GET['id'] ?? 0);
if ($id <= 0) {
    jsonResponse(false, 'Invalid supplier ID.');
}

try {
    $pdo = getDB();

    $suppStmt = $pdo->prepare('SELECT id, name, phone, address, total_due FROM suppliers WHERE id = :id');
    $suppStmt->execute(['id' => $id]);
    $supplier = $suppStmt->fetch();

    if (!$supplier) {
        jsonResponse(false, 'Supplier not found.');
    }

    // Purchase entries
    $purchaseStmt = $pdo->prepare("
        SELECT id, invoice_no, total_amount, paid_amount, due_amount, purchase_date AS date, 'purchase' AS entry_type
        FROM purchases WHERE supplier_id = :id
    ");
    $purchaseStmt->execute(['id' => $id]);
    $purchases = $purchaseStmt->fetchAll();

    // Payment entries
    $paymentsStmt = $pdo->prepare("
        SELECT id, amount, payment_date AS date, note, 'payment' AS entry_type
        FROM supplier_payments WHERE supplier_id = :id
    ");
    $paymentsStmt->execute(['id' => $id]);
    $payments = $paymentsStmt->fetchAll();

    $ledger = array_merge($purchases, $payments);
    usort($ledger, fn($a, $b) => strtotime($b['date']) <=> strtotime($a['date']));

    jsonResponse(true, '', [
        'supplier' => $supplier,
        'ledger'   => $ledger,
    ]);

} catch (PDOException $e) {
    jsonResponse(false, 'Failed to load supplier ledger.');
}