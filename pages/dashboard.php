<?php
require_once __DIR__ . '/../includes/auth_check.php';
?>
<div id="dashboardPage">

    <!-- ============ HEADER BLOCK: Title + Period + Cards + Add Buttons (Fixed Size) ============ -->
    <div class="dashboard-header-block">

        <div class="dash-header-row">
            <div class="dash-header-text">
                <h4><?php echo lang('dashboard'); ?></h4>
                <p>Overview of your business performance</p>
            </div>
            <select class="period-select" id="periodSelect">
                <option value="today" selected>Today</option>
                <option value="7">Last 7 Days</option>
                <option value="30">Last 1 Month</option>
                <option value="365">Last 1 Year</option>
            </select>
        </div>

        <div class="row g-2 mb-3" id="summaryCardsRow"></div>

        <div class="d-flex justify-content-center gap-3 mb-1 flex-wrap">
            <button class="ck-btn ck-btn-primary" id="btnAddCustomer">
                <i class="fa-solid fa-user-plus"></i> <?php echo lang('add_customer'); ?>
            </button>
            <button class="ck-btn ck-btn-outline" id="btnAddSupplier">
                <i class="fa-solid fa-truck"></i> <?php echo lang('add_supplier'); ?>
            </button>
        </div>
    </div>

    <!-- ============ RECENT GRID: বাকি সব জায়গা নিবে, ভেতরের List Scroll হবে ============ -->
    <div class="recent-grid" id="recentGrid">
        <div class="ck-card recent-card">
            <div class="d-flex justify-content-between align-items-center mb-3 recent-card-head">
                <h6 class="mb-0" style="font-weight:600;"><?php echo lang('recent_purchase'); ?></h6>
                <i class="fa-solid fa-cart-shopping recent-nav-icon" id="goToPurchase" title="View Purchase List"></i>
            </div>
            <div class="recent-scroll-body" id="recentPurchaseList"></div>
        </div>
        <div class="ck-card recent-card">
            <div class="d-flex justify-content-between align-items-center mb-3 recent-card-head">
                <h6 class="mb-0" style="font-weight:600;"><?php echo lang('recent_sales'); ?></h6>
                <i class="fa-solid fa-tags recent-nav-icon" id="goToSales" title="View Sales List"></i>
            </div>
            <div class="recent-scroll-body" id="recentSalesList"></div>
        </div>
    </div>
</div>

<!-- ============ MODAL: ADD CUSTOMER ============ -->
<div class="ck-modal-overlay" id="addCustomerOverlay" style="display:none;">
    <div class="ck-modal-box">
        <div class="ck-modal-header">
            <h5><?php echo lang('add_customer'); ?></h5>
            <i class="fa-solid fa-xmark ck-modal-close" data-close="addCustomerOverlay"></i>
        </div>
        <form id="addCustomerForm">
            <label class="ck-label"><?php echo lang('name'); ?></label>
            <input type="text" class="ck-input" id="customerName" required>

            <label class="ck-label mt-2"><?php echo lang('mobile'); ?></label>
            <input type="text" class="ck-input" id="customerMobile" required>

            <label class="ck-label mt-2"><?php echo lang('address'); ?> <span class="text-muted">(optional)</span></label>
            <input type="text" class="ck-input" id="customerAddress">

            <div class="d-flex gap-2 mt-3">
                <button type="button" class="ck-btn ck-btn-outline flex-fill justify-content-center" data-close="addCustomerOverlay"><?php echo lang('cancel'); ?></button>
                <button type="submit" class="ck-btn ck-btn-primary flex-fill justify-content-center"><?php echo lang('save'); ?></button>
            </div>
            <button type="button" class="ck-btn ck-btn-outline w-100 justify-content-center mt-2" id="btnGoCustomerList">
                <i class="fa-solid fa-list"></i> View Customer List
            </button>
        </form>
    </div>
</div>

