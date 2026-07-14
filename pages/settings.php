<?php
require_once __DIR__ . '/../includes/auth_check.php';
require_once __DIR__ . '/../config/db.php';
$settings = $pdo->query("SELECT * FROM settings LIMIT 1")->fetch();
$currentLang = $_SESSION['language'] ?? 'en';
?>
<div id="settingsPage">

    <!-- ============ HEADER: Desktop বামে, Mobile ডানে ============ -->
    <div class="settings-header">
        <h4><?php echo lang('settings'); ?></h4>
        <p><?php echo lang('settings_subtitle'); ?></p>
    </div>

    <!-- ============ শুধু বাটন — কোনো Inline Content নেই, ক্লিক করলে Popup খুলবে ============ -->
    <div class="settings-tabs">
        <button class="settings-tab" data-modal="accountModal"><i class="fa-solid fa-user"></i> <?php echo lang('my_account'); ?></button>
        <button class="settings-tab" data-modal="businessModal"><i class="fa-solid fa-store"></i> <?php echo lang('business_info'); ?></button>
        <button class="settings-tab" data-modal="cashModal"><i class="fa-solid fa-sack-dollar"></i> <?php echo lang('cash_balance'); ?></button>
        <button class="settings-tab" data-modal="usersModal"><i class="fa-solid fa-user-group"></i> <?php echo lang('users'); ?></button>
        <button class="settings-tab" data-modal="languageModal"><i class="fa-solid fa-language"></i> <?php echo lang('language'); ?></button>
        <button class="settings-tab" data-modal="customersModal"><i class="fa-solid fa-users"></i> <?php echo lang('customer'); ?></button>
        <button class="settings-tab" data-modal="suppliersModal"><i class="fa-solid fa-truck"></i> <?php echo lang('supplier'); ?></button>
        <button class="settings-tab" data-modal="dataModal"><i class="fa-solid fa-database"></i> <?php echo lang('data_backup'); ?></button>
    </div>
</div>

<!-- ============ MODAL: MY ACCOUNT ============ -->
<div class="ck-modal-overlay" id="accountModal" style="display:none;">
    <div class="ck-modal-box" style="max-width:420px;">
        <div class="ck-modal-header">
            <h5><?php echo lang('my_account'); ?></h5>
            <i class="fa-solid fa-xmark ck-modal-close" data-close="accountModal"></i>
        </div>
        <div class="text-center mb-3">
            <div class="profile-avatar" style="width:56px;height:56px;font-size:22px;margin:0 auto 10px;">
                <?php echo strtoupper(substr($_SESSION['full_name'] ?? 'U', 0, 1)); ?>
            </div>
            <p class="text-muted mb-0" style="font-size:12px;"><?php echo lang('account_subtitle'); ?></p>
        </div>
        <form id="myAccountForm">
            <label class="ck-label"><?php echo lang('name'); ?></label>
            <input type="text" class="ck-input" id="myFullName" value="<?php echo htmlspecialchars($_SESSION['full_name'] ?? ''); ?>" required>

            <label class="ck-label mt-2"><?php echo lang('username'); ?></label>
            <input type="text" class="ck-input" id="myUsername" value="<?php echo htmlspecialchars($_SESSION['username'] ?? ''); ?>" required>

            <label class="ck-label mt-2"><?php echo lang('password'); ?> <span class="text-muted">(<?php echo lang('leave_blank_password'); ?>)</span></label>
            <input type="text" class="ck-input" id="myPassword" placeholder="<?php echo lang('leave_blank_password'); ?>">

            <button type="submit" class="ck-btn ck-btn-primary w-100 justify-content-center mt-3" id="myAccountSaveBtn">
                <i class="fa-solid fa-check"></i> <?php echo lang('save'); ?>
            </button>
        </form>
    </div>
</div>

<!-- ============ MODAL: BUSINESS INFO ============ -->
<div class="ck-modal-overlay" id="businessModal" style="display:none;">
    <div class="ck-modal-box" style="max-width:460px;">
        <div class="ck-modal-header">
            <h5><?php echo lang('business_info'); ?></h5>
            <i class="fa-solid fa-xmark ck-modal-close" data-close="businessModal"></i>
        </div>
        <p class="text-muted mb-3" style="font-size:12px;"><?php echo lang('business_subtitle'); ?></p>
        <form id="businessInfoForm">
            <label class="ck-label"><?php echo lang('business_name'); ?></label>
            <input type="text" class="ck-input" id="businessNameInput" value="<?php echo htmlspecialchars($settings['business_name']); ?>" required>

            <label class="ck-label mt-2"><?php echo lang('address'); ?></label>
            <input type="text" class="ck-input" id="businessAddressInput" value="<?php echo htmlspecialchars($settings['business_address']); ?>">

            <label class="ck-label mt-2">Phone</label>
            <input type="text" class="ck-input" id="businessPhoneInput" value="<?php echo htmlspecialchars($settings['business_phone']); ?>">

            <button type="submit" class="ck-btn ck-btn-primary w-100 justify-content-center mt-3" id="businessSaveBtn">
                <i class="fa-solid fa-check"></i> <?php echo lang('save'); ?>
            </button>
        </form>
    </div>
