<?php
require_once __DIR__ . '/../../includes/auth_check.php';
require_once __DIR__ . '/../../config/db.php';
header('Content-Type: application/json');

$input = json_decode(file_get_contents('php://input'), true);
$id = (int) ($input['id'] ?? 0);

if ($id <= 0) {
    echo json_encode(["status" => "error", "message" => "Invalid Sale ID"]);
    exit;
}

try {
    $pdo->beginTransaction();

    $stmt = $pdo->prepare("SELECT * FROM sales WHERE id = ? FOR UPDATE");
    $stmt->execute([$id]);
    $sale = $stmt->fetch();

    if (!$sale) {
        $pdo->rollBack();
        echo json_encode(["status" => "error", "message" => "Sale record not found"]);
        exit;
    }

    // Check cash balance is enough to reverse (subtract back the paid amount)
    $settings = $pdo->query("SELECT cash_balance FROM settings LIMIT 1 FOR UPDATE")->fetch();
    $currentCash = (float) $settings['cash_balance'];

    if ($sale['paid_amount'] > $currentCash) {
        $pdo->rollBack();
        echo json_encode(["status" => "error", "message" => "Cannot delete: reversing this sale would make cash balance negative"]);
        exit;
    }

    // Restore Stock
    $pdo->prepare("UPDATE products SET stock = stock + ? WHERE id = ?")->execute([$sale['quantity'], $sale['product_id']]);

    // Reverse Cash Balance
    $newCash = $currentCash - $sale['paid_amount'];
    $pdo->prepare("UPDATE settings SET cash_balance = ?")->execute([$newCash]);

    // Reverse Customer Due
    if ($sale['due_amount'] > 0 && $sale['customer_id']) {
        $pdo->prepare("UPDATE customers SET due = GREATEST(due - ?, 0) WHERE id = ?")->execute([$sale['due_amount'], $sale['customer_id']]);
    }

    $pdo->prepare("DELETE FROM sales WHERE id = ?")->execute([$id]);

    $pdo->commit();

    echo json_encode(["status" => "success", "message" => "Sale deleted successfully", "cash_balance" => $newCash]);
} catch (Exception $e) {
    $pdo->rollBack();
    echo json_encode(["status" => "error", "message" => $e->getMessage()]);
}