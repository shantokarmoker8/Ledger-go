<?php
require_once __DIR__ . '/../../includes/auth_check.php';
require_once __DIR__ . '/../../config/db.php';
header('Content-Type: application/json');

$input = json_decode(file_get_contents('php://input'), true);

$name = trim($input['name'] ?? '');
$mobile = trim($input['mobile'] ?? '');
$address = trim($input['address'] ?? '');

if ($name === '' || $mobile === '') {
    echo json_encode(["status" => "error", "message" => "Name and Mobile Number are required"]);
    exit;
}

try {
    $stmt = $pdo->prepare("INSERT INTO suppliers (name, mobile, address, due) VALUES (?, ?, ?, 0)");
    $stmt->execute([$name, $mobile, $address]);

    echo json_encode([
        "status" => "success",
        "message" => "Supplier added successfully",
        "data" => [
            "id" => $pdo->lastInsertId(),
            "name" => $name,
            "mobile" => $mobile,
            "address" => $address,
            "due" => 0
        ]
    ]);
} catch (Exception $e) {
    echo json_encode(["status" => "error", "message" => $e->getMessage()]);
}