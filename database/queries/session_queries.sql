-- Game session-related queries
-- Use with MySQLi prepared statements (bind parameters with ?)

-- Start new game session
INSERT INTO game_sessions (user_id, puzzle_size, initial_state, current_state) 
VALUES (?, ?, ?, ?);

-- Get active session for user
SELECT * FROM game_sessions 
WHERE user_id = ? AND completed = FALSE 
ORDER BY start_time DESC 
LIMIT 1;

-- Get session by ID
SELECT * FROM game_sessions WHERE id = ?;

-- Update session after move
UPDATE game_sessions 
SET moves = ?, current_state = ? 
WHERE id = ?;

-- Complete session
UPDATE game_sessions 
SET completed = TRUE, 
    end_time = NOW(), 
    completion_time = TIMESTAMPDIFF(SECOND, start_time, NOW())
WHERE id = ?;

-- Get user's recent sessions
SELECT * FROM game_sessions 
WHERE user_id = ? 
ORDER BY start_time DESC 
LIMIT 10;

-- Get session with move count
SELECT 
    gs.*,
    COUNT(mh.id) as total_moves
FROM game_sessions gs
LEFT JOIN move_history mh ON gs.id = mh.session_id
WHERE gs.id = ?
GROUP BY gs.id;

-- Get all completed sessions for user
SELECT * FROM game_sessions 
WHERE user_id = ? AND completed = TRUE 
ORDER BY completion_time ASC;

-- Get best time for user
SELECT MIN(completion_time) as best_time 
FROM game_sessions 
WHERE user_id = ? AND completed = TRUE;

-- Get best moves for user
SELECT MIN(moves) as best_moves 
FROM game_sessions 
WHERE user_id = ? AND completed = TRUE;

-- Record a move in move_history
INSERT INTO move_history (session_id, move_number, from_position, to_position, puzzle_state) 
VALUES (?, ?, ?, ?, ?);

-- Get all moves for a session
SELECT * FROM move_history 
WHERE session_id = ? 
ORDER BY move_number ASC;

-- Get move count for session
SELECT COUNT(*) as move_count 
FROM move_history 
WHERE session_id = ?;

