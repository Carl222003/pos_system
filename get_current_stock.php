<?php
// Suppress any output that might interfere with JSON response
error_reporting(0);
ini_set('display_errors', 0);

// Set proper headers for JSON response
header('Content-Type: application/json');

// Add CORS headers if needed
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, POST, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type');

require_once 'db_connect.php';
require_once 'auth_function.php';

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    http_response_code(401);
    echo json_encode([
        'success' => false,
        'message' => 'User not authenticated'
    ]);
    exit();
}

requireLogin();

try {
    // Fetch current stock quantities from main ingredients table
    // Modified to include all ingredients for admin modal (not just those with stock > 0)
    $stmt = $pdo->prepare("
        SELECT 
            i.ingredient_id, 
            i.ingredient_name, 
            i.ingredient_unit, 
            i.ingredient_quantity, 
            i.ingredient_status, 
            i.category_id, 
            c.category_name
        FROM ingredients i
        LEFT JOIN pos_category c ON i.category_id = c.category_id
        WHERE c.status = 'active' 
        AND i.ingredient_status = 'Available'
        AND (i.consume_before IS NULL OR i.consume_before > CURDATE())
        ORDER BY c.category_name, i.ingredient_name
    ");
    
    $stmt->execute();
    $ingredients = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    // Debug: Log the query and results
    error_log("get_current_stock.php - Query executed, found " . count($ingredients) . " ingredients");
    if (count($ingredients) > 0) {
        error_log("Sample ingredient: " . json_encode($ingredients[0]));
    }
    
    echo json_encode([
        'success' => true,
        'ingredients' => $ingredients,
        'timestamp' => date('Y-m-d H:i:s'),
        'count' => count($ingredients),
        'debug' => [
            'user_id' => $_SESSION['user_id'] ?? 'not_set',
            'user_type' => $_SESSION['user_type'] ?? 'not_set',
            'query_condition' => 'ingredient_quantity > 0',
            'sample_ingredients' => array_slice($ingredients, 0, 3), // Show first 3 ingredients for debugging
            'all_ingredients' => $ingredients // Show all ingredients for debugging
        ]
    ]);
    
} catch (PDOException $e) {
    error_log("Database error in get_current_stock.php: " . $e->getMessage());
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'message' => 'Database error: ' . $e->getMessage()
    ]);
} catch (Exception $e) {
    error_log("General error in get_current_stock.php: " . $e->getMessage());
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'message' => 'An error occurred: ' . $e->getMessage()
    ]);
}
?>
