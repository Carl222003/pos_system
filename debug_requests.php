<?php
require_once 'db_connect.php';

echo "<h2>Debug: Checking Ingredient Requests</h2>";

try {
    // Check if ingredient_requests table exists
    $tableExists = $pdo->query("SHOW TABLES LIKE 'ingredient_requests'")->rowCount();
    if ($tableExists == 0) {
        echo "<p style='color: red;'>❌ ingredient_requests table does not exist!</p>";
        exit;
    } else {
        echo "<p style='color: green;'>✅ ingredient_requests table exists</p>";
    }
    
    // Show table structure
    echo "<h3>Table Structure:</h3>";
    $columns = $pdo->query("SHOW COLUMNS FROM ingredient_requests")->fetchAll(PDO::FETCH_ASSOC);
    echo "<table border='1' style='border-collapse: collapse; width: 100%;'>";
    echo "<tr><th>Field</th><th>Type</th><th>Null</th><th>Key</th><th>Default</th><th>Extra</th></tr>";
    foreach ($columns as $column) {
        echo "<tr>";
        echo "<td>" . $column['Field'] . "</td>";
        echo "<td>" . $column['Type'] . "</td>";
        echo "<td>" . $column['Null'] . "</td>";
        echo "<td>" . $column['Key'] . "</td>";
        echo "<td>" . $column['Default'] . "</td>";
        echo "<td>" . $column['Extra'] . "</td>";
        echo "</tr>";
    }
    echo "</table>";
    
    // Count total requests
    $totalRequests = $pdo->query("SELECT COUNT(*) FROM ingredient_requests")->fetchColumn();
    echo "<h3>Total Requests: $totalRequests</h3>";
    
    // Show recent requests (last 10)
    echo "<h3>Recent Requests (Last 10):</h3>";
    $recentRequests = $pdo->query("SELECT * FROM ingredient_requests ORDER BY request_date DESC LIMIT 10")->fetchAll(PDO::FETCH_ASSOC);
    
    if (empty($recentRequests)) {
        echo "<p style='color: orange;'>⚠️ No requests found in the database</p>";
    } else {
        echo "<table border='1' style='border-collapse: collapse; width: 100%;'>";
        echo "<tr><th>ID</th><th>Branch ID</th><th>Date</th><th>Ingredients</th><th>Status</th><th>Notes</th></tr>";
        foreach ($recentRequests as $request) {
            echo "<tr>";
            echo "<td>" . $request['request_id'] . "</td>";
            echo "<td>" . $request['branch_id'] . "</td>";
            echo "<td>" . $request['request_date'] . "</td>";
            echo "<td>" . htmlspecialchars(substr($request['ingredients'], 0, 100)) . "...</td>";
            echo "<td>" . $request['status'] . "</td>";
            echo "<td>" . htmlspecialchars(substr($request['notes'] ?? '', 0, 50)) . "</td>";
            echo "</tr>";
        }
        echo "</table>";
    }
    
    // Check branches
    echo "<h3>Available Branches:</h3>";
    $branches = $pdo->query("SELECT branch_id, branch_name FROM pos_branch")->fetchAll(PDO::FETCH_ASSOC);
    if (empty($branches)) {
        echo "<p style='color: red;'>❌ No branches found!</p>";
    } else {
        echo "<ul>";
        foreach ($branches as $branch) {
            echo "<li>ID: {$branch['branch_id']} - {$branch['branch_name']}</li>";
        }
        echo "</ul>";
    }
    
    // Check stockmen
    echo "<h3>Stockmen:</h3>";
    $stockmen = $pdo->query("SELECT user_id, user_name, branch_id FROM pos_user WHERE user_type = 'Stockman'")->fetchAll(PDO::FETCH_ASSOC);
    if (empty($stockmen)) {
        echo "<p style='color: red;'>❌ No stockmen found!</p>";
    } else {
        echo "<ul>";
        foreach ($stockmen as $stockman) {
            echo "<li>ID: {$stockman['user_id']} - {$stockman['user_name']} (Branch: {$stockman['branch_id']})</li>";
        }
        echo "</ul>";
    }
    
    // Test request insertion
    echo "<h3>Testing Request Insertion:</h3>";
    $testBranchId = $branches[0]['branch_id'] ?? 1;
    $testIngredients = json_encode([['ingredient_id' => 1, 'quantity' => 5]]);
    
    try {
        $stmt = $pdo->prepare("INSERT INTO ingredient_requests (branch_id, request_date, ingredients, status, notes) VALUES (?, NOW(), ?, ?, ?)");
        $stmt->execute([$testBranchId, $testIngredients, 'pending', 'Test request']);
        $testId = $pdo->lastInsertId();
        echo "<p style='color: green;'>✅ Test request inserted successfully (ID: $testId)</p>";
        
        // Clean up test data
        $pdo->exec("DELETE FROM ingredient_requests WHERE request_id = $testId");
        echo "<p style='color: blue;'>✓ Test data cleaned up</p>";
        
    } catch (Exception $e) {
        echo "<p style='color: red;'>❌ Test insertion failed: " . $e->getMessage() . "</p>";
    }
    
} catch (Exception $e) {
    echo "<p style='color: red;'>❌ Error: " . $e->getMessage() . "</p>";
}
?> 