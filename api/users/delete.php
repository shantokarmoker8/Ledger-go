<?php
require_once __DIR__ . '/../../includes/auth_check.php';
require_once __DIR__ . '/../../config/db.php';
header('Content-Type: application/json');

$input = json_decode(file_get_contents('php://input'), true);
$id = (int) ($input['id'] ?? 0);

if ($id <= 0) {
    echo json_encode(["status" => "error", "message" => "Invalid User ID"]);
    exit;
}

if ($id === (int) $_SESSION['user_id']) {
    echo json_encode(["status" => "error", "message" => "You cannot delete your own account while logged in"]);
    exit;
}

try {
    $totalUsers = $pdo->query("SELECT COUNT(*) AS cnt FROM users")->fetch()['cnt'];
    if ($totalUsers <= 1) {
        echo json_encode(["status" => "error", "message" => "At least one user must remain"]);
        exit;
    }

    $pdo->prepare("DELETE FROM users WHERE id = ?")->execute([$id]);
    echo json_encode(["status" => "success", "message" => "User deleted successfully"]);
} catch (Exception $e) {
    echo json_encode(["status" => "error", "message" => $e->getMessage()]);
}