<?php
require_once __DIR__ . '/../includes/auth_check.php';
require_once __DIR__ . '/../config/db.php';
$settings = $pdo->query("SELECT * FROM settings LIMIT 1")->fetch();
$currentLang = $_SESSION['language'] ?? 'en';
?>
<!-- ============ SETTINGS PAGE ============ -->
<div id="settingsPage">

    <div class="mb-3">
        <h4 class="mb-0" style="font-weight:600;"><?php echo lang('settings'); ?></h4>
        <p class="text-muted mb-0" style="font-size:13px;">Manage business information, language, customers and suppliers</p>
    </div>

    <!-- Tabs -->
    <div class="settings-tabs mb-3">
        <button class="settings-tab active" data-tab="business"><i class="fa-solid fa-store"></i> <?php echo lang('business_info'); ?></button>
        <button class="settings-tab" data-tab="language"><i class="fa-solid fa-language"></i> <?php echo lang('language'); ?></button>
        <button class="settings-tab" data-tab="customers"><i class="fa-solid fa-users"></i> <?php echo lang('customer'); ?></button>
        <button class="settings-tab" data-tab="suppliers"><i class="fa-solid fa-truck"></i> <?php echo lang('supplier'); ?></button>
    </div>

    <!-- ============ TAB: BUSINESS INFO ============ -->
    <div class="settings-tab-content active" id="tabBusiness">
        <div class="ck-card" style="max-width:520px;">
            <form id="businessInfoForm">
                <label class="ck-label"><?php echo lang('business_name'); ?></label>
                <input type="text" class="ck-input" id="businessNameInput" value="<?php echo htmlspecialchars($settings['business_name']); ?>" required>

                <label class="ck-label mt-2"><?php echo lang('address'); ?></label>
                <input type="text" class="ck-input" id="businessAddressInput" value="<?php echo htmlspecialchars($settings['business_address']); ?>">

                <label class="ck-label mt-2">Phone</label>
                <input type="text" class="ck-input" id="businessPhoneInput" value="<?php echo htmlspecialchars($settings['business_phone']); ?>">

                <button type="submit" class="ck-btn ck-btn-primary mt-3" id="businessSaveBtn">
                    <i class="fa-solid fa-check"></i> <?php echo lang('save'); ?>
                </button>
            </form>
        </div>

        <!-- ============ CASH BALANCE (MANUAL) ============ -->
        <div class="ck-card mt-3" style="max-width:520px;">
            <div class="d-flex align-items-center gap-3 mb-3">
                <div style="width:44px;height:44px;background:#eff6ff;color:#2563eb;border-radius:10px;display:flex;align-items:center;justify-content:center;font-size:17px;">
                    <i class="fa-solid fa-sack-dollar"></i>
                </div>
                <div>
                    <div style="font-weight:600;font-size:14px;"><?php echo lang('cash_balance'); ?></div>
                    <div class="text-muted" style="font-size:12px;">Current Balance: <span id="currentCashDisplay" style="font-weight:600;color:var(--primary-blue);">৳<?php echo number_format($settings['cash_balance'], 2); ?></span></div>
                </div>
            </div>

            <form id="cashBalanceForm">
                <label class="ck-label"><?php echo lang('opening_cash'); ?> / Set New Balance</label>
                <input type="number" step="0.01" min="0" class="ck-input" id="cashBalanceInput" placeholder="e.g. 10000" required>
                <p class="text-muted mb-0 mt-1" style="font-size:11px;">এখানে যে Amount দিবে, Cash Balance সেই Amount-এ সরাসরি Set হয়ে যাবে (Add না, Replace হবে)।</p>

                <button type="submit" class="ck-btn ck-btn-primary mt-3" id="cashBalanceSaveBtn">
                    <i class="fa-solid fa-check"></i> Update Cash Balance
                </button>
            </form>
        </div>
    </div>

    <!-- ============ TAB: LANGUAGE ============ -->
    <div class="settings-tab-content" id="tabLanguage">
        <div class="ck-card" style="max-width:520px;">
            <p class="text-muted mb-3" style="font-size:13px;">Select your preferred language. The interface will update immediately.</p>
            <div class="row g-3">
                <div class="col-6">
                    <div class="lang-option <?php echo $currentLang === 'en' ? 'active' : ''; ?>" data-lang="en">
                        <i class="fa-solid fa-check lang-check"></i>
                        <div style="font-size:22px;">🇬🇧</div>
                        <div style="font-weight:600;margin-top:8px;">English</div>
                    </div>
                </div>
                <div class="col-6">
                    <div class="lang-option <?php echo $currentLang === 'bn' ? 'active' : ''; ?>" data-lang="bn">
                        <i class="fa-solid fa-check lang-check"></i>
                        <div style="font-size:22px;">🇧🇩</div>
                        <div style="font-weight:600;margin-top:8px;">বাংলা</div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- ============ TAB: CUSTOMERS ============ -->
    <div class="settings-tab-content" id="tabCustomers">
        <div class="ck-card mb-3">
            <div class="input-group-search">
                <i class="fa-solid fa-magnifying-glass"></i>
                <input type="text" id="customerSearchInput" placeholder="<?php echo lang('search'); ?> customers...">
            </div>
        </div>
        <div class="ck-card p-0">
            <div class="table-responsive">
                <table class="ck-table">
                    <thead>
                        <tr>
                            <th><?php echo lang('name'); ?></th>
                            <th><?php echo lang('mobile'); ?></th>
                            <th><?php echo lang('customer_due'); ?></th>
                            <th><?php echo lang('action'); ?></th>
                        </tr>
                    </thead>
                    <tbody id="customerTableBody">
                        <tr><td colspan="4" class="text-center py-4 text-muted">Loading...</td></tr>
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <!-- ============ TAB: SUPPLIERS ============ -->
    <div class="settings-tab-content" id="tabSuppliers">
        <div class="ck-card mb-3">
            <div class="input-group-search">
                <i class="fa-solid fa-magnifying-glass"></i>
                <input type="text" id="supplierSearchInput" placeholder="<?php echo lang('search'); ?> suppliers...">
            </div>
        </div>
        <div class="ck-card p-0">
            <div class="table-responsive">
                <table class="ck-table">
                    <thead>
                        <tr>
                            <th><?php echo lang('name'); ?></th>
                            <th><?php echo lang('mobile'); ?></th>
                            <th><?php echo lang('supplier_due'); ?></th>
                            <th><?php echo lang('action'); ?></th>
                        </tr>
                    </thead>
                    <tbody id="supplierTableBody">
                        <tr><td colspan="4" class="text-center py-4 text-muted">Loading...</td></tr>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

