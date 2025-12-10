<?php
// api/hint.php
declare(strict_types=1);

require_once __DIR__ . '/../backend/auth.php';
require_once __DIR__ . '/../backend/db.php';
require_once __DIR__ . '/../backend/puzzleLogic.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    jsonResponse(['error' => 'Method not allowed'], 405);
}

$userId = requireAuth();
$pdo    = getPDO();

$input     = json_decode(file_get_contents('php://input'), true);
$sessionId = (int)($input['sessionId'] ?? 0);
$board     = $input['board'] ?? [];

if ($sessionId <= 0 || !is_array($board)) {
    jsonResponse(['error' => 'Invalid payload'], 400);
}

// confirm session + puzzle size
$stmt = $pdo->prepare("
    SELECT puzzle_size
    FROM game_sessions
    WHERE id = ? AND user_id = ?
");
$stmt->execute([$sessionId, $userId]);
$row = $stmt->fetch();

if (!$row) {
    jsonResponse(['error' => 'Session not found'], 404);
}

$size = (int)$row['puzzle_size'];
if (count($board) !== $size * $size) {
    jsonResponse(['error' => 'Invalid board size'], 400);
}

$hintIndex = getHintMove($board, $size);

if ($hintIndex === null) {
    jsonResponse(['success' => true, 'hintAvailable' => false]);
}

jsonResponse([
    'success'       => true,
    'hintAvailable' => true,
    'hintIndex'     => $hintIndex
]);
