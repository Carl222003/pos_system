<?php
// Test the API endpoint directly
session_start();
$_SESSION['user_id'] = 1;
$_SESSION['user_type'] = 'Admin';

echo "<h1>Direct API Test</h1>\n";

// Capture the output from get_current_stock.php
ob_start();
include 'get_current_stock.php';
$apiOutput = ob_get_clean();

echo "<h2>Raw API Output:</h2>\n";
echo "<pre>" . htmlspecialchars($apiOutput) . "</pre>\n";

// Try to decode the JSON
$apiResponse = json_decode($apiOutput, true);
if ($apiResponse) {
    echo "<h2>Decoded Response:</h2>\n";
    echo "<pre>" . print_r($apiResponse, true) . "</pre>\n";
    
    if (isset($apiResponse['ingredients'])) {
        echo "<h2>Ingredients Found:</h2>\n";
        foreach ($apiResponse['ingredients'] as $ingredient) {
            if (strpos($ingredient['ingredient_name'], 'Coke') !== false) {
                echo "<strong style='color: red;'>COKE MISMO: {$ingredient['ingredient_quantity']} {$ingredient['ingredient_unit']}</strong><br>\n";
            } else {
                echo "- {$ingredient['ingredient_name']}: {$ingredient['ingredient_quantity']} {$ingredient['ingredient_unit']}<br>\n";
            }
        }
    }
} else {
    echo "<h2>Failed to decode JSON</h2>\n";
    echo "JSON Error: " . json_last_error_msg() . "\n";
}
?>
