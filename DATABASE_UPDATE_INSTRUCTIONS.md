# Database Update Instructions - Adding Level System

## Step-by-Step Instructions

### Option 1: Using SSH and MySQL Command Line (Recommended)

1. **Open a terminal and SSH into the codd server:**
   ```bash
   ssh ebinitie1@codd.cs.gsu.edu
   ```
   (Enter your password when prompted)

2. **Connect to MySQL:**
   ```bash
   mysql -u ebinitie1 -p
   ```
   (Enter your MySQL password when prompted - should be `ebinitie1`)

3. **Select your database:**
   ```sql
   USE ebinitie1;
   ```

4. **Run the migration to add the current_level column:**
   ```sql
   ALTER TABLE user_preferences 
   ADD COLUMN current_level INT DEFAULT 1 AFTER difficulty_preference;
   ```

5. **Update existing users to have level 1 (if any exist):**
   ```sql
   UPDATE user_preferences SET current_level = 1 WHERE current_level IS NULL;
   ```

6. **Verify the column was added:**
   ```sql
   DESCRIBE user_preferences;
   ```
   You should see `current_level` in the list of columns.

7. **Exit MySQL:**
   ```sql
   exit;
   ```

8. **Exit SSH:**
   ```bash
   exit
   ```

---

### Option 2: Using the Migration File Directly

1. **SSH into the codd server:**
   ```bash
   ssh ebinitie1@codd.cs.gsu.edu
   ```

2. **Navigate to your project directory (if you have the files there), or create the SQL file:**
   ```bash
   # If files are on the server, navigate to the project
   cd ~/your-project-path
   
   # Or create the SQL file directly
   cat > add_level.sql << 'EOF'
   USE ebinitie1;
   
   ALTER TABLE user_preferences 
   ADD COLUMN current_level INT DEFAULT 1 AFTER difficulty_preference;
   
   UPDATE user_preferences SET current_level = 1 WHERE current_level IS NULL;
   EOF
   ```

3. **Run the SQL file:**
   ```bash
   mysql -u ebinitie1 -p ebinitie1 < add_level.sql
   ```
   (Enter password: `ebinitie1`)

4. **Verify it worked:**
   ```bash
   mysql -u ebinitie1 -p ebinitie1 -e "DESCRIBE user_preferences;"
   ```

---

### Option 3: Using PHP Script (From Your Local Machine)

1. **Create a PHP script to run the migration:**
   ```bash
   cat > run_migration.php << 'EOF'
   <?php
   require_once __DIR__ . '/backend/db.php';
   
   $mysqli = getMySQLi();
   
   // Check if column already exists
   $result = $mysqli->query("SHOW COLUMNS FROM user_preferences LIKE 'current_level'");
   if ($result->num_rows > 0) {
       echo "Column 'current_level' already exists. No migration needed.\n";
       exit;
   }
   
   // Add the column
   $sql = "ALTER TABLE user_preferences 
           ADD COLUMN current_level INT DEFAULT 1 AFTER difficulty_preference";
   
   if ($mysqli->query($sql)) {
       echo "✓ Successfully added 'current_level' column\n";
       
       // Update existing records
       $mysqli->query("UPDATE user_preferences SET current_level = 1 WHERE current_level IS NULL");
       echo "✓ Updated existing users to level 1\n";
   } else {
       echo "✗ Error: " . $mysqli->error . "\n";
   }
   ?>
   EOF
   ```

2. **Make sure your SSH tunnel is running:**
   ```bash
   # In a separate terminal, start the tunnel:
   ssh -L 3306:localhost:3306 ebinitie1@codd.cs.gsu.edu
   ```

3. **Run the PHP script:**
   ```bash
   php run_migration.php
   ```

---

## Verification

After running any of the above methods, verify the update worked:

```sql
-- Check the table structure
DESCRIBE user_preferences;

-- Should show:
-- | current_level | int(11) | YES | | 1 | |
```

Or check existing user preferences:

```sql
SELECT user_id, current_level FROM user_preferences;
```

---

## Troubleshooting

**If you get "Duplicate column name" error:**
- The column already exists, you're all set!

**If you get "Table doesn't exist" error:**
- Make sure you're using the correct database: `USE ebinitie1;`

**If connection fails:**
- Make sure your SSH tunnel is running (for local connections)
- Verify your password is correct
- Check that you're connecting to the right server

---

## Quick Copy-Paste Commands

**For SSH + MySQL (fastest method):**

```bash
ssh ebinitie1@codd.cs.gsu.edu
mysql -u ebinitie1 -p ebinitie1
```

Then paste:
```sql
ALTER TABLE user_preferences ADD COLUMN current_level INT DEFAULT 1 AFTER difficulty_preference;
UPDATE user_preferences SET current_level = 1 WHERE current_level IS NULL;
DESCRIBE user_preferences;
exit;
```

Exit SSH:
```bash
exit
```

