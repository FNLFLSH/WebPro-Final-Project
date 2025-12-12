<?php
// backend/auth.php
declare(strict_types=1);

require_once __DIR__ . '/session.php';
require_once __DIR__ . '/db.php';

/**
 * Register new user.
 */
function registerUser(string $username, string $email, string $password): void {
    $mysqli = getMySQLi();

    // Check username
    $stmt = $mysqli->prepare("SELECT COUNT(*) AS cnt FROM users WHERE username = ?");
    if (!$stmt) {
        jsonResponse(['error' => 'Database error'], 500);
    }
    $stmt->bind_param("s", $username);
    $stmt->execute();
    $result = $stmt->get_result();
    $row = $result->fetch_assoc();
    $stmt->close();
    
    if ((int)$row['cnt'] > 0) {
        jsonResponse(['error' => 'Username already exists'], 400);
    }

    // Check email
    $stmt = $mysqli->prepare("SELECT COUNT(*) AS cnt FROM users WHERE email = ?");
    if (!$stmt) {
        jsonResponse(['error' => 'Database error'], 500);
    }
    $stmt->bind_param("s", $email);
    $stmt->execute();
    $result = $stmt->get_result();
    $row = $result->fetch_assoc();
    $stmt->close();
    
    if ((int)$row['cnt'] > 0) {
        jsonResponse(['error' => 'Email already registered'], 400);
    }

    $hash = password_hash($password, PASSWORD_DEFAULT);

    $stmt = $mysqli->prepare("INSERT INTO users (username, email, password_hash) VALUES (?, ?, ?)");
    if (!$stmt) {
        jsonResponse(['error' => 'Database error'], 500);
    }
    $stmt->bind_param("sss", $username, $email, $hash);
    $stmt->execute();
    $stmt->close();

    jsonResponse(['success' => true]);
}

/**
 * Login either by username OR email (whichever user types).
 * Caches user data after successful login.
 * Session will persist for 30 days.
 */
function loginUser(string $identifier, string $password): void {
    $mysqli = getMySQLi();

    $stmt = $mysqli->prepare("
        SELECT id, username, email, password_hash
        FROM users
        WHERE username = ? OR email = ?
        LIMIT 1
    ");
    if (!$stmt) {
        jsonResponse(['error' => 'Database error'], 500);
    }
    
    $stmt->bind_param("ss", $identifier, $identifier);
    $stmt->execute();
    $result = $stmt->get_result();
    $user = $result->fetch_assoc();
    $stmt->close();

    if (!$user || !password_verify($password, $user['password_hash'])) {
        jsonResponse(['error' => 'Invalid credentials'], 401);
    }

    $userId = (int)$user['id'];
    
    // Store in session (will persist for 30 days)
    $_SESSION['user_id']  = $userId;
    $_SESSION['username'] = $user['username'];
    $_SESSION['login_time'] = time(); // Track when user logged in
    
    // Regenerate session ID for security after login
    session_regenerate_id(true);
    
    // Cache user data (will be fetched and cached by getUserData if needed)
    // For now, we can manually cache it since we have it
    global $userCache;
    $userCache[$userId] = [
        'id' => $userId,
        'username' => $user['username'],
        'email' => $user['email'],
        'password_hash' => $user['password_hash']
    ];

    jsonResponse([
        'success' => true,
        'user'    => [
            'id'       => $userId,
            'username' => $user['username'],
            'email'    => $user['email'],
        ]
    ]);
}

function requireAuth(): int {
    if (!isset($_SESSION['user_id'])) {
        jsonResponse(['error' => 'Unauthorized'], 401);
    }
    
    // Optional: Check if session is too old (e.g., > 30 days)
    // You can add this check if you want to enforce re-login after a certain period
    // if (isset($_SESSION['login_time']) && (time() - $_SESSION['login_time']) > 2592000) {
    //     session_destroy();
    //     jsonResponse(['error' => 'Session expired'], 401);
    // }
    
    return (int)$_SESSION['user_id'];
}

/**
 * Get current user data (uses cache)
 */
function getCurrentUser(): ?array {
    if (!isset($_SESSION['user_id'])) {
        return null;
    }
    return getUserData((int)$_SESSION['user_id']);
}
