<?php
require_once __DIR__ . '/../includes/auth_check.php';
?>
<!-- ============ SALES PAGE ============ -->
<div id="salesPage">

    <div class="d-flex justify-content-between align-items-center mb-3 flex-wrap gap-2">
        <div>
            <h4 class="mb-0" style="font-weight:600;"><?php echo lang('sales'); ?></h4>
            <p class="text-muted mb-0" style="font-size:13px;">Sell products from your available stock</p>
        </div>
        <button class="ck-btn ck-btn-outline" id="btnToggleHistory">
            <i class="fa-solid fa-clock-rotate-left"></i> <span id="toggleHistoryText">View History</span>
        </button>
    </div>

    <!-- Search -->
    <div class="ck-card mb-3">
        <div class="input-group-search">
            <i class="fa-solid fa-magnifying-glass"></i>
            <input type="text" id="salesSearch" placeholder="<?php echo lang('search'); ?> products...">
        </div>
    </div>

    <!-- Product Grid (Sell Mode) -->
    <div id="productGridView">
        <div class="row g-3" id="productGrid">
            <div class="col-12 text-center py-5 text-muted">Loading products...</div>
        </div>
    </div>

    <!-- Sales History Table (Hidden by default) -->
    <div id="salesHistoryView" style="display:none;">
        <div class="ck-card p-0">
            <div class="table-responsive">
                <table class="ck-table">
                    <thead>
                        <tr>
                            <th><?php echo lang('product_name'); ?></th>
                            <th><?php echo lang('customer'); ?></th>
                            <th><?php echo lang('quantity'); ?></th>
                            <th><?php echo lang('sale_price'); ?></th>
                            <th><?php echo lang('total_amount'); ?></th>
                            <th><?php echo lang('payment_type'); ?></th>
                            <th><?php echo lang('date'); ?></th>
                            <th><?php echo lang('action'); ?></th>
                        </tr>
                    </thead>
                    <tbody id="salesTableBody">
                        <tr><td colspan="8" class="text-center py-4 text-muted">Loading...</td></tr>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

<!-- ============ MODAL: SELL PRODUCT ============ -->
<div class="ck-modal-overlay" id="sellProductOverlay" style="display:none;">
    <div class="ck-modal-box" style="max-width:460px;">
        <div class="ck-modal-header">
            <h5><?php echo lang('sell'); ?>: <span id="sellProductName"></span></h5>
            <i class="fa-solid fa-xmark ck-modal-close" data-close="sellProductOverlay"></i>
        </div>

        <form id="sellProductForm">
            <input type="hidden" id="sellProductId">

            <p class="text-muted mb-3" style="font-size:12px;">
                <?php echo lang('stock'); ?>: <span id="sellAvailableStock" style="font-weight:600;color:var(--text-dark);"></span>
            </p>

            <div class="row g-2">
                <div class="col-6">
                    <label class="ck-label"><?php echo lang('quantity'); ?></label>
                    <input type="number" min="1" class="ck-input" id="sellQuantityInput" required>
                </div>
                <div class="col-6">
                    <label class="ck-label"><?php echo lang('sale_price'); ?></label>
                    <input type="number" step="0.01" min="0.01" class="ck-input" id="sellPriceInput" required>
                </div>
            </div>

            <label class="ck-label mt-2"><?php echo lang('customer'); ?> <span class="text-muted">(optional for cash sale)</span></label>
            <select class="ck-select" id="sellCustomerSelect">
                <option value="">-- Walk-in Customer --</option>
            </select>

            <div class="ck-total-box mt-3">
                <span><?php echo lang('total_amount'); ?></span>
                <span id="sellTotalDisplay">৳0.00</span>
            </div>

            <label class="ck-label mt-3"><?php echo lang('payment_type'); ?></label>
            <div class="ck-toggle-tabs">
                <button type="button" class="ck-toggle-btn active" data-payment="cash"><?php echo lang('cash'); ?></button>
                <button type="button" class="ck-toggle-btn" data-payment="due"><?php echo lang('due'); ?></button>
            </div>

            <div id="sellPaidAmountBlock" class="mt-2" style="display:none;">
                <label class="ck-label"><?php echo lang('paid_amount'); ?> <span class="text-muted">(optional partial payment)</span></label>
                <input type="number" step="0.01" min="0" class="ck-input" id="sellPaidAmountInput" value="0">
            </div>

            <div class="d-flex gap-2 mt-3">
                <button type="button" class="ck-btn ck-btn-outline flex-fill justify-content-center" data-close="sellProductOverlay"><?php echo lang('cancel'); ?></button>
                <button type="submit" class="ck-btn ck-btn-primary flex-fill justify-content-center" id="sellSaveBtn"><?php echo lang('sell'); ?></button>
            </div>
        </form>
    </div>
