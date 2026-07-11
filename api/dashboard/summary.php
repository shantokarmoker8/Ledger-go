<?php
declare(strict_types=1);

require_once __DIR__ . '/../../includes/functions.php';
requireAuth();
header('Content-Type: application/json');

$pdo = getDB();

$filter = $_GET['filter'] ?? 'today';

// Determine date range
$today = date('Y-m-d');
switch ($filter) {
    case '7days':
        $startDate = date('Y-m-d', strtotime('-6 days'));
        break;
    case '30days':
        $startDate = date('Y-m-d', strtotime('-29 days'));
        break;
    case 'month':
        $startDate = date('Y-m-01');
        break;
    case 'year':
        $startDate = date('Y-01-01');
        break;
    default: // today
        $startDate = $today;
}
$endDate = $today;

try {
    // Opening Balance
    $openingBalance = (float) $pdo->query('SELECT opening_balance FROM settings ORDER BY id LIMIT 1')
        ->fetchColumn();

    // Current Cash = Opening + SUM(in) - SUM(out) [all-time]
    $cashStmt = $pdo->query("
        SELECT
            COALESCE(SUM(CASE WHEN direction = 'in' THEN amount ELSE 0 END), 0) AS total_in,
            COALESCE(SUM(CASE WHEN direction = 'out' THEN amount ELSE 0 END), 0) AS total_out
        FROM cash_transactions
    ");
    $cashRow = $cashStmt->fetch();
    $currentCash = $openingBalance + (float) $cashRow['total_in'] - (float) $cashRow['total_out'];

    // Cash In / Out within selected filter range
    $rangeStmt = $pdo->prepare("
        SELECT
            COALESCE(SUM(CASE WHEN direction = 'in' THEN amount ELSE 0 END), 0) AS cash_in,
            COALESCE(SUM(CASE WHEN direction = 'out' THEN amount ELSE 0 END), 0) AS cash_out
        FROM cash_transactions
        WHERE transaction_date BETWEEN :start AND :end
    ");
    $rangeStmt->execute(['start' => $startDate, 'end' => $endDate]);
    $rangeRow = $rangeStmt->fetch();

    // Today's Income (Sales paid amount today)
    $incomeStmt = $pdo->prepare("
        SELECT COALESCE(SUM(paid_amount), 0) AS total
        FROM sales WHERE sale_date = :today
    ");
    $incomeStmt->execute(['today' => $today]);
    $todaysIncome = (float) $incomeStmt->fetch()['total'];

    // Today's Expense
    $expenseStmt = $pdo->prepare("
        SELECT COALESCE(SUM(amount), 0) AS total
        FROM expenses WHERE expense_date = :today
    ");
    $expenseStmt->execute(['today' => $today]);
    $todaysExpense = (float) $expenseStmt->fetch()['total'];

    // Today's Profit (from sale_items)
    $profitStmt = $pdo->prepare("
        SELECT COALESCE(SUM(si.profit), 0) AS total
        FROM sale_items si
        INNER JOIN sales s ON s.id = si.sale_id
        WHERE s.sale_date = :today
    ");
    $profitStmt->execute(['today' => $today]);
    $todaysProfit = (float) $profitStmt->fetch()['total'];

    // Customer Due
    $custDue = (float) $pdo->query('SELECT COALESCE(SUM(total_due), 0) FROM customers')->fetchColumn();

    // Supplier Due
    $suppDue = (float) $pdo->query('SELECT COALESCE(SUM(total_due), 0) FROM suppliers')->fetchColumn();

    // Recent Transactions (last 10)
    $recentStmt = $pdo->query("
        SELECT type, direction, amount, note, transaction_date
        FROM cash_transactions
        ORDER BY id DESC
        LIMIT 10
    ");
    $recentTransactions = $recentStmt->fetchAll();

    // Income vs Expense Chart Data (grouped by day within range)
    $chartStmt = $pdo->prepare("
        SELECT
            transaction_date,
            COALESCE(SUM(CASE WHEN type = 'sale' THEN amount ELSE 0 END), 0) AS income,
            COALESCE(SUM(CASE WHEN type = 'expense' THEN amount ELSE 0 END), 0) AS expense
        FROM cash_transactions
        WHERE transaction_date BETWEEN :start AND :end
        GROUP BY transaction_date
        ORDER BY transaction_date ASC
    ");
    $chartStmt->execute(['start' => $startDate, 'end' => $endDate]);
    $chartData = $chartStmt->fetchAll();

    jsonResponse(true, '', [
        'current_cash'    => round($currentCash, 2),
        'opening_balance' => round($openingBalance, 2),
        'cash_in'         => round((float) $rangeRow['cash_in'], 2),
        'cash_out'        => round((float) $rangeRow['cash_out'], 2),
        'todays_income'   => round($todaysIncome, 2),
        'todays_expense'  => round($todaysExpense, 2),
        'todays_profit'   => round($todaysProfit, 2),
        'customer_due'    => round($custDue, 2),
        'supplier_due'    => round($suppDue, 2),
        'recent'          => $recentTransactions,
        'chart'           => $chartData,
    ]);

} catch (PDOException $e) {
    jsonResponse(false, 'Failed to load dashboard data.');
}