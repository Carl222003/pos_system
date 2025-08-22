<?php
require_once 'db_connect.php';

// Admin account details
$admin_name = "Admin";
$admin_email = "admin@gmail.com";
$admin_password = "123";
$admin_contact = "1234567890";

try {
    // Check if admin already exists
    $stmt = $pdo->prepare("SELECT COUNT(*) FROM pos_user WHERE user_email = ?");
    $stmt->execute([$admin_email]);
    
    if ($stmt->fetchColumn() > 0) {
        echo "Admin account already exists with email: $admin_email\n";
        exit;
    }
    
    // Insert admin account
    $stmt = $pdo->prepare("
        INSERT INTO pos_user (
            user_name,
            user_email,
            user_password,
            user_type,
            contact_number,
            user_status,
            created_at
        ) VALUES (
            :user_name,
            :user_email,
            :user_password,
            'Admin',
            :contact_number,
            'Active',
            NOW()
        )
    ");
    
    $stmt->execute([
        'user_name' => $admin_name,
        'user_email' => $admin_email,
        'user_password' => password_hash($admin_password, PASSWORD_DEFAULT),
        'contact_number' => $admin_contact
    ]);
    
    $user_id = $pdo->lastInsertId();
    
    echo "Admin account created successfully!\n";
    echo "User ID: $user_id\n";
    echo "Email: $admin_email\n";
    echo "Password: $admin_password\n";
    echo "You can now login with these credentials.\n";
    
} catch (Exception $e) {
    echo "Error creating admin account: " . $e->getMessage() . "\n";
}
?> 