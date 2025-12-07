-- User-related queries
-- Use with MySQLi prepared statements (bind parameters with ?)

-- Get user by username (for login)
SELECT * FROM users WHERE username = ?;

-- Get user by ID
SELECT * FROM users WHERE id = ?;

-- Create new user (registration)
INSERT INTO users (username, email, password_hash) VALUES (?, ?, ?);

-- Get user with preferences
SELECT u.*, up.theme, up.difficulty_preference, up.sound_enabled
FROM users u 
LEFT JOIN user_preferences up ON u.id = up.user_id 
WHERE u.id = ?;

-- Get user statistics
SELECT 
    COUNT(DISTINCT gs.id) as total_games,
    COUNT(DISTINCT CASE WHEN gs.completed = 1 THEN gs.id END) as completed_games,
    AVG(gs.completion_time) as avg_completion_time,
    MIN(gs.completion_time) as best_time,
    AVG(gs.moves) as avg_moves,
    MIN(gs.moves) as best_moves
FROM users u
LEFT JOIN game_sessions gs ON u.id = gs.user_id
WHERE u.id = ?;

-- Get user's rewards
SELECT * FROM rewards WHERE user_id = ? ORDER BY earned_at DESC;

-- Check if username exists
SELECT COUNT(*) as count FROM users WHERE username = ?;

-- Check if email exists
SELECT COUNT(*) as count FROM users WHERE email = ?;

-- Update user email
UPDATE users SET email = ? WHERE id = ?;

-- Update user password
UPDATE users SET password_hash = ? WHERE id = ?;

