<?php
// api/analytics.php
declare(strict_types=1);

require_once __DIR__ . '/../backend/auth.php';
require_once __DIR__ . '/../backend/db.php';

if ($_SERVER['REQUEST_METHOD'] !== 'GET') {
    jsonResponse(['error' => 'Method not allowed'], 405);
}

$userId = requireAuth();
$mysqli = getMySQLi();

// --- User analytics summary ---
$stmt = $mysqli->prepare("
    SELECT 
        COUNT(DISTINCT gs.id) AS total_sessions,
        COUNT(DISTINCT CASE WHEN gs.completed = 1 THEN gs.id END) AS completed_sessions,
        AVG(gs.moves) AS avg_moves,
        AVG(gs.completion_time) AS avg_time,
        COUNT(DISTINCT r.id) AS total_rewards,
        COUNT(DISTINCT CASE WHEN r.reward_type = 'badge' THEN r.id END) AS badges_earned
    FROM users u
    LEFT JOIN game_sessions gs ON u.id = gs.user_id
    LEFT JOIN rewards r ON u.id = r.user_id
    WHERE u.id = ?
");
if (!$stmt) {
    jsonResponse(['error' => 'Database error'], 500);
}
$stmt->bind_param("i", $userId);
$stmt->execute();
$result = $stmt->get_result();
$summary = $result->fetch_assoc();
$stmt->close();

// --- Average completion time by puzzle size ---
$stmt = $mysqli->prepare("
    SELECT 
        puzzle_size,
        AVG(completion_time) AS avg_time,
        MIN(completion_time) AS best_time,
        COUNT(*) AS games_completed
    FROM game_sessions
    WHERE user_id = ? AND completed = TRUE
    GROUP BY puzzle_size
    ORDER BY puzzle_size
");
if (!$stmt) {
    jsonResponse(['error' => 'Database error'], 500);
}
$stmt->bind_param("i", $userId);
$stmt->execute();
$result = $stmt->get_result();
$bySize = [];
while ($row = $result->fetch_assoc()) {
    $bySize[] = $row;
}
$stmt->close();

// --- Leaderboard by completion time (top 10) ---
$query = "
    SELECT 
        u.username,
        MIN(gs.completion_time) AS best_time,
        COUNT(DISTINCT gs.id) AS games_played,
        AVG(gs.completion_time) AS avg_time
    FROM users u
    JOIN game_sessions gs ON u.id = gs.user_id
    WHERE gs.completed = 1
    GROUP BY u.id, u.username
    ORDER BY best_time ASC
    LIMIT 10
";
$result = $mysqli->query($query);
$leaderTime = [];
if ($result) {
    while ($row = $result->fetch_assoc()) {
        $leaderTime[] = $row;
    }
}

// --- Leaderboard by moves (top 10) ---
$query = "
    SELECT 
        u.username,
        MIN(gs.moves) AS best_moves,
        COUNT(DISTINCT gs.id) AS games_played,
        AVG(gs.moves) AS avg_moves
    FROM users u
    JOIN game_sessions gs ON u.id = gs.user_id
    WHERE gs.completed = 1
    GROUP BY u.id, u.username
    ORDER BY best_moves ASC
    LIMIT 10
";
$result = $mysqli->query($query);
$leaderMoves = [];
if ($result) {
    while ($row = $result->fetch_assoc()) {
        $leaderMoves[] = $row;
    }
}

// --- Recent completions feed ---
$query = "
    SELECT 
        u.username,
        gs.puzzle_size,
        gs.completion_time,
        gs.moves,
        gs.end_time
    FROM game_sessions gs
    JOIN users u ON gs.user_id = u.id
    WHERE gs.completed = TRUE
    ORDER BY gs.end_time DESC
    LIMIT 20
";
$result = $mysqli->query($query);
$recent = [];
if ($result) {
    while ($row = $result->fetch_assoc()) {
        $recent[] = $row;
    }
}

// Check if leaderboards are requested
$requestType = $_GET['type'] ?? '';

if ($requestType === 'leaderboards') {
    jsonResponse([
        'success' => true,
        'fastest_times' => $leaderTime,
        'fewest_moves' => $leaderMoves
    ]);
}

jsonResponse([
    'success'        => true,
    'summary'        => $summary,
    'bySize'         => $bySize,
    'leaderboardTime'=> $leaderTime,
    'leaderboardMoves'=>$leaderMoves,
    'recentCompletions' => $recent
]);
