#!/bin/bash
# Uninstall SSH tunnel launchd service

LAUNCHD_DIR="$HOME/Library/LaunchAgents"
PLIST_NAME="com.webpro3.tunnel.plist"
TARGET_PLIST="$LAUNCHD_DIR/$PLIST_NAME"

echo "🔌 Uninstalling SSH Tunnel Auto-Start Service..."
echo ""

# Stop the service
launchctl stop com.webpro3.tunnel 2>/dev/null
echo "✓ Service stopped"

# Unload the service
launchctl unload "$TARGET_PLIST" 2>/dev/null
echo "✓ Service unloaded"

# Remove the plist file
if [ -f "$TARGET_PLIST" ]; then
    rm "$TARGET_PLIST"
    echo "✓ Removed plist file"
fi

echo ""
echo "✅ SSH tunnel service uninstalled!"
echo ""
echo "You can now start the tunnel manually with:"
echo "  ./START_DB_TUNNEL.sh"

