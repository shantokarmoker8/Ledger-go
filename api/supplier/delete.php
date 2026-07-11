<?php
require_once __DIR__ . '/../../includes/auth_check.php';
require_once __DIR__ . '/../../config/db.php';
header('Content-Type: application/json');

$input = json_decode(file_get_contents('php://input'), true);
$id = (int) ($input['id'] ?? 0);

if ($id <= 0) {
    echo json_encode(["status" => "error", "message" => "Invalid Supplier ID"]);
    exit;
}

try {
    $stmt = $pdo->prepare("SELECT due FROM suppliers WHERE id = ?");
    $stmt->execute([$id]);
    $supplier = $stmt->fetch();

    if (!$supplier) {
        echo json_encode(["status" => "error", "message" => "Supplier not found"]);
        exit;
    }

    if ($supplier['due'] > 0) {
        echo json_encode(["status" => "error", "message" => "Cannot delete supplier with pending due. Clear due first."]);
        exit;
    }

    $pdo->prepare("UPDATE products SET supplier_id = NULL WHERE supplier_id = ?")->execute([$id]);
    $pdo->prepare("UPDATE purchases SET supplier_id = NULL WHERE supplier_id = ?")->execute([$id]);
    $pdo->prepare("DELETE FROM suppliers WHERE id = ?")->execute([$id]);

    echo json_encode(["status" => "success", "message" => "Supplier deleted successfully"]);
} catch (Exception $e) {
    echo json_encode(["status" => "error", "message" => $e->getMessage()]);
}