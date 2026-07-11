<?php
require_once __DIR__ . '/../../includes/auth_check.php';
require_once __DIR__ . '/../../config/db.php';
header('Content-Type: application/json');

try {
    $stmt = $pdo->query("SELECT id, username, full_name, created_at FROM users ORDER BY id ASC");
    $rows = $stmt->fetchAll();
    echo json_encode(["status" => "success", "data" => $rows]);
} catch (Exception $e) {
    echo json_encode(["status" => "error", "message" => $e->getMessage()]);
}