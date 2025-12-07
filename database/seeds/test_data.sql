-- Sample test data for Christmas Puzzle Game
-- Use this to populate the database with test data for development
-- Note: Replace password_hash with actual bcrypt hashes when implementing

USE ebinitie1;

-- Sample users
-- IMPORTANT: Replace password_hash with actual bcrypt hashes in production
INSERT INTO users (username, email, password_hash) VALUES
('testuser', 'test@example.com', '$2y$10$example_hash_replace_with_real_bcrypt'),
('puzzlemaster', 'puzzle@example.com', '$2y$10$example_hash_replace_with_real_bcrypt'),
('holidayplayer', 'holiday@example.com', '$2y$10$example_hash_replace_with_real_bcrypt'),
('quickplayer', 'quick@example.com', '$2y$10$example_hash_replace_with_real_bcrypt');

-- Sample game sessions
-- Note: Puzzle states are stored as JSON strings
INSERT INTO game_sessions (user_id, puzzle_size, initial_state, current_state, moves, completed) VALUES
(1, 4, '[1,2,3,4,5,6,7,8,9,10,11,12,13,14,15,0]', '[1,2,3,4,5,6,7,8,9,10,11,12,13,14,15,0]', 0, FALSE),
(1, 3, '[1,2,3,4,5,6,7,8,0]', '[1,2,3,4,5,6,7,8,0]', 15, TRUE),
(2, 4, '[1,2,3,4,5,6,7,8,9,10,11,12,13,14,15,0]', '[1,2,3,4,5,6,7,8,9,10,11,12,13,14,15,0]', 45, TRUE),
(2, 4, '[1,2,3,4,5,6,7,8,9,10,11,12,13,14,15,0]', '[1,2,3,4,5,6,7,8,9,10,11,12,13,14,15,0]', 32, TRUE),
(3, 3, '[1,2,3,4,5,6,7,8,0]', '[1,2,3,4,5,6,7,8,0]', 20, TRUE);

-- Sample user preferences
INSERT INTO user_preferences (user_id, theme, difficulty_preference, sound_enabled) VALUES
(1, 'santa', 'medium', TRUE),
(2, 'reindeer', 'hard', TRUE),
(3, 'elf', 'easy', FALSE),
(4, 'santa', 'medium', TRUE);

-- Sample rewards
INSERT INTO rewards (user_id, reward_type, reward_name) VALUES
(1, 'badge', 'First Win'),
(1, 'achievement', 'Speed Solver'),
(2, 'badge', 'Puzzle Master'),
(2, 'achievement', 'Perfect Game'),
(2, 'badge', 'Quick Thinker'),
(3, 'badge', 'Holiday Hero');

-- Sample move history (for session 1)
INSERT INTO move_history (session_id, move_number, from_position, to_position, puzzle_state) VALUES
(1, 1, 14, 15, '[1,2,3,4,5,6,7,8,9,10,11,12,13,0,14,15]'),
(1, 2, 13, 14, '[1,2,3,4,5,6,7,8,9,10,11,12,0,13,14,15]'),
(1, 3, 12, 13, '[1,2,3,4,5,6,7,8,9,10,11,0,12,13,14,15]');

-- Sample analytics events
INSERT INTO analytics (user_id, session_id, event_type, event_data) VALUES
(1, 1, 'game_started', '{"puzzle_size": 4}'),
(1, 1, 'move_made', '{"move_number": 1, "from": 14, "to": 15}'),
(2, 3, 'game_completed', '{"completion_time": 120, "moves": 45}'),
(2, 4, 'game_completed', '{"completion_time": 95, "moves": 32}');

