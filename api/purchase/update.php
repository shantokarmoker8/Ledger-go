<?php
require_once __DIR__ . '/../../includes/auth_check.php';
require_once __DIR__ . '/../../config/db.php';
header('Content-Type: application/json');

$input = json_decode(file_get_contents('php://input'), true);
$id            = (int) ($input['id'] ?? 0);
$description   = trim($input['description'] ?? '');
$purchasePrice = (float) ($input['purchase_price'] ?? 0);
$salePrice     = (float) ($input['sale_price'] ?? 0);
$quantity      = (int) ($input['quantity'] ?? 0);
$supplierId    = isset($input['supplier_id']) && $input['supplier_id'] !== '' ? (int) $input['supplier_id'] : null;

if ($id <= 0) { echo json_encode(["status" => "error", "message" => "Invalid Purchase ID"]); exit; }
if ($quantity <= 0) { echo json_encode(["status" => "error", "message" => "Quantity must be greater than 0"]); exit; }
if ($purchasePrice <= 0) { echo json_encode(["status" => "error", "message" => "Purchase Price must be greater than 0"]); exit; }
if ($salePrice <= 0) { echo json_encode(["status" => "error", "message" => "Sale Price must be greater than 0"]); exit; }

try {
    $pdo->beginTransaction();

    $stmt = $pdo->prepare("SELECT * FROM purchases WHERE id = ? FOR UPDATE");
    $stmt->execute([$id]);
    $purchase = $stmt->fetch();

    if (!$purchase) {
        $pdo->rollBack();
        echo json_encode(["status" => "error", "message" => "Purchase not found"]);
        exit;
    }

    if ($purchase['payment_type'] === 'due' && !$supplierId) {
        $pdo->rollBack();
        echo json_encode(["status" => "error", "message" => "Supplier is required for a Due purchase"]);
        exit;
    }

    $product = $pdo->prepare("SELECT * FROM products WHERE id = ? FOR UPDATE");
    $product->execute([$purchase['product_id']]);
    $product = $product->fetch();

    if (!$product) {
        $pdo->rollBack();
        echo json_encode(["status" => "error", "message" => "Product not found"]);
        exit;
    }

    // পুরনো Quantity বাদ দিয়ে নতুন Quantity বসানো
    $stockAfterReverse = $product['stock'] - $purchase['quantity'];
    if ($stockAfterReverse < 0) {
        $pdo->rollBack();
        echo json_encode(["status" => "error", "message" => "Cannot edit: some of this stock has already been sold"]);
        exit;
    }
    $newStock = $stockAfterReverse + $quantity;

    $pdo->prepare("UPDATE products SET stock = ?, description = ?, purchase_price = ?, sale_price = ?, supplier_id = ? WHERE id = ?")
        ->execute([$newStock, $description, $purchasePrice, $salePrice, $supplierId, $product['id']]);

    $newTotal = $purchasePrice * $quantity;
    $paidAmount = (float) $purchase['paid_amount'];

    if ($paidAmount > $newTotal) {
        $pdo->rollBack();
        echo json_encode(["status" => "error", "message" => "Already Paid Amount is more than the new Total. Increase price/quantity."]);
        exit;
    }
    $newDue = $newTotal - $paidAmount;

    // Supplier Due Reverse করে নতুন করে বসানো
    if ($purchase['due_amount'] > 0 && $purchase['supplier_id']) {
        $pdo->prepare("UPDATE suppliers SET due = GREATEST(due - ?, 0) WHERE id = ?")->execute([$purchase['due_amount'], $purchase['supplier_id']]);
    }
    if ($newDue > 0 && $supplierId) {
        $pdo->prepare("UPDATE suppliers SET due = due + ? WHERE id = ?")->execute([$newDue, $supplierId]);
    }

    $pdo->prepare("UPDATE purchases SET supplier_id = ?, quantity = ?, purchase_price = ?, total_amount = ?, due_amount = ? WHERE id = ?")
        ->execute([$supplierId, $quantity, $purchasePrice, $newTotal, $newDue, $id]);

    $pdo->commit();

    echo json_encode(["status" => "success", "message" => "Purchase updated successfully"]);
} catch (Exception $e) {
    $pdo->rollBack();
    echo json_encode(["status" => "error", "message" => $e->getMessage()]);
}