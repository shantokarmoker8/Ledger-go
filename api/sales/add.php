<?php
require_once __DIR__ . '/../../includes/auth_check.php';
require_once __DIR__ . '/../../config/db.php';
header('Content-Type: application/json');

$input = json_decode(file_get_contents('php://input'), true);

$productId      = (int) ($input['product_id'] ?? 0);
$customerId     = isset($input['customer_id']) && $input['customer_id'] !== '' ? (int) $input['customer_id'] : null;
$quantity       = (int) ($input['quantity'] ?? 0);
$salePrice      = (float) ($input['sale_price'] ?? 0);
$discountAmount = (float) ($input['discount_amount'] ?? 0);
$paidAmount     = (float) ($input['paid_amount'] ?? 0);

if ($productId <= 0) {
    echo json_encode(["status" => "error", "message" => "Please select a product"]);
    exit;
}
if ($quantity <= 0) {
    echo json_encode(["status" => "error", "message" => "Quantity must be greater than 0"]);
    exit;
}
if ($salePrice <= 0) {
    echo json_encode(["status" => "error", "message" => "Sale Price must be greater than 0"]);
    exit;
}
if ($discountAmount < 0) {
    echo json_encode(["status" => "error", "message" => "Discount cannot be negative"]);
    exit;
}
if ($paidAmount < 0) {
    echo json_encode(["status" => "error", "message" => "Pay Amount cannot be negative"]);
    exit;
}

$grossAmount = $salePrice * $quantity;

if ($discountAmount > $grossAmount) {
    echo json_encode(["status" => "error", "message" => "Discount cannot exceed the total amount"]);
    exit;
}

try {
    $pdo->beginTransaction();

    $prodStmt = $pdo->prepare("SELECT * FROM products WHERE id = ? FOR UPDATE");
    $prodStmt->execute([$productId]);
    $product = $prodStmt->fetch();

    if (!$product) {
        $pdo->rollBack();
        echo json_encode(["status" => "error", "message" => "Product not found"]);
        exit;
    }

    if ($product['stock'] < $quantity) {
        $pdo->rollBack();
        echo json_encode(["status" => "error", "message" => "Insufficient Stock. Available: " . $product['stock']]);
        exit;
    }

    // ============ LOSS PROTECTION ============
    // মোট ক্রয়মূল্য (Cost) থেকে কমে বিক্রি করা যাবে না
    $totalCost = (float) $product['purchase_price'] * $quantity;
    $totalAmount = $grossAmount - $discountAmount;

    if ($totalAmount < $totalCost) {
        $maxAllowedDiscount = $grossAmount - $totalCost;
        $pdo->rollBack();
        echo json_encode([
            "status" => "error",
            "message" => "Discount too high — this would cause a loss. Maximum allowed discount is " . number_format($maxAllowedDiscount, 2) . " Taka (Cost Price: " . number_format($totalCost, 2) . ")"
        ]);
        exit;
    }

    if ($paidAmount > $totalAmount) {
        $pdo->rollBack();
        echo json_encode(["status" => "error", "message" => "Pay Amount cannot exceed Total Amount (after discount)"]);
        exit;
    }

    $dueAmount = $totalAmount - $paidAmount;

    if ($dueAmount > 0 && !$customerId) {
        $pdo->rollBack();
        echo json_encode(["status" => "error", "message" => "Customer is required when there is a Due amount"]);
        exit;
    }

    $paymentType = ($dueAmount <= 0) ? 'cash' : 'due';

    $pdo->prepare("UPDATE products SET stock = stock - ? WHERE id = ?")->execute([$quantity, $productId]);

    $insertSale = $pdo->prepare("
        INSERT INTO sales (product_id, customer_id, quantity, sale_price, discount_amount, total_amount, payment_type, paid_amount, due_amount)
        VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)
    ");
    $insertSale->execute([$productId, $customerId, $quantity, $salePrice, $discountAmount, $totalAmount, $paymentType, $paidAmount, $dueAmount]);

    $settings = $pdo->query("SELECT cash_balance FROM settings LIMIT 1 FOR UPDATE")->fetch();
    $newCash = (float) $settings['cash_balance'] + $paidAmount;
    $pdo->prepare("UPDATE settings SET cash_balance = ?")->execute([$newCash]);

    if ($dueAmount > 0 && $customerId) {
        $pdo->prepare("UPDATE customers SET due = due + ? WHERE id = ?")->execute([$dueAmount, $customerId]);
    }

    $pdo->commit();

    echo json_encode([
        "status" => "success",
        "message" => "Sale completed successfully",
        "cash_balance" => $newCash
    ]);
} catch (Exception $e) {
    $pdo->rollBack();
    echo json_encode(["status" => "error", "message" => $e->getMessage()]);
}