<?php
require_once __DIR__ . '/../../includes/auth_check.php';
require_once __DIR__ . '/../../config/db.php';
header('Content-Type: application/json');

$input = json_decode(file_get_contents('php://input'), true);
$id = (int) ($input['id'] ?? 0);
$amount = (float) ($input['amount'] ?? 0);

if ($id <= 0) { echo json_encode(["status" => "error", "message" => "Invalid Purchase ID"]); exit; }
if ($amount <= 0) { echo json_encode(["status" => "error", "message" => "Amount must be greater than 0"]); exit; }

try {
    $pdo->beginTransaction();

    $stmt = $pdo->prepare("SELECT * FROM purchases WHERE id = ? FOR UPDATE");
    $stmt->execute([$id]);
    $purchase = $stmt->fetch();

    if (!$purchase) { $pdo->rollBack(); echo json_encode(["status" => "error", "message" => "Purchase not found"]); exit; }
    if ($purchase['due_amount'] <= 0) { $pdo->rollBack(); echo json_encode(["status" => "error", "message" => "This purchase has no due amount"]); exit; }
    if ($amount > $purchase['due_amount']) { $pdo->rollBack(); echo json_encode(["status" => "error", "message" => "Amount cannot exceed the Due Amount"]); exit; }

    $settings = $pdo->query("SELECT cash_balance FROM settings LIMIT 1 FOR UPDATE")->fetch();
    $currentCash = (float) $settings['cash_balance'];

    if ($amount > $currentCash) { $pdo->rollBack(); echo json_encode(["status" => "error", "message" => "Insufficient Cash Balance"]); exit; }

    $newCash = $currentCash - $amount;
    $pdo->prepare("UPDATE settings SET cash_balance = ?")->execute([$newCash]);

    $pdo->prepare("UPDATE purchases SET paid_amount = paid_amount + ?, due_amount = due_amount - ? WHERE id = ?")
        ->execute([$amount, $amount, $id]);

    if ($purchase['supplier_id']) {
        $pdo->prepare("UPDATE suppliers SET due = GREATEST(due - ?, 0) WHERE id = ?")->execute([$amount, $purchase['supplier_id']]);
    }

    $pdo->commit();

    echo json_encode(["status" => "success", "message" => "Due paid successfully", "cash_balance" => $newCash]);
} catch (Exception $e) {
    $pdo->rollBack();
    echo json_encode(["status" => "error", "message" => $e->getMessage()]);
}