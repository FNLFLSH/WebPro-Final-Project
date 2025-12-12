<?php
// api/save-user-level.php
declare(strict_types=1);

require_once __DIR__ . '/../backend/auth.php';
require_once __DIR__ . '/../backend/db.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    jsonResponse(['error' => 'Method not allowed'], 405);
}

$userId = requireAuth();
$mysqli = getMySQLi();

$input = json_decode(file_get_contents('php://input'), true);
$level = (int)($input['level'] ?? 1);

if ($level < 1) {
    jsonResponse(['error' => 'Invalid level'], 400);
}

// Insert or update user preferences with new level
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

jsonResponse([
    'success' => true,
    'level' => $level,
    'message' => 'Level saved successfully'
]);

