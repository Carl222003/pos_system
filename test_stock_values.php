<?php
require_once 'db_connect.php';

try {
    echo "<h2>Direct Database Stock Check</h2>\n";
    
    // Test the exact query used in get_current_stock.php
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
    
    echo "<h3>Ingredients with stock > 0 (from get_current_stock.php query):</h3>\n";
    echo "<table border='1' style='border-collapse: collapse;'>\n";
    echo "<tr><th>ID</th><th>Name</th><th>Quantity</th><th>Unit</th><th>Status</th><th>Category</th></tr>\n";
    
    foreach ($ingredients as $ingredient) {
        echo "<tr>";
        echo "<td>{$ingredient['ingredient_id']}</td>";
        echo "<td>{$ingredient['ingredient_name']}</td>";
        echo "<td>{$ingredient['ingredient_quantity']}</td>";
        echo "<td>{$ingredient['ingredient_unit']}</td>";
        echo "<td>{$ingredient['ingredient_status']}</td>";
        echo "<td>{$ingredient['category_name']}</td>";
        echo "</tr>\n";
    }
    echo "</table>\n";
    
    echo "<h3>All ingredients (regardless of stock):</h3>\n";
    $allStmt = $pdo->prepare("
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
    
    $allStmt->execute();
    $allIngredients = $allStmt->fetchAll(PDO::FETCH_ASSOC);
    
    echo "<table border='1' style='border-collapse: collapse;'>\n";
    echo "<tr><th>ID</th><th>Name</th><th>Quantity</th><th>Unit</th><th>Status</th><th>Category</th></tr>\n";
    
    foreach ($allIngredients as $ingredient) {
        $rowColor = $ingredient['ingredient_quantity'] > 0 ? 'white' : 'lightcoral';
        echo "<tr style='background-color: $rowColor;'>";
        echo "<td>{$ingredient['ingredient_id']}</td>";
        echo "<td>{$ingredient['ingredient_name']}</td>";
        echo "<td>{$ingredient['ingredient_quantity']}</td>";
        echo "<td>{$ingredient['ingredient_unit']}</td>";
        echo "<td>{$ingredient['ingredient_status']}</td>";
        echo "<td>{$ingredient['category_name']}</td>";
        echo "</tr>\n";
    }
    echo "</table>\n";
    
    echo "<h3>Database Connection Info:</h3>\n";
    echo "Database: " . $pdo->query('SELECT DATABASE()')->fetchColumn() . "<br>\n";
    echo "Server: " . $pdo->query('SELECT @@hostname')->fetchColumn() . "<br>\n";
    
} catch (Exception $e) {
    echo "Error: " . $e->getMessage() . "\n";
}
?>
