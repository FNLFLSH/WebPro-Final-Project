# 🚀 Auto-Start Database Tunnel Guide

This guide shows you how to automatically start the SSH tunnel so you don't have to run it manually every time.

## Option 1: Auto-Start on Login (Recommended)

This will start the tunnel automatically when you log into your Mac.

### Step 1: Setup SSH Keys (One-time setup)

Run this script to enable passwordless login:

```bash
./setup-ssh-keys.sh
```

This will:
- Generate an SSH key if you don't have one
- Copy it to the server (you'll enter your password once)
- Enable automatic connections

### Step 2: Install Auto-Start Service

```bash
./install-tunnel-service.sh
```

The tunnel will now:
- ✅ Start automatically when you log in
- ✅ Restart automatically if it crashes
- ✅ Run in the background

### Step 3: Verify It's Running

```bash
launchctl list | grep webpro3
```

You should see `com.webpro3.tunnel` in the list.

### To Stop/Uninstall

```bash
# Stop temporarily
launchctl stop com.webpro3.tunnel

# Start again
launchctl start com.webpro3.tunnel

# Uninstall completely
./uninstall-tunnel-service.sh
```

---

## Option 2: Quick Start Script (Alternative)

If you prefer to start manually but want it easier:

```bash
./start-dev.sh
```

This script will:
- ✅ Check if tunnel is already running
- ✅ Start tunnel in background if needed
- ✅ Start PHP server
- ✅ Show you the URL to open

**Note:** This requires SSH keys to be set up (run `./setup-ssh-keys.sh` first).

---

## Option 3: Manual (Current Method)

If you prefer to keep it manual:

```bash
# Terminal 1: SSH Tunnel
./START_DB_TUNNEL.sh

# Terminal 2: PHP Server
php -S localhost:8000
```

---

## Troubleshooting

### "Permission denied (publickey)"

You need to set up SSH keys:
```bash
./setup-ssh-keys.sh
```

### "Connection refused"

Make sure you're on GSU WiFi or VPN.

### Check if tunnel is running

```bash
lsof -i :3306
```

### View tunnel logs

```bash
# If using launchd service
tail -f tunnel.log
tail -f tunnel.error.log
```

### Kill existing tunnel

```bash
# Find and kill
lsof -ti :3306 | xargs kill -9

# Or if using launchd
launchctl stop com.webpro3.tunnel
```

---

## Quick Reference

| Task | Command |
|------|---------|
| Setup SSH keys | `./setup-ssh-keys.sh` |
| Install auto-start | `./install-tunnel-service.sh` |
| Uninstall auto-start | `./uninstall-tunnel-service.sh` |
| Quick start (tunnel + server) | `./start-dev.sh` |
| Check tunnel status | `lsof -i :3306` |
| Check launchd service | `launchctl list \| grep webpro3` |

