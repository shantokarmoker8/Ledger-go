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
                <i class="fa-solid fa-tags recent-nav-icon" id="goToSales" title="View Sales History"></i>
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

<!-- ============ MODAL: CUSTOMER LIST (Direct Popup — Settings-এ যেতে হবে না) ============ -->
<div class="ck-modal-overlay" id="customerListOverlay" style="display:none;">
    <div class="ck-modal-box" style="max-width:640px;">
        <div class="ck-modal-header">
            <h5><?php echo lang('customer'); ?></h5>
            <i class="fa-solid fa-xmark ck-modal-close" data-close="customerListOverlay"></i>
        </div>
        <div class="ck-card mb-3">
            <div class="input-group-search">
                <i class="fa-solid fa-magnifying-glass"></i>
                <input type="text" id="clSearchInput" placeholder="<?php echo lang('search'); ?> customers...">
            </div>
        </div>
        <div class="ck-card p-0">
            <div class="fixed-scroll-area-cl">
                <table class="ck-table">
                    <thead>
                        <tr>
                            <th><?php echo lang('name'); ?></th>
                            <th><?php echo lang('mobile'); ?></th>
                            <th><?php echo lang('customer_due'); ?></th>
                            <th><?php echo lang('action'); ?></th>
                        </tr>
                    </thead>
                    <tbody id="clTableBody">
                        <tr><td colspan="4" class="text-center py-4 text-muted">Loading...</td></tr>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

<!-- ============ MODAL: SUPPLIER LIST (Direct Popup) ============ -->
<div class="ck-modal-overlay" id="supplierListOverlay" style="display:none;">
    <div class="ck-modal-box" style="max-width:640px;">
        <div class="ck-modal-header">
            <h5><?php echo lang('supplier'); ?></h5>
            <i class="fa-solid fa-xmark ck-modal-close" data-close="supplierListOverlay"></i>
        </div>
        <div class="ck-card mb-3">
            <div class="input-group-search">
                <i class="fa-solid fa-magnifying-glass"></i>
                <input type="text" id="slSearchInput" placeholder="<?php echo lang('search'); ?> suppliers...">
            </div>
        </div>
        <div class="ck-card p-0">
            <div class="fixed-scroll-area-cl">
                <table class="ck-table">
                    <thead>
                        <tr>
                            <th><?php echo lang('name'); ?></th>
                            <th><?php echo lang('mobile'); ?></th>
                            <th><?php echo lang('supplier_due'); ?></th>
                            <th><?php echo lang('action'); ?></th>
                        </tr>
                    </thead>
                    <tbody id="slTableBody">
                        <tr><td colspan="4" class="text-center py-4 text-muted">Loading...</td></tr>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

<!-- ============ SUB-MODAL: RECEIVE PAYMENT (CUSTOMER) ============ -->
<div class="ck-modal-overlay" id="clPaymentOverlay" style="display:none;z-index:2100;">
    <div class="ck-modal-box" style="max-width:400px;">
        <div class="ck-modal-header">
            <h5><?php echo lang('receive_payment'); ?></h5>
            <i class="fa-solid fa-xmark ck-modal-close" data-close="clPaymentOverlay"></i>
        </div>
        <form id="clPaymentForm">
            <input type="hidden" id="clPaymentCustomerId">
            <p style="font-size:13px;"><?php echo lang('customer'); ?>: <strong id="clPaymentCustomerName"></strong></p>
            <p class="text-muted" style="font-size:12px;"><?php echo lang('current_due'); ?>: <span id="clPaymentCustomerDue" style="font-weight:600;color:var(--danger);"></span></p>

            <label class="ck-label mt-2"><?php echo lang('amount'); ?></label>
            <input type="number" step="0.01" min="0.01" class="ck-input" id="clPaymentAmount" required>

            <div class="d-flex gap-2 mt-3">
                <button type="button" class="ck-btn ck-btn-outline flex-fill justify-content-center" data-close="clPaymentOverlay"><?php echo lang('cancel'); ?></button>
                <button type="submit" class="ck-btn ck-btn-primary flex-fill justify-content-center"><?php echo lang('save'); ?></button>
            </div>
        </form>
    </div>
</div>

