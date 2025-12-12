# Manual Database Setup - Step by Step

## Step 1: Connect to MySQL

SSH into the server and connect to MySQL:
```bash
ssh ebinitie1@codd.cs.gsu.edu
mysql -u ebinitie1 -p
# Password: ebinitie1
```

## Step 2: Select Database

```sql
USE ebinitie1;
```

## Step 3: Create Tables (One by One)

### 3.1: Users Table
```sql
CREATE TABLE IF NOT EXISTS users (
    id INT AUTO_INCREMENT PRIMARY KEY,
    username VARCHAR(50) UNIQUE NOT NULL,
    email VARCHAR(100) UNIQUE NOT NULL,
    password_hash VARCHAR(255) NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    INDEX idx_username (username),
    INDEX idx_email (email)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
```

### 3.2: User Preferences Table (with current_level)
```sql
CREATE TABLE IF NOT EXISTS user_preferences (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT UNIQUE NOT NULL,
    theme VARCHAR(20) DEFAULT 'santa',
    difficulty_preference VARCHAR(20) DEFAULT 'medium',
    current_level INT DEFAULT 1,
    sound_enabled BOOLEAN DEFAULT TRUE,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
```

### 3.3: Game Sessions Table
```sql
CREATE TABLE IF NOT EXISTS game_sessions (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    puzzle_size INT NOT NULL,
    initial_state LONGTEXT NOT NULL,
    current_state LONGTEXT,
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
```

### 3.4: Analytics Table
```sql
CREATE TABLE IF NOT EXISTS analytics (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    session_id INT,
    event_type VARCHAR(50) NOT NULL,
    event_data LONGTEXT,
    timestamp TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    FOREIGN KEY (session_id) REFERENCES game_sessions(id) ON DELETE SET NULL,
    INDEX idx_user_id (user_id),
    INDEX idx_event_type (event_type),
    INDEX idx_timestamp (timestamp)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
```

### 3.5: Rewards Table
```sql
CREATE TABLE IF NOT EXISTS rewards (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    reward_type VARCHAR(50) NOT NULL,
    reward_name VARCHAR(100) NOT NULL,
    earned_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    INDEX idx_user_id (user_id),
    INDEX idx_reward_type (reward_type)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
```

### 3.6: Move History Table
```sql
CREATE TABLE IF NOT EXISTS move_history (
    id INT AUTO_INCREMENT PRIMARY KEY,
    session_id INT NOT NULL,
    move_number INT NOT NULL,
    from_position INT NOT NULL,
    to_position INT NOT NULL,
    puzzle_state LONGTEXT NOT NULL,
    timestamp TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (session_id) REFERENCES game_sessions(id) ON DELETE CASCADE,
    INDEX idx_session_id (session_id),
    INDEX idx_move_number (move_number)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
```

## Step 4: Verify Tables

```sql
SHOW TABLES;
```

You should see:
- users
- user_preferences
- game_sessions
- analytics
- rewards
- move_history

## Step 5: Check Table Structures

```sql
DESCRIBE users;
DESCRIBE user_preferences;
DESCRIBE game_sessions;
```

## Step 6: If Tables Already Exist

If tables exist but missing columns, add them:

### Add current_level to user_preferences (if missing):
```sql
ALTER TABLE user_preferences 
ADD COLUMN current_level INT DEFAULT 1 AFTER difficulty_preference;
```

### If JSON columns need to be LONGTEXT (for MariaDB 10.3):
```sql
-- Check current structure first
DESCRIBE game_sessions;

-- If initial_state is JSON, change to LONGTEXT:
ALTER TABLE game_sessions 
MODIFY COLUMN initial_state LONGTEXT NOT NULL,
MODIFY COLUMN current_state LONGTEXT;
```

## Step 7: Test with Sample Data

```sql
-- Create a test user
INSERT INTO users (username, email, password_hash) 
VALUES ('testuser', 'test@example.com', '$2y$10$examplehash');

-- Get the user ID
SELECT id FROM users WHERE username = 'testuser';

-- Create preferences for that user (replace USER_ID with actual ID)
INSERT INTO user_preferences (user_id, theme, difficulty_preference, current_level) 
VALUES (1, 'santa', 'medium', 1);
```

## Step 8: Exit

```sql
exit;
```

Then exit SSH:
```bash
exit
```

