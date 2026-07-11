<?php
declare(strict_types=1);

require_once __DIR__ . '/../config/db.php';

// Start session securely
function startSecureSession(): void
{
    if (session_status() === PHP_SESSION_NONE) {
        session_set_cookie_params([
            'lifetime' => 0,
            'path'     => '/',
            'httponly' => true,
            'samesite' => 'Strict',
        ]);
        session_start();
    }
}

// Check if user is logged in — used at the top of every protected api/page file
function requireAuth(): void
{
    startSecureSession();
    if (empty($_SESSION['user_id'])) {
        http_response_code(401);
        header('Content-Type: application/json');
        die(json_encode(['success' => false, 'message' => 'Unauthorized. Please login again.']));
    }
}

// CSRF Token Generation
function generateCsrfToken(): string
{
    startSecureSession();
    if (empty($_SESSION['csrf_token'])) {
        $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
    }
    return $_SESSION['csrf_token'];
}

// CSRF Token Validation
function verifyCsrfToken(?string $token): void
{
    startSecureSession();
    if (empty($token) || empty($_SESSION['csrf_token']) || !hash_equals($_SESSION['csrf_token'], $token)) {
        http_response_code(403);
        header('Content-Type: application/json');
        die(json_encode(['success' => false, 'message' => 'Invalid request token.']));
    }
}

// Sanitize input (XSS Protection)
function clean(?string $value): string
{
    return htmlspecialchars(trim($value ?? ''), ENT_QUOTES, 'UTF-8');
}

// Get JSON body from request
function getJsonInput(): array
{
    $data = json_decode(file_get_contents('php://input'), true);
    return is_array($data) ? $data : [];
}

// Standard JSON response helper
function jsonResponse(bool $success, string $message = '', array $data = []): void
{
    header('Content-Type: application/json');
    echo json_encode(array_merge(['success' => $success, 'message' => $message], $data));
    exit;
}