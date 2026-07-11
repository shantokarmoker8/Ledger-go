<?php
require_once __DIR__ . '/../../includes/auth_check.php';
require_once __DIR__ . '/../../config/db.php';
header('Content-Type: application/json');

try {
    $search = trim($_GET['search'] ?? '');

    $sql = "
        SELECT pu.id, pu.quantity, pu.purchase_price, pu.total_amount, pu.payment_type,
               pu.paid_amount, pu.due_amount, pu.created_at,
               pr.name AS product_name,
               s.name AS supplier_name
        FROM purchases pu
        INNER JOIN products pr ON pr.id = pu.product_id
        LEFT JOIN suppliers s ON s.id = pu.supplier_id
    ";

    $params = [];
    if ($search !== '') {
        $sql .= " WHERE pr.name LIKE ? OR s.name LIKE ?";
        $params[] = "%$search%";
        $params[] = "%$search%";
    }

    $sql .= " ORDER BY pu.id DESC";

    $stmt = $pdo->prepare($sql);
    $stmt->execute($params);
    $rows = $stmt->fetchAll();

    echo json_encode(["status" => "success", "data" => $rows]);
} catch (Exception $e) {
    echo json_encode(["status" => "error", "message" => $e->getMessage()]);
}