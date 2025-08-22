<?php
require_once 'db_connect.php';

$admin_email = "admin@gmail.com";
$new_password = "123";

try {
    // Check if admin exists
    $stmt = $pdo->prepare("SELECT user_id FROM pos_user WHERE user_email = ?");
    $stmt->execute([$admin_email]);
    $admin = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if ($admin) {
        // Update password
        $stmt = $pdo->prepare("UPDATE pos_user SET user_password = ? WHERE user_email = ?");
        $hashed_password = password_hash($new_password, PASSWORD_DEFAULT);
        $stmt->execute([$hashed_password, $admin_email]);
        
        echo "Admin password updated successfully!\n";
        echo "Email: $admin_email\n";
        echo "New Password: $new_password\n";
        echo "Password Hash: $hashed_password\n";
        
        // Verify the password works
        if (password_verify($new_password, $hashed_password)) {
            echo "\nPassword verification: SUCCESS\n";
        } else {
            echo "\nPassword verification: FAILED\n";
        }
        
    } else {
        echo "No admin account found with email: $admin_email\n";
        echo "Please run create_admin_account.php first.\n";
    }
    
} catch (Exception $e) {
    echo "Error resetting admin password: " . $e->getMessage() . "\n";
}
?> 