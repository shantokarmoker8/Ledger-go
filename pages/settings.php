<?php
require_once __DIR__ . '/../includes/auth_check.php';
require_once __DIR__ . '/../config/db.php';
$settings = $pdo->query("SELECT * FROM settings LIMIT 1")->fetch();
$currentLang = $_SESSION['language'] ?? 'en';
?>
<div id="settingsPage">

    <div class="mb-3">
        <h4 class="mb-0" style="font-weight:600;"><?php echo lang('settings'); ?></h4>
        <p class="text-muted mb-0" style="font-size:13px;">Manage business, cash, users, customers and suppliers</p>
    </div>

    <!-- Tabs -->
    <div class="settings-tabs mb-3">
        <button class="settings-tab active" data-tab="business"><i class="fa-solid fa-store"></i> <?php echo lang('business_info'); ?></button>
        <button class="settings-tab" data-tab="cash"><i class="fa-solid fa-sack-dollar"></i> <?php echo lang('cash_balance'); ?></button>
        <button class="settings-tab" data-tab="users"><i class="fa-solid fa-user-group"></i> Users</button>
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
    </div>

    <!-- ============ TAB: CASH BALANCE (ADD / WITHDRAW / HISTORY) ============ -->
    <div class="settings-tab-content" id="tabCash">
        <div class="row g-3">
            <div class="col-lg-5">
                <div class="ck-card mb-3">
                    <div class="d-flex align-items-center gap-3 mb-1">
                        <div style="width:44px;height:44px;background:#eff6ff;color:#2563eb;border-radius:10px;display:flex;align-items:center;justify-content:center;font-size:17px;flex-shrink:0;">
                            <i class="fa-solid fa-sack-dollar"></i>
                        </div>
                        <div>
                            <div class="text-muted" style="font-size:12px;"><?php echo lang('cash_balance'); ?></div>
                            <div id="currentCashDisplay" style="font-weight:700;font-size:19px;color:var(--primary-blue);">৳<?php echo number_format($settings['cash_balance'], 2); ?></div>
                        </div>
                    </div>
                </div>

                <div class="ck-card">
                    <div class="ck-toggle-tabs mb-3">
                        <button type="button" class="ck-toggle-btn active" data-cashmode="add"><i class="fa-solid fa-plus"></i> Add Cash</button>
                        <button type="button" class="ck-toggle-btn" data-cashmode="withdraw"><i class="fa-solid fa-minus"></i> Withdraw Cash</button>
                    </div>

                    <form id="cashTransactionForm">
                        <label class="ck-label"><?php echo lang('amount'); ?></label>
                        <input type="number" step="0.01" min="0.01" class="ck-input" id="cashAmountInput" placeholder="e.g. 5000" required>

                        <label class="ck-label mt-2">Note <span class="text-muted">(optional)</span></label>
                        <input type="text" class="ck-input" id="cashNoteInput" placeholder="e.g. Personal use, Extra investment">

                        <button type="submit" class="ck-btn ck-btn-primary w-100 justify-content-center mt-3" id="cashTransactionSaveBtn">
                            <i class="fa-solid fa-check"></i> <span id="cashTransactionBtnText">Add Cash</span>
                        </button>
                    </form>
                </div>
            </div>

            <div class="col-lg-7">
                <div class="ck-card p-0">
                    <div class="p-3 pb-0">
                        <h6 style="font-weight:600;margin-bottom:0;">Transaction History</h6>
                    </div>
                    <div class="table-responsive">
                        <table class="ck-table">
                            <thead>
                                <tr>
                                    <th>Type</th>
                                    <th><?php echo lang('amount'); ?></th>
                                    <th>Note</th>
                                    <th>By</th>
                                    <th><?php echo lang('date'); ?></th>
                                </tr>
                            </thead>
                            <tbody id="cashHistoryTableBody">
                                <tr><td colspan="5" class="text-center py-4 text-muted">Loading...</td></tr>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- ============ TAB: USERS (EMPLOYEE LOGIN) ============ -->
    <div class="settings-tab-content" id="tabUsers">
        <div class="d-flex justify-content-between align-items-center mb-3">
            <p class="text-muted mb-0" style="font-size:13px;">Add employees who can log in to this system.</p>
            <button class="ck-btn ck-btn-primary" id="btnAddUser">
                <i class="fa-solid fa-user-plus"></i> Add User
            </button>
        </div>
        <div class="ck-card p-0">
            <div class="table-responsive">
                <table class="ck-table">
                    <thead>
                        <tr>
                            <th><?php echo lang('name'); ?></th>
                            <th><?php echo lang('username'); ?></th>
                            <th>Joined</th>
                            <th><?php echo lang('action'); ?></th>
                        </tr>
                    </thead>
                    <tbody id="userTableBody">
                        <tr><td colspan="4" class="text-center py-4 text-muted">Loading...</td></tr>
                    </tbody>
                </table>
            </div>
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

