#!/bin/bash
# Start Everything: Database Tunnel + Frontend + Backend
# This script starts all services needed for the Christmas Puzzle game

SCRIPT_DIR="$(cd "$(dirname "${BASH_SOURCE[0]}")" && pwd)"
cd "$SCRIPT_DIR"

# Colors for output
GREEN='\033[0;32m'
YELLOW='\033[1;33m'
RED='\033[0;31m'
BLUE='\033[0;34m'
NC='\033[0m' # No Color

echo ""
echo "🎄 Christmas Puzzle Game - Starting All Services..."
echo "=================================================="
echo ""

# Function to check if port is in use
check_port() {
    lsof -Pi :$1 -sTCP:LISTEN -t >/dev/null 2>&1
}

# Function to cleanup on exit
cleanup() {
    echo ""
    echo ""
    echo "🛑 Shutting down services..."
    
    # Kill PHP server
    if [ ! -z "$PHP_PID" ]; then
        kill $PHP_PID 2>/dev/null
        echo "✓ PHP server stopped"
    fi
    
    # Kill SSH tunnel (only if we started it)
    if [ "$TUNNEL_STARTED" = "true" ]; then
        if [ ! -z "$TUNNEL_PID" ]; then
            kill $TUNNEL_PID 2>/dev/null
            echo "✓ SSH tunnel stopped"
        fi
    fi
    
    echo ""
    echo "👋 All services stopped. Goodbye!"
    exit 0
}

# Trap Ctrl+C
trap cleanup SIGINT SIGTERM

# Function to prompt for password (hidden input)
prompt_password() {
    local prompt_text="$1"
    local password=""
    
    echo -n "$prompt_text" >&2
    stty -echo
    read -r password
    stty echo
    echo "" >&2
    echo "$password"
}

# ============================================
# Step 1: Check/Start SSH Tunnel
# ============================================
echo "🔌 Checking database tunnel..."

TUNNEL_STARTED="false"
TUNNEL_PID=""
SSH_USER="ebinitie1"
SSH_HOST="codd.cs.gsu.edu"

if check_port 3306; then
    TUNNEL_PID=$(lsof -ti :3306 | head -1)
    echo -e "${GREEN}✓${NC} SSH tunnel already running (PID: $TUNNEL_PID)"
