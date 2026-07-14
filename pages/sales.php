<?php
require_once __DIR__ . '/../includes/auth_check.php';
?>
<div id="salesPage">

    <!-- ============ HEADER BLOCK: Title + History Toggle + Search (Fixed Size) ============ -->
    <div class="sales-header-block">
        <div class="page-head mb-3">
            <div>
                <h4><?php echo lang('sales'); ?></h4>
                <p>Sell products from your available stock</p>
            </div>
            <button class="ck-btn ck-btn-outline" id="btnToggleHistory">
                <i class="fa-solid fa-clock-rotate-left"></i> <span id="toggleHistoryText">View History</span>
            </button>
        </div>

        <div class="ck-card mb-3">
            <div class="input-group-search">
                <i class="fa-solid fa-magnifying-glass"></i>
                <input type="text" id="salesSearch" placeholder="<?php echo lang('search'); ?> products...">
            </div>
        </div>
    </div>

    <!-- ============ SALES BODY: বাকি সব জায়গা নিবে, ভেতরে শুধু Table Scroll হবে ============ -->
    <div class="sales-body">

        <!-- ============ PRODUCT LIST (TABLE, sell mode) ============ -->
        <div id="productGridView" class="sales-view">
            <div class="ck-card p-0 sales-table-card">
                <div class="table-responsive table-scroll-box" id="productTableScroll">
                    <table class="ck-table">
                        <thead>
                            <tr>
                                <th><?php echo lang('product_name'); ?></th>
                                <th><?php echo lang('description'); ?></th>
                                <th><?php echo lang('sale_price'); ?></th>
                                <th><?php echo lang('stock'); ?></th>
                                <th><?php echo lang('action'); ?></th>
                            </tr>
                        </thead>
                        <tbody id="productTableBody">
                            <tr><td colspan="5" class="text-center py-4 text-muted">Loading...</td></tr>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        <!-- ============ SALES HISTORY (TABLE) ============ -->
        <div id="salesHistoryView" class="sales-view" style="display:none;">
            <div class="ck-card p-0 sales-table-card">
                <div class="table-responsive table-scroll-box" id="salesHistoryTableScroll">
                    <table class="ck-table">
                        <thead>
                            <tr>
                                <th><?php echo lang('product_name'); ?></th>
                                <th><?php echo lang('customer'); ?></th>
                                <th><?php echo lang('quantity'); ?></th>
                                <th><?php echo lang('sale_price'); ?></th>
                                <th><?php echo lang('total_amount'); ?></th>
                                <th>Status</th>
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
            <input type="hidden" id="sellProductCostPrice">

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

            <label class="ck-label mt-2"><?php echo lang('customer'); ?> <span class="text-muted">(optional)</span></label>
            <div class="d-flex gap-2">
                <select class="ck-select" id="sellCustomerSelect">
                    <option value="">-- Walk-in Customer --</option>
                </select>
                <button type="button" class="ck-btn ck-btn-outline" id="btnQuickAddCustomer" style="padding:10px 14px;" title="Add New Customer">
                    <i class="fa-solid fa-plus"></i>
                </button>
            </div>

            <div class="row g-2 mt-1">
                <div class="col-6">
                    <label class="ck-label">Gross Amount</label>
                    <div class="ck-input" style="background:#f8fafc;color:var(--text-muted);" id="sellGrossDisplay">৳0.00</div>
                </div>
                <div class="col-6">
                    <label class="ck-label">Discount</label>
                    <input type="number" step="0.01" min="0" class="ck-input" id="sellDiscountInput" placeholder="0.00" value="0">
                </div>
            </div>
            <p class="text-muted mb-0 mt-1" style="font-size:11px;">Max Discount (No Loss): <span id="sellMaxDiscountText" style="font-weight:600;color:var(--success);">৳0.00</span></p>
            <p class="mb-0 mt-1" id="sellDiscountWarning" style="font-size:11px;color:var(--danger);display:none;">
                <i class="fa-solid fa-triangle-exclamation"></i> এর বেশি Discount দিলে Loss হবে!
            </p>

            <div class="ck-total-box mt-2">
                <span><?php echo lang('total_amount'); ?> (After Discount)</span>
                <span id="sellTotalDisplay">৳0.00</span>
            </div>

            <label class="ck-label mt-3">Pay Amount</label>
            <input type="number" step="0.01" min="0" class="ck-input" id="sellPaidAmountInput" placeholder="0.00">

            <div class="d-flex justify-content-between mt-2" style="font-size:12px;">
                <span class="text-muted">Due Amount</span>
                <span id="sellDueDisplay" style="font-weight:600;color:var(--danger);">৳0.00</span>
            </div>

            <div class="d-flex gap-2 mt-3">
                <button type="button" class="ck-btn ck-btn-outline flex-fill justify-content-center" data-close="sellProductOverlay"><?php echo lang('cancel'); ?></button>
                <button type="submit" class="ck-btn ck-btn-primary flex-fill justify-content-center" id="sellSaveBtn"><?php echo lang('sell'); ?></button>
            </div>
        </form>
    </div>
