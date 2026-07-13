<?php
require_once __DIR__ . '/../../includes/auth_check.php';
require_once __DIR__ . '/../../config/db.php';
header('Content-Type: application/json');

$input = json_decode(file_get_contents('php://input'), true);

$id       = (int) ($input['id'] ?? 0);
$fullName = trim($input['full_name'] ?? '');
$username = trim($input['username'] ?? '');
$password = trim($input['password'] ?? ''); // Blank রাখলে পরিবর্তন হবে না

if ($id <= 0) {
    echo json_encode(["status" => "error", "message" => "Invalid User ID"]);
    exit;
}
if ($fullName === '' || $username === '') {
    echo json_encode(["status" => "error", "message" => "Name and Username are required"]);
    exit;
}

try {
    // Username অন্য কোনো User ব্যবহার করছে কিনা চেক
    $check = $pdo->prepare("SELECT id FROM users WHERE username = ? AND id != ?");
    $check->execute([$username, $id]);
    if ($check->fetch()) {
        echo json_encode(["status" => "error", "message" => "This username is already taken"]);
        exit;
    }

    if ($password !== '') {
        $stmt = $pdo->prepare("UPDATE users SET full_name = ?, username = ?, password = ? WHERE id = ?");
        $stmt->execute([$fullName, $username, $password, $id]);
    } else {
        $stmt = $pdo->prepare("UPDATE users SET full_name = ?, username = ? WHERE id = ?");
        $stmt->execute([$fullName, $username, $id]);
    }

    // যদি নিজের Account Edit করে থাকে, Session Update করো
    if ($id === (int) $_SESSION['user_id']) {
        $_SESSION['full_name'] = $fullName;
        $_SESSION['username'] = $username;
    }

    echo json_encode([
        "status" => "success",
        "message" => "User updated successfully",
        "data" => ["full_name" => $fullName, "username" => $username]
    ]);
} catch (Exception $e) {
    echo json_encode(["status" => "error", "message" => $e->getMessage()]);
}