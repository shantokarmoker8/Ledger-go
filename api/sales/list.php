<?php
require_once __DIR__ . '/../../includes/auth_check.php';
require_once __DIR__ . '/../../config/db.php';
header('Content-Type: application/json');

try {
    $search = trim($_GET['search'] ?? '');

    $sql = "
        SELECT sl.id, sl.quantity, sl.sale_price, sl.total_amount, sl.payment_type,
               sl.paid_amount, sl.due_amount, sl.created_at,
               pr.name AS product_name,
               c.name AS customer_name
        FROM sales sl
        INNER JOIN products pr ON pr.id = sl.product_id
        LEFT JOIN customers c ON c.id = sl.customer_id
    ";

    $params = [];
    if ($search !== '') {
        $sql .= " WHERE pr.name LIKE ? OR c.name LIKE ?";
        $params[] = "%$search%";
        $params[] = "%$search%";
    }

    $sql .= " ORDER BY sl.id DESC";

    $stmt = $pdo->prepare($sql);
    $stmt->execute($params);
    $rows = $stmt->fetchAll();

    echo json_encode(["status" => "success", "data" => $rows]);
} catch (Exception $e) {
    echo json_encode(["status" => "error", "message" => $e->getMessage()]);
}