<?php
require_once __DIR__ . '/../../includes/auth_check.php';
require_once __DIR__ . '/../../config/db.php';
header('Content-Type: application/json');

try {
    $stmt = $pdo->query("
        SELECT ct.id, ct.type, ct.amount, ct.note, ct.created_at, u.full_name AS user_name
        FROM cash_transactions ct
        LEFT JOIN users u ON u.id = ct.created_by
        ORDER BY ct.id DESC
        LIMIT 100
    ");
    $rows = $stmt->fetchAll();

    echo json_encode(["status" => "success", "data" => $rows]);
} catch (Exception $e) {
    echo json_encode(["status" => "error", "message" => $e->getMessage()]);
}