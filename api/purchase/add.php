<?php
require_once __DIR__ . '/../../includes/auth_check.php';
require_once __DIR__ . '/../../config/db.php';
header('Content-Type: application/json');

$input = json_decode(file_get_contents('php://input'), true);

$productName    = trim($input['product_name'] ?? '');
$description    = trim($input['description'] ?? '');
$purchasePrice  = (float) ($input['purchase_price'] ?? 0);
$salePrice      = (float) ($input['sale_price'] ?? 0);
$quantity       = (int) ($input['quantity'] ?? 0);
$lowStockAlert  = 5;
$supplierId     = isset($input['supplier_id']) && $input['supplier_id'] !== '' ? (int) $input['supplier_id'] : null;
$paidAmount     = (float) ($input['paid_amount'] ?? 0);

if ($productName === '') {
    echo json_encode(["status" => "error", "message" => "Product Name is required"]);
    exit;
}
if ($quantity <= 0) {
    echo json_encode(["status" => "error", "message" => "Quantity must be greater than 0"]);
    exit;
}
if ($purchasePrice <= 0) {
    echo json_encode(["status" => "error", "message" => "Purchase Price must be greater than 0"]);
    exit;
}
if ($salePrice <= 0) {
    echo json_encode(["status" => "error", "message" => "Sale Price must be greater than 0"]);
    exit;
}
if ($paidAmount < 0) {
    echo json_encode(["status" => "error", "message" => "Pay Amount cannot be negative"]);
    exit;
}

$totalAmount = $purchasePrice * $quantity;

if ($paidAmount > $totalAmount) {
    echo json_encode(["status" => "error", "message" => "Pay Amount cannot exceed Total Amount"]);
    exit;
}

$dueAmount = $totalAmount - $paidAmount;

if ($dueAmount > 0 && !$supplierId) {
    echo json_encode(["status" => "error", "message" => "Supplier is required when there is a Due amount"]);
    exit;
}

// Cash হিসেবে Full Paid হলে 'cash', আংশিক/সম্পূর্ণ বাকি থাকলে 'due' — শুধু Display/History-এর জন্য
$paymentType = ($dueAmount <= 0) ? 'cash' : 'due';

try {
    $pdo->beginTransaction();

    $settings = $pdo->query("SELECT * FROM settings LIMIT 1 FOR UPDATE")->fetch();
    $currentCash = (float) $settings['cash_balance'];

    if ($paidAmount > $currentCash) {
        $pdo->rollBack();
        echo json_encode(["status" => "error", "message" => "Insufficient Cash Balance"]);
        exit;
    }

    // ============ AUTO-DETECT: Same Name Product (Case-Insensitive) ============
    $matchStmt = $pdo->prepare("SELECT * FROM products WHERE LOWER(name) = LOWER(?) LIMIT 1 FOR UPDATE");
    $matchStmt->execute([$productName]);
    $product = $matchStmt->fetch();

    if ($product) {
        $productId = $product['id'];
        $pdo->prepare("UPDATE products SET stock = stock + ?, description = ?, purchase_price = ?, sale_price = ?, low_stock_alert = ?, supplier_id = ? WHERE id = ?")
            ->execute([$quantity, $description, $purchasePrice, $salePrice, $lowStockAlert, $supplierId, $productId]);
    } else {
        $insertProd = $pdo->prepare("INSERT INTO products (name, description, purchase_price, sale_price, stock, low_stock_alert, supplier_id) VALUES (?, ?, ?, ?, ?, ?, ?)");
        $insertProd->execute([$productName, $description, $purchasePrice, $salePrice, $quantity, $lowStockAlert, $supplierId]);
        $productId = $pdo->lastInsertId();
    }

    $insertPurchase = $pdo->prepare("
        INSERT INTO purchases (product_id, supplier_id, quantity, purchase_price, total_amount, payment_type, paid_amount, due_amount)
        VALUES (?, ?, ?, ?, ?, ?, ?, ?)
    ");
    $insertPurchase->execute([$productId, $supplierId, $quantity, $purchasePrice, $totalAmount, $paymentType, $paidAmount, $dueAmount]);

    if ($paidAmount > 0) {
        $newCash = $currentCash - $paidAmount;
        $pdo->prepare("UPDATE settings SET cash_balance = ?")->execute([$newCash]);
    } else {
        $newCash = $currentCash;
    }

    if ($dueAmount > 0 && $supplierId) {
        $pdo->prepare("UPDATE suppliers SET due = due + ? WHERE id = ?")->execute([$dueAmount, $supplierId]);
    }

    $pdo->commit();

    echo json_encode([
        "status" => "success",
        "message" => "Purchase saved successfully",
        "cash_balance" => $newCash
    ]);
} catch (Exception $e) {
    $pdo->rollBack();
    echo json_encode(["status" => "error", "message" => $e->getMessage()]);
}