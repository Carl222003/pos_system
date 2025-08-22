<?php
require_once 'db_connect.php';
require_once 'auth_function.php';

// Check if user is logged in
if (!isset($_SESSION['user_logged_in']) || $_SESSION['user_logged_in'] !== true) {
    header('Content-Type: application/json');
    echo json_encode(['success' => false, 'error' => 'Unauthorized access']);
    exit();
}

header('Content-Type: application/json');

try {
    // First, check what columns exist in the pos_category table
    $stmt = $pdo->query("DESCRIBE pos_category");
    $columns = $stmt->fetchAll(PDO::FETCH_COLUMN);
    
    // Determine the correct column names based on what exists
    $statusColumn = 'status';
    if (in_array('category_status', $columns)) {
        $statusColumn = 'category_status';
    }
    
    // Determine active status value
    $activeValues = ['Active', 'active'];
    
    // Build the query dynamically
    $selectColumns = "category_id, category_name";
    if (in_array('created_at', $columns)) {
        $selectColumns .= ", created_at";
    }
    
    // Try different combinations to find active categories
    $categories = [];
    foreach ($activeValues as $activeValue) {
        try {
            $query = "
                SELECT $selectColumns
                FROM pos_category 
                WHERE $statusColumn = :status
                ORDER BY category_name ASC
            ";
            
            $stmt = $pdo->prepare($query);
            $stmt->execute([':status' => $activeValue]);
            $categories = $stmt->fetchAll(PDO::FETCH_ASSOC);
            
            if (!empty($categories)) {
                break; // Found categories, stop trying
            }
        } catch (PDOException $e) {
            // Continue to next attempt
            continue;
        }
    }
    
    // If no categories found, try without status filter
    if (empty($categories)) {
        try {
            $query = "
                SELECT $selectColumns
                FROM pos_category 
                ORDER BY category_name ASC
            ";
            
            $stmt = $pdo->query($query);
            $categories = $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            // If still no categories, create some default ones
            $categories = [];
        }
    }
    
    // If still no categories exist, create some default ones
    if (empty($categories)) {
        try {
            $defaultCategories = [
                ['name' => 'Beverages', 'status' => 'Active'],
                ['name' => 'Main Course', 'status' => 'Active'],
                ['name' => 'Desserts', 'status' => 'Active'],
                ['name' => 'Appetizers', 'status' => 'Active'],
                ['name' => 'Side Dishes', 'status' => 'Active']
            ];
            
            // Insert default categories
            foreach ($defaultCategories as $category) {
                try {
                    if ($statusColumn === 'category_status') {
                        $insertQuery = "INSERT INTO pos_category (category_name, category_status) VALUES (?, ?)";
                    } else {
                        $insertQuery = "INSERT INTO pos_category (category_name, status) VALUES (?, ?)";
                    }
                    
                    $stmt = $pdo->prepare($insertQuery);
                    $stmt->execute([$category['name'], $category['status']]);
                } catch (PDOException $e) {
                    // Category might already exist, continue
                    continue;
                }
            }
            
            // Re-fetch categories after insertion
            $query = "
                SELECT $selectColumns
                FROM pos_category 
                ORDER BY category_name ASC
            ";
            
            $stmt = $pdo->query($query);
            $categories = $stmt->fetchAll(PDO::FETCH_ASSOC);
            
        } catch (PDOException $e) {
            // If insertion fails, provide fallback categories
            $categories = [
                ['category_id' => 1, 'category_name' => 'Beverages'],
                ['category_id' => 2, 'category_name' => 'Main Course'],
                ['category_id' => 3, 'category_name' => 'Desserts'],
                ['category_id' => 4, 'category_name' => 'Appetizers'],
                ['category_id' => 5, 'category_name' => 'Side Dishes']
            ];
        }
    }

    echo json_encode([
        'success' => true,
        'categories' => $categories,
        'total_count' => count($categories)
    ]);

} catch (PDOException $e) {
    error_log("Database error in get_categories.php: " . $e->getMessage());
    
    // Provide fallback categories even on database error
    $fallbackCategories = [
        ['category_id' => 1, 'category_name' => 'Beverages'],
        ['category_id' => 2, 'category_name' => 'Main Course'],
        ['category_id' => 3, 'category_name' => 'Desserts'],
        ['category_id' => 4, 'category_name' => 'Appetizers'],
        ['category_id' => 5, 'category_name' => 'Side Dishes']
    ];
    
    echo json_encode([
        'success' => true,
        'categories' => $fallbackCategories,
        'total_count' => count($fallbackCategories)
    ]);
} catch (Exception $e) {
    error_log("General error in get_categories.php: " . $e->getMessage());
    echo json_encode([
        'success' => false,
        'error' => 'An error occurred while fetching categories',
        'categories' => []
    ]);
}
?>
