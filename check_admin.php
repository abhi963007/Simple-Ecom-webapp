<?php
require_once __DIR__ . '/includes/config.php';

$result = $conn->query('SELECT id, username, email, role, password, is_active, account_locked FROM users WHERE email = "admin@admin.com" OR username = "admin"');

if ($row = $result->fetch_assoc()) {
    echo "Admin account found:\n";
    echo "ID: " . $row['id'] . "\n";
    echo "Username: " . $row['username'] . "\n";
    echo "Email: " . $row['email'] . "\n";
    echo "Role: " . $row['role'] . "\n";
    echo "Is Active: " . ($row['is_active'] ? 'Yes' : 'No') . "\n";
    echo "Account Locked: " . ($row['account_locked'] ? 'Yes' : 'No') . "\n";
    
    // Test password verification
    echo "\nTesting password verification:\n";
    $test_password = 'admin123';
    echo "Password verification result: " . (password_verify($test_password, $row['password']) ? 'Success' : 'Failed') . "\n";
} else {
    echo "No admin account found in database.\n";
} 