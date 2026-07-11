<?php
require_once __DIR__ . '/../../includes/auth_check.php';
require_once __DIR__ . '/../../config/db.php';
header('Content-Type: application/json');

try {
    $settings = $pdo->query("SELECT * FROM settings LIMIT 1")->fetch();
    echo json_encode(["status" => "success", "data" => $settings]);
} catch (Exception $e) {
    echo json_encode(["status" => "error", "message" => $e->getMessage()]);
}