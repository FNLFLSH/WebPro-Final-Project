# Exact Steps to Update Database

## You're currently in the bash shell. Follow these steps:

### Step 1: Connect to MySQL
Type this command and press Enter:
```bash
mysql -u ebinitie1 -p
```

When it asks for a password, type: `ebinitie1` (it won't show as you type, that's normal)

### Step 2: You should now see a MySQL prompt like:
```
MariaDB [(none)]>
```

### Step 3: Run these SQL commands one at a time:

```sql
USE ebinitie1;
```

```sql
ALTER TABLE user_preferences 
ADD COLUMN current_level INT DEFAULT 1 AFTER difficulty_preference;
```

```sql
UPDATE user_preferences SET current_level = 1 WHERE current_level IS NULL;
```

```sql
DESCRIBE user_preferences;
```

### Step 4: Exit MySQL
```sql
exit;
```

### Step 5: Exit SSH (if you want)
```bash
exit
```

---

## Quick Copy-Paste Version (All at Once)

If you want to do it all in one command from bash:

```bash
mysql -u ebinitie1 -p ebinitie1 -e "ALTER TABLE user_preferences ADD COLUMN current_level INT DEFAULT 1 AFTER difficulty_preference; UPDATE user_preferences SET current_level = 1 WHERE current_level IS NULL; DESCRIBE user_preferences;"
```

(Enter password: `ebinitie1` when prompted)

