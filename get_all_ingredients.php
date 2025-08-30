<?php
require_once 'db_connect.php';

// Set JSON header
header('Content-Type: application/json');

try {
    // Check if ingredients table exists
    $tableExists = $pdo->query("SHOW TABLES LIKE 'ingredients'")->rowCount();
    
    if ($tableExists == 0) {
        echo json_encode([
            "success" => false,
            "error" => "Ingredients table does not exist",
            "data" => []
        ]);
        exit;
    }
    
    // Check if table has data
    $countQuery = "SELECT COUNT(*) FROM ingredients";
    $countStmt = $pdo->query($countQuery);
    $totalCount = $countStmt->fetchColumn();
    
    if ($totalCount == 0) {
        echo json_encode([
            "success" => true,
            "data" => [],
            "total_count" => 0,
            "message" => "No ingredients found in database"
        ]);
        exit;
    }
    
    // Fetch all ingredients with all required fields
    $dataQuery = "SELECT 
                    i.ingredient_id,
                    COALESCE(c.category_name, 'Uncategorized') as category_name,
                    i.ingredient_name,
                    i.ingredient_quantity,
                    i.ingredient_unit,
                    i.consume_before,
                    CASE 
                        WHEN i.consume_before IS NOT NULL AND i.consume_before <= CURDATE() THEN 'Out of Stock'
                        ELSE i.ingredient_status
                    END as ingredient_status,
                    i.minimum_stock
                  FROM ingredients i 
                  LEFT JOIN pos_category c ON i.category_id = c.category_id
                  WHERE i.ingredient_status != 'archived'
                  ORDER BY i.ingredient_name ASC";
    
    $dataStmt = $pdo->prepare($dataQuery);
    $dataStmt->execute();
    $data = $dataStmt->fetchAll(PDO::FETCH_ASSOC);
    
    // Return JSON response
    $response = [
        "success" => true,
        "data" => $data,
        "total_count" => count($data)
    ];
    
    echo json_encode($response);
    
} catch (PDOException $e) {
    error_log("Database error in get_all_ingredients.php: " . $e->getMessage());
    
    echo json_encode([
        "success" => false,
        "error" => "Database error: " . $e->getMessage(),
        "data" => []
    ]);
    
} catch (Exception $e) {
    error_log("General error in get_all_ingredients.php: " . $e->getMessage());
    
    echo json_encode([
        "success" => false,
        "error" => "An error occurred while fetching ingredients",
        "data" => []
    ]);
}
?> 