<?php
require_once 'db_connect.php';

echo "<h2>Test Pagination with Multiple Requests</h2>";

try {
    // Get current count
    $currentCount = $pdo->query("SELECT COUNT(*) FROM ingredient_requests")->fetchColumn();
    echo "<h3>1. Current Request Count: $currentCount</h3>";
    
    // Create multiple test requests if we have less than 15
    if ($currentCount < 15) {
        echo "<h3>2. Creating Test Requests:</h3>";
        
        $branch = $pdo->query("SELECT branch_id FROM pos_branch LIMIT 1")->fetchColumn();
        
        for ($i = 1; $i <= 20; $i++) {
            $ingredients = json_encode([['ingredient_id' => 1, 'quantity' => $i]]);
            $notes = "Test request #$i for pagination - " . date('Y-m-d H:i:s');
            
            $stmt = $pdo->prepare("INSERT INTO ingredient_requests (branch_id, request_date, ingredients, status, notes) VALUES (?, NOW(), ?, ?, ?)");
            $stmt->execute([$branch, $ingredients, 'pending', $notes]);
            
            echo "<p>Created test request #$i</p>";
        }
        
        echo "<p style='color: green;'>✅ Created 20 test requests</p>";
    } else {
        echo "<p style='color: blue;'>ℹ️ Already have enough requests for pagination testing</p>";
    }
    
    // Show total count now
    $newCount = $pdo->query("SELECT COUNT(*) FROM ingredient_requests")->fetchColumn();
    echo "<h3>3. Total Requests Now: $newCount</h3>";
    
    // Show recent requests
    echo "<h3>4. Recent Requests (Last 10):</h3>";
    $recentRequests = $pdo->query("SELECT r.*, b.branch_name FROM ingredient_requests r LEFT JOIN pos_branch b ON r.branch_id = b.branch_id ORDER BY r.request_date DESC LIMIT 10")->fetchAll(PDO::FETCH_ASSOC);
    
    echo "<table border='1' style='border-collapse: collapse; width: 100%;'>";
    echo "<tr><th>ID</th><th>Branch</th><th>Date</th><th>Status</th><th>Notes</th></tr>";
    foreach ($recentRequests as $request) {
        echo "<tr>";
        echo "<td>{$request['request_id']}</td>";
        echo "<td>{$request['branch_name']}</td>";
        echo "<td>{$request['request_date']}</td>";
        echo "<td>{$request['status']}</td>";
        echo "<td>" . htmlspecialchars(substr($request['notes'], 0, 30)) . "...</td>";
        echo "</tr>";
    }
    echo "</table>";
    
    echo "<h3 style='color: green;'>✅ Pagination test data ready!</h3>";
    echo "<p><a href='ingredient_requests.php'>Go to Admin Ingredient Requests Page</a></p>";
    echo "<p><strong>Expected:</strong> With $newCount requests and 10 per page, you should see pagination with Previous/Next buttons.</p>";
    
} catch (Exception $e) {
    echo "<p style='color: red;'>❌ Error: " . $e->getMessage() . "</p>";
}
?> 