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
$pdo    = getPDO();

$input      = json_decode(file_get_contents('php://input'), true);
$puzzleSize = (int)($input['gridSize'] ?? $input['puzzleSize'] ?? 4);
$allowed    = [3, 4, 6, 8, 10];
if (!in_array($puzzleSize, $allowed, true)) {
    $puzzleSize = 4;
}

// --- get user stats for difficulty ---
$stmt = $pdo->prepare("
    SELECT 
        COUNT(*) AS attempted,
        SUM(CASE WHEN completed = TRUE THEN 1 ELSE 0 END) AS completed,
        AVG(completion_time) AS avg_time,
        AVG(moves) AS avg_moves
    FROM game_sessions
    WHERE user_id = ?
");
$stmt->execute([$userId]);
$stats = $stmt->fetch() ?: [
    'attempted' => 0,
    'completed' => 0,
    'avg_time'  => null,
    'avg_moves' => null,
];

$difficulty = computeDifficultyLevel($stats);
$powerUps   = getPowerUpsForDifficulty($difficulty);

// --- generate puzzle & insert session ---
$board     = generatePuzzle($puzzleSize);
$boardJson = json_encode($board);

$stmt = $pdo->prepare("
    INSERT INTO game_sessions (user_id, puzzle_size, initial_state, current_state)
    VALUES (?, ?, ?, ?)
");
$stmt->execute([$userId, $puzzleSize, $boardJson, $boardJson]);
$sessionId = (int)$pdo->lastInsertId();

// log analytics event
$eventData = json_encode([
    'puzzle_size' => $puzzleSize,
    'difficulty'  => $difficulty
]);
$stmt = $pdo->prepare("
    INSERT INTO analytics (user_id, session_id, event_type, event_data)
    VALUES (?, ?, 'game_started', ?)
");
$stmt->execute([$userId, $sessionId, $eventData]);

jsonResponse([
    'success'    => true,
    'sessionId'  => $sessionId,
    'puzzleSize' => $puzzleSize,
    'difficulty' => $difficulty,
    'powerUps'   => $powerUps,
    'board'      => $board,
]);