</div>

<!-- ============ MODAL: QUICK ADD CUSTOMER ============ -->
<div class="ck-modal-overlay" id="quickCustomerOverlay" style="display:none;">
    <div class="ck-modal-box" style="max-width:380px;">
        <div class="ck-modal-header">
            <h5><?php echo lang('add_customer'); ?></h5>
            <i class="fa-solid fa-xmark ck-modal-close" data-close="quickCustomerOverlay"></i>
        </div>
        <form id="quickCustomerForm">
            <label class="ck-label"><?php echo lang('name'); ?></label>
            <input type="text" class="ck-input" id="qcName" required>

            <label class="ck-label mt-2"><?php echo lang('mobile'); ?></label>
            <input type="text" class="ck-input" id="qcMobile" required>

            <label class="ck-label mt-2"><?php echo lang('address'); ?> <span class="text-muted">(optional)</span></label>
            <input type="text" class="ck-input" id="qcAddress">

            <div class="d-flex gap-2 mt-3">
                <button type="button" class="ck-btn ck-btn-outline flex-fill justify-content-center" data-close="quickCustomerOverlay"><?php echo lang('cancel'); ?></button>
                <button type="submit" class="ck-btn ck-btn-primary flex-fill justify-content-center"><?php echo lang('save'); ?></button>
            </div>
        </form>
    </div>
</div>

<!-- ============ MODAL: RETURN PRODUCT ============ -->
<div class="ck-modal-overlay" id="returnOverlay" style="display:none;">
    <div class="ck-modal-box" style="max-width:400px;">
        <div class="ck-modal-header">
            <h5>Return Product</h5>
            <i class="fa-solid fa-xmark ck-modal-close" data-close="returnOverlay"></i>
        </div>
        <form id="returnForm">
            <input type="hidden" id="returnSaleId">
            <p style="font-size:13px;">Product: <strong id="returnProductName"></strong></p>
            <p class="text-muted" style="font-size:12px;">Sold Quantity: <span id="returnSoldQty" style="font-weight:600;"></span></p>

            <label class="ck-label mt-2">Return Quantity</label>
            <input type="number" min="1" class="ck-input" id="returnQtyInput" required>

            <div class="ck-total-box mt-3">
                <span>Refund Amount</span>
                <span id="returnAmountDisplay">৳0.00</span>
            </div>
            <p class="text-muted mb-0 mt-2" style="font-size:11px;">Stock ফেরত যাবে, এবং Refund Amount Cash Balance অথবা Customer Due থেকে সমন্বয় হবে।</p>

            <div class="d-flex gap-2 mt-3">
                <button type="button" class="ck-btn ck-btn-outline flex-fill justify-content-center" data-close="returnOverlay"><?php echo lang('cancel'); ?></button>
                <button type="submit" class="ck-btn ck-btn-danger-soft flex-fill justify-content-center" id="returnSaveBtn">Confirm Return</button>
            </div>
        </form>
    </div>