<!-- ============ MODAL: ADD SUPPLIER ============ -->
<div class="ck-modal-overlay" id="addSupplierOverlay" style="display:none;">
    <div class="ck-modal-box">
        <div class="ck-modal-header">
            <h5><?php echo lang('add_supplier'); ?></h5>
            <i class="fa-solid fa-xmark ck-modal-close" data-close="addSupplierOverlay"></i>
        </div>
        <form id="addSupplierForm">
            <label class="ck-label"><?php echo lang('name'); ?></label>
            <input type="text" class="ck-input" id="supplierName" required>

            <label class="ck-label mt-2"><?php echo lang('mobile'); ?></label>
            <input type="text" class="ck-input" id="supplierMobile" required>

            <label class="ck-label mt-2"><?php echo lang('address'); ?> <span class="text-muted">(optional)</span></label>
            <input type="text" class="ck-input" id="supplierAddress">

            <div class="d-flex gap-2 mt-3">
                <button type="button" class="ck-btn ck-btn-outline flex-fill justify-content-center" data-close="addSupplierOverlay"><?php echo lang('cancel'); ?></button>
                <button type="submit" class="ck-btn ck-btn-primary flex-fill justify-content-center"><?php echo lang('save'); ?></button>
            </div>
            <button type="button" class="ck-btn ck-btn-outline w-100 justify-content-center mt-2" id="btnGoSupplierList">
                <i class="fa-solid fa-list"></i> View Supplier List
            </button>
        </form>
    </div>
</div>

<!-- ============ MODAL: CHART POPUP ============ -->
<div class="ck-modal-overlay" id="chartOverlay" style="display:none;">
    <div class="ck-modal-box" style="max-width:640px;">
        <div class="ck-modal-header">
            <h5 id="chartModalTitle">Chart</h5>
            <i class="fa-solid fa-xmark ck-modal-close" data-close="chartOverlay"></i>
        </div>

        <div class="d-flex gap-2 mb-3" id="chartFilterButtons">
            <button class="ck-filter-btn active" data-days="7">Last 7 Days</button>
            <button class="ck-filter-btn" data-days="30">Last 30 Days</button>
            <button class="ck-filter-btn" data-days="365">Last 1 Year</button>
        </div>

        <div style="position:relative;height:280px;">
            <canvas id="dashboardChartCanvas"></canvas>
        </div>

        <div id="chartSummaryBox" class="mt-3 text-center text-muted" style="font-size:13px;"></div>

        <button type="button" class="ck-btn ck-btn-primary w-100 justify-content-center mt-3" id="chartViewListBtn">
            <i class="fa-solid fa-list"></i> <span id="chartViewListText">View List</span>
        </button>
    </div>
</div>

