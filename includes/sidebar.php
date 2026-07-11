<?php $lang = loadLangFile(); ?>
<aside id="mainSidebar" class="bg-white border-end">
    <div class="p-3">
        <ul class="nav nav-pills flex-column gap-1" id="sidebarNav">
            <li class="nav-item">
                <a class="nav-link sidebar-link" href="#/dashboard" data-route="dashboard">
                    <i class="bi bi-speedometer2 me-2"></i><?= $lang['dashboard'] ?>
                </a>
            </li>
            <li class="nav-item">
                <a class="nav-link sidebar-link" href="#/customers" data-route="customers">
                    <i class="bi bi-people me-2"></i><?= $lang['customers'] ?>
                </a>
            </li>
            <li class="nav-item">
                <a class="nav-link sidebar-link" href="#/suppliers" data-route="suppliers">
                    <i class="bi bi-truck me-2"></i><?= $lang['suppliers'] ?>
                </a>
            </li>
            <li class="nav-item">
                <a class="nav-link sidebar-link" href="#/products" data-route="products">
                    <i class="bi bi-box-seam me-2"></i><?= $lang['products'] ?>
                </a>
            </li>
            <li class="nav-item">
                <a class="nav-link sidebar-link" href="#/purchase" data-route="purchase">
                    <i class="bi bi-cart-plus me-2"></i><?= $lang['purchase'] ?>
                </a>
            </li>
            <li class="nav-item">
                <a class="nav-link sidebar-link" href="#/sales" data-route="sales">
                    <i class="bi bi-cart-check me-2"></i><?= $lang['sales'] ?>
                </a>
            </li>
            <li class="nav-item">
                <a class="nav-link sidebar-link" href="#/expenses" data-route="expenses">
                    <i class="bi bi-wallet2 me-2"></i><?= $lang['expenses'] ?>
                </a>
            </li>
            <li class="nav-item">
                <a class="nav-link sidebar-link" href="#/reports" data-route="reports">
                    <i class="bi bi-bar-chart me-2"></i><?= $lang['reports'] ?>
                </a>
            </li>
            <li class="nav-item">
                <a class="nav-link sidebar-link" href="#/settings" data-route="settings">
                    <i class="bi bi-gear me-2"></i><?= $lang['settings'] ?>
                </a>
            </li>
        </ul>
    </div>
</aside>
<div id="sidebarOverlay" class="d-lg-none"></div>