</div>

<!-- ============ MODAL: PAY SALE DUE ============ -->
<div class="ck-modal-overlay" id="salesPayDueOverlay" style="display:none;">
    <div class="ck-modal-box" style="max-width:380px;">
        <div class="ck-modal-header">
            <h5>Pay Due</h5>
            <i class="fa-solid fa-xmark ck-modal-close" data-close="salesPayDueOverlay"></i>
        </div>
        <form id="salesPayDueForm">
            <input type="hidden" id="salesPayDueSaleId">
            <p style="font-size:13px;">Product: <strong id="salesPayDueProductName"></strong></p>
            <p class="text-muted" style="font-size:12px;">Customer: <strong id="salesPayDueCustomerName"></strong></p>
            <p class="text-muted" style="font-size:12px;">Due Amount: <span id="salesPayDueAmountText" style="font-weight:600;color:var(--danger);"></span></p>

            <label class="ck-label mt-2"><?php echo lang('amount'); ?></label>
            <input type="number" step="0.01" min="0.01" class="ck-input" id="salesPayDueAmountInput" required>

            <div class="d-flex gap-2 mt-3">
                <button type="button" class="ck-btn ck-btn-outline flex-fill justify-content-center" data-close="salesPayDueOverlay"><?php echo lang('cancel'); ?></button>
                <button type="submit" class="ck-btn ck-btn-primary flex-fill justify-content-center">Pay</button>
            </div>
        </form>
    </div>
</div>

<!-- ============ PAGE-SPECIFIC STYLES ============ -->
<style>
    #salesPage {
        display: flex;
        flex-direction: column;
        height: calc(100vh - var(--topbar-height) - 52px);
        height: calc(100svh - var(--topbar-height) - 52px);
    }
    @media (max-width: 991px) {
        #salesPage {
            height: calc(100vh - var(--topbar-height) - 108px);
            height: calc(100svh - var(--topbar-height) - 108px);
        }
    }

    .sales-header-block { flex-shrink: 0; }

    .sales-body {
        flex: 1 1 auto;
        min-height: 0;
        position: relative;
    }

    .sales-view {
        height: 100%;
        display: flex;
        flex-direction: column;
    }

    .sales-table-card {
        flex: 1 1 auto;
        min-height: 0;
        overflow: hidden;
        display: flex;
        flex-direction: column;
    }

    .table-scroll-box {
        flex: 1 1 auto;
        min-height: 0;
        overflow-y: auto;
        -webkit-overflow-scrolling: touch;
        scrollbar-width: none;
        -ms-overflow-style: none;
    }
    .table-scroll-box::-webkit-scrollbar { display: none; }

    .table-scroll-box .ck-table thead th {
        position: sticky;
        top: 0;
        z-index: 5;
    }

    @media (max-width: 767px) {
        .table-scroll-box { padding: 4px 4px 12px; }
    }
</style>

