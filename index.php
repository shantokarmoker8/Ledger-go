<?php
require_once __DIR__ . '/includes/auth_check.php';
require_once __DIR__ . '/config/db.php';

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

<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
<link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;500;600;700&display=swap" rel="stylesheet">
<link rel="shortcut icon" href="./assets/logo.png" type="image/x-icon">
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

    * { margin: 0; padding: 0; box-sizing: border-box; font-family: 'Poppins', sans-serif; }

    /* ============ FIX: overflow-x:hidden পুরো <body>-কে একটা লুকানো
       Scroll Container বানিয়ে ফেলে, যার কারণে position:sticky দুইটা
       আলাদা Scroll Context-এর মধ্যে হিসাব গণ্ডগোল করে নড়াচড়া করত।
       overflow-x:clip একইভাবে Horizontal Overflow আটকায় কিন্তু কোনো
       নতুন Scroll Container তৈরি করে না — তাই Sticky Header এখন
       সরাসরি Window-এর সাপেক্ষে হিসাব হয়ে সম্পূর্ণ স্থির থাকে। ============ */
    body { background: var(--body-bg); color: var(--text-dark); overflow-x: clip; }

    a { text-decoration: none; }
    ::-webkit-scrollbar { width: 6px; height: 6px; }
    ::-webkit-scrollbar-thumb { background: #cbd5e1; border-radius: 10px; }

    /* ============ TOP BAR ============ */
    .topbar {
        position: fixed; top: 0; left: var(--sidebar-width); right: 0; height: var(--topbar-height);
        background: #ffffff; border-bottom: 1px solid var(--border-color);
        display: flex; align-items: center; justify-content: space-between;
        padding: 0 26px; z-index: 1000; transition: left 0.2s ease;
    }

    .topbar-left { display: none; align-items: center; gap: 12px; }
    .topbar-left .brand-logo {
        width: 36px; height: 36px; background: var(--primary-blue); border-radius: 10px;
        display: flex; align-items: center; justify-content: center; color: #fff; font-size: 15px;
    }
    .topbar-left .brand-text { display: none; }

    .topbar-right { display: flex; align-items: center; gap: 14px; margin-left: auto; }

    .cash-balance-box {
        display: flex; align-items: center; gap: 10px; background: var(--light-blue);
        border: 1px solid #dbeafe; padding: 8px 16px; border-radius: 10px;
    }
    .cash-balance-box i { color: var(--primary-blue); font-size: 14px; }
    .cash-balance-box .cb-label { font-size: 11px; color: var(--text-muted); display: block; line-height: 1; margin-bottom: 3px; }
    .cash-balance-box .cb-value { font-size: 14px; font-weight: 600; color: var(--primary-blue); line-height: 1; }

    .profile-menu { position: relative; }
    .profile-btn {
        display: flex; align-items: center; gap: 10px; background: transparent; border: none;
        cursor: pointer; padding: 6px 8px; border-radius: 10px; transition: background 0.2s ease;
    }
    .profile-btn:hover { background: #f1f5f9; }
    .profile-avatar {
        width: 36px; height: 36px; border-radius: 50%; background: var(--primary-blue); color: #fff;
        display: flex; align-items: center; justify-content: center; font-weight: 600; font-size: 14px;
    }
    .profile-name { font-size: 13px; font-weight: 500; color: var(--text-dark); display: none; }
    .profile-dropdown {
        position: absolute; top: 52px; right: 0; background: #fff; border: 1px solid var(--border-color);
        border-radius: 12px; box-shadow: 0 8px 24px rgba(0,0,0,0.08); min-width: 180px; padding: 8px;
        display: none; z-index: 1200;
    }
    .profile-dropdown.show { display: block; }
    .profile-dropdown a {
        display: flex; align-items: center; gap: 10px; padding: 10px 12px; border-radius: 8px;
        color: var(--text-dark); font-size: 13px; cursor: pointer; transition: background 0.15s ease;
    }
    .profile-dropdown a:hover { background: var(--light-blue); color: var(--primary-blue); }
    .profile-dropdown a.logout-link:hover { background: #fef2f2; color: var(--danger); }

    /* ============ SIDEBAR (DESKTOP) ============ */
    .sidebar {
        position: fixed; top: 0; left: 0; width: var(--sidebar-width); height: 100vh;
        background: var(--sidebar-bg); border-right: 1px solid var(--border-color);
        z-index: 1100; display: flex; flex-direction: column;
    }
    .sidebar-brand {
        height: var(--topbar-height); display: flex; align-items: center; gap: 12px;
        padding: 0 22px; border-bottom: 1px solid var(--border-color);
    }
    .sidebar-brand .brand-logo {
        width: 38px; height: 38px; background: var(--primary-blue); border-radius: 10px;
        display: flex; align-items: center; justify-content: center; color: #fff; font-size: 16px;
    }
    .sidebar-brand .brand-text { font-weight: 700; font-size: 17px; color: var(--text-dark); }
    .sidebar-nav { padding: 20px 14px; display: flex; flex-direction: column; gap: 4px; }
    .sidebar-nav .nav-item {
        display: flex; align-items: center; gap: 12px; padding: 12px 14px; border-radius: 10px;
        color: var(--text-muted); font-size: 14px; font-weight: 500; cursor: pointer; transition: all 0.2s ease;
    }
    .sidebar-nav .nav-item i { width: 20px; text-align: center; font-size: 15px; }
    .sidebar-nav .nav-item:hover { background: var(--light-blue); color: var(--primary-blue); }
    .sidebar-nav .nav-item.active { background: var(--primary-blue); color: #fff; }

    /* ============ MAIN CONTENT ============ */
    .main-content {
        margin-left: var(--sidebar-width); margin-top: var(--topbar-height); padding: 26px;
        min-height: calc(100vh - var(--topbar-height)); transition: margin-left 0.2s ease;
    }
    #pageContent { position: relative; }

    /* ============ BOTTOM NAV (TABLET / MOBILE) ============ */
    .bottom-nav {
        display: none; position: fixed; bottom: 0; left: 0; right: 0; height: var(--bottomnav-height);
        background: #ffffff; border-top: 1px solid var(--border-color); z-index: 1100;
        align-items: center; justify-content: space-around; box-shadow: 0 -4px 16px rgba(0,0,0,0.04);
    }
    .bottom-nav .bn-item {
        display: flex; flex-direction: column; align-items: center; gap: 4px; color: var(--text-muted);
        font-size: 10px; cursor: pointer; flex: 1; padding: 6px 0; transition: color 0.2s ease;
    }
    .bottom-nav .bn-item i { font-size: 17px; }
    .bottom-nav .bn-item.active { color: var(--primary-blue); }

    /* ============ SKELETON LOADING ============ */
    .skeleton {
        background: linear-gradient(90deg, #eef2f8 25%, #f7f9fc 50%, #eef2f8 75%);
        background-size: 200% 100%; animation: skeleton-loading 1.4s ease infinite; border-radius: 8px;
    }
    @keyframes skeleton-loading { 0% { background-position: 200% 0; } 100% { background-position: -200% 0; } }
    .skeleton-card { height: 100px; margin-bottom: 16px; }
    .skeleton-row { height: 46px; margin-bottom: 10px; }

    /* ============ GLOBAL: PAGE HEADER ============ */
    .page-head { display: flex; justify-content: space-between; align-items: center; flex-wrap: wrap; gap: 10px; margin-bottom: 16px; }
    .page-head h4 { font-weight: 600; margin: 0; }
    .page-head p { color: var(--text-muted); font-size: 13px; margin: 0; }

    /* ============ GLOBAL CARD / BUTTON ============ */
    .ck-card { background: #fff; border: 1px solid var(--border-color); border-radius: 14px; padding: 20px; }
    .ck-btn {
        border: none; border-radius: 10px; padding: 10px 18px; font-size: 13px; font-weight: 600;
        cursor: pointer; display: inline-flex; align-items: center; gap: 8px; transition: all 0.2s ease;
    }
    .ck-btn-primary { background: var(--primary-blue); color: #fff; }
    .ck-btn-primary:hover { background: var(--dark-blue); }
    .ck-btn-outline { background: #fff; border: 1.5px solid var(--border-color); color: var(--text-dark); }
    .ck-btn-outline:hover { border-color: var(--primary-blue); color: var(--primary-blue); }
    .ck-btn-danger-soft { background: #fef2f2; color: var(--danger); }
    .ck-btn-danger-soft:hover { background: #fee2e2; }
    .ck-btn-success-soft { background: #f0fdf4; color: var(--success); }
    .ck-btn-success-soft:hover { background: #dcfce7; }
    .icon-btn {
        width: 30px; height: 30px; border-radius: 8px; border: none; display: inline-flex;
        align-items: center; justify-content: center; cursor: pointer; font-size: 12px; transition: all 0.2s ease;
    }

    /* ============ GLOBAL: SEARCH INPUT ============ */
    .input-group-search { position: relative; }
    .input-group-search i { position: absolute; left: 14px; top: 50%; transform: translateY(-50%); color: var(--text-muted); font-size: 13px; }
    .input-group-search input {
        width: 100%; padding: 10px 14px 10px 38px; border: 1.5px solid var(--border-color);
        border-radius: 10px; font-size: 13px; outline: none;
    }
    .input-group-search input:focus { border-color: var(--primary-blue); }

    /* ============ GLOBAL: TABLE ============ */
    .ck-table { width: 100%; border-collapse: collapse; font-size: 13px; }
    .ck-table thead th {
        text-align: left; padding: 14px 18px; background: #f8fafc; color: var(--text-muted);
        font-weight: 600; font-size: 11px; text-transform: uppercase; letter-spacing: 0.4px;
        border-bottom: 1px solid var(--border-color); white-space: nowrap;
    }
    .ck-table tbody td { padding: 14px 18px; border-bottom: 1px solid var(--border-color); vertical-align: middle; white-space: nowrap; }
    .ck-table tbody tr:last-child td { border-bottom: none; }
    .ck-table tbody tr:hover { background: #fafbfd; }

    /* ============ GLOBAL: MODAL ============ */
    .ck-modal-overlay {
        position: fixed; inset: 0; background: rgba(15,23,42,0.45); z-index: 2000;
        display: flex; align-items: center; justify-content: center; padding: 16px;
    }
    .ck-modal-box { background: #fff; border-radius: 16px; padding: 24px; width: 100%; max-width: 460px; max-height: 90vh; overflow-y: auto; }
    .ck-modal-header { display: flex; justify-content: space-between; align-items: center; margin-bottom: 18px; }
    .ck-modal-header h5 { font-weight: 600; margin: 0; }
    .ck-modal-close { cursor: pointer; color: var(--text-muted); font-size: 16px; transition: color 0.2s ease; }
    .ck-modal-close:hover { color: var(--danger); }

    /* ============ GLOBAL: FORM ELEMENTS ============ */
    .ck-label { font-size: 12px; font-weight: 500; color: var(--text-dark); display: block; margin-bottom: 6px; }
    .ck-input, .ck-select {
        width: 100%; padding: 10px 14px; border: 1.5px solid var(--border-color); border-radius: 10px;
        font-size: 14px; outline: none; transition: all 0.2s ease;
    }
    .ck-input:focus, .ck-select:focus { border-color: var(--primary-blue); box-shadow: 0 0 0 3px rgba(37,99,235,0.1); }

    .ck-toggle-tabs { display: flex; background: #f1f5f9; border-radius: 10px; padding: 4px; gap: 4px; }
    .ck-toggle-btn {
        flex: 1; border: none; background: transparent; padding: 9px; border-radius: 8px;
        font-size: 12.5px; font-weight: 500; color: var(--text-muted); cursor: pointer; transition: all 0.2s ease;
    }
    .ck-toggle-btn.active { background: #fff; color: var(--primary-blue); box-shadow: 0 1px 4px rgba(0,0,0,0.08); }

    .ck-total-box {
        display: flex; justify-content: space-between; align-items: center; background: var(--light-blue);
        padding: 12px 16px; border-radius: 10px; font-size: 13px; font-weight: 600; color: var(--primary-blue);
    }

    .ck-filter-btn {
        border: 1.5px solid var(--border-color); background: #fff; color: var(--text-muted);
        padding: 7px 14px; border-radius: 8px; font-size: 12px; font-weight: 500; cursor: pointer; transition: all 0.2s ease;
    }
    .ck-filter-btn.active { background: var(--primary-blue); border-color: var(--primary-blue); color: #fff; }

    /* ============ GLOBAL: LIST ROWS ============ */
    .list-row { display: flex; justify-content: space-between; align-items: center; padding: 12px 0; border-bottom: 1px solid var(--border-color); font-size: 13px; }
    .list-row:last-child { border-bottom: none; }
    .list-row .lr-title { font-weight: 500; color: var(--text-dark); }
    .list-row .lr-sub { color: var(--text-muted); font-size: 11px; margin-top: 2px; }
    .list-row .lr-amount { font-weight: 600; }
    .badge-cash { background: #f0fdf4; color: #16a34a; font-size: 10px; padding: 3px 8px; border-radius: 6px; }
    .badge-due { background: #fff7ed; color: #d97706; font-size: 10px; padding: 3px 8px; border-radius: 6px; }

    /* ============ RESPONSIVE BREAKPOINTS ============ */
    @media (min-width: 992px) { .profile-name { display: block; } }

    @media (max-width: 991px) {
        :root { --sidebar-width: 0px; }
        .sidebar { display: none; }
        .topbar { left: 0; }
        .topbar-left { display: flex; }
        .main-content { margin-left: 0; padding: 18px 16px 90px 16px; }
        .bottom-nav { display: flex; }
    }

    @media (max-width: 767px) {
        .ck-btn { padding: 9px 16px; font-size: 12px; }
        .page-head { flex-direction: column; align-items: center; text-align: center; gap: 12px; }

        .ck-table thead { display: none; }
        .ck-table, .ck-table tbody, .ck-table tr, .ck-table td { display: block; width: 100%; }
        .ck-table tbody tr {
            margin-bottom: 12px; border: 1px solid var(--border-color); border-radius: 12px;
            padding: 6px 14px; background: #fff;
        }
        .ck-table tbody td {
            display: flex; justify-content: space-between; align-items: center; gap: 10px;
            padding: 9px 0; border-bottom: 1px dashed var(--border-color); white-space: normal; text-align: right;
        }
        .ck-table tbody td:last-child { border-bottom: none; }
        .ck-table tbody td::before {
            content: attr(data-label); font-weight: 600; color: var(--text-muted); font-size: 11px; text-align: left;
        }
    }

    @media (max-width: 480px) {
        .cash-balance-box .cb-label { display: none; }
        .cash-balance-box { padding: 8px 12px; }
    }
</style>
</head>
<body>

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
                <div class="profile-avatar"><?php echo strtoupper(substr($_SESSION['full_name'] ?? 'U', 0, 1)); ?></div>
                <span class="profile-name"><?php echo htmlspecialchars($_SESSION['full_name'] ?? 'User'); ?></span>
                <i class="fa-solid fa-chevron-down" style="font-size:11px;color:var(--text-muted);"></i>
            </button>
            <div class="profile-dropdown" id="profileDropdown">
                <a data-page="settings"><i class="fa-solid fa-gear"></i> <?php echo lang('settings'); ?></a>
                <a class="logout-link" id="logoutBtn"><i class="fa-solid fa-right-from-bracket"></i> <?php echo lang('logout'); ?></a>
            </div>
        </div>
    </div>
</header>

<main class="main-content">
    <div id="pageContent">
        <div class="skeleton skeleton-card"></div>
        <div class="skeleton skeleton-card"></div>
        <div class="skeleton skeleton-card"></div>
    </div>
</main>

<nav class="bottom-nav" id="bottomNav">
    <div class="bn-item" data-page="dashboard"><i class="fa-solid fa-gauge-high"></i><span><?php echo lang('dashboard'); ?></span></div>
    <div class="bn-item" data-page="purchase"><i class="fa-solid fa-cart-shopping"></i><span><?php echo lang('purchase'); ?></span></div>
    <div class="bn-item" data-page="sales"><i class="fa-solid fa-tags"></i><span><?php echo lang('sales'); ?></span></div>
    <div class="bn-item" data-page="expenses"><i class="fa-solid fa-receipt"></i><span><?php echo lang('expenses'); ?></span></div>
</nav>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/gsap/3.12.5/gsap.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.4/dist/chart.umd.min.js"></script>

<script>
const CK = { currentPage: null, lang: <?php echo json_encode($currentLang); ?> };

function updateCashBalance(newBalance) {
    const el = document.getElementById('topCashBalance');
    gsap.to(el, {
        duration: 0.3,
        onStart: function () {
            el.textContent = '৳' + parseFloat(newBalance).toLocaleString('en-US', { minimumFractionDigits: 2, maximumFractionDigits: 2 });
        },
        scale: 1.08, yoyo: true, repeat: 1, ease: "power1.inOut"
    });
}

function showSkeleton() {
    document.getElementById('pageContent').innerHTML = `
        <div class="skeleton skeleton-card"></div>
        <div class="skeleton skeleton-card"></div>
        <div class="skeleton skeleton-row"></div>
        <div class="skeleton skeleton-row"></div>
        <div class="skeleton skeleton-row"></div>
    `;
}

function executeInlineScripts(container) {
    const scripts = container.querySelectorAll('script');
    scripts.forEach(oldScript => {
        const newScript = document.createElement('script');
        if (oldScript.src) newScript.src = oldScript.src;
        else newScript.textContent = oldScript.textContent;
        oldScript.parentNode.replaceChild(newScript, oldScript);
    });
}

async function loadPage(page, pushState = true) {
    if (!page) page = 'dashboard';
    showSkeleton();
    setActiveNav(page);
    CK.currentPage = page;

    try {
        const response = await fetch('pages/' + page + '.php', { headers: { 'X-Requested-With': 'XMLHttpRequest' } });
        if (!response.ok) throw new Error('Page not found');

        const html = await response.text();
        const content = document.getElementById('pageContent');
        content.innerHTML = html;

        gsap.fromTo(content,
            { opacity: 0 },
            { opacity: 1, duration: 0.35, ease: "power2.out", clearProps: "opacity" }
        );

        executeInlineScripts(content);

        if (pushState) history.pushState({ page: page }, '', '#' + page);
    } catch (err) {
        document.getElementById('pageContent').innerHTML = `
            <div class="ck-card text-center py-5">
                <i class="fa-solid fa-triangle-exclamation" style="font-size:32px;color:var(--warning);"></i>
                <p class="mt-3 text-muted">Failed to load page. Please try again.</p>
            </div>
        `;
    }
}

function setActiveNav(page) {
    document.querySelectorAll('.sidebar-nav .nav-item').forEach(el => el.classList.toggle('active', el.dataset.page === page));
    document.querySelectorAll('.bottom-nav .bn-item').forEach(el => el.classList.toggle('active', el.dataset.page === page));
}

function ckToast(icon, title) {
    Swal.fire({ icon, title, toast: true, position: 'top-end', showConfirmButton: false, timer: 2200, timerProgressBar: true });
}

function ckConfirm(text) {
    return Swal.fire({
        title: 'Are you sure?', text, icon: 'warning', showCancelButton: true,
        confirmButtonColor: '#dc2626', cancelButtonColor: '#64748b',
        confirmButtonText: 'Yes, confirm', reverseButtons: true
    });
}

document.querySelectorAll('[data-page]').forEach(el => el.addEventListener('click', () => loadPage(el.dataset.page)));

const profileBtn = document.getElementById('profileBtn');
const profileDropdown = document.getElementById('profileDropdown');
profileBtn.addEventListener('click', (e) => { e.stopPropagation(); profileDropdown.classList.toggle('show'); });
document.addEventListener('click', () => profileDropdown.classList.remove('show'));

document.getElementById('logoutBtn').addEventListener('click', async () => {
    const result = await Swal.fire({
        title: 'Logout?', text: 'You will be signed out of Cash Khata.', icon: 'question',
        showCancelButton: true, confirmButtonColor: '#2563eb', confirmButtonText: 'Yes, logout'
    });
    if (result.isConfirmed) {
        await fetch('api/auth/logout.php');
        window.location.href = 'login.php';
    }
});

window.addEventListener('popstate', (e) => {
    const page = (e.state && e.state.page) ? e.state.page : 'dashboard';
    loadPage(page, false);
});

window.addEventListener('DOMContentLoaded', () => {
    gsap.from('.sidebar', { x: -20, opacity: 0, duration: 0.5, ease: "power2.out" });
    gsap.from('.topbar', { y: -16, opacity: 0, duration: 0.5, ease: "power2.out" });
    const initialPage = window.location.hash ? window.location.hash.replace('#', '') : 'dashboard';
    loadPage(initialPage, false);
});
</script>

</body>
</html>