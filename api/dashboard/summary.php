<?php
require_once __DIR__ . '/../../includes/auth_check.php';
require_once __DIR__ . '/../../config/db.php';
header('Content-Type: application/json');

$period = $_GET['period'] ?? 'today'; // today | 7 | 30 | 365
$allowedPeriods = ['today', '7', '30', '365'];
if (!in_array($period, $allowedPeriods)) {
    $period = 'today';
}

/**
 * Period অনুযায়ী SQL Date Condition তৈরি করে
 */
function periodCondition($period, $column) {
    if ($period === 'today') {
        return "DATE($column) = CURDATE()";
    }
    $days = (int) $period;
    return "$column >= DATE_SUB(NOW(), INTERVAL $days DAY)";
}

try {
    $settings = $pdo->query("SELECT * FROM settings LIMIT 1")->fetch();

    // ============ Total Purchase (Period ভিত্তিক) ============
    $totalPurchase = $pdo->query("SELECT COALESCE(SUM(total_amount),0) AS val FROM purchases WHERE " . periodCondition($period, 'created_at'))->fetch()['val'];

    // ============ Total Sales (Period ভিত্তিক) ============
    $totalSales = $pdo->query("SELECT COALESCE(SUM(total_amount),0) AS val FROM sales WHERE " . periodCondition($period, 'created_at'))->fetch()['val'];

    // ============ Total Expenses (Period ভিত্তিক) ============
    $totalExpenses = $pdo->query("SELECT COALESCE(SUM(amount),0) AS val FROM expenses WHERE " . periodCondition($period, 'created_at'))->fetch()['val'];

    // ============ Total Profit (Period ভিত্তিক) = Sales - COGS - Expenses ============
    $cogsStmt = $pdo->query("
        SELECT COALESCE(SUM(s.quantity * p.purchase_price), 0) AS cogs
        FROM sales s
        INNER JOIN products p ON p.id = s.product_id
        WHERE " . periodCondition($period, 's.created_at')
    );
    $cogs = $cogsStmt->fetch()['cogs'];
    $totalProfit = ($totalSales - $cogs) - $totalExpenses;

    // ============ Customer/Supplier Due — সবসময় বর্তমান মোট (Period-independent) ============
    $customerDue = $pdo->query("SELECT COALESCE(SUM(due),0) AS val FROM customers")->fetch()['val'];
    $supplierDue = $pdo->query("SELECT COALESCE(SUM(due),0) AS val FROM suppliers")->fetch()['val'];

    // ============ Recent Purchase/Sales — সবসময় সাম্প্রতিক ১০টা (Scrollable List) ============
    $recentPurchases = $pdo->query("
        SELECT pu.id, pr.name AS product_name, pu.quantity, pu.total_amount, pu.payment_type, pu.created_at,
               s.name AS supplier_name
        FROM purchases pu
        INNER JOIN products pr ON pr.id = pu.product_id
        LEFT JOIN suppliers s ON s.id = pu.supplier_id
        ORDER BY pu.id DESC
        LIMIT 10
    ")->fetchAll();

    $recentSales = $pdo->query("
        SELECT sl.id, pr.name AS product_name, sl.quantity, sl.total_amount, sl.payment_type, sl.created_at,
               c.name AS customer_name
        FROM sales sl
        INNER JOIN products pr ON pr.id = sl.product_id
        LEFT JOIN customers c ON c.id = sl.customer_id
        ORDER BY sl.id DESC
        LIMIT 10
    ")->fetchAll();

    echo json_encode([
        "status" => "success",
        "data" => [
            "cash_balance"      => (float) $settings['cash_balance'],
            "opening_cash_set"  => (int) $settings['opening_cash_set'],
            "total_purchase"    => (float) $totalPurchase,
            "total_sales"       => (float) $totalSales,
            "total_profit"      => (float) $totalProfit,
            "customer_due"      => (float) $customerDue,
            "supplier_due"      => (float) $supplierDue,
            "total_expenses"    => (float) $totalExpenses,
            "recent_purchases"  => $recentPurchases,
            "recent_sales"      => $recentSales
        ]
    ]);
} catch (Exception $e) {
    echo json_encode(["status" => "error", "message" => $e->getMessage()]);
}