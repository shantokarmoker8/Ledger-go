<?php
require_once __DIR__ . '/../../includes/auth_check.php';
require_once __DIR__ . '/../../config/db.php';
header('Content-Type: application/json');

$input = json_decode(file_get_contents('php://input'), true);
$customerId = (int) ($input['customer_id'] ?? 0);
$amount     = (float) ($input['amount'] ?? 0);

if ($customerId <= 0) {
    echo json_encode(["status" => "error", "message" => "Invalid customer"]);
    exit;
}
if ($amount <= 0) {
    echo json_encode(["status" => "error", "message" => "Amount must be greater than 0"]);
    exit;
}

try {
    $pdo->beginTransaction();

    $stmt = $pdo->prepare("SELECT * FROM customers WHERE id = ? FOR UPDATE");
    $stmt->execute([$customerId]);
    $customer = $stmt->fetch();

    if (!$customer) {
        $pdo->rollBack();
        echo json_encode(["status" => "error", "message" => "Customer not found"]);
        exit;
    }

    if ($amount > $customer['due']) {
        $pdo->rollBack();
        echo json_encode(["status" => "error", "message" => "Payment amount cannot exceed Customer Due"]);
        exit;
    }

    // Reduce customer due
    $pdo->prepare("UPDATE customers SET due = due - ? WHERE id = ?")->execute([$amount, $customerId]);

    // Increase cash balance
    $settings = $pdo->query("SELECT cash_balance FROM settings LIMIT 1 FOR UPDATE")->fetch();
    $newCash = (float) $settings['cash_balance'] + $amount;
    $pdo->prepare("UPDATE settings SET cash_balance = ?")->execute([$newCash]);

    // Log payment
    $pdo->prepare("INSERT INTO customer_payments (customer_id, amount) VALUES (?, ?)")->execute([$customerId, $amount]);

    $pdo->commit();

    echo json_encode([
        "status" => "success",
        "message" => "Payment received successfully",
        "cash_balance" => $newCash
    ]);
} catch (Exception $e) {
    $pdo->rollBack();
    echo json_encode(["status" => "error", "message" => $e->getMessage()]);
}