-- Analytics and leaderboard queries
-- Use with MySQLi prepared statements (bind parameters with ?)

-- Leaderboard by completion time (top 10)
SELECT 
    u.username,
    MIN(gs.completion_time) as best_time,
    COUNT(DISTINCT gs.id) as games_played,
    AVG(gs.completion_time) as avg_time
FROM users u
JOIN game_sessions gs ON u.id = gs.user_id
WHERE gs.completed = 1
GROUP BY u.id, u.username
ORDER BY best_time ASC
LIMIT 10;

-- Leaderboard by moves (top 10)
SELECT 
    u.username,
    MIN(gs.moves) as best_moves,
    COUNT(DISTINCT gs.id) as games_played,
    AVG(gs.moves) as avg_moves
FROM users u
JOIN game_sessions gs ON u.id = gs.user_id
WHERE gs.completed = 1
GROUP BY u.id, u.username
ORDER BY best_moves ASC
LIMIT 10;

-- User analytics summary
SELECT 
    COUNT(DISTINCT gs.id) as total_sessions,
    COUNT(DISTINCT CASE WHEN gs.completed = 1 THEN gs.id END) as completed_sessions,
    AVG(gs.moves) as avg_moves,
    AVG(gs.completion_time) as avg_time,
    COUNT(DISTINCT r.id) as total_rewards,
    COUNT(DISTINCT CASE WHEN r.reward_type = 'badge' THEN r.id END) as badges_earned
FROM users u
LEFT JOIN game_sessions gs ON u.id = gs.user_id
LEFT JOIN rewards r ON u.id = r.user_id
WHERE u.id = ?;

-- Log analytics event
INSERT INTO analytics (user_id, session_id, event_type, event_data) 
VALUES (?, ?, ?, ?);

-- Get user's analytics events
SELECT * FROM analytics 
WHERE user_id = ? 
ORDER BY timestamp DESC 
LIMIT 50;

-- Get analytics events by type
SELECT * FROM analytics 
WHERE user_id = ? AND event_type = ? 
ORDER BY timestamp DESC;

-- Get completion rate for user
SELECT 
    COUNT(*) as total_sessions,
    SUM(CASE WHEN completed = 1 THEN 1 ELSE 0 END) as completed_sessions,
    (SUM(CASE WHEN completed = 1 THEN 1 ELSE 0 END) / COUNT(*)) * 100 as completion_rate
FROM game_sessions 
WHERE user_id = ?;

-- Get average completion time by puzzle size
SELECT 
    puzzle_size,
    AVG(completion_time) as avg_time,
    MIN(completion_time) as best_time,
    COUNT(*) as games_completed
FROM game_sessions 
WHERE user_id = ? AND completed = TRUE
GROUP BY puzzle_size;

-- Get recent completions (for activity feed)
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
LIMIT 20;

