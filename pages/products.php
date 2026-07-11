<?php
declare(strict_types=1);
require_once __DIR__ . '/../includes/functions.php';
requireAuth();
$lang = loadLangFile();
?>
<div id="productsPage">

    <div class="d-flex flex-wrap justify-content-between align-items-center mb-3 gap-2">
        <h5 class="fw-bold mb-0"><?= $lang['products'] ?></h5>
        <div class="d-flex gap-2">
            <button class="btn btn-outline-secondary btn-sm" data-bs-toggle="modal" data-bs-target="#categoryModal">
                <i class="bi bi-tags me-1"></i> Categories
            </button>
            <button class="btn btn-primary btn-sm" id="addProductBtn">
                <i class="bi bi-plus-lg me-1"></i> Add Product
            </button>
        </div>
    </div>

    <div class="card p-3">
        <div class="d-flex flex-wrap gap-2 mb-3">
            <input type="text" class="form-control form-control-sm w-auto" id="productSearch" placeholder="Search product...">
            <select class="form-select form-select-sm w-auto" id="categoryFilter">
                <option value="">All Categories</option>
            </select>
            <div class="form-check d-flex align-items-center ms-2">
                <input class="form-check-input me-2" type="checkbox" id="lowStockFilter">
                <label class="form-check-label small" for="lowStockFilter">Low Stock Only</label>
            </div>
        </div>

        <div class="table-responsive">
            <table class="table align-middle">
                <thead>
                    <tr>
                        <th>Name</th>
                        <th>Category</th>
                        <th class="text-end">Buy Price</th>
                        <th class="text-end">Sell Price</th>
                        <th class="text-end">Stock</th>
                        <th>Status</th>
                        <th class="text-center">Actions</th>
                    </tr>
                </thead>
                <tbody id="productsTableBody">
                    <tr><td colspan="7" class="text-center text-muted py-4">Loading...</td></tr>
                </tbody>
            </table>
        </div>

        <nav>
            <ul class="pagination pagination-sm justify-content-center mb-0" id="productsPagination"></ul>
        </nav>
    </div>
</div>

<!-- Add/Edit Product Modal -->
<div class="modal fade" id="productModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content" style="border-radius:16px;">
            <div class="modal-header">
                <h6 class="modal-title fw-bold" id="productModalTitle">Add Product</h6>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <form id="productForm">
                    <input type="hidden" id="productId">
                    <div class="mb-3">
                        <label class="form-label">Product Name *</label>
                        <input type="text" class="form-control" id="productName" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Category</label>
                        <select class="form-select" id="productCategory">
                            <option value="">-- None --</option>
                        </select>
                    </div>
                    <div class="row">
                        <div class="col-6 mb-3">
                            <label class="form-label">Buy Price *</label>
                            <input type="number" step="0.01" min="0" class="form-control" id="productBuyPrice" required>
                        </div>
                        <div class="col-6 mb-3">
                            <label class="form-label">Sell Price *</label>
                            <input type="number" step="0.01" min="0" class="form-control" id="productSellPrice" required>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-6 mb-3" id="stockQtyWrapper">
                            <label class="form-label">Opening Stock</label>
                            <input type="number" step="0.01" min="0" class="form-control" id="productStockQty" value="0">
                        </div>
                        <div class="col-6 mb-3">
                            <label class="form-label">Alert Quantity</label>
                            <input type="number" step="0.01" min="0" class="form-control" id="productAlertQty" value="5">
                        </div>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Unit</label>
                        <select class="form-select" id="productUnit">
                            <option value="pcs">Pcs</option>
                            <option value="kg">Kg</option>
                            <option value="litre">Litre</option>
                            <option value="box">Box</option>
                            <option value="dozen">Dozen</option>
                        </select>
                    </div>
                    <div class="mb-3 d-none" id="productStatusWrapper">
                        <label class="form-label">Status</label>
                        <select class="form-select" id="productStatus">
                            <option value="active">Active</option>
                            <option value="inactive">Inactive</option>
                        </select>
                    </div>
                    <button type="submit" class="btn btn-primary w-100">Save Product</button>
                </form>
            </div>
        </div>
    </div>