</div>

<!-- ============ MODAL: CASH BALANCE (বড় Modal, Table সহ) ============ -->
<div class="ck-modal-overlay" id="cashModal" style="display:none;">
    <div class="ck-modal-box" style="max-width:800px;">
        <div class="ck-modal-header">
            <h5><?php echo lang('cash_balance'); ?></h5>
            <i class="fa-solid fa-xmark ck-modal-close" data-close="cashModal"></i>
        </div>

        <div class="row g-3">
            <div class="col-lg-5">
                <div class="ck-card mb-3">
                    <div class="d-flex align-items-center gap-3">
                        <div style="width:44px;height:44px;background:#eff6ff;color:#2563eb;border-radius:10px;display:flex;align-items:center;justify-content:center;font-size:17px;flex-shrink:0;">
                            <i class="fa-solid fa-sack-dollar"></i>
                        </div>
                        <div>
                            <div class="text-muted" style="font-size:12px;"><?php echo lang('cash_balance'); ?></div>
                            <div id="currentCashDisplay" style="font-weight:700;font-size:19px;color:var(--primary-blue);"></div>
                        </div>
                    </div>
                </div>

                <div class="ck-card">
                    <p class="text-muted mb-3" style="font-size:12px;"><?php echo lang('cash_subtitle'); ?></p>
                    <div class="ck-toggle-tabs mb-3">
                        <button type="button" class="ck-toggle-btn active" data-cashmode="add"><i class="fa-solid fa-plus"></i> <?php echo lang('add_cash'); ?></button>
                        <button type="button" class="ck-toggle-btn" data-cashmode="withdraw"><i class="fa-solid fa-minus"></i> <?php echo lang('withdraw_cash'); ?></button>
                    </div>

                    <form id="cashTransactionForm">
                        <label class="ck-label"><?php echo lang('amount'); ?></label>
                        <input type="number" step="0.01" min="0.01" class="ck-input" id="cashAmountInput" placeholder="e.g. 5000" required>

                        <label class="ck-label mt-2"><?php echo lang('note'); ?> <span class="text-muted">(optional)</span></label>
                        <input type="text" class="ck-input" id="cashNoteInput">

                        <button type="submit" class="ck-btn ck-btn-primary w-100 justify-content-center mt-3" id="cashTransactionSaveBtn">
                            <i class="fa-solid fa-check"></i> <span id="cashTransactionBtnText"><?php echo lang('add_cash'); ?></span>
                        </button>
                    </form>
                </div>
            </div>

            <div class="col-lg-7">
                <div class="ck-card p-0">
                    <div class="p-3 pb-0">
                        <h6 style="font-weight:600;margin-bottom:0;"><?php echo lang('transaction_history'); ?></h6>
                    </div>
                    <div class="table-responsive" style="max-height:340px;overflow-y:auto;">
                        <table class="ck-table">
                            <thead>
                                <tr>
                                    <th><?php echo lang('type'); ?></th>
                                    <th><?php echo lang('amount'); ?></th>
                                    <th><?php echo lang('note'); ?></th>
                                    <th><?php echo lang('by'); ?></th>
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
</div>

<!-- ============ MODAL: USERS ============ -->
<div class="ck-modal-overlay" id="usersModal" style="display:none;">
    <div class="ck-modal-box" style="max-width:640px;">
        <div class="ck-modal-header">
            <h5><?php echo lang('users'); ?></h5>
            <i class="fa-solid fa-xmark ck-modal-close" data-close="usersModal"></i>
        </div>
        <div class="settings-inner-head">
            <p class="text-muted mb-0" style="font-size:13px;"><?php echo lang('users_subtitle'); ?></p>
            <button class="ck-btn ck-btn-primary" id="btnAddUser">
                <i class="fa-solid fa-user-plus"></i> <?php echo lang('add_customer'); ?>
            </button>
        </div>
        <div class="ck-card p-0">
            <div class="table-responsive" style="max-height:360px;overflow-y:auto;">
                <table class="ck-table">
                    <thead>
                        <tr>
                            <th><?php echo lang('name'); ?></th>
                            <th><?php echo lang('username'); ?></th>
                            <th><?php echo lang('joined'); ?></th>
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
</div>

