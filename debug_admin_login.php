<?php
echo "=== ADMIN LOGIN DEBUGGING SCRIPT ===\n\n";

// Test 1: Check if database connection works
echo "1. Testing database connection...\n";
try {
    require_once 'db_connect.php';
    echo "✅ Database connection successful\n";
} catch (Exception $e) {
    echo "❌ Database connection failed: " . $e->getMessage() . "\n";
    exit;
}

// Test 2: Check if pos_user table exists
echo "\n2. Checking if pos_user table exists...\n";
try {
    $stmt = $pdo->query("SHOW TABLES LIKE 'pos_user'");
    if ($stmt->rowCount() > 0) {
        echo "✅ pos_user table exists\n";
    } else {
        echo "❌ pos_user table does not exist\n";
        exit;
    }
} catch (Exception $e) {
    echo "❌ Error checking table: " . $e->getMessage() . "\n";
    exit;
}

// Test 3: Check table structure
echo "\n3. Checking pos_user table structure...\n";
try {
    $stmt = $pdo->query("DESCRIBE pos_user");
    $columns = $stmt->fetchAll(PDO::FETCH_ASSOC);
    echo "Table columns:\n";
    foreach ($columns as $column) {
        echo "  - " . $column['Field'] . " (" . $column['Type'] . ")\n";
    }
} catch (Exception $e) {
    echo "❌ Error checking table structure: " . $e->getMessage() . "\n";
}

// Test 4: Check if admin account exists
echo "\n4. Checking for admin account...\n";
$admin_email = "admin@gmail.com";
try {
    $stmt = $pdo->prepare("SELECT * FROM pos_user WHERE user_email = ?");
    $stmt->execute([$admin_email]);
    $admin = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if ($admin) {
        echo "✅ Admin account found\n";
        echo "  User ID: " . $admin['user_id'] . "\n";
        echo "  Name: " . $admin['user_name'] . "\n";
        echo "  Email: " . $admin['user_email'] . "\n";
        echo "  User Type: " . $admin['user_type'] . "\n";
        echo "  Status: " . $admin['user_status'] . "\n";
        echo "  Password Hash: " . substr($admin['user_password'], 0, 20) . "...\n";
    } else {
        echo "❌ No admin account found with email: $admin_email\n";
        echo "Creating admin account now...\n";
        
        // Create admin account
        $stmt = $pdo->prepare("
            INSERT INTO pos_user (
                user_name, user_email, user_password, user_type, 
                contact_number, user_status, created_at
            ) VALUES (
                'Admin', 'admin@gmail.com', ?, 'Admin', 
                '1234567890', 'Active', NOW()
            )
        ");
        
        $hashed_password = password_hash('123', PASSWORD_DEFAULT);
        $stmt->execute([$hashed_password]);
        
        echo "✅ Admin account created successfully\n";
        
        // Fetch the newly created account
        $stmt = $pdo->prepare("SELECT * FROM pos_user WHERE user_email = ?");
        $stmt->execute([$admin_email]);
        $admin = $stmt->fetch(PDO::FETCH_ASSOC);
    }
} catch (Exception $e) {
    echo "❌ Error checking admin account: " . $e->getMessage() . "\n";
    exit;
}

// Test 5: Test password verification
echo "\n5. Testing password verification...\n";
try {
    $test_password = "123";
    if (password_verify($test_password, $admin['user_password'])) {
        echo "✅ Password verification successful\n";
    } else {
        echo "❌ Password verification failed\n";
        echo "Updating password...\n";
        
        // Update password
        $new_hash = password_hash($test_password, PASSWORD_DEFAULT);
        $stmt = $pdo->prepare("UPDATE pos_user SET user_password = ? WHERE user_email = ?");
        $stmt->execute([$new_hash, $admin_email]);
        
        echo "✅ Password updated successfully\n";
        
        // Test again
        if (password_verify($test_password, $new_hash)) {
            echo "✅ Password verification now successful\n";
        } else {
            echo "❌ Password verification still failing\n";
        }
    }
} catch (Exception $e) {
    echo "❌ Error testing password: " . $e->getMessage() . "\n";
}

// Test 6: Check login_attempts table
echo "\n6. Checking login_attempts table...\n";
try {
    $stmt = $pdo->query("SHOW TABLES LIKE 'login_attempts'");
    if ($stmt->rowCount() > 0) {
        echo "✅ login_attempts table exists\n";
        
        // Check for recent attempts
        $stmt = $pdo->prepare("SELECT COUNT(*) FROM login_attempts WHERE email = ? AND timestamp > DATE_SUB(NOW(), INTERVAL 2 MINUTE)");
        $stmt->execute([$admin_email]);
        $attempts = $stmt->fetchColumn();
        echo "  Recent login attempts: $attempts\n";
        
        if ($attempts > 0) {
            echo "  Clearing recent attempts...\n";
            $stmt = $pdo->prepare("DELETE FROM login_attempts WHERE email = ?");
            $stmt->execute([$admin_email]);
            echo "  ✅ Login attempts cleared\n";
        }
    } else {
        echo "⚠️  login_attempts table does not exist (this is okay)\n";
    }
} catch (Exception $e) {
    echo "⚠️  Error checking login_attempts: " . $e->getMessage() . "\n";
}

// Test 7: Simulate login process
echo "\n7. Simulating login process...\n";
try {
    // Simulate the exact login logic from login.php
    $email = "admin@gmail.com";
    $password = "123";
    
    $stmt = $pdo->prepare("SELECT * FROM pos_user WHERE user_email = ? AND user_status = 'Active'");
    $stmt->execute([$email]);
    $user = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if ($user) {
        echo "✅ User found in database\n";
        if (password_verify($password, $user['user_password'])) {
            echo "✅ Password verification successful\n";
            echo "✅ Login simulation successful!\n";
        } else {
            echo "❌ Password verification failed in simulation\n";
        }
    } else {
        echo "❌ User not found or status not active\n";
    }
} catch (Exception $e) {
    echo "❌ Error in login simulation: " . $e->getMessage() . "\n";
}

echo "\n=== FINAL LOGIN CREDENTIALS ===\n";
echo "Email: admin@gmail.com\n";
echo "Password: 123\n";
echo "\nTry logging in with these credentials now.\n";
echo "If it still doesn't work, check the CAPTCHA and make sure you're entering it correctly.\n";
?> 