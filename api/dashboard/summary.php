<?php
require_once __DIR__ . '/../../includes/auth_check.php';
require_once __DIR__ . '/../../config/db.php';
header('Content-Type: application/json');

try {
    // Settings (cash balance, opening cash flag)
    $settings = $pdo->query("SELECT * FROM settings LIMIT 1")->fetch();

    // Total Purchase (sum of all purchase total_amount)
    $totalPurchase = $pdo->query("SELECT COALESCE(SUM(total_amount),0) AS val FROM purchases")->fetch()['val'];

    // Total Sales
    $totalSales = $pdo->query("SELECT COALESCE(SUM(total_amount),0) AS val FROM sales")->fetch()['val'];

    // Total Expenses
    $totalExpenses = $pdo->query("SELECT COALESCE(SUM(amount),0) AS val FROM expenses")->fetch()['val'];

    // Total Profit = (Sales Total - Cost of Goods Sold) - Expenses
    // Cost of Goods Sold = sum(sale.quantity * product.purchase_price at time of sale)
    // We approximate using purchase_price stored on purchase records average, simpler: use products current purchase_price
    $cogsStmt = $pdo->query("
        SELECT COALESCE(SUM(s.quantity * p.purchase_price), 0) AS cogs
        FROM sales s
        INNER JOIN products p ON p.id = s.product_id
    ");
    $cogs = $cogsStmt->fetch()['cogs'];
    $totalProfit = ($totalSales - $cogs) - $totalExpenses;

    // Customer Due (sum)
    $customerDue = $pdo->query("SELECT COALESCE(SUM(due),0) AS val FROM customers")->fetch()['val'];

    // Supplier Due (sum)
    $supplierDue = $pdo->query("SELECT COALESCE(SUM(due),0) AS val FROM suppliers")->fetch()['val'];

    // Recent Purchases (last 6)
    $recentPurchases = $pdo->query("
        SELECT pu.id, pr.name AS product_name, pu.quantity, pu.total_amount, pu.payment_type, pu.created_at,
               s.name AS supplier_name
        FROM purchases pu
        INNER JOIN products pr ON pr.id = pu.product_id
        LEFT JOIN suppliers s ON s.id = pu.supplier_id
        ORDER BY pu.id DESC
        LIMIT 6
    ")->fetchAll();

    // Recent Sales (last 6)
    $recentSales = $pdo->query("
        SELECT sl.id, pr.name AS product_name, sl.quantity, sl.total_amount, sl.payment_type, sl.created_at,
               c.name AS customer_name
        FROM sales sl
        INNER JOIN products pr ON pr.id = sl.product_id
        LEFT JOIN customers c ON c.id = sl.customer_id
        ORDER BY sl.id DESC
        LIMIT 6
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