USE ebinitie1;

-- Add current_level column if it doesn't exist
ALTER TABLE user_preferences 
ADD COLUMN current_level INT DEFAULT 1 AFTER difficulty_preference;

-- Update existing users to start at level 1
UPDATE user_preferences SET current_level = 1 WHERE current_level IS NULL;
