<?php
/**
 * Test Database Connection
 * Run this to check if you can connect to the database
 */

echo "🔍 Testing Database Connection...\n\n";

// Test 1: Direct connection
echo "Test 1: Direct connection to codd.cs.gsu.edu\n";
$host1 = 'codd.cs.gsu.edu';
$db = 'ebinitie1';
$user = 'ebinitie1';
$pass = 'ebinitie1';

try {
    $mysqli1 = @new mysqli($host1, $user, $pass, $db, 3306);
    if ($mysqli1->connect_error) {
        echo "  ✗ Failed: " . $mysqli1->connect_error . "\n";
    } else {
        echo "  ✓ SUCCESS! Direct connection works!\n";
        $result = $mysqli1->query("SELECT COUNT(*) as cnt FROM users");
        $row = $result->fetch_assoc();
        echo "  ✓ Found " . $row['cnt'] . " users in database\n";
        $mysqli1->close();
        exit(0);
    }
} catch (Exception $e) {
    echo "  ✗ Failed: Connection refused\n";
}

// Test 2: Localhost (SSH tunnel)
echo "\nTest 2: Localhost connection (SSH tunnel)\n";
try {
    $mysqli2 = @new mysqli('127.0.0.1', $user, $pass, $db, 3306);
    if ($mysqli2->connect_error) {
        echo "  ✗ Failed: " . $mysqli2->connect_error . "\n";
    } else {
        echo "  ✓ SUCCESS! SSH tunnel connection works!\n";
        $result = $mysqli2->query("SELECT COUNT(*) as cnt FROM users");
        $row = $result->fetch_assoc();
        echo "  ✓ Found " . $row['cnt'] . " users in database\n";
        $mysqli2->close();
        exit(0);
    }
} catch (Exception $e) {
    echo "  ✗ Failed: Connection refused (SSH tunnel not running)\n";
}

// Both failed
echo "\n❌ Both connection methods failed!\n\n";
echo "📋 Next Steps:\n";
echo "1. Connect to GSU VPN (if off-campus)\n";
echo "2. Or try SSH tunnel: ssh -L 3306:localhost:3306 ebinitie1@codd.cs.gsu.edu\n";
echo "3. Or connect from on-campus network\n";
echo "4. Then run this test again: php test_db_connection.php\n";