<!-- ============ SUB-MODAL: MAKE PAYMENT (SUPPLIER) ============ -->
<div class="ck-modal-overlay" id="slPaymentOverlay" style="display:none;z-index:2100;">
    <div class="ck-modal-box" style="max-width:400px;">
        <div class="ck-modal-header">
            <h5><?php echo lang('make_payment'); ?></h5>
            <i class="fa-solid fa-xmark ck-modal-close" data-close="slPaymentOverlay"></i>
        </div>
        <form id="slPaymentForm">
            <input type="hidden" id="slPaymentSupplierId">
            <p style="font-size:13px;"><?php echo lang('supplier'); ?>: <strong id="slPaymentSupplierName"></strong></p>
            <p class="text-muted" style="font-size:12px;"><?php echo lang('current_due'); ?>: <span id="slPaymentSupplierDue" style="font-weight:600;color:var(--danger);"></span></p>

            <label class="ck-label mt-2"><?php echo lang('amount'); ?></label>
            <input type="number" step="0.01" min="0.01" class="ck-input" id="slPaymentAmount" required>

            <div class="d-flex gap-2 mt-3">
                <button type="button" class="ck-btn ck-btn-outline flex-fill justify-content-center" data-close="slPaymentOverlay"><?php echo lang('cancel'); ?></button>
                <button type="submit" class="ck-btn ck-btn-primary flex-fill justify-content-center"><?php echo lang('save'); ?></button>
            </div>
        </form>
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

    /* ============ Recent Grid ============ */
    .recent-grid {
        flex: 1 1 auto; min-height: 0; display: grid;
        grid-template-columns: 1fr 1fr; gap: 16px; margin-top: 6px;
    }
    .recent-card { display: flex; flex-direction: column; overflow: hidden; height: 100%; }
    .recent-card-head { flex-shrink: 0; }
    .recent-scroll-body {
        flex: 1 1 auto; min-height: 0; overflow-y: auto;
        -webkit-overflow-scrolling: touch; padding-right: 4px;
        scrollbar-width: none; -ms-overflow-style: none;
    }
    .recent-scroll-body::-webkit-scrollbar { display: none; }

    .recent-nav-icon {
        cursor: pointer; color: var(--text-muted); font-size: 15px; transition: color 0.2s ease;
        width: 30px; height: 30px; display: flex; align-items: center; justify-content: center;
        border-radius: 8px;
    }
    .recent-nav-icon:hover { color: var(--primary-blue); background: var(--light-blue); }

    /* ============ Customer/Supplier List Modal Scroll (Scrollbar লুকানো) ============ */
    .fixed-scroll-area-cl {
        max-height: 400px; overflow-y: auto;
        scrollbar-width: none; -ms-overflow-style: none;
    }
    .fixed-scroll-area-cl::-webkit-scrollbar { display: none; }

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
            display: flex; overflow-x: auto; scroll-snap-type: x mandatory;
            gap: 12px; padding-bottom: 4px; -webkit-overflow-scrolling: touch;
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

    let dashboardChart = null;
    let currentPeriod = 'today';
    let currentChartType = 'sales';

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

    /* ============ Sales Page-এ গিয়ে সরাসরি History View-এ যাওয়া ============ */
    function goToSalesHistory() {
        loadPage('sales').then(() => {
            setTimeout(() => {
                const btn = document.getElementById('btnToggleHistory');
                if (btn) btn.click();
            }, 300);
        });
    }

    /* ============ DASHBOARD DATA LOAD ============ */
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

            /* ============ Recent Purchase: due_amount দিয়ে Badge (payment_type নয়) ============ */
            const purchaseBox = document.getElementById('recentPurchaseList');
            if (data.recent_purchases.length === 0) {
                purchaseBox.innerHTML = `<p class="text-muted text-center py-4" style="font-size:13px;"><?php echo lang('no_data'); ?></p>`;
            } else {
                purchaseBox.innerHTML = data.recent_purchases.map(p => {
                    const isDue = parseFloat(p.due_amount) > 0;
                    return `
                    <div class="list-row">
                        <div>
                            <div class="lr-title">${p.product_name}</div>
                            <div class="lr-sub">${p.supplier_name ? p.supplier_name : 'No Supplier'} • Qty: ${p.quantity} • ${timeAgo(p.created_at)}</div>
                        </div>
                        <div class="text-end">
                            <div class="lr-amount">${money(p.total_amount)}</div>
                            <span class="${isDue ? 'badge-due' : 'badge-cash'}">${isDue ? '<?php echo lang('due'); ?>' : '<?php echo lang('cash'); ?>'}</span>
                        </div>
                    </div>
                `;
                }).join('');
            }

            /* ============ Recent Sales: due_amount দিয়ে Badge (payment_type নয়) ============ */
            const salesBox = document.getElementById('recentSalesList');
            if (data.recent_sales.length === 0) {
                salesBox.innerHTML = `<p class="text-muted text-center py-4" style="font-size:13px;"><?php echo lang('no_data'); ?></p>`;
            } else {
                salesBox.innerHTML = data.recent_sales.map(s => {
                    const isDue = parseFloat(s.due_amount) > 0;
                    return `
                    <div class="list-row">
                        <div>
                            <div class="lr-title">${s.product_name}</div>
                            <div class="lr-sub">${s.customer_name ? s.customer_name : 'Walk-in Customer'} • Qty: ${s.quantity} • ${timeAgo(s.created_at)}</div>
                        </div>
                        <div class="text-end">
                            <div class="lr-amount">${money(s.total_amount)}</div>
                            <span class="${isDue ? 'badge-due' : 'badge-cash'}">${isDue ? '<?php echo lang('due'); ?>' : '<?php echo lang('cash'); ?>'}</span>
                        </div>
                    </div>
                `;
                }).join('');
            }
        } catch (err) {
            ckToast('error', 'Something went wrong while loading dashboard');
        }
    }

    document.getElementById('periodSelect').addEventListener('change', function () {
        loadDashboard(this.value);
    });

    document.getElementById('goToPurchase').addEventListener('click', () => loadPage('purchase'));
    document.getElementById('goToSales').addEventListener('click', () => goToSalesHistory());

    /* ============ ADD CUSTOMER / SUPPLIER ============ */
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
    document.querySelectorAll('.ck-modal-overlay').forEach(overlay => {
        overlay.addEventListener('click', function (e) {
            if (e.target === overlay) overlay.style.display = 'none';
        });
    });

    document.getElementById('btnGoCustomerList').addEventListener('click', () => {
        document.getElementById('addCustomerOverlay').style.display = 'none';
        openCustomerListModal();
    });
    document.getElementById('btnGoSupplierList').addEventListener('click', () => {
        document.getElementById('addSupplierOverlay').style.display = 'none';
        openSupplierListModal();
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

    /* ============ CUSTOMER LIST MODAL (Direct Popup) ============ */
    window.openCustomerListModal = function () {
        document.getElementById('customerListOverlay').style.display = 'flex';
        loadCustomerListModal();
    };

    async function loadCustomerListModal(search = '') {
        const tbody = document.getElementById('clTableBody');
        try {
            const res = await fetch('api/customer/list.php?search=' + encodeURIComponent(search));
            const result = await res.json();
            if (result.status !== 'success' || result.data.length === 0) {
                tbody.innerHTML = `<tr><td colspan="4" class="text-center py-4 text-muted"><?php echo lang('no_data'); ?></td></tr>`;
                return;
            }
            tbody.innerHTML = result.data.map(c => `
                <tr>
                    <td data-label="<?php echo lang('name'); ?>" style="font-weight:500;">${c.name}</td>
                    <td data-label="<?php echo lang('mobile'); ?>">${c.mobile}</td>
                    <td data-label="<?php echo lang('customer_due'); ?>" style="font-weight:600;color:${c.due > 0 ? 'var(--danger)' : 'var(--success)'};">${money(c.due)}</td>
                    <td data-label="<?php echo lang('action'); ?>">
                        <div class="d-flex gap-2 justify-content-end">
                            ${c.due > 0 ? `<button class="ck-btn ck-btn-primary" style="padding:6px 12px;font-size:11px;" onclick="openClPayment(${c.id}, '${c.name.replace(/'/g, "\\'")}', ${c.due})"><i class="fa-solid fa-hand-holding-dollar"></i> <?php echo lang('receive_payment'); ?></button>` : ''}
                            <button class="icon-btn ck-btn-danger-soft" onclick="deleteClCustomer(${c.id}, ${c.due})"><i class="fa-solid fa-trash"></i></button>
                        </div>
                    </td>
                </tr>
            `).join('');
        } catch (err) {
            tbody.innerHTML = `<tr><td colspan="4" class="text-center py-4 text-danger">Error</td></tr>`;
        }
    }

    window.openClPayment = function (id, name, due) {
        document.getElementById('clPaymentCustomerId').value = id;
        document.getElementById('clPaymentCustomerName').textContent = name;
        document.getElementById('clPaymentCustomerDue').textContent = money(due);
        document.getElementById('clPaymentAmount').value = '';
        document.getElementById('clPaymentAmount').max = due;
        document.getElementById('clPaymentOverlay').style.display = 'flex';
    };

    document.getElementById('clPaymentForm').addEventListener('submit', async function (e) {
        e.preventDefault();
        const payload = {
            customer_id: document.getElementById('clPaymentCustomerId').value,
            amount: document.getElementById('clPaymentAmount').value
        };
        try {
            const res = await fetch('api/customer/payment.php', {
                method: 'POST', headers: { 'Content-Type': 'application/json' }, body: JSON.stringify(payload)
            });
            const result = await res.json();
            if (result.status === 'success') {
                ckToast('success', result.message);
                document.getElementById('clPaymentOverlay').style.display = 'none';
                updateCashBalance(result.cash_balance);
                loadCustomerListModal(document.getElementById('clSearchInput').value);
                loadDashboard();
            } else {
                ckToast('error', result.message);
            }
        } catch (err) { ckToast('error', 'Failed to process payment'); }
    });

    window.deleteClCustomer = async function (id, due) {
        if (due > 0) {
            ckToast('warning', 'Cannot delete: this customer has pending due. Clear due first.');
            return;
        }
        const confirmResult = await ckConfirm('This customer will be permanently deleted.');
        if (!confirmResult.isConfirmed) return;
        try {
            const res = await fetch('api/customer/delete.php', {
                method: 'POST', headers: { 'Content-Type': 'application/json' }, body: JSON.stringify({ id })
            });
            const result = await res.json();
            if (result.status === 'success') {
                ckToast('success', result.message);
                loadCustomerListModal(document.getElementById('clSearchInput').value);
            } else {
                ckToast('error', result.message);
            }
        } catch (err) { ckToast('error', 'Failed to delete customer'); }
    };

    let clSearchTimer;
    document.getElementById('clSearchInput').addEventListener('input', function () {
        clearTimeout(clSearchTimer);
        const val = this.value;
        clSearchTimer = setTimeout(() => loadCustomerListModal(val), 350);
    });

    /* ============ SUPPLIER LIST MODAL (Direct Popup) ============ */
    window.openSupplierListModal = function () {
        document.getElementById('supplierListOverlay').style.display = 'flex';
        loadSupplierListModal();
    };

    async function loadSupplierListModal(search = '') {
        const tbody = document.getElementById('slTableBody');
        try {
            const res = await fetch('api/supplier/list.php?search=' + encodeURIComponent(search));
            const result = await res.json();
            if (result.status !== 'success' || result.data.length === 0) {
                tbody.innerHTML = `<tr><td colspan="4" class="text-center py-4 text-muted"><?php echo lang('no_data'); ?></td></tr>`;
                return;
            }
            tbody.innerHTML = result.data.map(s => `
                <tr>
                    <td data-label="<?php echo lang('name'); ?>" style="font-weight:500;">${s.name}</td>
                    <td data-label="<?php echo lang('mobile'); ?>">${s.mobile}</td>
                    <td data-label="<?php echo lang('supplier_due'); ?>" style="font-weight:600;color:${s.due > 0 ? 'var(--danger)' : 'var(--success)'};">${money(s.due)}</td>
                    <td data-label="<?php echo lang('action'); ?>">
                        <div class="d-flex gap-2 justify-content-end">
                            ${s.due > 0 ? `<button class="ck-btn ck-btn-primary" style="padding:6px 12px;font-size:11px;" onclick="openSlPayment(${s.id}, '${s.name.replace(/'/g, "\\'")}', ${s.due})"><i class="fa-solid fa-money-bill-transfer"></i> <?php echo lang('make_payment'); ?></button>` : ''}
                            <button class="icon-btn ck-btn-danger-soft" onclick="deleteSlSupplier(${s.id}, ${s.due})"><i class="fa-solid fa-trash"></i></button>
                        </div>
                    </td>
                </tr>
            `).join('');
        } catch (err) {
            tbody.innerHTML = `<tr><td colspan="4" class="text-center py-4 text-danger">Error</td></tr>`;
        }
    }

    window.openSlPayment = function (id, name, due) {
        document.getElementById('slPaymentSupplierId').value = id;
        document.getElementById('slPaymentSupplierName').textContent = name;
        document.getElementById('slPaymentSupplierDue').textContent = money(due);
        document.getElementById('slPaymentAmount').value = '';
        document.getElementById('slPaymentAmount').max = due;
        document.getElementById('slPaymentOverlay').style.display = 'flex';
    };

    document.getElementById('slPaymentForm').addEventListener('submit', async function (e) {
        e.preventDefault();
        const payload = {
            supplier_id: document.getElementById('slPaymentSupplierId').value,
            amount: document.getElementById('slPaymentAmount').value
        };
        try {
            const res = await fetch('api/supplier/payment.php', {
                method: 'POST', headers: { 'Content-Type': 'application/json' }, body: JSON.stringify(payload)
            });
            const result = await res.json();
            if (result.status === 'success') {
                ckToast('success', result.message);
                document.getElementById('slPaymentOverlay').style.display = 'none';
                updateCashBalance(result.cash_balance);
                loadSupplierListModal(document.getElementById('slSearchInput').value);
                loadDashboard();
            } else {
                ckToast('error', result.message);
            }
        } catch (err) { ckToast('error', 'Failed to process payment'); }
    });

    window.deleteSlSupplier = async function (id, due) {
        if (due > 0) {
            ckToast('warning', 'Cannot delete: this supplier has pending due. Clear due first.');
            return;
        }
        const confirmResult = await ckConfirm('This supplier will be permanently deleted.');
        if (!confirmResult.isConfirmed) return;
        try {
            const res = await fetch('api/supplier/delete.php', {
                method: 'POST', headers: { 'Content-Type': 'application/json' }, body: JSON.stringify({ id })
            });
            const result = await res.json();
            if (result.status === 'success') {
                ckToast('success', result.message);
                loadSupplierListModal(document.getElementById('slSearchInput').value);
            } else {
                ckToast('error', result.message);
            }
        } catch (err) { ckToast('error', 'Failed to delete supplier'); }
    };

    let slSearchTimer;
    document.getElementById('slSearchInput').addEventListener('input', function () {
        clearTimeout(slSearchTimer);
        const val = this.value;
        slSearchTimer = setTimeout(() => loadSupplierListModal(val), 350);
    });

    /* ============ CHART MODAL ============ */
    async function openChartModal(type, label) {
        currentChartType = type;
        document.getElementById('chartModalTitle').textContent = label;
        document.getElementById('chartOverlay').style.display = 'flex';
        document.querySelectorAll('.ck-filter-btn').forEach(b => b.classList.remove('active'));
        document.querySelector('.ck-filter-btn[data-days="7"]').classList.add('active');

        const listLabels = {
            purchase: 'View Purchase List',
            sales: 'View Sales History',
            profit: 'View Sales History',
            expenses: 'View Expense List',
            customer_due: 'View Customer List',
            supplier_due: 'View Supplier List'
        };
        document.getElementById('chartViewListText').textContent = listLabels[type] || 'View List';

        /* Modal ঠিকভাবে Visible হওয়ার একটু পরে Chart তৈরি করা হচ্ছে —
           নাহলে Canvas Hidden অবস্থায় Size ০ পেয়ে ভাঙা Chart দেখাত */
        await new Promise(resolve => setTimeout(resolve, 60));
        await renderChart(type, 7);
    }

    document.getElementById('chartViewListBtn').addEventListener('click', function () {
        document.getElementById('chartOverlay').style.display = 'none';
        switch (currentChartType) {
            case 'purchase':
                loadPage('purchase');
                break;
            case 'sales':
            case 'profit':
                goToSalesHistory();
                break;
            case 'expenses':
                loadPage('expenses');
                break;
            case 'customer_due':
                openCustomerListModal();
                break;
            case 'supplier_due':
                openSupplierListModal();
                break;
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

            const canvas = document.getElementById('dashboardChartCanvas');
            const ctx = canvas.getContext('2d');

            if (dashboardChart) {
                dashboardChart.destroy();
                dashboardChart = null;
            }

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

            /* Resize এর মাধ্যমে নিশ্চিত করা হচ্ছে Chart সঠিক Dimension ব্যবহার করছে */
            requestAnimationFrame(() => { if (dashboardChart) dashboardChart.resize(); });
        } catch (err) {
            ckToast('error', 'Something went wrong while loading chart');
        }
    }

    window.addEventListener('resize', function () {
        if (dashboardChart && document.getElementById('chartOverlay').style.display === 'flex') {
            dashboardChart.resize();
        }
    });

    loadDashboard('today');
})();
</script>