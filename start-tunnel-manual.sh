#!/bin/bash
# Start SSH tunnel manually (with password prompt)
# Run this in a separate terminal, then use ./start-all.sh

echo "🔌 Starting SSH Tunnel..."
echo "   You'll be prompted for your password"
echo "   Keep this terminal open!"
echo ""

ssh -L 3306:localhost:3306 ebinitie1@codd.cs.gsu.edu

