<?php
require_once 'db_connect.php';

try {
    echo "<h2>Adding approved_ingredients column to ingredient_requests table</h2>\n";
    
    // Check if approved_ingredients column exists
    $checkColumn = $pdo->query("SHOW COLUMNS FROM ingredient_requests LIKE 'approved_ingredients'");
    
    if ($checkColumn->rowCount() == 0) {
        // Column doesn't exist, add it
        $addColumn = $pdo->exec("ALTER TABLE ingredient_requests ADD COLUMN approved_ingredients TEXT NULL AFTER notes");
        echo "✅ Successfully added approved_ingredients column to ingredient_requests table\n";
    } else {
        echo "✅ approved_ingredients column already exists\n";
    }
    
    // Show the current table structure
    echo "\n📋 Current ingredient_requests table structure:\n";
    $columns = $pdo->query("DESCRIBE ingredient_requests")->fetchAll(PDO::FETCH_ASSOC);
    echo "<table border='1' style='border-collapse: collapse;'>\n";
    echo "<tr><th>Field</th><th>Type</th><th>Null</th><th>Key</th><th>Default</th><th>Extra</th></tr>\n";
    foreach ($columns as $column) {
        echo "<tr>";
        echo "<td>{$column['Field']}</td>";
        echo "<td>{$column['Type']}</td>";
        echo "<td>{$column['Null']}</td>";
        echo "<td>{$column['Key']}</td>";
        echo "<td>{$column['Default']}</td>";
        echo "<td>{$column['Extra']}</td>";
        echo "</tr>\n";
    }
    echo "</table>\n";
    
    echo "\n🎉 Database schema updated successfully!\n";
    
} catch (Exception $e) {
    echo "❌ Error: " . $e->getMessage() . "\n";
}
?>
