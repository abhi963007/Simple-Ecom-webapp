<?php
require_once __DIR__ . '/includes/config.php';

// Delete existing admin account
$conn->query('DELETE FROM users WHERE email = "admin@admin.com" OR username = "admin"');

// Force recreation of admin account
$admin_username = ADMIN_USERNAME;
$admin_email = ADMIN_EMAIL;
$hashed_password = password_hash(ADMIN_PASSWORD, PASSWORD_DEFAULT);
$role = 'admin';
$is_active = true;
$account_locked = false;
$login_attempts = 0;

$stmt = $conn->prepare("
    INSERT INTO users (
        username, email, password, role, is_active, 
        login_attempts, account_locked
    ) VALUES (?, ?, ?, ?, ?, ?, ?)
");

$stmt->bind_param(
    "ssssiib",
    $admin_username,
    $admin_email,
    $hashed_password,
    $role,
    $is_active,
    $login_attempts,
    $account_locked
);

if ($stmt->execute()) {
    echo "Admin account reset successfully!\n";
    echo "Email: admin@admin.com\n";
    echo "Password: admin123\n";
} else {
    echo "Error resetting admin account: " . $stmt->error . "\n";
} 