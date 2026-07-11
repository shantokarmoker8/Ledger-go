<?php
require_once __DIR__ . '/includes/auth_check.php';
require_once __DIR__ . '/config/db.php';

// Get current cash balance and business name for initial render
$settings = $pdo->query("SELECT * FROM settings LIMIT 1")->fetch();
$cashBalance = $settings['cash_balance'] ?? 0;
$businessName = $settings['business_name'] ?? 'Cash Khata';
$currentLang = $_SESSION['language'] ?? 'en';
?>
<!DOCTYPE html>
<html lang="<?php echo $currentLang; ?>">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Cash Khata - <?php echo lang('dashboard'); ?></title>

<!-- Bootstrap 5 -->
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
<!-- Font Awesome -->
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
<!-- Google Font -->
<link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;500;600;700&display=swap" rel="stylesheet">

<style>
    :root {
        --primary-blue: #2563eb;
        --dark-blue: #1e40af;
        --light-blue: #eff6ff;
        --sidebar-bg: #ffffff;
        --body-bg: #f4f7fe;
        --text-dark: #1e293b;
        --text-muted: #64748b;
        --border-color: #e6ebf3;
        --success: #16a34a;
        --danger: #dc2626;
        --warning: #d97706;
        --sidebar-width: 250px;
        --topbar-height: 68px;
        --bottomnav-height: 64px;
    }

    * {
        margin: 0;
        padding: 0;
        box-sizing: border-box;
        font-family: 'Poppins', sans-serif;
    }

    body {
        background: var(--body-bg);
        color: var(--text-dark);
        overflow-x: hidden;
    }

    a { text-decoration: none; }

    /* ============ SCROLLBAR ============ */
    ::-webkit-scrollbar { width: 6px; height: 6px; }
    ::-webkit-scrollbar-thumb { background: #cbd5e1; border-radius: 10px; }

    /* ============ TOP BAR ============ */
    .topbar {
        position: fixed;
        top: 0;
        left: var(--sidebar-width);
        right: 0;
        height: var(--topbar-height);
        background: #ffffff;
        border-bottom: 1px solid var(--border-color);
        display: flex;
        align-items: center;
        justify-content: space-between;
        padding: 0 26px;
        z-index: 1000;
        transition: left 0.2s ease;
    }

    .topbar-left {
        display: flex;
        align-items: center;
        gap: 12px;
    }

    .topbar-left .brand-logo {
        width: 38px;
        height: 38px;
        background: var(--primary-blue);
        border-radius: 10px;
        display: flex;
        align-items: center;
        justify-content: center;
        color: #fff;
        font-size: 16px;
    }

    .topbar-left .brand-text {
        font-weight: 600;
        font-size: 16px;
        color: var(--text-dark);
        display: none;
    }

    .topbar-right {
        display: flex;
        align-items: center;
        gap: 14px;
    }

    .cash-balance-box {
        display: flex;
        align-items: center;
        gap: 10px;
        background: var(--light-blue);
        border: 1px solid #dbeafe;
        padding: 8px 16px;
        border-radius: 10px;
    }

    .cash-balance-box i {
        color: var(--primary-blue);
        font-size: 14px;
    }

    .cash-balance-box .cb-label {
        font-size: 11px;
        color: var(--text-muted);
        display: block;
        line-height: 1;
        margin-bottom: 3px;
    }

    .cash-balance-box .cb-value {
        font-size: 14px;
        font-weight: 600;
        color: var(--primary-blue);
        line-height: 1;
    }

    .profile-menu {
        position: relative;
    }

    .profile-btn {
        display: flex;
        align-items: center;
        gap: 10px;
        background: transparent;
        border: none;
        cursor: pointer;
        padding: 6px 8px;
        border-radius: 10px;
        transition: background 0.2s ease;
    }

    .profile-btn:hover { background: #f1f5f9; }

    .profile-avatar {
        width: 36px;
        height: 36px;
        border-radius: 50%;
        background: var(--primary-blue);
        color: #fff;
        display: flex;
        align-items: center;
        justify-content: center;
        font-weight: 600;
        font-size: 14px;
    }

    .profile-name {
        font-size: 13px;
        font-weight: 500;
        color: var(--text-dark);
        display: none;
    }

    .profile-dropdown {
        position: absolute;
        top: 52px;
        right: 0;
        background: #fff;
        border: 1px solid var(--border-color);
        border-radius: 12px;
        box-shadow: 0 8px 24px rgba(0,0,0,0.08);
        min-width: 180px;
        padding: 8px;
        display: none;
        z-index: 1200;
    }

    .profile-dropdown.show { display: block; }

    .profile-dropdown a {
        display: flex;
        align-items: center;
        gap: 10px;
        padding: 10px 12px;
        border-radius: 8px;
        color: var(--text-dark);
        font-size: 13px;
        cursor: pointer;
        transition: background 0.15s ease;
    }

    .profile-dropdown a:hover { background: var(--light-blue); color: var(--primary-blue); }
    .profile-dropdown a.logout-link:hover { background: #fef2f2; color: var(--danger); }

    /* ============ SIDEBAR (DESKTOP) ============ */
    .sidebar {
        position: fixed;
        top: 0;
        left: 0;
        width: var(--sidebar-width);
        height: 100vh;
        background: var(--sidebar-bg);
        border-right: 1px solid var(--border-color);
        z-index: 1100;
        display: flex;
        flex-direction: column;
    }

    .sidebar-brand {
        height: var(--topbar-height);
        display: flex;
        align-items: center;
        gap: 12px;
        padding: 0 22px;
        border-bottom: 1px solid var(--border-color);
    }

    .sidebar-brand .brand-logo {
        width: 38px;
        height: 38px;
        background: var(--primary-blue);
        border-radius: 10px;
        display: flex;
        align-items: center;
        justify-content: center;
        color: #fff;
        font-size: 16px;
    }

    .sidebar-brand .brand-text {
        font-weight: 700;
        font-size: 17px;
        color: var(--text-dark);
    }

    .sidebar-nav {
        padding: 20px 14px;
        display: flex;
        flex-direction: column;
        gap: 4px;
    }

    .sidebar-nav .nav-item {
        display: flex;
        align-items: center;
        gap: 12px;
        padding: 12px 14px;
        border-radius: 10px;
        color: var(--text-muted);
        font-size: 14px;
        font-weight: 500;
        cursor: pointer;
        transition: all 0.2s ease;
        position: relative;
    }

    .sidebar-nav .nav-item i {
        width: 20px;
        text-align: center;
        font-size: 15px;
    }

    .sidebar-nav .nav-item:hover {
        background: var(--light-blue);
        color: var(--primary-blue);
    }

    .sidebar-nav .nav-item.active {
        background: var(--primary-blue);
        color: #fff;
    }

    /* ============ MAIN CONTENT ============ */
    .main-content {
        margin-left: var(--sidebar-width);
        margin-top: var(--topbar-height);
        padding: 26px;
        min-height: calc(100vh - var(--topbar-height));
        transition: margin-left 0.2s ease;
    }

    #pageContent { position: relative; }

    /* ============ BOTTOM NAV (TABLET / MOBILE) ============ */
    .bottom-nav {
        display: none;
        position: fixed;
        bottom: 0;
        left: 0;
        right: 0;
        height: var(--bottomnav-height);
        background: #ffffff;
        border-top: 1px solid var(--border-color);
        z-index: 1100;
        align-items: center;
        justify-content: space-around;
        box-shadow: 0 -4px 16px rgba(0,0,0,0.04);
    }

    .bottom-nav .bn-item {
        display: flex;
        flex-direction: column;
        align-items: center;
        gap: 4px;
        color: var(--text-muted);
        font-size: 10px;
        cursor: pointer;
        flex: 1;
        padding: 6px 0;
        transition: color 0.2s ease;
    }

    .bottom-nav .bn-item i { font-size: 17px; }

    .bottom-nav .bn-item.active { color: var(--primary-blue); }

    /* ============ SKELETON LOADING ============ */
    .skeleton {
        background: linear-gradient(90deg, #eef2f8 25%, #f7f9fc 50%, #eef2f8 75%);
        background-size: 200% 100%;
        animation: skeleton-loading 1.4s ease infinite;
        border-radius: 8px;
    }

    @keyframes skeleton-loading {
        0% { background-position: 200% 0; }
        100% { background-position: -200% 0; }
    }

    .skeleton-card { height: 100px; margin-bottom: 16px; }
    .skeleton-row { height: 46px; margin-bottom: 10px; }

    /* ============ GLOBAL CARD STYLE (used by pages) ============ */
    .ck-card {
        background: #fff;
        border: 1px solid var(--border-color);
        border-radius: 14px;
        padding: 20px;
    }

    .ck-btn {
        border: none;
        border-radius: 10px;
        padding: 10px 18px;
        font-size: 13px;
        font-weight: 600;
        cursor: pointer;
        display: inline-flex;
        align-items: center;
        gap: 8px;
        transition: all 0.2s ease;
    }

    .ck-btn-primary { background: var(--primary-blue); color: #fff; }
    .ck-btn-primary:hover { background: var(--dark-blue); }
    .ck-btn-outline { background: #fff; border: 1.5px solid var(--border-color); color: var(--text-dark); }
    .ck-btn-outline:hover { border-color: var(--primary-blue); color: var(--primary-blue); }
    .ck-btn-danger-soft { background: #fef2f2; color: var(--danger); }
    .ck-btn-danger-soft:hover { background: #fee2e2; }

    /* ============ RESPONSIVE BREAKPOINTS ============ */
    @media (min-width: 992px) {
        .topbar-left .brand-text,
        .profile-name { display: block; }
    }

    @media (max-width: 991px) {
        :root { --sidebar-width: 0px; }
        .sidebar { display: none; }
        .topbar { left: 0; }
        .main-content { margin-left: 0; padding: 18px 16px 90px 16px; }
        .bottom-nav { display: flex; }
    }

    @media (max-width: 480px) {
        .cash-balance-box .cb-label { display: none; }
        .cash-balance-box { padding: 8px 12px; }
    }
</style>
</head>
<body>

<!-- ============ SIDEBAR ============ -->
<aside class="sidebar" id="sidebar">
    <div class="sidebar-brand">
        <div class="brand-logo"><i class="fa-solid fa-wallet"></i></div>
        <div class="brand-text"><?php echo htmlspecialchars($businessName); ?></div>
    </div>
    <nav class="sidebar-nav" id="sidebarNav">
        <div class="nav-item" data-page="dashboard"><i class="fa-solid fa-gauge-high"></i><span><?php echo lang('dashboard'); ?></span></div>
        <div class="nav-item" data-page="purchase"><i class="fa-solid fa-cart-shopping"></i><span><?php echo lang('purchase'); ?></span></div>
        <div class="nav-item" data-page="sales"><i class="fa-solid fa-tags"></i><span><?php echo lang('sales'); ?></span></div>
        <div class="nav-item" data-page="expenses"><i class="fa-solid fa-receipt"></i><span><?php echo lang('expenses'); ?></span></div>
        <div class="nav-item" data-page="settings"><i class="fa-solid fa-gear"></i><span><?php echo lang('settings'); ?></span></div>
    </nav>
</aside>

<!-- ============ TOP BAR ============ -->
<header class="topbar" id="topbar">
    <div class="topbar-left">
        <div class="brand-logo"><i class="fa-solid fa-wallet"></i></div>
        <div class="brand-text"><?php echo htmlspecialchars($businessName); ?></div>
    </div>

    <div class="topbar-right">
        <div class="cash-balance-box">
            <i class="fa-solid fa-sack-dollar"></i>
            <div>
                <span class="cb-label"><?php echo lang('cash_balance'); ?></span>
                <span class="cb-value" id="topCashBalance">৳<?php echo number_format($cashBalance, 2); ?></span>
            </div>
        </div>

        <div class="profile-menu">
            <button class="profile-btn" id="profileBtn">
                <div class="profile-avatar"><?php echo strtoupper(substr($_SESSION['full_name'], 0, 1)); ?></div>
                <span class="profile-name"><?php echo htmlspecialchars($_SESSION['full_name']); ?></span>
                <i class="fa-solid fa-chevron-down" style="font-size:11px;color:var(--text-muted);"></i>
            </button>
            <div class="profile-dropdown" id="profileDropdown">
                <a data-page="settings"><i class="fa-solid fa-gear"></i> <?php echo lang('settings'); ?></a>
                <a class="logout-link" id="logoutBtn"><i class="fa-solid fa-right-from-bracket"></i> <?php echo lang('logout'); ?></a>
            </div>
        </div>
    </div>
</header>

<!-- ============ MAIN CONTENT ============ -->
<main class="main-content">
    <div id="pageContent">
        <!-- Page content loads here dynamically -->
        <div class="skeleton skeleton-card"></div>
        <div class="skeleton skeleton-card"></div>
        <div class="skeleton skeleton-card"></div>
    </div>
</main>

<!-- ============ BOTTOM NAV ============ -->
<nav class="bottom-nav" id="bottomNav">
    <div class="bn-item" data-page="dashboard"><i class="fa-solid fa-gauge-high"></i><span><?php echo lang('dashboard'); ?></span></div>
    <div class="bn-item" data-page="purchase"><i class="fa-solid fa-cart-shopping"></i><span><?php echo lang('purchase'); ?></span></div>
    <div class="bn-item" data-page="sales"><i class="fa-solid fa-tags"></i><span><?php echo lang('sales'); ?></span></div>
    <div class="bn-item" data-page="expenses"><i class="fa-solid fa-receipt"></i><span><?php echo lang('expenses'); ?></span></div>
    <div class="bn-item" data-page="settings"><i class="fa-solid fa-gear"></i><span><?php echo lang('settings'); ?></span></div>
</nav>

<!-- ============ LIBRARIES ============ -->
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/gsap/3.12.5/gsap.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.4/dist/chart.umd.min.js"></script>

<script>
/* ==========================================================
   CASH KHATA - CORE SPA ENGINE
   ========================================================== */

// Global App State
const CK = {
    currentPage: null,
    lang: <?php echo json_encode($currentLang); ?>
};

/**
 * Update Top Bar Cash Balance (call this after any transaction)
 */
function updateCashBalance(newBalance) {
    const el = document.getElementById('topCashBalance');
    gsap.to(el, {
        duration: 0.3,
        onStart: function () {
            el.textContent = '৳' + parseFloat(newBalance).toLocaleString('en-US', { minimumFractionDigits: 2, maximumFractionDigits: 2 });
        },
        scale: 1.08,
        yoyo: true,
        repeat: 1,
        ease: "power1.inOut"
    });
}

/**
 * Show Skeleton Loading inside content area
 */
function showSkeleton() {
    const content = document.getElementById('pageContent');
    content.innerHTML = `
        <div class="skeleton skeleton-card"></div>
        <div class="skeleton skeleton-card"></div>
        <div class="skeleton skeleton-row"></div>
        <div class="skeleton skeleton-row"></div>
        <div class="skeleton skeleton-row"></div>
    `;
}

/**
 * Execute <script> tags found inside dynamically injected HTML
 * (innerHTML does not auto-run scripts, so we do it manually)
 */
function executeInlineScripts(container) {
    const scripts = container.querySelectorAll('script');
    scripts.forEach(oldScript => {
        const newScript = document.createElement('script');
        if (oldScript.src) {
            newScript.src = oldScript.src;
        } else {
            newScript.textContent = oldScript.textContent;
        }
        oldScript.parentNode.replaceChild(newScript, oldScript);
    });
}

/**
 * Load a page into the SPA content area via Fetch (AJAX)
 */
async function loadPage(page, pushState = true) {
    if (!page) page = 'dashboard';

    showSkeleton();
    setActiveNav(page);
    CK.currentPage = page;

    try {
        const response = await fetch('pages/' + page + '.php', {
            headers: { 'X-Requested-With': 'XMLHttpRequest' }
        });

        if (!response.ok) throw new Error('Page not found');

        const html = await response.text();
        const content = document.getElementById('pageContent');
        content.innerHTML = html;

        // Animate content in
        gsap.from(content.children, {
            y: 16,
            opacity: 0,
            duration: 0.45,
            stagger: 0.05,
            ease: "power2.out"
        });

        executeInlineScripts(content);

        if (pushState) {
            history.pushState({ page: page }, '', '#' + page);
        }
    } catch (err) {
        document.getElementById('pageContent').innerHTML = `
            <div class="ck-card text-center py-5">
                <i class="fa-solid fa-triangle-exclamation" style="font-size:32px;color:var(--warning);"></i>
                <p class="mt-3 text-muted">Failed to load page. Please try again.</p>
            </div>
        `;
    }
}

/**
 * Highlight active nav item in sidebar + bottom nav
 */
function setActiveNav(page) {
    document.querySelectorAll('.sidebar-nav .nav-item').forEach(el => {
        el.classList.toggle('active', el.dataset.page === page);
    });
    document.querySelectorAll('.bottom-nav .bn-item').forEach(el => {
        el.classList.toggle('active', el.dataset.page === page);
    });
}

/**
 * Global SweetAlert2 Helpers (reusable everywhere)
 */
function ckToast(icon, title) {
    Swal.fire({
        icon: icon,
        title: title,
        toast: true,
        position: 'top-end',
        showConfirmButton: false,
        timer: 2200,
        timerProgressBar: true
    });
}

function ckConfirm(text) {
    return Swal.fire({
        title: 'Are you sure?',
        text: text,
        icon: 'warning',
        showCancelButton: true,
        confirmButtonColor: '#dc2626',
        cancelButtonColor: '#64748b',
        confirmButtonText: 'Yes, delete it',
        reverseButtons: true
    });
}

/* ==========================================================
   NAVIGATION EVENT BINDING
   ========================================================== */
document.querySelectorAll('[data-page]').forEach(el => {
    el.addEventListener('click', () => loadPage(el.dataset.page));
});

/* Profile Dropdown Toggle */
const profileBtn = document.getElementById('profileBtn');
const profileDropdown = document.getElementById('profileDropdown');

profileBtn.addEventListener('click', (e) => {
    e.stopPropagation();
    profileDropdown.classList.toggle('show');
});

document.addEventListener('click', () => {
    profileDropdown.classList.remove('show');
});

/* Logout */
document.getElementById('logoutBtn').addEventListener('click', async () => {
    const result = await Swal.fire({
        title: 'Logout?',
        text: 'You will be signed out of Cash Khata.',
        icon: 'question',
        showCancelButton: true,
        confirmButtonColor: '#2563eb',
        confirmButtonText: 'Yes, logout'
    });

    if (result.isConfirmed) {
        await fetch('api/auth/logout.php');
        window.location.href = 'login.php';
    }
});

/* Browser Back/Forward Support */
window.addEventListener('popstate', (e) => {
    const page = (e.state && e.state.page) ? e.state.page : 'dashboard';
    loadPage(page, false);
});

/* ==========================================================
   INITIAL PAGE LOAD
   ========================================================== */
window.addEventListener('DOMContentLoaded', () => {
    gsap.from('.sidebar', { x: -20, opacity: 0, duration: 0.5, ease: "power2.out" });
    gsap.from('.topbar', { y: -16, opacity: 0, duration: 0.5, ease: "power2.out" });

    const initialPage = window.location.hash ? window.location.hash.replace('#', '') : 'dashboard';
    loadPage(initialPage, false);
});
</script>

</body>
</html>