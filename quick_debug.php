<?php
require_once 'db_connect.php';

echo "<h2>Quick Debug - Stockman Requests Issue</h2>";

try {
    // 1. Check if ingredient_requests table exists
    echo "<h3>1. Table Check:</h3>";
    $tables = $pdo->query("SHOW TABLES LIKE 'ingredient_requests'")->fetchAll();
    if (empty($tables)) {
        echo "<p style='color: red;'>❌ ingredient_requests table does not exist!</p>";
        exit;
    } else {
        echo "<p style='color: green;'>✅ ingredient_requests table exists</p>";
    }
    
    // 2. Check table structure
    echo "<h3>2. Table Structure:</h3>";
    $columns = $pdo->query("DESCRIBE ingredient_requests")->fetchAll(PDO::FETCH_ASSOC);
    echo "<table border='1'>";
    echo "<tr><th>Column</th><th>Type</th><th>Null</th><th>Key</th><th>Default</th></tr>";
    foreach ($columns as $col) {
        echo "<tr>";
        echo "<td>{$col['Field']}</td>";
        echo "<td>{$col['Type']}</td>";
        echo "<td>{$col['Null']}</td>";
        echo "<td>{$col['Key']}</td>";
        echo "<td>{$col['Default']}</td>";
        echo "</tr>";
    }
    echo "</table>";
    
    // 3. Count total requests
    echo "<h3>3. Request Counts:</h3>";
    $totalRequests = $pdo->query("SELECT COUNT(*) FROM ingredient_requests")->fetchColumn();
    echo "<p>Total requests: $totalRequests</p>";
    
    $pendingRequests = $pdo->query("SELECT COUNT(*) FROM ingredient_requests WHERE status = 'pending'")->fetchColumn();
    echo "<p>Pending requests: $pendingRequests</p>";
    
    // 4. Show recent requests
    echo "<h3>4. Recent Requests (Last 5):</h3>";
    $recentRequests = $pdo->query("SELECT * FROM ingredient_requests ORDER BY request_date DESC LIMIT 5")->fetchAll(PDO::FETCH_ASSOC);
    
    if (empty($recentRequests)) {
        echo "<p style='color: orange;'>⚠️ No requests found</p>";
    } else {
        echo "<table border='1' style='border-collapse: collapse; width: 100%;'>";
        echo "<tr><th>ID</th><th>Branch ID</th><th>Date</th><th>Status</th><th>Ingredients</th></tr>";
        foreach ($recentRequests as $request) {
            echo "<tr>";
            echo "<td>{$request['request_id']}</td>";
            echo "<td>{$request['branch_id']}</td>";
            echo "<td>{$request['request_date']}</td>";
            echo "<td>{$request['status']}</td>";
            echo "<td>" . htmlspecialchars(substr($request['ingredients'], 0, 50)) . "...</td>";
            echo "</tr>";
        }
        echo "</table>";
    }
    
    // 5. Check stockman users
    echo "<h3>5. Stockman Users:</h3>";
    $stockmen = $pdo->query("SELECT user_id, user_name, user_type, branch_id FROM pos_user WHERE user_type = 'Stockman'")->fetchAll(PDO::FETCH_ASSOC);
    
    if (empty($stockmen)) {
        echo "<p style='color: red;'>❌ No stockman users found</p>";
    } else {
        echo "<table border='1' style='border-collapse: collapse; width: 100%;'>";
        echo "<tr><th>User ID</th><th>Name</th><th>Type</th><th>Branch ID</th></tr>";
        foreach ($stockmen as $stockman) {
            echo "<tr>";
            echo "<td>{$stockman['user_id']}</td>";
            echo "<td>{$stockman['user_name']}</td>";
            echo "<td>{$stockman['user_type']}</td>";
            echo "<td>" . ($stockman['branch_id'] ?? 'NULL') . "</td>";
            echo "</tr>";
        }
        echo "</table>";
    }
    
    // 6. Check branches
    echo "<h3>6. Branches:</h3>";
    $branches = $pdo->query("SELECT branch_id, branch_name FROM pos_branch")->fetchAll(PDO::FETCH_ASSOC);
    
    if (empty($branches)) {
        echo "<p style='color: red;'>❌ No branches found</p>";
    } else {
        echo "<table border='1' style='border-collapse: collapse; width: 100%;'>";
        echo "<tr><th>Branch ID</th><th>Branch Name</th></tr>";
        foreach ($branches as $branch) {
            echo "<tr>";
            echo "<td>{$branch['branch_id']}</td>";
            echo "<td>{$branch['branch_name']}</td>";
            echo "</tr>";
        }
        echo "</table>";
    }
    
    // 7. Test admin query directly
    echo "<h3>7. Admin Query Test:</h3>";
    $adminQuery = "SELECT r.*, b.branch_name, u.user_name as updated_by_name
                   FROM ingredient_requests r 
                   LEFT JOIN pos_branch b ON r.branch_id = b.branch_id 
                   LEFT JOIN pos_user u ON r.updated_by = u.user_id
                   WHERE 1=1
                   ORDER BY r.request_date DESC";
    
    $adminResults = $pdo->query($adminQuery)->fetchAll(PDO::FETCH_ASSOC);
    echo "<p>Admin query found: " . count($adminResults) . " requests</p>";
    
    if (!empty($adminResults)) {
        echo "<table border='1' style='border-collapse: collapse; width: 100%;'>";
        echo "<tr><th>ID</th><th>Branch</th><th>Date</th><th>Status</th><th>Updated By</th></tr>";
        foreach ($adminResults as $request) {
            echo "<tr>";
            echo "<td>{$request['request_id']}</td>";
            echo "<td>" . ($request['branch_name'] ?? 'Unknown') . "</td>";
            echo "<td>{$request['request_date']}</td>";
            echo "<td>{$request['status']}</td>";
            echo "<td>" . ($request['updated_by_name'] ?? 'N/A') . "</td>";
            echo "</tr>";
        }
        echo "</table>";
    }
    
    echo "<h3 style='color: green;'>✅ Debug completed!</h3>";
    
} catch (Exception $e) {
    echo "<p style='color: red;'>❌ Error: " . $e->getMessage() . "</p>";
}
?> 