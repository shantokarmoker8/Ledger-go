<?php
require_once __DIR__ . '/../../includes/auth_check.php';
require_once __DIR__ . '/../../config/db.php';
header('Content-Type: application/json');

$input = json_decode(file_get_contents('php://input'), true);

$productId    = (int) ($input['product_id'] ?? 0);
$customerId   = isset($input['customer_id']) && $input['customer_id'] !== '' ? (int) $input['customer_id'] : null;
$quantity     = (int) ($input['quantity'] ?? 0);
$salePrice    = (float) ($input['sale_price'] ?? 0);
$paymentType  = $input['payment_type'] ?? 'cash'; // cash | due
$paidAmount   = (float) ($input['paid_amount'] ?? 0);

// ============ VALIDATION ============
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
if (!in_array($paymentType, ['cash', 'due'])) {
    echo json_encode(["status" => "error", "message" => "Invalid payment type"]);
    exit;
}
if ($paymentType === 'due' && !$customerId) {
    echo json_encode(["status" => "error", "message" => "Customer is required for Due Sale"]);
    exit;
}

$totalAmount = $salePrice * $quantity;

// For cash sale, paid amount = total (full cash sale). Allow partial? Spec says cash sale increases cash balance fully.
// We'll treat: cash sale = fully paid (paid_amount = total). Due sale = fully due (paid_amount = 0), unless customer partially pays now.
if ($paymentType === 'cash') {
    $paidAmount = $totalAmount;
} else {
    if ($paidAmount > $totalAmount) {
        echo json_encode(["status" => "error", "message" => "Paid Amount cannot exceed Total Amount"]);
        exit;
    }
}

try {
    $pdo->beginTransaction();

    // Lock product row to check stock safely
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

    // Reduce Stock
    $pdo->prepare("UPDATE products SET stock = stock - ? WHERE id = ?")->execute([$quantity, $productId]);

    $dueAmount = $totalAmount - $paidAmount;

    // Insert Sale Record
    $insertSale = $pdo->prepare("
        INSERT INTO sales (product_id, customer_id, quantity, sale_price, total_amount, payment_type, paid_amount, due_amount)
        VALUES (?, ?, ?, ?, ?, ?, ?, ?)
    ");
    $insertSale->execute([$productId, $customerId, $quantity, $salePrice, $totalAmount, $paymentType, $paidAmount, $dueAmount]);

    // Update Cash Balance (increase by paid amount)
    $settings = $pdo->query("SELECT cash_balance FROM settings LIMIT 1 FOR UPDATE")->fetch();
    $newCash = (float) $settings['cash_balance'] + $paidAmount;
    $pdo->prepare("UPDATE settings SET cash_balance = ?")->execute([$newCash]);

    // Update Customer Due
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