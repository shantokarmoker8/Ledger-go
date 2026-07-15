<?php
require_once __DIR__ . '/../../includes/auth_check.php';
require_once __DIR__ . '/../../config/db.php';
header('Content-Type: application/json');

$input = json_decode(file_get_contents('php://input'), true);

$username = trim($input['username'] ?? '');
$password = trim($input['password'] ?? '');
$fullName = trim($input['full_name'] ?? '');
$role     = trim($input['role'] ?? 'staff');

if ($username === '' || $password === '' || $fullName === '') {
    echo json_encode(["status" => "error", "message" => "All fields are required"]);
    exit;
}

if (!in_array($role, ['admin', 'staff'], true)) {
    $role = 'staff';
}

// শুধু Admin-ই নতুন Admin ইউজার তৈরি করতে পারবে; Staff শুধু Staff-ই তৈরি করতে পারবে
if (!isAdmin()) {
    $role = 'staff';
}

try {
    $check = $pdo->prepare("SELECT id FROM users WHERE username = ?");
    $check->execute([$username]);
    if ($check->fetch()) {
        echo json_encode(["status" => "error", "message" => "Username already exists"]);
        exit;
    }

    $stmt = $pdo->prepare("INSERT INTO users (username, password, full_name, role) VALUES (?, ?, ?, ?)");
    $stmt->execute([$username, $password, $fullName, $role]);

    echo json_encode([
        "status" => "success",
        "message" => "User added successfully",
        "data" => [
            "id" => $pdo->lastInsertId(),
            "username" => $username,
            "full_name" => $fullName,
            "role" => $role
        ]
    ]);
} catch (Exception $e) {
    echo json_encode(["status" => "error", "message" => $e->getMessage()]);
}