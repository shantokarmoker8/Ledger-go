<?php
declare(strict_types=1);

require_once __DIR__ . '/../../includes/functions.php';
requireAuth();

$input = getJsonInput();
verifyCsrfToken($input['csrf_token'] ?? null);

$name    = clean($input['name'] ?? '');
$phone   = clean($input['phone'] ?? '');
$address = clean($input['address'] ?? '');
$openingDue = (float) ($input['opening_due'] ?? 0);

if ($name === '') {
    jsonResponse(false, 'Customer name is required.');
}
if ($phone !== '' && !preg_match('/^[0-9+\-\s]{7,20}$/', $phone)) {
    jsonResponse(false, 'Invalid phone number format.');
}

try {
    $pdo = getDB();
    $pdo->beginTransaction();

    $stmt = $pdo->prepare('
        INSERT INTO customers (name, phone, address, total_due, status)
        VALUES (:name, :phone, :address, :due, "active")
    ');
    $stmt->execute([
        'name'    => $name,
        'phone'   => $phone,
        'address' => $address,
        'due'     => $openingDue,
    ]);

    $pdo->commit();
    jsonResponse(true, 'Customer added successfully.', ['id' => (int) $pdo->lastInsertId()]);

} catch (PDOException $e) {
    $pdo->rollBack();
    jsonResponse(false, 'Failed to add customer.');
}