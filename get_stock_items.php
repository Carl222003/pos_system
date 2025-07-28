<?php
require_once 'db_connect.php';
require_once 'auth_function.php';

// Check if user is logged in and is a stockman
if (!isset($_SESSION['user_logged_in']) || $_SESSION['user_logged_in'] !== true || $_SESSION['user_type'] !== 'Stockman') {
    header('Content-Type: application/json');
    echo json_encode(['error' => 'Unauthorized access']);
    exit();
}

header('Content-Type: application/json');

try {
    $user_id = $_SESSION['user_id'];
    $branch_id = $_SESSION['branch_id'] ?? null;

    // If branch_id is not in session, try to fetch from user record
    if (!$branch_id) {
        $stmt = $pdo->prepare('SELECT branch_id FROM pos_user WHERE user_id = ?');
        $stmt->execute([$user_id]);
        $branch_id = $stmt->fetchColumn();
    }

    if (!$branch_id) {
        echo json_encode(['error' => 'Branch not found for this user']);
        exit();
    }

    // Get all ingredients for this stockman's branch
    $stmt = $pdo->prepare("
        SELECT 
            i.ingredient_id as id,
            i.ingredient_name as item_name,
            i.ingredient_quantity as current_stock,
            i.ingredient_unit,
            CASE 
                WHEN i.ingredient_quantity = 0 THEN 'Out of Stock'
                WHEN i.ingredient_quantity <= 5 THEN 'Low Stock'
                ELSE 'Adequate'
            END as status,
            i.ingredient_status,
            i.category_id,
            c.category_name
        FROM ingredients i
        LEFT JOIN pos_category c ON i.category_id = c.category_id
        WHERE i.ingredient_status = 'Available' 
        AND i.branch_id = ?
        ORDER BY c.category_name ASC, i.ingredient_name ASC
    ");
    $stmt->execute([$branch_id]);
    $items = $stmt->fetchAll(PDO::FETCH_ASSOC);

    // Format data for display
    foreach ($items as &$item) {
        // Add unit to item name for better display
        $item['item_name'] = $item['item_name'] . ' (' . $item['ingredient_unit'] . ')';
        
        // Add category info
        $item['category_name'] = $item['category_name'] ?: 'Uncategorized';
    }

    echo json_encode([
        'success' => true,
        'items' => $items
    ]);

} catch (PDOException $e) {
    error_log('Error in get_stock_items.php: ' . $e->getMessage());
    echo json_encode([
        'success' => false,
        'error' => 'Database error occurred'
    ]);
} 