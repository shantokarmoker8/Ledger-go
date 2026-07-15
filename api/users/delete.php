<?php
require_once __DIR__ . '/../../includes/auth_check.php';
require_once __DIR__ . '/../../config/db.php';
header('Content-Type: application/json');

if (!isAdmin()) {
    echo json_encode(["status" => "error", "message" => "Only admin can delete users"]);
    exit;
}

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

    $target = $pdo->prepare("SELECT role FROM users WHERE id = ?");
    $target->execute([$id]);
    $targetRole = $target->fetchColumn();

    if ($targetRole === 'admin') {
        $adminCount = $pdo->query("SELECT COUNT(*) AS cnt FROM users WHERE role = 'admin'")->fetch()['cnt'];
        if ($adminCount <= 1) {
            echo json_encode(["status" => "error", "message" => "At least one admin must remain"]);
            exit;
        }
    }

    $pdo->prepare("DELETE FROM users WHERE id = ?")->execute([$id]);
    echo json_encode(["status" => "success", "message" => "User deleted successfully"]);
} catch (Exception $e) {
    echo json_encode(["status" => "error", "message" => $e->getMessage()]);
}