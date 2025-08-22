<?php
session_start();

// Simulate stockman session for testing
$_SESSION['user_logged_in'] = true;
$_SESSION['user_type'] = 'Stockman';
$_SESSION['user_id'] = 1;

require_once 'db_connect.php';

echo "<h2>Status Toggle Test</h2>";

// Test the API endpoints directly
if (isset($_POST['test_action'])) {
    echo "<h3>Test Results:</h3>";
    
    if ($_POST['test_action'] === 'update_status') {
        // Test status update
        $product_id = intval($_POST['product_id']);
        $new_status = $_POST['new_status'];
        
        echo "<h4>1. Testing Status Update:</h4>";
        echo "<p>Product ID: $product_id, New Status: $new_status</p>";
        
        // Simulate the AJAX call
        $_POST['product_id'] = $product_id;
        $_POST['status'] = $new_status;
        
        ob_start();
        include 'update_product_status.php';
        $response = ob_get_clean();
        
        echo "<p><strong>API Response:</strong></p>";
        echo "<pre>" . htmlspecialchars($response) . "</pre>";
        
        $data = json_decode($response, true);
        if ($data && $data['success']) {
            echo "<p style='color: green;'>✅ Status update successful</p>";
        } else {
            echo "<p style='color: red;'>❌ Status update failed</p>";
        }
    }
    
    if ($_POST['test_action'] === 'fetch_products') {
        // Test product fetching
        $filter_status = $_POST['filter_status'] ?? '';
        
        echo "<h4>2. Testing Product Fetch:</h4>";
        echo "<p>Filter Status: " . ($filter_status ?: 'All') . "</p>";
        
        // Simulate the AJAX call
        $_GET['status'] = $filter_status;
        $_GET['search'] = '';
        $_GET['category'] = '';
        
        ob_start();
        include 'get_stockman_products.php';
        $response = ob_get_clean();
        
        echo "<p><strong>API Response:</strong></p>";
        $data = json_decode($response, true);
        
        if ($data && $data['success']) {
            echo "<p style='color: green;'>✅ Found {$data['total_count']} products</p>";
            
            if (!empty($data['products'])) {
                echo "<table border='1' style='border-collapse: collapse;'>";
                echo "<tr><th>ID</th><th>Name</th><th>Status</th></tr>";
                foreach ($data['products'] as $product) {
                    $statusColor = $product['product_status'] === 'Available' ? 'green' : 'red';
                    echo "<tr>";
                    echo "<td>{$product['product_id']}</td>";
                    echo "<td>{$product['product_name']}</td>";
                    echo "<td style='color: $statusColor; font-weight: bold;'>{$product['product_status']}</td>";
                    echo "</tr>";
                }
                echo "</table>";
            }
            
            if (isset($data['debug_info'])) {
                echo "<p><strong>Debug Info:</strong></p>";
                echo "<pre>" . htmlspecialchars(json_encode($data['debug_info'], JSON_PRETTY_PRINT)) . "</pre>";
            }
        } else {
            echo "<p style='color: red;'>❌ Product fetch failed</p>";
            echo "<pre>" . htmlspecialchars($response) . "</pre>";
        }
    }
}

// Get current products for testing
try {
    $stmt = $pdo->query("SELECT product_id, product_name, product_status FROM pos_product ORDER BY product_id LIMIT 5");
    $products = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    echo "<h3>Test Actions:</h3>";
    
    if (empty($products)) {
        echo "<p>No products found for testing.</p>";
    } else {
        echo "<h4>1. Test Status Update:</h4>";
        foreach ($products as $product) {
            $newStatus = $product['product_status'] === 'Available' ? 'Unavailable' : 'Available';
            $statusColor = $product['product_status'] === 'Available' ? 'green' : 'red';
            
            echo "<div style='margin: 10px 0; padding: 10px; border: 1px solid #ccc;'>";
            echo "<strong>{$product['product_name']}</strong> ";
            echo "<span style='color: $statusColor;'>({$product['product_status']})</span>";
            echo "<form method='post' style='display: inline; margin-left: 10px;'>";
            echo "<input type='hidden' name='test_action' value='update_status'>";
            echo "<input type='hidden' name='product_id' value='{$product['product_id']}'>";
            echo "<input type='hidden' name='new_status' value='$newStatus'>";
            echo "<button type='submit'>Make $newStatus</button>";
            echo "</form>";
            echo "</div>";
        }
        
        echo "<h4>2. Test Product Filtering:</h4>";
        $filters = [
            '' => 'All Status',
            'Available' => 'Available Only',
            'Unavailable' => 'Unavailable Only'
        ];
        
        foreach ($filters as $filterValue => $filterLabel) {
            echo "<form method='post' style='display: inline; margin: 5px;'>";
            echo "<input type='hidden' name='test_action' value='fetch_products'>";
            echo "<input type='hidden' name='filter_status' value='$filterValue'>";
            echo "<button type='submit'>Test: $filterLabel</button>";
            echo "</form>";
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
pre { background: #f5f5f5; padding: 10px; border-radius: 4px; overflow-x: auto; }
</style>
