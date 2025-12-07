-- Database schema for Christmas Puzzle Game
-- MySQLi Compatible - For use with PHP MySQLi extension
-- Run this on codd.cs.gsu.edu via MySQL CLI or PHP script

CREATE DATABASE IF NOT EXISTS ebinitie1;
USE ebinitie1;

-- Users table
CREATE TABLE users (
    id INT AUTO_INCREMENT PRIMARY KEY,
    username VARCHAR(50) UNIQUE NOT NULL,
    email VARCHAR(100) UNIQUE NOT NULL,
    password_hash VARCHAR(255) NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    INDEX idx_username (username),
    INDEX idx_email (email)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Game sessions table
CREATE TABLE game_sessions (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    puzzle_size INT NOT NULL,
    initial_state JSON NOT NULL,
    current_state JSON,
    moves INT DEFAULT 0,
    start_time TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    end_time TIMESTAMP NULL,
    completion_time INT NULL,
    completed BOOLEAN DEFAULT FALSE,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    INDEX idx_user_id (user_id),
    INDEX idx_completed (completed),
    INDEX idx_start_time (start_time)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Puzzles table (optional - for storing puzzle configurations)
CREATE TABLE puzzles (
    id INT AUTO_INCREMENT PRIMARY KEY,
    size INT NOT NULL,
    state JSON NOT NULL,
    solvable BOOLEAN NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    INDEX idx_size (size)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Analytics table
CREATE TABLE analytics (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    session_id INT,
    event_type VARCHAR(50) NOT NULL,
    event_data JSON,
    timestamp TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    FOREIGN KEY (session_id) REFERENCES game_sessions(id) ON DELETE SET NULL,
    INDEX idx_user_id (user_id),
    INDEX idx_event_type (event_type),
    INDEX idx_timestamp (timestamp)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- User preferences table
CREATE TABLE user_preferences (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT UNIQUE NOT NULL,
    theme VARCHAR(20) DEFAULT 'santa',
    difficulty_preference VARCHAR(20) DEFAULT 'medium',
    sound_enabled BOOLEAN DEFAULT TRUE,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Rewards/Badges table
CREATE TABLE rewards (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    reward_type VARCHAR(50) NOT NULL,
    reward_name VARCHAR(100) NOT NULL,
    earned_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    INDEX idx_user_id (user_id),
    INDEX idx_reward_type (reward_type)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Move history table
CREATE TABLE move_history (
    id INT AUTO_INCREMENT PRIMARY KEY,
    session_id INT NOT NULL,
    move_number INT NOT NULL,
    from_position INT NOT NULL,
    to_position INT NOT NULL,
    puzzle_state JSON NOT NULL,
    timestamp TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (session_id) REFERENCES game_sessions(id) ON DELETE CASCADE,
    INDEX idx_session_id (session_id),
    INDEX idx_move_number (move_number)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

