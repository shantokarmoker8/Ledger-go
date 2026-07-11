<?php
/**
 * Auth Check - Include this file at the top of every protected page/api
 */
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

if (!isset($_SESSION['user_id'])) {
    // If it's an API request, return JSON error
    if (strpos($_SERVER['REQUEST_URI'], '/api/') !== false) {
        header('Content-Type: application/json');
        http_response_code(401);
        echo json_encode(["status" => "error", "message" => "Unauthorized. Please login."]);
        exit;
    } else {
        // If it's a page request, redirect to login
        header("Location: login.php");
        exit;
    }
}

/**
 * Helper function to load language strings
 */
function lang($key)
{
    static $strings = null;
    if ($strings === null) {
        $langCode = $_SESSION['language'] ?? 'en';
        $file = __DIR__ . "/../lang/{$langCode}.php";
        if (!file_exists($file)) {
            $file = __DIR__ . "/../lang/en.php";
        }
        $strings = require $file;
    }
    return $strings[$key] ?? $key;
}