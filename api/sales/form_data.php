<?php
require_once __DIR__ . '/../../includes/auth_check.php';
require_once __DIR__ . '/../../config/db.php';
header('Content-Type: application/json');

try {
    $products = $pdo->query("SELECT id, name, description, purchase_price, sale_price, stock, low_stock_alert FROM products WHERE stock > 0 ORDER BY name ASC")->fetchAll();
    $customers = $pdo->query("SELECT id, name, mobile FROM customers ORDER BY name ASC")->fetchAll();
    $settings = $pdo->query("SELECT cash_balance FROM settings LIMIT 1")->fetch();

    echo json_encode([
        "status" => "success",
        "data" => [
            "products" => $products,
            "customers" => $customers,
            "cash_balance" => (float) $settings['cash_balance']
        ]
    ]);
} catch (Exception $e) {
    echo json_encode(["status" => "error", "message" => $e->getMessage()]);
}