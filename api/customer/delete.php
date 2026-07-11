<?php
require_once __DIR__ . '/../../includes/auth_check.php';
require_once __DIR__ . '/../../config/db.php';
header('Content-Type: application/json');

$input = json_decode(file_get_contents('php://input'), true);
$id = (int) ($input['id'] ?? 0);

if ($id <= 0) {
    echo json_encode(["status" => "error", "message" => "Invalid Customer ID"]);
    exit;
}

try {
    $stmt = $pdo->prepare("SELECT due FROM customers WHERE id = ?");
    $stmt->execute([$id]);
    $customer = $stmt->fetch();

    if (!$customer) {
        echo json_encode(["status" => "error", "message" => "Customer not found"]);
        exit;
    }

    if ($customer['due'] > 0) {
        echo json_encode(["status" => "error", "message" => "Cannot delete customer with pending due. Clear due first."]);
        exit;
    }

    $pdo->prepare("UPDATE sales SET customer_id = NULL WHERE customer_id = ?")->execute([$id]);
    $pdo->prepare("DELETE FROM customers WHERE id = ?")->execute([$id]);

    echo json_encode(["status" => "success", "message" => "Customer deleted successfully"]);
} catch (Exception $e) {
    echo json_encode(["status" => "error", "message" => $e->getMessage()]);
}