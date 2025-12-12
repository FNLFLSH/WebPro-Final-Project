#!/bin/bash
# SSH Tunnel script for database connection
# Run this in a separate terminal and keep it open

echo "Starting SSH tunnel to codd.cs.gsu.edu..."
echo "This will forward local port 3306 to the database server"
echo "Keep this terminal open while testing!"
echo ""

ssh -L 3306:localhost:3306 ebinitie1@codd.cs.gsu.edu

