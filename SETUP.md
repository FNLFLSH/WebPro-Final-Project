# Christmas Puzzle Game - Setup & Run Guide

## 🚀 Quick Start

### Prerequisites
- PHP 8.4+ installed
- Access to GSU WiFi or VPN
- MySQL database on `codd.cs.gsu.edu`

### Step 1: Database Setup

**If you're on GSU WiFi or have SSH access:**

1. **SSH into the database server:**
   ```bash
   ssh ebinitie1@codd.cs.gsu.edu
   ```

2. **Connect to MySQL:**
   ```bash
   mysql -u ebinitie1 -p
   ```
   (Password: `ebinitie1`)

3. **Run the setup SQL:**
   ```sql
   USE ebinitie1;
   
   -- Add current_level column if it doesn't exist
   ALTER TABLE user_preferences 
   ADD COLUMN IF NOT EXISTS current_level INT DEFAULT 1 AFTER difficulty_preference;
   
   -- Update existing users
   UPDATE user_preferences SET current_level = 1 WHERE current_level IS NULL;
   
   -- Verify
   DESCRIBE user_preferences;
   ```

4. **Exit MySQL and SSH:**
   ```sql
   exit;
   ```
   ```bash
   exit
   ```

### Step 2: Start SSH Tunnel (Required for Database)

**Open a terminal and keep it running:**
```bash
ssh -L 3306:localhost:3306 ebinitie1@codd.cs.gsu.edu
```

**Important:** Keep this terminal open while using the app. This creates a tunnel so your PHP app can connect to the database.

### Step 3: Start PHP Server

**In a different terminal:**
```bash
cd /Users/edge/Desktop/WebPro3
php -S localhost:8000 -t .
```

### Step 4: Open in Browser

Navigate to: **http://localhost:8000**

---

## 📋 Complete Setup Checklist

- [ ] Database tables created (see `database/schema.sql`)
- [ ] `current_level` column added to `user_preferences` table
- [ ] SSH tunnel running (port 3306 forwarded)
- [ ] PHP server running on port 8000
- [ ] Browser opened to `http://localhost:8000`

---

## 🔧 Troubleshooting

### "Database connection failed"

**Solution:** Make sure the SSH tunnel is running:
```bash
# Check if tunnel is running
ps aux | grep "ssh.*3306" | grep -v grep

# If not running, start it:
ssh -L 3306:localhost:3306 ebinitie1@codd.cs.gsu.edu
```

### "Port 3306 already in use"

**Solution:** Kill existing tunnel:
```bash
pkill -f "ssh.*3306"
# Then start it again
```

### "Connection refused" on SSH

**Solution:** 
- Make sure you're on GSU WiFi or connected to GSU VPN
- Verify you can ping the server: `ping codd.cs.gsu.edu`

### PHP Server won't start

**Solution:**
```bash
# Kill existing PHP servers
pkill -f "php -S"

# Start fresh
php -S localhost:8000 -t .
```

---

## 📁 Project Structure

```
WebPro3/
├── frontend/          # PHP pages (login, home, game, etc.)
├── backend/           # PHP backend logic (auth, db, puzzle)
├── api/               # API endpoints (REST)
├── public/assets/     # Static files (CSS, JS, images)
├── database/          # SQL schemas and migrations
└── docs/              # Documentation
```

---

## 🎮 How to Use

1. **Register/Login** - Create an account or log in
2. **Select Level** - Choose from unlocked levels
3. **Play** - Solve the puzzle by sliding tiles
4. **Progress** - Beat levels to unlock new ones
5. **Replay** - Replay any level you've completed

---

## 🔐 Database Connection Info

- **Server:** `codd.cs.gsu.edu` (via SSH tunnel: `127.0.0.1:3306`)
- **Database:** `ebinitie1`
- **Username:** `ebinitie1`
- **Password:** `ebinitie1`

---

## 📝 Notes

- The SSH tunnel must stay running while using the app
- Database connection uses MySQLi (as per requirements)
- User progress (levels) persists across sessions
- All levels are locked until you complete the previous one
- You can replay any level you've already beaten

---

## 🆘 Need Help?

1. Check that SSH tunnel is running
2. Verify PHP server is running
3. Check browser console (F12) for errors
4. Verify database connection: `php test_db_connection.php`

