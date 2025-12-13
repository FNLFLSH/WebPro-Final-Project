#!/bin/bash
# Setup SSH keys for passwordless login to codd.cs.gsu.edu
# This is required for automatic tunnel startup

echo "🔑 Setting up SSH keys for passwordless login..."
echo ""

# Check if SSH key already exists
if [ -f "$HOME/.ssh/id_rsa" ] || [ -f "$HOME/.ssh/id_ed25519" ]; then
    echo "✓ SSH key found"
    KEY_FILE="$HOME/.ssh/id_rsa"
    if [ ! -f "$KEY_FILE" ]; then
        KEY_FILE="$HOME/.ssh/id_ed25519"
    fi
else
    echo "📝 Generating new SSH key..."
    ssh-keygen -t ed25519 -f "$HOME/.ssh/id_ed25519" -N "" -C "webpro3-tunnel"
    KEY_FILE="$HOME/.ssh/id_ed25519"
    echo "✓ SSH key generated"
fi

echo ""
echo "📤 Copying public key to codd.cs.gsu.edu..."
echo "   (You'll need to enter your password one last time)"
echo ""

# Copy public key to server
ssh-copy-id -i "$KEY_FILE.pub" ebinitie1@codd.cs.gsu.edu

if [ $? -eq 0 ]; then
    echo ""
    echo "✅ SSH key setup complete!"
    echo ""
    echo "You can now:"
    echo "  1. Run ./install-tunnel-service.sh to auto-start on login"
    echo "  2. Or use ./start-dev.sh to start tunnel + PHP server"
    echo ""
    echo "Test passwordless login:"
    echo "  ssh ebinitie1@codd.cs.gsu.edu"
else
    echo ""
    echo "✗ Failed to copy SSH key"
    echo "  Make sure you're on GSU WiFi or VPN"
    echo "  You can manually copy the key:"
    echo "    cat $KEY_FILE.pub | ssh ebinitie1@codd.cs.gsu.edu 'mkdir -p ~/.ssh && cat >> ~/.ssh/authorized_keys'"
fi

