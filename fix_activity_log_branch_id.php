<?php
require_once 'db_connect.php';

echo "<h2>Fixing Activity Log Branch ID Column</h2>";

try {
    // Check if branch_id column exists
    $checkColumn = $pdo->query("SHOW COLUMNS FROM pos_activity_log LIKE 'branch_id'");
    $columnExists = $checkColumn->rowCount() > 0;
    
    if (!$columnExists) {
        // Add the branch_id column
        $pdo->exec("ALTER TABLE pos_activity_log ADD COLUMN branch_id INT NULL AFTER user_id");
        echo "<p style='color: green;'>✅ branch_id column added successfully to pos_activity_log table.</p>";
    } else {
        echo "<p style='color: blue;'>✓ branch_id column already exists in pos_activity_log table.</p>";
    }
    
    // Show current table structure
    echo "<h3>Current pos_activity_log table structure:</h3>";
    $columns = $pdo->query("SHOW COLUMNS FROM pos_activity_log")->fetchAll(PDO::FETCH_ASSOC);
    echo "<table border='1' style='border-collapse: collapse; width: 100%;'>";
    echo "<tr><th>Field</th><th>Type</th><th>Null</th><th>Key</th><th>Default</th><th>Extra</th></tr>";
    foreach ($columns as $column) {
        echo "<tr>";
        echo "<td>" . $column['Field'] . "</td>";
        echo "<td>" . $column['Type'] . "</td>";
        echo "<td>" . $column['Null'] . "</td>";
        echo "<td>" . $column['Key'] . "</td>";
        echo "<td>" . $column['Default'] . "</td>";
        echo "<td>" . $column['Extra'] . "</td>";
        echo "</tr>";
    }
    echo "</table>";
    
    echo "<h3 style='color: green;'>✅ Database fix completed successfully!</h3>";
    echo "<p><a href='stockman_activity_log.php'>Go to Stockman Activity Log</a></p>";
    
} catch (Exception $e) {
    echo "<p style='color: red;'>❌ Error: " . $e->getMessage() . "</p>";
}
?> 