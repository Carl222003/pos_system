<?php
echo "<h2>🔍 Simple Ingredients Test</h2>";

try {
    require_once 'db_connect.php';
    echo "<p style='color: green;'>✅ Database connected successfully</p>";
    
    // Check if ingredients table exists
    $tableExists = $pdo->query("SHOW TABLES LIKE 'ingredients'")->rowCount();
    
    if ($tableExists > 0) {
        echo "<p style='color: green;'>✅ Ingredients table exists</p>";
        
        // Try to get ingredients
        $stmt = $pdo->query("SELECT ingredient_id, ingredient_name FROM ingredients LIMIT 5");
        $ingredients = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        if ($ingredients) {
            echo "<p style='color: green;'>✅ Found " . count($ingredients) . " ingredients</p>";
            echo "<ul>";
            foreach ($ingredients as $ingredient) {
                echo "<li>ID: {$ingredient['ingredient_id']} - {$ingredient['ingredient_name']}</li>";
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
    echo "<p>Error code: " . $e->getCode() . "</p>";
}

echo "<hr>";
echo "<p><a href='ingredient_requests.php'>← Back to Ingredient Requests</a></p>";
?>
