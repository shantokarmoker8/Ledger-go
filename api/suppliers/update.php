<?php
declare(strict_types=1);

require_once __DIR__ . '/../../includes/functions.php';
requireAuth();

$input = getJsonInput();
verifyCsrfToken($input['csrf_token'] ?? null);

$id      = (int) ($input['id'] ?? 0);
$name    = clean($input['name'] ?? '');
$phone   = clean($input['phone'] ?? '');
$address = clean($input['address'] ?? '');
$status  = in_array($input['status'] ?? '', ['active', 'inactive'], true) ? $input['status'] : 'active';

if ($id <= 0 || $name === '') {
    jsonResponse(false, 'Invalid supplier data.');
}

try {
    $pdo = getDB();
    $stmt = $pdo->prepare('
        UPDATE suppliers
        SET name = :name, phone = :phone, address = :address, status = :status
        WHERE id = :id
    ');
    $stmt->execute([
        'name'    => $name,
        'phone'   => $phone,
        'address' => $address,
        'status'  => $status,
        'id'      => $id,
    ]);

    jsonResponse(true, 'Supplier updated successfully.');

} catch (PDOException $e) {
    jsonResponse(false, 'Failed to update supplier.');
}