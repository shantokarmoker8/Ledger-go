<?php
$lang = loadLangFile();
$settingsStmt = getDB()->query('SELECT shop_name, logo FROM settings ORDER BY id LIMIT 1');
$shopSettings = $settingsStmt->fetch() ?: ['shop_name' => 'LedgerGo', 'logo' => 'assets/logo.png'];
?>
<nav class="navbar navbar-expand bg-white border-bottom sticky-top px-3 py-2" id="mainHeader">
    <button class="btn btn-light border-0 me-2 d-lg-none" id="sidebarToggleBtn">
        <i class="bi bi-list fs-4"></i>
    </button>

    <span class="navbar-brand fw-bold text-primary d-flex align-items-center gap-2 mb-0">
        <img src="<?= htmlspecialchars($shopSettings['logo']) ?>" alt="Logo" style="width:32px;height:32px;object-fit:contain;">
        <span class="d-none d-sm-inline"><?= htmlspecialchars($shopSettings['shop_name']) ?></span>
    </span>

    <div class="ms-auto d-flex align-items-center gap-2">
        <!-- Language Switch -->
        <div class="dropdown">
            <button class="btn btn-light btn-sm dropdown-toggle" data-bs-toggle="dropdown">
                <i class="bi bi-translate"></i>
                <span class="d-none d-md-inline"><?= $lang['current'] ?? ($_SESSION['lang'] === 'bn' ? 'বাংলা' : 'English') ?></span>
            </button>
            <ul class="dropdown-menu dropdown-menu-end">
                <li><a class="dropdown-item lang-switch" href="#" data-lang="en">English</a></li>
                <li><a class="dropdown-item lang-switch" href="#" data-lang="bn">বাংলা</a></li>
            </ul>
        </div>

        <!-- User Menu -->
        <div class="dropdown">
            <button class="btn btn-light btn-sm dropdown-toggle d-flex align-items-center gap-2" data-bs-toggle="dropdown">
                <i class="bi bi-person-circle fs-5"></i>
                <span class="d-none d-md-inline"><?= htmlspecialchars($_SESSION['user_name'] ?? '') ?></span>
            </button>
            <ul class="dropdown-menu dropdown-menu-end">
                <li><a class="dropdown-item" href="#/settings"><i class="bi bi-gear me-2"></i><?= $lang['settings'] ?></a></li>
                <li><hr class="dropdown-divider"></li>
                <li><a class="dropdown-item text-danger" href="#" id="logoutBtn"><i class="bi bi-box-arrow-right me-2"></i><?= $lang['logout'] ?></a></li>
            </ul>
        </div>
    </div>
</nav>