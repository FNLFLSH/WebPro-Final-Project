<?php
// api/register.php
declare(strict_types=1);

require_once __DIR__ . '/../backend/auth.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    jsonResponse(['error' => 'Method not allowed'], 405);
}

$input    = json_decode(file_get_contents('php://input'), true);
$username = trim($input['username'] ?? '');
$email    = trim($input['email'] ?? '');
$password = $input['password'] ?? '';

if ($username === '' || $email === '' || $password === '') {
    jsonResponse(['error' => 'Missing fields'], 400);
}

registerUser($username, $email, $password);
