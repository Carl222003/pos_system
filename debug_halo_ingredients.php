<?php
require_once 'db_connect.php';

echo "<h1>Debug Halo Ingredients</h1>\n";

try {
    // Check Halo Cover and Halo Cup specifically
    $ingredients = ['Halo Cover', 'Halo Cup', 'Coke Mismo'];
    
    foreach ($ingredients as $ingredientName) {
        echo "<h2>Checking: $ingredientName</h2>\n";
        
        $stmt = $pdo->prepare("SELECT * FROM ingredients WHERE ingredient_name = ?");
        $stmt->execute([$ingredientName]);
        $ingredient = $stmt->fetch(PDO::FETCH_ASSOC);
        
        if ($ingredient) {
            echo "<strong>Found in database:</strong><br>\n";
            echo "ID: {$ingredient['ingredient_id']}<br>\n";
            echo "Name: {$ingredient['ingredient_name']}<br>\n";
            echo "Quantity: {$ingredient['ingredient_quantity']}<br>\n";
            echo "Unit: {$ingredient['ingredient_unit']}<br>\n";
            echo "Status: {$ingredient['ingredient_status']}<br>\n";
            echo "Category ID: {$ingredient['category_id']}<br>\n";
            echo "Consume Before: {$ingredient['consume_before']}<br>\n";
            
            // Check category status
            if ($ingredient['category_id']) {
                $catStmt = $pdo->prepare("SELECT * FROM pos_category WHERE category_id = ?");
                $catStmt->execute([$ingredient['category_id']]);
                $category = $catStmt->fetch(PDO::FETCH_ASSOC);
                
                if ($category) {
                    echo "Category: {$category['category_name']} (Status: {$category['status']})<br>\n";
                }
            }
            
            // Test the exact query from get_current_stock.php
            echo "<h3>Testing get_current_stock.php query:</h3>\n";
            $testStmt = $pdo->prepare("
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
                WHERE i.ingredient_name = ?
                AND c.status = 'active' 
                AND i.ingredient_status = 'Available'
                AND (i.consume_before IS NULL OR i.consume_before > CURDATE())
            ");
            
            $testStmt->execute([$ingredientName]);
            $result = $testStmt->fetch(PDO::FETCH_ASSOC);
            
            if ($result) {
                echo "<span style='color: green;'><strong>✅ FOUND in get_current_stock.php query</strong></span><br>\n";
                echo "Quantity: {$result['ingredient_quantity']}<br>\n";
                echo "Status: {$result['ingredient_status']}<br>\n";
                echo "Category: {$result['category_name']}<br>\n";
            } else {
                echo "<span style='color: red;'><strong>❌ NOT FOUND in get_current_stock.php query</strong></span><br>\n";
                
                // Check each condition individually
                echo "<h4>Checking individual conditions:</h4>\n";
                
                // Check category status
                $catCheck = $pdo->prepare("SELECT status FROM pos_category WHERE category_id = ?");
                $catCheck->execute([$ingredient['category_id']]);
                $catStatus = $catCheck->fetchColumn();
                echo "Category status: " . ($catStatus ?: 'NULL') . " (should be 'active')<br>\n";
                
                // Check status
                echo "Ingredient status: '{$ingredient['ingredient_status']}' (should be 'Available')<br>\n";
                
                // Check consume_before
                $consumeCheck = $ingredient['consume_before'] ? ($ingredient['consume_before'] > date('Y-m-d') ? 'YES' : 'NO') : 'YES (NULL)';
                echo "Consume before > today: $consumeCheck (value: {$ingredient['consume_before']})<br>\n";
            }
            
        } else {
            echo "<span style='color: red;'><strong>❌ NOT FOUND in ingredients table</strong></span><br>\n";
        }
        
        echo "<hr>\n";
    }
    
} catch (Exception $e) {
    echo "Error: " . $e->getMessage() . "\n";
}
?>
