<?php
// backend/auth.php
declare(strict_types=1);

require_once __DIR__ . '/db.php';
session_start();

/**
 * Register new user.
 */
function registerUser(string $username, string $email, string $password): void {
    $pdo = getPDO();

    // Check username
    $stmt = $pdo->prepare("SELECT COUNT(*) AS cnt FROM users WHERE username = ?");
    $stmt->execute([$username]);
    if ((int)$stmt->fetch()['cnt'] > 0) {
        jsonResponse(['error' => 'Username already exists'], 400);
    }

    // Check email
    $stmt = $pdo->prepare("SELECT COUNT(*) AS cnt FROM users WHERE email = ?");
    $stmt->execute([$email]);
    if ((int)$stmt->fetch()['cnt'] > 0) {
        jsonResponse(['error' => 'Email already registered'], 400);
    }

    $hash = password_hash($password, PASSWORD_DEFAULT);

    $stmt = $pdo->prepare("
        INSERT INTO users (username, email, password_hash)
        VALUES (?, ?, ?)
    ");
    $stmt->execute([$username, $email, $hash]);

    jsonResponse(['success' => true]);
}

/**
 * Login either by username OR email (whichever user types).
 */
function loginUser(string $identifier, string $password): void {
    $pdo = getPDO();

    $stmt = $pdo->prepare("
        SELECT id, username, email, password_hash
        FROM users
        WHERE username = ? OR email = ?
        LIMIT 1
    ");
    $stmt->execute([$identifier, $identifier]);
    $user = $stmt->fetch();

    if (!$user || !password_verify($password, $user['password_hash'])) {
        jsonResponse(['error' => 'Invalid credentials'], 401);
    }

    $_SESSION['user_id']  = (int)$user['id'];
    $_SESSION['username'] = $user['username'];

    jsonResponse([
        'success' => true,
        'user'    => [
            'id'       => (int)$user['id'],
            'username' => $user['username'],
            'email'    => $user['email'],
        ]
    ]);
}

function requireAuth(): int {
    if (!isset($_SESSION['user_id'])) {
        jsonResponse(['error' => 'Unauthorized'], 401);
    }
    return (int)$_SESSION['user_id'];
}
