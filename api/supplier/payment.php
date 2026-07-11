<?php
require_once __DIR__ . '/../../includes/auth_check.php';
require_once __DIR__ . '/../../config/db.php';
header('Content-Type: application/json');

$input = json_decode(file_get_contents('php://input'), true);
$supplierId = (int) ($input['supplier_id'] ?? 0);
$amount     = (float) ($input['amount'] ?? 0);

if ($supplierId <= 0) {
    echo json_encode(["status" => "error", "message" => "Invalid supplier"]);
    exit;
}
if ($amount <= 0) {
    echo json_encode(["status" => "error", "message" => "Amount must be greater than 0"]);
    exit;
}

try {
    $pdo->beginTransaction();

    $stmt = $pdo->prepare("SELECT * FROM suppliers WHERE id = ? FOR UPDATE");
    $stmt->execute([$supplierId]);
    $supplier = $stmt->fetch();

    if (!$supplier) {
        $pdo->rollBack();
        echo json_encode(["status" => "error", "message" => "Supplier not found"]);
        exit;
    }

    if ($amount > $supplier['due']) {
        $pdo->rollBack();
        echo json_encode(["status" => "error", "message" => "Payment amount cannot exceed Supplier Due"]);
        exit;
    }

    $settings = $pdo->query("SELECT cash_balance FROM settings LIMIT 1 FOR UPDATE")->fetch();
    $currentCash = (float) $settings['cash_balance'];

    if ($amount > $currentCash) {
        $pdo->rollBack();
        echo json_encode(["status" => "error", "message" => "Insufficient Cash Balance"]);
        exit;
    }

    // Reduce supplier due
    $pdo->prepare("UPDATE suppliers SET due = due - ? WHERE id = ?")->execute([$amount, $supplierId]);

    // Decrease cash balance
    $newCash = $currentCash - $amount;
    $pdo->prepare("UPDATE settings SET cash_balance = ?")->execute([$newCash]);

    // Log payment
    $pdo->prepare("INSERT INTO supplier_payments (supplier_id, amount) VALUES (?, ?)")->execute([$supplierId, $amount]);

    $pdo->commit();

    echo json_encode([
        "status" => "success",
        "message" => "Payment made successfully",
        "cash_balance" => $newCash
    ]);
} catch (Exception $e) {
    $pdo->rollBack();
    echo json_encode(["status" => "error", "message" => $e->getMessage()]);
}