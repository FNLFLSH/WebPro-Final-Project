<?php
// api/get-user-level.php
declare(strict_types=1);

require_once __DIR__ . '/../backend/auth.php';
require_once __DIR__ . '/../backend/db.php';

if ($_SERVER['REQUEST_METHOD'] !== 'GET') {
    jsonResponse(['error' => 'Method not allowed'], 405);
}

$userId = requireAuth();
$mysqli = getMySQLi();

// Get user's current level from preferences
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

// If no preferences exist, create them with level 1
if (!$prefs) {
    $stmt = $mysqli->prepare("
        INSERT INTO user_preferences (user_id, current_level) 
        VALUES (?, 1)
        ON DUPLICATE KEY UPDATE current_level = 1
    ");
    if (!$stmt) {
        jsonResponse(['error' => 'Database error'], 500);
    }
    $stmt->bind_param("i", $userId);
    $stmt->execute();
    $stmt->close();
    
    $currentLevel = 1;
} else {
    $currentLevel = (int)($prefs['current_level'] ?? 1);
}

jsonResponse([
    'success' => true,
    'level' => $currentLevel
]);

