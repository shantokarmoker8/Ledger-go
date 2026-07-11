<?php
declare(strict_types=1);
require_once __DIR__ . '/../includes/functions.php';
requireAuth();
$lang = loadLangFile();
?>
<div id="customersPage">

    <div class="d-flex flex-wrap justify-content-between align-items-center mb-3 gap-2">
        <h5 class="fw-bold mb-0"><?= $lang['customers'] ?></h5>
        <button class="btn btn-primary btn-sm" data-bs-toggle="modal" data-bs-target="#customerModal" id="addCustomerBtn">
            <i class="bi bi-plus-lg me-1"></i> Add Customer
        </button>
    </div>

    <div class="card p-3">
        <div class="mb-3">
            <input type="text" class="form-control form-control-sm w-auto d-inline-block" id="customerSearch" placeholder="Search by name or phone...">
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
                <tbody id="customersTableBody">
                    <tr><td colspan="6" class="text-center text-muted py-4">Loading...</td></tr>
                </tbody>
            </table>
        </div>

        <nav>
            <ul class="pagination pagination-sm justify-content-center mb-0" id="customersPagination"></ul>
        </nav>
    </div>
</div>

<!-- Add/Edit Customer Modal -->
<div class="modal fade" id="customerModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content" style="border-radius:16px;">
            <div class="modal-header">
                <h6 class="modal-title fw-bold" id="customerModalTitle">Add Customer</h6>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <form id="customerForm">
                    <input type="hidden" id="customerId">
                    <div class="mb-3">
                        <label class="form-label">Name *</label>
                        <input type="text" class="form-control" id="customerName" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Phone</label>
                        <input type="text" class="form-control" id="customerPhone">
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Address</label>
                        <textarea class="form-control" id="customerAddress" rows="2"></textarea>
                    </div>
                    <div class="mb-3" id="openingDueWrapper">
                        <label class="form-label">Opening Due</label>
                        <input type="number" step="0.01" min="0" class="form-control" id="customerOpeningDue" value="0">
                    </div>
                    <div class="mb-3 d-none" id="statusWrapper">
                        <label class="form-label">Status</label>
                        <select class="form-select" id="customerStatus">
                            <option value="active">Active</option>
                            <option value="inactive">Inactive</option>
                        </select>
                    </div>
                    <button type="submit" class="btn btn-primary w-100">Save Customer</button>
                </form>
            </div>
        </div>
    </div>
</div>

<!-- Ledger Modal -->
<div class="modal fade" id="ledgerModal" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content" style="border-radius:16px;">
            <div class="modal-header">
                <h6 class="modal-title fw-bold">Customer Ledger — <span id="ledgerCustomerName"></span></h6>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <div class="d-flex justify-content-between mb-3">
                    <span>Total Due: <strong class="text-danger" id="ledgerTotalDue">৳0</strong></span>
                    <button class="btn btn-success btn-sm" id="openPaymentFromLedgerBtn">
                        <i class="bi bi-cash-coin me-1"></i> Receive Payment
                    </button>
                </div>
                <div class="table-responsive" style="max-height:400px; overflow-y:auto;">
                    <table class="table table-sm align-middle">
                        <thead>
                            <tr><th>Date</th><th>Type</th><th>Reference</th><th class="text-end">Amount</th></tr>
                        </thead>
                        <tbody id="ledgerTableBody"></tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Receive Payment Modal -->
<div class="modal fade" id="paymentModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content" style="border-radius:16px;">
            <div class="modal-header">
                <h6 class="modal-title fw-bold">Receive Payment</h6>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <form id="paymentForm">
                    <input type="hidden" id="paymentCustomerId">
                    <div class="mb-3">
                        <label class="form-label">Current Due</label>
                        <input type="text" class="form-control" id="paymentCurrentDue" disabled>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Amount *</label>
                        <input type="number" step="0.01" min="0.01" class="form-control" id="paymentAmount" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Payment Date</label>
                        <input type="date" class="form-control" id="paymentDate" value="<?= date('Y-m-d') ?>">
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Note</label>
                        <input type="text" class="form-control" id="paymentNote">
                    </div>
                    <button type="submit" class="btn btn-success w-100">Confirm Payment</button>
                </form>
            </div>
        </div>
    </div>
