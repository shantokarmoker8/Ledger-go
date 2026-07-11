<?php
declare(strict_types=1);

require_once __DIR__ . '/../../includes/functions.php';
requireAuth();

$input = getJsonInput();
verifyCsrfToken($input['csrf_token'] ?? null);

$id = (int) ($input['id'] ?? 0);
if ($id <= 0) {
    jsonResponse(false, 'Invalid supplier ID.');
}

try {
    $pdo = getDB();

    $checkStmt = $pdo->prepare('SELECT COUNT(*) AS total FROM purchases WHERE supplier_id = :id');
    $checkStmt->execute(['id' => $id]);
    if ((int) $checkStmt->fetch()['total'] > 0) {
        jsonResponse(false, 'Cannot delete: this supplier has purchase history. Set status to Inactive instead.');
    }

    $stmt = $pdo->prepare('DELETE FROM suppliers WHERE id = :id');
    $stmt->execute(['id' => $id]);

    jsonResponse(true, 'Supplier deleted successfully.');

} catch (PDOException $e) {
    jsonResponse(false, 'Failed to delete supplier.');
}