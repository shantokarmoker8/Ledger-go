<?php
require_once __DIR__ . '/../../includes/auth_check.php';
require_once __DIR__ . '/../../config/db.php';
header('Content-Type: application/json');

$type = $_GET['type'] ?? 'sales';       // purchase | sales | profit | customer_due | supplier_due | expenses
$days = (int) ($_GET['days'] ?? 7);     // 7 | 30 | 365

$allowedTypes = ['purchase', 'sales', 'profit', 'customer_due', 'supplier_due', 'expenses'];
if (!in_array($type, $allowedTypes)) {
    echo json_encode(["status" => "error", "message" => "Invalid chart type"]);
    exit;
}

try {
    $labels = [];
    $values = [];

    switch ($type) {
        case 'purchase':
            $stmt = $pdo->prepare("
                SELECT DATE(created_at) AS d, SUM(total_amount) AS val
                FROM purchases
                WHERE created_at >= DATE_SUB(NOW(), INTERVAL :days DAY)
                GROUP BY DATE(created_at) ORDER BY d ASC
            ");
            $stmt->execute(['days' => $days]);
            $rows = $stmt->fetchAll();
            break;

        case 'sales':
            $stmt = $pdo->prepare("
                SELECT DATE(created_at) AS d, SUM(total_amount) AS val
                FROM sales
                WHERE created_at >= DATE_SUB(NOW(), INTERVAL :days DAY)
                GROUP BY DATE(created_at) ORDER BY d ASC
            ");
            $stmt->execute(['days' => $days]);
            $rows = $stmt->fetchAll();
            break;

        case 'expenses':
            $stmt = $pdo->prepare("
                SELECT DATE(created_at) AS d, SUM(amount) AS val
                FROM expenses
                WHERE created_at >= DATE_SUB(NOW(), INTERVAL :days DAY)
                GROUP BY DATE(created_at) ORDER BY d ASC
            ");
            $stmt->execute(['days' => $days]);
            $rows = $stmt->fetchAll();
            break;

        case 'customer_due':
            $stmt = $pdo->prepare("SELECT name, due FROM customers WHERE due > 0 ORDER BY due DESC LIMIT 10");
            $stmt->execute();
            $rows = $stmt->fetchAll();
            foreach ($rows as $r) {
                $labels[] = $r['name'];
                $values[] = (float) $r['due'];
            }
            echo json_encode(["status" => "success", "labels" => $labels, "values" => $values, "chart_type" => "bar"]);
            exit;

        case 'supplier_due':
            $stmt = $pdo->prepare("SELECT name, due FROM suppliers WHERE due > 0 ORDER BY due DESC LIMIT 10");
            $stmt->execute();
            $rows = $stmt->fetchAll();
            foreach ($rows as $r) {
                $labels[] = $r['name'];
                $values[] = (float) $r['due'];
            }
            echo json_encode(["status" => "success", "labels" => $labels, "values" => $values, "chart_type" => "bar"]);
            exit;

        case 'profit':
            $stmt = $pdo->prepare("
                SELECT DATE(s.created_at) AS d,
                       SUM(s.total_amount) AS sales_val,
                       SUM(s.quantity * p.purchase_price) AS cogs_val
                FROM sales s
                INNER JOIN products p ON p.id = s.product_id
                WHERE s.created_at >= DATE_SUB(NOW(), INTERVAL :days DAY)
                GROUP BY DATE(s.created_at) ORDER BY d ASC
            ");
            $stmt->execute(['days' => $days]);
            $rows = $stmt->fetchAll();
            foreach ($rows as $r) {
                $labels[] = date('M d', strtotime($r['d']));
                $values[] = (float) ($r['sales_val'] - $r['cogs_val']);
            }
            echo json_encode(["status" => "success", "labels" => $labels, "values" => $values, "chart_type" => "line"]);
            exit;
    }

    foreach ($rows as $r) {
        $labels[] = date('M d', strtotime($r['d']));
        $values[] = (float) $r['val'];
    }

    echo json_encode(["status" => "success", "labels" => $labels, "values" => $values, "chart_type" => "line"]);
} catch (Exception $e) {
    echo json_encode(["status" => "error", "message" => $e->getMessage()]);
}