<?php
require_once 'db_connect.php';

echo "<h2>Fixing Ingredients Table</h2>";

try {
    // Check if columns exist first
    $columns = $pdo->query("SHOW COLUMNS FROM ingredients")->fetchAll(PDO::FETCH_COLUMN);
    
    echo "<p>Current columns: " . implode(', ', $columns) . "</p>";
    
    // Add new columns if they don't exist
    $newColumns = [
        'minimum_stock' => 'DECIMAL(10,2) DEFAULT 0',
        'storage_location' => 'VARCHAR(255)',
        'cost_per_unit' => 'DECIMAL(10,2)'
    ];
    
    foreach ($newColumns as $column => $definition) {
        if (!in_array($column, $columns)) {
            $sql = "ALTER TABLE ingredients ADD COLUMN $column $definition";
            $pdo->exec($sql);
            echo "<p style='color: green;'>✓ Added column: $column</p>";
        } else {
            echo "<p style='color: blue;'>- Column already exists: $column</p>";
        }
    }
    
    // Update existing records
    $pdo->exec("UPDATE ingredients SET minimum_stock = 0 WHERE minimum_stock IS NULL");
    echo "<p style='color: green;'>✓ Updated existing records</p>";
    
    echo "<h3 style='color: green;'>✅ Ingredients table updated successfully!</h3>";
    echo "<p><a href='ingredients.php'>← Back to Ingredients</a></p>";
    
} catch (Exception $e) {
    echo "<h3 style='color: red;'>❌ Error: " . $e->getMessage() . "</h3>";
    echo "<p><a href='ingredients.php'>← Back to Ingredients</a></p>";
}
?> 