<!-- ============ MODAL: LANGUAGE ============ -->
<div class="ck-modal-overlay" id="languageModal" style="display:none;">
    <div class="ck-modal-box" style="max-width:420px;">
        <div class="ck-modal-header">
            <h5><?php echo lang('language'); ?></h5>
            <i class="fa-solid fa-xmark ck-modal-close" data-close="languageModal"></i>
        </div>
        <p class="text-muted mb-3" style="font-size:13px;"><?php echo lang('language_subtitle'); ?></p>
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

<!-- ============ MODAL: CUSTOMERS ============ -->
<div class="ck-modal-overlay" id="customersModal" style="display:none;">
    <div class="ck-modal-box" style="max-width:640px;">
        <div class="ck-modal-header">
            <h5><?php echo lang('customer'); ?></h5>
            <i class="fa-solid fa-xmark ck-modal-close" data-close="customersModal"></i>
        </div>
        <div class="ck-card mb-3">
            <div class="input-group-search">
                <i class="fa-solid fa-magnifying-glass"></i>
                <input type="text" id="customerSearchInput" placeholder="<?php echo lang('search'); ?> customers...">
            </div>
        </div>
        <div class="ck-card p-0">
            <div class="table-responsive" style="max-height:360px;overflow-y:auto;">
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
</div>

<!-- ============ MODAL: SUPPLIERS ============ -->
<div class="ck-modal-overlay" id="suppliersModal" style="display:none;">
    <div class="ck-modal-box" style="max-width:640px;">
        <div class="ck-modal-header">
            <h5><?php echo lang('supplier'); ?></h5>
            <i class="fa-solid fa-xmark ck-modal-close" data-close="suppliersModal"></i>
        </div>
        <div class="ck-card mb-3">
            <div class="input-group-search">
                <i class="fa-solid fa-magnifying-glass"></i>
                <input type="text" id="supplierSearchInput" placeholder="<?php echo lang('search'); ?> suppliers...">
            </div>
        </div>
        <div class="ck-card p-0">
            <div class="table-responsive" style="max-height:360px;overflow-y:auto;">
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

<!-- ============ MODAL: DATA (BACKUP / IMPORT / DELETE) ============ -->
<div class="ck-modal-overlay" id="dataModal" style="display:none;">
    <div class="ck-modal-box" style="max-width:720px;">
        <div class="ck-modal-header">
            <h5><?php echo lang('data_backup'); ?></h5>
            <i class="fa-solid fa-xmark ck-modal-close" data-close="dataModal"></i>
        </div>
        <p class="text-muted mb-3 text-center" style="font-size:13px;"><?php echo lang('data_subtitle'); ?></p>

        <div class="row g-3">
            <div class="col-md-4">
                <div class="ck-card text-center h-100 data-action-card">
                    <div class="data-icon-box" style="background:#eff6ff;color:#2563eb;"><i class="fa-solid fa-cloud-arrow-down"></i></div>
                    <h6 style="font-weight:600;margin:12px 0 6px;"><?php echo lang('backup_data'); ?></h6>
                    <p class="text-muted mb-3" style="font-size:12px;">সমস্ত তথ্য একটি JSON ফাইল হিসেবে ডাউনলোড করুন।</p>
                    <button class="ck-btn ck-btn-primary w-100 justify-content-center" id="btnDownloadBackup">
                        <i class="fa-solid fa-download"></i> <?php echo lang('download_backup'); ?>
                    </button>
                </div>
            </div>

            <div class="col-md-4">
                <div class="ck-card text-center h-100 data-action-card">
                    <div class="data-icon-box" style="background:#f0fdf4;color:#16a34a;"><i class="fa-solid fa-cloud-arrow-up"></i></div>
                    <h6 style="font-weight:600;margin:12px 0 6px;"><?php echo lang('import_data'); ?></h6>
                    <p class="text-muted mb-3" style="font-size:12px;"><?php echo lang('select_backup_file'); ?></p>
                    <input type="file" accept=".json,application/json" id="importFileInput" style="display:none;">
                    <button class="ck-btn ck-btn-success-soft w-100 justify-content-center" id="btnImportData">
                        <i class="fa-solid fa-upload"></i> <?php echo lang('upload_backup'); ?>
                    </button>
                </div>
            </div>

            <div class="col-md-4">
                <div class="ck-card text-center h-100 data-action-card">
                    <div class="data-icon-box" style="background:#fef2f2;color:#dc2626;"><i class="fa-solid fa-trash-can"></i></div>
                    <h6 style="font-weight:600;margin:12px 0 6px;"><?php echo lang('delete_all_data'); ?></h6>
                    <p class="text-muted mb-3" style="font-size:12px;"><?php echo lang('delete_warning'); ?></p>
                    <button class="ck-btn ck-btn-danger-soft w-100 justify-content-center" id="btnOpenDeleteAll">
                        <i class="fa-solid fa-triangle-exclamation"></i> <?php echo lang('delete_all_data'); ?>
                    </button>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- ============ SUB-MODAL: ADD USER (Users Modal-এর উপরে খুলবে) ============ -->
