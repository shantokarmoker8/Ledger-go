<?php
require_once __DIR__ . '/../../includes/auth_check.php';
require_once __DIR__ . '/../../config/db.php';
header('Content-Type: application/json');

$input = json_decode(file_get_contents('php://input'), true);

if (!$input || !isset($input['_meta'])) {
    echo json_encode(["status" => "error", "message" => "Invalid or corrupted backup file"]);
    exit;
}

$tables = ['customers', 'suppliers', 'products', 'purchases', 'sales', 'expenses', 'customer_payments', 'supplier_payments', 'cash_transactions', 'users', 'settings'];

try {
    $pdo->exec("SET FOREIGN_KEY_CHECKS=0");
    $pdo->beginTransaction();

    foreach ($tables as $t) {
        if (!isset($input[$t]) || !is_array($input[$t])) continue;

        $pdo->exec("TRUNCATE TABLE `$t`");

        $rows = $input[$t];
        if (empty($rows)) continue;

        $columns = array_keys($rows[0]);
        $colList = implode(',', array_map(fn($c) => "`$c`", $columns));
        $placeholders = implode(',', array_fill(0, count($columns), '?'));
        $stmt = $pdo->prepare("INSERT INTO `$t` ($colList) VALUES ($placeholders)");

        foreach ($rows as $row) {
            $stmt->execute(array_values($row));
        }
    }

    $pdo->commit();
    $pdo->exec("SET FOREIGN_KEY_CHECKS=1");

    // বর্তমান Logged-in User এখনো নতুন Data-তে আছে কিনা চেক করা
    $userStmt = $pdo->prepare("SELECT * FROM users WHERE id = ?");
    $userStmt->execute([$_SESSION['user_id']]);
    $user = $userStmt->fetch();

    if (!$user) {
        session_destroy();
        echo json_encode(["status" => "success", "message" => "Data imported. Please login again.", "force_logout" => true]);
        exit;
    }

    $_SESSION['full_name'] = $user['full_name'];
    $_SESSION['username'] = $user['username'];

    echo json_encode(["status" => "success", "message" => "Data imported successfully"]);
} catch (Exception $e) {
    $pdo->exec("SET FOREIGN_KEY_CHECKS=1");
    if ($pdo->inTransaction()) $pdo->rollBack();
    echo json_encode(["status" => "error", "message" => $e->getMessage()]);
}