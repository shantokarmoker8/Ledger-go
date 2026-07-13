<?php
require_once __DIR__ . '/../includes/auth_check.php';
?>
<div id="purchasePage">

    <!-- ============ Header Block: স্বাভাবিক আকারে থাকবে, নিচের Table বাকি সব জায়গা নিবে ============ -->
    <div class="purchase-header-block">
        <div class="page-head mb-3">
            <div>
                <h4><?php echo lang('purchase'); ?></h4>
                <p>Manage product purchases and stock</p>
            </div>
            <button class="ck-btn ck-btn-primary" id="btnNewPurchase">
                <i class="fa-solid fa-plus"></i> <?php echo lang('new_purchase'); ?>
            </button>
        </div>

        <div class="ck-card mb-3">
            <div class="input-group-search">
                <i class="fa-solid fa-magnifying-glass"></i>
                <input type="text" id="purchaseSearch" placeholder="<?php echo lang('search'); ?> product or supplier...">
            </div>
        </div>
    </div>

    <!-- ============ Table Card: বাকি সব জায়গা নিবে, ভেতরে শুধু Table Scroll হবে ============ -->
    <div class="ck-card p-0 purchase-table-card">
        <div class="table-responsive table-scroll-box" id="purchaseTableScroll">
            <table class="ck-table">
                <thead>
                    <tr>
                        <th><?php echo lang('product_name'); ?></th>
                        <th><?php echo lang('supplier'); ?></th>
                        <th><?php echo lang('quantity'); ?></th>
                        <th><?php echo lang('purchase_price'); ?></th>
                        <th><?php echo lang('total_amount'); ?></th>
                        <th>Status</th>
                        <th><?php echo lang('date'); ?></th>
                        <th><?php echo lang('action'); ?></th>
                    </tr>
                </thead>
                <tbody id="purchaseTableBody">
                    <tr><td colspan="8" class="text-center py-4 text-muted">Loading...</td></tr>
                </tbody>
            </table>
        </div>
    </div>
</div>

<!-- ============ MODAL: NEW / EDIT PURCHASE ============ -->
<div class="ck-modal-overlay" id="purchaseFormOverlay" style="display:none;">
    <div class="ck-modal-box" style="max-width:500px;">
        <div class="ck-modal-header">
            <h5 id="purchaseModalTitle"><?php echo lang('new_purchase'); ?></h5>
            <i class="fa-solid fa-xmark ck-modal-close" data-close="purchaseFormOverlay"></i>
        </div>

        <form id="purchaseForm">
            <input type="hidden" id="purchaseEditId" value="">

            <label class="ck-label"><?php echo lang('product_name'); ?></label>
            <input type="text" class="ck-input" id="pProductName" required>

            <label class="ck-label mt-2"><?php echo lang('description'); ?> <span class="text-muted">(optional)</span></label>
            <input type="text" class="ck-input" id="pDescription">

            <div class="row g-2 mt-1">
                <div class="col-6">
                    <label class="ck-label"><?php echo lang('purchase_price'); ?></label>
                    <input type="number" step="0.01" min="0.01" class="ck-input" id="pPurchasePrice" required>
                </div>
                <div class="col-6">
                    <label class="ck-label"><?php echo lang('sale_price'); ?></label>
                    <input type="number" step="0.01" min="0.01" class="ck-input" id="pSalePrice" required>
                </div>
            </div>

            <label class="ck-label mt-2"><?php echo lang('quantity'); ?></label>
            <input type="number" min="1" class="ck-input" id="pQuantity" required>

            <label class="ck-label mt-2"><?php echo lang('supplier'); ?></label>
            <div class="d-flex gap-2">
                <select class="ck-select" id="pSupplierSelect">
                    <option value="">-- No Supplier --</option>
                </select>
                <button type="button" class="ck-btn ck-btn-outline" id="btnQuickAddSupplier" style="padding:10px 14px;" title="Add New Supplier">
                    <i class="fa-solid fa-plus"></i>
                </button>
            </div>

            <div class="ck-total-box mt-3">
                <span><?php echo lang('total_amount'); ?></span>
                <span id="pTotalDisplay">৳0.00</span>
            </div>

            <div id="paymentBlock">
                <label class="ck-label mt-3">Pay Amount</label>
                <input type="number" step="0.01" min="0" class="ck-input" id="pPaidAmount" placeholder="0.00">
                <p class="text-muted mb-0 mt-1" style="font-size:11px;">Available Cash: <span id="pAvailableCashText">৳0.00</span></p>

                <div class="d-flex justify-content-between mt-2" style="font-size:12px;">
                    <span class="text-muted">Due Amount</span>
                    <span id="pDueDisplay" style="font-weight:600;color:var(--danger);">৳0.00</span>
                </div>
            </div>

            <div class="d-flex gap-2 mt-3">
                <button type="button" class="ck-btn ck-btn-outline flex-fill justify-content-center" data-close="purchaseFormOverlay"><?php echo lang('cancel'); ?></button>
                <button type="submit" class="ck-btn ck-btn-primary flex-fill justify-content-center" id="purchaseSaveBtn"><?php echo lang('save'); ?></button>
            </div>
        </form>
    </div>
