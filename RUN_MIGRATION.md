# Database Migration Instructions - Coins & Power-ups

## Quick Steps to Run Migration

### Option 1: Copy and Paste SQL Commands

1. **SSH into the database server:**
   ```bash
   ssh ebinitie1@codd.cs.gsu.edu
   # Password: lastsemester2026
   ```

2. **Connect to MySQL:**
   ```bash
   mysql -u ebinitie1 -p
   # Password: ebinitie1
   ```

3. **Run the migration SQL:**
   ```sql
   USE ebinitie1;
   
   -- Add coins column to user_preferences
   -- Note: If you get "Duplicate column name" error, the column already exists - that's okay!
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
   
   -- Verify the changes
   DESCRIBE user_preferences;
   DESCRIBE user_powerups;
   ```

4. **Exit MySQL and SSH:**
   ```sql
   exit;
   ```
   ```bash
   exit
   ```

### Option 2: Copy Migration File to Server

1. **Copy the migration file to the server:**
   ```bash
   scp database/migrations/003_add_coins_and_powerups.sql ebinitie1@codd.cs.gsu.edu:~/
   # Password: lastsemester2026
   ```

2. **SSH into the server:**
   ```bash
   ssh ebinitie1@codd.cs.gsu.edu
   # Password: lastsemester2026
   ```

3. **Run the migration:**
   ```bash
   mysql -u ebinitie1 -p ebinitie1 < ~/003_add_coins_and_powerups.sql
   # Password: ebinitie1
   ```

4. **Verify:**
   ```bash
   mysql -u ebinitie1 -p ebinitie1 -e "DESCRIBE user_preferences; DESCRIBE user_powerups;"
   # Password: ebinitie1
   ```

## What This Migration Does

1. ✅ Adds `coins` column to `user_preferences` table (default: 0)
2. ✅ Creates `user_powerups` table to track purchased power-ups
3. ✅ Initializes coins to 0 for existing users
4. ✅ Sets up proper indexes and foreign keys

## Verification

After running the migration, you should see:

**user_preferences table:**
- Should have a `coins` column (INT, DEFAULT 0)

**user_powerups table:**
- Should exist with columns: id, user_id, powerup_type, quantity, purchased_at

## Troubleshooting

### "Column already exists" error
- The `coins` column might already exist. This is okay - the migration uses `IF NOT EXISTS` where possible.

### "Table already exists" error
- The `user_powerups` table might already exist. This is okay - the migration uses `CREATE TABLE IF NOT EXISTS`.

### "Access denied" error
- Make sure you're using the correct MySQL password: `ebinitie1`
- Make sure you're connected to GSU WiFi or VPN

