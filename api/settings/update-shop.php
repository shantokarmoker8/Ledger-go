<?php
declare(strict_types=1);

require_once __DIR__ . '/../../includes/functions.php';
requireAuth();

$input = getJsonInput();
verifyCsrfToken($input['csrf_token'] ?? null);

$lang = $input['lang'] ?? 'en';
if (!in_array($lang, ['en', 'bn'], true)) {
    $lang = 'en';
}

startSecureSession();
$_SESSION['lang'] = $lang;

jsonResponse(true, 'Language updated.');