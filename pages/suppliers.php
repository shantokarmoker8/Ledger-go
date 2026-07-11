<?php
declare(strict_types=1);
require_once __DIR__ . '/../includes/functions.php';
requireAuth();
$lang = loadLangFile();
?>
<div id="suppliersPage">

    <div class="d-flex flex-wrap justify-content-between align-items-center mb-3 gap-2">
        <h5 class="fw-bold mb-0"><?= $lang['suppliers'] ?></h5>
        <button class="btn btn-primary btn-sm" data-bs-toggle="modal" data-bs-target="#supplierModal" id="addSupplierBtn">
            <i class="bi bi-plus-lg me-1"></i> Add Supplier
        </button>
    </div>

    <div class="card p-3">
        <div class="mb-3">
            <input type="text" class="form-control form-control-sm w-auto d-inline-block" id="supplierSearch" placeholder="Search by name or phone...">
        </div>

        <div class="table-responsive">
            <table class="table align-middle">
                <thead>
                    <tr>
                        <th>Name</th>
                        <th>Phone</th>
                        <th>Address</th>
                        <th class="text-end">Due</th>
                        <th>Status</th>
                        <th class="text-center">Actions</th>
                    </tr>
                </thead>
                <tbody id="suppliersTableBody">
                    <tr><td colspan="6" class="text-center text-muted py-4">Loading...</td></tr>
                </tbody>
            </table>
        </div>

        <nav>
            <ul class="pagination pagination-sm justify-content-center mb-0" id="suppliersPagination"></ul>
        </nav>
    </div>
</div>

<!-- Add/Edit Supplier Modal -->
<div class="modal fade" id="supplierModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content" style="border-radius:16px;">
            <div class="modal-header">
                <h6 class="modal-title fw-bold" id="supplierModalTitle">Add Supplier</h6>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <form id="supplierForm">
                    <input type="hidden" id="supplierId">
                    <div class="mb-3">
                        <label class="form-label">Name *</label>
                        <input type="text" class="form-control" id="supplierName" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Phone</label>
                        <input type="text" class="form-control" id="supplierPhone">
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Address</label>
                        <textarea class="form-control" id="supplierAddress" rows="2"></textarea>
                    </div>
                    <div class="mb-3" id="supplierOpeningDueWrapper">
                        <label class="form-label">Opening Due</label>
                        <input type="number" step="0.01" min="0" class="form-control" id="supplierOpeningDue" value="0">
                    </div>
                    <div class="mb-3 d-none" id="supplierStatusWrapper">
                        <label class="form-label">Status</label>
                        <select class="form-select" id="supplierStatus">
                            <option value="active">Active</option>
                            <option value="inactive">Inactive</option>
                        </select>
                    </div>
                    <button type="submit" class="btn btn-primary w-100">Save Supplier</button>
                </form>
            </div>
        </div>
    </div>
</div>

<!-- Ledger Modal -->
<div class="modal fade" id="supplierLedgerModal" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content" style="border-radius:16px;">
            <div class="modal-header">
                <h6 class="modal-title fw-bold">Supplier Ledger — <span id="supplierLedgerName"></span></h6>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <div class="d-flex justify-content-between mb-3">
                    <span>Total Due: <strong class="text-danger" id="supplierLedgerTotalDue">৳0</strong></span>
                    <button class="btn btn-success btn-sm" id="openSupplierPaymentBtn">
                        <i class="bi bi-cash-coin me-1"></i> Make Payment
                    </button>
                </div>
                <div class="table-responsive" style="max-height:400px; overflow-y:auto;">
                    <table class="table table-sm align-middle">
                        <thead>
                            <tr><th>Date</th><th>Type</th><th>Reference</th><th class="text-end">Amount</th></tr>
                        </thead>
                        <tbody id="supplierLedgerTableBody"></tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Make Payment Modal -->
<div class="modal fade" id="supplierPaymentModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content" style="border-radius:16px;">
            <div class="modal-header">
                <h6 class="modal-title fw-bold">Make Payment</h6>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <form id="supplierPaymentForm">
                    <input type="hidden" id="supplierPaymentSupplierId">
                    <div class="mb-3">
                        <label class="form-label">Current Due</label>
                        <input type="text" class="form-control" id="supplierPaymentCurrentDue" disabled>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Amount *</label>
                        <input type="number" step="0.01" min="0.01" class="form-control" id="supplierPaymentAmount" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Payment Date</label>
                        <input type="date" class="form-control" id="supplierPaymentDate" value="<?= date('Y-m-d') ?>">
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Note</label>
                        <input type="text" class="form-control" id="supplierPaymentNote">
                    </div>
                    <button type="submit" class="btn btn-success w-100">Confirm Payment</button>
                </form>
            </div>
        </div>
    </div>
