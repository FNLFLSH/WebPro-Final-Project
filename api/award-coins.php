<?php
// api/award-coins.php
declare(strict_types=1);

require_once __DIR__ . '/../backend/auth.php';
require_once __DIR__ . '/../backend/db.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    jsonResponse(['error' => 'Method not allowed'], 405);
}

$userId = requireAuth();
$mysqli = getMySQLi();

$input = json_decode(file_get_contents('php://input'), true);
$coinsToAward = (int)($input['coins'] ?? 50); // Default 50 coins per level

if ($coinsToAward <= 0) {
    jsonResponse(['error' => 'Invalid coin amount'], 400);
}

// Get current coins
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

$currentCoins = (int)($prefs['coins'] ?? 0);
$newCoins = $currentCoins + $coinsToAward;

// Update coins
$stmt = $mysqli->prepare("
    INSERT INTO user_preferences (user_id, coins) 
    VALUES (?, ?)
    ON DUPLICATE KEY UPDATE coins = ?
");
if (!$stmt) {
    jsonResponse(['error' => 'Database error'], 500);
}

$stmt->bind_param("iii", $userId, $newCoins, $newCoins);
$stmt->execute();
$stmt->close();

jsonResponse([
    'success' => true,
    'coinsAwarded' => $coinsToAward,
    'totalCoins' => $newCoins,
    'message' => "Awarded {$coinsToAward} coins!"
]);

