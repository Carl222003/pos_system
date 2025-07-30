<?php
require_once 'db_connect.php';

// Test script to check delivery status update functionality
echo "<h2>Testing Delivery Status Update</h2>";

try {
    // Check if columns exist
    echo "<h3>1. Checking Database Columns</h3>";
    $columns = $pdo->query("SHOW COLUMNS FROM ingredient_requests")->fetchAll(PDO::FETCH_COLUMN);
    
    $required_columns = ['delivery_status', 'delivery_date', 'delivery_notes'];
    foreach ($required_columns as $col) {
        if (in_array($col, $columns)) {
            echo "✓ Column '$col' exists<br>";
        } else {
            echo "✗ Column '$col' missing<br>";
        }
    }
    
    // Check if there are any ingredient requests
    echo "<h3>2. Checking Ingredient Requests</h3>";
    $requests = $pdo->query("SELECT request_id, branch_id, status, delivery_status FROM ingredient_requests LIMIT 5")->fetchAll(PDO::FETCH_ASSOC);
    
    if (count($requests) > 0) {
        echo "Found " . count($requests) . " ingredient requests:<br>";
        foreach ($requests as $req) {
            echo "- Request ID: {$req['request_id']}, Branch: {$req['branch_id']}, Status: {$req['status']}, Delivery Status: {$req['delivery_status']}<br>";
        }
    } else {
        echo "No ingredient requests found<br>";
    }
    
    // Check branches
    echo "<h3>3. Checking Branches</h3>";
    $branches = $pdo->query("SELECT branch_id, branch_name FROM pos_branch LIMIT 5")->fetchAll(PDO::FETCH_ASSOC);
    
    if (count($branches) > 0) {
        echo "Found " . count($branches) . " branches:<br>";
        foreach ($branches as $branch) {
            echo "- Branch ID: {$branch['branch_id']}, Name: {$branch['branch_name']}<br>";
        }
    } else {
        echo "No branches found<br>";
    }
    
} catch (Exception $e) {
    echo "Error: " . $e->getMessage();
}
?> 