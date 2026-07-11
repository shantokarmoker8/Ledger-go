<?php
declare(strict_types=1);

require_once __DIR__ . '/../../includes/functions.php';
requireAuth();

$input = getJsonInput();
verifyCsrfToken($input['csrf_token'] ?? null);

$name       = clean($input['name'] ?? '');
$categoryId = !empty($input['category_id']) ? (int) $input['category_id'] : null;
$buyPrice   = (float) ($input['buy_price'] ?? 0);
$sellPrice  = (float) ($input['sell_price'] ?? 0);
$stockQty   = (float) ($input['stock_qty'] ?? 0);
$alertQty   = (float) ($input['alert_qty'] ?? 5);
$unit       = clean($input['unit'] ?? 'pcs');

if ($name === '') {
    jsonResponse(false, 'Product name is required.');
}
if ($buyPrice < 0 || $sellPrice < 0) {
    jsonResponse(false, 'Prices cannot be negative.');
}
if ($stockQty < 0) {
    jsonResponse(false, 'Stock quantity cannot be negative.');
}

try {
    $pdo = getDB();
    $stmt = $pdo->prepare('
        INSERT INTO products (category_id, name, buy_price, sell_price, stock_qty, alert_qty, unit, status)
        VALUES (:category_id, :name, :buy_price, :sell_price, :stock_qty, :alert_qty, :unit, "active")
    ');
    $stmt->execute([
        'category_id' => $categoryId,
        'name'        => $name,
        'buy_price'   => $buyPrice,
        'sell_price'  => $sellPrice,
        'stock_qty'   => $stockQty,
        'alert_qty'   => $alertQty,
        'unit'        => $unit,
    ]);

    jsonResponse(true, 'Product added successfully.', ['id' => (int) $pdo->lastInsertId()]);

} catch (PDOException $e) {
    jsonResponse(false, 'Failed to add product.');
}