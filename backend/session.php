<?php
// backend/session.php
// Session configuration for persistent login

// Configure session to persist across browser restarts
// Set cookie to expire in 30 days (persistent session)
ini_set('session.cookie_lifetime', 2592000); // 30 days in seconds
ini_set('session.gc_maxlifetime', 2592000);   // 30 days in seconds
ini_set('session.cookie_httponly', 1);        // Security: prevent JavaScript access
ini_set('session.cookie_samesite', 'Lax');    // CSRF protection

// Start session if not already started
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