<div class="ck-modal-overlay" id="addUserOverlay" style="display:none;z-index:2100;">
    <div class="ck-modal-box" style="max-width:400px;">
        <div class="ck-modal-header">
            <h5><?php echo lang('users'); ?></h5>
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

<!-- ============ SUB-MODAL: EDIT USER ============ -->
<div class="ck-modal-overlay" id="editUserOverlay" style="display:none;z-index:2100;">
    <div class="ck-modal-box" style="max-width:400px;">
        <div class="ck-modal-header">
            <h5><?php echo lang('edit'); ?></h5>
            <i class="fa-solid fa-xmark ck-modal-close" data-close="editUserOverlay"></i>
        </div>
        <form id="editUserForm">
            <input type="hidden" id="editUserId">

            <label class="ck-label"><?php echo lang('name'); ?></label>
            <input type="text" class="ck-input" id="editUserFullName" required>

            <label class="ck-label mt-2"><?php echo lang('username'); ?></label>
            <input type="text" class="ck-input" id="editUserUsername" required>

            <label class="ck-label mt-2"><?php echo lang('password'); ?> <span class="text-muted">(<?php echo lang('leave_blank_password'); ?>)</span></label>
            <input type="text" class="ck-input" id="editUserPassword">

            <div class="d-flex gap-2 mt-3">
                <button type="button" class="ck-btn ck-btn-outline flex-fill justify-content-center" data-close="editUserOverlay"><?php echo lang('cancel'); ?></button>
                <button type="submit" class="ck-btn ck-btn-primary flex-fill justify-content-center"><?php echo lang('save'); ?></button>
            </div>
        </form>
    </div>
</div>

<!-- ============ SUB-MODAL: RECEIVE PAYMENT (CUSTOMER) ============ -->
<div class="ck-modal-overlay" id="customerPaymentOverlay" style="display:none;z-index:2100;">
    <div class="ck-modal-box" style="max-width:400px;">
        <div class="ck-modal-header">
            <h5><?php echo lang('receive_payment'); ?></h5>
            <i class="fa-solid fa-xmark ck-modal-close" data-close="customerPaymentOverlay"></i>
        </div>
        <form id="customerPaymentForm">
            <input type="hidden" id="paymentCustomerId">
            <p style="font-size:13px;"><?php echo lang('customer'); ?>: <strong id="paymentCustomerName"></strong></p>
            <p class="text-muted" style="font-size:12px;"><?php echo lang('current_due'); ?>: <span id="paymentCustomerDue" style="font-weight:600;color:var(--danger);"></span></p>

            <label class="ck-label mt-2"><?php echo lang('amount'); ?></label>
            <input type="number" step="0.01" min="0.01" class="ck-input" id="customerPaymentAmount" required>

            <div class="d-flex gap-2 mt-3">
                <button type="button" class="ck-btn ck-btn-outline flex-fill justify-content-center" data-close="customerPaymentOverlay"><?php echo lang('cancel'); ?></button>
                <button type="submit" class="ck-btn ck-btn-primary flex-fill justify-content-center"><?php echo lang('save'); ?></button>
            </div>
        </form>
    </div>
</div>

<!-- ============ SUB-MODAL: MAKE PAYMENT (SUPPLIER) ============ -->
<div class="ck-modal-overlay" id="supplierPaymentOverlay" style="display:none;z-index:2100;">
    <div class="ck-modal-box" style="max-width:400px;">
        <div class="ck-modal-header">
            <h5><?php echo lang('make_payment'); ?></h5>
            <i class="fa-solid fa-xmark ck-modal-close" data-close="supplierPaymentOverlay"></i>
        </div>
        <form id="supplierPaymentForm">
            <input type="hidden" id="paymentSupplierId">
            <p style="font-size:13px;"><?php echo lang('supplier'); ?>: <strong id="paymentSupplierName"></strong></p>
            <p class="text-muted" style="font-size:12px;"><?php echo lang('current_due'); ?>: <span id="paymentSupplierDue" style="font-weight:600;color:var(--danger);"></span></p>

            <label class="ck-label mt-2"><?php echo lang('amount'); ?></label>
            <input type="number" step="0.01" min="0.01" class="ck-input" id="supplierPaymentAmount" required>

            <div class="d-flex gap-2 mt-3">
                <button type="button" class="ck-btn ck-btn-outline flex-fill justify-content-center" data-close="supplierPaymentOverlay"><?php echo lang('cancel'); ?></button>
                <button type="submit" class="ck-btn ck-btn-primary flex-fill justify-content-center"><?php echo lang('save'); ?></button>
            </div>
        </form>
    </div>
