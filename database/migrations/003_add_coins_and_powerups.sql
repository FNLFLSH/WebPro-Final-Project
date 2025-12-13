-- Migration 003: Add coins and power-ups system
-- Run this on codd.cs.gsu.edu via MySQL CLI

USE ebinitie1;

-- Add coins column to user_preferences (if it doesn't already exist)
-- Note: If column already exists, you'll get an error - that's okay, just continue
ALTER TABLE user_preferences 
ADD COLUMN coins INT DEFAULT 0 AFTER sound_enabled;

-- Create user_powerups table for tracking purchased power-ups
CREATE TABLE IF NOT EXISTS user_powerups (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    powerup_type VARCHAR(50) NOT NULL,
    quantity INT DEFAULT 1,
    purchased_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    INDEX idx_user_id (user_id),
    INDEX idx_powerup_type (powerup_type)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Initialize coins for existing users (set to 0 if NULL)
UPDATE user_preferences SET coins = 0 WHERE coins IS NULL;

-- Verify
DESCRIBE user_preferences;
DESCRIBE user_powerups;

