<?php
// api/get-user-coins.php
declare(strict_types=1);

require_once __DIR__ . '/../backend/auth.php';
require_once __DIR__ . '/../backend/db.php';

if ($_SERVER['REQUEST_METHOD'] !== 'GET') {
    jsonResponse(['error' => 'Method not allowed'], 405);
}

$userId = requireAuth();
$mysqli = getMySQLi();

// Get user's coin balance
$stmt = $mysqli->prepare("
    SELECT coins 
    FROM user_preferences 
    WHERE user_id = ?
");
if (!$stmt) {
    jsonResponse(['error' => 'Database error'], 500);
}

$stmt->bind_param("i", $userId);
$stmt->execute();
$result = $stmt->get_result();
$prefs = $result->fetch_assoc();
$stmt->close();

$coins = (int)($prefs['coins'] ?? 0);

// If no preferences exist, create them with 0 coins
if (!$prefs) {
    $stmt = $mysqli->prepare("
        INSERT INTO user_preferences (user_id, coins) 
        VALUES (?, 0)
        ON DUPLICATE KEY UPDATE coins = 0
    ");
    if ($stmt) {
        $stmt->bind_param("i", $userId);
        $stmt->execute();
        $stmt->close();
    }
}

jsonResponse([
    'success' => true,
    'coins' => $coins
]);

