<?php
require_once 'db_connect.php';

// Test script to verify product status functionality
echo "<h2>Product Status Test</h2>";

try {
    // Check table structure
    echo "<h3>1. Checking pos_product table structure:</h3>";
    $stmt = $pdo->query("DESCRIBE pos_product");
    $columns = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    echo "<table border='1'>";
    echo "<tr><th>Field</th><th>Type</th><th>Null</th><th>Key</th><th>Default</th><th>Extra</th></tr>";
    foreach ($columns as $column) {
        echo "<tr>";
        echo "<td>{$column['Field']}</td>";
        echo "<td>{$column['Type']}</td>";
        echo "<td>{$column['Null']}</td>";
        echo "<td>{$column['Key']}</td>";
        echo "<td>{$column['Default']}</td>";
        echo "<td>{$column['Extra']}</td>";
        echo "</tr>";
    }
    echo "</table>";
    
    // Determine status column
    $statusColumn = 'product_status';
    if (in_array('status', array_column($columns, 'Field')) && !in_array('product_status', array_column($columns, 'Field'))) {
        $statusColumn = 'status';
    }
    
    echo "<p><strong>Status column detected:</strong> $statusColumn</p>";
    
    // Check current products
    echo "<h3>2. Current products and their status:</h3>";
    $stmt = $pdo->query("SELECT product_id, product_name, $statusColumn FROM pos_product LIMIT 10");
    $products = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    if (empty($products)) {
        echo "<p>No products found in the database.</p>";
    } else {
        echo "<table border='1'>";
        echo "<tr><th>Product ID</th><th>Product Name</th><th>Status</th><th>Test Action</th></tr>";
        foreach ($products as $product) {
            $currentStatus = $product[$statusColumn];
            $newStatus = $currentStatus === 'Available' ? 'Unavailable' : 'Available';
            echo "<tr>";
            echo "<td>{$product['product_id']}</td>";
            echo "<td>{$product['product_name']}</td>";
            echo "<td><strong>$currentStatus</strong></td>";
            echo "<td>";
            echo "<form method='post' style='display:inline;'>";
            echo "<input type='hidden' name='test_product_id' value='{$product['product_id']}'>";
            echo "<input type='hidden' name='test_new_status' value='$newStatus'>";
            echo "<button type='submit' name='test_update'>Change to $newStatus</button>";
            echo "</form>";
            echo "</td>";
            echo "</tr>";
        }
        echo "</table>";
    }
    
    // Handle test update
    if (isset($_POST['test_update'])) {
        $test_product_id = intval($_POST['test_product_id']);
        $test_new_status = $_POST['test_new_status'];
        
        echo "<h3>3. Test Update Result:</h3>";
        
        $updateQuery = "UPDATE pos_product SET $statusColumn = ? WHERE product_id = ?";
        $stmt = $pdo->prepare($updateQuery);
        $success = $stmt->execute([$test_new_status, $test_product_id]);
        
        if ($success) {
            echo "<p style='color: green;'>✅ Successfully updated product ID $test_product_id to status: $test_new_status</p>";
            echo "<p><a href='test_product_status.php'>Refresh page to see changes</a></p>";
        } else {
            echo "<p style='color: red;'>❌ Failed to update product status</p>";
        }
    }
    
} catch (Exception $e) {
    echo "<p style='color: red;'>Error: " . $e->getMessage() . "</p>";
}
?>

<style>
table { border-collapse: collapse; margin: 10px 0; }
th, td { padding: 8px; text-align: left; }
th { background-color: #f2f2f2; }
</style>