</div>

<!-- ============ SUB-MODAL: DELETE ALL DATA (Double Password) ============ -->
<div class="ck-modal-overlay" id="deleteAllOverlay" style="display:none;z-index:2100;">
    <div class="ck-modal-box" style="max-width:400px;">
        <div class="ck-modal-header">
            <h5 style="color:var(--danger);"><i class="fa-solid fa-triangle-exclamation"></i> <?php echo lang('delete_all_data'); ?></h5>
            <i class="fa-solid fa-xmark ck-modal-close" data-close="deleteAllOverlay"></i>
        </div>
        <p class="text-muted" style="font-size:12px;"><?php echo lang('delete_warning'); ?></p>
        <form id="deleteAllForm">
            <label class="ck-label mt-2"><?php echo lang('password'); ?></label>
            <input type="password" class="ck-input" id="deletePassword1" required>

            <label class="ck-label mt-2"><?php echo lang('confirm_password_again'); ?></label>
            <input type="password" class="ck-input" id="deletePassword2" required>

            <div class="d-flex gap-2 mt-3">
                <button type="button" class="ck-btn ck-btn-outline flex-fill justify-content-center" data-close="deleteAllOverlay"><?php echo lang('cancel'); ?></button>
                <button type="submit" class="ck-btn ck-btn-danger-soft flex-fill justify-content-center" id="deleteAllSaveBtn"><?php echo lang('delete_permanently'); ?></button>
            </div>
        </form>
    </div>
</div>

<!-- ============ PAGE-SPECIFIC STYLES ============ -->
<style>
    /* ============ HEADER: Desktop বামে, Mobile ডানে (নিশ্চিতভাবে) ============ */
    .settings-header {
        margin-bottom: 20px;
        width: 100%;
    }
    .settings-header h4 { font-weight: 600; margin: 0 0 2px; }
    .settings-header p { color: var(--text-muted); font-size: 13px; margin: 0; }

    .settings-header,
    .settings-header h4,
    .settings-header p {
        text-align: left;
    }

    @media (max-width: 767px) {
        .settings-header,
        .settings-header h4,
        .settings-header p {
            text-align: right;
        }
    }

    /* ============ Tabs: শুধু বাটন, কোনো Content নিচে দেখাবে না ============ */
    .settings-tabs {
        display: grid;
        grid-template-columns: repeat(auto-fill, minmax(180px, 1fr));
        gap: 12px;
    }
    .settings-tab {
        border: 1.5px solid var(--border-color); background: #fff; color: var(--text-dark);
        padding: 22px 16px; border-radius: 14px; font-size: 14px; font-weight: 600;
        cursor: pointer; display: flex; flex-direction: column; align-items: center; gap: 10px;
        transition: all 0.15s ease;
    }
    .settings-tab i { font-size: 22px; color: var(--primary-blue); }
    .settings-tab:hover { border-color: var(--primary-blue); box-shadow: 0 4px 14px rgba(37,99,235,0.1); transform: translateY(-2px); }

    /* Click করার সাথে সাথে Visual Press Effect */
    .settings-tab:active { transform: scale(0.94); }

    .settings-inner-head {
        display: flex; justify-content: space-between; align-items: center;
        flex-wrap: wrap; gap: 10px; margin-bottom: 16px;
    }

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

    .user-you-badge { background: var(--light-blue); color: var(--primary-blue); font-size: 9px; padding: 2px 7px; border-radius: 6px; margin-left: 6px; font-weight: 600; }

    .data-action-card { display: flex; flex-direction: column; align-items: center; justify-content: flex-start; padding: 24px 18px; }
    .data-icon-box {
        width: 52px; height: 52px; border-radius: 14px; display: flex; align-items: center;
        justify-content: center; font-size: 21px;
    }

    @media (max-width: 480px) {
        .settings-tabs { grid-template-columns: repeat(2, 1fr); }
        .settings-tab { padding: 16px 10px; font-size: 12px; }
        .settings-tab i { font-size: 18px; }
    }
</style>

