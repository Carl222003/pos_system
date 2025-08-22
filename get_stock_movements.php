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

    // Get stock movements for this branch
    $stmt = $pdo->prepare("
        SELECT 
            sm.movement_id,
            sm.ingredient_id,
            i.ingredient_name,
            sm.movement_type,
            sm.quantity,
            sm.previous_stock,
            sm.new_stock,
            sm.reason,
            sm.created_at,
            u.user_name as adjusted_by
        FROM pos_stock_movement sm
        JOIN ingredients i ON sm.ingredient_id = i.ingredient_id
        LEFT JOIN pos_user u ON sm.user_id = u.user_id
        WHERE i.branch_id = ?
        ORDER BY sm.created_at DESC
        LIMIT 50
    ");
    $stmt->execute([$branch_id]);
    $movements = $stmt->fetchAll(PDO::FETCH_ASSOC);

    // Format data for display
    foreach ($movements as &$movement) {
        $movement['formatted_date'] = date('M d, Y H:i', strtotime($movement['created_at']));
        $movement['movement_type_text'] = ucfirst($movement['movement_type']);
        $movement['quantity_formatted'] = $movement['quantity'] . ' ' . ($movement['quantity'] == 1 ? 'unit' : 'units');
    }

    echo json_encode([
        'success' => true,
        'movements' => $movements
    ]);

} catch (PDOException $e) {
    error_log('Error in get_stock_movements.php: ' . $e->getMessage());
    echo json_encode([
        'success' => false,
        'error' => 'Database error occurred'
    ]);
}
?> 