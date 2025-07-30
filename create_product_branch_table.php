<?php
require_once 'db_connect.php';

echo "<h2>Creating Product Branch Table</h2>";

try {
    // Create product_branch table
    $sql = "
    CREATE TABLE IF NOT EXISTS product_branch (
        id INT AUTO_INCREMENT PRIMARY KEY,
        product_id INT NOT NULL,
        branch_id INT NOT NULL,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        UNIQUE KEY unique_product_branch (product_id, branch_id),
        FOREIGN KEY (product_id) REFERENCES pos_product(product_id) ON DELETE CASCADE,
        FOREIGN KEY (branch_id) REFERENCES pos_branch(branch_id) ON DELETE CASCADE
    )";
    
    $pdo->exec($sql);
    echo "<p style='color: green;'>✅ product_branch table created successfully!</p>";
    
    // Add indexes for better performance
    $indexes = [
        "CREATE INDEX idx_product_branch_product ON product_branch(product_id)",
        "CREATE INDEX idx_product_branch_branch ON product_branch(branch_id)"
    ];
    
    foreach ($indexes as $index_sql) {
        try {
            $pdo->exec($index_sql);
            echo "<p style='color: green;'>✅ Index created successfully!</p>";
        } catch (PDOException $e) {
            if ($e->getCode() == 1061) { // Duplicate key name error
                echo "<p style='color: blue;'>ℹ️ Index already exists</p>";
            } else {
                echo "<p style='color: orange;'>⚠️ Index creation warning: " . $e->getMessage() . "</p>";
            }
        }
    }
    
    // Verify table exists
    $tables = $pdo->query("SHOW TABLES LIKE 'product_branch'")->fetchAll();
    if (count($tables) > 0) {
        echo "<p style='color: green;'>✅ Table verification successful!</p>";
        
        // Show table structure
        $columns = $pdo->query("DESCRIBE product_branch")->fetchAll(PDO::FETCH_ASSOC);
        echo "<h3>Table Structure:</h3>";
        echo "<table border='1' style='border-collapse: collapse;'>";
        echo "<tr><th>Field</th><th>Type</th><th>Null</th><th>Key</th><th>Default</th></tr>";
        foreach ($columns as $column) {
            echo "<tr>";
            echo "<td>{$column['Field']}</td>";
            echo "<td>{$column['Type']}</td>";
            echo "<td>{$column['Null']}</td>";
            echo "<td>{$column['Key']}</td>";
            echo "<td>{$column['Default']}</td>";
            echo "</tr>";
        }
        echo "</table>";
    } else {
        echo "<p style='color: red;'>❌ Table creation failed!</p>";
    }
    
    echo "<h3 style='color: green;'>✅ Setup completed!</h3>";
    echo "<p><a href='product.php'>Go to Product Management</a></p>";
    
} catch (Exception $e) {
    echo "<p style='color: red;'>❌ Error: " . $e->getMessage() . "</p>";
}
?> 