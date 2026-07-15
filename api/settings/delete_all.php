<?php
require_once __DIR__ . '/../../includes/auth_check.php';
require_once __DIR__ . '/../../config/db.php';
header('Content-Type: application/json');

if (!isAdmin()) {
    echo json_encode(["status" => "error", "message" => "Only admin can delete all data"]);
    exit;
}

$input = json_decode(file_get_contents('php://input'), true);

$password1   = trim($input['password1'] ?? '');
$password2   = trim($input['password2'] ?? '');
$confirmText = trim($input['confirm_text'] ?? '');

if ($password1 === '' || $password2 === '') {
    echo json_encode(["status" => "error", "message" => "Please enter your password in both fields"]);
    exit;
}

if ($password1 !== $password2) {
    echo json_encode(["status" => "error", "message" => "Passwords do not match"]);
    exit;
}

if ($confirmText !== 'DELETE') {
    echo json_encode(["status" => "error", "message" => "Please type DELETE in capital letters exactly"]);
    exit;
}

try {
    $stmt = $pdo->prepare("SELECT password FROM users WHERE id = ?");
    $stmt->execute([$_SESSION['user_id']]);
    $user = $stmt->fetch();

    if (!$user || $password1 !== $user['password']) {
        echo json_encode(["status" => "error", "message" => "Incorrect password"]);
        exit;
    }

    // ============ FIX: TRUNCATE একটা DDL Statement, এটা নিজে থেকেই Auto-Commit
    // হয়ে যায় — তাই এটাকে beginTransaction()/commit()-এর ভেতরে রাখলে পরে
    // commit() কল করার সময় PDO "no active transaction" Exception ছুঁড়ে দিতো।
    // ডাটা ঠিকই মুছে যেত, কিন্তু স্ক্রিপ্ট এরর করায় ব্রাউজারে "Failed" দেখাতো।
    // তাই এখন কোনো Transaction ছাড়াই সরাসরি প্রতিটা Table Truncate করা হচ্ছে।
    $pdo->exec('SET FOREIGN_KEY_CHECKS = 0');

    $tables = [
        'customer_payments', 'supplier_payments', 'sales', 'purchases',
        'products', 'customers', 'suppliers', 'cash_transactions', 'expenses'
    ];
    foreach ($tables as $table) {
        $pdo->exec("TRUNCATE TABLE `$table`");
    }

    $pdo->exec('SET FOREIGN_KEY_CHECKS = 1');

    $pdo->prepare("UPDATE settings SET cash_balance = 0, opening_cash_set = 0")->execute();

    echo json_encode(["status" => "success", "message" => "All business data has been deleted"]);
} catch (Exception $e) {
    $pdo->exec('SET FOREIGN_KEY_CHECKS = 1');
    echo json_encode(["status" => "error", "message" => $e->getMessage()]);
}