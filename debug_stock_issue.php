<?php
require_once 'db_connect.php';

echo "<h1>Stock Debugging Report</h1>\n";

try {
    // Check database connection
    echo "<h2>1. Database Connection</h2>\n";
    echo "Connected to: " . $pdo->query('SELECT DATABASE()')->fetchColumn() . "<br>\n";
    echo "Server: " . $pdo->query('SELECT @@hostname')->fetchColumn() . "<br>\n";
    
    // Check specific ingredients mentioned in the error
    echo "<h2>2. Specific Ingredients Check</h2>\n";
    $specificIngredients = ['Chicken Ham', 'Corn Flakes', 'Coke Mismo', 'Halo Cover', 'Halo Cup', 'Ice Cream'];
    
    foreach ($specificIngredients as $ingredientName) {
        $stmt = $pdo->prepare("SELECT * FROM ingredients WHERE ingredient_name = ?");
        $stmt->execute([$ingredientName]);
        $ingredient = $stmt->fetch(PDO::FETCH_ASSOC);
        
        if ($ingredient) {
            echo "<strong>$ingredientName:</strong><br>\n";
            echo "- ID: {$ingredient['ingredient_id']}<br>\n";
            echo "- Quantity: {$ingredient['ingredient_quantity']}<br>\n";
            echo "- Unit: {$ingredient['ingredient_unit']}<br>\n";
            echo "- Status: {$ingredient['ingredient_status']}<br>\n";
            echo "- Category ID: {$ingredient['category_id']}<br>\n";
            
            // Check category status
            $catStmt = $pdo->prepare("SELECT category_name, status FROM pos_category WHERE category_id = ?");
            $catStmt->execute([$ingredient['category_id']]);
            $category = $catStmt->fetch(PDO::FETCH_ASSOC);
            echo "- Category: " . ($category['category_name'] ?? 'Unknown') . " (Status: " . ($category['status'] ?? 'Unknown') . ")<br>\n";
            
            // Check consume_before date
            echo "- Consume Before: " . ($ingredient['consume_before'] ?? 'NULL') . "<br>\n";
            echo "<br>\n";
        } else {
            echo "<strong>$ingredientName:</strong> NOT FOUND<br><br>\n";
        }
    }
    
    // Test the exact query from get_current_stock.php
    echo "<h2>3. Testing get_current_stock.php Query</h2>\n";
    $stmt = $pdo->prepare("
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
    
    $stmt->execute();
    $ingredients = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    echo "Query returned " . count($ingredients) . " ingredients with stock > 0:<br>\n";
    foreach ($ingredients as $ingredient) {
        echo "- {$ingredient['ingredient_name']}: {$ingredient['ingredient_quantity']} {$ingredient['ingredient_unit']}<br>\n";
    }
    
    // Test with relaxed conditions
    echo "<h2>4. Testing with Relaxed Conditions</h2>\n";
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
        AND i.ingredient_status = 'Available'
        ORDER BY c.category_name, i.ingredient_name
    ");
    
    $stmt2->execute();
    $allIngredients = $stmt2->fetchAll(PDO::FETCH_ASSOC);
    
    echo "Query with relaxed conditions returned " . count($allIngredients) . " ingredients:<br>\n";
    foreach ($allIngredients as $ingredient) {
        $color = $ingredient['ingredient_quantity'] > 0 ? 'green' : 'red';
        echo "<span style='color: $color;'>- {$ingredient['ingredient_name']}: {$ingredient['ingredient_quantity']} {$ingredient['ingredient_unit']}</span><br>\n";
    }
    
    // Check if there are any recent stock movements
    echo "<h2>5. Recent Stock Movements</h2>\n";
    $movementStmt = $pdo->prepare("
        SELECT sm.*, i.ingredient_name 
        FROM stock_movements sm 
        LEFT JOIN ingredients i ON sm.ingredient_id = i.ingredient_id 
        ORDER BY sm.movement_date DESC 
        LIMIT 10
    ");
    $movementStmt->execute();
    $movements = $movementStmt->fetchAll(PDO::FETCH_ASSOC);
    
    if (count($movements) > 0) {
        echo "Recent stock movements:<br>\n";
        foreach ($movements as $movement) {
            echo "- {$movement['ingredient_name']}: {$movement['movement_type']} {$movement['quantity']} on {$movement['movement_date']}<br>\n";
        }
    } else {
        echo "No recent stock movements found.<br>\n";
    }
    
} catch (Exception $e) {
    echo "Error: " . $e->getMessage() . "\n";
}
?>
