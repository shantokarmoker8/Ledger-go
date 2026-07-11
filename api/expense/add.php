<?php
require_once __DIR__ . '/../../includes/auth_check.php';
require_once __DIR__ . '/../../config/db.php';
header('Content-Type: application/json');

$input = json_decode(file_get_contents('php://input'), true);

$name   = trim($input['name'] ?? '');
$amount = (float) ($input['amount'] ?? 0);

if ($name === '') {
    echo json_encode(["status" => "error", "message" => "Expense name is required"]);
    exit;
}
if ($amount <= 0) {
    echo json_encode(["status" => "error", "message" => "Amount must be greater than 0"]);
    exit;
}

try {
    $pdo->beginTransaction();

    $settings = $pdo->query("SELECT cash_balance FROM settings LIMIT 1 FOR UPDATE")->fetch();
    $currentCash = (float) $settings['cash_balance'];

    if ($amount > $currentCash) {
        $pdo->rollBack();
        echo json_encode(["status" => "error", "message" => "Insufficient Cash Balance"]);
        exit;
    }

    $pdo->prepare("INSERT INTO expenses (name, amount) VALUES (?, ?)")->execute([$name, $amount]);

    $newCash = $currentCash - $amount;
    $pdo->prepare("UPDATE settings SET cash_balance = ?")->execute([$newCash]);

    $pdo->commit();

    echo json_encode([
        "status" => "success",
        "message" => "Expense added successfully",
        "cash_balance" => $newCash
    ]);
} catch (Exception $e) {
    $pdo->rollBack();
    echo json_encode(["status" => "error", "message" => $e->getMessage()]);
}