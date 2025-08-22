<?php
require_once 'db_connect.php';

echo "<h2>🌱 Adding Sample Ingredients</h2>";

try {
    // Check if ingredients table exists
    $tableExists = $pdo->query("SHOW TABLES LIKE 'ingredients'")->rowCount();
    
    if ($tableExists == 0) {
        echo "<p style='color: red;'>❌ Ingredients table does not exist!</p>";
        echo "<p>You need to create the ingredients table first.</p>";
        exit;
    }
    
    // Check current count
    $currentCount = $pdo->query("SELECT COUNT(*) FROM ingredients")->fetchColumn();
    echo "<p>Current ingredients in table: <strong>{$currentCount}</strong></p>";
    
    if ($currentCount > 0) {
        echo "<p style='color: green;'>✅ Table already has ingredients. No need to add samples.</p>";
        echo "<p><a href='ingredient_requests.php'>← Back to Ingredient Requests</a></p>";
        exit;
    }
    
    // Sample ingredients data
    $sampleIngredients = [
        ['name' => 'Flour', 'unit' => 'kg', 'status' => 'Active'],
        ['name' => 'Sugar', 'unit' => 'kg', 'status' => 'Active'],
        ['name' => 'Salt', 'unit' => 'kg', 'status' => 'Active'],
        ['name' => 'Eggs', 'unit' => 'pieces', 'status' => 'Active'],
        ['name' => 'Milk', 'unit' => 'liters', 'status' => 'Active'],
        ['name' => 'Butter', 'unit' => 'kg', 'status' => 'Active'],
        ['name' => 'Vanilla Extract', 'unit' => 'ml', 'status' => 'Active'],
        ['name' => 'Baking Powder', 'unit' => 'g', 'status' => 'Active'],
        ['name' => 'Chocolate Chips', 'unit' => 'kg', 'status' => 'Active'],
        ['name' => 'Strawberries', 'unit' => 'kg', 'status' => 'Active']
    ];
    
    // Insert sample ingredients
    $insertQuery = "INSERT INTO ingredients (ingredient_name, ingredient_unit, ingredient_status) VALUES (?, ?, ?)";
    $insertStmt = $pdo->prepare($insertQuery);
    
    $insertedCount = 0;
    foreach ($sampleIngredients as $ingredient) {
        try {
            $insertStmt->execute([
                $ingredient['name'],
                $ingredient['unit'],
                $ingredient['status']
            ]);
            $insertedCount++;
            echo "<p style='color: green;'>✅ Added: {$ingredient['name']}</p>";
        } catch (Exception $e) {
            echo "<p style='color: orange;'>⚠️ Could not add {$ingredient['name']}: {$e->getMessage()}</p>";
        }
    }
    
    echo "<h3 style='color: green;'>🎉 Successfully added {$insertedCount} sample ingredients!</h3>";
    
    // Show final count
    $finalCount = $pdo->query("SELECT COUNT(*) FROM ingredients")->fetchColumn();
    echo "<p>Total ingredients now: <strong>{$finalCount}</strong></p>";
    
    echo "<p><a href='ingredient_requests.php'>← Back to Ingredient Requests</a></p>";
    
} catch (Exception $e) {
    echo "<h3 style='color: red;'>❌ Error: " . $e->getMessage() . "</h3>";
    echo "<p><a href='ingredient_requests.php'>← Back to Ingredient Requests</a></p>";
}
?>