</div>

<!-- ============ PAGE-SPECIFIC STYLES ============ -->
<style>
    .product-card {
        background: #fff; border: 1px solid var(--border-color); border-radius: 14px;
        padding: 16px; transition: all 0.2s ease; height: 100%;
    }
    .product-card:hover { border-color: var(--primary-blue); box-shadow: 0 6px 18px rgba(37,99,235,0.08); }
    .product-card .pc-icon {
        width: 44px; height: 44px; background: var(--light-blue); color: var(--primary-blue);
        border-radius: 10px; display: flex; align-items: center; justify-content: center; font-size: 18px; margin-bottom: 12px;
    }
    .product-card .pc-name { font-weight: 600; font-size: 14px; margin-bottom: 4px; }
    .product-card .pc-desc { font-size: 11px; color: var(--text-muted); margin-bottom: 10px; min-height: 14px; }
    .product-card .pc-price { font-size: 15px; font-weight: 700; color: var(--primary-blue); }
    .product-card .pc-stock { font-size: 11px; color: var(--text-muted); margin-top: 2px; }
    .product-card .pc-stock.low { color: var(--warning); font-weight: 600; }
    .product-card .pc-sell-btn {
        width: 100%; margin-top: 12px; background: var(--primary-blue); color: #fff; border: none;
        border-radius: 8px; padding: 9px; font-size: 12.5px; font-weight: 600; cursor: pointer; transition: all 0.2s ease;
    }
    .product-card .pc-sell-btn:hover { background: var(--dark-blue); }
</style>

