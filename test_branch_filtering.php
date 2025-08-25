<?php
require_once 'db_connect.php';
require_once 'auth_function.php';

// This is a test file to verify branch filtering
echo "<h2>🔍 Branch Filtering Test</h2>";

// Test 1: Check all stockmen and their branches
echo "<h3>1. Stockmen and Their Branches:</h3>";
$stockmen = $pdo->query("SELECT user_id, user_name, user_type, branch_id FROM pos_user WHERE user_type = 'Stockman' ORDER BY user_id")->fetchAll(PDO::FETCH_ASSOC);

if (empty($stockmen)) {
    echo "<p style='color: red;'>❌ No stockmen found in the system!</p>";
} else {
    echo "<table border='1' style='border-collapse: collapse; width: 100%;'>";
    echo "<tr><th>User ID</th><th>Name</th><th>Type</th><th>Branch ID</th><th>Branch Name</th><th>Ingredients Count</th></tr>";
    
    foreach ($stockmen as $stockman) {
        $branch_name = 'Not Assigned';
        $ingredients_count = 0;
        
        if ($stockman['branch_id']) {
            $branch_stmt = $pdo->prepare("SELECT branch_name FROM pos_branch WHERE branch_id = ?");
            $branch_stmt->execute([$stockman['branch_id']]);
            $branch_name = $branch_stmt->fetchColumn() ?: 'Unknown Branch';
            
            $ingredients_stmt = $pdo->prepare("SELECT COUNT(*) FROM ingredients WHERE branch_id = ?");
            $ingredients_stmt->execute([$stockman['branch_id']]);
            $ingredients_count = $ingredients_stmt->fetchColumn();
        }
        
        echo "<tr>";
        echo "<td>{$stockman['user_id']}</td>";
        echo "<td>{$stockman['user_name']}</td>";
        echo "<td>{$stockman['user_type']}</td>";
        echo "<td>{$stockman['branch_id']}</td>";
        echo "<td>{$branch_name}</td>";
        echo "<td>{$ingredients_count}</td>";
        echo "</tr>";
    }
    echo "</table>";
}

// Test 2: Check ingredients distribution by branch
echo "<h3>2. Ingredients Distribution by Branch:</h3>";
$branches = $pdo->query("SELECT b.branch_id, b.branch_name, COUNT(i.ingredient_id) as ingredient_count 
                        FROM pos_branch b 
                        LEFT JOIN ingredients i ON b.branch_id = i.branch_id 
                        GROUP BY b.branch_id, b.branch_name 
                        ORDER BY b.branch_id")->fetchAll(PDO::FETCH_ASSOC);

echo "<table border='1' style='border-collapse: collapse; width: 100%;'>";
echo "<tr><th>Branch ID</th><th>Branch Name</th><th>Ingredients Count</th><th>Sample Ingredients</th></tr>";

foreach ($branches as $branch) {
    $sample_ingredients = [];
    if ($branch['ingredient_count'] > 0) {
        $sample_stmt = $pdo->prepare("SELECT ingredient_name FROM ingredients WHERE branch_id = ? LIMIT 3");
        $sample_stmt->execute([$branch['branch_id']]);
        $sample_ingredients = $sample_stmt->fetchAll(PDO::FETCH_COLUMN);
    }
    
    echo "<tr>";
    echo "<td>{$branch['branch_id']}</td>";
    echo "<td>{$branch['branch_name']}</td>";
    echo "<td>{$branch['ingredient_count']}</td>";
    echo "<td>" . implode(', ', $sample_ingredients) . "</td>";
    echo "</tr>";
}
echo "</table>";

// Test 3: Simulate stockman access
echo "<h3>3. Simulate Stockman Access:</h3>";
if (!empty($stockmen)) {
    $test_stockman = $stockmen[0]; // Use first stockman for testing
    
    echo "<p><strong>Testing for Stockman:</strong> {$test_stockman['user_name']} (ID: {$test_stockman['user_id']})</p>";
    
    if ($test_stockman['branch_id']) {
        $branch_stmt = $pdo->prepare("SELECT branch_name FROM pos_branch WHERE branch_id = ?");
        $branch_stmt->execute([$test_stockman['branch_id']]);
        $branch_name = $branch_stmt->fetchColumn();
        
        echo "<p><strong>Branch:</strong> {$branch_name} (ID: {$test_stockman['branch_id']})</p>";
        
        // Get ingredients for this stockman's branch
        $ingredients_stmt = $pdo->prepare("SELECT ingredient_id, ingredient_name, ingredient_quantity, ingredient_unit, category_id 
                                         FROM ingredients 
                                         WHERE branch_id = ? 
                                         ORDER BY ingredient_name 
                                         LIMIT 5");
        $ingredients_stmt->execute([$test_stockman['branch_id']]);
        $ingredients = $ingredients_stmt->fetchAll(PDO::FETCH_ASSOC);
        
        if (empty($ingredients)) {
            echo "<p style='color: orange;'>⚠️ No ingredients found for this branch.</p>";
        } else {
            echo "<p><strong>Available Ingredients (showing first 5):</strong></p>";
            echo "<ul>";
            foreach ($ingredients as $ingredient) {
                echo "<li>{$ingredient['ingredient_name']} - {$ingredient['ingredient_quantity']} {$ingredient['ingredient_unit']}</li>";
            }
            echo "</ul>";
        }
    } else {
        echo "<p style='color: red;'>❌ This stockman is not assigned to any branch!</p>";
    }
}

echo "<hr>";
echo "<p><strong>✅ Branch filtering is properly implemented if:</strong></p>";
echo "<ul>";
echo "<li>Each stockman shows only ingredients from their assigned branch</li>";
echo "<li>Stockmen without branch assignment show no ingredients</li>";
echo "<li>Different stockmen from different branches see different ingredients</li>";
echo "</ul>";

echo "<p><a href='request_stock_updates.php' style='background: #28a745; color: white; padding: 10px 20px; text-decoration: none; border-radius: 5px;'>🔗 Go to Update Stock Page</a></p>";
?>
