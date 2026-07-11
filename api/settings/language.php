<?php
require_once __DIR__ . '/../../includes/auth_check.php';
require_once __DIR__ . '/../../config/db.php';
header('Content-Type: application/json');

$input = json_decode(file_get_contents('php://input'), true);
$lang = $input['language'] ?? 'en';

if (!in_array($lang, ['en', 'bn'])) {
    echo json_encode(["status" => "error", "message" => "Invalid language"]);
    exit;
}

try {
    $pdo->prepare("UPDATE settings SET language = ?")->execute([$lang]);
    $_SESSION['language'] = $lang;

    echo json_encode(["status" => "success", "message" => "Language updated successfully"]);
} catch (Exception $e) {
    echo json_encode(["status" => "error", "message" => $e->getMessage()]);
}