<!-- ============ MODAL: RECEIVE PAYMENT (CUSTOMER) ============ -->
<div class="ck-modal-overlay" id="customerPaymentOverlay" style="display:none;">
    <div class="ck-modal-box" style="max-width:400px;">
        <div class="ck-modal-header">
            <h5><?php echo lang('receive_payment'); ?></h5>
            <i class="fa-solid fa-xmark ck-modal-close" data-close="customerPaymentOverlay"></i>
        </div>
        <form id="customerPaymentForm">
            <input type="hidden" id="paymentCustomerId">
            <p style="font-size:13px;">Customer: <strong id="paymentCustomerName"></strong></p>
            <p class="text-muted" style="font-size:12px;">Current Due: <span id="paymentCustomerDue" style="font-weight:600;color:var(--danger);"></span></p>

            <label class="ck-label mt-2"><?php echo lang('amount'); ?></label>
            <input type="number" step="0.01" min="0.01" class="ck-input" id="customerPaymentAmount" required>

            <div class="d-flex gap-2 mt-3">
                <button type="button" class="ck-btn ck-btn-outline flex-fill justify-content-center" data-close="customerPaymentOverlay"><?php echo lang('cancel'); ?></button>
                <button type="submit" class="ck-btn ck-btn-primary flex-fill justify-content-center"><?php echo lang('save'); ?></button>
            </div>
        </form>
    </div>
</div>

<!-- ============ MODAL: MAKE PAYMENT (SUPPLIER) ============ -->
<div class="ck-modal-overlay" id="supplierPaymentOverlay" style="display:none;">
    <div class="ck-modal-box" style="max-width:400px;">
        <div class="ck-modal-header">
            <h5><?php echo lang('make_payment'); ?></h5>
            <i class="fa-solid fa-xmark ck-modal-close" data-close="supplierPaymentOverlay"></i>
        </div>
        <form id="supplierPaymentForm">
            <input type="hidden" id="paymentSupplierId">
            <p style="font-size:13px;">Supplier: <strong id="paymentSupplierName"></strong></p>
            <p class="text-muted" style="font-size:12px;">Current Due: <span id="paymentSupplierDue" style="font-weight:600;color:var(--danger);"></span></p>

            <label class="ck-label mt-2"><?php echo lang('amount'); ?></label>
            <input type="number" step="0.01" min="0.01" class="ck-input" id="supplierPaymentAmount" required>

            <div class="d-flex gap-2 mt-3">
                <button type="button" class="ck-btn ck-btn-outline flex-fill justify-content-center" data-close="supplierPaymentOverlay"><?php echo lang('cancel'); ?></button>
                <button type="submit" class="ck-btn ck-btn-primary flex-fill justify-content-center"><?php echo lang('save'); ?></button>
            </div>
        </form>
    </div>
