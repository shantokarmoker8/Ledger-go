<?php
require_once __DIR__ . '/../includes/auth_check.php';
?>
<!-- ============ DASHBOARD PAGE ============ -->
<div id="dashboardPage">

    <!-- Page Header -->
    <div class="d-flex justify-content-between align-items-center mb-3 flex-wrap gap-2">
        <div>
            <h4 class="mb-0" style="font-weight:600;"><?php echo lang('dashboard'); ?></h4>
            <p class="text-muted mb-0" style="font-size:13px;">Overview of your business performance</p>
        </div>
    </div>

    <!-- Summary Cards -->
    <div class="row g-3 mb-3" id="summaryCardsRow">
        <!-- Cards injected by JS -->
    </div>

    <!-- Add Customer / Add Supplier -->
    <div class="row g-3 mb-3">
        <div class="col-6 col-md-3">
            <button class="ck-btn ck-btn-primary w-100 justify-content-center" id="btnAddCustomer">
                <i class="fa-solid fa-user-plus"></i> <?php echo lang('add_customer'); ?>
            </button>
        </div>
        <div class="col-6 col-md-3">
            <button class="ck-btn ck-btn-outline w-100 justify-content-center" id="btnAddSupplier">
                <i class="fa-solid fa-truck"></i> <?php echo lang('add_supplier'); ?>
            </button>
        </div>
    </div>

    <!-- Recent Purchase / Sales -->
    <div class="row g-3">
        <div class="col-lg-6">
            <div class="ck-card">
                <div class="d-flex justify-content-between align-items-center mb-3">
                    <h6 class="mb-0" style="font-weight:600;"><?php echo lang('recent_purchase'); ?></h6>
                    <i class="fa-solid fa-cart-shopping text-muted"></i>
                </div>
                <div id="recentPurchaseList"></div>
            </div>
        </div>
        <div class="col-lg-6">
            <div class="ck-card">
                <div class="d-flex justify-content-between align-items-center mb-3">
                    <h6 class="mb-0" style="font-weight:600;"><?php echo lang('recent_sales'); ?></h6>
                    <i class="fa-solid fa-tags text-muted"></i>
                </div>
                <div id="recentSalesList"></div>
            </div>
        </div>
    </div>
</div>

<!-- ============ MODAL: OPENING CASH (first time only) ============ -->
<div class="ck-modal-overlay" id="openingCashOverlay" style="display:none;">
    <div class="ck-modal-box" style="max-width:420px;">
        <div class="text-center mb-3">
            <div style="width:60px;height:60px;background:#eff6ff;border-radius:16px;display:flex;align-items:center;justify-content:center;margin:0 auto 14px;">
                <i class="fa-solid fa-sack-dollar" style="font-size:24px;color:#2563eb;"></i>
            </div>
            <h5 style="font-weight:600;">Set Opening Cash Balance</h5>
            <p class="text-muted" style="font-size:13px;">Enter your business's starting cash amount to begin.</p>
        </div>
        <form id="openingCashForm">
            <label class="ck-label">Opening Cash Amount</label>
            <input type="number" step="0.01" min="0" class="ck-input" id="openingCashInput" placeholder="e.g. 10000" required>
            <button type="submit" class="ck-btn ck-btn-primary w-100 justify-content-center mt-3">
                <i class="fa-solid fa-check"></i> Save & Continue
            </button>
        </form>
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
    </div>
</div>

