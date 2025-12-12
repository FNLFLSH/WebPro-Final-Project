<?php
// backend/db.php
declare(strict_types=1);

// Only set JSON header if not already sent (for CLI scripts)
if (!headers_sent() && php_sapi_name() !== 'cli') {
    header('Content-Type: application/json');
}

// Try direct connection first (if SSH tunnel fails)
$DB_HOST = 'codd.cs.gsu.edu';  // Direct connection to database server
$DB_NAME = 'ebinitie1';
$DB_USER = 'ebinitie1';     
$DB_PASS = 'ebinitie1';  

/**
 * Get MySQLi connection (cached per request)
 */
function getMySQLi(): mysqli {
    static $mysqli = null;

    if ($mysqli === null) {
        global $DB_HOST, $DB_NAME, $DB_USER, $DB_PASS;

        try {
            // Try direct connection first
            $mysqli = @new mysqli($DB_HOST, $DB_USER, $DB_PASS, $DB_NAME, 3306);
            
            // If direct connection fails, try localhost (SSH tunnel)
            if ($mysqli->connect_error && $DB_HOST !== '127.0.0.1') {
                $mysqli = @new mysqli('127.0.0.1', $DB_USER, $DB_PASS, $DB_NAME, 3306);
            }
            
            if ($mysqli->connect_error) {
                http_response_code(500);
                echo json_encode(['error' => 'Database connection failed: ' . $mysqli->connect_error . '. Make sure you are connected to GSU network or VPN.']);
                exit;
            }
            
            $mysqli->set_charset('utf8mb4');
        } catch (Exception $e) {
            http_response_code(500);
            echo json_encode(['error' => 'Database connection failed']);
            exit;
        }
    }

    return $mysqli;
}

/**
 * User data cache (in-memory, per request)
 * Key: user_id, Value: user data array
 */
$userCache = [];

/**
 * Get user data with caching
 * Fetches from database if not in cache, then caches the result
 */
function getUserData(int $userId): ?array {
    global $userCache;
    
    // Check cache first
    if (isset($userCache[$userId])) {
        return $userCache[$userId];
    }
    
    // Fetch from database
    $mysqli = getMySQLi();
    $stmt = $mysqli->prepare("SELECT id, username, email, password_hash, created_at, updated_at FROM users WHERE id = ?");
    
    if (!$stmt) {
        return null;
    }
    
    $stmt->bind_param("i", $userId);
    $stmt->execute();
    $result = $stmt->get_result();
    $user = $result->fetch_assoc();
    $stmt->close();
    
    if ($user) {
        // Cache the user data
        $userCache[$userId] = $user;
        return $user;
    }
    
    return null;
}

/**
 * Clear user from cache (call after updates)
 */
function clearUserCache(int $userId): void {
    global $userCache;
    unset($userCache[$userId]);
}

/**
 * Clear all user cache
 */
function clearAllUserCache(): void {
    global $userCache;
    $userCache = [];
}

/**
 * Helper function for prepared statements with MySQLi
 * Returns the statement object
 */
function prepareQuery(string $query): mysqli_stmt {
    $mysqli = getMySQLi();
    $stmt = $mysqli->prepare($query);
    
    if (!$stmt) {
        http_response_code(500);
        echo json_encode(['error' => 'Database query preparation failed: ' . $mysqli->error]);
        exit;
    }
    
    return $stmt;
}

/**
 * Execute a prepared statement and return results as associative array
 */
function executeQuery(mysqli_stmt $stmt): array {
    $stmt->execute();
    $result = $stmt->get_result();
    $rows = [];
    
    while ($row = $result->fetch_assoc()) {
        $rows[] = $row;
    }
    
    return $rows;
}

/**
 * Execute a prepared statement and return single row as associative array
 */
function executeQuerySingle(mysqli_stmt $stmt): ?array {
    $stmt->execute();
    $result = $stmt->get_result();
    return $result->fetch_assoc() ?: null;
}

/**
 * Get last insert ID
 */
function getLastInsertId(): int {
    return (int)getMySQLi()->insert_id;
}

function jsonResponse($data, int $status = 200): void {
    http_response_code($status);
    echo json_encode($data);
    exit;
}
