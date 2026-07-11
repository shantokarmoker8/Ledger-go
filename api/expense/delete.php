<?php
require_once __DIR__ . '/../../includes/auth_check.php';
require_once __DIR__ . '/../../config/db.php';
header('Content-Type: application/json');

$input = json_decode(file_get_contents('php://input'), true);
$id = (int) ($input['id'] ?? 0);

if ($id <= 0) {
    echo json_encode(["status" => "error", "message" => "Invalid Expense ID"]);
    exit;
}

try {
    $pdo->beginTransaction();

    $stmt = $pdo->prepare("SELECT * FROM expenses WHERE id = ? FOR UPDATE");
    $stmt->execute([$id]);
    $expense = $stmt->fetch();

    if (!$expense) {
        $pdo->rollBack();
        echo json_encode(["status" => "error", "message" => "Expense not found"]);
        exit;
    }

    // Reverse: add amount back to cash balance
    $pdo->prepare("UPDATE settings SET cash_balance = cash_balance + ?")->execute([$expense['amount']]);
    $pdo->prepare("DELETE FROM expenses WHERE id = ?")->execute([$id]);

    $pdo->commit();

    $newCash = $pdo->query("SELECT cash_balance FROM settings LIMIT 1")->fetch()['cash_balance'];

    echo json_encode(["status" => "success", "message" => "Expense deleted successfully", "cash_balance" => (float) $newCash]);
} catch (Exception $e) {
    $pdo->rollBack();
    echo json_encode(["status" => "error", "message" => $e->getMessage()]);
}