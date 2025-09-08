<?php
require_once 'db_connect.php';

echo "<h1>Coke Mismo Stock Check</h1>\n";

try {
    // Check Coke Mismo specifically
    $stmt = $pdo->prepare("SELECT * FROM ingredients WHERE ingredient_name = 'Coke Mismo'");
    $stmt->execute();
    $coke = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if ($coke) {
        echo "<h2>Coke Mismo Details:</h2>\n";
        echo "<strong>ID:</strong> {$coke['ingredient_id']}<br>\n";
        echo "<strong>Name:</strong> {$coke['ingredient_name']}<br>\n";
        echo "<strong>Quantity:</strong> {$coke['ingredient_quantity']}<br>\n";
        echo "<strong>Unit:</strong> {$coke['ingredient_unit']}<br>\n";
        echo "<strong>Status:</strong> {$coke['ingredient_status']}<br>\n";
        echo "<strong>Category ID:</strong> {$coke['category_id']}<br>\n";
        echo "<strong>Consume Before:</strong> {$coke['consume_before']}<br>\n";
        
        // Check category status
        if ($coke['category_id']) {
            $catStmt = $pdo->prepare("SELECT * FROM pos_category WHERE category_id = ?");
            $catStmt->execute([$coke['category_id']]);
            $category = $catStmt->fetch(PDO::FETCH_ASSOC);
            
            if ($category) {
                echo "<strong>Category Name:</strong> {$category['category_name']}<br>\n";
                echo "<strong>Category Status:</strong> {$category['status']}<br>\n";
            }
        }
        
        // Test the exact query from request_stock.php
        echo "<h2>Testing Request Stock Query:</h2>\n";
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
            WHERE i.ingredient_name = 'Coke Mismo'
            AND c.status = 'active' 
            AND i.ingredient_quantity > 0
            AND i.ingredient_status = 'Available'
            AND (i.consume_before IS NULL OR i.consume_before > CURDATE())
        ");
        
        $testStmt->execute();
        $result = $testStmt->fetch(PDO::FETCH_ASSOC);
        
        if ($result) {
            echo "<span style='color: green;'><strong>✅ FOUND in request_stock.php query:</strong></span><br>\n";
            echo "Quantity: {$result['ingredient_quantity']}<br>\n";
            echo "Status: {$result['ingredient_status']}<br>\n";
            echo "Category: {$result['category_name']} (Status: active)<br>\n";
        } else {
            echo "<span style='color: red;'><strong>❌ NOT FOUND in request_stock.php query</strong></span><br>\n";
            
            // Check each condition individually
            echo "<h3>Checking individual conditions:</h3>\n";
            
            // Check category status
            $catCheck = $pdo->prepare("SELECT status FROM pos_category WHERE category_id = ?");
            $catCheck->execute([$coke['category_id']]);
            $catStatus = $catCheck->fetchColumn();
            echo "Category status: " . ($catStatus ?: 'NULL') . " (should be 'active')<br>\n";
            
            // Check quantity
            echo "Quantity > 0: " . ($coke['ingredient_quantity'] > 0 ? 'YES' : 'NO') . " (value: {$coke['ingredient_quantity']})<br>\n";
            
            // Check status
            echo "Status = 'Available': " . ($coke['ingredient_status'] === 'Available' ? 'YES' : 'NO') . " (value: '{$coke['ingredient_status']}')<br>\n";
            
            // Check consume_before
            $consumeCheck = $coke['consume_before'] ? ($coke['consume_before'] > date('Y-m-d') ? 'YES' : 'NO') : 'YES (NULL)';
            echo "Consume before > today: $consumeCheck (value: {$coke['consume_before']})<br>\n";
        }
        
    } else {
        echo "<span style='color: red;'><strong>❌ Coke Mismo not found in ingredients table</strong></span><br>\n";
    }
    
    // Check all ingredients with similar names
    echo "<h2>Similar Ingredients:</h2>\n";
    $similarStmt = $pdo->prepare("SELECT * FROM ingredients WHERE ingredient_name LIKE '%Coke%' OR ingredient_name LIKE '%Mismo%'");
    $similarStmt->execute();
    $similar = $similarStmt->fetchAll(PDO::FETCH_ASSOC);
    
    foreach ($similar as $ingredient) {
        echo "- {$ingredient['ingredient_name']}: {$ingredient['ingredient_quantity']} {$ingredient['ingredient_unit']} (Status: {$ingredient['ingredient_status']})<br>\n";
    }
    
} catch (Exception $e) {
    echo "Error: " . $e->getMessage() . "\n";
}
?>
