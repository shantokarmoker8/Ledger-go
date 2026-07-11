<?php
require_once __DIR__ . '/../../includes/auth_check.php';
require_once __DIR__ . '/../../config/db.php';
header('Content-Type: application/json');

try {
    $products = $pdo->query("SELECT id, name, purchase_price, sale_price, low_stock_alert, stock FROM products ORDER BY name ASC")->fetchAll();
    $suppliers = $pdo->query("SELECT id, name, mobile FROM suppliers ORDER BY name ASC")->fetchAll();
    $settings = $pdo->query("SELECT cash_balance FROM settings LIMIT 1")->fetch();

    echo json_encode([
        "status" => "success",
        "data" => [
            "products" => $products,
            "suppliers" => $suppliers,
            "cash_balance" => (float) $settings['cash_balance']
        ]
    ]);
} catch (Exception $e) {
    echo json_encode(["status" => "error", "message" => $e->getMessage()]);
}