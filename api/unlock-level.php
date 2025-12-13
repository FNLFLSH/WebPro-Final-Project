<?php
// api/unlock-level.php
declare(strict_types=1);

require_once __DIR__ . '/../backend/auth.php';
require_once __DIR__ . '/../backend/db.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    jsonResponse(['error' => 'Method not allowed'], 405);
}

$userId = requireAuth();
$mysqli = getMySQLi();

$input = json_decode(file_get_contents('php://input'), true);
$level = (int)($input['level'] ?? 0);

if ($level < 1 || $level > 8) {
    jsonResponse(['error' => 'Invalid level'], 400);
}

// Get current level
$stmt = $mysqli->prepare("
    SELECT current_level 
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

$currentLevel = (int)($prefs['current_level'] ?? 1);

// Update current_level if the new level is higher
if ($level > $currentLevel) {
    $stmt = $mysqli->prepare("
        INSERT INTO user_preferences (user_id, current_level) 
        VALUES (?, ?)
        ON DUPLICATE KEY UPDATE current_level = ?
    ");
    if (!$stmt) {
        jsonResponse(['error' => 'Database error'], 500);
    }
    
    $stmt->bind_param("iii", $userId, $level, $level);
    $stmt->execute();
    $stmt->close();
}

jsonResponse([
    'success' => true,
    'level' => $level,
    'unlocked' => true,
    'message' => "Level $level unlocked"
]);

