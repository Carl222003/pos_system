<?php
echo "<h2>🧪 Testing get_all_ingredients.php</h2>";

// Test the file directly
$url = 'http://localhost/morebites_pos/pos_system/get_all_ingredients.php';

echo "<p>Testing URL: {$url}</p>";

try {
    $response = file_get_contents($url);
    
    if ($response === false) {
        echo "<p style='color: red;'>❌ Failed to get response</p>";
    } else {
        echo "<p style='color: green;'>✅ Got response</p>";
        echo "<h3>Raw Response:</h3>";
        echo "<pre>" . htmlspecialchars($response) . "</pre>";
        
        // Try to decode JSON
        $data = json_decode($response, true);
        if ($data === null) {
            echo "<p style='color: red;'>❌ Invalid JSON response</p>";
        } else {
            echo "<h3>Decoded JSON:</h3>";
            echo "<pre>" . print_r($data, true) . "</pre>";
            
            if (isset($data['success']) && $data['success']) {
                echo "<p style='color: green;'>✅ Success field is true</p>";
                if (isset($data['data'])) {
                    echo "<p style='color: green;'>✅ Data field exists with " . count($data['data']) . " ingredients</p>";
                } else {
                    echo "<p style='color: red;'>❌ Data field missing</p>";
                }
            } else {
                echo "<p style='color: red;'>❌ Success field is false or missing</p>";
            }
        }
    }
    
} catch (Exception $e) {
    echo "<p style='color: red;'>❌ Error: " . $e->getMessage() . "</p>";
}

echo "<hr>";
echo "<p><a href='ingredient_requests.php'>← Back to Ingredient Requests</a></p>";
?>
