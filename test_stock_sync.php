<?php
require_once 'db_connect.php';

echo "<h1>Stock Synchronization Test</h1>\n";

try {
    // Test the initial query from request_stock.php
    echo "<h2>1. Initial Query (from request_stock.php)</h2>\n";
    $stmt1 = $pdo->prepare("
        SELECT 
            i.ingredient_id, 
            i.ingredient_name, 
            i.ingredient_unit, 
            i.ingredient_quantity, 
            i.ingredient_status, 
            i.category_id, 
            c.category_name
        FROM ingredients i
        LEFT JOIN pos_category c ON i.category_id = c.category_id
        WHERE c.status = 'active' 
        AND i.ingredient_quantity > 0
        AND i.ingredient_status = 'Available'
        AND (i.consume_before IS NULL OR i.consume_before > CURDATE())
        ORDER BY c.category_name, i.ingredient_name
    ");
    
    $stmt1->execute();
    $initialIngredients = $stmt1->fetchAll(PDO::FETCH_ASSOC);
    
    echo "Found " . count($initialIngredients) . " ingredients with stock > 0:<br>\n";
    foreach ($initialIngredients as $ingredient) {
        echo "- {$ingredient['ingredient_name']}: {$ingredient['ingredient_quantity']} {$ingredient['ingredient_unit']}<br>\n";
    }
    
    // Test the refresh query from get_current_stock.php
    echo "<h2>2. Refresh Query (from get_current_stock.php)</h2>\n";
    $stmt2 = $pdo->prepare("
        SELECT 
            i.ingredient_id, 
            i.ingredient_name, 
            i.ingredient_unit, 
            i.ingredient_quantity, 
            i.ingredient_status, 
            i.category_id, 
            c.category_name
        FROM ingredients i
        LEFT JOIN pos_category c ON i.category_id = c.category_id
        WHERE c.status = 'active' 
        AND i.ingredient_quantity > 0
        AND i.ingredient_status = 'Available'
        AND (i.consume_before IS NULL OR i.consume_before > CURDATE())
        ORDER BY c.category_name, i.ingredient_name
    ");
    
    $stmt2->execute();
    $refreshIngredients = $stmt2->fetchAll(PDO::FETCH_ASSOC);
    
    echo "Found " . count($refreshIngredients) . " ingredients with stock > 0:<br>\n";
    foreach ($refreshIngredients as $ingredient) {
        echo "- {$ingredient['ingredient_name']}: {$ingredient['ingredient_quantity']} {$ingredient['ingredient_unit']}<br>\n";
    }
    
    // Check specific ingredients
    echo "<h2>3. Specific Ingredient Check</h2>\n";
    $specificIngredients = ['Coke Mismo', 'Halo Cover', 'Halo Cup', 'Ice Cream', 'Chicken Ham'];
    
    foreach ($specificIngredients as $ingredientName) {
        $stmt = $pdo->prepare("SELECT * FROM ingredients WHERE ingredient_name = ?");
        $stmt->execute([$ingredientName]);
        $ingredient = $stmt->fetch(PDO::FETCH_ASSOC);
        
        if ($ingredient) {
            $color = $ingredient['ingredient_quantity'] > 0 ? 'green' : 'red';
            echo "<span style='color: $color;'><strong>$ingredientName:</strong> {$ingredient['ingredient_quantity']} {$ingredient['ingredient_unit']} (Status: {$ingredient['ingredient_status']})</span><br>\n";
        } else {
            echo "<strong>$ingredientName:</strong> NOT FOUND<br>\n";
        }
    }
    
    // Test the API endpoint
    echo "<h2>4. API Endpoint Test</h2>\n";
    echo "Testing get_current_stock.php...<br>\n";
    
    // Simulate session
    session_start();
    $_SESSION['user_id'] = 1;
    $_SESSION['user_type'] = 'Admin';
    
    // Capture API output
    ob_start();
    include 'get_current_stock.php';
    $apiOutput = ob_get_clean();
    
    $apiResponse = json_decode($apiOutput, true);
    if ($apiResponse && isset($apiResponse['ingredients'])) {
        echo "API returned " . count($apiResponse['ingredients']) . " ingredients:<br>\n";
        foreach ($apiResponse['ingredients'] as $ingredient) {
            echo "- {$ingredient['ingredient_name']}: {$ingredient['ingredient_quantity']} {$ingredient['ingredient_unit']}<br>\n";
        }
    } else {
        echo "API failed or returned no ingredients<br>\n";
        echo "Raw output: " . htmlspecialchars($apiOutput) . "<br>\n";
    }
    
} catch (Exception $e) {
    echo "Error: " . $e->getMessage() . "\n";
}
?>
