<?php
declare(strict_types=1);

// Database Configuration
define('DB_HOST', 'localhost');
define('DB_NAME', 'ledgergo');
define('DB_USER', 'root');
define('DB_PASS', '');

// Firebase API Key (used for server-side token verification via REST API)
define('FIREBASE_API_KEY', 'AIzaSyAAN0rK7wzORzeUP0p_W8aqei1hfINwX_k');

function getDB(): PDO
{
    static $pdo = null;

    if ($pdo === null) {
        try {
            $dsn = "mysql:host=" . DB_HOST . ";dbname=" . DB_NAME . ";charset=utf8mb4";
            $pdo = new PDO($dsn, DB_USER, DB_PASS, [
                PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
                PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                PDO::ATTR_EMULATE_PREPARES   => false, // Real Prepared Statements = SQL Injection Protection
            ]);
        } catch (PDOException $e) {
            http_response_code(500);
            die(json_encode(['success' => false, 'message' => 'Database connection failed.']));
        }
    }

    return $pdo;
}