<?php
// api/use-powerup.php
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

if (!in_array($powerupType, ['freeze_timer', 'smart_shuffle'])) {
    jsonResponse(['error' => 'Invalid power-up type'], 400);
}

// Get first available power-up of this type
$stmt = $mysqli->prepare("
    SELECT id, quantity 
    FROM user_powerups
    WHERE user_id = ? AND powerup_type = ? AND quantity > 0
    ORDER BY purchased_at ASC
    LIMIT 1
");
if (!$stmt) {
    jsonResponse(['error' => 'Database error'], 500);
}

$stmt->bind_param("is", $userId, $powerupType);
$stmt->execute();
$result = $stmt->get_result();
$powerup = $result->fetch_assoc();
$stmt->close();

if (!$powerup) {
    jsonResponse(['error' => 'No power-up available'], 404);
}

$powerupId = (int)$powerup['id'];
$quantity = (int)$powerup['quantity'];

// Decrement quantity
if ($quantity > 1) {
    $newQuantity = $quantity - 1;
    $stmt = $mysqli->prepare("
        UPDATE user_powerups 
        SET quantity = ? 
        WHERE id = ?
    ");
    if (!$stmt) {
        jsonResponse(['error' => 'Database error'], 500);
    }
    $stmt->bind_param("ii", $newQuantity, $powerupId);
    $stmt->execute();
    $stmt->close();
} else {
    // Remove if quantity reaches 0
    $stmt = $mysqli->prepare("
        DELETE FROM user_powerups 
        WHERE id = ?
    ");
    if (!$stmt) {
        jsonResponse(['error' => 'Database error'], 500);
    }
    $stmt->bind_param("i", $powerupId);
    $stmt->execute();
    $stmt->close();
}

jsonResponse([
    'success' => true,
    'powerup_type' => $powerupType,
    'remaining' => $quantity - 1
]);