<!-- ============ PAGE-SPECIFIC STYLES ============ -->
<style>
    /* ============ পুরো Page Exactly Display-এর সমান Height-এ Fix ============ */
    #dashboardPage {
        display: flex;
        flex-direction: column;
        height: calc(100vh - var(--topbar-height) - 52px);
        height: calc(100svh - var(--topbar-height) - 52px);
    }
    @media (max-width: 991px) {
        #dashboardPage {
            height: calc(100vh - var(--topbar-height) - 108px);
            height: calc(100svh - var(--topbar-height) - 108px);
        }
    }

    .dashboard-header-block { flex-shrink: 0; }

    .dash-header-row {
        display: flex; justify-content: space-between; align-items: center;
        flex-wrap: nowrap; gap: 10px; margin-bottom: 14px;
    }
    .dash-header-text { min-width: 0; }
    .dash-header-text h4 {
        font-weight: 600; margin: 0 0 2px;
        white-space: nowrap; overflow: hidden; text-overflow: ellipsis;
    }
    .dash-header-text p {
        color: var(--text-muted); font-size: 13px; margin: 0;
        white-space: nowrap; overflow: hidden; text-overflow: ellipsis;
    }

    .period-select {
        border: 1.5px solid var(--border-color); border-radius: 10px; padding: 9px 14px;
        font-size: 12.5px; font-weight: 500; color: var(--text-dark); background: #fff;
        cursor: pointer; outline: none; flex-shrink: 0;
    }
    .period-select:focus { border-color: var(--primary-blue); }

    /* ============ Summary Cards ============ */
    .summary-card {
        background: #fff; border: 1px solid var(--border-color); border-radius: 14px;
        padding: 16px; cursor: pointer; transition: all 0.2s ease; height: 100%;
        display: flex; flex-direction: row-reverse; align-items: center; justify-content: space-between; gap: 10px;
    }
    .summary-card:hover { border-color: var(--primary-blue); box-shadow: 0 6px 18px rgba(37,99,235,0.1); transform: translateY(-2px); }
    .summary-card .sc-icon {
        width: 40px; height: 40px; border-radius: 10px; display: flex; align-items: center;
        justify-content: center; font-size: 15px; flex-shrink: 0;
    }
    .summary-card .sc-content { display: flex; flex-direction: column; gap: 4px; min-width: 0; }
    .summary-card .sc-label { font-size: 11px; color: var(--text-muted); white-space: nowrap; overflow: hidden; text-overflow: ellipsis; }
    .summary-card .sc-value { font-size: 16px; font-weight: 700; color: var(--text-dark); white-space: nowrap; overflow: hidden; text-overflow: ellipsis; }

    /* ============ Recent Grid: বাকি জায়গা নিবে, ভেতরে Scroll ============ */
    .recent-grid {
        flex: 1 1 auto;
        min-height: 0;
        display: grid;
        grid-template-columns: 1fr 1fr;
        gap: 16px;
        margin-top: 6px;
    }
    .recent-card {
        display: flex;
        flex-direction: column;
        overflow: hidden;
        height: 100%;
    }
    .recent-card-head { flex-shrink: 0; }
    .recent-scroll-body {
        flex: 1 1 auto;
        min-height: 0;
        overflow-y: auto;
        -webkit-overflow-scrolling: touch;
        padding-right: 4px;
        scrollbar-width: none;
        -ms-overflow-style: none;
    }
    .recent-scroll-body::-webkit-scrollbar { display: none; }

    .recent-nav-icon {
        cursor: pointer; color: var(--text-muted); font-size: 15px; transition: color 0.2s ease;
        width: 30px; height: 30px; display: flex; align-items: center; justify-content: center;
        border-radius: 8px;
    }
    .recent-nav-icon:hover { color: var(--primary-blue); background: var(--light-blue); }

    @media (max-width: 767px) {
        .dash-header-text h4 { font-size: 16px; }
        .dash-header-text p { font-size: 10.5px; }
        .period-select { padding: 7px 8px; font-size: 11px; }

        .summary-card { flex-direction: column; text-align: center; padding: 8px 4px; gap: 4px; }
        .summary-card .sc-icon { width: 26px; height: 26px; font-size: 11px; }
        .summary-card .sc-content { align-items: center; }
        .summary-card .sc-label { font-size: 8.5px; white-space: normal; }
        .summary-card .sc-value { font-size: 11px; }

        .recent-grid {
            display: flex;
            overflow-x: auto;
            scroll-snap-type: x mandatory;
            gap: 12px;
            padding-bottom: 4px;
            -webkit-overflow-scrolling: touch;
        }
        .recent-grid > .recent-card { flex: 0 0 100%; scroll-snap-align: start; }
    }

    @media (max-width: 380px) {
        .dash-header-text h4 { font-size: 14px; }
        .dash-header-text p { display: none; }
        .period-select { padding: 6px 6px; font-size: 10px; }
    }

    @media (max-width: 480px) {
        #chartFilterButtons { gap: 5px; }
        #chartFilterButtons .ck-filter-btn { padding: 5px 8px; font-size: 10px; }
    }
