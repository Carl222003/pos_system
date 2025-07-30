<?php
require_once 'db_connect.php';

echo "<h2>Debug Stockman Requests</h2>";

try {
    // Check recent requests
    echo "<h3>1. Recent Ingredient Requests (Last 10):</h3>";
    $recentRequests = $pdo->query("SELECT * FROM ingredient_requests ORDER BY request_date DESC LIMIT 10")->fetchAll(PDO::FETCH_ASSOC);
    
    if (empty($recentRequests)) {
        echo "<p style='color: orange;'>⚠️ No requests found in database</p>";
    } else {
        echo "<table border='1' style='border-collapse: collapse; width: 100%;'>";
        echo "<tr><th>ID</th><th>Branch ID</th><th>Date</th><th>Status</th><th>Ingredients</th></tr>";
        foreach ($recentRequests as $request) {
            echo "<tr>";
            echo "<td>" . $request['request_id'] . "</td>";
            echo "<td>" . $request['branch_id'] . "</td>";
            echo "<td>" . $request['request_date'] . "</td>";
            echo "<td>" . $request['status'] . "</td>";
            echo "<td>" . htmlspecialchars(substr($request['ingredients'], 0, 100)) . "...</td>";
            echo "</tr>";
        }
        echo "</table>";
    }
    
    // Check if there are any pending requests specifically
    echo "<h3>2. Pending Requests:</h3>";
    $pendingRequests = $pdo->query("SELECT * FROM ingredient_requests WHERE status = 'pending' ORDER BY request_date DESC")->fetchAll(PDO::FETCH_ASSOC);
    echo "<p>Found " . count($pendingRequests) . " pending requests</p>";
    
    if (!empty($pendingRequests)) {
        echo "<ul>";
        foreach ($pendingRequests as $request) {
            echo "<li>ID: {$request['request_id']} - Branch: {$request['branch_id']} - Date: {$request['request_date']}</li>";
        }
        echo "</ul>";
    }
    
    // Test the exact admin query
    echo "<h3>3. Admin Query Test:</h3>";
    $adminQuery = "SELECT r.*, b.branch_name, u.user_name as updated_by_name
                   FROM ingredient_requests r 
                   LEFT JOIN pos_branch b ON r.branch_id = b.branch_id 
                   LEFT JOIN pos_user u ON r.updated_by = u.user_id
                   WHERE 1=1
                   ORDER BY r.request_date DESC";
    
    $adminResults = $pdo->query($adminQuery)->fetchAll(PDO::FETCH_ASSOC);
    echo "<p>Admin query found " . count($adminResults) . " requests</p>";
    
    if (!empty($adminResults)) {
        echo "<table border='1' style='border-collapse: collapse; width: 100%;'>";
        echo "<tr><th>ID</th><th>Branch</th><th>Date</th><th>Status</th><th>Updated By</th></tr>";
        foreach ($adminResults as $request) {
            echo "<tr>";
            echo "<td>" . $request['request_id'] . "</td>";
            echo "<td>" . ($request['branch_name'] ?? 'Unknown') . "</td>";
            echo "<td>" . $request['request_date'] . "</td>";
            echo "<td>" . $request['status'] . "</td>";
            echo "<td>" . ($request['updated_by_name'] ?? 'N/A') . "</td>";
            echo "</tr>";
        }
        echo "</table>";
    }
    
    // Check stockman users and their branches
    echo "<h3>4. Stockman Users and Branches:</h3>";
    $stockmen = $pdo->query("SELECT user_id, user_name, user_type, branch_id FROM pos_user WHERE user_type = 'Stockman'")->fetchAll(PDO::FETCH_ASSOC);
    
    if (empty($stockmen)) {
        echo "<p style='color: orange;'>⚠️ No stockman users found</p>";
    } else {
        echo "<table border='1' style='border-collapse: collapse; width: 100%;'>";
        echo "<tr><th>User ID</th><th>Name</th><th>Type</th><th>Branch ID</th></tr>";
        foreach ($stockmen as $stockman) {
            echo "<tr>";
            echo "<td>" . $stockman['user_id'] . "</td>";
            echo "<td>" . $stockman['user_name'] . "</td>";
            echo "<td>" . $stockman['user_type'] . "</td>";
            echo "<td>" . ($stockman['branch_id'] ?? 'NULL') . "</td>";
            echo "</tr>";
        }
        echo "</table>";
    }
    
    // Check branches
    echo "<h3>5. Available Branches:</h3>";
    $branches = $pdo->query("SELECT branch_id, branch_name FROM pos_branch")->fetchAll(PDO::FETCH_ASSOC);
    
    if (empty($branches)) {
        echo "<p style='color: orange;'>⚠️ No branches found</p>";
    } else {
        echo "<table border='1' style='border-collapse: collapse; width: 100%;'>";
        echo "<tr><th>Branch ID</th><th>Branch Name</th></tr>";
        foreach ($branches as $branch) {
            echo "<tr>";
            echo "<td>" . $branch['branch_id'] . "</td>";
            echo "<td>" . $branch['branch_name'] . "</td>";
            echo "</tr>";
        }
        echo "</table>";
    }
    
    // Create a test request
    echo "<h3>6. Creating Test Request:</h3>";
    $testBranchId = $pdo->query("SELECT branch_id FROM pos_branch LIMIT 1")->fetchColumn();
    $testIngredients = json_encode([['ingredient_id' => 1, 'quantity' => 5]]);
    
    $insertStmt = $pdo->prepare("INSERT INTO ingredient_requests (branch_id, request_date, ingredients, status, notes) VALUES (?, NOW(), ?, ?, ?)");
    $insertStmt->execute([$testBranchId, $testIngredients, 'pending', 'Test request from debug script']);
    $newRequestId = $pdo->lastInsertId();
    
    echo "<p style='color: green;'>✅ Created test request with ID: $newRequestId</p>";
    
    // Check if the new request appears in admin query
    $newRequest = $pdo->query("SELECT r.*, b.branch_name FROM ingredient_requests r LEFT JOIN pos_branch b ON r.branch_id = b.branch_id WHERE r.request_id = $newRequestId")->fetch(PDO::FETCH_ASSOC);
    
    if ($newRequest) {
        echo "<p style='color: green;'>✅ New request is visible in database</p>";
        echo "<p>Branch: {$newRequest['branch_name']}, Status: {$newRequest['status']}</p>";
        
        // Test admin query specifically for this request
        $adminTestQuery = "SELECT r.*, b.branch_name, u.user_name as updated_by_name
                          FROM ingredient_requests r 
                          LEFT JOIN pos_branch b ON r.branch_id = b.branch_id 
                          LEFT JOIN pos_user u ON r.updated_by = u.user_id
                          WHERE r.request_id = $newRequestId";
        
        $adminTestResult = $pdo->query($adminTestQuery)->fetch(PDO::FETCH_ASSOC);
        
        if ($adminTestResult) {
            echo "<p style='color: green;'>✅ New request is visible in admin query</p>";
        } else {
            echo "<p style='color: red;'>❌ New request NOT visible in admin query</p>";
        }
    } else {
        echo "<p style='color: red;'>❌ New request not found</p>";
    }
    
    // Clean up test data
    $pdo->exec("DELETE FROM ingredient_requests WHERE request_id = $newRequestId");
    echo "<p style='color: blue;'>✓ Test data cleaned up</p>";
    
    echo "<h3 style='color: green;'>✅ Debug completed!</h3>";
    echo "<p><a href='ingredient_requests.php'>Go to Admin Ingredient Requests Page</a></p>";
    
} catch (Exception $e) {
    echo "<p style='color: red;'>❌ Error: " . $e->getMessage() . "</p>";
}
?> 