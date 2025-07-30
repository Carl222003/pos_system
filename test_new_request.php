<?php
require_once 'db_connect.php';

echo "<h2>Test New Request Visibility</h2>";

try {
    // Get a stockman and branch
    $stockman = $pdo->query("SELECT user_id, user_name, branch_id FROM pos_user WHERE user_type = 'Stockman' LIMIT 1")->fetch(PDO::FETCH_ASSOC);
    $branch = $pdo->query("SELECT branch_id, branch_name FROM pos_branch LIMIT 1")->fetch(PDO::FETCH_ASSOC);
    
    if (!$stockman || !$branch) {
        echo "<p style='color: red;'>❌ Missing stockman or branch data</p>";
        exit;
    }
    
    echo "<h3>1. Current State:</h3>";
    $currentCount = $pdo->query("SELECT COUNT(*) FROM ingredient_requests")->fetchColumn();
    echo "<p>Current total requests: $currentCount</p>";
    
    $pendingCount = $pdo->query("SELECT COUNT(*) FROM ingredient_requests WHERE status = 'pending'")->fetchColumn();
    echo "<p>Current pending requests: $pendingCount</p>";
    
    // Create a test request
    echo "<h3>2. Creating Test Request:</h3>";
    $testIngredients = json_encode([['ingredient_id' => 1, 'quantity' => 15]]);
    
    $stmt = $pdo->prepare("INSERT INTO ingredient_requests (branch_id, request_date, ingredients, status, notes) VALUES (?, NOW(), ?, ?, ?)");
    $stmt->execute([
        $branch['branch_id'],
        $testIngredients,
        'pending',
        'Test request for visibility check - ' . date('Y-m-d H:i:s')
    ]);
    
    $newRequestId = $pdo->lastInsertId();
    echo "<p style='color: green;'>✅ Created test request with ID: $newRequestId</p>";
    
    // Check if it appears in admin query
    echo "<h3>3. Checking Admin Query:</h3>";
    $adminQuery = "SELECT r.*, b.branch_name, u.user_name as updated_by_name
                   FROM ingredient_requests r 
                   LEFT JOIN pos_branch b ON r.branch_id = b.branch_id 
                   LEFT JOIN pos_user u ON r.updated_by = u.user_id
                   WHERE 1=1
                   ORDER BY r.request_date DESC";
    
    $adminResults = $pdo->query($adminQuery)->fetchAll(PDO::FETCH_ASSOC);
    echo "<p>Admin query found: " . count($adminResults) . " requests</p>";
    
    // Find our new request
    $found = false;
    foreach ($adminResults as $request) {
        if ($request['request_id'] == $newRequestId) {
            $found = true;
            echo "<p style='color: green;'>✅ New request found in admin query!</p>";
            echo "<p>Branch: {$request['branch_name']}</p>";
            echo "<p>Status: {$request['status']}</p>";
            echo "<p>Date: {$request['request_date']}</p>";
            break;
        }
    }
    
    if (!$found) {
        echo "<p style='color: red;'>❌ New request NOT found in admin query</p>";
    }
    
    // Show recent requests
    echo "<h3>4. Recent Requests (Last 5):</h3>";
    $recentRequests = $pdo->query("SELECT r.*, b.branch_name FROM ingredient_requests r LEFT JOIN pos_branch b ON r.branch_id = b.branch_id ORDER BY r.request_date DESC LIMIT 5")->fetchAll(PDO::FETCH_ASSOC);
    
    echo "<table border='1' style='border-collapse: collapse; width: 100%;'>";
    echo "<tr><th>ID</th><th>Branch</th><th>Date</th><th>Status</th></tr>";
    foreach ($recentRequests as $request) {
        $highlight = ($request['request_id'] == $newRequestId) ? 'style="background-color: yellow;"' : '';
        echo "<tr $highlight>";
        echo "<td>{$request['request_id']}</td>";
        echo "<td>{$request['branch_name']}</td>";
        echo "<td>{$request['request_date']}</td>";
        echo "<td>{$request['status']}</td>";
        echo "</tr>";
    }
    echo "</table>";
    
    echo "<h3 style='color: green;'>✅ Test completed!</h3>";
    echo "<p><a href='ingredient_requests.php'>Go to Admin Ingredient Requests Page</a></p>";
    echo "<p><strong>Note:</strong> The test request will remain in the database. You can delete it manually if needed.</p>";
    
} catch (Exception $e) {
    echo "<p style='color: red;'>❌ Error: " . $e->getMessage() . "</p>";
}
?> 