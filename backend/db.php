<?php
// backend/db.php
declare(strict_types=1);

header('Content-Type: application/json');

$DB_HOST = 'localhost';
$DB_NAME = 'ebinitie1';          // from schema.sql
$DB_USER = 'root';     
$DB_PASS = '';  

function getPDO(): PDO {
    static $pdo = null;

    if ($pdo === null) {
        global $DB_HOST, $DB_NAME, $DB_USER, $DB_PASS;

        $dsn = "mysql:host=$DB_HOST;dbname=$DB_NAME;charset=utf8mb4";
        $options = [
            PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
            PDO::ATTR_EMULATE_PREPARES   => false,
        ];

        try {
            $pdo = new PDO($dsn, $DB_USER, $DB_PASS, $options);
        } catch (PDOException $e) {
            http_response_code(500);
            echo json_encode(['error' => 'Database connection failed']);
            exit;
        }
    }

    return $pdo;
}

function jsonResponse($data, int $status = 200): void {
    http_response_code($status);
    echo json_encode($data);
    exit;
}
