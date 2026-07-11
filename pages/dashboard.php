<?php
declare(strict_types=1);
require_once __DIR__ . '/../includes/functions.php';
requireAuth();
$lang = loadLangFile();
?>
<div id="dashboardPage">

    <!-- Filter Bar -->
    <div class="d-flex flex-wrap justify-content-between align-items-center mb-3 gap-2">
        <h5 class="fw-bold mb-0"><?= $lang['dashboard'] ?></h5>
        <select class="form-select form-select-sm w-auto" id="dashboardFilter">
            <option value="today"><?= $lang['today'] ?></option>
            <option value="7days"><?= $lang['last_7_days'] ?></option>
            <option value="30days"><?= $lang['last_30_days'] ?></option>
            <option value="month"><?= $lang['this_month'] ?></option>
            <option value="year"><?= $lang['this_year'] ?></option>
        </select>
    </div>

    <!-- Summary Cards -->
    <div class="row g-3 mb-3" id="summaryCards">
        <div class="col-6 col-md-4 col-xl-3">
            <div class="card p-3 h-100">
                <div class="text-muted small"><?= $lang['current_cash'] ?></div>
                <div class="fs-5 fw-bold text-primary" id="card_current_cash">৳0</div>
            </div>
        </div>
        <div class="col-6 col-md-4 col-xl-3">
            <div class="card p-3 h-100">
                <div class="text-muted small"><?= $lang['opening_balance'] ?></div>
                <div class="fs-5 fw-bold" id="card_opening_balance">৳0</div>
            </div>
        </div>
        <div class="col-6 col-md-4 col-xl-3">
            <div class="card p-3 h-100">
                <div class="text-muted small"><?= $lang['cash_in'] ?></div>
                <div class="fs-5 fw-bold text-success" id="card_cash_in">৳0</div>
            </div>
        </div>
        <div class="col-6 col-md-4 col-xl-3">
            <div class="card p-3 h-100">
                <div class="text-muted small"><?= $lang['cash_out'] ?></div>
                <div class="fs-5 fw-bold text-danger" id="card_cash_out">৳0</div>
            </div>
        </div>
        <div class="col-6 col-md-4 col-xl-3">
            <div class="card p-3 h-100">
                <div class="text-muted small"><?= $lang['todays_income'] ?></div>
                <div class="fs-5 fw-bold text-success" id="card_todays_income">৳0</div>
            </div>
        </div>
        <div class="col-6 col-md-4 col-xl-3">
            <div class="card p-3 h-100">
                <div class="text-muted small"><?= $lang['todays_expense'] ?></div>
                <div class="fs-5 fw-bold text-danger" id="card_todays_expense">৳0</div>
            </div>
        </div>
        <div class="col-6 col-md-4 col-xl-3">
            <div class="card p-3 h-100">
                <div class="text-muted small"><?= $lang['todays_profit'] ?></div>
                <div class="fs-5 fw-bold text-primary" id="card_todays_profit">৳0</div>
            </div>
        </div>
        <div class="col-6 col-md-4 col-xl-3">
            <div class="card p-3 h-100">
                <div class="text-muted small"><?= $lang['customer_due'] ?></div>
                <div class="fs-5 fw-bold text-warning" id="card_customer_due">৳0</div>
            </div>
        </div>
        <div class="col-6 col-md-4 col-xl-3">
            <div class="card p-3 h-100">
                <div class="text-muted small"><?= $lang['supplier_due'] ?></div>
                <div class="fs-5 fw-bold text-warning" id="card_supplier_due">৳0</div>
            </div>
        </div>
    </div>

    <div class="row g-3">
        <!-- Chart -->
        <div class="col-lg-7">
            <div class="card p-3 h-100">
                <h6 class="fw-bold mb-3"><?= $lang['income_vs_expense'] ?></h6>
                <canvas id="incomeExpenseChart" height="180"></canvas>
            </div>
        </div>

        <!-- Recent Transactions -->
        <div class="col-lg-5">
            <div class="card p-3 h-100">
                <h6 class="fw-bold mb-3"><?= $lang['recent_transactions'] ?></h6>
                <div class="table-responsive" style="max-height: 320px; overflow-y:auto;">
                    <table class="table table-sm align-middle mb-0">
                        <thead>
                            <tr>
                                <th><?= $lang['date'] ?></th>
                                <th><?= $lang['type'] ?></th>
                                <th class="text-end"><?= $lang['amount'] ?></th>
                            </tr>
                        </thead>
                        <tbody id="recentTransactionsBody">
                            <tr><td colspan="3" class="text-center text-muted py-3"><?= $lang['no_data_found'] ?></td></tr>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
