<?php
// Test the get_current_stock.php API endpoint
echo "<h2>Testing get_current_stock.php API Endpoint</h2>\n";

// Simulate a logged-in user session
session_start();
$_SESSION['user_id'] = 1; // Assuming admin user ID is 1
$_SESSION['user_type'] = 'Admin';

// Capture the output from get_current_stock.php
ob_start();
include 'get_current_stock.php';
$output = ob_get_clean();

echo "<h3>Raw API Response:</h3>\n";
echo "<pre>" . htmlspecialchars($output) . "</pre>\n";

// Try to decode the JSON
$response = json_decode($output, true);
if ($response) {
    echo "<h3>Decoded Response:</h3>\n";
    echo "<pre>" . print_r($response, true) . "</pre>\n";
    
    if (isset($response['ingredients'])) {
        echo "<h3>Ingredients from API:</h3>\n";
        echo "<table border='1' style='border-collapse: collapse;'>\n";
        echo "<tr><th>ID</th><th>Name</th><th>Quantity</th><th>Unit</th><th>Status</th></tr>\n";
        
        foreach ($response['ingredients'] as $ingredient) {
            echo "<tr>";
            echo "<td>{$ingredient['ingredient_id']}</td>";
            echo "<td>{$ingredient['ingredient_name']}</td>";
            echo "<td>{$ingredient['ingredient_quantity']}</td>";
            echo "<td>{$ingredient['ingredient_unit']}</td>";
            echo "<td>{$ingredient['ingredient_status']}</td>";
            echo "</tr>\n";
        }
        echo "</table>\n";
    }
} else {
    echo "<h3>Failed to decode JSON response</h3>\n";
    echo "Raw output: " . htmlspecialchars($output) . "\n";
}
?>