</div>

<!-- ============ MODAL: QUICK ADD SUPPLIER ============ -->
<div class="ck-modal-overlay" id="quickSupplierOverlay" style="display:none;">
    <div class="ck-modal-box" style="max-width:380px;">
        <div class="ck-modal-header">
            <h5><?php echo lang('add_supplier'); ?></h5>
            <i class="fa-solid fa-xmark ck-modal-close" data-close="quickSupplierOverlay"></i>
        </div>
        <form id="quickSupplierForm">
            <label class="ck-label"><?php echo lang('name'); ?></label>
            <input type="text" class="ck-input" id="qsName" required>

            <label class="ck-label mt-2"><?php echo lang('mobile'); ?></label>
            <input type="text" class="ck-input" id="qsMobile" required>

            <div class="d-flex gap-2 mt-3">
                <button type="button" class="ck-btn ck-btn-outline flex-fill justify-content-center" data-close="quickSupplierOverlay"><?php echo lang('cancel'); ?></button>
                <button type="submit" class="ck-btn ck-btn-primary flex-fill justify-content-center"><?php echo lang('save'); ?></button>
            </div>
        </form>
    </div>
</div>

<!-- ============ MODAL: PAY DUE ============ -->
<div class="ck-modal-overlay" id="payDueOverlay" style="display:none;">
    <div class="ck-modal-box" style="max-width:380px;">
        <div class="ck-modal-header">
            <h5>Pay Due</h5>
            <i class="fa-solid fa-xmark ck-modal-close" data-close="payDueOverlay"></i>
        </div>
        <form id="payDueForm">
            <input type="hidden" id="payDuePurchaseId">
            <p style="font-size:13px;">Product: <strong id="payDueProductName"></strong></p>
            <p class="text-muted" style="font-size:12px;">Due Amount: <span id="payDueAmountText" style="font-weight:600;color:var(--danger);"></span></p>

            <label class="ck-label mt-2"><?php echo lang('amount'); ?></label>
            <input type="number" step="0.01" min="0.01" class="ck-input" id="payDueAmountInput" required>

            <div class="d-flex gap-2 mt-3">
                <button type="button" class="ck-btn ck-btn-outline flex-fill justify-content-center" data-close="payDueOverlay"><?php echo lang('cancel'); ?></button>
                <button type="submit" class="ck-btn ck-btn-primary flex-fill justify-content-center">Pay</button>
            </div>
        </form>
    </div>
</div>

<!-- ============ PAGE-SPECIFIC STYLES ============ -->
<style>
    /* ==========================================================
       পুরো Page Exactly Display-এর সমান Height-এ Fix — কোনো Outer
       Page Scroll থাকবে না, তাই কোনো নাড়াচাড়াই হবে না।
       100svh (Small Viewport Height) ব্যবহার করা হয়েছে কারণ এটা
       Mobile Browser-এর Address Bar দেখা/লুকানোর কারণে পরিবর্তিত হয়
       না — সবসময় একটা স্থির মান থাকে, তাই কোনো Reflow/Wobble হয় না।
       (যেসব পুরনো Browser 100svh বোঝে না, সেখানে Fallback 100vh)
       ========================================================== */
    #purchasePage {
        display: flex;
        flex-direction: column;
        height: calc(100vh - var(--topbar-height) - 52px);
        height: calc(100svh - var(--topbar-height) - 52px);
    }

    @media (max-width: 991px) {
        #purchasePage {
            height: calc(100vh - var(--topbar-height) - 108px);
            height: calc(100svh - var(--topbar-height) - 108px);
        }
    }

    .purchase-header-block {
        flex-shrink: 0; /* Header+Search সবসময় নিজের স্বাভাবিক Height নেয় */
    }

    .purchase-table-card {
        flex: 1 1 auto;
        min-height: 0;      /* Flex Child-কে সঠিকভাবে Shrink/Scroll করতে দেয় */
        overflow: hidden;   /* Rounded Corner ঠিক রাখে — ভেতরের Sticky Header
                                বা Table-এর কোনো Square Edge আর কোণা কেটে ফেলবে না */
        display: flex;
        flex-direction: column;
    }

    .table-scroll-box {
        flex: 1 1 auto;
        min-height: 0;
        overflow-y: auto;
        -webkit-overflow-scrolling: touch;

        /* Scrollbar সম্পূর্ণ লুকানো (Scroll কাজ করবে ঠিকই) */
        scrollbar-width: none;
        -ms-overflow-style: none;
    }
    .table-scroll-box::-webkit-scrollbar {
        display: none;
    }

    /* Desktop/Tablet: Table Header উপরে আটকে থাকবে */
    .table-scroll-box .ck-table thead th {
        position: sticky;
        top: 0;
        z-index: 5;
    }

    /* Mobile: Card আকারে রূপান্তরিত Row-গুলোর চারপাশে সামান্য Gap
       রাখা হচ্ছে যাতে Rounded Corner পরিষ্কারভাবে দেখা যায় */
    @media (max-width: 767px) {
        .table-scroll-box { padding: 4px 4px 12px; }
    }
