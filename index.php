<?php
declare(strict_types=1);
require_once __DIR__ . '/includes/functions.php';
startSecureSession();

// Protect SPA — must be logged in
if (empty($_SESSION['user_id'])) {
    header('Location: login.php');
    exit;
}

$csrfToken = generateCsrfToken();
?>
<!-- Full SPA Shell will be built in the Dashboard step (Step 3),
     since sidebar + header + router depend on Dashboard structure. -->