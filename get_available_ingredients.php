<?php
require_once 'db_connect.php';
require_once 'auth_function.php';

// Check if user is logged in and is a stockman
if (!isset($_SESSION['user_logged_in']) || $_SESSION['user_logged_in'] !== true || $_SESSION['user_type'] !== 'Stockman') {
    http_response_code(403);
    echo json_encode(['success' => false, 'error' => 'Unauthorized access']);
    exit();
}

header('Content-Type: application/json');

try {
    $branch_id = $_SESSION['branch_id'];
    
    if (!$branch_id) {
        echo json_encode(['success' => false, 'error' => 'Branch ID not found in session']);
        exit();
    }
    
    // Get branch information
    $branch_stmt = $pdo->prepare("SELECT branch_name FROM pos_branch WHERE branch_id = ?");
    $branch_stmt->execute([$branch_id]);
    $branch = $branch_stmt->fetch(PDO::FETCH_ASSOC);
    $branch_name = $branch['branch_name'] ?? 'Unknown Branch';
    
    // Check if ingredients table has branch_id column
    $columns_check = $pdo->query("SHOW COLUMNS FROM ingredients LIKE 'branch_id'");
    $has_branch_id = $columns_check->rowCount() > 0;
    
    if ($has_branch_id) {
        // Get only ingredients specifically assigned to this branch
        $query = "SELECT 
                    i.ingredient_id,
                    i.ingredient_name,
                    i.ingredient_quantity,
                    i.ingredient_unit,
                    i.ingredient_status,
                    i.minimum_stock,
                    i.consume_before,
                    i.date_added,
                    i.branch_id,
                    c.category_name,
                    c.category_id,
                    b.branch_name
                  FROM ingredients i
                  LEFT JOIN pos_category c ON i.category_id = c.category_id
                  LEFT JOIN pos_branch b ON i.branch_id = b.branch_id
                  WHERE i.branch_id = ? 
                  AND i.ingredient_status != 'archived'
                  AND i.ingredient_status != 'unassigned'
                  ORDER BY 
                    CASE 
                        WHEN i.ingredient_quantity <= 0 THEN 1
                        WHEN i.ingredient_quantity <= i.minimum_stock THEN 2
                        ELSE 3
                    END,
                    i.ingredient_name";
        
        $stmt = $pdo->prepare($query);
        $stmt->execute([$branch_id]);
    } else {
        // Fallback: get all ingredients (no branch filtering)
        $query = "SELECT 
                    i.ingredient_id,
                    i.ingredient_name,
                    i.ingredient_quantity,
                    i.ingredient_unit,
                    i.ingredient_status,
                    i.minimum_stock,
                    i.consume_before,
                    i.date_added,
                    c.category_name,
                    c.category_id
                  FROM ingredients i
                  LEFT JOIN pos_category c ON i.category_id = c.category_id
                  WHERE i.ingredient_status != 'archived'
                  ORDER BY 
                    CASE 
                        WHEN i.ingredient_quantity <= 0 THEN 1
                        WHEN i.ingredient_quantity <= i.minimum_stock THEN 2
                        ELSE 3
                    END,
                    i.ingredient_name";
        
        $stmt = $pdo->query($query);
    }
    
    $ingredients = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    // Calculate statistics
    $stats = [
        'total' => 0,
        'available' => 0,
        'low_stock' => 0,
        'out_of_stock' => 0,
        'expiring' => 0,
        'branch_name' => $branch_name
    ];
    
    $today = time();
    $week_from_now = $today + (7 * 24 * 60 * 60);
    
    foreach ($ingredients as &$ingredient) {
        $stats['total']++;
        
        $quantity = floatval($ingredient['ingredient_quantity']);
        $min_stock = floatval($ingredient['minimum_stock']);
        
        // Determine availability status based on quantity (like the second image)
        if ($quantity <= 0) {
            $ingredient['ingredient_status'] = 'Out of Stock';
            $ingredient['availability_status'] = 'unavailable';
            $ingredient['status_display'] = 'OUT OF STOCK';
            $stats['out_of_stock']++;
            $ingredient['stock_level'] = 'out_of_stock';
        } elseif ($quantity <= $min_stock) {
            $ingredient['ingredient_status'] = 'Low Stock';
            $ingredient['availability_status'] = 'low_stock';
            $ingredient['status_display'] = 'LOW STOCK';
            $stats['low_stock']++;
            $ingredient['stock_level'] = 'low_stock';
        } else {
            $ingredient['ingredient_status'] = 'Available';
            $ingredient['availability_status'] = 'available';
            $ingredient['status_display'] = 'ADEQUATE';
            $stats['available']++;
            $ingredient['stock_level'] = 'adequate';
        }
        
        // Check expiry
        if ($ingredient['consume_before']) {
            $expire_time = strtotime($ingredient['consume_before']);
            if ($expire_time && $expire_time <= $week_from_now) {
                $stats['expiring']++;
                $ingredient['is_expiring'] = true;
                $ingredient['days_until_expiry'] = ceil(($expire_time - $today) / (24 * 60 * 60));
                
                // Override status if expiring soon
                if ($ingredient['days_until_expiry'] <= 3) {
                    $ingredient['ingredient_status'] = 'Expiring Soon';
                    $ingredient['availability_status'] = 'expiring';
                    $ingredient['status_display'] = 'EXPIRING SOON';
                }
            } else {
                $ingredient['is_expiring'] = false;
            }
        } else {
            $ingredient['is_expiring'] = false;
        }
        
        // Format dates
        $ingredient['date_added_formatted'] = date('M d, Y', strtotime($ingredient['date_added']));
        if ($ingredient['consume_before']) {
            $ingredient['consume_before_formatted'] = date('M d, Y', strtotime($ingredient['consume_before']));
        }
        
        // Add status color and icon (like the second image with ADEQUATE badges)
        switch ($ingredient['availability_status']) {
            case 'available':
                $ingredient['status_color'] = '#28a745';
                $ingredient['status_icon'] = 'fa-check-circle';
                $ingredient['status_bg'] = '#d4edda';
                $ingredient['status_text_color'] = '#155724';
                break;
            case 'low_stock':
                $ingredient['status_color'] = '#ffc107';
                $ingredient['status_icon'] = 'fa-exclamation-triangle';
                $ingredient['status_bg'] = '#fff3cd';
                $ingredient['status_text_color'] = '#856404';
                break;
            case 'unavailable':
                $ingredient['status_color'] = '#dc3545';
                $ingredient['status_icon'] = 'fa-times-circle';
                $ingredient['status_bg'] = '#f8d7da';
                $ingredient['status_text_color'] = '#721c24';
                break;
            case 'expiring':
                $ingredient['status_color'] = '#fd7e14';
                $ingredient['status_icon'] = 'fa-calendar-times';
                $ingredient['status_bg'] = '#ffeaa7';
                $ingredient['status_text_color'] = '#856404';
                break;
            default:
                $ingredient['status_color'] = '#6c757d';
                $ingredient['status_icon'] = 'fa-question-circle';
                $ingredient['status_bg'] = '#f8f9fa';
                $ingredient['status_text_color'] = '#495057';
        }
    }
    
    echo json_encode([
        'success' => true,
        'ingredients' => $ingredients,
        'stats' => $stats,
        'branch_id' => $branch_id,
        'branch_name' => $branch_name,
        'has_branch_filtering' => $has_branch_id,
        'timestamp' => date('Y-m-d H:i:s'),
        'message' => $has_branch_id ? 
            "Showing branch-specific ingredients for $branch_name" : 
            "Showing all ingredients (branch filtering not available)"
    ]);
    
} catch (Exception $e) {
    echo json_encode([
        'success' => false,
        'error' => $e->getMessage()
    ]);
}
?>
