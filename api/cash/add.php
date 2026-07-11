<?php
require_once __DIR__ . '/../../includes/auth_check.php';
require_once __DIR__ . '/../../config/db.php';
header('Content-Type: application/json');

$input = json_decode(file_get_contents('php://input'), true);
$amount = (float) ($input['amount'] ?? 0);
$note = trim($input['note'] ?? '');

if ($amount <= 0) {
    echo json_encode(["status" => "error", "message" => "Amount must be greater than 0"]);
    exit;
}

try {
    $pdo->beginTransaction();

    $settings = $pdo->query("SELECT cash_balance FROM settings LIMIT 1 FOR UPDATE")->fetch();
    $newCash = (float) $settings['cash_balance'] + $amount;

    $pdo->prepare("UPDATE settings SET cash_balance = ?, opening_cash_set = 1")->execute([$newCash]);

    $pdo->prepare("INSERT INTO cash_transactions (type, amount, note, created_by) VALUES ('add', ?, ?, ?)")
        ->execute([$amount, $note, $_SESSION['user_id']]);

    $pdo->commit();

    echo json_encode([
        "status" => "success",
        "message" => "Cash added successfully",
        "cash_balance" => $newCash
    ]);
} catch (Exception $e) {
    $pdo->rollBack();
    echo json_encode(["status" => "error", "message" => $e->getMessage()]);
}