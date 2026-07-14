<?php
require_once __DIR__ . '/../../includes/auth_check.php';
require_once __DIR__ . '/../../config/db.php';

$tables = ['settings', 'users', 'customers', 'suppliers', 'products', 'purchases', 'sales', 'expenses', 'customer_payments', 'supplier_payments', 'cash_transactions'];

$backup = [];
foreach ($tables as $t) {
    $backup[$t] = $pdo->query("SELECT * FROM `$t`")->fetchAll();
}
$backup['_meta'] = [
    'app' => 'Cash Khata',
    'exported_at' => date('Y-m-d H:i:s'),
    'version' => 1
];

$filename = 'cash-khata-backup-' . date('Y-m-d_His') . '.json';

header('Content-Type: application/json');
header('Content-Disposition: attachment; filename="' . $filename . '"');
echo json_encode($backup, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);