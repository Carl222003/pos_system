<?php
require_once 'db_connect.php';

echo "<h1>Fix Coke Mismo Stock Issue</h1>\n";

try {
    // Check current Coke Mismo stock
    $stmt = $pdo->prepare("SELECT * FROM ingredients WHERE ingredient_name = 'Coke Mismo'");
    $stmt->execute();
    $coke = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if ($coke) {
        echo "<h2>Current Coke Mismo Data:</h2>\n";
        echo "ID: {$coke['ingredient_id']}<br>\n";
        echo "Name: {$coke['ingredient_name']}<br>\n";
        echo "Quantity: {$coke['ingredient_quantity']}<br>\n";
        echo "Unit: {$coke['ingredient_unit']}<br>\n";
        echo "Status: {$coke['ingredient_status']}<br>\n";
        echo "Category ID: {$coke['category_id']}<br>\n";
        
        // Check if quantity is 0 and fix it
        if ($coke['ingredient_quantity'] == 0) {
            echo "<h3>⚠️ Quantity is 0 - Fixing to 41</h3>\n";
            $updateStmt = $pdo->prepare("UPDATE ingredients SET ingredient_quantity = 41 WHERE ingredient_id = ?");
            $updateStmt->execute([$coke['ingredient_id']]);
            echo "✅ Updated Coke Mismo quantity to 41<br>\n";
        } else {
            echo "<h3>✅ Quantity is already correct: {$coke['ingredient_quantity']}</h3>\n";
        }
        
        // Check category status
        if ($coke['category_id']) {
            $catStmt = $pdo->prepare("SELECT * FROM pos_category WHERE category_id = ?");
            $catStmt->execute([$coke['category_id']]);
            $category = $catStmt->fetch(PDO::FETCH_ASSOC);
            
            if ($category) {
                echo "Category: {$category['category_name']} (Status: {$category['status']})<br>\n";
                
                if ($category['status'] !== 'active') {
                    echo "<h3>⚠️ Category status is not 'active' - Fixing</h3>\n";
                    $updateCatStmt = $pdo->prepare("UPDATE pos_category SET status = 'active' WHERE category_id = ?");
                    $updateCatStmt->execute([$coke['category_id']]);
                    echo "✅ Updated category status to 'active'<br>\n";
                }
            }
        }
        
        // Check ingredient status
        if ($coke['ingredient_status'] !== 'Available') {
            echo "<h3>⚠️ Ingredient status is not 'Available' - Fixing</h3>\n";
            $updateStatusStmt = $pdo->prepare("UPDATE ingredients SET ingredient_status = 'Available' WHERE ingredient_id = ?");
            $updateStatusStmt->execute([$coke['ingredient_id']]);
            echo "✅ Updated ingredient status to 'Available'<br>\n";
        }
        
        // Test the query that should find Coke Mismo
        echo "<h2>Testing Query:</h2>\n";
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
            echo "<span style='color: green;'><strong>✅ SUCCESS: Coke Mismo found in query</strong></span><br>\n";
            echo "Quantity: {$result['ingredient_quantity']}<br>\n";
            echo "Status: {$result['ingredient_status']}<br>\n";
            echo "Category: {$result['category_name']}<br>\n";
        } else {
            echo "<span style='color: red;'><strong>❌ FAILED: Coke Mismo not found in query</strong></span><br>\n";
        }
        
    } else {
        echo "<span style='color: red;'><strong>❌ Coke Mismo not found in database</strong></span><br>\n";
    }
    
} catch (Exception $e) {
    echo "Error: " . $e->getMessage() . "\n";
}
?>
