<?php
declare(strict_types=1);

require_once __DIR__ . '/../../includes/functions.php';

startSecureSession();
header('Content-Type: application/json');

$input   = getJsonInput();
$idToken = $input['idToken'] ?? '';
$remember = $input['remember'] ?? false;

if (empty($idToken)) {
    jsonResponse(false, 'Missing authentication token.');
}

// Verify ID Token with Firebase REST API (accounts:lookup)
$url = 'https://identitytoolkit.googleapis.com/v1/accounts:lookup?key=' . FIREBASE_API_KEY;

$ch = curl_init($url);
curl_setopt_array($ch, [
    CURLOPT_RETURNTRANSFER => true,
    CURLOPT_POST           => true,
    CURLOPT_HTTPHEADER     => ['Content-Type: application/json'],
    CURLOPT_POSTFIELDS     => json_encode(['idToken' => $idToken]),
    CURLOPT_TIMEOUT        => 10,
]);
$response = curl_exec($ch);
$httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
curl_close($ch);

if ($httpCode !== 200) {
    jsonResponse(false, 'Session verification failed. Please login again.');
}

$result = json_decode($response, true);
$firebaseUser = $result['users'][0] ?? null;

if (!$firebaseUser) {
    jsonResponse(false, 'Invalid authentication token.');
}

$firebaseUid = $firebaseUser['localId'];
$email       = $firebaseUser['email'] ?? '';
$name        = $firebaseUser['displayName'] ?? explode('@', $email)[0];

try {
    $pdo = getDB();

    // Check if user already exists
    $stmt = $pdo->prepare('SELECT id, name, email, role, status FROM users WHERE firebase_uid = :uid LIMIT 1');
    $stmt->execute(['uid' => $firebaseUid]);
    $user = $stmt->fetch();

    if (!$user) {
        // First-time login -> auto register as staff (first user ever = admin)
        $countStmt = $pdo->query('SELECT COUNT(*) as total FROM users');
        $isFirstUser = ((int) $countStmt->fetch()['total']) === 0;

        $insert = $pdo->prepare('
            INSERT INTO users (firebase_uid, name, email, role, status)
            VALUES (:uid, :name, :email, :role, "active")
        ');
        $insert->execute([
            'uid'   => $firebaseUid,
            'name'  => $name,
            'email' => $email,
            'role'  => $isFirstUser ? 'admin' : 'staff',
        ]);

        $userId = (int) $pdo->lastInsertId();
        $role    = $isFirstUser ? 'admin' : 'staff';
    } else {
        if ($user['status'] !== 'active') {
            jsonResponse(false, 'Your account has been disabled. Contact admin.');
        }
        $userId = (int) $user['id'];
        $role   = $user['role'];
        $name   = $user['name'];
    }

    // Create PHP Session
    $_SESSION['user_id']       = $userId;
    $_SESSION['user_name']     = $name;
    $_SESSION['user_email']    = $email;
    $_SESSION['user_role']     = $role;
    $_SESSION['firebase_uid']  = $firebaseUid;

    // Remember Me -> extend session cookie lifetime to 30 days
    if ($remember) {
        setcookie(session_name(), session_id(), time() + (30 * 24 * 60 * 60), '/', '', false, true);
    }

    jsonResponse(true, 'Login successful.', [
        'user' => ['id' => $userId, 'name' => $name, 'email' => $email, 'role' => $role],
    ]);

} catch (PDOException $e) {
    jsonResponse(false, 'Server error during login.');
}