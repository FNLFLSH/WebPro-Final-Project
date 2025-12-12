<?php
// api/update-session.php
declare(strict_types=1);

require_once __DIR__ . '/../backend/auth.php';
require_once __DIR__ . '/../backend/db.php';
require_once __DIR__ . '/../backend/puzzleLogic.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    jsonResponse(['error' => 'Method not allowed'], 405);
}

$userId = requireAuth();
$mysqli = getMySQLi();

$input       = json_decode(file_get_contents('php://input'), true);
$sessionId   = (int)($input['sessionId'] ?? 0);
$board       = $input['board'] ?? [];
$moves       = (int)($input['moves'] ?? 0);
$magicUsed   = (int)($input['magicUsed'] ?? 0);
$completed   = (bool)($input['completed'] ?? false);

if ($sessionId <= 0 || !is_array($board)) {
    jsonResponse(['error' => 'Invalid payload'], 400);
}

// Check session belongs to user & get puzzle_size
$stmt = $mysqli->prepare("
    SELECT id, user_id, puzzle_size
    FROM game_sessions
    WHERE id = ? AND user_id = ?
");
if (!$stmt) {
    jsonResponse(['error' => 'Database error'], 500);
}
$stmt->bind_param("ii", $sessionId, $userId);
$stmt->execute();
$result = $stmt->get_result();
$session = $result->fetch_assoc();
$stmt->close();

if (!$session) {
    jsonResponse(['error' => 'Session not found'], 404);
}

$size = (int)$session['puzzle_size'];
if (count($board) !== $size * $size) {
    jsonResponse(['error' => 'Invalid board size'], 400);
}

$boardJson = json_encode($board);
$isSolved  = isSolved($board);

if ($completed && !$isSolved) {
    // front-end lied / bug, ignore completed flag
    $completed = false;
}

// If not completed yet, just update moves + state
if (!$completed) {
    $stmt = $mysqli->prepare("
        UPDATE game_sessions
        SET moves = ?, current_state = ?
        WHERE id = ? AND user_id = ?
    ");
    if (!$stmt) {
        jsonResponse(['error' => 'Database error'], 500);
    }
    $stmt->bind_param("isii", $moves, $boardJson, $sessionId, $userId);
    $stmt->execute();
    $stmt->close();

    // optional analytics: "move_made"
    $eventData = json_encode(['moves' => $moves]);
    $stmt = $mysqli->prepare("
        INSERT INTO analytics (user_id, session_id, event_type, event_data)
        VALUES (?, ?, 'move_made', ?)
    ");
    if (!$stmt) {
        jsonResponse(['error' => 'Database error'], 500);
    }
    $stmt->bind_param("iis", $userId, $sessionId, $eventData);
    $stmt->execute();
    $stmt->close();

    jsonResponse([
        'success'   => true,
        'completed' => false,
        'isSolved'  => $isSolved
    ]);
}

// Completed AND solved: close session
$stmt = $mysqli->prepare("
    UPDATE game_sessions
    SET moves = ?, current_state = ?, completed = TRUE,
        end_time = NOW(),
        completion_time = TIMESTAMPDIFF(SECOND, start_time, NOW())
    WHERE id = ? AND user_id = ?
");
if (!$stmt) {
    jsonResponse(['error' => 'Database error'], 500);
}
$stmt->bind_param("isii", $moves, $boardJson, $sessionId, $userId);
$stmt->execute();
$stmt->close();

// log "game_completed"
$eventData = json_encode(['moves' => $moves]);
$stmt = $mysqli->prepare("
    INSERT INTO analytics (user_id, session_id, event_type, event_data)
    VALUES (?, ?, 'game_completed', ?)
");
if (!$stmt) {
    jsonResponse(['error' => 'Database error'], 500);
}
$stmt->bind_param("iis", $userId, $sessionId, $eventData);
$stmt->execute();
$stmt->close();

jsonResponse([
    'success'   => true,
    'completed' => true,
    'isSolved'  => true
]);
