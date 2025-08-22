<?php
require_once 'db_connect.php';

echo "<h2>🔍 Checking Ingredients Table</h2>";

try {
    // Check if ingredients table exists
    $tableExists = $pdo->query("SHOW TABLES LIKE 'ingredients'")->rowCount();
    
    if ($tableExists > 0) {
        echo "<p style='color: green;'>✅ Ingredients table exists</p>";
        
        // Check total count
        $count = $pdo->query("SELECT COUNT(*) FROM ingredients")->fetchColumn();
        echo "<p><strong>Total ingredients:</strong> {$count}</p>";
        
        // Check non-archived count
        $activeCount = $pdo->query("SELECT COUNT(*) FROM ingredients WHERE ingredient_status != 'archived'")->fetchColumn();
        echo "<p><strong>Active ingredients:</strong> {$activeCount}</p>";
        
        // Show sample ingredients
        $sample = $pdo->query("SELECT ingredient_id, ingredient_name, ingredient_status FROM ingredients LIMIT 10")->fetchAll(PDO::FETCH_ASSOC);
        
        if ($sample) {
            echo "<h3>Sample Ingredients:</h3>";
            echo "<ul>";
            foreach ($sample as $ingredient) {
                echo "<li>ID: {$ingredient['ingredient_id']} - {$ingredient['ingredient_name']} ({$ingredient['ingredient_status']})</li>";
            }
            echo "</ul>";
        } else {
            echo "<p style='color: orange;'>⚠️ No ingredients found in table</p>";
        }
        
    } else {
        echo "<p style='color: red;'>❌ Ingredients table does not exist!</p>";
    }
    
} catch (Exception $e) {
    echo "<h3 style='color: red;'>❌ Error: " . $e->getMessage() . "</h3>";
}

echo "<hr>";
echo "<p><a href='ingredient_requests.php'>← Back to Ingredient Requests</a></p>";
?>