<!-- ============ PAGE-SPECIFIC STYLES ============ -->
<style>
    .summary-card {
        background: #fff;
        border: 1px solid var(--border-color);
        border-radius: 14px;
        padding: 18px;
        cursor: pointer;
        transition: all 0.2s ease;
        position: relative;
        overflow: hidden;
    }
    .summary-card:hover { border-color: var(--primary-blue); box-shadow: 0 6px 18px rgba(37,99,235,0.1); transform: translateY(-2px); }
    .summary-card .sc-icon {
        width: 42px; height: 42px; border-radius: 10px;
        display: flex; align-items: center; justify-content: center;
        font-size: 16px; margin-bottom: 14px;
    }
    .summary-card .sc-label { font-size: 12px; color: var(--text-muted); margin-bottom: 4px; }
    .summary-card .sc-value { font-size: 19px; font-weight: 700; color: var(--text-dark); }

    .ck-modal-overlay {
        position: fixed; inset: 0; background: rgba(15,23,42,0.45);
        z-index: 2000; display: flex; align-items: center; justify-content: center; padding: 16px;
    }
    .ck-modal-box {
        background: #fff; border-radius: 16px; padding: 24px; width: 100%; max-width: 460px;
        max-height: 90vh; overflow-y: auto;
    }
    .ck-modal-header { display: flex; justify-content: space-between; align-items: center; margin-bottom: 18px; }
    .ck-modal-header h5 { font-weight: 600; margin: 0; }
    .ck-modal-close { cursor: pointer; color: var(--text-muted); font-size: 16px; transition: color 0.2s ease; }
    .ck-modal-close:hover { color: var(--danger); }

    .ck-label { font-size: 12px; font-weight: 500; color: var(--text-dark); display: block; margin-bottom: 6px; }
    .ck-input, .ck-select {
        width: 100%; padding: 10px 14px; border: 1.5px solid var(--border-color);
        border-radius: 10px; font-size: 14px; outline: none; transition: all 0.2s ease;
    }
    .ck-input:focus, .ck-select:focus { border-color: var(--primary-blue); box-shadow: 0 0 0 3px rgba(37,99,235,0.1); }

    .ck-filter-btn {
        border: 1.5px solid var(--border-color); background: #fff; color: var(--text-muted);
        padding: 7px 14px; border-radius: 8px; font-size: 12px; font-weight: 500; cursor: pointer; transition: all 0.2s ease;
    }
    .ck-filter-btn.active { background: var(--primary-blue); border-color: var(--primary-blue); color: #fff; }

    .list-row {
        display: flex; justify-content: space-between; align-items: center;
        padding: 12px 0; border-bottom: 1px solid var(--border-color); font-size: 13px;
    }
    .list-row:last-child { border-bottom: none; }
    .list-row .lr-title { font-weight: 500; color: var(--text-dark); }
    .list-row .lr-sub { color: var(--text-muted); font-size: 11px; margin-top: 2px; }
    .list-row .lr-amount { font-weight: 600; }
    .badge-cash { background: #f0fdf4; color: #16a34a; font-size: 10px; padding: 3px 8px; border-radius: 6px; }
    .badge-due { background: #fff7ed; color: #d97706; font-size: 10px; padding: 3px 8px; border-radius: 6px; }

    @media (max-width: 991px) {
        .summary-card .sc-value { font-size: 16px; }
    }
</style>

<!-- ============ PAGE-SPECIFIC SCRIPT ============ -->
<script>
(function () {
    // Card meta configuration (icon, color, label key, type for chart API)
    const cardMeta = [
        { key: 'total_purchase', label: '<?php echo lang('total_purchase'); ?>', icon: 'fa-cart-shopping', color: '#2563eb', bg: '#eff6ff', type: 'purchase' },
        { key: 'total_sales',    label: '<?php echo lang('total_sales'); ?>',    icon: 'fa-tags',          color: '#16a34a', bg: '#f0fdf4', type: 'sales' },
        { key: 'total_profit',   label: '<?php echo lang('total_profit'); ?>',   icon: 'fa-chart-line',    color: '#7c3aed', bg: '#f5f3ff', type: 'profit' },
        { key: 'customer_due',   label: '<?php echo lang('customer_due'); ?>',   icon: 'fa-user-clock',    color: '#d97706', bg: '#fff7ed', type: 'customer_due' },
        { key: 'supplier_due',   label: '<?php echo lang('supplier_due'); ?>',   icon: 'fa-truck-fast',    color: '#dc2626', bg: '#fef2f2', type: 'supplier_due' },
        { key: 'total_expenses', label: '<?php echo lang('total_expenses'); ?>', icon: 'fa-receipt',       color: '#0891b2', bg: '#ecfeff', type: 'expenses' }
    ];

    let dashboardChart = null;

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

    /**
     * Fetch dashboard summary and render everything
     */
    async function loadDashboard() {
        try {
            const res = await fetch('api/dashboard/summary.php');
            const result = await res.json();

            if (result.status !== 'success') {
                ckToast('error', result.message || 'Failed to load dashboard');
                return;
            }

            const data = result.data;

            // Show Opening Cash Modal if not set yet
            if (data.opening_cash_set === 0) {
                document.getElementById('openingCashOverlay').style.display = 'flex';
            }

            // Render Summary Cards
            const cardsRow = document.getElementById('summaryCardsRow');
            cardsRow.innerHTML = cardMeta.map(m => `
                <div class="col-6 col-lg-4">
                    <div class="summary-card" data-type="${m.type}" data-label="${m.label}">
                        <div class="sc-icon" style="background:${m.bg};color:${m.color};">
                            <i class="fa-solid ${m.icon}"></i>
                        </div>
                        <div class="sc-label">${m.label}</div>
                        <div class="sc-value">${money(data[m.key])}</div>
                    </div>
                </div>
            `).join('');

            gsap.from('.summary-card', { y: 14, opacity: 0, duration: 0.4, stagger: 0.06, ease: "power2.out" });

            // Bind card click -> open chart modal
            document.querySelectorAll('.summary-card').forEach(card => {
                card.addEventListener('click', () => openChartModal(card.dataset.type, card.dataset.label));
            });

            // Update Top Bar Cash Balance
            updateCashBalance(data.cash_balance);

            // Recent Purchase List
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

            // Recent Sales List
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

    /**
     * Opening Cash Form Submit
     */
    document.getElementById('openingCashForm').addEventListener('submit', async function (e) {
        e.preventDefault();
        const amount = document.getElementById('openingCashInput').value;

        try {
            const res = await fetch('api/settings/opening_cash.php', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify({ amount: amount })
            });
            const result = await res.json();

            if (result.status === 'success') {
                document.getElementById('openingCashOverlay').style.display = 'none';
                ckToast('success', result.message);
                loadDashboard();
            } else {
                ckToast('error', result.message);
            }
        } catch (err) {
            ckToast('error', 'Failed to save opening cash');
        }
    });

    /**
     * Modal Open/Close Helpers
     */
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

    /**
     * Add Customer Form Submit
     */
    document.getElementById('addCustomerForm').addEventListener('submit', async function (e) {
        e.preventDefault();
        const payload = {
            name: document.getElementById('customerName').value.trim(),
            mobile: document.getElementById('customerMobile').value.trim(),
            address: document.getElementById('customerAddress').value.trim()
        };

        try {
            const res = await fetch('api/customer/add.php', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify(payload)
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

    /**
     * Add Supplier Form Submit
     */
    document.getElementById('addSupplierForm').addEventListener('submit', async function (e) {
        e.preventDefault();
        const payload = {
            name: document.getElementById('supplierName').value.trim(),
            mobile: document.getElementById('supplierMobile').value.trim(),
            address: document.getElementById('supplierAddress').value.trim()
        };

        try {
            const res = await fetch('api/supplier/add.php', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify(payload)
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

    /**
     * Chart Modal
     */
    let currentChartType = 'sales';

    async function openChartModal(type, label) {
        currentChartType = type;
        document.getElementById('chartModalTitle').textContent = label;
        document.getElementById('chartOverlay').style.display = 'flex';

        document.querySelectorAll('.ck-filter-btn').forEach(b => b.classList.remove('active'));
        document.querySelector('.ck-filter-btn[data-days="7"]').classList.add('active');

        await renderChart(type, 7);
    }

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

            if (result.status !== 'success') {
                ckToast('error', 'Failed to load chart data');
                return;
            }

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
                        label: type,
                        data: result.values.length ? result.values : [0],
                        borderColor: '#2563eb',
                        backgroundColor: result.chart_type === 'bar' ? 'rgba(37,99,235,0.7)' : 'rgba(37,99,235,0.12)',
                        borderWidth: 2,
                        fill: true,
                        tension: 0.35,
                        borderRadius: 6
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    plugins: { legend: { display: false } },
                    scales: {
                        y: { beginAtZero: true, grid: { color: '#f1f5f9' } },
                        x: { grid: { display: false } }
                    }
                }
            });
        } catch (err) {
            ckToast('error', 'Something went wrong while loading chart');
        }
    }

    // Initial Load
    loadDashboard();
})();
</script>