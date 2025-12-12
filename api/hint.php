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
$mysqli = getMySQLi();

$input     = json_decode(file_get_contents('php://input'), true);
if ($input === null) {
    jsonResponse(['error' => 'Invalid JSON input'], 400);
}

$sessionId = (int)($input['sessionId'] ?? 0);
$board     = $input['board'] ?? [];
$gridSize  = (int)($input['gridSize'] ?? 4);

// Validate board is an array
if (!is_array($board)) {
    jsonResponse(['error' => 'Board must be an array'], 400);
}

// If gridSize is provided directly, use it (for level-based gameplay)
if ($gridSize > 0 && is_array($board) && count($board) > 0) {
    $expectedSize = $gridSize * $gridSize;
    if (count($board) !== $expectedSize) {
        jsonResponse(['error' => 'Invalid board size. Expected ' . $expectedSize . ' tiles, got ' . count($board)], 400);
    }
    $size = $gridSize;
} else if ($sessionId > 0) {
    // Fallback to session-based lookup
    $stmt = $mysqli->prepare("
        SELECT puzzle_size
        FROM game_sessions
        WHERE id = ? AND user_id = ?
    ");
    if (!$stmt) {
        jsonResponse(['error' => 'Database error'], 500);
    }
    $stmt->bind_param("ii", $sessionId, $userId);
    $stmt->execute();
    $result = $stmt->get_result();
    $row = $result->fetch_assoc();
    $stmt->close();

    if (!$row) {
        jsonResponse(['error' => 'Session not found'], 404);
    }

    $size = (int)$row['puzzle_size'];
    if (count($board) !== $size * $size) {
        jsonResponse(['error' => 'Invalid board size'], 400);
    }
} else {
    jsonResponse(['error' => 'Invalid payload'], 400);
}

try {
    $hintIndex = getHintMove($board, $size);
    
    if ($hintIndex === null) {
        jsonResponse(['success' => true, 'hintAvailable' => false]);
    }
    
    jsonResponse([
        'success'       => true,
        'hintAvailable' => true,
        'hintIndex'     => $hintIndex
    ]);
} catch (Exception $e) {
    error_log("Hint error: " . $e->getMessage());
    jsonResponse(['error' => 'Failed to generate hint: ' . $e->getMessage()], 500);
}
