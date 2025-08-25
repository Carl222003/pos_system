<?php
require_once 'db_connect.php';

try {
    // Create stock_update_requests table
    $sql = "CREATE TABLE IF NOT EXISTS stock_update_requests (
        request_id INT AUTO_INCREMENT PRIMARY KEY,
        stockman_id INT NOT NULL,
        ingredient_id INT NOT NULL,
        update_type ENUM('add', 'adjust', 'correct') NOT NULL,
        quantity DECIMAL(10,2) NOT NULL,
        unit VARCHAR(50) NOT NULL,
        urgency_level ENUM('low', 'medium', 'high', 'critical') NOT NULL,
        priority ENUM('normal', 'high', 'urgent') NOT NULL,
        reason TEXT NOT NULL,
        notes TEXT,
        status ENUM('pending', 'approved', 'rejected', 'completed') DEFAULT 'pending',
        admin_response TEXT,
        request_date TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        response_date TIMESTAMP NULL,
        processed_by INT NULL,
        processed_date TIMESTAMP NULL,
        FOREIGN KEY (stockman_id) REFERENCES pos_user(user_id) ON DELETE CASCADE,
        FOREIGN KEY (ingredient_id) REFERENCES ingredients(ingredient_id) ON DELETE CASCADE,
        FOREIGN KEY (processed_by) REFERENCES pos_user(user_id) ON DELETE SET NULL,
        INDEX idx_stockman_id (stockman_id),
        INDEX idx_ingredient_id (ingredient_id),
        INDEX idx_status (status),
        INDEX idx_request_date (request_date)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci";
    
    $pdo->exec($sql);
    echo "✅ Stock update requests table created successfully!\n";
    
    // Create stock_update_logs table for tracking changes
    $sql2 = "CREATE TABLE IF NOT EXISTS stock_update_logs (
        log_id INT AUTO_INCREMENT PRIMARY KEY,
        request_id INT NOT NULL,
        ingredient_id INT NOT NULL,
        old_quantity DECIMAL(10,2) NOT NULL,
        new_quantity DECIMAL(10,2) NOT NULL,
        change_amount DECIMAL(10,2) NOT NULL,
        change_type ENUM('add', 'subtract', 'set') NOT NULL,
        updated_by INT NOT NULL,
        update_date TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        notes TEXT,
        FOREIGN KEY (request_id) REFERENCES stock_update_requests(request_id) ON DELETE CASCADE,
        FOREIGN KEY (ingredient_id) REFERENCES ingredients(ingredient_id) ON DELETE CASCADE,
        FOREIGN KEY (updated_by) REFERENCES pos_user(user_id) ON DELETE CASCADE,
        INDEX idx_request_id (request_id),
        INDEX idx_ingredient_id (ingredient_id),
        INDEX idx_update_date (update_date)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci";
    
    $pdo->exec($sql2);
    echo "✅ Stock update logs table created successfully!\n";
    
    // Create admin notifications table for stock update requests
    $sql3 = "CREATE TABLE IF NOT EXISTS admin_stock_notifications (
        notification_id INT AUTO_INCREMENT PRIMARY KEY,
        request_id INT NOT NULL,
        admin_id INT NULL,
        notification_type ENUM('new_request', 'urgent_request', 'request_approved', 'request_rejected') NOT NULL,
        message TEXT NOT NULL,
        is_read BOOLEAN DEFAULT FALSE,
        created_date TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        read_date TIMESTAMP NULL,
        FOREIGN KEY (request_id) REFERENCES stock_update_requests(request_id) ON DELETE CASCADE,
        FOREIGN KEY (admin_id) REFERENCES pos_user(user_id) ON DELETE SET NULL,
        INDEX idx_admin_id (admin_id),
        INDEX idx_is_read (is_read),
        INDEX idx_created_date (created_date)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci";
    
    $pdo->exec($sql3);
    echo "✅ Admin stock notifications table created successfully!\n";
    
    echo "\n🎉 All tables created successfully! The stock update request system is ready to use.\n";
    
} catch (PDOException $e) {
    echo "❌ Error creating tables: " . $e->getMessage() . "\n";
}
?>