<!-- ============ MODAL: ADD USER ============ -->
<div class="ck-modal-overlay" id="addUserOverlay" style="display:none;">
    <div class="ck-modal-box" style="max-width:400px;">
        <div class="ck-modal-header">
            <h5>Add User</h5>
            <i class="fa-solid fa-xmark ck-modal-close" data-close="addUserOverlay"></i>
        </div>
        <form id="addUserForm">
            <label class="ck-label"><?php echo lang('name'); ?></label>
            <input type="text" class="ck-input" id="userFullName" required>

            <label class="ck-label mt-2"><?php echo lang('username'); ?></label>
            <input type="text" class="ck-input" id="userUsername" required>

            <label class="ck-label mt-2"><?php echo lang('password'); ?></label>
            <input type="text" class="ck-input" id="userPassword" required>

            <div class="d-flex gap-2 mt-3">
                <button type="button" class="ck-btn ck-btn-outline flex-fill justify-content-center" data-close="addUserOverlay"><?php echo lang('cancel'); ?></button>
                <button type="submit" class="ck-btn ck-btn-primary flex-fill justify-content-center"><?php echo lang('save'); ?></button>
            </div>
        </form>
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
    .settings-tabs { display: flex; gap: 6px; overflow-x: auto; padding-bottom: 4px; }
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

    .cash-type-badge-add { background: #f0fdf4; color: #16a34a; font-size: 10px; padding: 3px 8px; border-radius: 6px; }
    .cash-type-badge-withdraw { background: #fef2f2; color: #dc2626; font-size: 10px; padding: 3px 8px; border-radius: 6px; }
</style>

<!-- ============ PAGE-SPECIFIC SCRIPT ============ -->
<script>
(function () {
    function money(v) {
        return '৳' + parseFloat(v || 0).toLocaleString('en-US', { minimumFractionDigits: 2, maximumFractionDigits: 2 });
    }

    function formatDate(dateStr) {
        const d = new Date(dateStr.replace(' ', 'T'));
        return d.toLocaleDateString('en-GB', { day: '2-digit', month: 'short', year: 'numeric', hour: '2-digit', minute: '2-digit' });
    }

    /* ============ TABS ============ */
    document.querySelectorAll('.settings-tab').forEach(tab => {
        tab.addEventListener('click', function () {
            document.querySelectorAll('.settings-tab').forEach(t => t.classList.remove('active'));
            document.querySelectorAll('.settings-tab-content').forEach(c => c.classList.remove('active'));

            this.classList.add('active');
            const target = document.getElementById('tab' + this.dataset.tab.charAt(0).toUpperCase() + this.dataset.tab.slice(1));
            target.classList.add('active');
            gsap.fromTo(target, { opacity: 0, y: 10 }, { opacity: 1, y: 0, duration: 0.3, ease: "power2.out", clearProps: "opacity,transform" });

            if (this.dataset.tab === 'cash') loadCashHistory();
            if (this.dataset.tab === 'users') loadUsers();
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
                method: 'POST', headers: { 'Content-Type': 'application/json' }, body: JSON.stringify(payload)
            });
            const result = await res.json();

            if (result.status === 'success') {
                ckToast('success', result.message);
                document.querySelector('.sidebar-brand .brand-text').textContent = payload.business_name;
                const topBrand = document.querySelector('.topbar-left .brand-text');
                if (topBrand) topBrand.textContent = payload.business_name;
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

    /* ============ CASH ADD / WITHDRAW ============ */
    let cashMode = 'add';

    document.querySelectorAll('#tabCash .ck-toggle-btn').forEach(btn => {
        btn.addEventListener('click', function () {
            document.querySelectorAll('#tabCash .ck-toggle-btn').forEach(b => b.classList.remove('active'));
            this.classList.add('active');
            cashMode = this.dataset.cashmode;
            document.getElementById('cashTransactionBtnText').textContent = cashMode === 'add' ? 'Add Cash' : 'Withdraw Cash';
        });
    });

    document.getElementById('cashTransactionForm').addEventListener('submit', async function (e) {
        e.preventDefault();
        const payload = {
            amount: document.getElementById('cashAmountInput').value,
            note: document.getElementById('cashNoteInput').value.trim()
        };

        const endpoint = cashMode === 'add' ? 'api/cash/add.php' : 'api/cash/withdraw.php';

        const btn = document.getElementById('cashTransactionSaveBtn');
        btn.disabled = true;
        btn.innerHTML = '<i class="fa-solid fa-spinner fa-spin"></i> Saving...';

        try {
            const res = await fetch(endpoint, {
                method: 'POST', headers: { 'Content-Type': 'application/json' }, body: JSON.stringify(payload)
            });
            const result = await res.json();

            if (result.status === 'success') {
                ckToast('success', result.message);
                document.getElementById('currentCashDisplay').textContent = money(result.cash_balance);
                updateCashBalance(result.cash_balance);
                document.getElementById('cashTransactionForm').reset();
                loadCashHistory();
            } else {
                ckToast('error', result.message);
            }
        } catch (err) {
            ckToast('error', 'Failed to process cash transaction');
        } finally {
            btn.disabled = false;
            btn.innerHTML = '<i class="fa-solid fa-check"></i> <span id="cashTransactionBtnText">' + (cashMode === 'add' ? 'Add Cash' : 'Withdraw Cash') + '</span>';
        }
    });

    async function loadCashHistory() {
        const tbody = document.getElementById('cashHistoryTableBody');
        try {
            const res = await fetch('api/cash/history.php');
            const result = await res.json();

            if (result.status !== 'success' || result.data.length === 0) {
                tbody.innerHTML = `<tr><td colspan="5" class="text-center py-4 text-muted"><?php echo lang('no_data'); ?></td></tr>`;
                return;
            }

            tbody.innerHTML = result.data.map(t => `
                <tr>
                    <td data-label="Type"><span class="${t.type === 'add' ? 'cash-type-badge-add' : 'cash-type-badge-withdraw'}">${t.type === 'add' ? 'Added' : 'Withdrawn'}</span></td>
                    <td data-label="<?php echo lang('amount'); ?>" style="font-weight:600;color:${t.type === 'add' ? 'var(--success)' : 'var(--danger)'};">${t.type === 'add' ? '+' : '-'}${money(t.amount)}</td>
                    <td data-label="Note">${t.note ? t.note : '<span class="text-muted">—</span>'}</td>
                    <td data-label="By">${t.user_name ? t.user_name : '<span class="text-muted">—</span>'}</td>
                    <td data-label="<?php echo lang('date'); ?>">${formatDate(t.created_at)}</td>
                </tr>
            `).join('');
        } catch (err) {
            tbody.innerHTML = `<tr><td colspan="5" class="text-center py-4 text-danger">Error loading data</td></tr>`;
        }
    }

    /* ============ USERS ============ */
    async function loadUsers() {
        const tbody = document.getElementById('userTableBody');
        try {
            const res = await fetch('api/users/list.php');
            const result = await res.json();

            if (result.status !== 'success' || result.data.length === 0) {
                tbody.innerHTML = `<tr><td colspan="4" class="text-center py-4 text-muted"><?php echo lang('no_data'); ?></td></tr>`;
                return;
            }

            tbody.innerHTML = result.data.map(u => `
                <tr>
                    <td data-label="<?php echo lang('name'); ?>" style="font-weight:500;">${u.full_name}</td>
                    <td data-label="<?php echo lang('username'); ?>">${u.username}</td>
                    <td data-label="Joined">${formatDate(u.created_at)}</td>
                    <td data-label="<?php echo lang('action'); ?>"><button class="icon-btn ck-btn-danger-soft" onclick="deleteUser(${u.id})"><i class="fa-solid fa-trash"></i></button></td>
                </tr>
            `).join('');
        } catch (err) {
            tbody.innerHTML = `<tr><td colspan="4" class="text-center py-4 text-danger">Error loading data</td></tr>`;
        }
    }

    document.getElementById('btnAddUser').addEventListener('click', () => {
        document.getElementById('addUserForm').reset();
        document.getElementById('addUserOverlay').style.display = 'flex';
    });

    document.getElementById('addUserForm').addEventListener('submit', async function (e) {
        e.preventDefault();
        const payload = {
            full_name: document.getElementById('userFullName').value.trim(),
            username: document.getElementById('userUsername').value.trim(),
            password: document.getElementById('userPassword').value.trim()
        };

        try {
            const res = await fetch('api/users/add.php', {
                method: 'POST', headers: { 'Content-Type': 'application/json' }, body: JSON.stringify(payload)
            });
            const result = await res.json();

            if (result.status === 'success') {
                ckToast('success', result.message);
                document.getElementById('addUserOverlay').style.display = 'none';
                loadUsers();
            } else {
                ckToast('error', result.message);
            }
        } catch (err) {
            ckToast('error', 'Failed to add user');
        }
    });

    window.deleteUser = async function (id) {
        const confirmResult = await ckConfirm('This user will no longer be able to log in.');
        if (!confirmResult.isConfirmed) return;

        try {
            const res = await fetch('api/users/delete.php', {
                method: 'POST', headers: { 'Content-Type': 'application/json' }, body: JSON.stringify({ id })
            });
            const result = await res.json();

            if (result.status === 'success') {
                ckToast('success', result.message);
                loadUsers();
            } else {
                ckToast('error', result.message);
            }
        } catch (err) {
            ckToast('error', 'Failed to delete user');
        }
    };

    /* ============ LANGUAGE SWITCH ============ */
    document.querySelectorAll('.lang-option').forEach(opt => {
        opt.addEventListener('click', async function () {
            const lang = this.dataset.lang;
            if (this.classList.contains('active')) return;

            try {
                const res = await fetch('api/settings/language.php', {
                    method: 'POST', headers: { 'Content-Type': 'application/json' }, body: JSON.stringify({ language: lang })
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
                    <td data-label="<?php echo lang('name'); ?>" style="font-weight:500;">${c.name}</td>
                    <td data-label="<?php echo lang('mobile'); ?>">${c.mobile}</td>
                    <td data-label="<?php echo lang('customer_due'); ?>" style="font-weight:600;color:${c.due > 0 ? 'var(--danger)' : 'var(--success)'};">${money(c.due)}</td>
                    <td data-label="<?php echo lang('action'); ?>">
                        <div class="d-flex gap-2 justify-content-end">
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
                method: 'POST', headers: { 'Content-Type': 'application/json' }, body: JSON.stringify(payload)
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
                method: 'POST', headers: { 'Content-Type': 'application/json' }, body: JSON.stringify({ id })
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
                    <td data-label="<?php echo lang('name'); ?>" style="font-weight:500;">${s.name}</td>
                    <td data-label="<?php echo lang('mobile'); ?>">${s.mobile}</td>
                    <td data-label="<?php echo lang('supplier_due'); ?>" style="font-weight:600;color:${s.due > 0 ? 'var(--danger)' : 'var(--success)'};">${money(s.due)}</td>
                    <td data-label="<?php echo lang('action'); ?>">
                        <div class="d-flex gap-2 justify-content-end">
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
                method: 'POST', headers: { 'Content-Type': 'application/json' }, body: JSON.stringify(payload)
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
                method: 'POST', headers: { 'Content-Type': 'application/json' }, body: JSON.stringify({ id })
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