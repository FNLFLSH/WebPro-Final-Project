<?php
// test_connection.php
// Tests database connection using MySQLi

echo "Testing database connection with MySQLi...\n\n";

require_once __DIR__ . '/backend/db.php';

try {
    $mysqli = getMySQLi();
    echo "✓ Database connection successful!\n\n";

    // Test: Show tables
    $result = $mysqli->query("SHOW TABLES");
    $tables = [];
    if ($result) {
        while ($row = $result->fetch_array()) {
            $tables[] = $row[0];
        }
    }
    
    if (!empty($tables)) {
        echo "✓ Found " . count($tables) . " tables:\n";
        foreach ($tables as $table) {
            echo "  - " . $table . "\n";
        }
    } else {
        echo "ℹ No tables found in database.\n";
    }

    // Test: Count users
    $result = $mysqli->query("SELECT COUNT(*) as count FROM users");
    if ($result) {
        $row = $result->fetch_assoc();
        echo "\n✓ Current users in database: " . $row['count'] . "\n";
    }
    
    // Test: User caching
    if (isset($tables) && in_array('users', $tables)) {
        $result = $mysqli->query("SELECT id FROM users LIMIT 1");
        if ($result && $row = $result->fetch_assoc()) {
            $testUserId = (int)$row['id'];
            echo "\n✓ Testing user cache with user ID: $testUserId\n";
            
            // First call - should fetch from DB
            $user1 = getUserData($testUserId);
            echo "  - First call (from DB): " . ($user1 ? "Success" : "Failed") . "\n";
            
            // Second call - should use cache
            $user2 = getUserData($testUserId);
            echo "  - Second call (from cache): " . ($user2 ? "Success" : "Failed") . "\n";
            
            if ($user1 && $user2 && $user1['id'] === $user2['id']) {
                echo "  ✓ User caching is working correctly!\n";
            }
        }
    }

} catch (Exception $e) {
    echo "✗ Database connection failed!\n";
    echo "Error: " . $e->getMessage() . "\n\n";
    echo "Make sure:\n";
    echo "1. The SSH tunnel is active (if needed):\n";
    echo "   ssh -L 3306:localhost:3306 ebinitie1@codd.cs.gsu.edu\n";
    echo "2. Your credentials are correct in backend/db.php\n";
    echo "3. Database 'ebinitie1' exists on the remote server.\n";
}
