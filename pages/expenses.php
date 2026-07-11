<?php
require_once __DIR__ . '/../includes/auth_check.php';
?>
<!-- ============ EXPENSES PAGE ============ -->
<div id="expensesPage">

    <div class="d-flex justify-content-between align-items-center mb-3 flex-wrap gap-2">
        <div>
            <h4 class="mb-0" style="font-weight:600;"><?php echo lang('expenses'); ?></h4>
            <p class="text-muted mb-0" style="font-size:13px;">Track your daily business expenses</p>
        </div>
        <button class="ck-btn ck-btn-primary" id="btnNewExpense">
            <i class="fa-solid fa-plus"></i> <?php echo lang('expenses'); ?>
        </button>
    </div>

    <!-- Total Expenses Summary -->
    <div class="ck-card mb-3" style="display:flex;align-items:center;gap:16px;">
        <div style="width:46px;height:46px;background:#ecfeff;color:#0891b2;border-radius:12px;display:flex;align-items:center;justify-content:center;font-size:18px;">
            <i class="fa-solid fa-receipt"></i>
        </div>
        <div>
            <div class="text-muted" style="font-size:12px;"><?php echo lang('total_expenses'); ?></div>
            <div id="expenseTotalDisplay" style="font-size:20px;font-weight:700;">৳0.00</div>
        </div>
    </div>

    <!-- Search -->
    <div class="ck-card mb-3">
        <div class="input-group-search">
            <i class="fa-solid fa-magnifying-glass"></i>
            <input type="text" id="expenseSearch" placeholder="<?php echo lang('search'); ?> expenses...">
        </div>
    </div>

    <!-- Expenses Table -->
    <div class="ck-card p-0">
        <div class="table-responsive">
            <table class="ck-table">
                <thead>
                    <tr>
                        <th><?php echo lang('name'); ?></th>
                        <th><?php echo lang('amount'); ?></th>
                        <th><?php echo lang('date'); ?></th>
                        <th><?php echo lang('action'); ?></th>
                    </tr>
                </thead>
                <tbody id="expenseTableBody">
                    <tr><td colspan="4" class="text-center py-4 text-muted">Loading...</td></tr>
                </tbody>
            </table>
        </div>
    </div>
</div>

<!-- ============ MODAL: NEW EXPENSE ============ -->
<div class="ck-modal-overlay" id="newExpenseOverlay" style="display:none;">
    <div class="ck-modal-box">
        <div class="ck-modal-header">
            <h5><?php echo lang('expenses'); ?></h5>
            <i class="fa-solid fa-xmark ck-modal-close" data-close="newExpenseOverlay"></i>
        </div>

        <form id="newExpenseForm">
            <label class="ck-label"><?php echo lang('name'); ?></label>
            <input type="text" class="ck-input" id="expenseNameInput" placeholder="e.g. Shop Rent, Electricity Bill" required>

            <label class="ck-label mt-2"><?php echo lang('amount'); ?></label>
            <input type="number" step="0.01" min="0.01" class="ck-input" id="expenseAmountInput" required>

            <div class="d-flex gap-2 mt-3">
                <button type="button" class="ck-btn ck-btn-outline flex-fill justify-content-center" data-close="newExpenseOverlay"><?php echo lang('cancel'); ?></button>
                <button type="submit" class="ck-btn ck-btn-primary flex-fill justify-content-center" id="expenseSaveBtn"><?php echo lang('save'); ?></button>
            </div>
        </form>
    </div>
</div>

<!-- ============ PAGE-SPECIFIC SCRIPT ============ -->
<script>
(function () {
    function money(v) {
        return '৳' + parseFloat(v || 0).toLocaleString('en-US', { minimumFractionDigits: 2, maximumFractionDigits: 2 });
    }

    function formatDate(dateStr) {
        const d = new Date(dateStr.replace(' ', 'T'));
        return d.toLocaleDateString('en-GB', { day: '2-digit', month: 'short', year: 'numeric' });
    }

    async function loadExpenses(search = '') {
        const tbody = document.getElementById('expenseTableBody');
        try {
            const res = await fetch('api/expense/list.php?search=' + encodeURIComponent(search));
            const result = await res.json();

            if (result.status !== 'success') {
                tbody.innerHTML = `<tr><td colspan="4" class="text-center py-4 text-danger">Failed to load</td></tr>`;
                return;
            }

            document.getElementById('expenseTotalDisplay').textContent = money(result.total);

            if (result.data.length === 0) {
                tbody.innerHTML = `<tr><td colspan="4" class="text-center py-4 text-muted"><?php echo lang('no_data'); ?></td></tr>`;
                return;
            }

            tbody.innerHTML = result.data.map(e => `
                <tr>
                    <td style="font-weight:500;">${e.name}</td>
                    <td style="font-weight:600;color:var(--danger);">${money(e.amount)}</td>
                    <td>${formatDate(e.created_at)}</td>
                    <td><button class="icon-btn ck-btn-danger-soft" onclick="deleteExpense(${e.id})"><i class="fa-solid fa-trash"></i></button></td>
                </tr>
            `).join('');
        } catch (err) {
            tbody.innerHTML = `<tr><td colspan="4" class="text-center py-4 text-danger">Error loading data</td></tr>`;
        }
    }

    window.deleteExpense = async function (id) {
        const confirm = await ckConfirm('This will refund the amount back to your Cash Balance.');
        if (!confirm.isConfirmed) return;

        try {
            const res = await fetch('api/expense/delete.php', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify({ id })
            });
            const result = await res.json();

            if (result.status === 'success') {
                ckToast('success', result.message);
                updateCashBalance(result.cash_balance);
                loadExpenses(document.getElementById('expenseSearch').value);
            } else {
                ckToast('error', result.message);
            }
        } catch (err) {
            ckToast('error', 'Failed to delete expense');
        }
    };

    document.getElementById('btnNewExpense').addEventListener('click', () => {
        document.getElementById('newExpenseForm').reset();
        document.getElementById('newExpenseOverlay').style.display = 'flex';
    });

    document.querySelectorAll('#newExpenseOverlay [data-close], #newExpenseOverlay .ck-modal-close').forEach(el => {
        el.addEventListener('click', () => document.getElementById('newExpenseOverlay').style.display = 'none');
    });

    document.getElementById('newExpenseForm').addEventListener('submit', async function (e) {
        e.preventDefault();

        const payload = {
            name: document.getElementById('expenseNameInput').value.trim(),
            amount: document.getElementById('expenseAmountInput').value
        };

        const saveBtn = document.getElementById('expenseSaveBtn');
        saveBtn.disabled = true;
        saveBtn.innerHTML = '<i class="fa-solid fa-spinner fa-spin"></i> Saving...';

        try {
            const res = await fetch('api/expense/add.php', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify(payload)
            });
            const result = await res.json();

            if (result.status === 'success') {
                ckToast('success', result.message);
                document.getElementById('newExpenseOverlay').style.display = 'none';
                updateCashBalance(result.cash_balance);
                loadExpenses();
            } else {
                ckToast('error', result.message);
            }
        } catch (err) {
            ckToast('error', 'Failed to add expense');
        } finally {
            saveBtn.disabled = false;
            saveBtn.innerHTML = '<?php echo lang('save'); ?>';
        }
    });

    let searchTimer;
    document.getElementById('expenseSearch').addEventListener('input', function () {
        clearTimeout(searchTimer);
        const val = this.value;
        searchTimer = setTimeout(() => loadExpenses(val), 350);
    });

    loadExpenses();
})();
</script>