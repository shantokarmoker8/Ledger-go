<?php
declare(strict_types=1);

require_once __DIR__ . '/../../includes/functions.php';
requireAuth();

$method = $_SERVER['REQUEST_METHOD'];
$pdo = getDB();

try {
    if ($method === 'GET') {
        $stmt = $pdo->query('SELECT id, name FROM categories ORDER BY name ASC');
        jsonResponse(true, '', ['categories' => $stmt->fetchAll()]);
    }

    if ($method === 'POST') {
        $input = getJsonInput();
        verifyCsrfToken($input['csrf_token'] ?? null);

        $name = clean($input['name'] ?? '');
        if ($name === '') {
            jsonResponse(false, 'Category name is required.');
        }

        $stmt = $pdo->prepare('INSERT INTO categories (name) VALUES (:name)');
        $stmt->execute(['name' => $name]);

        jsonResponse(true, 'Category added.', ['id' => (int) $pdo->lastInsertId(), 'name' => $name]);
    }

} catch (PDOException $e) {
    jsonResponse(false, 'Category operation failed.');
}