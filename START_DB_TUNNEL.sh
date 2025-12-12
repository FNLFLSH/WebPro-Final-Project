#!/bin/bash
# Start SSH tunnel for MySQL connection
# Keep this terminal open while using the app

echo "🔌 Starting SSH tunnel for MySQL..."
echo "   This will forward local port 3306 to codd.cs.gsu.edu:3306"
echo "   Keep this terminal open!"
echo ""

ssh -L 3306:localhost:3306 ebinitie1@codd.cs.gsu.edu

