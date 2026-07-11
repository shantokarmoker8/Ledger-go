<?php
declare(strict_types=1);
require_once __DIR__ . '/includes/functions.php';
startSecureSession();

if (empty($_SESSION['user_id'])) {
    header('Location: login.php');
    exit;
}

if (empty($_SESSION['lang'])) {
    $_SESSION['lang'] = 'en';
}

$csrfToken = generateCsrfToken();
$lang = loadLangFile();
?>
<!DOCTYPE html>
<html lang="<?= $_SESSION['lang'] ?>">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>LedgerGo</title>
<meta name="csrf-token" content="<?= $csrfToken ?>">

<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.css">
<link rel="icon" href="assets/logo.png">

<style>
    :root { --primary: #2F5BE0; }
    body { background: #f4f6fb; font-family: 'Segoe UI', sans-serif; overflow-x: hidden; }

    #appWrapper { display: flex; min-height: 100vh; }

    #mainSidebar {
        width: 240px;
        min-height: calc(100vh - 60px);
        position: fixed;
        top: 60px;
        left: 0;
        bottom: 0;
        z-index: 1030;
        transition: transform 0.3s ease;
        overflow-y: auto;
    }
    #contentArea {
        margin-left: 240px;
        margin-top: 60px;
        width: calc(100% - 240px);
        min-height: calc(100vh - 60px);
        display: flex;
        flex-direction: column;
    }
    #pageContainer { flex: 1; padding: 20px; }

    .sidebar-link {
        color: #495057;
        border-radius: 10px;
        font-weight: 500;
        padding: 10px 14px;
    }
    .sidebar-link.active, .sidebar-link:hover {
        background-color: var(--primary);
        color: #fff;
    }

    .card { border: none; border-radius: 16px; box-shadow: 0 2px 12px rgba(0,0,0,0.06); }
    .btn-primary { background-color: var(--primary); border-color: var(--primary); border-radius: 10px; }
    .btn-primary:hover { background-color: #2547b8; border-color: #2547b8; }

    #sidebarOverlay {
        display: none;
        position: fixed; inset: 0;
        background: rgba(0,0,0,0.4);
        z-index: 1020;
    }

    @media (max-width: 991.98px) {
        #mainSidebar { transform: translateX(-100%); }
        #mainSidebar.show { transform: translateX(0); }
        #contentArea { margin-left: 0; width: 100%; }
        #sidebarOverlay.show { display: block; }
    }

    #routeLoader {
        position: fixed; inset: 0; background: rgba(255,255,255,0.7);
        display: none; align-items: center; justify-content: center; z-index: 2000;
    }
    #routeLoader.show { display: flex; }
</style>
</head>
<body>

<div id="routeLoader"><img src="assets/loading.gif" style="width:60px;"></div>

<?php include __DIR__ . '/includes/header.php'; ?>

<div id="appWrapper">
    <?php include __DIR__ . '/includes/sidebar.php'; ?>

    <div id="contentArea">
        <div id="pageContainer">
            <!-- SPA Pages load here via AJAX -->
        </div>
        <?php include __DIR__ . '/includes/footer.php'; ?>
    </div>
</div>

<script src="https://www.gstatic.com/firebasejs/10.13.0/firebase-app-compat.js"></script>
<script src="https://www.gstatic.com/firebasejs/10.13.0/firebase-auth-compat.js"></script>
<script src="config/firebase-config.js"></script>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/gsap@3.12.5/dist/gsap.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.4/dist/chart.umd.min.js"></script>
<script src="js/app.js"></script>

</body>
</html>