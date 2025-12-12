<?php
require_once __DIR__ . '/../backend/session.php';

header('Content-Type: application/json');

// Destroy session
session_destroy();

// Clear any user cache if needed
if (function_exists('clearAllUserCache')) {
    clearAllUserCache();
}

// Return JSON response for AJAX calls, or redirect for direct access
if ($_SERVER['REQUEST_METHOD'] === 'POST' || 
    (isset($_SERVER['HTTP_X_REQUESTED_WITH']) && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) === 'xmlhttprequest')) {
    echo json_encode(['success' => true, 'message' => 'Logged out successfully']);
    exit();
}

// Redirect to login page for direct browser access
header("Location: /frontend/login.php");
exit();

