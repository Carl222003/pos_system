<?php
require_once 'db_connect.php';
session_start();

echo "<h1>🧪 Test API Response</h1>";

// Simulate being logged in as a stockman for testing
if (!isset($_SESSION['user_logged_in'])) {
    // Get first stockman user
    $user = $pdo->query("SELECT user_id, user_name, branch_id FROM pos_user WHERE user_type = 'Stockman' LIMIT 1")->fetch(PDO::FETCH_ASSOC);
    
    if ($user) {
        $_SESSION['user_logged_in'] = true;
        $_SESSION['user_type'] = 'Stockman';
        $_SESSION['user_id'] = $user['user_id'];
        $_SESSION['user_name'] = $user['user_name'];
        $_SESSION['branch_id'] = $user['branch_id'];
        
        echo "<p style='color: green;'>✅ Simulated login as: {$user['user_name']} (Branch ID: {$user['branch_id']})</p>";
    } else {
        echo "<p style='color: red;'>❌ No stockman users found!</p>";
        exit;
    }
} else {
    echo "<p>✅ Already logged in as: " . ($_SESSION['user_name'] ?? 'Unknown') . "</p>";
}

echo "<h3>📋 Current Session:</h3>";
echo "<ul>";
echo "<li><strong>User ID:</strong> " . ($_SESSION['user_id'] ?? 'Not set') . "</li>";
echo "<li><strong>User Type:</strong> " . ($_SESSION['user_type'] ?? 'Not set') . "</li>";
echo "<li><strong>Branch ID:</strong> " . ($_SESSION['branch_id'] ?? 'Not set') . "</li>";
echo "</ul>";

echo "<h3>🔗 Testing API Call:</h3>";

// Make the API call
$ch = curl_init();
curl_setopt($ch, CURLOPT_URL, "http://" . $_SERVER['HTTP_HOST'] . dirname($_SERVER['REQUEST_URI']) . "/get_stockman_products.php");
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_COOKIE, session_name() . "=" . session_id());
curl_setopt($ch, CURLOPT_TIMEOUT, 10);
$response = curl_exec($ch);
$httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
curl_close($ch);

echo "<p><strong>HTTP Code:</strong> $httpCode</p>";

if ($response === false) {
    echo "<p style='color: red;'>❌ cURL failed!</p>";
} else {
    echo "<h4>Raw Response:</h4>";
    echo "<textarea style='width: 100%; height: 200px;'>$response</textarea>";
    
    $data = json_decode($response, true);
    if ($data) {
        echo "<h4>Parsed JSON:</h4>";
        echo "<pre style='background: #f5f5f5; padding: 10px; border-radius: 5px; overflow-x: auto;'>";
        echo json_encode($data, JSON_PRETTY_PRINT);
        echo "</pre>";
        
        // Check specific fields
        echo "<h4>Key Information:</h4>";
        echo "<ul>";
        echo "<li><strong>Success:</strong> " . ($data['success'] ? 'true' : 'false') . "</li>";
        echo "<li><strong>Products Count:</strong> " . ($data['total_count'] ?? 0) . "</li>";
        
        if (isset($data['branch_info'])) {
            echo "<li><strong>Branch ID:</strong> " . ($data['branch_info']['branch_id'] ?? 'Not set') . "</li>";
            echo "<li><strong>Branch Name:</strong> " . ($data['branch_info']['branch_name'] ?? 'Not set') . "</li>";
        } else {
            echo "<li style='color: red;'><strong>Branch Info:</strong> Missing!</li>";
        }
        
        if (isset($data['debug_info'])) {
            echo "<li><strong>Using Fallback:</strong> " . ($data['debug_info']['using_fallback'] ? 'true' : 'false') . "</li>";
            echo "<li><strong>Table Used:</strong> " . ($data['debug_info']['branch_product_table_used'] ?? 'None') . "</li>";
        }
        
        if (isset($data['error'])) {
            echo "<li style='color: red;'><strong>Error:</strong> " . $data['error'] . "</li>";
        }
        echo "</ul>";
        
    } else {
        echo "<p style='color: red;'>❌ Invalid JSON response!</p>";
        echo "<p><strong>JSON Error:</strong> " . json_last_error_msg() . "</p>";
    }
}

echo "<h3>🔧 Quick Fixes:</h3>";
echo "<div style='display: flex; gap: 10px; margin: 10px 0;'>";
echo "<a href='debug_no_products.php' style='background: #007bff; color: white; padding: 8px 15px; text-decoration: none; border-radius: 4px;'>Debug Products</a>";
echo "<a href='assign_products_to_branches.php' style='background: #28a745; color: white; padding: 8px 15px; text-decoration: none; border-radius: 4px;'>Assign Products</a>";
echo "<a href='stockman_products.php' style='background: #6c757d; color: white; padding: 8px 15px; text-decoration: none; border-radius: 4px;'>Go to Products Page</a>";
echo "</div>";
?>

<style>
body { font-family: Arial, sans-serif; margin: 20px; }
pre { white-space: pre-wrap; word-wrap: break-word; }
textarea { font-family: monospace; }
</style>
