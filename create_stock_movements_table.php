<?php
require_once 'db_connect.php';

echo "<h1>Creating Stock Movements Table</h1>";

try {
    // Check if table exists
    $stmt = $pdo->query("SHOW TABLES LIKE 'stock_movements'");
    $table_exists = $stmt->rowCount() > 0;
    
    if ($table_exists) {
        echo "<p style='color: green;'>✅ Stock movements table already exists</p>";
    } else {
        // Create stock movements table
        $sql = "CREATE TABLE stock_movements (
            movement_id INT AUTO_INCREMENT PRIMARY KEY,
            branch_id INT NOT NULL,
            ingredient_id INT NOT NULL,
            movement_type ENUM('in', 'out', 'adjust') NOT NULL,
            quantity DECIMAL(10,2) NOT NULL,
            previous_quantity DECIMAL(10,2) NOT NULL,
            new_quantity DECIMAL(10,2) NOT NULL,
            reason VARCHAR(255),
            user_id INT,
            movement_date TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            notes TEXT,
            INDEX idx_branch_date (branch_id, movement_date),
            INDEX idx_ingredient (ingredient_id),
            INDEX idx_user (user_id)
        )";
        
        $pdo->exec($sql);
        echo "<p style='color: green;'>✅ Stock movements table created successfully</p>";
    }
    
    // Check if ingredients table has necessary columns
    echo "<h2>Checking Ingredients Table Structure</h2>";
    $stmt = $pdo->query("DESCRIBE ingredients");
    $columns = $stmt->fetchAll(PDO::FETCH_ASSOC);
    $column_names = array_column($columns, 'Field');
    
    $required_columns = [
        'ingredient_cost' => 'DECIMAL(10,2) DEFAULT 0.00',
        'ingredient_max_quantity' => 'DECIMAL(10,2) DEFAULT 100.00',
        'expiry_date' => 'DATE NULL',
        'last_movement_date' => 'TIMESTAMP NULL',
        'created_date' => 'TIMESTAMP DEFAULT CURRENT_TIMESTAMP'
    ];
    
    foreach ($required_columns as $column => $definition) {
        if (!in_array($column, $column_names)) {
            $sql = "ALTER TABLE ingredients ADD COLUMN $column $definition";
            $pdo->exec($sql);
            echo "<p style='color: green;'>✅ Added column: $column</p>";
        } else {
            echo "<p style='color: blue;'>ℹ️ Column already exists: $column</p>";
        }
    }
    
    // Insert sample stock movements data
    echo "<h2>Inserting Sample Data</h2>";
    
    // Get some ingredients to create sample movements
    $stmt = $pdo->query("SELECT ingredient_id, branch_id FROM ingredients LIMIT 10");
    $ingredients = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    if (empty($ingredients)) {
        echo "<p style='color: orange;'>⚠️ No ingredients found. Please add some ingredients first.</p>";
    } else {
        // Clear existing sample data
        $pdo->exec("DELETE FROM stock_movements WHERE reason LIKE 'Sample data%'");
        
        $sample_movements = 0;
        foreach ($ingredients as $ingredient) {
            // Create some sample movements for the past week
            for ($i = 6; $i >= 0; $i--) {
                $date = date('Y-m-d H:i:s', strtotime("-$i days"));
                $movement_type = rand(0, 1) ? 'in' : 'out';
                $quantity = rand(1, 10);
                
                $sql = "INSERT INTO stock_movements (
                    branch_id, ingredient_id, movement_type, quantity, 
                    previous_quantity, new_quantity, reason, user_id, movement_date
                ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)";
                
                $stmt = $pdo->prepare($sql);
                $stmt->execute([
                    $ingredient['branch_id'],
                    $ingredient['ingredient_id'],
                    $movement_type,
                    $quantity,
                    100, // Sample previous quantity
                    100 + ($movement_type === 'in' ? $quantity : -$quantity),
                    'Sample data for testing',
                    1, // Sample user ID
                    $date
                ]);
                
                $sample_movements++;
            }
        }
        
        echo "<p style='color: green;'>✅ Inserted $sample_movements sample stock movements</p>";
    }
    
    // Update ingredient costs and max quantities with sample data
    echo "<h2>Updating Sample Ingredient Data</h2>";
    
    $update_sql = "UPDATE ingredients SET 
        ingredient_cost = CASE 
            WHEN ingredient_id % 3 = 0 THEN 25.50
            WHEN ingredient_id % 3 = 1 THEN 15.75
            ELSE 8.99
        END,
        ingredient_max_quantity = CASE 
            WHEN ingredient_id % 4 = 0 THEN 200
            WHEN ingredient_id % 4 = 1 THEN 150
            WHEN ingredient_id % 4 = 2 THEN 100
            ELSE 75
        END,
        last_movement_date = CASE 
            WHEN ingredient_id % 2 = 0 THEN DATE_SUB(NOW(), INTERVAL 2 DAY)
            ELSE DATE_SUB(NOW(), INTERVAL 5 DAY)
        END
    WHERE ingredient_cost = 0 OR ingredient_cost IS NULL";
    
    $affected = $pdo->exec($update_sql);
    echo "<p style='color: green;'>✅ Updated $affected ingredients with sample data</p>";
    
    echo "<h2>Setup Complete!</h2>";
    echo "<p style='color: green;'>✅ Your stock analytics dashboard should now be functional!</p>";
    echo "<p><a href='stockman_dashboard.php'>Go to Dashboard</a></p>";
    echo "<p><a href='test_dashboard.php'>Run Dashboard Test</a></p>";
    
} catch (Exception $e) {
    echo "<p style='color: red;'>❌ Error: " . $e->getMessage() . "</p>";
}
?>

<style>
body { font-family: Arial, sans-serif; margin: 20px; line-height: 1.6; }
h1, h2 { color: #8B4543; }
p { margin: 8px 0; }
</style>
