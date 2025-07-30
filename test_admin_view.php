<?php
require_once 'db_connect.php';

echo "<h2>Testing Admin View of Ingredient Requests</h2>";

try {
    // Simulate the exact query that the admin page uses
    echo "<h3>1. Admin Query Test (Same as ingredient_requests_ajax.php):</h3>";
    
    $query = "SELECT r.*, b.branch_name, u.user_name as updated_by_name
              FROM ingredient_requests r 
              LEFT JOIN pos_branch b ON r.branch_id = b.branch_id 
              LEFT JOIN pos_user u ON r.updated_by = u.user_id
              WHERE 1=1
              ORDER BY r.request_date DESC";
    
    $stmt = $pdo->prepare($query);
    $stmt->execute();
    $requests = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    echo "<p>Found " . count($requests) . " requests</p>";
    
    if (empty($requests)) {
        echo "<p style='color: orange;'>⚠️ No requests found with admin query</p>";
    } else {
        echo "<table border='1' style='border-collapse: collapse; width: 100%;'>";
        echo "<tr><th>ID</th><th>Branch</th><th>Date</th><th>Status</th><th>Updated By</th></tr>";
        foreach ($requests as $request) {
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
    
    // Check if there are any pending requests specifically
    echo "<h3>2. Pending Requests Only:</h3>";
    $pendingQuery = "SELECT r.*, b.branch_name 
                     FROM ingredient_requests r 
                     LEFT JOIN pos_branch b ON r.branch_id = b.branch_id 
                     WHERE r.status = 'pending'
                     ORDER BY r.request_date DESC";
    
    $pendingRequests = $pdo->query($pendingQuery)->fetchAll(PDO::FETCH_ASSOC);
    echo "<p>Found " . count($pendingRequests) . " pending requests</p>";
    
    if (!empty($pendingRequests)) {
        echo "<ul>";
        foreach ($pendingRequests as $request) {
            echo "<li>ID: {$request['request_id']} - Branch: {$request['branch_name']} - Date: {$request['request_date']}</li>";
        }
        echo "</ul>";
    }
    
    // Check all requests without any joins
    echo "<h3>3. All Requests (Raw Data):</h3>";
    $allRequests = $pdo->query("SELECT * FROM ingredient_requests ORDER BY request_date DESC LIMIT 10")->fetchAll(PDO::FETCH_ASSOC);
    echo "<p>Found " . count($allRequests) . " total requests</p>";
    
    if (!empty($allRequests)) {
        echo "<table border='1' style='border-collapse: collapse; width: 100%;'>";
        echo "<tr><th>ID</th><th>Branch ID</th><th>Date</th><th>Status</th><th>Ingredients</th></tr>";
        foreach ($allRequests as $request) {
            echo "<tr>";
            echo "<td>" . $request['request_id'] . "</td>";
            echo "<td>" . $request['branch_id'] . "</td>";
            echo "<td>" . $request['request_date'] . "</td>";
            echo "<td>" . $request['status'] . "</td>";
            echo "<td>" . htmlspecialchars(substr($request['ingredients'], 0, 50)) . "...</td>";
            echo "</tr>";
        }
        echo "</table>";
    }
    
    // Test creating a new request
    echo "<h3>4. Creating Test Request:</h3>";
    $testBranchId = $pdo->query("SELECT branch_id FROM pos_branch LIMIT 1")->fetchColumn();
    $testIngredients = json_encode([['ingredient_id' => 1, 'quantity' => 10]]);
    
    $insertStmt = $pdo->prepare("INSERT INTO ingredient_requests (branch_id, request_date, ingredients, status, notes) VALUES (?, NOW(), ?, ?, ?)");
    $insertStmt->execute([$testBranchId, $testIngredients, 'pending', 'Test request from admin view test']);
    $newRequestId = $pdo->lastInsertId();
    
    echo "<p style='color: green;'>✅ Created test request with ID: $newRequestId</p>";
    
    // Verify the new request is visible
    $newRequest = $pdo->query("SELECT r.*, b.branch_name FROM ingredient_requests r LEFT JOIN pos_branch b ON r.branch_id = b.branch_id WHERE r.request_id = $newRequestId")->fetch(PDO::FETCH_ASSOC);
    
    if ($newRequest) {
        echo "<p style='color: green;'>✅ New request is visible in database</p>";
        echo "<p>Branch: {$newRequest['branch_name']}, Status: {$newRequest['status']}</p>";
    } else {
        echo "<p style='color: red;'>❌ New request not found</p>";
    }
    
    // Clean up test data
    $pdo->exec("DELETE FROM ingredient_requests WHERE request_id = $newRequestId");
    echo "<p style='color: blue;'>✓ Test data cleaned up</p>";
    
    echo "<h3 style='color: green;'>✅ Admin view test completed!</h3>";
    echo "<p><a href='ingredient_requests.php'>Go to Admin Ingredient Requests Page</a></p>";
    
} catch (Exception $e) {
    echo "<p style='color: red;'>❌ Error: " . $e->getMessage() . "</p>";
}
?> 