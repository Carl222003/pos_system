<?php
require_once 'db_connect.php';
require_once 'auth_function.php';

checkAdminLogin();

header('Content-Type: application/json');

try {
    $branch_id = $_GET['branch_id'] ?? null;
    
    if (!$branch_id) {
        throw new Exception('Branch ID is required');
    }

    // Get today's sales and orders
    $today = date('Y-m-d');
    $stmt = $pdo->prepare("
        SELECT 
            COUNT(*) as today_orders,
            COALESCE(SUM(total_amount), 0) as today_sales
        FROM pos_orders 
        WHERE branch_id = ? 
        AND DATE(order_date) = ?
        AND order_status != 'cancelled'
    ");
    $stmt->execute([$branch_id, $today]);
    $sales_data = $stmt->fetch(PDO::FETCH_ASSOC);

    // Get all cashiers assigned to this branch
    $stmt = $pdo->prepare("
        SELECT 
            u.user_id,
            CONCAT(u.first_name, ' ', u.last_name) as full_name,
            u.user_status,
            u.last_activity,
            CASE 
                WHEN u.last_activity > DATE_SUB(NOW(), INTERVAL 15 MINUTE) THEN 1 
                ELSE 0 
            END as is_active
        FROM pos_user u
        WHERE u.branch_id = ? 
        AND u.user_type = 'Cashier'
        AND u.user_status = 'Active'
        ORDER BY u.first_name, u.last_name
    ");
    $stmt->execute([$branch_id]);
    $all_cashiers = $stmt->fetchAll(PDO::FETCH_ASSOC);

    // Filter only active (online) cashiers
    $active_cashiers = array_filter($all_cashiers, function($cashier) {
        return $cashier['is_active'] == 1;
    });

    // Check if branch is operating (has active cashiers)
    $is_operating = count($active_cashiers) > 0;

    // Get low stock count (including expired items)
    $stmt = $pdo->prepare("
        SELECT COUNT(*) as low_stock_count
        FROM ingredients i
        WHERE i.branch_id = ? 
        AND (
            (i.ingredient_quantity <= i.minimum_stock AND i.ingredient_quantity > 0)
            OR (i.consume_before IS NOT NULL AND i.consume_before <= CURDATE())
        )
    ");
    $stmt->execute([$branch_id]);
    $low_stock = $stmt->fetch(PDO::FETCH_ASSOC);

    // Note: expiring count removed since ingredients table doesn't have expiry_date column

    $response = [
        'today_orders' => (int)$sales_data['today_orders'],
        'today_sales' => (float)$sales_data['today_sales'],
        'active_cashiers' => array_values($active_cashiers), // Only active cashiers
        'total_active_cashiers' => count($active_cashiers),
        'total_assigned_cashiers' => count($all_cashiers),
        'is_operating' => $is_operating,
        'low_stock_count' => (int)$low_stock['low_stock_count']
    ];

    echo json_encode($response);

} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(['error' => $e->getMessage()]);
}
?> 