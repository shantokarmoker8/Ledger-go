<?php
/**
 * ================================================
 * Error Handling Configuration
 * ================================================
 * Warning/Error যেন কখনো Browser-এ দেখা না যায় (Design ভাঙবে না)।
 * কিন্তু Error Log ফাইলে ঠিকই সংরক্ষিত হবে, যাতে Debug করা যায়।
 */
error_reporting(E_ALL);
ini_set('display_errors', '0');
ini_set('log_errors', '1');
ini_set('error_log', __DIR__ . '/../error_log.txt');

/**
 * ================================================
 * Database Configuration File
 * XAMPP Default Settings
 * ================================================
 */
$DB_HOST = "localhost";
$DB_USER = "root";
$DB_PASS = "";
$DB_NAME = "cash_khata";

try {
    $pdo = new PDO(
        "mysql:host=$DB_HOST;dbname=$DB_NAME;charset=utf8mb4",
        $DB_USER,
        $DB_PASS,
        [
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC
        ]
    );
} catch (PDOException $e) {
    die(json_encode([
        "status" => "error",
        "message" => "Database Connection Failed: " . $e->getMessage()
    ]));
}