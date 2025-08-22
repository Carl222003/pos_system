<?php
require_once 'db_connect.php';
session_start();

echo "<h1>🔍 Product Database Check</h1>";

try {
    // Check table structure first
    $stmt = $pdo->query("DESCRIBE pos_product");
    $columns = $stmt->fetchAll(PDO::FETCH_COLUMN);
    
    $statusColumn = 'product_status';
    if (in_array('status', $columns) && !in_array('product_status', $columns)) {
        $statusColumn = 'status';
    }
    
    echo "<p><strong>Status column being used:</strong> <code>$statusColumn</code></p>";
    
    // Get ALL products and their status
    $query = "SELECT product_id, product_name, $statusColumn as current_status FROM pos_product ORDER BY product_id";
    $stmt = $pdo->query($query);
    $allProducts = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    echo "<h2>📋 ALL Products in Database:</h2>";
    
    if (empty($allProducts)) {
        echo "<p style='color: red;'>❌ No products found in database!</p>";
    } else {
        $availableCount = 0;
        $unavailableCount = 0;
        $otherCount = 0;
        
        echo "<table border='1' style='border-collapse: collapse; width: 100%; margin: 10px 0;'>";
        echo "<tr style='background: #f5f5f5;'>";
        echo "<th>ID</th><th>Product Name</th><th>Current Status</th><th>Action</th>";
        echo "</tr>";
        
        foreach ($allProducts as $product) {
            $status = $product['current_status'];
            $statusColor = 'black';
            $bgColor = 'white';
            
            if ($status === 'Available') {
                $availableCount++;
                $statusColor = 'green';
                $bgColor = '#e8f5e8';
            } elseif ($status === 'Unavailable') {
                $unavailableCount++;
                $statusColor = 'red';
                $bgColor = '#ffe8e8';
            } else {
                $otherCount++;
                $statusColor = 'orange';
                $bgColor = '#fff3cd';
            }
            
            echo "<tr style='background: $bgColor;'>";
            echo "<td>{$product['product_id']}</td>";
            echo "<td><strong>{$product['product_name']}</strong></td>";
            echo "<td style='color: $statusColor; font-weight: bold;'>{$status}</td>";
            echo "<td>";
            
            // Add quick toggle buttons
            if ($status === 'Available') {
                echo "<a href='?toggle={$product['product_id']}&to=Unavailable' style='background: red; color: white; padding: 4px 8px; text-decoration: none; border-radius: 3px;'>Make Unavailable</a>";
            } elseif ($status === 'Unavailable') {
                echo "<a href='?toggle={$product['product_id']}&to=Available' style='background: green; color: white; padding: 4px 8px; text-decoration: none; border-radius: 3px;'>Make Available</a>";
            } else {
                echo "<a href='?toggle={$product['product_id']}&to=Available' style='background: blue; color: white; padding: 4px 8px; text-decoration: none; border-radius: 3px;'>Set to Available</a>";
            }
            
            echo "</td>";
            echo "</tr>";
        }
        
        echo "</table>";
        
        echo "<h3>📊 Status Summary:</h3>";
        echo "<div style='display: flex; gap: 20px; margin: 10px 0;'>";
        echo "<div style='background: #e8f5e8; padding: 10px; border-radius: 5px;'>";
        echo "<strong style='color: green;'>✅ Available: $availableCount</strong>";
        echo "</div>";
        echo "<div style='background: #ffe8e8; padding: 10px; border-radius: 5px;'>";
        echo "<strong style='color: red;'>❌ Unavailable: $unavailableCount</strong>";
        echo "</div>";
        echo "<div style='background: #fff3cd; padding: 10px; border-radius: 5px;'>";
        echo "<strong style='color: orange;'>⚠️ Other: $otherCount</strong>";
        echo "</div>";
        echo "</div>";
    }
    
    // Handle toggle action
    if (isset($_GET['toggle']) && isset($_GET['to'])) {
        $productId = intval($_GET['toggle']);
        $newStatus = $_GET['to'];
        
        echo "<h3>🔄 Status Update:</h3>";
        
        $updateQuery = "UPDATE pos_product SET $statusColumn = ? WHERE product_id = ?";
        $stmt = $pdo->prepare($updateQuery);
        $success = $stmt->execute([$newStatus, $productId]);
        
        if ($success) {
            echo "<div style='background: #d4edda; color: #155724; padding: 10px; border-radius: 5px; margin: 10px 0;'>";
            echo "✅ Successfully updated product ID $productId to: <strong>$newStatus</strong>";
            echo "</div>";
            echo "<p><a href='check_products_now.php' style='background: #007bff; color: white; padding: 8px 15px; text-decoration: none; border-radius: 4px;'>🔄 Refresh Page</a></p>";
        } else {
            echo "<div style='background: #f8d7da; color: #721c24; padding: 10px; border-radius: 5px; margin: 10px 0;'>";
            echo "❌ Failed to update product status";
            echo "</div>";
        }
    }
    
    // Test API calls
    echo "<h3>🧪 API Test:</h3>";
    echo "<p>Let's test the API that your frontend is calling:</p>";
    
    // Simulate the session for API call
    $_SESSION['user_logged_in'] = true;
    $_SESSION['user_type'] = 'Stockman';
    
    $testUrls = [
        'All Products' => 'get_stockman_products.php',
        'Available Only' => 'get_stockman_products.php?status=Available',
        'Unavailable Only' => 'get_stockman_products.php?status=Unavailable'
    ];
    
    foreach ($testUrls as $testName => $url) {
        echo "<h4>$testName:</h4>";
        echo "<p><strong>URL:</strong> <code>$url</code></p>";
        
        // Make internal API call
        ob_start();
        $_GET = [];
        if (strpos($url, '?') !== false) {
            parse_str(parse_url($url, PHP_URL_QUERY), $_GET);
        }
        
        include 'get_stockman_products.php';
        $apiResponse = ob_get_clean();
        
        $data = json_decode($apiResponse, true);
        if ($data && isset($data['success'])) {
            if ($data['success']) {
                $count = $data['total_count'] ?? 0;
                echo "<p style='color: green;'>✅ <strong>Found: $count products</strong></p>";
                
                if ($count > 0 && isset($data['products'])) {
                    echo "<ul>";
                    foreach ($data['products'] as $product) {
                        $statusBadge = $product['product_status'] === 'Available' ? 
                            "<span style='background: green; color: white; padding: 2px 6px; border-radius: 3px; font-size: 12px;'>Available</span>" :
                            "<span style='background: red; color: white; padding: 2px 6px; border-radius: 3px; font-size: 12px;'>Unavailable</span>";
                        echo "<li><strong>{$product['product_name']}</strong> $statusBadge</li>";
                    }
                    echo "</ul>";
                }
                
                if (isset($data['debug_info'])) {
                    echo "<details><summary>Debug Info</summary>";
                    echo "<pre style='background: #f5f5f5; padding: 10px; border-radius: 3px;'>";
                    echo "Status Column: " . $data['debug_info']['status_column_used'] . "\n";
                    echo "Query: " . $data['debug_info']['query_executed'] . "\n";
                    echo "</pre></details>";
                }
            } else {
                echo "<p style='color: red;'>❌ API Error: " . ($data['error'] ?? 'Unknown error') . "</p>";
            }
        } else {
            echo "<p style='color: orange;'>⚠️ Invalid API response</p>";
            echo "<pre style='background: #f5f5f5; padding: 10px;'>$apiResponse</pre>";
        }
    }
    
} catch (Exception $e) {
    echo "<p style='color: red;'>❌ Error: " . $e->getMessage() . "</p>";
}
?>

<style>
body { font-family: Arial, sans-serif; margin: 20px; }
table { border-collapse: collapse; width: 100%; }
th, td { padding: 8px 12px; text-align: left; border: 1px solid #ddd; }
th { background-color: #f2f2f2; font-weight: bold; }
code { background: #f5f5f5; padding: 2px 4px; border-radius: 3px; font-family: monospace; }
</style>