<!-- ============ PAGE-SPECIFIC SCRIPT ============ -->
<script>
(function () {
    const currentSessionUserId = <?php echo (int) $_SESSION['user_id']; ?>;
    const bnDigitMap = { '0':'০','1':'১','2':'২','3':'৩','4':'৪','5':'৫','6':'৬','7':'৭','8':'৮','9':'৯' };

    function toBn(str) {
        return String(str).replace(/[0-9]/g, d => bnDigitMap[d]);
    }

    function money(v) {
        const formatted = '৳' + parseFloat(v || 0).toLocaleString('en-US', { minimumFractionDigits: 2, maximumFractionDigits: 2 });
        return CK.lang === 'bn' ? toBn(formatted) : formatted;
    }

    function formatDate(dateStr) {
        const d = new Date(dateStr.replace(' ', 'T'));
        const formatted = d.toLocaleDateString('en-GB', { day: '2-digit', month: 'short', year: 'numeric', hour: '2-digit', minute: '2-digit' });
        return CK.lang === 'bn' ? toBn(formatted) : formatted;
    }

    /* ============ TABS: ক্লিক করলে সংশ্লিষ্ট Modal Popup হবে ============ */
    document.querySelectorAll('.settings-tab').forEach(tab => {
        tab.addEventListener('click', function () {
            const modalId = this.dataset.modal;
            const modal = document.getElementById(modalId);
            if (!modal) return;

            modal.style.display = 'flex';
            gsap.fromTo(modal.querySelector('.ck-modal-box'),
                { opacity: 0, y: 16 },
                { opacity: 1, y: 0, duration: 0.25, ease: "power2.out", clearProps: "opacity,transform" }
            );

            if (modalId === 'cashModal') loadCashHistory();
            if (modalId === 'usersModal') loadUsers();
            if (modalId === 'customersModal') loadCustomers();
            if (modalId === 'suppliersModal') loadSuppliers();
        });
    });

    /* ============ MY ACCOUNT ============ */
    document.getElementById('myAccountForm').addEventListener('submit', async function (e) {
        e.preventDefault();
        const payload = {
            id: currentSessionUserId,
            full_name: document.getElementById('myFullName').value.trim(),
            username: document.getElementById('myUsername').value.trim(),
            password: document.getElementById('myPassword').value.trim()
        };

        const btn = document.getElementById('myAccountSaveBtn');
        btn.disabled = true;
        btn.innerHTML = '<i class="fa-solid fa-spinner fa-spin"></i>';

        try {
            const res = await fetch('api/users/update.php', {
                method: 'POST', headers: { 'Content-Type': 'application/json' }, body: JSON.stringify(payload)
            });
            const result = await res.json();

            if (result.status === 'success') {
                ckToast('success', result.message);
                document.getElementById('myPassword').value = '';
                const topAvatar = document.querySelector('.profile-avatar');
                const topName = document.querySelector('.profile-name');
                if (topAvatar) topAvatar.textContent = payload.full_name.charAt(0).toUpperCase();
                if (topName) topName.textContent = payload.full_name;
            } else {
                ckToast('error', result.message);
            }
        } catch (err) {
            ckToast('error', 'Failed to update account');
        } finally {
            btn.disabled = false;
            btn.innerHTML = '<i class="fa-solid fa-check"></i> <?php echo lang('save'); ?>';
        }
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
        btn.innerHTML = '<i class="fa-solid fa-spinner fa-spin"></i>';

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

    document.querySelectorAll('#cashModal .ck-toggle-btn').forEach(btn => {
        btn.addEventListener('click', function () {
            document.querySelectorAll('#cashModal .ck-toggle-btn').forEach(b => b.classList.remove('active'));
            this.classList.add('active');
            cashMode = this.dataset.cashmode;
            document.getElementById('cashTransactionBtnText').textContent = cashMode === 'add' ? '<?php echo lang('add_cash'); ?>' : '<?php echo lang('withdraw_cash'); ?>';
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
        btn.innerHTML = '<i class="fa-solid fa-spinner fa-spin"></i>';

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
            btn.innerHTML = '<i class="fa-solid fa-check"></i> <span id="cashTransactionBtnText">' + (cashMode === 'add' ? '<?php echo lang('add_cash'); ?>' : '<?php echo lang('withdraw_cash'); ?>') + '</span>';
        }
    });

    async function loadCashHistory() {
        const tbody = document.getElementById('cashHistoryTableBody');
        document.getElementById('currentCashDisplay').textContent = money(<?php echo (float) $settings['cash_balance']; ?>);
        try {
            const res = await fetch('api/cash/history.php');
            const result = await res.json();

            if (result.status !== 'success' || result.data.length === 0) {
                tbody.innerHTML = `<tr><td colspan="5" class="text-center py-4 text-muted"><?php echo lang('no_data'); ?></td></tr>`;
                return;
            }

            tbody.innerHTML = result.data.map(t => `
                <tr>
                    <td data-label="<?php echo lang('type'); ?>"><span class="${t.type === 'add' ? 'cash-type-badge-add' : 'cash-type-badge-withdraw'}">${t.type === 'add' ? '<?php echo lang('added'); ?>' : '<?php echo lang('withdrawn'); ?>'}</span></td>
                    <td data-label="<?php echo lang('amount'); ?>" style="font-weight:600;color:${t.type === 'add' ? 'var(--success)' : 'var(--danger)'};">${t.type === 'add' ? '+' : '-'}${money(t.amount)}</td>
                    <td data-label="<?php echo lang('note'); ?>">${t.note ? t.note : '<span class="text-muted">—</span>'}</td>
                    <td data-label="<?php echo lang('by'); ?>">${t.user_name ? t.user_name : '<span class="text-muted">—</span>'}</td>
                    <td data-label="<?php echo lang('date'); ?>">${formatDate(t.created_at)}</td>
                </tr>
            `).join('');
        } catch (err) {
            tbody.innerHTML = `<tr><td colspan="5" class="text-center py-4 text-danger">Error</td></tr>`;
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
                    <td data-label="<?php echo lang('name'); ?>" style="font-weight:500;">${u.full_name}${u.id === result.current_user_id ? '<span class="user-you-badge"><?php echo lang('you'); ?></span>' : ''}</td>
                    <td data-label="<?php echo lang('username'); ?>">${u.username}</td>
                    <td data-label="<?php echo lang('joined'); ?>">${formatDate(u.created_at)}</td>
                    <td data-label="<?php echo lang('action'); ?>">
                        <div class="d-flex gap-2 justify-content-end">
                            <button class="icon-btn ck-btn-outline" onclick='openEditUser(${JSON.stringify(u)})'><i class="fa-solid fa-pen"></i></button>
                            ${u.id !== result.current_user_id ? `<button class="icon-btn ck-btn-danger-soft" onclick="deleteUser(${u.id})"><i class="fa-solid fa-trash"></i></button>` : ''}
                        </div>
                    </td>
                </tr>
            `).join('');
        } catch (err) {
            tbody.innerHTML = `<tr><td colspan="4" class="text-center py-4 text-danger">Error</td></tr>`;
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

    window.openEditUser = function (u) {
        document.getElementById('editUserId').value = u.id;
        document.getElementById('editUserFullName').value = u.full_name;
        document.getElementById('editUserUsername').value = u.username;
        document.getElementById('editUserPassword').value = '';
        document.getElementById('editUserOverlay').style.display = 'flex';
    };

    document.getElementById('editUserForm').addEventListener('submit', async function (e) {
        e.preventDefault();
        const payload = {
            id: document.getElementById('editUserId').value,
            full_name: document.getElementById('editUserFullName').value.trim(),
            username: document.getElementById('editUserUsername').value.trim(),
            password: document.getElementById('editUserPassword').value.trim()
        };
        try {
            const res = await fetch('api/users/update.php', {
                method: 'POST', headers: { 'Content-Type': 'application/json' }, body: JSON.stringify(payload)
            });
            const result = await res.json();
            if (result.status === 'success') {
                ckToast('success', result.message);
                document.getElementById('editUserOverlay').style.display = 'none';
                loadUsers();
                if (parseInt(payload.id) === currentSessionUserId) {
                    const topAvatar = document.querySelector('.profile-avatar');
                    const topName = document.querySelector('.profile-name');
                    if (topAvatar) topAvatar.textContent = payload.full_name.charAt(0).toUpperCase();
                    if (topName) topName.textContent = payload.full_name;
                }
            } else {
                ckToast('error', result.message);
            }
        } catch (err) {
            ckToast('error', 'Failed to update user');
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
            if (result.status === 'success') { ckToast('success', result.message); loadUsers(); }
            else { ckToast('error', result.message); }
        } catch (err) { ckToast('error', 'Failed to delete user'); }
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
                    setTimeout(() => window.location.href = 'index.php#settings', 500);
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
            tbody.innerHTML = `<tr><td colspan="4" class="text-center py-4 text-danger">Error</td></tr>`;
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
        } catch (err) { ckToast('error', 'Failed to process payment'); }
    });

    window.deleteCustomer = async function (id) {
        const confirmResult = await ckConfirm('This customer will be permanently deleted.');
        if (!confirmResult.isConfirmed) return;
        try {
            const res = await fetch('api/customer/delete.php', {
                method: 'POST', headers: { 'Content-Type': 'application/json' }, body: JSON.stringify({ id })
            });
            const result = await res.json();
            if (result.status === 'success') { ckToast('success', result.message); loadCustomers(); }
            else { ckToast('error', result.message); }
        } catch (err) { ckToast('error', 'Failed to delete customer'); }
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
            tbody.innerHTML = `<tr><td colspan="4" class="text-center py-4 text-danger">Error</td></tr>`;
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
        } catch (err) { ckToast('error', 'Failed to process payment'); }
    });

    window.deleteSupplier = async function (id) {
        const confirmResult = await ckConfirm('This supplier will be permanently deleted.');
        if (!confirmResult.isConfirmed) return;
        try {
            const res = await fetch('api/supplier/delete.php', {
                method: 'POST', headers: { 'Content-Type': 'application/json' }, body: JSON.stringify({ id })
            });
            const result = await res.json();
            if (result.status === 'success') { ckToast('success', result.message); loadSuppliers(); }
            else { ckToast('error', result.message); }
        } catch (err) { ckToast('error', 'Failed to delete supplier'); }
    };

    /* ============ DATA: BACKUP ============ */
    document.getElementById('btnDownloadBackup').addEventListener('click', () => {
        window.location.href = 'api/settings/backup.php';
    });

    /* ============ DATA: IMPORT ============ */
    document.getElementById('btnImportData').addEventListener('click', () => {
        document.getElementById('importFileInput').click();
    });

    document.getElementById('importFileInput').addEventListener('change', async function () {
        const file = this.files[0];
        if (!file) return;

        const confirmResult = await ckConfirm('এই ফাইলের Data দিয়ে বর্তমান সমস্ত Data প্রতিস্থাপিত হবে।');
        if (!confirmResult.isConfirmed) { this.value = ''; return; }

        try {
            const text = await file.text();
            const jsonData = JSON.parse(text);

            const res = await fetch('api/settings/import.php', {
                method: 'POST', headers: { 'Content-Type': 'application/json' }, body: JSON.stringify(jsonData)
            });
            const result = await res.json();

            if (result.status === 'success') {
                ckToast('success', result.message);
                if (result.force_logout) {
                    setTimeout(() => window.location.href = 'login.php', 1500);
                } else {
                    setTimeout(() => window.location.href = 'index.php#settings', 1200);
                }
            } else {
                ckToast('error', result.message);
            }
        } catch (err) {
            ckToast('error', 'Invalid backup file');
        } finally {
            this.value = '';
        }
    });

    /* ============ DATA: DELETE ALL ============ */
    document.getElementById('btnOpenDeleteAll').addEventListener('click', () => {
        document.getElementById('deleteAllForm').reset();
        document.getElementById('deleteAllOverlay').style.display = 'flex';
    });

    document.getElementById('deleteAllForm').addEventListener('submit', async function (e) {
        e.preventDefault();

        const confirmResult = await ckConfirm('এই কাজটি আর ফেরত আনা যাবে না। আপনি কি সম্পূর্ণ নিশ্চিত?');
        if (!confirmResult.isConfirmed) return;

        const payload = {
            password1: document.getElementById('deletePassword1').value,
            password2: document.getElementById('deletePassword2').value
        };

        const btn = document.getElementById('deleteAllSaveBtn');
        btn.disabled = true;
        btn.innerHTML = '<i class="fa-solid fa-spinner fa-spin"></i>';

        try {
            const res = await fetch('api/settings/delete_all.php', {
                method: 'POST', headers: { 'Content-Type': 'application/json' }, body: JSON.stringify(payload)
            });
            const result = await res.json();

            if (result.status === 'success') {
                ckToast('success', result.message);
                document.getElementById('deleteAllOverlay').style.display = 'none';
                setTimeout(() => window.location.href = 'index.php#dashboard', 1000);
            } else {
                ckToast('error', result.message);
            }
        } catch (err) {
            ckToast('error', 'Failed to delete data');
        } finally {
            btn.disabled = false;
            btn.innerHTML = '<?php echo lang('delete_permanently'); ?>';
        }
    });

    /* ============ CLOSE MODALS ============ */
    document.querySelectorAll('[data-close], .ck-modal-close').forEach(el => {
        el.addEventListener('click', function () {
            const target = this.dataset.close;
            if (target) document.getElementById(target).style.display = 'none';
        });
    });

    /* Overlay-এর বাইরে ক্লিক করলে বন্ধ হবে */
    document.querySelectorAll('.ck-modal-overlay').forEach(overlay => {
        overlay.addEventListener('click', function (e) {
            if (e.target === overlay) overlay.style.display = 'none';
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