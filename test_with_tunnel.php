<?php
// Test database connection through SSH tunnel
// Make sure SSH tunnel is running: ssh -L 3306:localhost:3306 ebinitie1@codd.cs.gsu.edu

$DB_HOST = '127.0.0.1';
$DB_NAME = 'ebinitie1';
$DB_USER = 'ebinitie1';
$DB_PASS = 'ebinitie1';

echo "Testing database connection through SSH tunnel (localhost:3306)...\n\n";

try {
    $dsn = "mysql:host=$DB_HOST;port=3306;dbname=$DB_NAME;charset=utf8mb4";
    $pdo = new PDO($dsn, $DB_USER, $DB_PASS, [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
    ]);
    
    echo "✓ Database connection successful through tunnel!\n\n";
    
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
    echo "1. SSH tunnel is running: ssh -L 3306:localhost:3306 ebinitie1@codd.cs.gsu.edu\n";
    echo "2. Keep the SSH tunnel terminal open\n";
    echo "3. Password is correct\n";
}

