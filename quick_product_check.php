<?php
require_once 'db_connect.php';

// Simple check to see what products exist and their status
try {
    // Check if we have product_status or status column
    $stmt = $pdo->query("DESCRIBE pos_product");
    $columns = $stmt->fetchAll(PDO::FETCH_COLUMN);
    
    $statusColumn = 'product_status';
    if (in_array('status', $columns) && !in_array('product_status', $columns)) {
        $statusColumn = 'status';
    }
    
    echo "<h2>🔍 Quick Product Status Check</h2>";
    echo "<p><strong>Status column being used:</strong> $statusColumn</p>";
    
    // Get all products
    $query = "SELECT product_id, product_name, $statusColumn as status FROM pos_product ORDER BY product_id";
    $stmt = $pdo->query($query);
    $products = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    $available = 0;
    $unavailable = 0;
    $other = 0;
    
    echo "<h3>📋 All Products:</h3>";
    echo "<table border='1' style='border-collapse: collapse; width: 100%;'>";
    echo "<tr style='background: #f5f5f5;'><th>ID</th><th>Product Name</th><th>Status</th></tr>";
    
    foreach ($products as $product) {
        $status = $product['status'];
        $color = 'black';
        
        if ($status === 'Available') {
            $available++;
            $color = 'green';
        } elseif ($status === 'Unavailable') {
            $unavailable++;
            $color = 'red';
        } else {
            $other++;
            $color = 'orange';
        }
        
        echo "<tr>";
        echo "<td>{$product['product_id']}</td>";
        echo "<td>{$product['product_name']}</td>";
        echo "<td style='color: $color; font-weight: bold;'>{$status}</td>";
        echo "</tr>";
    }
    
    echo "</table>";
    
    echo "<h3>📊 Summary:</h3>";
    echo "<ul>";
    echo "<li><span style='color: green; font-weight: bold;'>Available:</span> $available products</li>";
    echo "<li><span style='color: red; font-weight: bold;'>Unavailable:</span> $unavailable products</li>";
    echo "<li><span style='color: orange; font-weight: bold;'>Other status:</span> $other products</li>";
    echo "<li><strong>Total:</strong> " . count($products) . " products</li>";
    echo "</ul>";
    
    // Test the API calls
    echo "<h3>🧪 API Test Results:</h3>";
    
    // Test 1: All products
    echo "<h4>Test 1: All products (no filter)</h4>";
    $url1 = "http://" . $_SERVER['HTTP_HOST'] . dirname($_SERVER['REQUEST_URI']) . "/get_stockman_products.php";
    echo "<p><strong>URL:</strong> $url1</p>";
    
    // Test 2: Available only
    echo "<h4>Test 2: Available products only</h4>";
    $url2 = $url1 . "?status=Available";
    echo "<p><strong>URL:</strong> $url2</p>";
    
    // Test 3: Unavailable only
    echo "<h4>Test 3: Unavailable products only</h4>";
    $url3 = $url1 . "?status=Unavailable";
    echo "<p><strong>URL:</strong> $url3</p>";
    
    echo "<p><em>Note: Open these URLs in new tabs to see the API responses</em></p>";
    
    // Show the exact query that would be used
    echo "<h3>🔧 Query Examples:</h3>";
    echo "<h4>For All Products:</h4>";
    echo "<code>SELECT * FROM pos_product WHERE $statusColumn IN ('Available', 'Unavailable')</code>";
    
    echo "<h4>For Available Only:</h4>";
    echo "<code>SELECT * FROM pos_product WHERE $statusColumn IN ('Available', 'Unavailable') AND $statusColumn = 'Available'</code>";
    
    echo "<h4>For Unavailable Only:</h4>";
    echo "<code>SELECT * FROM pos_product WHERE $statusColumn IN ('Available', 'Unavailable') AND $statusColumn = 'Unavailable'</code>";
    
} catch (Exception $e) {
    echo "<p style='color: red;'>Error: " . $e->getMessage() . "</p>";
}
?>

<style>
table { border-collapse: collapse; margin: 10px 0; width: 100%; }
th, td { padding: 8px; text-align: left; border: 1px solid #ccc; }
th { background-color: #f2f2f2; font-weight: bold; }
code { background: #f5f5f5; padding: 2px 4px; border-radius: 3px; }
</style>