<!-- ============ PAGE-SPECIFIC SCRIPT ============ -->
<script>
(function () {
    let productsCache = [];
    let customersCache = [];
    let historyMode = false;

    function money(v) {
        return '৳' + parseFloat(v || 0).toLocaleString('en-US', { minimumFractionDigits: 2, maximumFractionDigits: 2 });
    }

    function formatDate(dateStr) {
        const d = new Date(dateStr.replace(' ', 'T'));
        return d.toLocaleDateString('en-GB', { day: '2-digit', month: 'short', year: 'numeric' });
    }

    /* ============ LOAD PRODUCT GRID ============ */
    async function loadProductGrid(search = '') {
        const grid = document.getElementById('productGrid');
        try {
            const res = await fetch('api/sales/form_data.php');
            const result = await res.json();

            if (result.status !== 'success') {
                grid.innerHTML = `<div class="col-12 text-center py-5 text-danger">Failed to load products</div>`;
                return;
            }

            productsCache = result.data.products;
            customersCache = result.data.customers;

            let filtered = productsCache;
            if (search.trim() !== '') {
                filtered = productsCache.filter(p => p.name.toLowerCase().includes(search.toLowerCase()));
            }

            if (filtered.length === 0) {
                grid.innerHTML = `<div class="col-12 text-center py-5 text-muted"><?php echo lang('no_data'); ?></div>`;
                return;
            }

            grid.innerHTML = filtered.map(p => `
                <div class="col-6 col-md-4 col-lg-3">
                    <div class="product-card">
                        <div class="pc-icon"><i class="fa-solid fa-box"></i></div>
                        <div class="pc-name">${p.name}</div>
                        <div class="pc-desc">${p.description || ''}</div>
                        <div class="pc-price">${money(p.sale_price)}</div>
                        <div class="pc-stock ${p.stock <= p.low_stock_alert ? 'low' : ''}">
                            <?php echo lang('stock'); ?>: ${p.stock} ${p.stock <= p.low_stock_alert ? '<i class="fa-solid fa-triangle-exclamation"></i>' : ''}
                        </div>
                        <button class="pc-sell-btn" data-id="${p.id}"><?php echo lang('sell'); ?></button>
                    </div>
                </div>
            `).join('');

            gsap.from('.product-card', { y: 12, opacity: 0, duration: 0.35, stagger: 0.04, ease: "power2.out" });

            document.querySelectorAll('.pc-sell-btn').forEach(btn => {
                btn.addEventListener('click', () => openSellModal(parseInt(btn.dataset.id)));
            });
        } catch (err) {
            grid.innerHTML = `<div class="col-12 text-center py-5 text-danger">Error loading products</div>`;
        }
    }

    /* ============ OPEN SELL MODAL ============ */
    function openSellModal(productId) {
        const product = productsCache.find(p => p.id === productId);
        if (!product) return;

        document.getElementById('sellProductId').value = product.id;
        document.getElementById('sellProductName').textContent = product.name;
        document.getElementById('sellAvailableStock').textContent = product.stock;
        document.getElementById('sellQuantityInput').value = 1;
        document.getElementById('sellQuantityInput').max = product.stock;
        document.getElementById('sellPriceInput').value = product.sale_price;
        document.getElementById('sellPaidAmountInput').value = 0;

        // Reset payment toggle to cash
        const tabs = document.querySelectorAll('#sellProductForm .ck-toggle-btn');
        tabs.forEach(b => b.classList.remove('active'));
        document.querySelector('#sellProductForm .ck-toggle-btn[data-payment="cash"]').classList.add('active');
        document.getElementById('sellPaidAmountBlock').style.display = 'none';

        const customerSelect = document.getElementById('sellCustomerSelect');
        customerSelect.innerHTML = '<option value="">-- Walk-in Customer --</option>' +
            customersCache.map(c => `<option value="${c.id}">${c.name} - ${c.mobile}</option>`).join('');

        recalcSellTotal();
        document.getElementById('sellProductOverlay').style.display = 'flex';
    }

    function recalcSellTotal() {
        const price = parseFloat(document.getElementById('sellPriceInput').value) || 0;
        const qty = parseFloat(document.getElementById('sellQuantityInput').value) || 0;
        document.getElementById('sellTotalDisplay').textContent = money(price * qty);
    }
    document.getElementById('sellPriceInput').addEventListener('input', recalcSellTotal);
    document.getElementById('sellQuantityInput').addEventListener('input', recalcSellTotal);

    /* ============ TOGGLE CASH / DUE ============ */
    document.querySelectorAll('#sellProductForm .ck-toggle-btn').forEach(btn => {
        btn.addEventListener('click', function () {
            document.querySelectorAll('#sellProductForm .ck-toggle-btn').forEach(b => b.classList.remove('active'));
            this.classList.add('active');
            document.getElementById('sellPaidAmountBlock').style.display = this.dataset.payment === 'due' ? 'block' : 'none';
        });
    });

    /* ============ CLOSE MODAL ============ */
    document.querySelectorAll('#sellProductOverlay [data-close], #sellProductOverlay .ck-modal-close').forEach(el => {
        el.addEventListener('click', () => document.getElementById('sellProductOverlay').style.display = 'none');
    });

    /* ============ SUBMIT SALE ============ */
    document.getElementById('sellProductForm').addEventListener('submit', async function (e) {
        e.preventDefault();

        const paymentType = document.querySelector('#sellProductForm .ck-toggle-btn.active').dataset.payment;
        const customerId = document.getElementById('sellCustomerSelect').value;

        if (paymentType === 'due' && !customerId) {
            ckToast('warning', 'Please select a customer for Due sale');
            return;
        }

        const payload = {
            product_id: document.getElementById('sellProductId').value,
            customer_id: customerId,
            quantity: document.getElementById('sellQuantityInput').value,
            sale_price: document.getElementById('sellPriceInput').value,
            payment_type: paymentType,
            paid_amount: paymentType === 'due' ? (document.getElementById('sellPaidAmountInput').value || 0) : 0
        };

        const saveBtn = document.getElementById('sellSaveBtn');
        saveBtn.disabled = true;
        saveBtn.innerHTML = '<i class="fa-solid fa-spinner fa-spin"></i> Processing...';

        try {
            const res = await fetch('api/sales/add.php', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify(payload)
            });
            const result = await res.json();

            if (result.status === 'success') {
                ckToast('success', result.message);
                document.getElementById('sellProductOverlay').style.display = 'none';
                updateCashBalance(result.cash_balance);
                loadProductGrid(document.getElementById('salesSearch').value);
                if (historyMode) loadSalesHistory();
            } else {
                ckToast('error', result.message);
            }
        } catch (err) {
            ckToast('error', 'Failed to process sale');
        } finally {
            saveBtn.disabled = false;
            saveBtn.innerHTML = '<?php echo lang('sell'); ?>';
        }
    });

    /* ============ SALES HISTORY ============ */
    async function loadSalesHistory(search = '') {
        const tbody = document.getElementById('salesTableBody');
        try {
            const res = await fetch('api/sales/list.php?search=' + encodeURIComponent(search));
            const result = await res.json();

            if (result.status !== 'success' || result.data.length === 0) {
                tbody.innerHTML = `<tr><td colspan="8" class="text-center py-4 text-muted"><?php echo lang('no_data'); ?></td></tr>`;
                return;
            }

            tbody.innerHTML = result.data.map(s => `
                <tr>
                    <td style="font-weight:500;">${s.product_name}</td>
                    <td>${s.customer_name ? s.customer_name : '<span class="text-muted">Walk-in</span>'}</td>
                    <td>${s.quantity}</td>
                    <td>${money(s.sale_price)}</td>
                    <td style="font-weight:600;">${money(s.total_amount)}</td>
                    <td><span class="${s.payment_type === 'cash' ? 'badge-cash' : 'badge-due'}">${s.payment_type === 'cash' ? '<?php echo lang('cash'); ?>' : '<?php echo lang('due'); ?>'}</span></td>
                    <td>${formatDate(s.created_at)}</td>
                    <td><button class="icon-btn ck-btn-danger-soft" onclick="deleteSale(${s.id})"><i class="fa-solid fa-trash"></i></button></td>
                </tr>
            `).join('');
        } catch (err) {
            tbody.innerHTML = `<tr><td colspan="8" class="text-center py-4 text-danger">Error loading data</td></tr>`;
        }
    }

    window.deleteSale = async function (id) {
        const confirm = await ckConfirm('This will restore stock and reverse cash balance for this sale.');
        if (!confirm.isConfirmed) return;

        try {
            const res = await fetch('api/sales/delete.php', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify({ id })
            });
            const result = await res.json();

            if (result.status === 'success') {
                ckToast('success', result.message);
                updateCashBalance(result.cash_balance);
                loadSalesHistory();
                loadProductGrid();
            } else {
                ckToast('error', result.message);
            }
        } catch (err) {
            ckToast('error', 'Failed to delete sale');
        }
    };

    /* ============ TOGGLE GRID / HISTORY VIEW ============ */
    document.getElementById('btnToggleHistory').addEventListener('click', function () {
        historyMode = !historyMode;
        document.getElementById('productGridView').style.display = historyMode ? 'none' : 'block';
        document.getElementById('salesHistoryView').style.display = historyMode ? 'block' : 'none';
        document.getElementById('toggleHistoryText').textContent = historyMode ? 'View Products' : 'View History';
        this.querySelector('i').className = historyMode ? 'fa-solid fa-box' : 'fa-solid fa-clock-rotate-left';

        if (historyMode) loadSalesHistory();
    });

    /* ============ SEARCH ============ */
    let searchTimer;
    document.getElementById('salesSearch').addEventListener('input', function () {
        clearTimeout(searchTimer);
        const val = this.value;
        searchTimer = setTimeout(() => {
            if (historyMode) loadSalesHistory(val);
            else loadProductGrid(val);
        }, 350);
    });

    /* Initial Load */
    loadProductGrid();
})();
</script>