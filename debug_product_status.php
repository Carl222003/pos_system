<?php
require_once 'db_connect.php';

echo "<h2>Product Status Debug</h2>";

try {
    // Check table structure
    echo "<h3>1. Table Structure:</h3>";
    $stmt = $pdo->query("DESCRIBE pos_product");
    $columns = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    $statusColumn = 'product_status';
    $hasProductStatus = false;
    $hasStatus = false;
    
    echo "<ul>";
    foreach ($columns as $column) {
        echo "<li><strong>{$column['Field']}</strong>: {$column['Type']}</li>";
        if ($column['Field'] === 'product_status') {
            $hasProductStatus = true;
        }
        if ($column['Field'] === 'status') {
            $hasStatus = true;
        }
    }
    echo "</ul>";
    
    // Determine status column
    if ($hasStatus && !$hasProductStatus) {
        $statusColumn = 'status';
    }
    
    echo "<p><strong>Status column being used:</strong> $statusColumn</p>";
    
    // Check all products and their status
    echo "<h3>2. All Products and Status:</h3>";
    $query = "SELECT product_id, product_name, $statusColumn as status FROM pos_product ORDER BY product_id";
    $stmt = $pdo->query($query);
    $products = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    if (empty($products)) {
        echo "<p>No products found in database.</p>";
    } else {
        echo "<table border='1' style='border-collapse: collapse;'>";
        echo "<tr><th>ID</th><th>Name</th><th>Status</th><th>Actions</th></tr>";
        
        $availableCount = 0;
        $unavailableCount = 0;
        
        foreach ($products as $product) {
            $status = $product['status'];
            if ($status === 'Available') {
                $availableCount++;
            } elseif ($status === 'Unavailable') {
                $unavailableCount++;
            }
            
            $statusColor = $status === 'Available' ? 'green' : ($status === 'Unavailable' ? 'red' : 'gray');
            
            echo "<tr>";
            echo "<td>{$product['product_id']}</td>";
            echo "<td>{$product['product_name']}</td>";
            echo "<td style='color: $statusColor; font-weight: bold;'>{$status}</td>";
            echo "<td>";
            
            // Toggle button
            $newStatus = $status === 'Available' ? 'Unavailable' : 'Available';
            echo "<form method='post' style='display: inline;'>";
            echo "<input type='hidden' name='toggle_product_id' value='{$product['product_id']}'>";
            echo "<input type='hidden' name='toggle_new_status' value='$newStatus'>";
            echo "<button type='submit' name='toggle_status'>Make $newStatus</button>";
            echo "</form>";
            
            echo "</td>";
            echo "</tr>";
        }
        echo "</table>";
        
        echo "<h3>3. Status Summary:</h3>";
        echo "<ul>";
        echo "<li><strong>Available:</strong> $availableCount products</li>";
        echo "<li><strong>Unavailable:</strong> $unavailableCount products</li>";
        echo "<li><strong>Total:</strong> " . count($products) . " products</li>";
        echo "</ul>";
    }
    
    // Handle toggle action
    if (isset($_POST['toggle_status'])) {
        $productId = intval($_POST['toggle_product_id']);
        $newStatus = $_POST['toggle_new_status'];
        
        echo "<h3>4. Toggle Result:</h3>";
        
        $updateQuery = "UPDATE pos_product SET $statusColumn = ? WHERE product_id = ?";
        $stmt = $pdo->prepare($updateQuery);
        $success = $stmt->execute([$newStatus, $productId]);
        
        if ($success) {
            echo "<p style='color: green;'>✅ Successfully updated product ID $productId to status: $newStatus</p>";
            echo "<p><a href='debug_product_status.php'>Refresh to see changes</a></p>";
        } else {
            echo "<p style='color: red;'>❌ Failed to update product status</p>";
        }
    }
    
    // Test the API endpoint
    echo "<h3>5. API Test:</h3>";
    echo "<p>Testing get_stockman_products.php with different filters:</p>";
    
    $testFilters = [
        ['name' => 'All products', 'params' => ''],
        ['name' => 'Available only', 'params' => 'status=Available'],
        ['name' => 'Unavailable only', 'params' => 'status=Unavailable']
    ];
    
    foreach ($testFilters as $test) {
        echo "<h4>{$test['name']}:</h4>";
        $url = "get_stockman_products.php?" . $test['params'];
        echo "<p><strong>URL:</strong> $url</p>";
        
        // Make a simple curl request to test
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, "http://" . $_SERVER['HTTP_HOST'] . dirname($_SERVER['REQUEST_URI']) . "/" . $url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_COOKIE, session_name() . "=" . session_id());
        $response = curl_exec($ch);
        curl_close($ch);
        
        if ($response) {
            $data = json_decode($response, true);
            if ($data && isset($data['success'])) {
                echo "<p><strong>Products found:</strong> " . ($data['total_count'] ?? 0) . "</p>";
                if (isset($data['debug_info'])) {
                    echo "<p><strong>Status column used:</strong> " . $data['debug_info']['status_column_used'] . "</p>";
                }
            } else {
                echo "<p style='color: red;'>API Error or invalid response</p>";
            }
        } else {
            echo "<p style='color: red;'>Failed to call API</p>";
        }
    }
    
} catch (Exception $e) {
    echo "<p style='color: red;'>Error: " . $e->getMessage() . "</p>";
}
?>

<style>
table { border-collapse: collapse; margin: 10px 0; }
th, td { padding: 8px; text-align: left; border: 1px solid #ccc; }
th { background-color: #f2f2f2; }
</style>