</div>

<script>
(function initCustomersPage() {
    let currentPage = 1;
    let searchTimeout = null;
    let selectedCustomerForPayment = null;

    const customerModal = new bootstrap.Modal(document.getElementById('customerModal'));
    const ledgerModal   = new bootstrap.Modal(document.getElementById('ledgerModal'));
    const paymentModal  = new bootstrap.Modal(document.getElementById('paymentModal'));

    async function loadCustomers(page = 1, search = '') {
        currentPage = page;
        const data = await apiRequest(`api/customers/list.php?page=${page}&search=${encodeURIComponent(search)}`);
        if (!data || !data.success) return;
        renderTable(data.customers);
        renderPagination(data.total_pages, page);
    }

    function renderTable(customers) {
        const tbody = document.getElementById('customersTableBody');
        if (!customers.length) {
            tbody.innerHTML = `<tr><td colspan="6" class="text-center text-muted py-4">No customers found</td></tr>`;
            return;
        }
        tbody.innerHTML = customers.map(c => `
            <tr>
                <td class="fw-medium">${escapeHtml(c.name)}</td>
                <td>${escapeHtml(c.phone || '-')}</td>
                <td class="small text-muted">${escapeHtml(c.address || '-')}</td>
                <td class="text-end ${c.total_due > 0 ? 'text-danger fw-bold' : ''}">৳${parseFloat(c.total_due).toFixed(2)}</td>
                <td><span class="badge ${c.status === 'active' ? 'bg-success' : 'bg-secondary'}">${c.status}</span></td>
                <td class="text-center">
                    <button class="btn btn-sm btn-light" onclick="viewLedger(${c.id}, '${escapeHtml(c.name)}')" title="Ledger">
                        <i class="bi bi-journal-text"></i>
                    </button>
                    <button class="btn btn-sm btn-light" onclick="editCustomer(${c.id}, '${escapeHtml(c.name)}', '${escapeHtml(c.phone || '')}', '${escapeHtml(c.address || '')}', '${c.status}')" title="Edit">
                        <i class="bi bi-pencil"></i>
                    </button>
                    <button class="btn btn-sm btn-light text-danger" onclick="deleteCustomer(${c.id})" title="Delete">
                        <i class="bi bi-trash"></i>
                    </button>
                </td>
            </tr>
        `).join('');
    }

    function renderPagination(totalPages, page) {
        const el = document.getElementById('customersPagination');
        if (totalPages <= 1) { el.innerHTML = ''; return; }
        let html = '';
        for (let i = 1; i <= totalPages; i++) {
            html += `<li class="page-item ${i === page ? 'active' : ''}">
                        <a class="page-link" href="#" onclick="event.preventDefault(); loadCustomersPage(${i})">${i}</a>
                      </li>`;
        }
        el.innerHTML = html;
    }

    window.loadCustomersPage = (page) => loadCustomers(page, document.getElementById('customerSearch').value);

    document.getElementById('customerSearch').addEventListener('input', (e) => {
        clearTimeout(searchTimeout);
        searchTimeout = setTimeout(() => loadCustomers(1, e.target.value), 400);
    });

    document.getElementById('addCustomerBtn').addEventListener('click', () => {
        document.getElementById('customerForm').reset();
        document.getElementById('customerId').value = '';
        document.getElementById('customerModalTitle').textContent = 'Add Customer';
        document.getElementById('openingDueWrapper').classList.remove('d-none');
        document.getElementById('statusWrapper').classList.add('d-none');
    });

    window.editCustomer = (id, name, phone, address, status) => {
        document.getElementById('customerId').value = id;
        document.getElementById('customerName').value = name;
        document.getElementById('customerPhone').value = phone;
        document.getElementById('customerAddress').value = address;
        document.getElementById('customerStatus').value = status;
        document.getElementById('customerModalTitle').textContent = 'Edit Customer';
        document.getElementById('openingDueWrapper').classList.add('d-none');
        document.getElementById('statusWrapper').classList.remove('d-none');
        customerModal.show();
    };

    document.getElementById('customerForm').addEventListener('submit', async (e) => {
        e.preventDefault();
        const id = document.getElementById('customerId').value;
        const payload = {
            name: document.getElementById('customerName').value.trim(),
            phone: document.getElementById('customerPhone').value.trim(),
            address: document.getElementById('customerAddress').value.trim(),
        };

        let result;
        if (id) {
            payload.id = parseInt(id);
            payload.status = document.getElementById('customerStatus').value;
            result = await apiRequest('api/customers/update.php', 'POST', payload);
        } else {
            payload.opening_due = parseFloat(document.getElementById('customerOpeningDue').value || 0);
            result = await apiRequest('api/customers/add.php', 'POST', payload);
        }

        if (result?.success) {
            showToast(result.message);
            customerModal.hide();
            loadCustomers(currentPage, document.getElementById('customerSearch').value);
        } else {
            showToast(result?.message || 'Something went wrong', 'error');
        }
    });

    window.deleteCustomer = async (id) => {
        const confirm = await Swal.fire({
            title: 'Delete Customer?',
            text: 'This action cannot be undone.',
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#dc3545',
        });
        if (!confirm.isConfirmed) return;

        const result = await apiRequest('api/customers/delete.php', 'POST', { id });
        if (result?.success) {
            showToast(result.message);
            loadCustomers(currentPage);
        } else {
            showToast(result?.message || 'Failed to delete', 'error');
        }
    };

    window.viewLedger = async (id, name) => {
        document.getElementById('ledgerCustomerName').textContent = name;
        const data = await apiRequest(`api/customers/ledger.php?id=${id}`);
        if (!data?.success) return;

        document.getElementById('ledgerTotalDue').textContent = '৳' + parseFloat(data.customer.total_due).toFixed(2);
        selectedCustomerForPayment = data.customer;

        const tbody = document.getElementById('ledgerTableBody');
        if (!data.ledger.length) {
            tbody.innerHTML = `<tr><td colspan="4" class="text-center text-muted py-3">No transactions found</td></tr>`;
        } else {
            tbody.innerHTML = data.ledger.map(entry => {
                if (entry.entry_type === 'sale') {
                    return `<tr>
                        <td class="small">${entry.date}</td>
                        <td><span class="badge bg-primary">Sale</span></td>
                        <td class="small">${escapeHtml(entry.invoice_no)}</td>
                        <td class="text-end small text-danger">৳${parseFloat(entry.due_amount).toFixed(2)} due</td>
                    </tr>`;
                }
                return `<tr>
                    <td class="small">${entry.date}</td>
                    <td><span class="badge bg-success">Payment</span></td>
                    <td class="small">${escapeHtml(entry.note || '-')}</td>
                    <td class="text-end small text-success">+৳${parseFloat(entry.amount).toFixed(2)}</td>
                </tr>`;
            }).join('');
        }
        ledgerModal.show();
    };

    document.getElementById('openPaymentFromLedgerBtn').addEventListener('click', () => {
        if (!selectedCustomerForPayment) return;
        document.getElementById('paymentCustomerId').value = selectedCustomerForPayment.id;
        document.getElementById('paymentCurrentDue').value = '৳' + parseFloat(selectedCustomerForPayment.total_due).toFixed(2);
        document.getElementById('paymentAmount').max = selectedCustomerForPayment.total_due;
        ledgerModal.hide();
        paymentModal.show();
    });

    document.getElementById('paymentForm').addEventListener('submit', async (e) => {
        e.preventDefault();
        const payload = {
            customer_id: parseInt(document.getElementById('paymentCustomerId').value),
            amount: parseFloat(document.getElementById('paymentAmount').value),
            payment_date: document.getElementById('paymentDate').value,
            note: document.getElementById('paymentNote').value.trim(),
        };
        const result = await apiRequest('api/payments/customer-payment.php', 'POST', payload);
        if (result?.success) {
            showToast(result.message);
            paymentModal.hide();
            document.getElementById('paymentForm').reset();
            loadCustomers(currentPage);
        } else {
            showToast(result?.message || 'Payment failed', 'error');
        }
    });

    function escapeHtml(str) {
        const div = document.createElement('div');
        div.textContent = str ?? '';
        return div.innerHTML;
    }

    loadCustomers();
})();
</script>