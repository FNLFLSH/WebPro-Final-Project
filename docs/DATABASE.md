# Database Documentation - Christmas Puzzle Game

## 📋 Quick Connection Guide

### Connection Information

| Setting | Value |
|---------|-------|
| **Server** | `codd.cs.gsu.edu` |
| **Database Name** | `ebinitie1` |
| **Username** | `ebinitie1` |
| **Port** | 3306 (default) |

### How to Connect

**Via SSH + MySQL CLI:**
```bash
# Step 1: SSH into the server
ssh ebinitie1@codd.cs.gsu.edu

# Step 2: Connect to MySQL
mysql -u ebinitie1 -p

# Step 3: Select the database
USE ebinitie1;
```

---

## 📊 Database Structure

### Tables Overview

The database contains **7 tables**:

1. **`users`** - User accounts and authentication information
2. **`game_sessions`** - Individual puzzle game sessions
3. **`puzzles`** - Puzzle configurations and states
4. **`analytics`** - User events and game statistics
5. **`user_preferences`** - User settings (theme, difficulty, sound)
6. **`rewards`** - Badges and achievements earned by users
7. **`move_history`** - Detailed record of every move in each game

### Table Relationships
users (1) ──→ (many) game_sessions
users (1) ──→ (1) user_preferences
users (1) ──→ (many) rewards
users (1) ──→ (many) analytics

game_sessions (1) ──→ (many) move_history
game_sessions (1) ──→ (many) analy

### users
| Column | Type | Constraints |
|--------|------|-------------|
| id | INT | PRIMARY KEY, AUTO_INCREMENT |
| username | VARCHAR(50) | UNIQUE, NOT NULL |
| email | VARCHAR(100) | UNIQUE, NOT NULL |
| password_hash | VARCHAR(255) | NOT NULL |
| created_at | TIMESTAMP | DEFAULT CURRENT_TIMESTAMP |
| updated_at | TIMESTAMP | DEFAULT CURRENT_TIMESTAMP ON UPDATE |

**Indexes:** username, email

---

### game_sessions
| Column | Type | Constraints |
|--------|------|-------------|
| id | INT | PRIMARY KEY, AUTO_INCREMENT |
| user_id | INT | NOT NULL, FOREIGN KEY → users(id) |
| puzzle_size | INT | NOT NULL |
| initial_state | LONGTEXT | NOT NULL (JSON format) |
| current_state | LONGTEXT | NULL (JSON format) |
| moves | INT | DEFAULT 0 |
| start_time | TIMESTAMP | DEFAULT CURRENT_TIMESTAMP |
| end_time | TIMESTAMP | NULL |
| completion_time | INT | NULL (seconds) |
| completed | BOOLEAN | DEFAULT FALSE |

**Indexes:** user_id, completed, start_time  
**Foreign Keys:** user_id → users(id) ON DELETE CASCADE

---

### puzzles
| Column | Type | Constraints |
|--------|------|-------------|
| id | INT | PRIMARY KEY, AUTO_INCREMENT |
| size | INT | NOT NULL |
| state | LONGTEXT | NOT NULL (JSON format) |
| solvable | BOOLEAN | NOT NULL |
| created_at | TIMESTAMP | DEFAULT CURRENT_TIMESTAMP |

**Indexes:** size

---

### analytics
| Column | Type | Constraints |
|--------|------|-------------|
| id | INT | PRIMARY KEY, AUTO_INCREMENT |
| user_id | INT | NOT NULL, FOREIGN KEY → users(id) |
| session_id | INT | NULL, FOREIGN KEY → game_sessions(id) |
| event_type | VARCHAR(50) | NOT NULL |
| event_data | LONGTEXT | NULL (JSON format) |
| timestamp | TIMESTAMP | DEFAULT CURRENT_TIMESTAMP |

**Indexes:** user_id, event_type, timestamp  
**Foreign Keys:** 
- user_id → users(id) ON DELETE CASCADE
- session_id → game_sessions(id) ON DELETE SET NULL

---

### user_preferences
| Column | Type | Constraints |
|--------|------|-------------|
| id | INT | PRIMARY KEY, AUTO_INCREMENT |
| user_id | INT | UNIQUE, NOT NULL, FOREIGN KEY → users(id) |
| theme | VARCHAR(20) | DEFAULT 'santa' |
| difficulty_preference | VARCHAR(20) | DEFAULT 'medium' |
| sound_enabled | BOOLEAN | DEFAULT TRUE |

