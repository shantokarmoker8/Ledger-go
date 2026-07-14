<?php
require_once __DIR__ . '/../../includes/auth_check.php';
require_once __DIR__ . '/../../config/db.php';
header('Content-Type: application/json');

$input = json_decode(file_get_contents('php://input'), true);
$password1 = trim($input['password1'] ?? '');
$password2 = trim($input['password2'] ?? '');

if ($password1 === '' || $password2 === '') {
    echo json_encode(["status" => "error", "message" => "Both password fields are required"]);
    exit;
}
if ($password1 !== $password2) {
    echo json_encode(["status" => "error", "message" => "Passwords do not match"]);
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

    $tables = ['cash_transactions', 'customer_payments', 'supplier_payments', 'sales', 'purchases', 'expenses', 'products', 'customers', 'suppliers'];

    $pdo->exec("SET FOREIGN_KEY_CHECKS=0");
    $pdo->beginTransaction();
    foreach ($tables as $t) {
        $pdo->exec("TRUNCATE TABLE `$t`");
    }
    $pdo->prepare("UPDATE settings SET cash_balance = 0, opening_cash_set = 0")->execute();
    $pdo->commit();
    $pdo->exec("SET FOREIGN_KEY_CHECKS=1");

    echo json_encode(["status" => "success", "message" => "All data deleted successfully"]);
} catch (Exception $e) {
    $pdo->exec("SET FOREIGN_KEY_CHECKS=1");
    if ($pdo->inTransaction()) $pdo->rollBack();
    echo json_encode(["status" => "error", "message" => $e->getMessage()]);
}