</div>

<script>
(function initSuppliersPage() {
    let currentPage = 1;
    let searchTimeout = null;
    let selectedSupplierForPayment = null;

    const supplierModal = new bootstrap.Modal(document.getElementById('supplierModal'));
    const ledgerModal    = new bootstrap.Modal(document.getElementById('supplierLedgerModal'));
    const paymentModal   = new bootstrap.Modal(document.getElementById('supplierPaymentModal'));

    async function loadSuppliers(page = 1, search = '') {
        currentPage = page;
        const data = await apiRequest(`api/suppliers/list.php?page=${page}&search=${encodeURIComponent(search)}`);
        if (!data || !data.success) return;
        renderTable(data.suppliers);
        renderPagination(data.total_pages, page);
    }

    function renderTable(suppliers) {
        const tbody = document.getElementById('suppliersTableBody');
        if (!suppliers.length) {
            tbody.innerHTML = `<tr><td colspan="6" class="text-center text-muted py-4">No suppliers found</td></tr>`;
            return;
        }
        tbody.innerHTML = suppliers.map(s => `
            <tr>
                <td class="fw-medium">${escapeHtml(s.name)}</td>
                <td>${escapeHtml(s.phone || '-')}</td>
                <td class="small text-muted">${escapeHtml(s.address || '-')}</td>
                <td class="text-end ${s.total_due > 0 ? 'text-danger fw-bold' : ''}">৳${parseFloat(s.total_due).toFixed(2)}</td>
                <td><span class="badge ${s.status === 'active' ? 'bg-success' : 'bg-secondary'}">${s.status}</span></td>
                <td class="text-center">
                    <button class="btn btn-sm btn-light" onclick="viewSupplierLedger(${s.id}, '${escapeHtml(s.name)}')" title="Ledger">
                        <i class="bi bi-journal-text"></i>
                    </button>
                    <button class="btn btn-sm btn-light" onclick="editSupplier(${s.id}, '${escapeHtml(s.name)}', '${escapeHtml(s.phone || '')}', '${escapeHtml(s.address || '')}', '${s.status}')" title="Edit">
                        <i class="bi bi-pencil"></i>
                    </button>
                    <button class="btn btn-sm btn-light text-danger" onclick="deleteSupplier(${s.id})" title="Delete">
                        <i class="bi bi-trash"></i>
                    </button>
                </td>
            </tr>
        `).join('');
    }

    function renderPagination(totalPages, page) {
        const el = document.getElementById('suppliersPagination');
        if (totalPages <= 1) { el.innerHTML = ''; return; }
        let html = '';
        for (let i = 1; i <= totalPages; i++) {
            html += `<li class="page-item ${i === page ? 'active' : ''}">
                        <a class="page-link" href="#" onclick="event.preventDefault(); loadSuppliersPage(${i})">${i}</a>
                      </li>`;
        }
        el.innerHTML = html;
    }

    window.loadSuppliersPage = (page) => loadSuppliers(page, document.getElementById('supplierSearch').value);

    document.getElementById('supplierSearch').addEventListener('input', (e) => {
        clearTimeout(searchTimeout);
        searchTimeout = setTimeout(() => loadSuppliers(1, e.target.value), 400);
    });

    document.getElementById('addSupplierBtn').addEventListener('click', () => {
        document.getElementById('supplierForm').reset();
        document.getElementById('supplierId').value = '';
        document.getElementById('supplierModalTitle').textContent = 'Add Supplier';
        document.getElementById('supplierOpeningDueWrapper').classList.remove('d-none');
        document.getElementById('supplierStatusWrapper').classList.add('d-none');
    });

    window.editSupplier = (id, name, phone, address, status) => {
        document.getElementById('supplierId').value = id;
        document.getElementById('supplierName').value = name;
        document.getElementById('supplierPhone').value = phone;
        document.getElementById('supplierAddress').value = address;
        document.getElementById('supplierStatus').value = status;
        document.getElementById('supplierModalTitle').textContent = 'Edit Supplier';
        document.getElementById('supplierOpeningDueWrapper').classList.add('d-none');
        document.getElementById('supplierStatusWrapper').classList.remove('d-none');
        supplierModal.show();
    };

    document.getElementById('supplierForm').addEventListener('submit', async (e) => {
        e.preventDefault();
        const id = document.getElementById('supplierId').value;
        const payload = {
            name: document.getElementById('supplierName').value.trim(),
            phone: document.getElementById('supplierPhone').value.trim(),
            address: document.getElementById('supplierAddress').value.trim(),
        };

        let result;
        if (id) {
            payload.id = parseInt(id);
            payload.status = document.getElementById('supplierStatus').value;
            result = await apiRequest('api/suppliers/update.php', 'POST', payload);
        } else {
            payload.opening_due = parseFloat(document.getElementById('supplierOpeningDue').value || 0);
            result = await apiRequest('api/suppliers/add.php', 'POST', payload);
        }

        if (result?.success) {
            showToast(result.message);
            supplierModal.hide();
            loadSuppliers(currentPage, document.getElementById('supplierSearch').value);
        } else {
            showToast(result?.message || 'Something went wrong', 'error');
        }
    });

    window.deleteSupplier = async (id) => {
        const confirm = await Swal.fire({
            title: 'Delete Supplier?',
            text: 'This action cannot be undone.',
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#dc3545',
        });
        if (!confirm.isConfirmed) return;

        const result = await apiRequest('api/suppliers/delete.php', 'POST', { id });
        if (result?.success) {
            showToast(result.message);
            loadSuppliers(currentPage);
        } else {
            showToast(result?.message || 'Failed to delete', 'error');
        }
    };

    window.viewSupplierLedger = async (id, name) => {
        document.getElementById('supplierLedgerName').textContent = name;
        const data = await apiRequest(`api/suppliers/ledger.php?id=${id}`);
        if (!data?.success) return;

        document.getElementById('supplierLedgerTotalDue').textContent = '৳' + parseFloat(data.supplier.total_due).toFixed(2);
        selectedSupplierForPayment = data.supplier;

        const tbody = document.getElementById('supplierLedgerTableBody');
        if (!data.ledger.length) {
            tbody.innerHTML = `<tr><td colspan="4" class="text-center text-muted py-3">No transactions found</td></tr>`;
        } else {
            tbody.innerHTML = data.ledger.map(entry => {
                if (entry.entry_type === 'purchase') {
                    return `<tr>
                        <td class="small">${entry.date}</td>
                        <td><span class="badge bg-primary">Purchase</span></td>
                        <td class="small">${escapeHtml(entry.invoice_no)}</td>
                        <td class="text-end small text-danger">৳${parseFloat(entry.due_amount).toFixed(2)} due</td>
                    </tr>`;
                }
                return `<tr>
                    <td class="small">${entry.date}</td>
                    <td><span class="badge bg-success">Payment</span></td>
                    <td class="small">${escapeHtml(entry.note || '-')}</td>
                    <td class="text-end small text-success">-৳${parseFloat(entry.amount).toFixed(2)}</td>
                </tr>`;
            }).join('');
        }
        ledgerModal.show();
    };

    document.getElementById('openSupplierPaymentBtn').addEventListener('click', () => {
        if (!selectedSupplierForPayment) return;
        document.getElementById('supplierPaymentSupplierId').value = selectedSupplierForPayment.id;
        document.getElementById('supplierPaymentCurrentDue').value = '৳' + parseFloat(selectedSupplierForPayment.total_due).toFixed(2);
        document.getElementById('supplierPaymentAmount').max = selectedSupplierForPayment.total_due;
        ledgerModal.hide();
        paymentModal.show();
    });

    document.getElementById('supplierPaymentForm').addEventListener('submit', async (e) => {
        e.preventDefault();
        const payload = {
            supplier_id: parseInt(document.getElementById('supplierPaymentSupplierId').value),
            amount: parseFloat(document.getElementById('supplierPaymentAmount').value),
            payment_date: document.getElementById('supplierPaymentDate').value,
            note: document.getElementById('supplierPaymentNote').value.trim(),
        };
        const result = await apiRequest('api/payments/supplier-payment.php', 'POST', payload);
        if (result?.success) {
            showToast(result.message);
            paymentModal.hide();
            document.getElementById('supplierPaymentForm').reset();
            loadSuppliers(currentPage);
        } else {
            showToast(result?.message || 'Payment failed', 'error');
        }
    });

    function escapeHtml(str) {
        const div = document.createElement('div');
        div.textContent = str ?? '';
        return div.innerHTML;
    }

    loadSuppliers();
})();
</script>