**Foreign Keys:** user_id → users(id) ON DELETE CASCADE

---

### rewards
| Column | Type | Constraints |
|--------|------|-------------|
| id | INT | PRIMARY KEY, AUTO_INCREMENT |
| user_id | INT | NOT NULL, FOREIGN KEY → users(id) |
| reward_type | VARCHAR(50) | NOT NULL |
| reward_name | VARCHAR(100) | NOT NULL |
| earned_at | TIMESTAMP | DEFAULT CURRENT_TIMESTAMP |

**Indexes:** user_id, reward_type  
**Foreign Keys:** user_id → users(id) ON DELETE CASCADE

---

### move_history
| Column | Type | Constraints |
|--------|------|-------------|
| id | INT | PRIMARY KEY, AUTO_INCREMENT |
| session_id | INT | NOT NULL, FOREIGN KEY → game_sessions(id) |
| move_number | INT | NOT NULL |
| from_position | INT | NOT NULL |
| to_position | INT | NOT NULL |
| puzzle_state | LONGTEXT | NOT NULL (JSON format) |
| timestamp | TIMESTAMP | DEFAULT CURRENT_TIMESTAMP |

**Indexes:** session_id, move_number  
**Foreign Keys:** session_id → game_sessions(id) ON DELETE CASCADE

---

## 🔍 Common SQL Queries

### User Queries

#### Get user by username
```sql
SELECT * FROM users WHERE username = ?;
```

#### Get user by ID
```sql
SELECT * FROM users WHERE id = ?;
```

#### Create new user
```sql
INSERT INTO users (username, email, password_hash) VALUES (?, ?, ?);
```

#### Get user with preferences
```sql
SELECT u.*, up.theme, up.difficulty_preference, up.sound_enabled
FROM users u 
LEFT JOIN user_preferences up ON u.id = up.user_id 
WHERE u.id = ?;
```

#### Get user statistics
```sql
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
```

---

### Game Session Queries

#### Start new game session
```sql
INSERT INTO game_sessions (user_id, puzzle_size, initial_state, current_state) 
VALUES (?, ?, ?, ?);
```

#### Get active session for user
```sql
SELECT * FROM game_sessions 
WHERE user_id = ? AND completed = FALSE 
ORDER BY start_time DESC 
LIMIT 1;
```

#### Get session by ID
```sql
SELECT * FROM game_sessions WHERE id = ?;
```

#### Update session after move
```sql
UPDATE game_sessions 
SET moves = ?, current_state = ? 
WHERE id = ?;
```

#### Complete session
```sql
UPDATE game_sessions 
SET completed = TRUE, 
    end_time = NOW(), 
    completion_time = TIMESTAMPDIFF(SECOND, start_time, NOW())
WHERE id = ?;
```

#### Get user's recent sessions
```sql
SELECT * FROM game_sessions 
WHERE user_id = ? 
ORDER BY start_time DESC 
LIMIT 10;
```

#### Get session with move count
```sql
SELECT 
    gs.*,
    COUNT(mh.id) as total_moves
FROM game_sessions gs
LEFT JOIN move_history mh ON gs.id = mh.session_id
WHERE gs.id = ?
GROUP BY gs.id;
```

---

### Move History Queries

#### Record a move
```sql
INSERT INTO move_history (session_id, move_number, from_position, to_position, puzzle_state) 
VALUES (?, ?, ?, ?, ?);
```

#### Get all moves for a session
```sql
SELECT * FROM move_history 
WHERE session_id = ? 
ORDER BY move_number ASC;
```

#### Get move count for session
```sql
SELECT COUNT(*) as move_count 
FROM move_history 
WHERE session_id = ?;
```

---

### Analytics Queries

#### Leaderboard by completion time
```sql
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
```

#### Leaderboard by moves
```sql
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
```

#### User analytics summary
```sql
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
```

#### Log analytics event
```sql
INSERT INTO analytics (user_id, session_id, event_type, event_data) 
VALUES (?, ?, ?, ?);
```

#### Get user's analytics events
```sql
SELECT * FROM analytics 
WHERE user_id = ? 
ORDER BY timestamp DESC 
LIMIT 50;
```

---

