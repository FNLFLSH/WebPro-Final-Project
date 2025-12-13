<?php
// api/get-unlocked-levels.php
declare(strict_types=1);

require_once __DIR__ . '/../backend/auth.php';
require_once __DIR__ . '/../backend/db.php';

if ($_SERVER['REQUEST_METHOD'] !== 'GET') {
    jsonResponse(['error' => 'Method not allowed'], 405);
}

$userId = requireAuth();
$mysqli = getMySQLi();

// Get user's current level (highest level they've reached)
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

// Get all levels user has completed (by checking game_sessions)
$stmt = $mysqli->prepare("
    SELECT DISTINCT puzzle_size
    FROM game_sessions
    WHERE user_id = ? AND completed = 1
");
if (!$stmt) {
    jsonResponse(['error' => 'Database error'], 500);
}

$stmt->bind_param("i", $userId);
$stmt->execute();
$result = $stmt->get_result();
$completedSizes = [];
while ($row = $result->fetch_assoc()) {
    $completedSizes[] = (int)$row['puzzle_size'];
}
$stmt->close();

// Map completed sizes to completed levels
$sizeToLevel = [3 => 1, 4 => 2, 5 => 3, 6 => 4, 7 => 5, 8 => 6, 9 => 7, 10 => 8];
$completedLevels = [];
foreach ($completedSizes as $size) {
    if (isset($sizeToLevel[$size])) {
        $completedLevels[] = $sizeToLevel[$size];
    }
}

// Map grid sizes to levels
$sizeToLevel = [3 => 1, 4 => 2, 5 => 3, 6 => 4, 7 => 5, 8 => 6, 9 => 7, 10 => 8];
$unlockedLevels = [1]; // Level 1 is always unlocked

// Add levels based on completed sizes
foreach ($completedSizes as $size) {
    if (isset($sizeToLevel[$size])) {
        $level = $sizeToLevel[$size];
        if (!in_array($level, $unlockedLevels)) {
            $unlockedLevels[] = $level;
        }
    }
}

// Also unlock all levels up to current level
for ($i = 1; $i <= $currentLevel; $i++) {
    if (!in_array($i, $unlockedLevels)) {
        $unlockedLevels[] = $i;
    }
}

// Also unlock level 2 if level 1 was completed (even if current_level is still 1)
// This handles the case where user completes level 1 from the levels page
$stmt = $mysqli->prepare("
    SELECT COUNT(*) as count
    FROM game_sessions
    WHERE user_id = ? AND completed = 1 AND puzzle_size = 3
");
if ($stmt) {
    $stmt->bind_param("i", $userId);
    $stmt->execute();
    $result = $stmt->get_result();
    $row = $result->fetch_assoc();
    $stmt->close();
    
    if ($row && $row['count'] > 0 && !in_array(2, $unlockedLevels)) {
        $unlockedLevels[] = 2;
    }
}

sort($unlockedLevels);

jsonResponse([
    'success' => true,
    'currentLevel' => $currentLevel,
    'unlockedLevels' => $unlockedLevels,
    'completedLevels' => $completedLevels, // Levels the user has actually completed
    'maxLevel' => 8
]);



