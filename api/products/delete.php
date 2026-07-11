<?php
declare(strict_types=1);

require_once __DIR__ . '/../../includes/functions.php';
requireAuth();

$input = getJsonInput();
verifyCsrfToken($input['csrf_token'] ?? null);

$id = (int) ($input['id'] ?? 0);
if ($id <= 0) {
    jsonResponse(false, 'Invalid product ID.');
}

try {
    $pdo = getDB();

    $checkStmt = $pdo->prepare('
        SELECT
            (SELECT COUNT(*) FROM sale_items WHERE product_id = :id1) AS sale_count,
            (SELECT COUNT(*) FROM purchase_items WHERE product_id = :id2) AS purchase_count
    ');
    $checkStmt->execute(['id1' => $id, 'id2' => $id]);
    $counts = $checkStmt->fetch();

    if ((int) $counts['sale_count'] > 0 || (int) $counts['purchase_count'] > 0) {
        jsonResponse(false, 'Cannot delete: this product has purchase/sales history. Set status to Inactive instead.');
    }

    $stmt = $pdo->prepare('DELETE FROM products WHERE id = :id');
    $stmt->execute(['id' => $id]);

    jsonResponse(true, 'Product deleted successfully.');

} catch (PDOException $e) {
    jsonResponse(false, 'Failed to delete product.');
}