<?php
require_once __DIR__ . '/../../includes/auth_check.php';
require_once __DIR__ . '/../../config/db.php';
header('Content-Type: application/json');

$input = json_decode(file_get_contents('php://input'), true);

// Common fields
$productId      = isset($input['product_id']) && $input['product_id'] !== '' ? (int) $input['product_id'] : null;
$productName    = trim($input['product_name'] ?? '');
$description    = trim($input['description'] ?? '');
$purchasePrice  = (float) ($input['purchase_price'] ?? 0);
$salePrice      = (float) ($input['sale_price'] ?? 0);
$quantity       = (int) ($input['quantity'] ?? 0);
$lowStockAlert  = (int) ($input['low_stock_alert'] ?? 5);
$supplierId     = isset($input['supplier_id']) && $input['supplier_id'] !== '' ? (int) $input['supplier_id'] : null;
$paymentType    = $input['payment_type'] ?? 'due'; // cash | due
$paidAmount     = (float) ($input['paid_amount'] ?? 0);

// ============ VALIDATION ============
if ($quantity <= 0) {
    echo json_encode(["status" => "error", "message" => "Quantity must be greater than 0"]);
    exit;
}
if ($purchasePrice <= 0) {
    echo json_encode(["status" => "error", "message" => "Purchase Price must be greater than 0"]);
    exit;
}
if (!$productId && $productName === '') {
    echo json_encode(["status" => "error", "message" => "Product Name is required"]);
    exit;
}
if (!in_array($paymentType, ['cash', 'due'])) {
    echo json_encode(["status" => "error", "message" => "Invalid payment type"]);
    exit;
}
if ($paymentType === 'due') {
    $paidAmount = 0; // Force zero paid for due purchase
    if (!$supplierId) {
        echo json_encode(["status" => "error", "message" => "Supplier is required for Due Purchase"]);
        exit;
    }
}

$totalAmount = $purchasePrice * $quantity;

if ($paidAmount > $totalAmount) {
    echo json_encode(["status" => "error", "message" => "Paid Amount cannot exceed Total Amount"]);
    exit;
}

try {
    $pdo->beginTransaction();

    // Lock settings row to safely check cash balance
    $settings = $pdo->query("SELECT * FROM settings LIMIT 1 FOR UPDATE")->fetch();
    $currentCash = (float) $settings['cash_balance'];

    if ($paymentType === 'cash' && $paidAmount > $currentCash) {
        $pdo->rollBack();
        echo json_encode(["status" => "error", "message" => "Insufficient Cash Balance"]);
        exit;
    }

    // ============ PRODUCT: Existing (Restock) or New ============
    if ($productId) {
        $prodStmt = $pdo->prepare("SELECT * FROM products WHERE id = ? FOR UPDATE");
        $prodStmt->execute([$productId]);
        $product = $prodStmt->fetch();

        if (!$product) {
            $pdo->rollBack();
            echo json_encode(["status" => "error", "message" => "Product not found"]);
            exit;
        }

        // Restock: increase stock, update prices to latest entered values
        $updateStmt = $pdo->prepare("UPDATE products SET stock = stock + ?, purchase_price = ?, sale_price = ?, low_stock_alert = ? WHERE id = ?");
        $updateStmt->execute([$quantity, $purchasePrice, $salePrice > 0 ? $salePrice : $product['sale_price'], $lowStockAlert, $productId]);
    } else {
        // New Product
        if ($salePrice <= 0) {
            $pdo->rollBack();
            echo json_encode(["status" => "error", "message" => "Sale Price is required for a new product"]);
            exit;
        }
        $insertProd = $pdo->prepare("INSERT INTO products (name, description, purchase_price, sale_price, stock, low_stock_alert, supplier_id) VALUES (?, ?, ?, ?, ?, ?, ?)");
        $insertProd->execute([$productName, $description, $purchasePrice, $salePrice, $quantity, $lowStockAlert, $supplierId]);
        $productId = $pdo->lastInsertId();
    }

    // ============ DUE AMOUNT ============
    $dueAmount = $totalAmount - $paidAmount;

    // ============ INSERT PURCHASE RECORD ============
    $insertPurchase = $pdo->prepare("
        INSERT INTO purchases (product_id, supplier_id, quantity, purchase_price, total_amount, payment_type, paid_amount, due_amount)
        VALUES (?, ?, ?, ?, ?, ?, ?, ?)
    ");
    $insertPurchase->execute([$productId, $supplierId, $quantity, $purchasePrice, $totalAmount, $paymentType, $paidAmount, $dueAmount]);

    // ============ UPDATE CASH BALANCE ============
    if ($paidAmount > 0) {
        $newCash = $currentCash - $paidAmount;
        $pdo->prepare("UPDATE settings SET cash_balance = ?")->execute([$newCash]);
    } else {
        $newCash = $currentCash;
    }

    // ============ UPDATE SUPPLIER DUE ============
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