<?php
declare(strict_types=1);

require_once __DIR__ . '/../../includes/functions.php';
requireAuth();

$pdo = getDB();

$search     = trim($_GET['search'] ?? '');
$categoryId = !empty($_GET['category_id']) ? (int) $_GET['category_id'] : null;
$lowStock   = !empty($_GET['low_stock']);
$page       = max(1, (int) ($_GET['page'] ?? 1));
$limit      = 15;
$offset     = ($page - 1) * $limit;

try {
    $conditions = [];
    $params = [];

    if ($search !== '') {
        $conditions[] = 'p.name LIKE :search';
        $params['search'] = "%{$search}%";
    }
    if ($categoryId) {
        $conditions[] = 'p.category_id = :category_id';
        $params['category_id'] = $categoryId;
    }
    if ($lowStock) {
        $conditions[] = 'p.stock_qty <= p.alert_qty';
    }

    $whereSql = $conditions ? 'WHERE ' . implode(' AND ', $conditions) : '';

    $countStmt = $pdo->prepare("SELECT COUNT(*) AS total FROM products p {$whereSql}");
    $countStmt->execute($params);
    $total = (int) $countStmt->fetch()['total'];

    $stmt = $pdo->prepare("
        SELECT p.id, p.name, p.buy_price, p.sell_price, p.stock_qty, p.alert_qty, p.unit, p.status,
               c.id AS category_id, c.name AS category_name
        FROM products p
        LEFT JOIN categories c ON c.id = p.category_id
        {$whereSql}
        ORDER BY p.id DESC
        LIMIT :limit OFFSET :offset
    ");
    foreach ($params as $key => $val) {
        $stmt->bindValue(':' . $key, $val);
    }
    $stmt->bindValue(':limit', $limit, PDO::PARAM_INT);
    $stmt->bindValue(':offset', $offset, PDO::PARAM_INT);
    $stmt->execute();

    jsonResponse(true, '', [
        'products'    => $stmt->fetchAll(),
        'total'       => $total,
        'page'        => $page,
        'total_pages' => (int) ceil($total / $limit),
    ]);

} catch (PDOException $e) {
    jsonResponse(false, 'Failed to load products.');
}