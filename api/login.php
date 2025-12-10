<?php
// api/login.php
declare(strict_types=1);

require_once __DIR__ . '/../backend/auth.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    jsonResponse(['error' => 'Method not allowed'], 405);
}

$input      = json_decode(file_get_contents('php://input'), true);
$identifier = trim($input['identifier'] ?? $input['email'] ?? $input['username'] ?? '');
$password   = $input['password'] ?? '';

if ($identifier === '' || $password === '') {
    jsonResponse(['error' => 'Missing fields'], 400);
}

loginUser($identifier, $password);
