<?php
require_once __DIR__ . '/../../includes/auth_check.php';
require_once __DIR__ . '/../../config/db.php';

$backup = [
    'exported_at' => date('Y-m-d H:i:s'),
    'app'         => 'Cash Khata',
    'version'     => 1,
    'tables'      => []
];

// নিরাপত্তার কারণে users টেবিল Backup-এ রাখা হচ্ছে না (Plain Text Password থাকে)
$tables = [
    'settings', 'customers', 'suppliers', 'products', 'purchases',
    'sales', 'expenses', 'customer_payments', 'supplier_payments', 'cash_transactions'
];

try {
    foreach ($tables as $table) {
        $stmt = $pdo->query("SELECT * FROM `$table`");
        $backup['tables'][$table] = $stmt->fetchAll();
    }

    $fileName = 'cash-khata-backup-' . date('Y-m-d-His') . '.json';

    header('Content-Type: application/json');
    header('Content-Disposition: attachment; filename="' . $fileName . '"');
    echo json_encode($backup, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);
} catch (Exception $e) {
    header('Content-Type: application/json');
    http_response_code(500);
    echo json_encode(["status" => "error", "message" => $e->getMessage()]);
}