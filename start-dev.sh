#!/bin/bash
# Combined startup script: Starts both SSH tunnel and PHP server
# This is an alternative to the launchd service

SCRIPT_DIR="$(cd "$(dirname "${BASH_SOURCE[0]}")" && pwd)"
cd "$SCRIPT_DIR"

echo "🚀 Starting WebPro3 Development Environment..."
echo ""

# Check if tunnel is already running
if lsof -Pi :3306 -sTCP:LISTEN -t >/dev/null 2>&1 ; then
    echo "✓ SSH tunnel already running on port 3306"
else
    echo "🔌 Starting SSH tunnel in background..."
    ssh -f -N -L 3306:localhost:3306 ebinitie1@codd.cs.gsu.edu
    sleep 2
    
    if lsof -Pi :3306 -sTCP:LISTEN -t >/dev/null 2>&1 ; then
        echo "✓ SSH tunnel started successfully"
    else
        echo "✗ Failed to start SSH tunnel"
        echo "  Make sure you're on GSU WiFi or VPN"
        exit 1
    fi
fi

# Check if PHP server is already running
if lsof -Pi :8000 -sTCP:LISTEN -t >/dev/null 2>&1 ; then
    echo "✓ PHP server already running on port 8000"
    echo ""
    echo "🌐 Open http://localhost:8000 in your browser"
else
    echo "🌐 Starting PHP server..."
    echo ""
    echo "✅ Development environment ready!"
    echo "   • SSH tunnel: running on port 3306"
    echo "   • PHP server: starting on port 8000"
    echo ""
    echo "🌐 Open http://localhost:8000 in your browser"
    echo ""
    echo "Press Ctrl+C to stop both servers"
    echo ""
    
    php -S localhost:8000
fi

