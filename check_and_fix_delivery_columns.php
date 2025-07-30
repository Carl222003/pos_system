<?php
require_once 'db_connect.php';

header('Content-Type: application/json');

try {
    // Check if delivery_status column exists
    $check_column = $pdo->query("SHOW COLUMNS FROM ingredient_requests LIKE 'delivery_status'");
    $column_exists = $check_column->fetch();
    
    if (!$column_exists) {
        // Add the columns if they don't exist
        $pdo->exec("ALTER TABLE ingredient_requests 
                   ADD COLUMN delivery_status ENUM('pending', 'on_delivery', 'delivered', 'returned', 'cancelled') DEFAULT 'pending' 
                   AFTER status");
        
        $pdo->exec("ALTER TABLE ingredient_requests 
                   ADD COLUMN delivery_date TIMESTAMP NULL 
                   AFTER delivery_status");
        
        $pdo->exec("ALTER TABLE ingredient_requests 
                   ADD COLUMN delivery_notes TEXT NULL 
                   AFTER delivery_date");
        
        echo json_encode([
            'success' => true,
            'message' => 'Delivery status columns added successfully'
        ]);
    } else {
        echo json_encode([
            'success' => true,
            'message' => 'Delivery status columns already exist'
        ]);
    }
    
} catch (Exception $e) {
    echo json_encode([
        'success' => false,
        'message' => 'Error: ' . $e->getMessage()
    ]);
}
?> 