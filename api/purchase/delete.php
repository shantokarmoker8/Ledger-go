<?php
require_once __DIR__ . '/../../includes/auth_check.php';
require_once __DIR__ . '/../../config/db.php';
header('Content-Type: application/json');

$input = json_decode(file_get_contents('php://input'), true);
$id = (int) ($input['id'] ?? 0);

if ($id <= 0) {
    echo json_encode(["status" => "error", "message" => "Invalid Purchase ID"]);
    exit;
}

try {
    $pdo->beginTransaction();

    $stmt = $pdo->prepare("SELECT * FROM purchases WHERE id = ? FOR UPDATE");
    $stmt->execute([$id]);
    $purchase = $stmt->fetch();

    if (!$purchase) {
        $pdo->rollBack();
        echo json_encode(["status" => "error", "message" => "Purchase record not found"]);
        exit;
    }

    // Check product has enough stock left to reverse (cannot go negative)
    $product = $pdo->prepare("SELECT * FROM products WHERE id = ? FOR UPDATE");
    $product->execute([$purchase['product_id']]);
    $product = $product->fetch();

    if ($product && $product['stock'] < $purchase['quantity']) {
        $pdo->rollBack();
        echo json_encode(["status" => "error", "message" => "Cannot delete: stock already partially sold, reversing would make stock negative"]);
        exit;
    }

    // Reverse Stock
    if ($product) {
        $pdo->prepare("UPDATE products SET stock = stock - ? WHERE id = ?")->execute([$purchase['quantity'], $purchase['product_id']]);
    }

    // Reverse Cash Balance (add back paid amount)
    if ($purchase['paid_amount'] > 0) {
        $pdo->prepare("UPDATE settings SET cash_balance = cash_balance + ?")->execute([$purchase['paid_amount']]);
    }

    // Reverse Supplier Due
    if ($purchase['due_amount'] > 0 && $purchase['supplier_id']) {
        $pdo->prepare("UPDATE suppliers SET due = GREATEST(due - ?, 0) WHERE id = ?")->execute([$purchase['due_amount'], $purchase['supplier_id']]);
    }

    $pdo->prepare("DELETE FROM purchases WHERE id = ?")->execute([$id]);

    $pdo->commit();

    $newCash = $pdo->query("SELECT cash_balance FROM settings LIMIT 1")->fetch()['cash_balance'];

    echo json_encode(["status" => "success", "message" => "Purchase deleted successfully", "cash_balance" => (float) $newCash]);
} catch (Exception $e) {
    $pdo->rollBack();
    echo json_encode(["status" => "error", "message" => $e->getMessage()]);
}