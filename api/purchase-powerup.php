<?php
// api/purchase-powerup.php
declare(strict_types=1);

require_once __DIR__ . '/../backend/auth.php';
require_once __DIR__ . '/../backend/db.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    jsonResponse(['error' => 'Method not allowed'], 405);
}

$userId = requireAuth();
$mysqli = getMySQLi();

$input = json_decode(file_get_contents('php://input'), true);
$powerupType = $input['powerup_type'] ?? '';

// Power-up prices (TESTING MODE - Set to 0 coins for free testing)
// TODO: Update prices before production
$prices = [
    'freeze_timer' => 0,  // Original: 100
    'smart_shuffle' => 0  // Original: 75
];

if (!isset($prices[$powerupType])) {
    jsonResponse(['error' => 'Invalid power-up type'], 400);
}

$price = $prices[$powerupType];

// Get user's current coins
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

// Allow purchase even with 0 coins if price is 0 (testing mode)
if ($price > 0 && $currentCoins < $price) {
    jsonResponse([
        'error' => 'Insufficient coins',
        'required' => $price,
        'current' => $currentCoins
    ], 400);
}

// Deduct coins
$newCoins = $currentCoins - $price;
$stmt = $mysqli->prepare("
    UPDATE user_preferences 
    SET coins = ? 
    WHERE user_id = ?
");
if (!$stmt) {
    jsonResponse(['error' => 'Database error'], 500);
}

$stmt->bind_param("ii", $newCoins, $userId);
$stmt->execute();
$stmt->close();

// Add power-up to inventory
$stmt = $mysqli->prepare("
    INSERT INTO user_powerups (user_id, powerup_type, quantity) 
    VALUES (?, ?, 1)
");
if (!$stmt) {
    jsonResponse(['error' => 'Database error'], 500);
}

$stmt->bind_param("is", $userId, $powerupType);
$stmt->execute();
$stmt->close();

jsonResponse([
    'success' => true,
    'powerup_type' => $powerupType,
    'coins_spent' => $price,
    'remaining_coins' => $newCoins,
    'message' => 'Power-up purchased successfully!'
]);

