<?php
require_once 'db_connect.php';

// Simple script to fix the delivery status columns
echo "<h2>Fixing Delivery Status Columns</h2>";

try {
    // Check if delivery_status column exists
    $check_column = $pdo->query("SHOW COLUMNS FROM ingredient_requests LIKE 'delivery_status'");
    $column_exists = $check_column->fetch();
    
    if (!$column_exists) {
        echo "<p>Adding delivery_status column...</p>";
        $pdo->exec("ALTER TABLE ingredient_requests 
                   ADD COLUMN delivery_status ENUM('pending', 'on_delivery', 'delivered', 'returned', 'cancelled') DEFAULT 'pending' 
                   AFTER status");
        echo "<p style='color: green;'>✓ delivery_status column added successfully</p>";
    } else {
        echo "<p style='color: blue;'>✓ delivery_status column already exists</p>";
    }
    
    // Check if delivery_date column exists
    $check_date = $pdo->query("SHOW COLUMNS FROM ingredient_requests LIKE 'delivery_date'");
    $date_exists = $check_date->fetch();
    
    if (!$date_exists) {
        echo "<p>Adding delivery_date column...</p>";
        $pdo->exec("ALTER TABLE ingredient_requests 
                   ADD COLUMN delivery_date TIMESTAMP NULL 
                   AFTER delivery_status");
        echo "<p style='color: green;'>✓ delivery_date column added successfully</p>";
    } else {
        echo "<p style='color: blue;'>✓ delivery_date column already exists</p>";
    }
    
    // Check if delivery_notes column exists
    $check_notes = $pdo->query("SHOW COLUMNS FROM ingredient_requests LIKE 'delivery_notes'");
    $notes_exists = $check_notes->fetch();
    
    if (!$notes_exists) {
        echo "<p>Adding delivery_notes column...</p>";
        $pdo->exec("ALTER TABLE ingredient_requests 
                   ADD COLUMN delivery_notes TEXT NULL 
                   AFTER delivery_date");
        echo "<p style='color: green;'>✓ delivery_notes column added successfully</p>";
    } else {
        echo "<p style='color: blue;'>✓ delivery_notes column already exists</p>";
    }
    
    // Update existing records
    echo "<p>Updating existing records...</p>";
    $pdo->exec("UPDATE ingredient_requests SET delivery_status = 'pending' WHERE delivery_status IS NULL");
    echo "<p style='color: green;'>✓ Existing records updated</p>";
    
    echo "<h3 style='color: green;'>Database fix completed successfully!</h3>";
    echo "<p><a href='ingredient_requests.php'>Go back to Ingredient Requests</a></p>";
    
} catch (Exception $e) {
    echo "<p style='color: red;'>Error: " . $e->getMessage() . "</p>";
}
?> 