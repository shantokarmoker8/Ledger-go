<?php
require_once __DIR__ . '/../../includes/auth_check.php';
require_once __DIR__ . '/../../config/db.php';
header('Content-Type: application/json');

$input = json_decode(file_get_contents('php://input'), true);

$businessName    = trim($input['business_name'] ?? '');
$businessAddress = trim($input['business_address'] ?? '');
$businessPhone   = trim($input['business_phone'] ?? '');

if ($businessName === '') {
    echo json_encode(["status" => "error", "message" => "Business Name is required"]);
    exit;
}

try {
    $stmt = $pdo->prepare("UPDATE settings SET business_name = ?, business_address = ?, business_phone = ?");
    $stmt->execute([$businessName, $businessAddress, $businessPhone]);

    echo json_encode([
        "status" => "success",
        "message" => "Business information updated successfully",
        "business_name" => $businessName
    ]);
} catch (Exception $e) {
    echo json_encode(["status" => "error", "message" => $e->getMessage()]);
}