<?php
require_once __DIR__ . '/../../includes/auth_check.php';
require_once __DIR__ . '/../../config/db.php';
header('Content-Type: application/json');

$input = json_decode(file_get_contents('php://input'), true);
$id = (int) ($input['id'] ?? 0);
$returnQty = (int) ($input['return_qty'] ?? 0);

if ($id <= 0) {
    echo json_encode(["status" => "error", "message" => "Invalid Sale ID"]);
    exit;
}
if ($returnQty <= 0) {
    echo json_encode(["status" => "error", "message" => "Return quantity must be greater than 0"]);
    exit;
}

try {
    $pdo->beginTransaction();

    $stmt = $pdo->prepare("SELECT * FROM sales WHERE id = ? FOR UPDATE");
    $stmt->execute([$id]);
    $sale = $stmt->fetch();

    if (!$sale) {
        $pdo->rollBack();
        echo json_encode(["status" => "error", "message" => "Sale record not found"]);
        exit;
    }

    if ($returnQty > $sale['quantity']) {
        $pdo->rollBack();
        echo json_encode(["status" => "error", "message" => "Return quantity cannot exceed sold quantity (" . $sale['quantity'] . ")"]);
        exit;
    }

    $unitPrice = (float) $sale['sale_price'];
    $returnAmount = $unitPrice * $returnQty;

    // প্রথমে Due থেকে বাদ, বাকিটা Cash Refund হিসেবে গণ্য
    $refundFromDue = min($returnAmount, (float) $sale['due_amount']);
    $refundFromCash = $returnAmount - $refundFromDue;

    // Cash Balance যথেষ্ট আছে কিনা চেক করো (দোকান থেকে Customer-কে টাকা ফেরত দেওয়া হবে)
    $settings = $pdo->query("SELECT cash_balance FROM settings LIMIT 1 FOR UPDATE")->fetch();
    $currentCash = (float) $settings['cash_balance'];

    if ($refundFromCash > $currentCash) {
        $pdo->rollBack();
        echo json_encode(["status" => "error", "message" => "Insufficient Cash Balance to process this refund"]);
        exit;
    }

    // Stock ফেরত যোগ
    $pdo->prepare("UPDATE products SET stock = stock + ? WHERE id = ?")->execute([$returnQty, $sale['product_id']]);

    // Cash Balance কমবে (Cash অংশ Refund)
    if ($refundFromCash > 0) {
        $newCash = $currentCash - $refundFromCash;
        $pdo->prepare("UPDATE settings SET cash_balance = ?")->execute([$newCash]);
    } else {
        $newCash = $currentCash;
    }

    // Customer Due কমবে (Due অংশ Refund)
    if ($refundFromDue > 0 && $sale['customer_id']) {
        $pdo->prepare("UPDATE customers SET due = GREATEST(due - ?, 0) WHERE id = ?")->execute([$refundFromDue, $sale['customer_id']]);
    }

    // Sale Record Update
    $newQuantity = $sale['quantity'] - $returnQty;
    $newTotal = (float) $sale['total_amount'] - $returnAmount;
    $newDue = (float) $sale['due_amount'] - $refundFromDue;
    $newPaid = (float) $sale['paid_amount'] - $refundFromCash;

    if ($newQuantity <= 0) {
        // পুরোটাই Return হলে Record মুছে ফেলা
        $pdo->prepare("DELETE FROM sales WHERE id = ?")->execute([$id]);
    } else {
        $pdo->prepare("UPDATE sales SET quantity = ?, total_amount = ?, due_amount = ?, paid_amount = ? WHERE id = ?")
            ->execute([$newQuantity, $newTotal, $newDue, $newPaid, $id]);
    }

    $pdo->commit();

    echo json_encode([
        "status" => "success",
        "message" => "Return processed successfully",
        "cash_balance" => $newCash
    ]);
} catch (Exception $e) {
    $pdo->rollBack();
    echo json_encode(["status" => "error", "message" => $e->getMessage()]);
}