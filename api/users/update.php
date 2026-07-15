<?php
require_once __DIR__ . '/../../includes/auth_check.php';
require_once __DIR__ . '/../../config/db.php';
header('Content-Type: application/json');

$input = json_decode(file_get_contents('php://input'), true);

$id       = (int) ($input['id'] ?? 0);
$fullName = trim($input['full_name'] ?? '');
$username = trim($input['username'] ?? '');
$password = trim($input['password'] ?? ''); // Blank রাখলে পরিবর্তন হবে না
$role     = trim($input['role'] ?? '');

if ($id <= 0) {
    echo json_encode(["status" => "error", "message" => "Invalid User ID"]);
    exit;
}
if ($fullName === '' || $username === '') {
    echo json_encode(["status" => "error", "message" => "Name and Username are required"]);
    exit;
}

$isSelf = ($id === (int) $_SESSION['user_id']);

// অন্য কোনো ইউজারকে Edit করতে গেলে অবশ্যই Admin হতে হবে
if (!$isSelf && !isAdmin()) {
    echo json_encode(["status" => "error", "message" => "Only admin can edit other users"]);
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

    // Role শুধুমাত্র Admin-ই পরিবর্তন করতে পারবে
    if (isAdmin() && in_array($role, ['admin', 'staff'], true)) {
        $target = $pdo->prepare("SELECT role FROM users WHERE id = ?");
        $target->execute([$id]);
        $currentRole = $target->fetchColumn();

        // শেষ Admin-কে Staff বানানো ঠেকানো হচ্ছে, নাহলে সিস্টেম পরিচালনা করার মতো কেউ থাকবে না
        if ($currentRole === 'admin' && $role === 'staff') {
            $adminCount = $pdo->query("SELECT COUNT(*) AS cnt FROM users WHERE role = 'admin'")->fetch()['cnt'];
            if ($adminCount <= 1) {
                echo json_encode(["status" => "error", "message" => "At least one admin must remain"]);
                exit;
            }
        }

        if ($password !== '') {
            $stmt = $pdo->prepare("UPDATE users SET full_name = ?, username = ?, password = ?, role = ? WHERE id = ?");
            $stmt->execute([$fullName, $username, $password, $role, $id]);
        } else {
            $stmt = $pdo->prepare("UPDATE users SET full_name = ?, username = ?, role = ? WHERE id = ?");
            $stmt->execute([$fullName, $username, $role, $id]);
        }
    } else {
        if ($password !== '') {
            $stmt = $pdo->prepare("UPDATE users SET full_name = ?, username = ?, password = ? WHERE id = ?");
            $stmt->execute([$fullName, $username, $password, $id]);
        } else {
            $stmt = $pdo->prepare("UPDATE users SET full_name = ?, username = ? WHERE id = ?");
            $stmt->execute([$fullName, $username, $id]);
        }
    }

    // যদি নিজের Account Edit করে থাকে, Session Update করো
    if ($isSelf) {
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