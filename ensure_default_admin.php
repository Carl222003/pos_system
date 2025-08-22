<?php
require_once 'db_connect.php';

// Default admin account details
$default_admin = [
    'name' => 'Admin',
    'email' => 'admin@gmail.com',
    'password' => '123',
    'contact' => '1234567890',
    'type' => 'Admin',
    'status' => 'Active'
];

try {
    // Check if default admin exists
    $stmt = $pdo->prepare("SELECT user_id, user_password FROM pos_user WHERE user_email = ?");
    $stmt->execute([$default_admin['email']]);
    $existing_admin = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if ($existing_admin) {
        // Admin exists, update password to ensure it's correct
        $hashed_password = password_hash($default_admin['password'], PASSWORD_DEFAULT);
        $stmt = $pdo->prepare("UPDATE pos_user SET user_password = ?, user_status = ? WHERE user_email = ?");
        $stmt->execute([$hashed_password, $default_admin['status'], $default_admin['email']]);
        
        echo "Default admin account updated successfully!\n";
        echo "User ID: " . $existing_admin['user_id'] . "\n";
    } else {
        // Create new default admin
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
                :user_type,
                :contact_number,
                :user_status,
                NOW()
            )
        ");
        
        $hashed_password = password_hash($default_admin['password'], PASSWORD_DEFAULT);
        $stmt->execute([
            'user_name' => $default_admin['name'],
            'user_email' => $default_admin['email'],
            'user_password' => $hashed_password,
            'user_type' => $default_admin['type'],
            'contact_number' => $default_admin['contact'],
            'user_status' => $default_admin['status']
        ]);
        
        $user_id = $pdo->lastInsertId();
        echo "Default admin account created successfully!\n";
        echo "User ID: " . $user_id . "\n";
    }
    
    // Verify the account works
    $stmt = $pdo->prepare("SELECT user_id, user_name, user_email, user_password, user_type, user_status FROM pos_user WHERE user_email = ?");
    $stmt->execute([$default_admin['email']]);
    $admin = $stmt->fetch(PDO::FETCH_ASSOC);
    
    echo "\n=== DEFAULT ADMIN ACCOUNT DETAILS ===\n";
    echo "User ID: " . $admin['user_id'] . "\n";
    echo "Name: " . $admin['user_name'] . "\n";
    echo "Email: " . $admin['user_email'] . "\n";
    echo "User Type: " . $admin['user_type'] . "\n";
    echo "Status: " . $admin['user_status'] . "\n";
    echo "Password: " . $default_admin['password'] . "\n";
    
    // Test password verification
    if (password_verify($default_admin['password'], $admin['user_password'])) {
        echo "\n✅ Password verification: SUCCESS\n";
        echo "✅ Default admin account is ready to use!\n";
    } else {
        echo "\n❌ Password verification: FAILED\n";
        echo "❌ There's an issue with the password hash.\n";
    }
    
    echo "\n=== LOGIN CREDENTIALS ===\n";
    echo "Email: " . $default_admin['email'] . "\n";
    echo "Password: " . $default_admin['password'] . "\n";
    echo "\nYou can now login to your POS system with these credentials.\n";
    
} catch (Exception $e) {
    echo "Error ensuring default admin account: " . $e->getMessage() . "\n";
}
?> 