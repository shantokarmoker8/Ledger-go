<?php
require_once __DIR__ . '/../../includes/auth_check.php';
require_once __DIR__ . '/../../config/db.php';
header('Content-Type: application/json');

if (!isAdmin()) {
    echo json_encode(["status" => "error", "message" => "Only admin can import data"]);
    exit;
}

$raw    = file_get_contents('php://input');
$backup = json_decode($raw, true);

if (!is_array($backup) || !isset($backup['tables']) || !is_array($backup['tables'])) {
    echo json_encode(["status" => "error", "message" => "Invalid or corrupted backup file"]);
    exit;
}

$allowedTables = [
    'settings', 'customers', 'suppliers', 'products', 'purchases',
    'sales', 'expenses', 'customer_payments', 'supplier_payments', 'cash_transactions'
];

// Foreign Key নির্ভরতার কারণে Delete করার সঠিক ক্রম (Child টেবিল আগে)
$deleteOrder = [
    'customer_payments', 'supplier_payments', 'sales', 'purchases',
    'products', 'customers', 'suppliers', 'cash_transactions', 'expenses'
];

try {
    $pdo->beginTransaction();
    $pdo->exec('SET FOREIGN_KEY_CHECKS = 0');

    foreach ($deleteOrder as $table) {
        if (isset($backup['tables'][$table])) {
            $pdo->exec("DELETE FROM `$table`");
        }
    }

    foreach ($backup['tables'] as $table => $rows) {
        if (!in_array($table, $allowedTables, true) || !is_array($rows) || count($rows) === 0) {
            continue;
        }

        if ($table === 'settings') {
            // Settings টেবিলে সবসময় ১টি Row থাকে, তাই Update করা হচ্ছে
            $row = $rows[0];
            $stmt = $pdo->prepare("UPDATE settings SET business_name=?, business_address=?, business_phone=?, cash_balance=?, opening_cash_set=?, language=? LIMIT 1");
            $stmt->execute([
                $row['business_name'] ?? 'My Business',
                $row['business_address'] ?? '',
                $row['business_phone'] ?? '',
                $row['cash_balance'] ?? 0,
                $row['opening_cash_set'] ?? 0,
                $row['language'] ?? ($_SESSION['language'] ?? 'en')
            ]);
            continue;
        }

        foreach ($rows as $row) {
            if (!is_array($row) || count($row) === 0) continue;
            $columns      = array_keys($row);
            $placeholders = implode(',', array_fill(0, count($columns), '?'));
            $columnList   = implode(',', array_map(fn($c) => "`$c`", $columns));
            $sql          = "INSERT INTO `$table` ($columnList) VALUES ($placeholders)";
            $stmt         = $pdo->prepare($sql);
            $stmt->execute(array_values($row));
        }
    }

    $pdo->exec('SET FOREIGN_KEY_CHECKS = 1');
    $pdo->commit();

    $settings = $pdo->query("SELECT cash_balance FROM settings LIMIT 1")->fetch();

    echo json_encode([
        "status"       => "success",
        "message"      => "Data imported successfully",
        "cash_balance" => $settings['cash_balance'] ?? 0
    ]);
} catch (Exception $e) {
    $pdo->rollBack();
    $pdo->exec('SET FOREIGN_KEY_CHECKS = 1');
    echo json_encode(["status" => "error", "message" => "Import failed: " . $e->getMessage()]);
}