<?php
require_once 'db_connect.php';

echo "Populating branch_ingredient table with existing ingredients...\n";

try {
    // Get all active branches
    $branches = $pdo->query("SELECT branch_id FROM pos_branch WHERE status = 'Active'")->fetchAll(PDO::FETCH_COLUMN);
    
    if (empty($branches)) {
        echo "No active branches found.\n";
        exit;
    }
    
    echo "Found " . count($branches) . " active branches.\n";
    
    // Get all active ingredients
    $ingredients = $pdo->query("SELECT ingredient_id, ingredient_quantity, minimum_stock FROM ingredients WHERE ingredient_status != 'archived'")->fetchAll(PDO::FETCH_ASSOC);
    
    if (empty($ingredients)) {
        echo "No active ingredients found.\n";
        exit;
    }
    
    echo "Found " . count($ingredients) . " active ingredients.\n";
    
    // Check if branch_ingredient table exists
    $tables = $pdo->query("SHOW TABLES LIKE 'branch_ingredient'")->fetchAll(PDO::FETCH_COLUMN);
    
    if (empty($tables)) {
        echo "branch_ingredient table does not exist. Creating it...\n";
        
        // Create the table
        $create_table_sql = "
        CREATE TABLE IF NOT EXISTS branch_ingredient (
            branch_ingredient_id INT PRIMARY KEY AUTO_INCREMENT,
            branch_id INT NOT NULL,
            ingredient_id INT NOT NULL,
            quantity INT NOT NULL DEFAULT 0,
            minimum_stock INT NOT NULL DEFAULT 5,
            status ENUM('active', 'inactive') DEFAULT 'active',
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
            UNIQUE KEY unique_branch_ingredient (branch_id, ingredient_id),
            FOREIGN KEY (branch_id) REFERENCES pos_branch(branch_id) ON DELETE CASCADE,
            FOREIGN KEY (ingredient_id) REFERENCES ingredients(ingredient_id) ON DELETE CASCADE
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
        ";
        
        $pdo->exec($create_table_sql);
        echo "branch_ingredient table created successfully.\n";
    }
    
    // Insert branch-ingredient relationships
    $insert_stmt = $pdo->prepare("INSERT INTO branch_ingredient (branch_id, ingredient_id, quantity, minimum_stock) VALUES (?, ?, ?, ?)");
    $inserted_count = 0;
    
    foreach ($ingredients as $ingredient) {
        foreach ($branches as $branch_id) {
            try {
                $quantity = $ingredient['ingredient_quantity'] ?: 10;
                $minimum_stock = $ingredient['minimum_stock'] ?: 5;
                
                $insert_stmt->execute([
                    $branch_id,
                    $ingredient['ingredient_id'],
                    $quantity,
                    $minimum_stock
                ]);
                
                $inserted_count++;
                echo "Assigned ingredient ID " . $ingredient['ingredient_id'] . " to branch ID " . $branch_id . " (Qty: " . $quantity . ")\n";
                
            } catch (PDOException $e) {
                if ($e->getCode() != 23000) { // Not duplicate key error
                    echo "Error assigning ingredient " . $ingredient['ingredient_id'] . " to branch " . $branch_id . ": " . $e->getMessage() . "\n";
                }
            }
        }
    }
    
    echo "Branch ingredient assignment completed! Total assignments: " . $inserted_count . "\n";
    
    // Show summary
    $total_relationships = $pdo->query("SELECT COUNT(*) FROM branch_ingredient")->fetchColumn();
    echo "Total branch-ingredient relationships in database: " . $total_relationships . "\n";
    
} catch (Exception $e) {
    echo "Error: " . $e->getMessage() . "\n";
}
?>
