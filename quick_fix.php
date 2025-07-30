<?php
require_once 'db_connect.php';

echo "<h2>Quick Fix for Delivery Status</h2>";

try {
    // Check if delivery_status column exists
    $result = $pdo->query("SHOW COLUMNS FROM ingredient_requests LIKE 'delivery_status'");
    
    if ($result->rowCount() == 0) {
        echo "<p>Adding missing columns...</p>";
        
        // Add delivery_status column
        $pdo->exec("ALTER TABLE ingredient_requests 
                   ADD COLUMN delivery_status ENUM('pending', 'on_delivery', 'delivered', 'returned', 'cancelled') DEFAULT 'pending' 
                   AFTER status");
        echo "<p style='color: green;'>✓ Added delivery_status column</p>";
        
        // Add delivery_date column
        $pdo->exec("ALTER TABLE ingredient_requests 
                   ADD COLUMN delivery_date TIMESTAMP NULL 
                   AFTER delivery_status");
        echo "<p style='color: green;'>✓ Added delivery_date column</p>";
        
        // Add delivery_notes column
        $pdo->exec("ALTER TABLE ingredient_requests 
                   ADD COLUMN delivery_notes TEXT NULL 
                   AFTER delivery_date");
        echo "<p style='color: green;'>✓ Added delivery_notes column</p>";
        
        // Update existing records
        $pdo->exec("UPDATE ingredient_requests SET delivery_status = 'pending' WHERE delivery_status IS NULL");
        echo "<p style='color: green;'>✓ Updated existing records</p>";
        
        echo "<h3 style='color: green;'>✅ FIXED! Database updated successfully.</h3>";
        echo "<p>You can now use the delivery status feature.</p>";
        
    } else {
        echo "<p style='color: blue;'>✓ Columns already exist. Database is ready.</p>";
    }
    
    echo "<br><p><a href='ingredient_requests.php' style='background: #8B4543; color: white; padding: 10px 20px; text-decoration: none; border-radius: 5px;'>Go to Ingredient Requests</a></p>";
    
} catch (Exception $e) {
    echo "<p style='color: red;'>❌ Error: " . $e->getMessage() . "</p>";
}
?> 