</style>

<script>
(function () {
    let suppliersCache = [];
    let availableCash = 0;
    let editMode = false;

    function money(v) {
        return '৳' + parseFloat(v || 0).toLocaleString('en-US', { minimumFractionDigits: 2, maximumFractionDigits: 2 });
    }

    function formatDate(dateStr) {
        const d = new Date(dateStr.replace(' ', 'T'));
        return d.toLocaleDateString('en-GB', { day: '2-digit', month: 'short', year: 'numeric' });
    }

    /* ============ LOAD PURCHASE LIST ============ */
    async function loadPurchaseList(search = '') {
        const tbody = document.getElementById('purchaseTableBody');
        try {
            const res = await fetch('api/purchase/list.php?search=' + encodeURIComponent(search));
            const result = await res.json();

            if (result.status !== 'success') {
                tbody.innerHTML = `<tr><td colspan="8" class="text-center py-4 text-danger">Failed to load</td></tr>`;
                return;
            }
            if (result.data.length === 0) {
                tbody.innerHTML = `<tr><td colspan="8" class="text-center py-4 text-muted"><?php echo lang('no_data'); ?></td></tr>`;
                return;
            }

            tbody.innerHTML = result.data.map(p => {
                let statusHtml;
                if (p.due_amount > 0 && p.paid_amount > 0) {
                    statusHtml = `<span class="badge-due">Partial</span><div style="font-size:10px;color:var(--danger);margin-top:2px;">Due: ${money(p.due_amount)}</div>`;
                } else if (p.due_amount > 0) {
                    statusHtml = `<span class="badge-due"><?php echo lang('due'); ?></span><div style="font-size:10px;color:var(--danger);margin-top:2px;">Due: ${money(p.due_amount)}</div>`;
                } else {
                    statusHtml = `<span class="badge-cash">Paid</span>`;
                }

                return `
                <tr>
                    <td data-label="<?php echo lang('product_name'); ?>" style="font-weight:500;">${p.product_name}</td>
                    <td data-label="<?php echo lang('supplier'); ?>">${p.supplier_name ? p.supplier_name : '<span class="text-muted">—</span>'}</td>
                    <td data-label="<?php echo lang('quantity'); ?>">${p.quantity}</td>
                    <td data-label="<?php echo lang('purchase_price'); ?>">${money(p.purchase_price)}</td>
                    <td data-label="<?php echo lang('total_amount'); ?>" style="font-weight:600;">${money(p.total_amount)}</td>
                    <td data-label="Status">${statusHtml}</td>
                    <td data-label="<?php echo lang('date'); ?>">${formatDate(p.created_at)}</td>
                    <td data-label="<?php echo lang('action'); ?>">
                        <div class="d-flex gap-2 justify-content-end">
                            ${p.due_amount > 0 ? `<button class="ck-btn ck-btn-success-soft" style="padding:6px 10px;font-size:11px;" onclick='openPayDue(${JSON.stringify(p)})'><i class="fa-solid fa-hand-holding-dollar"></i> Pay</button>` : ''}
                            <button class="icon-btn ck-btn-outline" onclick='openEditPurchase(${JSON.stringify(p)})'><i class="fa-solid fa-pen"></i></button>
                        </div>
                    </td>
                </tr>
            `;
            }).join('');
        } catch (err) {
            tbody.innerHTML = `<tr><td colspan="8" class="text-center py-4 text-danger">Error loading data</td></tr>`;
        }
    }

    let searchTimer;
    document.getElementById('purchaseSearch').addEventListener('input', function () {
        clearTimeout(searchTimer);
        const val = this.value;
        searchTimer = setTimeout(() => loadPurchaseList(val), 350);
    });

    /* ============ LOAD SUPPLIERS FOR DROPDOWN ============ */
    async function loadSuppliersDropdown(selectedId = null) {
        try {
            const res = await fetch('api/supplier/list.php');
            const result = await res.json();
            if (result.status === 'success') {
                suppliersCache = result.data;
                const select = document.getElementById('pSupplierSelect');
                select.innerHTML = '<option value="">-- No Supplier --</option>' +
                    suppliersCache.map(s => `<option value="${s.id}" ${selectedId == s.id ? 'selected' : ''}>${s.name} - ${s.mobile}</option>`).join('');
            }
        } catch (err) { /* silent */ }
    }

    /* ============ AUTO CALCULATION: Total ও Due ============ */
    function recalcTotal() {
        const price = parseFloat(document.getElementById('pPurchasePrice').value) || 0;
        const qty = parseFloat(document.getElementById('pQuantity').value) || 0;
        const total = price * qty;
        document.getElementById('pTotalDisplay').textContent = money(total);
        recalcDue();
    }

    function recalcDue() {
        const price = parseFloat(document.getElementById('pPurchasePrice').value) || 0;
        const qty = parseFloat(document.getElementById('pQuantity').value) || 0;
        const total = price * qty;
        let paid = parseFloat(document.getElementById('pPaidAmount').value) || 0;

        if (paid > total) {
            paid = total;
            document.getElementById('pPaidAmount').value = total.toFixed(2);
        }

        const due = total - paid;
        document.getElementById('pDueDisplay').textContent = money(due);
    }

    document.getElementById('pPurchasePrice').addEventListener('input', recalcTotal);
    document.getElementById('pQuantity').addEventListener('input', recalcTotal);
    document.getElementById('pPaidAmount').addEventListener('input', recalcDue);

    /* ============ OPEN NEW PURCHASE ============ */
    document.getElementById('btnNewPurchase').addEventListener('click', async () => {
        editMode = false;
        document.getElementById('purchaseModalTitle').textContent = '<?php echo lang('new_purchase'); ?>';
        document.getElementById('purchaseForm').reset();
        document.getElementById('purchaseEditId').value = '';
        document.getElementById('paymentBlock').style.display = 'block';
        document.getElementById('pPaidAmount').value = '';
        document.getElementById('pDueDisplay').textContent = '৳0.00';
        recalcTotal();

        await loadSuppliersDropdown();

        try {
            const res = await fetch('api/purchase/form_data.php');
            const result = await res.json();
            if (result.status === 'success') {
                availableCash = result.data.cash_balance;
                document.getElementById('pAvailableCashText').textContent = money(availableCash);
            }
        } catch (err) { /* silent */ }

        document.getElementById('purchaseSaveBtn').textContent = '<?php echo lang('save'); ?>';
        document.getElementById('purchaseFormOverlay').style.display = 'flex';
    });

    /* ============ OPEN EDIT PURCHASE ============ */
    window.openEditPurchase = async function (p) {
        editMode = true;
        document.getElementById('purchaseModalTitle').textContent = '<?php echo lang('edit'); ?> Purchase';
        document.getElementById('purchaseEditId').value = p.id;
        document.getElementById('pProductName').value = p.product_name;
        document.getElementById('pDescription').value = p.product_description || '';
        document.getElementById('pPurchasePrice').value = p.purchase_price;
        document.getElementById('pSalePrice').value = p.product_sale_price;
        document.getElementById('pQuantity').value = p.quantity;

        await loadSuppliersDropdown(p.supplier_id);

        document.getElementById('pTotalDisplay').textContent = money(p.total_amount);
        document.getElementById('paymentBlock').style.display = 'none';

        document.getElementById('purchaseSaveBtn').textContent = 'Update';
        document.getElementById('purchaseFormOverlay').style.display = 'flex';
    };

    /* ============ QUICK ADD SUPPLIER ============ */
    document.getElementById('btnQuickAddSupplier').addEventListener('click', () => {
        document.getElementById('quickSupplierForm').reset();
        document.getElementById('quickSupplierOverlay').style.display = 'flex';
    });

    document.getElementById('quickSupplierForm').addEventListener('submit', async function (e) {
        e.preventDefault();
        const payload = {
            name: document.getElementById('qsName').value.trim(),
            mobile: document.getElementById('qsMobile').value.trim(),
            address: ''
        };
        try {
            const res = await fetch('api/supplier/add.php', {
                method: 'POST', headers: { 'Content-Type': 'application/json' }, body: JSON.stringify(payload)
            });
            const result = await res.json();
            if (result.status === 'success') {
                ckToast('success', result.message);
                document.getElementById('quickSupplierOverlay').style.display = 'none';
                await loadSuppliersDropdown(result.data.id);
            } else {
                ckToast('error', result.message);
            }
        } catch (err) {
            ckToast('error', 'Failed to add supplier');
        }
    });

    /* ============ PAY DUE ============ */
    window.openPayDue = function (p) {
        document.getElementById('payDuePurchaseId').value = p.id;
        document.getElementById('payDueProductName').textContent = p.product_name;
        document.getElementById('payDueAmountText').textContent = money(p.due_amount);
        document.getElementById('payDueAmountInput').value = '';
        document.getElementById('payDueAmountInput').max = p.due_amount;
        document.getElementById('payDueOverlay').style.display = 'flex';
    };

    document.getElementById('payDueForm').addEventListener('submit', async function (e) {
        e.preventDefault();
        const payload = {
            id: document.getElementById('payDuePurchaseId').value,
            amount: document.getElementById('payDueAmountInput').value
        };
        try {
            const res = await fetch('api/purchase/pay_due.php', {
                method: 'POST', headers: { 'Content-Type': 'application/json' }, body: JSON.stringify(payload)
            });
            const result = await res.json();
            if (result.status === 'success') {
                ckToast('success', result.message);
                document.getElementById('payDueOverlay').style.display = 'none';
                updateCashBalance(result.cash_balance);
                loadPurchaseList(document.getElementById('purchaseSearch').value);
            } else {
                ckToast('error', result.message);
            }
        } catch (err) {
            ckToast('error', 'Failed to pay due');
        }
    });

    /* ============ CLOSE MODALS ============ */
    document.querySelectorAll('[data-close], .ck-modal-close').forEach(el => {
        el.addEventListener('click', function () {
            const target = this.dataset.close;
            if (target) document.getElementById(target).style.display = 'none';
        });
    });

    /* ============ SUBMIT (ADD OR UPDATE) ============ */
    document.getElementById('purchaseForm').addEventListener('submit', async function (e) {
        e.preventDefault();

        const editId = document.getElementById('purchaseEditId').value;
        const saveBtn = document.getElementById('purchaseSaveBtn');
        saveBtn.disabled = true;
        saveBtn.innerHTML = '<i class="fa-solid fa-spinner fa-spin"></i> Saving...';

        try {
            if (editId) {
                const payload = {
                    id: editId,
                    description: document.getElementById('pDescription').value.trim(),
                    purchase_price: document.getElementById('pPurchasePrice').value,
                    sale_price: document.getElementById('pSalePrice').value,
                    quantity: document.getElementById('pQuantity').value,
                    supplier_id: document.getElementById('pSupplierSelect').value
                };
                const res = await fetch('api/purchase/update.php', {
                    method: 'POST', headers: { 'Content-Type': 'application/json' }, body: JSON.stringify(payload)
                });
                const result = await res.json();
                if (result.status === 'success') {
                    ckToast('success', result.message);
                    document.getElementById('purchaseFormOverlay').style.display = 'none';
                    loadPurchaseList();
                } else {
                    ckToast('error', result.message);
                }
            } else {
                const payload = {
                    product_name: document.getElementById('pProductName').value.trim(),
                    description: document.getElementById('pDescription').value.trim(),
                    purchase_price: document.getElementById('pPurchasePrice').value,
                    sale_price: document.getElementById('pSalePrice').value,
                    quantity: document.getElementById('pQuantity').value,
                    supplier_id: document.getElementById('pSupplierSelect').value,
                    paid_amount: document.getElementById('pPaidAmount').value || 0
                };
                const res = await fetch('api/purchase/add.php', {
                    method: 'POST', headers: { 'Content-Type': 'application/json' }, body: JSON.stringify(payload)
                });
                const result = await res.json();
                if (result.status === 'success') {
                    ckToast('success', result.message);
                    document.getElementById('purchaseFormOverlay').style.display = 'none';
                    updateCashBalance(result.cash_balance);
                    loadPurchaseList();
                } else {
                    ckToast('error', result.message);
                }
            }
        } catch (err) {
            ckToast('error', 'Failed to save purchase');
        } finally {
            saveBtn.disabled = false;
            saveBtn.innerHTML = editId ? 'Update' : '<?php echo lang('save'); ?>';
        }
    });

    loadPurchaseList();
})();
</script>