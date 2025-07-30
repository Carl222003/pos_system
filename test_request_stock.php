<?php
require_once 'db_connect.php';

echo "<h2>Testing Request Stock Functionality</h2>";

try {
    // Test 1: Check if stockmen have branch assignments
    echo "<h3>1. Checking Stockman Branch Assignments:</h3>";
    $stockmen = $pdo->query("SELECT u.user_id, u.user_name, u.branch_id, b.branch_name 
                             FROM pos_user u 
                             LEFT JOIN pos_branch b ON u.branch_id = b.branch_id 
                             WHERE u.user_type = 'Stockman'")->fetchAll(PDO::FETCH_ASSOC);
    
    if (empty($stockmen)) {
        echo "❌ No stockmen found in the database<br>";
    } else {
        foreach ($stockmen as $stockman) {
            $status = $stockman['branch_id'] ? "✅ Assigned to {$stockman['branch_name']}" : "❌ Not assigned";
            echo "Stockman: {$stockman['user_name']} - $status<br>";
        }
    }
    
    // Test 2: Check if ingredient_requests table has required columns
    echo "<h3>2. Checking ingredient_requests table structure:</h3>";
    $columns = $pdo->query("SHOW COLUMNS FROM ingredient_requests")->fetchAll(PDO::FETCH_COLUMN);
    $requiredColumns = ['branch_id', 'request_date', 'ingredients', 'status', 'delivery_status'];
    
    foreach ($requiredColumns as $column) {
        if (in_array($column, $columns)) {
            echo "✅ $column column exists<br>";
        } else {
            echo "❌ $column column missing<br>";
        }
    }
    
    // Test 3: Check if pos_user table has branch_id column
    echo "<h3>3. Checking pos_user table structure:</h3>";
    $userColumns = $pdo->query("SHOW COLUMNS FROM pos_user")->fetchAll(PDO::FETCH_COLUMN);
    if (in_array('branch_id', $userColumns)) {
        echo "✅ branch_id column exists in pos_user table<br>";
    } else {
        echo "❌ branch_id column missing in pos_user table<br>";
    }
    
    // Test 4: Check if branches exist
    echo "<h3>4. Checking available branches:</h3>";
    $branches = $pdo->query("SELECT branch_id, branch_name FROM pos_branch")->fetchAll(PDO::FETCH_ASSOC);
    if (empty($branches)) {
        echo "❌ No branches found in the database<br>";
    } else {
        echo "Available branches:<br>";
        foreach ($branches as $branch) {
            echo "- {$branch['branch_name']} (ID: {$branch['branch_id']})<br>";
        }
    }
    
    // Test 5: Simulate a request submission
    echo "<h3>5. Testing request submission logic:</h3>";
    
    // Get first stockman and branch
    $firstStockman = $pdo->query("SELECT user_id, branch_id FROM pos_user WHERE user_type = 'Stockman' LIMIT 1")->fetch(PDO::FETCH_ASSOC);
    $firstBranch = $pdo->query("SELECT branch_id FROM pos_branch LIMIT 1")->fetch(PDO::FETCH_ASSOC);
    
    if ($firstStockman && $firstBranch) {
        echo "✅ Found stockman (ID: {$firstStockman['user_id']}) and branch (ID: {$firstBranch['branch_id']})<br>";
        
        // Test the INSERT query
        $testIngredients = json_encode([['ingredient_id' => 1, 'quantity' => 5]]);
        $stmt = $pdo->prepare("INSERT INTO ingredient_requests (branch_id, request_date, ingredients, status, notes) VALUES (?, NOW(), ?, ?, ?)");
        
        try {
            $stmt->execute([$firstBranch['branch_id'], $testIngredients, 'pending', 'Test request']);
            $requestId = $pdo->lastInsertId();
            echo "✅ Test request inserted successfully (ID: $requestId)<br>";
            
            // Clean up test data
            $pdo->exec("DELETE FROM ingredient_requests WHERE request_id = $requestId");
            echo "✅ Test data cleaned up<br>";
            
        } catch (Exception $e) {
            echo "❌ Test request failed: " . $e->getMessage() . "<br>";
        }
    } else {
        echo "❌ Cannot test request submission - missing stockman or branch<br>";
    }
    
    echo "<h3 style='color: green;'>✅ Testing completed!</h3>";
    echo "<p><a href='stockman_dashboard.php'>Go to Stockman Dashboard</a></p>";
    
} catch (Exception $e) {
    echo "<p style='color: red;'>❌ Error: " . $e->getMessage() . "</p>";
}
?> 