</div>

<!-- Category Modal -->
<div class="modal fade" id="categoryModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content" style="border-radius:16px;">
            <div class="modal-header">
                <h6 class="modal-title fw-bold">Manage Categories</h6>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <form id="categoryForm" class="d-flex gap-2 mb-3">
                    <input type="text" class="form-control" id="newCategoryName" placeholder="New category name" required>
                    <button type="submit" class="btn btn-primary">Add</button>
                </form>
                <ul class="list-group" id="categoryList"></ul>
            </div>
        </div>
    </div>
</div>

<script>
(function initProductsPage() {
    let currentPage = 1;
    let searchTimeout = null;
    let allCategories = [];

    const productModal  = new bootstrap.Modal(document.getElementById('productModal'));
    const categoryModal = new bootstrap.Modal(document.getElementById('categoryModal'));

    async function loadCategories() {
        const data = await apiRequest('api/products/categories.php');
        if (!data?.success) return;
        allCategories = data.categories;

        const filterSelect = document.getElementById('categoryFilter');
        const formSelect = document.getElementById('productCategory');
        const listEl = document.getElementById('categoryList');

        filterSelect.innerHTML = '<option value="">All Categories</option>' +
            allCategories.map(c => `<option value="${c.id}">${escapeHtml(c.name)}</option>`).join('');

        formSelect.innerHTML = '<option value="">-- None --</option>' +
            allCategories.map(c => `<option value="${c.id}">${escapeHtml(c.name)}</option>`).join('');

        listEl.innerHTML = allCategories.map(c =>
            `<li class="list-group-item">${escapeHtml(c.name)}</li>`
        ).join('') || '<li class="list-group-item text-muted">No categories yet</li>';
    }

    document.getElementById('categoryForm').addEventListener('submit', async (e) => {
        e.preventDefault();
        const name = document.getElementById('newCategoryName').value.trim();
        const result = await apiRequest('api/products/categories.php', 'POST', { name });
        if (result?.success) {
            document.getElementById('newCategoryName').value = '';
            loadCategories();
            showToast('Category added');
        } else {
            showToast(result?.message || 'Failed', 'error');
        }
    });

    async function loadProducts(page = 1) {
        currentPage = page;
        const search = document.getElementById('productSearch').value;
        const categoryId = document.getElementById('categoryFilter').value;
        const lowStock = document.getElementById('lowStockFilter').checked;

        const params = new URLSearchParams({ page, search });
        if (categoryId) params.append('category_id', categoryId);
        if (lowStock) params.append('low_stock', '1');

        const data = await apiRequest(`api/products/list.php?${params.toString()}`);
        if (!data?.success) return;
        renderTable(data.products);
        renderPagination(data.total_pages, page);
    }

    function renderTable(products) {
        const tbody = document.getElementById('productsTableBody');
        if (!products.length) {
            tbody.innerHTML = `<tr><td colspan="7" class="text-center text-muted py-4">No products found</td></tr>`;
            return;
        }
        tbody.innerHTML = products.map(p => {
            const lowStock = parseFloat(p.stock_qty) <= parseFloat(p.alert_qty);
            return `
            <tr>
                <td class="fw-medium">${escapeHtml(p.name)}</td>
                <td class="small text-muted">${escapeHtml(p.category_name || '-')}</td>
                <td class="text-end">৳${parseFloat(p.buy_price).toFixed(2)}</td>
                <td class="text-end">৳${parseFloat(p.sell_price).toFixed(2)}</td>
                <td class="text-end ${lowStock ? 'text-danger fw-bold' : ''}">
                    ${parseFloat(p.stock_qty)} ${escapeHtml(p.unit)}
                    ${lowStock ? '<i class="bi bi-exclamation-triangle-fill ms-1" title="Low Stock"></i>' : ''}
                </td>
                <td><span class="badge ${p.status === 'active' ? 'bg-success' : 'bg-secondary'}">${p.status}</span></td>
                <td class="text-center">
                    <button class="btn btn-sm btn-light" onclick='editProduct(${JSON.stringify(p)})' title="Edit">
                        <i class="bi bi-pencil"></i>
                    </button>
                    <button class="btn btn-sm btn-light text-danger" onclick="deleteProduct(${p.id})" title="Delete">
                        <i class="bi bi-trash"></i>
                    </button>
                </td>
            </tr>`;
        }).join('');
    }

    function renderPagination(totalPages, page) {
        const el = document.getElementById('productsPagination');
        if (totalPages <= 1) { el.innerHTML = ''; return; }
        let html = '';
        for (let i = 1; i <= totalPages; i++) {
            html += `<li class="page-item ${i === page ? 'active' : ''}">
                        <a class="page-link" href="#" onclick="event.preventDefault(); loadProductsPage(${i})">${i}</a>
                      </li>`;
        }
        el.innerHTML = html;
    }

    window.loadProductsPage = (page) => loadProducts(page);

    document.getElementById('productSearch').addEventListener('input', () => {
        clearTimeout(searchTimeout);
        searchTimeout = setTimeout(() => loadProducts(1), 400);
    });
    document.getElementById('categoryFilter').addEventListener('change', () => loadProducts(1));
    document.getElementById('lowStockFilter').addEventListener('change', () => loadProducts(1));

    document.getElementById('addProductBtn').addEventListener('click', () => {
        document.getElementById('productForm').reset();
        document.getElementById('productId').value = '';
        document.getElementById('productModalTitle').textContent = 'Add Product';
        document.getElementById('stockQtyWrapper').classList.remove('d-none');
        document.getElementById('productStatusWrapper').classList.add('d-none');
        productModal.show();
    });

    window.editProduct = (p) => {
        document.getElementById('productId').value = p.id;
        document.getElementById('productName').value = p.name;
        document.getElementById('productCategory').value = p.category_id || '';
        document.getElementById('productBuyPrice').value = p.buy_price;
        document.getElementById('productSellPrice').value = p.sell_price;
        document.getElementById('productAlertQty').value = p.alert_qty;
        document.getElementById('productUnit').value = p.unit;
        document.getElementById('productStatus').value = p.status;
        document.getElementById('productModalTitle').textContent = 'Edit Product';
        document.getElementById('stockQtyWrapper').classList.add('d-none');
        document.getElementById('productStatusWrapper').classList.remove('d-none');
        productModal.show();
    };

    document.getElementById('productForm').addEventListener('submit', async (e) => {
        e.preventDefault();
        const id = document.getElementById('productId').value;
        const payload = {
            name: document.getElementById('productName').value.trim(),
            category_id: document.getElementById('productCategory').value || null,
            buy_price: parseFloat(document.getElementById('productBuyPrice').value),
            sell_price: parseFloat(document.getElementById('productSellPrice').value),
            alert_qty: parseFloat(document.getElementById('productAlertQty').value || 5),
            unit: document.getElementById('productUnit').value,
        };

        let result;
        if (id) {
            payload.id = parseInt(id);
            payload.status = document.getElementById('productStatus').value;
            result = await apiRequest('api/products/update.php', 'POST', payload);
        } else {
            payload.stock_qty = parseFloat(document.getElementById('productStockQty').value || 0);
            result = await apiRequest('api/products/add.php', 'POST', payload);
        }

        if (result?.success) {
            showToast(result.message);
            productModal.hide();
            loadProducts(currentPage);
        } else {
            showToast(result?.message || 'Something went wrong', 'error');
        }
    });

    window.deleteProduct = async (id) => {
        const confirm = await Swal.fire({
            title: 'Delete Product?',
            text: 'This action cannot be undone.',
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#dc3545',
        });
        if (!confirm.isConfirmed) return;

        const result = await apiRequest('api/products/delete.php', 'POST', { id });
        if (result?.success) {
            showToast(result.message);
            loadProducts(currentPage);
        } else {
            showToast(result?.message || 'Failed to delete', 'error');
        }
    };

    function escapeHtml(str) {
        const div = document.createElement('div');
        div.textContent = str ?? '';
        return div.innerHTML;
    }

    loadCategories();
    loadProducts();
})();
</script>