# If You Can SSH Into codd.cs.gsu.edu

## Yes! This Will Fix Everything! 🎉

If you can successfully SSH into the server, you can:
1. ✅ Connect to MySQL directly from the server
2. ✅ Run all the database setup commands
3. ✅ Create all the tables
4. ✅ Then your app will be able to connect

## Step-by-Step (Once SSH Works)

### Step 1: SSH Into Server
```bash
ssh ebinitie1@codd.cs.gsu.edu
# Enter your password when prompted
```

### Step 2: Connect to MySQL
Once you're on the server, run:
```bash
mysql -u ebinitie1 -p
# Password: ebinitie1
```

### Step 3: Select Database
```sql
USE ebinitie1;
```

### Step 4: Run Database Setup
Copy and paste all the SQL from `QUICK_SETUP.sql`:

```sql
-- 1. Users Table
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

-- 2. User Preferences (with current_level)
CREATE TABLE IF NOT EXISTS user_preferences (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT UNIQUE NOT NULL,
    theme VARCHAR(20) DEFAULT 'santa',
    difficulty_preference VARCHAR(20) DEFAULT 'medium',
    current_level INT DEFAULT 1,
    sound_enabled BOOLEAN DEFAULT TRUE,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- 3. Game Sessions
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

-- 4. Analytics
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

-- 5. Rewards
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

-- 6. Move History
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

### Step 5: Verify Tables Created
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

### Step 6: Check Structure
```sql
DESCRIBE user_preferences;
```

Should show `current_level` column.

### Step 7: Exit
```sql
exit;
exit;  # Exit SSH too
```

## After Database Setup

### Option A: Direct Connection (If Allowed)
Your app should now be able to connect directly to `codd.cs.gsu.edu:3306` (if port 3306 is open from your machine).

### Option B: SSH Tunnel (If Direct Doesn't Work)
If direct connection still doesn't work, create an SSH tunnel:

```bash
# In a NEW terminal (keep this open)
ssh -L 3306:localhost:3306 ebinitie1@codd.cs.gsu.edu
```

Then your app will connect to `127.0.0.1:3306` which tunnels to the database.

## Why This Fixes Everything

1. ✅ **Database tables created** - All 6 tables with proper structure
2. ✅ **Foreign keys set up** - Data integrity ensured
3. ✅ **Indexes created** - Fast queries
4. ✅ **current_level column** - Levels system ready
5. ✅ **App can connect** - Once tables exist, connection will work

## Quick Test After Setup

1. Start your PHP server (if not running):
   ```bash
   php -S localhost:8000 -t .
   ```

2. Open browser: `http://localhost:8000`

3. Try to register a new user

4. If it works, everything is set up! 🎉

## If SSH Still Doesn't Work

Contact your instructor and ask:
- "How do I access codd.cs.gsu.edu to set up the database?"
- "Is there a web interface I can use instead?"
- "Can you help me set up the database tables?"