<script>
(function () {
    let productsCache = [];
    let customersCache = [];
    let historyMode = false;
    let returnUnitPrice = 0;
    let returnMaxQty = 0;

    function money(v) {
        return '৳' + parseFloat(v || 0).toLocaleString('en-US', { minimumFractionDigits: 2, maximumFractionDigits: 2 });
    }

    function formatDate(dateStr) {
        const d = new Date(dateStr.replace(' ', 'T'));
        return d.toLocaleDateString('en-GB', { day: '2-digit', month: 'short', year: 'numeric' });
    }

    /* ============ LOAD PRODUCT TABLE (sell mode) ============ */
    async function loadProductTable(search = '') {
        const tbody = document.getElementById('productTableBody');
        try {
            const res = await fetch('api/sales/form_data.php');
            const result = await res.json();
            if (result.status !== 'success') {
                tbody.innerHTML = `<tr><td colspan="5" class="text-center py-4 text-danger">Failed to load products</td></tr>`;
                return;
            }

            productsCache = result.data.products;
            customersCache = result.data.customers;

            let filtered = productsCache;
            if (search.trim() !== '') {
                filtered = productsCache.filter(p => p.name.toLowerCase().includes(search.toLowerCase()));
            }

            if (filtered.length === 0) {
                tbody.innerHTML = `<tr><td colspan="5" class="text-center py-4 text-muted"><?php echo lang('no_data'); ?></td></tr>`;
                return;
            }

            tbody.innerHTML = filtered.map(p => `
                <tr>
                    <td data-label="<?php echo lang('product_name'); ?>" style="font-weight:500;">${p.name}</td>
                    <td data-label="<?php echo lang('description'); ?>">${p.description ? p.description : '<span class="text-muted">—</span>'}</td>
                    <td data-label="<?php echo lang('sale_price'); ?>" style="font-weight:600;">${money(p.sale_price)}</td>
                    <td data-label="<?php echo lang('stock'); ?>">${p.stock}</td>
                    <td data-label="<?php echo lang('action'); ?>">
                        <button class="ck-btn ck-btn-primary" style="padding:6px 14px;font-size:11px;" onclick="openSellModal(${p.id})"><?php echo lang('sell'); ?></button>
                    </td>
                </tr>
            `).join('');
        } catch (err) {
            tbody.innerHTML = `<tr><td colspan="5" class="text-center py-4 text-danger">Error loading products</td></tr>`;
        }
    }

    /* ============ LOAD CUSTOMERS FOR DROPDOWN (Quick Add-এর পর Refresh করতে) ============ */
    async function loadCustomersDropdown(selectedId = null) {
        try {
            const res = await fetch('api/customer/list.php');
            const result = await res.json();
            if (result.status === 'success') {
                customersCache = result.data;
                const select = document.getElementById('sellCustomerSelect');
                select.innerHTML = '<option value="">-- Walk-in Customer --</option>' +
                    customersCache.map(c => `<option value="${c.id}" ${selectedId == c.id ? 'selected' : ''}>${c.name} - ${c.mobile}</option>`).join('');
            }
        } catch (err) { /* silent */ }
    }

    window.openSellModal = function (productId) {
        const product = productsCache.find(p => p.id === productId);
        if (!product) return;

        document.getElementById('sellProductId').value = product.id;
        document.getElementById('sellProductCostPrice').value = product.purchase_price;
        document.getElementById('sellProductName').textContent = product.name;
        document.getElementById('sellAvailableStock').textContent = product.stock;
        document.getElementById('sellQuantityInput').value = 1;
        document.getElementById('sellQuantityInput').max = product.stock;
        document.getElementById('sellPriceInput').value = product.sale_price;
        document.getElementById('sellDiscountInput').value = 0;
        document.getElementById('sellPaidAmountInput').value = '';
        document.getElementById('sellDueDisplay').textContent = '৳0.00';
        document.getElementById('sellDiscountWarning').style.display = 'none';

        const customerSelect = document.getElementById('sellCustomerSelect');
        customerSelect.innerHTML = '<option value="">-- Walk-in Customer --</option>' +
            customersCache.map(c => `<option value="${c.id}">${c.name} - ${c.mobile}</option>`).join('');

        recalcSellTotal();
        document.getElementById('sellProductOverlay').style.display = 'flex';
    };

    /* ============ CALCULATION: Gross, Max Discount (Loss Protection), Total, Due ============ */
    function recalcSellTotal() {
        const price = parseFloat(document.getElementById('sellPriceInput').value) || 0;
        const qty = parseFloat(document.getElementById('sellQuantityInput').value) || 0;
        const costPrice = parseFloat(document.getElementById('sellProductCostPrice').value) || 0;

        const gross = price * qty;
        const totalCost = costPrice * qty;
        const maxDiscount = Math.max(gross - totalCost, 0);

        document.getElementById('sellGrossDisplay').textContent = money(gross);
        document.getElementById('sellMaxDiscountText').textContent = money(maxDiscount);

        let discount = parseFloat(document.getElementById('sellDiscountInput').value) || 0;
        const warningEl = document.getElementById('sellDiscountWarning');

        if (discount > maxDiscount) {
            discount = maxDiscount;
            document.getElementById('sellDiscountInput').value = maxDiscount.toFixed(2);
            warningEl.style.display = 'block';
            setTimeout(() => { warningEl.style.display = 'none'; }, 2500);
        }

        const total = gross - discount;
        document.getElementById('sellTotalDisplay').textContent = money(total);
        recalcSellDue();
    }

    function recalcSellDue() {
        const price = parseFloat(document.getElementById('sellPriceInput').value) || 0;
        const qty = parseFloat(document.getElementById('sellQuantityInput').value) || 0;
        const discount = parseFloat(document.getElementById('sellDiscountInput').value) || 0;
        const total = (price * qty) - discount;
        let paid = parseFloat(document.getElementById('sellPaidAmountInput').value) || 0;

        if (paid > total) {
            paid = total;
            document.getElementById('sellPaidAmountInput').value = total.toFixed(2);
        }
        document.getElementById('sellDueDisplay').textContent = money(total - paid);
    }

    document.getElementById('sellPriceInput').addEventListener('input', recalcSellTotal);
    document.getElementById('sellQuantityInput').addEventListener('input', recalcSellTotal);
    document.getElementById('sellDiscountInput').addEventListener('input', recalcSellTotal);
    document.getElementById('sellPaidAmountInput').addEventListener('input', recalcSellDue);

    document.querySelectorAll('#sellProductOverlay [data-close], #sellProductOverlay .ck-modal-close').forEach(el => {
        el.addEventListener('click', () => document.getElementById('sellProductOverlay').style.display = 'none');
    });

    /* ============ QUICK ADD CUSTOMER ============ */
    document.getElementById('btnQuickAddCustomer').addEventListener('click', () => {
        document.getElementById('quickCustomerForm').reset();
        document.getElementById('quickCustomerOverlay').style.display = 'flex';
    });

    document.querySelectorAll('#quickCustomerOverlay [data-close], #quickCustomerOverlay .ck-modal-close').forEach(el => {
        el.addEventListener('click', () => document.getElementById('quickCustomerOverlay').style.display = 'none');
    });

    document.getElementById('quickCustomerForm').addEventListener('submit', async function (e) {
        e.preventDefault();
        const payload = {
            name: document.getElementById('qcName').value.trim(),
            mobile: document.getElementById('qcMobile').value.trim(),
            address: document.getElementById('qcAddress').value.trim()
        };
        try {
            const res = await fetch('api/customer/add.php', {
                method: 'POST', headers: { 'Content-Type': 'application/json' }, body: JSON.stringify(payload)
            });
            const result = await res.json();
            if (result.status === 'success') {
                ckToast('success', result.message);
                document.getElementById('quickCustomerOverlay').style.display = 'none';
                await loadCustomersDropdown(result.data.id);
            } else {
                ckToast('error', result.message);
            }
        } catch (err) {
            ckToast('error', 'Failed to add customer');
        }
    });

    /* ============ SUBMIT SALE ============ */
    document.getElementById('sellProductForm').addEventListener('submit', async function (e) {
        e.preventDefault();

        const customerId = document.getElementById('sellCustomerSelect').value;

        const payload = {
            product_id: document.getElementById('sellProductId').value,
            customer_id: customerId,
            quantity: document.getElementById('sellQuantityInput').value,
            sale_price: document.getElementById('sellPriceInput').value,
            discount_amount: document.getElementById('sellDiscountInput').value || 0,
            paid_amount: document.getElementById('sellPaidAmountInput').value || 0
        };

        const saveBtn = document.getElementById('sellSaveBtn');
        saveBtn.disabled = true;
        saveBtn.innerHTML = '<i class="fa-solid fa-spinner fa-spin"></i> Processing...';

        try {
            const res = await fetch('api/sales/add.php', {
                method: 'POST', headers: { 'Content-Type': 'application/json' }, body: JSON.stringify(payload)
            });
            const result = await res.json();

            if (result.status === 'success') {
                ckToast('success', result.message);
                document.getElementById('sellProductOverlay').style.display = 'none';
                updateCashBalance(result.cash_balance);
                loadProductTable(document.getElementById('salesSearch').value);
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

            tbody.innerHTML = result.data.map(s => {
                let statusHtml;
                if (s.due_amount > 0 && s.paid_amount > 0) {
                    statusHtml = `<span class="badge-due">Partial</span><div style="font-size:10px;color:var(--danger);margin-top:2px;">Due: ${money(s.due_amount)}</div>`;
                } else if (s.due_amount > 0) {
                    statusHtml = `<span class="badge-due"><?php echo lang('due'); ?></span><div style="font-size:10px;color:var(--danger);margin-top:2px;">Due: ${money(s.due_amount)}</div>`;
                } else {
                    statusHtml = `<span class="badge-cash">Paid</span>`;
                }

                return `
                <tr>
                    <td data-label="<?php echo lang('product_name'); ?>" style="font-weight:500;">${s.product_name}</td>
                    <td data-label="<?php echo lang('customer'); ?>">${s.customer_name ? s.customer_name : '<span class="text-muted">Walk-in</span>'}</td>
                    <td data-label="<?php echo lang('quantity'); ?>">${s.quantity}</td>
                    <td data-label="<?php echo lang('sale_price'); ?>">${money(s.sale_price)}</td>
                    <td data-label="<?php echo lang('total_amount'); ?>" style="font-weight:600;">${money(s.total_amount)}${s.discount_amount > 0 ? `<div style="font-size:10px;color:var(--warning);margin-top:2px;">Discount: ${money(s.discount_amount)}</div>` : ''}</td>
                    <td data-label="Status">${statusHtml}</td>
                    <td data-label="<?php echo lang('date'); ?>">${formatDate(s.created_at)}</td>
                    <td data-label="<?php echo lang('action'); ?>">
                        <div class="d-flex gap-2 justify-content-end">
                            ${s.due_amount > 0 ? `<button class="ck-btn ck-btn-success-soft" style="padding:6px 10px;font-size:11px;" onclick='openSalesPayDue(${JSON.stringify(s)})'><i class="fa-solid fa-hand-holding-dollar"></i> Pay</button>` : ''}
                            <button class="ck-btn ck-btn-outline" style="padding:6px 10px;font-size:11px;" onclick='openReturnModal(${JSON.stringify(s)})'><i class="fa-solid fa-rotate-left"></i> Return</button>
                        </div>
                    </td>
                </tr>
            `;
            }).join('');
        } catch (err) {
            tbody.innerHTML = `<tr><td colspan="8" class="text-center py-4 text-danger">Error loading data</td></tr>`;
        }
    }

    /* ============ PAY SALE DUE ============ */
    window.openSalesPayDue = function (s) {
        document.getElementById('salesPayDueSaleId').value = s.id;
        document.getElementById('salesPayDueProductName').textContent = s.product_name;
        document.getElementById('salesPayDueCustomerName').textContent = s.customer_name ? s.customer_name : 'Walk-in';
        document.getElementById('salesPayDueAmountText').textContent = money(s.due_amount);
        document.getElementById('salesPayDueAmountInput').value = '';
        document.getElementById('salesPayDueAmountInput').max = s.due_amount;
        document.getElementById('salesPayDueOverlay').style.display = 'flex';
    };

    document.getElementById('salesPayDueForm').addEventListener('submit', async function (e) {
        e.preventDefault();
        const payload = {
            id: document.getElementById('salesPayDueSaleId').value,
            amount: document.getElementById('salesPayDueAmountInput').value
        };
        try {
            const res = await fetch('api/sales/pay_due.php', {
                method: 'POST', headers: { 'Content-Type': 'application/json' }, body: JSON.stringify(payload)
            });
            const result = await res.json();
            if (result.status === 'success') {
                ckToast('success', result.message);
                document.getElementById('salesPayDueOverlay').style.display = 'none';
                updateCashBalance(result.cash_balance);
                loadSalesHistory(document.getElementById('salesSearch').value);
            } else {
                ckToast('error', result.message);
            }
        } catch (err) {
            ckToast('error', 'Failed to pay due');
        }
    });

    document.querySelectorAll('#salesPayDueOverlay [data-close], #salesPayDueOverlay .ck-modal-close').forEach(el => {
        el.addEventListener('click', () => document.getElementById('salesPayDueOverlay').style.display = 'none');
    });

    /* ============ RETURN MODAL ============ */
    window.openReturnModal = function (s) {
        document.getElementById('returnSaleId').value = s.id;
        document.getElementById('returnProductName').textContent = s.product_name;
        document.getElementById('returnSoldQty').textContent = s.quantity;
        document.getElementById('returnQtyInput').value = s.quantity;
        document.getElementById('returnQtyInput').max = s.quantity;

        returnUnitPrice = parseFloat(s.sale_price);
        returnMaxQty = s.quantity;

        recalcReturnAmount();
        document.getElementById('returnOverlay').style.display = 'flex';
    };

    function recalcReturnAmount() {
        let qty = parseInt(document.getElementById('returnQtyInput').value) || 0;
        if (qty > returnMaxQty) {
            qty = returnMaxQty;
            document.getElementById('returnQtyInput').value = qty;
        }
        document.getElementById('returnAmountDisplay').textContent = money(qty * returnUnitPrice);
    }
    document.getElementById('returnQtyInput').addEventListener('input', recalcReturnAmount);

    document.getElementById('returnForm').addEventListener('submit', async function (e) {
        e.preventDefault();
        const payload = {
            id: document.getElementById('returnSaleId').value,
            return_qty: document.getElementById('returnQtyInput').value
        };

        const btn = document.getElementById('returnSaveBtn');
        btn.disabled = true;
        btn.innerHTML = '<i class="fa-solid fa-spinner fa-spin"></i> Processing...';

        try {
            const res = await fetch('api/sales/return.php', {
                method: 'POST', headers: { 'Content-Type': 'application/json' }, body: JSON.stringify(payload)
            });
            const result = await res.json();

            if (result.status === 'success') {
                ckToast('success', result.message);
                document.getElementById('returnOverlay').style.display = 'none';
                updateCashBalance(result.cash_balance);
                loadSalesHistory();
                loadProductTable();
            } else {
                ckToast('error', result.message);
            }
        } catch (err) {
            ckToast('error', 'Failed to process return');
        } finally {
            btn.disabled = false;
            btn.innerHTML = 'Confirm Return';
        }
    });

    document.querySelectorAll('#returnOverlay [data-close], #returnOverlay .ck-modal-close').forEach(el => {
        el.addEventListener('click', () => document.getElementById('returnOverlay').style.display = 'none');
    });

    document.getElementById('btnToggleHistory').addEventListener('click', function () {
        historyMode = !historyMode;
        document.getElementById('productGridView').style.display = historyMode ? 'none' : 'flex';
        document.getElementById('salesHistoryView').style.display = historyMode ? 'flex' : 'none';
        document.getElementById('toggleHistoryText').textContent = historyMode ? 'View Products' : 'View History';
        this.querySelector('i').className = historyMode ? 'fa-solid fa-box' : 'fa-solid fa-clock-rotate-left';
        if (historyMode) loadSalesHistory();
    });

    let searchTimer;
    document.getElementById('salesSearch').addEventListener('input', function () {
        clearTimeout(searchTimer);
        const val = this.value;
        searchTimer = setTimeout(() => {
            if (historyMode) loadSalesHistory(val);
            else loadProductTable(val);
        }, 350);
    });

    loadProductTable();
})();
</script>