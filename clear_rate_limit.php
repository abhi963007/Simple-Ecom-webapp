<?php
require_once __DIR__ . '/includes/config.php';

// Clear security log entries for login attempts
$conn->query('DELETE FROM security_log WHERE action = "LOGIN_ATTEMPT"');

// Reset login attempts for all users
$conn->query('UPDATE users SET login_attempts = 0, account_locked = FALSE WHERE 1');

echo "Rate limiting records cleared successfully!\n";
echo "You can now try to log in again.";
?> 