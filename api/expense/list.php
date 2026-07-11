<?php
require_once __DIR__ . '/../../includes/auth_check.php';
require_once __DIR__ . '/../../config/db.php';
header('Content-Type: application/json');

try {
    $search = trim($_GET['search'] ?? '');

    $sql = "SELECT id, name, amount, created_at FROM expenses";
    $params = [];

    if ($search !== '') {
        $sql .= " WHERE name LIKE ?";
        $params[] = "%$search%";
    }

    $sql .= " ORDER BY id DESC";

    $stmt = $pdo->prepare($sql);
    $stmt->execute($params);
    $rows = $stmt->fetchAll();

    $total = $pdo->query("SELECT COALESCE(SUM(amount),0) AS val FROM expenses")->fetch()['val'];

    echo json_encode(["status" => "success", "data" => $rows, "total" => (float) $total]);
} catch (Exception $e) {
    echo json_encode(["status" => "error", "message" => $e->getMessage()]);
}