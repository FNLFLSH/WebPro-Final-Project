# 🚀 Startup Scripts Guide

## Quick Start

**Start everything (database tunnel + frontend + backend):**
```bash
./start-all.sh
```

This script will:
- ✅ Check if SSH tunnel is already running
- ✅ Try passwordless connection first (SSH keys)
- ✅ If needed, prompt you for SSH username and password
- ✅ Start PHP server (frontend + backend)
- ✅ Show you the URL to open

### SSH Credentials

When prompted by the script:
- **SSH Username:** `ebinitie1` (or press Enter for default)
- **SSH Password:** `lastsemester2026`

## How It Works

### 1. SSH Tunnel Detection
The script first checks if port 3306 is already in use (tunnel running).

### 2. Passwordless Connection (SSH Keys)
If no tunnel is running, it tries to connect using SSH keys (if set up).

### 3. Password Authentication
If SSH keys aren't available, it will:
- Prompt for SSH username (defaults to `ebinitie1`)
- Prompt for SSH password (hidden input, shows `*`)
  - **Default credentials:** Username: `ebinitie1`, Password: `lastsemester2026`
- Use `expect` to automate the password entry
- Start the tunnel in the background

### 4. PHP Server
Once the tunnel is running (or skipped), it starts the PHP server on port 8000.

## Requirements

### Required
- **PHP** - For running the server
- **SSH** - For database tunnel (built into macOS/Linux)

### Optional (for password automation)
- **expect** - Usually pre-installed on macOS
  - If missing: `brew install expect`
- **sshpass** - Alternative to expect (not required if expect works)
  - If needed: `brew install hudochenkov/sshpass/sshpass`

## Troubleshooting

### "expect: command not found"
Install expect:
```bash
brew install expect
```

### "Permission denied" when connecting
- **Default credentials:**
  - Username: `ebinitie1`
  - Password: `lastsemester2026`
- Make sure you're on GSU WiFi or VPN
- Verify the server is accessible: `ping codd.cs.gsu.edu`

### Tunnel starts but PHP server won't start
Check if port 8000 is in use:
```bash
lsof -i :8000
# Kill if needed:
kill -9 $(lsof -ti :8000)
```

### Want to skip password prompts?
Setup SSH keys for passwordless login:
```bash
./setup-ssh-keys.sh
```

## Manual Alternative

If automation doesn't work, you can start services manually:

**Terminal 1 - SSH Tunnel:**
```bash
ssh -L 3306:localhost:3306 ebinitie1@codd.cs.gsu.edu
```

**Terminal 2 - PHP Server:**
```bash
php -S localhost:8000
```

## Script Files

| File | Purpose |
|------|---------|
| `start-all.sh` | **Main script** - Starts everything |
| `start-ssh-tunnel-with-password.exp` | Expect script for password automation |
| `setup-ssh-keys.sh` | One-time SSH key setup |
| `start-dev.sh` | Simpler version (requires SSH keys) |
| `start-tunnel-manual.sh` | Manual tunnel starter |

