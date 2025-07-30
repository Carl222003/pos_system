<?php
require_once 'db_connect.php';

echo "<h2>Test Stockman Request Submission</h2>";

try {
    // Get a stockman user
    $stockman = $pdo->query("SELECT user_id, user_name, branch_id FROM pos_user WHERE user_type = 'Stockman' LIMIT 1")->fetch(PDO::FETCH_ASSOC);
    
    if (!$stockman) {
        echo "<p style='color: red;'>❌ No stockman users found</p>";
        exit;
    }
    
    echo "<h3>1. Stockman Info:</h3>";
    echo "<p>User ID: {$stockman['user_id']}</p>";
    echo "<p>Name: {$stockman['user_name']}</p>";
    echo "<p>Branch ID: {$stockman['branch_id']}</p>";
    
    // Get branch name
    $branchName = $pdo->query("SELECT branch_name FROM pos_branch WHERE branch_id = {$stockman['branch_id']}")->fetchColumn();
    echo "<p>Branch Name: $branchName</p>";
    
    // Get an ingredient
    $ingredient = $pdo->query("SELECT ingredient_id, ingredient_name FROM ingredients WHERE branch_id = {$stockman['branch_id']} LIMIT 1")->fetch(PDO::FETCH_ASSOC);
    
    if (!$ingredient) {
        echo "<p style='color: red;'>❌ No ingredients found for this branch</p>";
        exit;
    }
    
    echo "<h3>2. Test Ingredient:</h3>";
    echo "<p>Ingredient ID: {$ingredient['ingredient_id']}</p>";
    echo "<p>Ingredient Name: {$ingredient['ingredient_name']}</p>";
    
    // Simulate the exact request submission
    echo "<h3>3. Submitting Test Request:</h3>";
    
    $ingredients = [$ingredient['ingredient_id']];
    $quantities = [$ingredient['ingredient_id'] => 10];
    
    // Build ingredient list
    $ingredient_list = [];
    foreach ($ingredients as $ingredient_id) {
        $qty = isset($quantities[$ingredient_id]) ? intval($quantities[$ingredient_id]) : 0;
        if ($qty > 0) {
            $ingredient_list[] = [
                'ingredient_id' => $ingredient_id,
                'quantity' => $qty
            ];
        }
    }
    
    $ingredients_json = json_encode($ingredient_list);
    $notes = 'Test request from stockman submission test';
    
    // Insert the request
    $stmt = $pdo->prepare('INSERT INTO ingredient_requests (branch_id, request_date, ingredients, status, notes) VALUES (?, NOW(), ?, ?, ?)');
    $stmt->execute([
        $stockman['branch_id'],
        $ingredients_json,
        'pending',
        $notes
    ]);
    
    $request_id = $pdo->lastInsertId();
    echo "<p style='color: green;'>✅ Request submitted successfully! ID: $request_id</p>";
    
    // Check if it appears in database
    echo "<h3>4. Checking Database:</h3>";
    $newRequest = $pdo->query("SELECT * FROM ingredient_requests WHERE request_id = $request_id")->fetch(PDO::FETCH_ASSOC);
    
    if ($newRequest) {
        echo "<p style='color: green;'>✅ Request found in database</p>";
        echo "<p>Branch ID: {$newRequest['branch_id']}</p>";
        echo "<p>Status: {$newRequest['status']}</p>";
        echo "<p>Ingredients: " . htmlspecialchars($newRequest['ingredients']) . "</p>";
    } else {
        echo "<p style='color: red;'>❌ Request not found in database</p>";
    }
    
    // Check if it appears in admin query
    echo "<h3>5. Checking Admin Query:</h3>";
    $adminQuery = "SELECT r.*, b.branch_name, u.user_name as updated_by_name
                   FROM ingredient_requests r 
                   LEFT JOIN pos_branch b ON r.branch_id = b.branch_id 
                   LEFT JOIN pos_user u ON r.updated_by = u.user_id
                   WHERE r.request_id = $request_id";
    
    $adminResult = $pdo->query($adminQuery)->fetch(PDO::FETCH_ASSOC);
    
    if ($adminResult) {
        echo "<p style='color: green;'>✅ Request visible in admin query</p>";
        echo "<p>Branch: {$adminResult['branch_name']}</p>";
        echo "<p>Status: {$adminResult['status']}</p>";
        echo "<p>Updated By: " . ($adminResult['updated_by_name'] ?? 'N/A') . "</p>";
    } else {
        echo "<p style='color: red;'>❌ Request NOT visible in admin query</p>";
    }
    
    // Check all pending requests
    echo "<h3>6. All Pending Requests:</h3>";
    $pendingRequests = $pdo->query("SELECT r.*, b.branch_name FROM ingredient_requests r LEFT JOIN pos_branch b ON r.branch_id = b.branch_id WHERE r.status = 'pending' ORDER BY r.request_date DESC")->fetchAll(PDO::FETCH_ASSOC);
    
    echo "<p>Found " . count($pendingRequests) . " pending requests</p>";
    
    if (!empty($pendingRequests)) {
        echo "<table border='1' style='border-collapse: collapse; width: 100%;'>";
        echo "<tr><th>ID</th><th>Branch</th><th>Date</th><th>Status</th></tr>";
        foreach ($pendingRequests as $request) {
            $highlight = ($request['request_id'] == $request_id) ? 'style="background-color: yellow;"' : '';
            echo "<tr $highlight>";
            echo "<td>" . $request['request_id'] . "</td>";
            echo "<td>" . $request['branch_name'] . "</td>";
            echo "<td>" . $request['request_date'] . "</td>";
            echo "<td>" . $request['status'] . "</td>";
            echo "</tr>";
        }
        echo "</table>";
    }
    
    echo "<h3 style='color: green;'>✅ Test completed!</h3>";
    echo "<p><a href='ingredient_requests.php'>Go to Admin Ingredient Requests Page</a></p>";
    echo "<p><strong>Note:</strong> The test request will remain in the database. You can delete it manually if needed.</p>";
    
} catch (Exception $e) {
    echo "<p style='color: red;'>❌ Error: " . $e->getMessage() . "</p>";
}
?> 