<?php
declare(strict_types=1);

require_once __DIR__ . '/../../includes/functions.php';
requireAuth();

$id = (int) ($_GET['id'] ?? 0);
if ($id <= 0) {
    jsonResponse(false, 'Invalid customer ID.');
}

try {
    $pdo = getDB();

    $custStmt = $pdo->prepare('SELECT id, name, phone, address, total_due FROM customers WHERE id = :id');
    $custStmt->execute(['id' => $id]);
    $customer = $custStmt->fetch();

    if (!$customer) {
        jsonResponse(false, 'Customer not found.');
    }

    // Sales entries
    $salesStmt = $pdo->prepare("
        SELECT id, invoice_no, total_amount, paid_amount, due_amount, sale_date AS date, 'sale' AS entry_type
        FROM sales WHERE customer_id = :id
    ");
    $salesStmt->execute(['id' => $id]);
    $sales = $salesStmt->fetchAll();

    // Payment entries
    $paymentsStmt = $pdo->prepare("
        SELECT id, amount, payment_date AS date, note, 'payment' AS entry_type
        FROM customer_payments WHERE customer_id = :id
    ");
    $paymentsStmt->execute(['id' => $id]);
    $payments = $paymentsStmt->fetchAll();

    // Merge and sort by date descending
    $ledger = array_merge($sales, $payments);
    usort($ledger, fn($a, $b) => strtotime($b['date']) <=> strtotime($a['date']));

    jsonResponse(true, '', [
        'customer' => $customer,
        'ledger'   => $ledger,
    ]);

} catch (PDOException $e) {
    jsonResponse(false, 'Failed to load customer ledger.');
}