</style>

<!-- ============ PAGE-SPECIFIC SCRIPT ============ -->
<script>
(function () {
    const cardMeta = [
        { key: 'total_purchase', label: '<?php echo lang('total_purchase'); ?>', icon: 'fa-cart-shopping', color: '#2563eb', bg: '#eff6ff', type: 'purchase' },
        { key: 'total_sales',    label: '<?php echo lang('total_sales'); ?>',    icon: 'fa-tags',          color: '#16a34a', bg: '#f0fdf4', type: 'sales' },
        { key: 'total_profit',   label: '<?php echo lang('total_profit'); ?>',   icon: 'fa-chart-line',    color: '#7c3aed', bg: '#f5f3ff', type: 'profit' },
        { key: 'customer_due',   label: '<?php echo lang('customer_due'); ?>',   icon: 'fa-user-clock',    color: '#d97706', bg: '#fff7ed', type: 'customer_due' },
        { key: 'supplier_due',   label: '<?php echo lang('supplier_due'); ?>',   icon: 'fa-truck-fast',    color: '#dc2626', bg: '#fef2f2', type: 'supplier_due' },
        { key: 'total_expenses', label: '<?php echo lang('total_expenses'); ?>', icon: 'fa-receipt',       color: '#0891b2', bg: '#ecfeff', type: 'expenses' }
    ];

    const viewListMap = {
        purchase:     { page: 'purchase' },
        sales:        { page: 'sales' },
        profit:       { page: 'sales' },
        expenses:     { page: 'expenses' },
        customer_due: { page: 'settings', tab: 'customers' },
        supplier_due: { page: 'settings', tab: 'suppliers' }
    };

    let dashboardChart = null;
    let currentPeriod = 'today';

    function money(v) {
        return '৳' + parseFloat(v).toLocaleString('en-US', { minimumFractionDigits: 2, maximumFractionDigits: 2 });
    }

    function timeAgo(dateStr) {
        const date = new Date(dateStr.replace(' ', 'T'));
        const diff = Math.floor((new Date() - date) / 1000);
        if (diff < 60) return 'Just now';
        if (diff < 3600) return Math.floor(diff / 60) + 'm ago';
        if (diff < 86400) return Math.floor(diff / 3600) + 'h ago';
        return Math.floor(diff / 86400) + 'd ago';
    }

    function goToSettingsTab(tab) {
        loadPage('settings').then(() => {
            setTimeout(() => {
                const tabBtn = document.querySelector('.settings-tab[data-tab="' + tab + '"]');
                if (tabBtn) tabBtn.click();
            }, 300);
        });
    }

    async function loadDashboard(period) {
        if (period) currentPeriod = period;

        try {
            const res = await fetch('api/dashboard/summary.php?period=' + currentPeriod);
            const result = await res.json();

            if (result.status !== 'success') {
                ckToast('error', result.message || 'Failed to load dashboard');
                return;
            }

            const data = result.data;

            const cardsRow = document.getElementById('summaryCardsRow');
            cardsRow.innerHTML = cardMeta.map(m => `
                <div class="col-4">
                    <div class="summary-card" data-type="${m.type}" data-label="${m.label}">
                        <div class="sc-icon" style="background:${m.bg};color:${m.color};">
                            <i class="fa-solid ${m.icon}"></i>
                        </div>
                        <div class="sc-content">
                            <div class="sc-label">${m.label}</div>
                            <div class="sc-value">${money(data[m.key])}</div>
                        </div>
                    </div>
                </div>
            `).join('');

            document.querySelectorAll('.summary-card').forEach(card => {
                card.addEventListener('click', () => openChartModal(card.dataset.type, card.dataset.label));
            });

            updateCashBalance(data.cash_balance);

            const purchaseBox = document.getElementById('recentPurchaseList');
            if (data.recent_purchases.length === 0) {
                purchaseBox.innerHTML = `<p class="text-muted text-center py-4" style="font-size:13px;"><?php echo lang('no_data'); ?></p>`;
            } else {
                purchaseBox.innerHTML = data.recent_purchases.map(p => `
                    <div class="list-row">
                        <div>
                            <div class="lr-title">${p.product_name}</div>
                            <div class="lr-sub">${p.supplier_name ? p.supplier_name : 'No Supplier'} • Qty: ${p.quantity} • ${timeAgo(p.created_at)}</div>
                        </div>
                        <div class="text-end">
                            <div class="lr-amount">${money(p.total_amount)}</div>
                            <span class="${p.payment_type === 'cash' ? 'badge-cash' : 'badge-due'}">${p.payment_type === 'cash' ? '<?php echo lang('cash'); ?>' : '<?php echo lang('due'); ?>'}</span>
                        </div>
                    </div>
                `).join('');
            }

            const salesBox = document.getElementById('recentSalesList');
            if (data.recent_sales.length === 0) {
                salesBox.innerHTML = `<p class="text-muted text-center py-4" style="font-size:13px;"><?php echo lang('no_data'); ?></p>`;
            } else {
                salesBox.innerHTML = data.recent_sales.map(s => `
                    <div class="list-row">
                        <div>
                            <div class="lr-title">${s.product_name}</div>
                            <div class="lr-sub">${s.customer_name ? s.customer_name : 'Walk-in Customer'} • Qty: ${s.quantity} • ${timeAgo(s.created_at)}</div>
                        </div>
                        <div class="text-end">
                            <div class="lr-amount">${money(s.total_amount)}</div>
                            <span class="${s.payment_type === 'cash' ? 'badge-cash' : 'badge-due'}">${s.payment_type === 'cash' ? '<?php echo lang('cash'); ?>' : '<?php echo lang('due'); ?>'}</span>
                        </div>
                    </div>
                `).join('');
            }
        } catch (err) {
            ckToast('error', 'Something went wrong while loading dashboard');
        }
    }

    document.getElementById('periodSelect').addEventListener('change', function () {
        loadDashboard(this.value);
    });

    document.getElementById('goToPurchase').addEventListener('click', () => loadPage('purchase'));
    document.getElementById('goToSales').addEventListener('click', () => loadPage('sales'));

    document.getElementById('btnAddCustomer').addEventListener('click', () => {
        document.getElementById('addCustomerOverlay').style.display = 'flex';
    });
    document.getElementById('btnAddSupplier').addEventListener('click', () => {
        document.getElementById('addSupplierOverlay').style.display = 'flex';
    });
    document.querySelectorAll('.ck-modal-close, [data-close]').forEach(el => {
        el.addEventListener('click', function () {
            const target = this.dataset.close;
            if (target) document.getElementById(target).style.display = 'none';
        });
    });

    document.getElementById('btnGoCustomerList').addEventListener('click', () => {
        document.getElementById('addCustomerOverlay').style.display = 'none';
        goToSettingsTab('customers');
    });
    document.getElementById('btnGoSupplierList').addEventListener('click', () => {
        document.getElementById('addSupplierOverlay').style.display = 'none';
        goToSettingsTab('suppliers');
    });

    document.getElementById('addCustomerForm').addEventListener('submit', async function (e) {
        e.preventDefault();
        const payload = {
            name: document.getElementById('customerName').value.trim(),
            mobile: document.getElementById('customerMobile').value.trim(),
            address: document.getElementById('customerAddress').value.trim()
        };
        try {
            const res = await fetch('api/customer/add.php', {
                method: 'POST', headers: { 'Content-Type': 'application/json' }, body: JSON.stringify(payload)
            });
            const result = await res.json();
            if (result.status === 'success') {
                document.getElementById('addCustomerOverlay').style.display = 'none';
                document.getElementById('addCustomerForm').reset();
                ckToast('success', result.message);
            } else {
                ckToast('error', result.message);
            }
        } catch (err) {
            ckToast('error', 'Failed to add customer');
        }
    });

    document.getElementById('addSupplierForm').addEventListener('submit', async function (e) {
        e.preventDefault();
        const payload = {
            name: document.getElementById('supplierName').value.trim(),
            mobile: document.getElementById('supplierMobile').value.trim(),
            address: document.getElementById('supplierAddress').value.trim()
        };
        try {
            const res = await fetch('api/supplier/add.php', {
                method: 'POST', headers: { 'Content-Type': 'application/json' }, body: JSON.stringify(payload)
            });
            const result = await res.json();
            if (result.status === 'success') {
                document.getElementById('addSupplierOverlay').style.display = 'none';
                document.getElementById('addSupplierForm').reset();
                ckToast('success', result.message);
            } else {
                ckToast('error', result.message);
            }
        } catch (err) {
            ckToast('error', 'Failed to add supplier');
        }
    });

    let currentChartType = 'sales';

    async function openChartModal(type, label) {
        currentChartType = type;
        document.getElementById('chartModalTitle').textContent = label;
        document.getElementById('chartOverlay').style.display = 'flex';
        document.querySelectorAll('.ck-filter-btn').forEach(b => b.classList.remove('active'));
        document.querySelector('.ck-filter-btn[data-days="7"]').classList.add('active');

        const target = viewListMap[type];
        document.getElementById('chartViewListText').textContent = target.tab
            ? ('View ' + target.tab.charAt(0).toUpperCase() + target.tab.slice(1) + ' List')
            : 'View List';

        await renderChart(type, 7);
    }

    document.getElementById('chartViewListBtn').addEventListener('click', function () {
        document.getElementById('chartOverlay').style.display = 'none';
        const target = viewListMap[currentChartType];
        if (target.tab) {
            goToSettingsTab(target.tab);
        } else {
            loadPage(target.page);
        }
    });

    document.querySelectorAll('.ck-filter-btn').forEach(btn => {
        btn.addEventListener('click', function () {
            document.querySelectorAll('.ck-filter-btn').forEach(b => b.classList.remove('active'));
            this.classList.add('active');
            renderChart(currentChartType, parseInt(this.dataset.days));
        });
    });

    async function renderChart(type, days) {
        try {
            const res = await fetch(`api/dashboard/chart.php?type=${type}&days=${days}`);
            const result = await res.json();
            if (result.status !== 'success') { ckToast('error', 'Failed to load chart data'); return; }

            const ctx = document.getElementById('dashboardChartCanvas').getContext('2d');
            if (dashboardChart) dashboardChart.destroy();

            const total = result.values.reduce((a, b) => a + b, 0);
            document.getElementById('chartSummaryBox').textContent =
                `Total: ${money(total)} across ${result.labels.length} ${result.labels.length === 1 ? 'entry' : 'entries'}`;

            dashboardChart = new Chart(ctx, {
                type: result.chart_type === 'bar' ? 'bar' : 'line',
                data: {
                    labels: result.labels.length ? result.labels : ['No Data'],
                    datasets: [{
                        label: type, data: result.values.length ? result.values : [0],
                        borderColor: '#2563eb', backgroundColor: result.chart_type === 'bar' ? 'rgba(37,99,235,0.7)' : 'rgba(37,99,235,0.12)',
                        borderWidth: 2, fill: true, tension: 0.35, borderRadius: 6
                    }]
                },
                options: {
                    responsive: true, maintainAspectRatio: false,
                    plugins: { legend: { display: false } },
                    scales: { y: { beginAtZero: true, grid: { color: '#f1f5f9' } }, x: { grid: { display: false } } }
                }
            });
        } catch (err) {
            ckToast('error', 'Something went wrong while loading chart');
        }
    }

    loadDashboard('today');
})();
</script>