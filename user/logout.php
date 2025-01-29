<?php
require_once __DIR__ . '/../includes/utils.php';

// Start session if not already started
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Unset all session variables
$_SESSION = array();

// Destroy the session
session_destroy();

// Redirect to login page
redirect('/user/login.php', 'You have been logged out successfully.', 'success'); 