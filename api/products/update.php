<?php
declare(strict_types=1);

require_once __DIR__ . '/../../includes/functions.php';
requireAuth();

$input = getJsonInput();
verifyCsrfToken($input['csrf_token'] ?? null);

$id         = (int) ($input['id'] ?? 0);
$name       = clean($input['name'] ?? '');
$categoryId = !empty($input['category_id']) ? (int) $input['category_id'] : null;
$buyPrice   = (float) ($input['buy_price'] ?? 0);
$sellPrice  = (float) ($input['sell_price'] ?? 0);
$alertQty   = (float) ($input['alert_qty'] ?? 5);
$unit       = clean($input['unit'] ?? 'pcs');
$status     = in_array($input['status'] ?? '', ['active', 'inactive'], true) ? $input['status'] : 'active';

if ($id <= 0 || $name === '') {
    jsonResponse(false, 'Invalid product data.');
}
if ($buyPrice < 0 || $sellPrice < 0) {
    jsonResponse(false, 'Prices cannot be negative.');
}

// Note: stock_qty is intentionally NOT editable here.
// Stock only changes via Purchase (increase) or Sales (decrease) to keep the ledger accurate.

try {
    $pdo = getDB();
    $stmt = $pdo->prepare('
        UPDATE products
        SET name = :name, category_id = :category_id, buy_price = :buy_price,
            sell_price = :sell_price, alert_qty = :alert_qty, unit = :unit, status = :status
        WHERE id = :id
    ');
    $stmt->execute([
        'name'        => $name,
        'category_id' => $categoryId,
        'buy_price'   => $buyPrice,
        'sell_price'  => $sellPrice,
        'alert_qty'   => $alertQty,
        'unit'        => $unit,
        'status'      => $status,
        'id'          => $id,
    ]);

    jsonResponse(true, 'Product updated successfully.');

} catch (PDOException $e) {
    jsonResponse(false, 'Failed to update product.');
}