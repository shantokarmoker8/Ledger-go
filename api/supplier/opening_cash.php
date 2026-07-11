<?php
require_once __DIR__ . '/../../includes/auth_check.php';
require_once __DIR__ . '/../../config/db.php';
header('Content-Type: application/json');

$input = json_decode(file_get_contents('php://input'), true);
$amount = (float) ($input['amount'] ?? -1);

if ($amount < 0) {
    echo json_encode(["status" => "error", "message" => "Please enter a valid amount"]);
    exit;
}

try {
    $stmt = $pdo->prepare("UPDATE settings SET cash_balance = ?, opening_cash_set = 1");
    $stmt->execute([$amount]);

    echo json_encode([
        "status" => "success",
        "message" => "Opening cash balance saved successfully",
        "cash_balance" => $amount
    ]);
} catch (Exception $e) {
    echo json_encode(["status" => "error", "message" => $e->getMessage()]);
}