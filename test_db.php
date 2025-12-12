<?php
// Database connection test (without headers)
$DB_HOST = 'codd.cs.gsu.edu';
$DB_NAME = 'ebinitie1';
$DB_USER = 'ebinitie1';
$DB_PASS = ''; // ADD YOUR PASSWORD HERE

echo "Testing database connection to codd.cs.gsu.edu...\n\n";

try {
    $dsn = "mysql:host=$DB_HOST;dbname=$DB_NAME;charset=utf8mb4";
    $pdo = new PDO($dsn, $DB_USER, $DB_PASS, [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
    ]);
    
    echo "✓ Database connection successful!\n\n";
    
    // Test: Check tables
    $stmt = $pdo->query("SHOW TABLES");
    $tables = $stmt->fetchAll(PDO::FETCH_COLUMN);
    echo "✓ Found " . count($tables) . " tables:\n";
    foreach ($tables as $table) {
        echo "  - $table\n";
    }
    
    // Test: Count users
    $stmt = $pdo->query("SELECT COUNT(*) as count FROM users");
    $result = $stmt->fetch();
    echo "\n✓ Current users in database: " . $result['count'] . "\n";
    
} catch (PDOException $e) {
    echo "✗ Database connection failed!\n";
    echo "Error: " . $e->getMessage() . "\n\n";
    echo "Make sure:\n";
    echo "1. Add your password to this file (line 7)\n";
    echo "2. You can connect to codd.cs.gsu.edu\n";
    echo "3. Database 'ebinitie1' exists\n";
}

