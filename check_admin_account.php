<?php
require_once 'db_connect.php';

$admin_email = "admin@gmail.com";

try {
    // Check if admin exists
    $stmt = $pdo->prepare("SELECT user_id, user_name, user_email, user_password, user_type, user_status FROM pos_user WHERE user_email = ?");
    $stmt->execute([$admin_email]);
    $admin = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if ($admin) {
        echo "Admin account found:\n";
        echo "User ID: " . $admin['user_id'] . "\n";
        echo "Name: " . $admin['user_name'] . "\n";
        echo "Email: " . $admin['user_email'] . "\n";
        echo "User Type: " . $admin['user_type'] . "\n";
        echo "Status: " . $admin['user_status'] . "\n";
        echo "Password Hash: " . $admin['user_password'] . "\n";
        
        // Test password verification
        $test_password = "123";
        if (password_verify($test_password, $admin['user_password'])) {
            echo "\nPassword verification: SUCCESS - '123' is correct\n";
        } else {
            echo "\nPassword verification: FAILED - '123' is incorrect\n";
        }
        
    } else {
        echo "No admin account found with email: $admin_email\n";
        echo "You need to run create_admin_account.php first.\n";
    }
    
} catch (Exception $e) {
    echo "Error checking admin account: " . $e->getMessage() . "\n";
}
?> 