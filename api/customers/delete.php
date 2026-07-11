<?php
declare(strict_types=1);

require_once __DIR__ . '/../../includes/functions.php';
requireAuth();

$input = getJsonInput();
verifyCsrfToken($input['csrf_token'] ?? null);

$id = (int) ($input['id'] ?? 0);
if ($id <= 0) {
    jsonResponse(false, 'Invalid customer ID.');
}

try {
    $pdo = getDB();

    // Prevent deletion if customer has sales history (data integrity)
    $checkStmt = $pdo->prepare('SELECT COUNT(*) AS total FROM sales WHERE customer_id = :id');
    $checkStmt->execute(['id' => $id]);
    if ((int) $checkStmt->fetch()['total'] > 0) {
        jsonResponse(false, 'Cannot delete: this customer has sales history. Set status to Inactive instead.');
    }

    $stmt = $pdo->prepare('DELETE FROM customers WHERE id = :id');
    $stmt->execute(['id' => $id]);

    jsonResponse(true, 'Customer deleted successfully.');

} catch (PDOException $e) {
    jsonResponse(false, 'Failed to delete customer.');
}