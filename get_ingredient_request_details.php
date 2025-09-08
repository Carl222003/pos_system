<?php
require_once 'db_connect.php';
require_once 'auth_function.php';

header('Content-Type: application/json');

// Check if user is logged in and is a stockman
requireLogin();
if ($_SESSION['user_type'] !== 'Stockman') {
    echo json_encode(['success' => false, 'error' => 'Access denied. Only Stockman can access this data.']);
    exit();
}

if (!isset($_GET['request_id']) || !is_numeric($_GET['request_id'])) {
    echo json_encode(['success' => false, 'error' => 'Invalid request ID.']);
    exit();
}

try {
    $user_id = $_SESSION['user_id'];
    $branch_id = $_SESSION['branch_id'] ?? null;
    $request_id = intval($_GET['request_id']);
    
    // If branch_id is not in session, try to fetch from user record
    if (!$branch_id) {
        $stmt = $pdo->prepare('SELECT branch_id FROM pos_user WHERE user_id = ?');
        $stmt->execute([$user_id]);
        $branch_id = $stmt->fetchColumn();
    }
    
    // Get request details
    $stmt = $pdo->prepare("
        SELECT 
            r.request_id,
            r.branch_id,
            r.ingredients,
            r.approved_ingredients,
            r.status,
            r.delivery_status,
            r.delivery_notes,
            r.notes,
            r.request_date,
            r.updated_by,
            r.updated_date,
            b.branch_name,
            u.user_name as updated_by_name
        FROM ingredient_requests r 
        LEFT JOIN pos_branch b ON r.branch_id = b.branch_id 
        LEFT JOIN pos_user u ON r.updated_by = u.user_id
        WHERE r.request_id = ? AND r.branch_id = ?
    ");
    
    $stmt->execute([$request_id, $branch_id]);
    $request = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if (!$request) {
        echo json_encode(['success' => false, 'error' => 'Request not found or access denied.']);
        exit();
    }
    
    // Parse ingredients JSON and get detailed ingredient information
    $ingredients_list = [];
    
    // Check if this is an approved request with approved_ingredients
    if ($request['status'] === 'approved' && !empty($request['approved_ingredients'])) {
        // Use approved ingredients for approved requests
        $ingredients_json = json_decode($request['approved_ingredients'], true);
    } else {
        // Use original ingredients for pending/rejected requests
        $ingredients_json = json_decode($request['ingredients'], true);
    }
    
    if ($ingredients_json && is_array($ingredients_json)) {
        foreach ($ingredients_json as $ingredient) {
            if (isset($ingredient['ingredient_id']) && isset($ingredient['quantity'])) {
                // Get detailed ingredient information
                $stmt_ingredient = $pdo->prepare("
                    SELECT 
                        i.ingredient_id,
                        i.ingredient_name,
                        i.ingredient_unit,
                        i.ingredient_quantity as current_stock,
                        i.ingredient_status,
                        c.category_name
                    FROM ingredients i
                    LEFT JOIN pos_category c ON i.category_id = c.category_id
                    WHERE i.ingredient_id = ?
                ");
                $stmt_ingredient->execute([$ingredient['ingredient_id']]);
                $ingredient_info = $stmt_ingredient->fetch(PDO::FETCH_ASSOC);
                
                if ($ingredient_info) {
                    $ingredients_list[] = [
                        'ingredient_id' => $ingredient_info['ingredient_id'],
                        'name' => $ingredient_info['ingredient_name'],
                        'quantity' => $ingredient['quantity'],
                        'unit' => $ingredient_info['ingredient_unit'],
                        'category' => $ingredient_info['category_name'] ?: 'General',
                        'current_stock' => $ingredient_info['current_stock'],
                        'current_unit' => $ingredient_info['ingredient_unit'],
                        'status' => $ingredient_info['ingredient_status']
                    ];
                } else {
                    // Fallback for missing ingredient
                    $ingredients_list[] = [
                        'ingredient_id' => $ingredient['ingredient_id'],
                        'name' => 'Unknown Ingredient (ID: ' . $ingredient['ingredient_id'] . ')',
                        'quantity' => $ingredient['quantity'],
                        'unit' => 'pieces',
                        'category' => 'Unknown',
                        'current_stock' => 0,
                        'current_unit' => 'pieces',
                        'status' => 'unknown'
                    ];
                }
            } else if (isset($ingredient['ingredient_name']) && isset($ingredient['quantity'])) {
                // Handle ingredients without ID (from selective approval)
                $unit = isset($ingredient['unit']) ? $ingredient['unit'] : 'pieces';
                $ingredients_list[] = [
                    'ingredient_id' => null,
                    'name' => $ingredient['ingredient_name'],
                    'quantity' => $ingredient['quantity'],
                    'unit' => $unit,
                    'category' => 'General',
                    'current_stock' => 0,
                    'current_unit' => $unit,
                    'status' => 'active'
                ];
            }
        }
    }
    
    // Format dates
    $request['request_date'] = date('Y-m-d H:i:s', strtotime($request['request_date']));
    if ($request['updated_date']) {
        $request['updated_date'] = date('Y-m-d H:i:s', strtotime($request['updated_date']));
    }
    
    // Add formatted display values
    $request['formatted_request_date'] = date('M j, Y g:i A', strtotime($request['request_date']));
    if ($request['updated_date']) {
        $request['formatted_updated_date'] = date('M j, Y g:i A', strtotime($request['updated_date']));
    }
    
    // Add ingredients list to request
    $request['ingredients_list'] = $ingredients_list;
    
    echo json_encode([
        'success' => true,
        'request' => $request
    ]);
    
} catch (PDOException $e) {
    error_log("Database error in get_ingredient_request_details.php: " . $e->getMessage());
    echo json_encode(['success' => false, 'error' => 'Database error occurred. Please try again.']);
} catch (Exception $e) {
    error_log("Error in get_ingredient_request_details.php: " . $e->getMessage());
    echo json_encode(['success' => false, 'error' => 'An error occurred. Please try again.']);
}
?>