else
    echo "   Starting SSH tunnel..."
    
    # Try passwordless first (SSH keys)
    echo "   Attempting passwordless connection..."
    ssh -f -N -o ServerAliveInterval=60 -o ServerAliveCountMax=3 \
        -o ConnectTimeout=5 \
        -o BatchMode=yes \
        -L 3306:localhost:3306 ${SSH_USER}@${SSH_HOST} 2>/dev/null
    
    sleep 2
    
    if check_port 3306; then
        TUNNEL_PID=$(lsof -ti :3306 | head -1)
        TUNNEL_STARTED="true"
        echo -e "${GREEN}✓${NC} SSH tunnel started with SSH keys (PID: $TUNNEL_PID)"
    else
        # Passwordless failed, prompt for credentials
        echo -e "${YELLOW}⚠${NC}  Passwordless connection failed"
        echo ""
        echo "   SSH credentials required:"
        echo ""
        
        # Prompt for username
        read -p "   SSH Username [${SSH_USER}]: " input_username
        if [ ! -z "$input_username" ]; then
            SSH_USER="$input_username"
        fi
        
        # Prompt for password (hidden)
        SSH_PASSWORD=$(prompt_password "   SSH Password: ")
        
        if [ -z "$SSH_PASSWORD" ]; then
            echo -e "${RED}✗${NC}  Password cannot be empty"
            read -p "Continue without tunnel? (PHP server will start, but DB won't work) (y/n) " -n 1 -r
            echo ""
            if [[ ! $REPLY =~ ^[Yy]$ ]]; then
                exit 1
            fi
        else
            echo ""
            echo "   Connecting with password..."
            
            # Check if expect is available
            if command -v expect &> /dev/null; then
                # Use expect script for automated password entry
                expect_script="$SCRIPT_DIR/start-ssh-tunnel-with-password.exp"
                if [ -f "$expect_script" ]; then
                    chmod +x "$expect_script" 2>/dev/null
                    
                    # Run expect script in background
                    expect "$expect_script" "$SSH_USER" "$SSH_PASSWORD" > /dev/null 2>&1 &
                    EXPECT_PID=$!
                    
                    # Wait a moment for connection
                    sleep 3
                    
                    # Check if tunnel started
                    if check_port 3306; then
                        TUNNEL_PID=$(lsof -ti :3306 | head -1)
                        TUNNEL_STARTED="true"
                        echo -e "${GREEN}✓${NC} SSH tunnel started with password (PID: $TUNNEL_PID)"
                    else
                        # Kill expect process if tunnel didn't start
                        kill $EXPECT_PID 2>/dev/null
                        echo -e "${RED}✗${NC}  Failed to start tunnel"
                        echo "   Check your credentials and network connection"
                        read -p "Continue without tunnel? (y/n) " -n 1 -r
                        echo ""
                        if [[ ! $REPLY =~ ^[Yy]$ ]]; then
                            exit 1
                        fi
                    fi
                else
                    echo -e "${YELLOW}⚠${NC}  Expect script not found, trying alternative method..."
                    # Fall through to manual method
                fi
            fi
            
            # If expect failed or not available, use sshpass or manual method
            if [ "$TUNNEL_STARTED" != "true" ]; then
                if command -v sshpass &> /dev/null; then
                    # Use sshpass
                    sshpass -p "$SSH_PASSWORD" ssh -f -N \
                        -o ServerAliveInterval=60 -o ServerAliveCountMax=3 \
                        -o StrictHostKeyChecking=no \
                        -L 3306:localhost:3306 ${SSH_USER}@${SSH_HOST} 2>/dev/null
                    
                    sleep 2
                    
                    if check_port 3306; then
                        TUNNEL_PID=$(lsof -ti :3306 | head -1)
                        TUNNEL_STARTED="true"
                        echo -e "${GREEN}✓${NC} SSH tunnel started with sshpass (PID: $TUNNEL_PID)"
                    fi
                fi
                
                # If still not started, provide manual instructions
                if [ "$TUNNEL_STARTED" != "true" ]; then
                    echo -e "${YELLOW}⚠${NC}  Could not automate tunnel startup"
                    echo ""
                    echo "   Please start tunnel manually in another terminal:"
                    echo "   ssh -L 3306:localhost:3306 ${SSH_USER}@${SSH_HOST}"
                    echo ""
                    echo "   Or install 'expect' or 'sshpass' for automation:"
                    echo "   brew install expect    # macOS"
                    echo "   brew install hudochenkov/sshpass/sshpass  # macOS"
                    echo ""
                    read -p "Continue without tunnel? (PHP server will start, but DB won't work) (y/n) " -n 1 -r
                    echo ""
                    if [[ ! $REPLY =~ ^[Yy]$ ]]; then
                        exit 1
                    fi
                fi
            fi
        fi
    fi
fi

# ============================================
# Step 2: Check/Start PHP Server (Frontend + Backend)
# ============================================
echo ""
echo "🌐 Checking PHP server..."

if check_port 8000; then
    PHP_PID=$(lsof -ti :8000 | head -1)
    echo -e "${GREEN}✓${NC} PHP server already running (PID: $PHP_PID)"
    echo ""
    echo -e "${BLUE}🌐 Open http://localhost:8000 in your browser${NC}"
    echo ""
    echo "Press Ctrl+C to stop all services"
    echo ""
    
    # Wait for user interrupt
    while true; do
        sleep 1
        # Check if processes are still running
        if [ "$TUNNEL_STARTED" = "true" ] && ! kill -0 $TUNNEL_PID 2>/dev/null; then
            echo ""
            echo -e "${YELLOW}⚠${NC}  SSH tunnel stopped unexpectedly"
        fi
        if ! kill -0 $PHP_PID 2>/dev/null; then
            echo ""
            echo -e "${YELLOW}⚠${NC}  PHP server stopped unexpectedly"
            break
        fi
    done
else
    echo "   Starting PHP server..."
    echo ""
    echo "=================================================="
    echo -e "${GREEN}✅ All services running!${NC}"
    echo "=================================================="
    echo ""
    echo -e "${BLUE}🌐 Frontend + Backend:${NC} http://localhost:8000"
    echo -e "${BLUE}🔌 Database Tunnel:${NC}    Port 3306 (localhost)"
    echo ""
    echo "📋 Services:"
    echo "   • Frontend (PHP server)"
    echo "   • Backend API (same server)"
    echo "   • Database tunnel (SSH)"
    echo ""
    echo "Press Ctrl+C to stop all services"
    echo ""
    echo "=================================================="
    echo ""
    
    # Start PHP server in foreground
    php -S localhost:8000
    
    # If PHP server exits, cleanup
    cleanup
fi

