<?php
require_once __DIR__ . '/../includes/auth_check.php';
?>
<div id="purchasePage">

    <div class="d-flex justify-content-between align-items-center mb-3 flex-wrap gap-2">
        <div>
            <h4 class="mb-0" style="font-weight:600;"><?php echo lang('purchase'); ?></h4>
            <p class="text-muted mb-0" style="font-size:13px;">Manage product purchases and stock</p>
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

    <div class="ck-card p-0">
        <div class="table-responsive">
            <table class="ck-table">
                <thead>
                    <tr>
                        <th><?php echo lang('product_name'); ?></th>
                        <th><?php echo lang('supplier'); ?></th>
                        <th><?php echo lang('quantity'); ?></th>
                        <th><?php echo lang('purchase_price'); ?></th>
                        <th><?php echo lang('total_amount'); ?></th>
                        <th><?php echo lang('payment_type'); ?></th>
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

<!-- ============ MODAL: NEW PURCHASE ============ -->
<div class="ck-modal-overlay" id="newPurchaseOverlay" style="display:none;">
    <div class="ck-modal-box" style="max-width:520px;">
        <div class="ck-modal-header">
            <h5><?php echo lang('new_purchase'); ?></h5>
            <i class="fa-solid fa-xmark ck-modal-close" data-close="newPurchaseOverlay"></i>
        </div>

        <form id="newPurchaseForm">

            <div class="ck-toggle-tabs mb-3">
                <button type="button" class="ck-toggle-btn active" data-mode="existing">Existing Product</button>
                <button type="button" class="ck-toggle-btn" data-mode="new">New Product</button>
            </div>

            <div id="existingProductBlock">
                <label class="ck-label"><?php echo lang('product_name'); ?></label>
                <select class="ck-select" id="existingProductSelect">
                    <option value="">-- Select Product --</option>
                </select>
            </div>

            <div id="newProductBlock" style="display:none;">
                <label class="ck-label"><?php echo lang('product_name'); ?></label>
                <input type="text" class="ck-input" id="newProductName">

                <label class="ck-label mt-2"><?php echo lang('description'); ?> <span class="text-muted">(optional)</span></label>
                <input type="text" class="ck-input" id="newProductDescription">

                <label class="ck-label mt-2"><?php echo lang('sale_price'); ?></label>
                <input type="number" step="0.01" min="0" class="ck-input" id="newProductSalePrice">

                <label class="ck-label mt-2"><?php echo lang('low_stock_alert'); ?></label>
                <input type="number" min="1" class="ck-input" id="newProductLowStock" value="5">
            </div>

            <div class="row g-2 mt-1">
                <div class="col-6">
                    <label class="ck-label"><?php echo lang('purchase_price'); ?></label>
                    <input type="number" step="0.01" min="0.01" class="ck-input" id="purchasePriceInput" required>
                </div>
                <div class="col-6">
                    <label class="ck-label"><?php echo lang('quantity'); ?></label>
                    <input type="number" min="1" class="ck-input" id="purchaseQuantityInput" required>
                </div>
            </div>

            <label class="ck-label mt-2"><?php echo lang('supplier'); ?></label>
            <select class="ck-select" id="purchaseSupplierSelect">
                <option value="">-- No Supplier --</option>
            </select>

            <div class="ck-total-box mt-3">
                <span><?php echo lang('total_amount'); ?></span>
                <span id="purchaseTotalDisplay">৳0.00</span>
            </div>

            <label class="ck-label mt-3"><?php echo lang('payment_type'); ?></label>
            <div class="ck-toggle-tabs">
                <button type="button" class="ck-toggle-btn active" data-payment="cash"><?php echo lang('cash'); ?></button>
                <button type="button" class="ck-toggle-btn" data-payment="due"><?php echo lang('due'); ?></button>
            </div>

            <div id="paidAmountBlock" class="mt-2">
                <label class="ck-label"><?php echo lang('paid_amount'); ?></label>
                <input type="number" step="0.01" min="0" class="ck-input" id="purchasePaidAmountInput">
                <p class="text-muted mb-0 mt-1" style="font-size:11px;">Available Cash: <span id="availableCashText">৳0.00</span></p>
            </div>

            <div class="d-flex gap-2 mt-3">
                <button type="button" class="ck-btn ck-btn-outline flex-fill justify-content-center" data-close="newPurchaseOverlay"><?php echo lang('cancel'); ?></button>
                <button type="submit" class="ck-btn ck-btn-primary flex-fill justify-content-center" id="purchaseSaveBtn"><?php echo lang('save'); ?></button>
            </div>
        </form>
    </div>
