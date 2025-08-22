<?php
require_once 'db_connect.php';

try {
    // Check if pos_stock_movement table exists
    $stmt = $pdo->query("SHOW TABLES LIKE 'pos_stock_movement'");
    if ($stmt->rowCount() == 0) {
        // Create the stock movement table
        $pdo->exec("
            CREATE TABLE pos_stock_movement (
                movement_id INT PRIMARY KEY AUTO_INCREMENT,
                ingredient_id INT NOT NULL,
                user_id INT NOT NULL,
                branch_id INT NOT NULL,
                movement_type ENUM('add', 'subtract', 'set', 'adjust') NOT NULL,
                quantity DECIMAL(10,2) NOT NULL,
                previous_stock DECIMAL(10,2) NOT NULL,
                new_stock DECIMAL(10,2) NOT NULL,
                reason TEXT,
                reference_type VARCHAR(50),
                reference_id INT,
                created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                INDEX idx_ingredient_id (ingredient_id),
                INDEX idx_user_id (user_id),
                INDEX idx_branch_id (branch_id),
                INDEX idx_created_at (created_at),
                INDEX idx_movement_type (movement_type)
            )
        ");
        echo "✅ Stock movement table created successfully!<br>";
    } else {
        echo "✅ Stock movement table already exists!<br>";
    }

    // Check if ingredients table has the required columns
    $stmt = $pdo->query("SHOW COLUMNS FROM ingredients LIKE 'minimum_stock'");
    if ($stmt->rowCount() == 0) {
        $pdo->exec("ALTER TABLE ingredients ADD COLUMN minimum_stock DECIMAL(10,2) DEFAULT 5.00 AFTER ingredient_quantity");
        echo "✅ Added minimum_stock column to ingredients table!<br>";
    } else {
        echo "✅ minimum_stock column already exists in ingredients table!<br>";
    }

    // Check if ingredients table has expiry_date column
    $stmt = $pdo->query("SHOW COLUMNS FROM ingredients LIKE 'expiry_date'");
    if ($stmt->rowCount() == 0) {
        $pdo->exec("ALTER TABLE ingredients ADD COLUMN expiry_date DATE NULL AFTER minimum_stock");
        echo "✅ Added expiry_date column to ingredients table!<br>";
    } else {
        echo "✅ expiry_date column already exists in ingredients table!<br>";
    }

    echo "<br>🎉 Stock management system setup complete!";

} catch (PDOException $e) {
    echo "❌ Error: " . $e->getMessage();
}
?>
