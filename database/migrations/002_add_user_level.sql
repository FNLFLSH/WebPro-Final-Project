-- Migration: Add current_level to user_preferences table
-- Run this on codd.cs.gsu.edu

USE ebinitie1;

-- Add current_level column to user_preferences
ALTER TABLE user_preferences 
ADD COLUMN current_level INT DEFAULT 1 AFTER difficulty_preference;

-- Update existing users to start at level 1
UPDATE user_preferences SET current_level = 1 WHERE current_level IS NULL;