</div>

<script>
(function () {
    let productsCache = [];
    let suppliersCache = [];
    let availableCash = 0;

    function money(v) {
        return '৳' + parseFloat(v || 0).toLocaleString('en-US', { minimumFractionDigits: 2, maximumFractionDigits: 2 });
    }

    function formatDate(dateStr) {
        const d = new Date(dateStr.replace(' ', 'T'));
        return d.toLocaleDateString('en-GB', { day: '2-digit', month: 'short', year: 'numeric' });
    }

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

            tbody.innerHTML = result.data.map(p => `
                <tr>
                    <td data-label="<?php echo lang('product_name'); ?>" style="font-weight:500;">${p.product_name}</td>
                    <td data-label="<?php echo lang('supplier'); ?>">${p.supplier_name ? p.supplier_name : '<span class="text-muted">—</span>'}</td>
                    <td data-label="<?php echo lang('quantity'); ?>">${p.quantity}</td>
                    <td data-label="<?php echo lang('purchase_price'); ?>">${money(p.purchase_price)}</td>
                    <td data-label="<?php echo lang('total_amount'); ?>" style="font-weight:600;">${money(p.total_amount)}</td>
                    <td data-label="<?php echo lang('payment_type'); ?>"><span class="${p.payment_type === 'cash' ? 'badge-cash' : 'badge-due'}">${p.payment_type === 'cash' ? '<?php echo lang('cash'); ?>' : '<?php echo lang('due'); ?>'}</span></td>
                    <td data-label="<?php echo lang('date'); ?>">${formatDate(p.created_at)}</td>
                    <td data-label="<?php echo lang('action'); ?>">
                        <button class="icon-btn ck-btn-danger-soft" onclick="deletePurchase(${p.id})"><i class="fa-solid fa-trash"></i></button>
                    </td>
                </tr>
            `).join('');
        } catch (err) {
            tbody.innerHTML = `<tr><td colspan="8" class="text-center py-4 text-danger">Error loading data</td></tr>`;
        }
    }

    window.deletePurchase = async function (id) {
        const confirmResult = await ckConfirm('This will reverse stock and cash balance for this purchase.');
        if (!confirmResult.isConfirmed) return;

        try {
            const res = await fetch('api/purchase/delete.php', {
                method: 'POST', headers: { 'Content-Type': 'application/json' }, body: JSON.stringify({ id })
            });
            const result = await res.json();

            if (result.status === 'success') {
                ckToast('success', result.message);
                updateCashBalance(result.cash_balance);
                loadPurchaseList(document.getElementById('purchaseSearch').value);
            } else {
                ckToast('error', result.message);
            }
        } catch (err) {
            ckToast('error', 'Failed to delete purchase');
        }
    };

    let searchTimer;
    document.getElementById('purchaseSearch').addEventListener('input', function () {
        clearTimeout(searchTimer);
        const val = this.value;
        searchTimer = setTimeout(() => loadPurchaseList(val), 350);
    });

    document.getElementById('btnNewPurchase').addEventListener('click', async () => {
        document.getElementById('newPurchaseOverlay').style.display = 'flex';
        document.getElementById('newPurchaseForm').reset();
        recalcTotal();

        try {
            const res = await fetch('api/purchase/form_data.php');
            const result = await res.json();
            if (result.status === 'success') {
                productsCache = result.data.products;
                suppliersCache = result.data.suppliers;
                availableCash = result.data.cash_balance;

                document.getElementById('availableCashText').textContent = money(availableCash);

                const productSelect = document.getElementById('existingProductSelect');
                productSelect.innerHTML = '<option value="">-- Select Product --</option>' +
                    productsCache.map(p => `<option value="${p.id}" data-price="${p.purchase_price}">${p.name} (Stock: ${p.stock})</option>`).join('');

                const supplierSelect = document.getElementById('purchaseSupplierSelect');
                supplierSelect.innerHTML = '<option value="">-- No Supplier --</option>' +
                    suppliersCache.map(s => `<option value="${s.id}">${s.name} - ${s.mobile}</option>`).join('');
            }
        } catch (err) {
            ckToast('error', 'Failed to load form data');
        }
    });

    document.querySelectorAll('#newPurchaseForm .ck-toggle-tabs')[0].querySelectorAll('.ck-toggle-btn').forEach(btn => {
        btn.addEventListener('click', function () {
            this.parentElement.querySelectorAll('.ck-toggle-btn').forEach(b => b.classList.remove('active'));
            this.classList.add('active');
            const mode = this.dataset.mode;
            document.getElementById('existingProductBlock').style.display = mode === 'existing' ? 'block' : 'none';
            document.getElementById('newProductBlock').style.display = mode === 'new' ? 'block' : 'none';
        });
    });

    document.querySelectorAll('#newPurchaseForm .ck-toggle-tabs')[1].querySelectorAll('.ck-toggle-btn').forEach(btn => {
        btn.addEventListener('click', function () {
            this.parentElement.querySelectorAll('.ck-toggle-btn').forEach(b => b.classList.remove('active'));
            this.classList.add('active');
            const type = this.dataset.payment;
            document.getElementById('paidAmountBlock').style.display = type === 'cash' ? 'block' : 'none';
        });
    });

    function recalcTotal() {
        const price = parseFloat(document.getElementById('purchasePriceInput').value) || 0;
        const qty = parseFloat(document.getElementById('purchaseQuantityInput').value) || 0;
        const total = price * qty;
        document.getElementById('purchaseTotalDisplay').textContent = money(total);

        const cashActive = document.querySelectorAll('#newPurchaseForm .ck-toggle-tabs')[1].querySelector('.active').dataset.payment === 'cash';
        if (cashActive) {
            document.getElementById('purchasePaidAmountInput').value = total.toFixed(2);
        }
    }
    document.getElementById('purchasePriceInput').addEventListener('input', recalcTotal);
    document.getElementById('purchaseQuantityInput').addEventListener('input', recalcTotal);

    document.getElementById('existingProductSelect').addEventListener('change', function () {
        const selected = this.options[this.selectedIndex];
        const price = selected.dataset.price;
        if (price) {
            document.getElementById('purchasePriceInput').value = price;
            recalcTotal();
        }
    });

    document.querySelectorAll('#newPurchaseOverlay [data-close], #newPurchaseOverlay .ck-modal-close').forEach(el => {
        el.addEventListener('click', function () {
            document.getElementById('newPurchaseOverlay').style.display = 'none';
        });
    });

    document.getElementById('newPurchaseForm').addEventListener('submit', async function (e) {
        e.preventDefault();

        const mode = document.querySelectorAll('#newPurchaseForm .ck-toggle-tabs')[0].querySelector('.active').dataset.mode;
        const paymentType = document.querySelectorAll('#newPurchaseForm .ck-toggle-tabs')[1].querySelector('.active').dataset.payment;

        const payload = {
            purchase_price: document.getElementById('purchasePriceInput').value,
            quantity: document.getElementById('purchaseQuantityInput').value,
            supplier_id: document.getElementById('purchaseSupplierSelect').value,
            payment_type: paymentType,
            paid_amount: paymentType === 'cash' ? document.getElementById('purchasePaidAmountInput').value : 0
        };

        if (mode === 'existing') {
            payload.product_id = document.getElementById('existingProductSelect').value;
            if (!payload.product_id) { ckToast('warning', 'Please select a product'); return; }
        } else {
            payload.product_name = document.getElementById('newProductName').value.trim();
            payload.description = document.getElementById('newProductDescription').value.trim();
            payload.sale_price = document.getElementById('newProductSalePrice').value;
            payload.low_stock_alert = document.getElementById('newProductLowStock').value;
            if (!payload.product_name) { ckToast('warning', 'Please enter product name'); return; }
        }

        const saveBtn = document.getElementById('purchaseSaveBtn');
        saveBtn.disabled = true;
        saveBtn.innerHTML = '<i class="fa-solid fa-spinner fa-spin"></i> Saving...';

        try {
            const res = await fetch('api/purchase/add.php', {
                method: 'POST', headers: { 'Content-Type': 'application/json' }, body: JSON.stringify(payload)
            });
            const result = await res.json();

            if (result.status === 'success') {
                ckToast('success', result.message);
                document.getElementById('newPurchaseOverlay').style.display = 'none';
                updateCashBalance(result.cash_balance);
                loadPurchaseList();
            } else {
                ckToast('error', result.message);
            }
        } catch (err) {
            ckToast('error', 'Failed to save purchase');
        } finally {
            saveBtn.disabled = false;
            saveBtn.innerHTML = '<?php echo lang('save'); ?>';
        }
    });

    loadPurchaseList();
})();
</script>