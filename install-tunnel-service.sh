#!/bin/bash
# Install SSH tunnel as a macOS launchd service
# This will auto-start the tunnel on login

SCRIPT_DIR="$(cd "$(dirname "${BASH_SOURCE[0]}")" && pwd)"
PLIST_FILE="$SCRIPT_DIR/com.webpro3.tunnel.plist"
LAUNCHD_DIR="$HOME/Library/LaunchAgents"
PLIST_NAME="com.webpro3.tunnel.plist"
TARGET_PLIST="$LAUNCHD_DIR/$PLIST_NAME"

echo "🔌 Installing SSH Tunnel Auto-Start Service..."
echo ""

# Create LaunchAgents directory if it doesn't exist
mkdir -p "$LAUNCHD_DIR"

# Copy plist file
if [ -f "$PLIST_FILE" ]; then
    cp "$PLIST_FILE" "$TARGET_PLIST"
    echo "✓ Copied plist to LaunchAgents"
else
    echo "✗ Error: $PLIST_FILE not found!"
    exit 1
fi

# Load the service
launchctl load "$TARGET_PLIST" 2>/dev/null || launchctl load -w "$TARGET_PLIST"
echo "✓ Service loaded"

# Start the service
launchctl start com.webpro3.tunnel
echo "✓ Service started"

echo ""
echo "✅ SSH tunnel service installed and running!"
echo ""
echo "The tunnel will now:"
echo "  • Start automatically when you log in"
echo "  • Restart automatically if it crashes"
echo "  • Run in the background"
echo ""
echo "To check status:"
echo "  launchctl list | grep webpro3"
echo ""
echo "To stop:"
echo "  launchctl stop com.webpro3.tunnel"
echo ""
echo "To uninstall:"
echo "  ./uninstall-tunnel-service.sh"

