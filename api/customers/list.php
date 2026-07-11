<?php
declare(strict_types=1);

require_once __DIR__ . '/../../includes/functions.php';
requireAuth();

$pdo = getDB();

$search = trim($_GET['search'] ?? '');
$page   = max(1, (int) ($_GET['page'] ?? 1));
$limit  = 15;
$offset = ($page - 1) * $limit;

try {
    $where = '';
    $params = [];
    if ($search !== '') {
        $where = 'WHERE name LIKE :search OR phone LIKE :search';
        $params['search'] = "%{$search}%";
    }

    $countStmt = $pdo->prepare("SELECT COUNT(*) AS total FROM customers {$where}");
    $countStmt->execute($params);
    $total = (int) $countStmt->fetch()['total'];

    $stmt = $pdo->prepare("
        SELECT id, name, phone, address, total_due, status, created_at
        FROM customers
        {$where}
        ORDER BY id DESC
        LIMIT :limit OFFSET :offset
    ");
    foreach ($params as $key => $val) {
        $stmt->bindValue(':' . $key, $val, PDO::PARAM_STR);
    }
    $stmt->bindValue(':limit', $limit, PDO::PARAM_INT);
    $stmt->bindValue(':offset', $offset, PDO::PARAM_INT);
    $stmt->execute();

    jsonResponse(true, '', [
        'customers'   => $stmt->fetchAll(),
        'total'       => $total,
        'page'        => $page,
        'total_pages' => (int) ceil($total / $limit),
    ]);

} catch (PDOException $e) {
    jsonResponse(false, 'Failed to load customers.');
}