</div>

<!-- ============ PAGE-SPECIFIC STYLES ============ -->
<style>
    .settings-tabs {
        display: flex; gap: 6px; overflow-x: auto; padding-bottom: 4px;
    }
    .settings-tab {
        border: 1.5px solid var(--border-color); background: #fff; color: var(--text-muted);
        padding: 10px 16px; border-radius: 10px; font-size: 13px; font-weight: 500;
        white-space: nowrap; cursor: pointer; display: flex; align-items: center; gap: 8px; transition: all 0.2s ease;
    }
    .settings-tab.active { background: var(--primary-blue); border-color: var(--primary-blue); color: #fff; }
    .settings-tab:hover:not(.active) { border-color: var(--primary-blue); color: var(--primary-blue); }

    .settings-tab-content { display: none; }
    .settings-tab-content.active { display: block; }

    .lang-option {
        border: 1.5px solid var(--border-color); border-radius: 12px; padding: 20px;
        text-align: center; cursor: pointer; position: relative; transition: all 0.2s ease;
    }
    .lang-option:hover { border-color: var(--primary-blue); }
    .lang-option.active { border-color: var(--primary-blue); background: var(--light-blue); }
    .lang-option .lang-check {
        position: absolute; top: 10px; right: 10px; color: var(--primary-blue);
        font-size: 12px; opacity: 0; transition: opacity 0.2s ease;
    }
    .lang-option.active .lang-check { opacity: 1; }
</style>

<!-- ============ PAGE-SPECIFIC SCRIPT ============ -->
<script>
(function () {
    function money(v) {
        return '৳' + parseFloat(v || 0).toLocaleString('en-US', { minimumFractionDigits: 2, maximumFractionDigits: 2 });
    }

    /* ============ TABS ============ */
    document.querySelectorAll('.settings-tab').forEach(tab => {
        tab.addEventListener('click', function () {
            document.querySelectorAll('.settings-tab').forEach(t => t.classList.remove('active'));
            document.querySelectorAll('.settings-tab-content').forEach(c => c.classList.remove('active'));

            this.classList.add('active');
            const target = document.getElementById('tab' + this.dataset.tab.charAt(0).toUpperCase() + this.dataset.tab.slice(1));
            target.classList.add('active');
            gsap.from(target, { opacity: 0, y: 10, duration: 0.3, ease: "power2.out" });

            if (this.dataset.tab === 'customers') loadCustomers();
            if (this.dataset.tab === 'suppliers') loadSuppliers();
        });
    });

    /* ============ BUSINESS INFO SAVE ============ */
    document.getElementById('businessInfoForm').addEventListener('submit', async function (e) {
        e.preventDefault();
        const payload = {
            business_name: document.getElementById('businessNameInput').value.trim(),
            business_address: document.getElementById('businessAddressInput').value.trim(),
            business_phone: document.getElementById('businessPhoneInput').value.trim()
        };

        const btn = document.getElementById('businessSaveBtn');
        btn.disabled = true;
        btn.innerHTML = '<i class="fa-solid fa-spinner fa-spin"></i> Saving...';

        try {
            const res = await fetch('api/settings/update.php', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify(payload)
            });
            const result = await res.json();

            if (result.status === 'success') {
                ckToast('success', result.message);
                document.querySelector('.sidebar-brand .brand-text').textContent = payload.business_name;
                document.querySelector('.topbar-left .brand-text').textContent = payload.business_name;
            } else {
                ckToast('error', result.message);
            }
        } catch (err) {
            ckToast('error', 'Failed to update business info');
        } finally {
            btn.disabled = false;
            btn.innerHTML = '<i class="fa-solid fa-check"></i> <?php echo lang('save'); ?>';
        }
    });

    /* ============ CASH BALANCE MANUAL UPDATE ============ */
    document.getElementById('cashBalanceForm').addEventListener('submit', async function (e) {
        e.preventDefault();
        const amount = document.getElementById('cashBalanceInput').value;

        const confirmResult = await ckConfirm('This will directly set your Cash Balance to this amount.');
        if (!confirmResult.isConfirmed) return;

        const btn = document.getElementById('cashBalanceSaveBtn');
        btn.disabled = true;
        btn.innerHTML = '<i class="fa-solid fa-spinner fa-spin"></i> Saving...';

        try {
            const res = await fetch('api/settings/opening_cash.php', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify({ amount: amount })
            });
            const result = await res.json();

            if (result.status === 'success') {
                ckToast('success', 'Cash Balance updated successfully');
                document.getElementById('currentCashDisplay').textContent = money(result.cash_balance);
                updateCashBalance(result.cash_balance);
                document.getElementById('cashBalanceForm').reset();
            } else {
                ckToast('error', result.message);
            }
        } catch (err) {
            ckToast('error', 'Failed to update cash balance');
        } finally {
            btn.disabled = false;
            btn.innerHTML = '<i class="fa-solid fa-check"></i> Update Cash Balance';
        }
    });

    /* ============ LANGUAGE SWITCH ============ */
    document.querySelectorAll('.lang-option').forEach(opt => {
        opt.addEventListener('click', async function () {
            const lang = this.dataset.lang;
            if (this.classList.contains('active')) return;

            try {
                const res = await fetch('api/settings/language.php', {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json' },
                    body: JSON.stringify({ language: lang })
                });
                const result = await res.json();

                if (result.status === 'success') {
                    ckToast('success', result.message);
                    setTimeout(() => window.location.href = 'index.php#settings', 600);
                } else {
                    ckToast('error', result.message);
                }
            } catch (err) {
                ckToast('error', 'Failed to change language');
            }
        });
    });

    /* ============ CUSTOMERS LIST ============ */
    async function loadCustomers(search = '') {
        const tbody = document.getElementById('customerTableBody');
        try {
            const res = await fetch('api/customer/list.php?search=' + encodeURIComponent(search));
            const result = await res.json();

            if (result.status !== 'success' || result.data.length === 0) {
                tbody.innerHTML = `<tr><td colspan="4" class="text-center py-4 text-muted"><?php echo lang('no_data'); ?></td></tr>`;
                return;
            }

            tbody.innerHTML = result.data.map(c => `
                <tr>
                    <td style="font-weight:500;">${c.name}</td>
                    <td>${c.mobile}</td>
                    <td style="font-weight:600;color:${c.due > 0 ? 'var(--danger)' : 'var(--success)'};">${money(c.due)}</td>
                    <td>
                        <div class="d-flex gap-2">
                            ${c.due > 0 ? `<button class="ck-btn ck-btn-primary" style="padding:6px 12px;font-size:11px;" onclick="openCustomerPayment(${c.id}, '${c.name.replace(/'/g, "\\'")}', ${c.due})"><i class="fa-solid fa-hand-holding-dollar"></i> <?php echo lang('receive_payment'); ?></button>` : ''}
                            <button class="icon-btn ck-btn-danger-soft" onclick="deleteCustomer(${c.id})"><i class="fa-solid fa-trash"></i></button>
                        </div>
                    </td>
                </tr>
            `).join('');
        } catch (err) {
            tbody.innerHTML = `<tr><td colspan="4" class="text-center py-4 text-danger">Error loading data</td></tr>`;
        }
    }

    window.openCustomerPayment = function (id, name, due) {
        document.getElementById('paymentCustomerId').value = id;
        document.getElementById('paymentCustomerName').textContent = name;
        document.getElementById('paymentCustomerDue').textContent = money(due);
        document.getElementById('customerPaymentAmount').value = '';
        document.getElementById('customerPaymentAmount').max = due;
        document.getElementById('customerPaymentOverlay').style.display = 'flex';
    };

    document.getElementById('customerPaymentForm').addEventListener('submit', async function (e) {
        e.preventDefault();
        const payload = {
            customer_id: document.getElementById('paymentCustomerId').value,
            amount: document.getElementById('customerPaymentAmount').value
        };

        try {
            const res = await fetch('api/customer/payment.php', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify(payload)
            });
            const result = await res.json();

            if (result.status === 'success') {
                ckToast('success', result.message);
                document.getElementById('customerPaymentOverlay').style.display = 'none';
                updateCashBalance(result.cash_balance);
                loadCustomers();
            } else {
                ckToast('error', result.message);
            }
        } catch (err) {
            ckToast('error', 'Failed to process payment');
        }
    });

    window.deleteCustomer = async function (id) {
        const confirmResult = await ckConfirm('This customer will be permanently deleted.');
        if (!confirmResult.isConfirmed) return;

        try {
            const res = await fetch('api/customer/delete.php', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify({ id })
            });
            const result = await res.json();

            if (result.status === 'success') {
                ckToast('success', result.message);
                loadCustomers();
            } else {
                ckToast('error', result.message);
            }
        } catch (err) {
            ckToast('error', 'Failed to delete customer');
        }
    };

    /* ============ SUPPLIERS LIST ============ */
    async function loadSuppliers(search = '') {
        const tbody = document.getElementById('supplierTableBody');
        try {
            const res = await fetch('api/supplier/list.php?search=' + encodeURIComponent(search));
            const result = await res.json();

            if (result.status !== 'success' || result.data.length === 0) {
                tbody.innerHTML = `<tr><td colspan="4" class="text-center py-4 text-muted"><?php echo lang('no_data'); ?></td></tr>`;
                return;
            }

            tbody.innerHTML = result.data.map(s => `
                <tr>
                    <td style="font-weight:500;">${s.name}</td>
                    <td>${s.mobile}</td>
                    <td style="font-weight:600;color:${s.due > 0 ? 'var(--danger)' : 'var(--success)'};">${money(s.due)}</td>
                    <td>
                        <div class="d-flex gap-2">
                            ${s.due > 0 ? `<button class="ck-btn ck-btn-primary" style="padding:6px 12px;font-size:11px;" onclick="openSupplierPayment(${s.id}, '${s.name.replace(/'/g, "\\'")}', ${s.due})"><i class="fa-solid fa-money-bill-transfer"></i> <?php echo lang('make_payment'); ?></button>` : ''}
                            <button class="icon-btn ck-btn-danger-soft" onclick="deleteSupplier(${s.id})"><i class="fa-solid fa-trash"></i></button>
                        </div>
                    </td>
                </tr>
            `).join('');
        } catch (err) {
            tbody.innerHTML = `<tr><td colspan="4" class="text-center py-4 text-danger">Error loading data</td></tr>`;
        }
    }

    window.openSupplierPayment = function (id, name, due) {
        document.getElementById('paymentSupplierId').value = id;
        document.getElementById('paymentSupplierName').textContent = name;
        document.getElementById('paymentSupplierDue').textContent = money(due);
        document.getElementById('supplierPaymentAmount').value = '';
        document.getElementById('supplierPaymentAmount').max = due;
        document.getElementById('supplierPaymentOverlay').style.display = 'flex';
    };

    document.getElementById('supplierPaymentForm').addEventListener('submit', async function (e) {
        e.preventDefault();
        const payload = {
            supplier_id: document.getElementById('paymentSupplierId').value,
            amount: document.getElementById('supplierPaymentAmount').value
        };

        try {
            const res = await fetch('api/supplier/payment.php', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify(payload)
            });
            const result = await res.json();

            if (result.status === 'success') {
                ckToast('success', result.message);
                document.getElementById('supplierPaymentOverlay').style.display = 'none';
                updateCashBalance(result.cash_balance);
                loadSuppliers();
            } else {
                ckToast('error', result.message);
            }
        } catch (err) {
            ckToast('error', 'Failed to process payment');
        }
    });

    window.deleteSupplier = async function (id) {
        const confirmResult = await ckConfirm('This supplier will be permanently deleted.');
        if (!confirmResult.isConfirmed) return;

        try {
            const res = await fetch('api/supplier/delete.php', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify({ id })
            });
            const result = await res.json();

            if (result.status === 'success') {
                ckToast('success', result.message);
                loadSuppliers();
            } else {
                ckToast('error', result.message);
            }
        } catch (err) {
            ckToast('error', 'Failed to delete supplier');
        }
    };

    /* ============ CLOSE MODALS ============ */
    document.querySelectorAll('[data-close], .ck-modal-close').forEach(el => {
        el.addEventListener('click', function () {
            const target = this.dataset.close;
            if (target) document.getElementById(target).style.display = 'none';
        });
    });

    /* ============ SEARCH ============ */
    let custTimer;
    document.getElementById('customerSearchInput').addEventListener('input', function () {
        clearTimeout(custTimer);
        const val = this.value;
        custTimer = setTimeout(() => loadCustomers(val), 350);
    });

    let suppTimer;
    document.getElementById('supplierSearchInput').addEventListener('input', function () {
        clearTimeout(suppTimer);
        const val = this.value;
        suppTimer = setTimeout(() => loadSuppliers(val), 350);
    });
})();
</script>