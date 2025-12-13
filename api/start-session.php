<?php
// api/start-session.php
declare(strict_types=1);

require_once __DIR__ . '/../backend/auth.php';
require_once __DIR__ . '/../backend/db.php';
require_once __DIR__ . '/../backend/puzzleLogic.php';
require_once __DIR__ . '/../backend/difficulty.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    jsonResponse(['error' => 'Method not allowed'], 405);
}

$userId = requireAuth();
$mysqli = getMySQLi();

$input      = json_decode(file_get_contents('php://input'), true);
$puzzleSize = (int)($input['gridSize'] ?? $input['puzzleSize'] ?? 4);

// Get user's current level to determine puzzle size if not provided
if (!isset($input['gridSize']) && !isset($input['puzzleSize'])) {
    $stmt = $mysqli->prepare("
        SELECT current_level 
        FROM user_preferences 
        WHERE user_id = ?
    ");
    if ($stmt) {
        $stmt->bind_param("i", $userId);
        $stmt->execute();
        $result = $stmt->get_result();
        $prefs = $result->fetch_assoc();
        $stmt->close();
        
        if ($prefs && isset($prefs['current_level'])) {
            $level = (int)$prefs['current_level'];
            // Map level to grid size: Level 1=3x3, 2=4x4, 3=5x5, 4=6x6, 5=7x7, 6=8x8, 7=9x9, 8=10x10
            $levelToSize = [1 => 3, 2 => 4, 3 => 5, 4 => 6, 5 => 7, 6 => 8, 7 => 9, 8 => 10];
            $puzzleSize = $levelToSize[$level] ?? 4;
        }
    }
}

$allowed = [3, 4, 5, 6, 7, 8, 9, 10];
if (!in_array($puzzleSize, $allowed, true)) {
    $puzzleSize = 4;
}

// --- get user stats for difficulty ---
$stmt = $mysqli->prepare("
    SELECT 
        COUNT(*) AS attempted,
        SUM(CASE WHEN completed = TRUE THEN 1 ELSE 0 END) AS completed,
        AVG(completion_time) AS avg_time,
        AVG(moves) AS avg_moves
    FROM game_sessions
    WHERE user_id = ?
");
if (!$stmt) {
    jsonResponse(['error' => 'Database error'], 500);
}
$stmt->bind_param("i", $userId);
$stmt->execute();
$result = $stmt->get_result();
$stats = $result->fetch_assoc() ?: [
    'attempted' => 0,
    'completed' => 0,
    'avg_time'  => null,
    'avg_moves' => null,
];
$stmt->close();

$difficulty = computeDifficultyLevel($stats);
$powerUps   = getPowerUpsForDifficulty($difficulty);

// --- generate puzzle & insert session ---
$board     = generatePuzzle($puzzleSize);
$boardJson = json_encode($board);

$stmt = $mysqli->prepare("
    INSERT INTO game_sessions (user_id, puzzle_size, initial_state, current_state)
    VALUES (?, ?, ?, ?)
");
if (!$stmt) {
    jsonResponse(['error' => 'Database error'], 500);
}
$stmt->bind_param("iiss", $userId, $puzzleSize, $boardJson, $boardJson);
$stmt->execute();
$sessionId = (int)$mysqli->insert_id;
$stmt->close();

// log analytics event
$eventData = json_encode([
    'puzzle_size' => $puzzleSize,
    'difficulty'  => $difficulty
]);
$stmt = $mysqli->prepare("
    INSERT INTO analytics (user_id, session_id, event_type, event_data)
    VALUES (?, ?, 'game_started', ?)
");
if (!$stmt) {
    jsonResponse(['error' => 'Database error'], 500);
}
$stmt->bind_param("iis", $userId, $sessionId, $eventData);
$stmt->execute();
$stmt->close();

jsonResponse([
    'success'    => true,
    'sessionId'  => $sessionId,
    'puzzleSize' => $puzzleSize,
    'difficulty' => $difficulty,
    'powerUps'   => $powerUps,
    'board'      => $board,
]);
