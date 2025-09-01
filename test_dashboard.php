<?php
// Simple test file to verify dashboard functionality
require_once 'db_connect.php';

echo "<h1>Dashboard Analytics Test</h1>";

try {
    // Test basic database connection
    echo "<h2>Database Connection Test</h2>";
    $stmt = $pdo->query("SELECT COUNT(*) as total FROM ingredients");
    $result = $stmt->fetch();
    echo "<p>Total ingredients in database: " . $result['total'] . "</p>";
    
    // Test categories
    echo "<h2>Categories Test</h2>";
    $stmt = $pdo->query("SELECT COUNT(*) as total FROM pos_category WHERE status = 'active'");
    $result = $stmt->fetch();
    echo "<p>Active categories: " . $result['total'] . "</p>";
    
    // Test stock movements table
    echo "<h2>Stock Movements Test</h2>";
    try {
        $stmt = $pdo->query("SELECT COUNT(*) as total FROM stock_movements");
        $result = $stmt->fetch();
        echo "<p>Stock movements: " . $result['total'] . "</p>";
    } catch (Exception $e) {
        echo "<p style='color: red;'>Stock movements table not found: " . $e->getMessage() . "</p>";
    }
    
    // Test ingredients table structure
    echo "<h2>Ingredients Table Structure</h2>";
    $stmt = $pdo->query("DESCRIBE ingredients");
    $columns = $stmt->fetchAll(PDO::FETCH_ASSOC);
    echo "<ul>";
    foreach ($columns as $column) {
        echo "<li>" . $column['Field'] . " - " . $column['Type'] . "</li>";
    }
    echo "</ul>";
    
    // Test sample data
    echo "<h2>Sample Ingredients Data</h2>";
    $stmt = $pdo->query("SELECT ingredient_id, ingredient_name, ingredient_quantity, ingredient_unit FROM ingredients LIMIT 5");
    $ingredients = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    if (empty($ingredients)) {
        echo "<p style='color: orange;'>No ingredients found in database</p>";
    } else {
        echo "<table border='1' style='border-collapse: collapse;'>";
        echo "<tr><th>ID</th><th>Name</th><th>Quantity</th><th>Unit</th></tr>";
        foreach ($ingredients as $ingredient) {
            echo "<tr>";
            echo "<td>" . $ingredient['ingredient_id'] . "</td>";
            echo "<td>" . htmlspecialchars($ingredient['ingredient_name']) . "</td>";
            echo "<td>" . $ingredient['ingredient_quantity'] . "</td>";
            echo "<td>" . htmlspecialchars($ingredient['ingredient_unit']) . "</td>";
            echo "</tr>";
        }
        echo "</table>";
    }
    
    // Test analytics endpoint
    echo "<h2>Analytics Endpoint Test</h2>";
    echo "<p><a href='get_stockman_analytics.php' target='_blank'>Test Analytics Endpoint</a></p>";
    
} catch (Exception $e) {
    echo "<p style='color: red;'>Error: " . $e->getMessage() . "</p>";
}
?>

<style>
body { font-family: Arial, sans-serif; margin: 20px; }
h1, h2 { color: #8B4543; }
table { margin: 10px 0; }
th, td { padding: 8px; text-align: left; }
th { background: #f8f9fa; }
</style>