(function initDashboard() {
    let incomeExpenseChartInstance = null;

    async function fetchDashboardData(filter = 'today') {
        const data = await apiRequest(`api/dashboard/summary.php?filter=${filter}`);
        if (!data || !data.success) return;
        renderCards(data);
        renderRecentTransactions(data.recent);
        renderChart(data.chart);
    }

    function formatMoney(num) {
        return '৳' + Number(num).toLocaleString('en-BD', { minimumFractionDigits: 2, maximumFractionDigits: 2 });
    }

    function renderCards(data) {
        document.getElementById('card_current_cash').textContent = formatMoney(data.current_cash);
        document.getElementById('card_opening_balance').textContent = formatMoney(data.opening_balance);
        document.getElementById('card_cash_in').textContent = formatMoney(data.cash_in);
        document.getElementById('card_cash_out').textContent = formatMoney(data.cash_out);
        document.getElementById('card_todays_income').textContent = formatMoney(data.todays_income);
        document.getElementById('card_todays_expense').textContent = formatMoney(data.todays_expense);
        document.getElementById('card_todays_profit').textContent = formatMoney(data.todays_profit);
        document.getElementById('card_customer_due').textContent = formatMoney(data.customer_due);
        document.getElementById('card_supplier_due').textContent = formatMoney(data.supplier_due);
    }

    function renderRecentTransactions(list) {
        const tbody = document.getElementById('recentTransactionsBody');
        if (!list || list.length === 0) {
            tbody.innerHTML = `<tr><td colspan="3" class="text-center text-muted py-3">No data found</td></tr>`;
            return;
        }
        tbody.innerHTML = list.map(tx => `
            <tr>
                <td class="small">${tx.transaction_date}</td>
                <td class="small text-capitalize">${tx.type.replace('_', ' ')}</td>
                <td class="text-end small ${tx.direction === 'in' ? 'text-success' : 'text-danger'}">
                    ${tx.direction === 'in' ? '+' : '-'}${formatMoney(tx.amount)}
                </td>
            </tr>
        `).join('');
    }

    function renderChart(chartData) {
        const ctx = document.getElementById('incomeExpenseChart');
        const labels = chartData.map(d => d.transaction_date);
        const income = chartData.map(d => parseFloat(d.income));
        const expense = chartData.map(d => parseFloat(d.expense));

        if (incomeExpenseChartInstance) incomeExpenseChartInstance.destroy();

        incomeExpenseChartInstance = new Chart(ctx, {
            type: 'line',
            data: {
                labels: labels.length ? labels : ['No Data'],
                datasets: [
                    {
                        label: 'Income',
                        data: income.length ? income : [0],
                        borderColor: '#2F5BE0',
                        backgroundColor: 'rgba(47,91,224,0.1)',
                        tension: 0.3,
                        fill: true,
                    },
                    {
                        label: 'Expense',
                        data: expense.length ? expense : [0],
                        borderColor: '#dc3545',
                        backgroundColor: 'rgba(220,53,69,0.1)',
                        tension: 0.3,
                        fill: true,
                    },
                ],
            },
            options: {
                responsive: true,
                plugins: { legend: { position: 'bottom' } },
                scales: { y: { beginAtZero: true } },
            },
        });
    }

    document.getElementById('dashboardFilter').addEventListener('change', (e) => {
        fetchDashboardData(e.target.value);
    });

    fetchDashboardData('today');
})();
</script>