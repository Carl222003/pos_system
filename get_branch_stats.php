<?php
require_once 'db_connect.php';
require_once 'auth_function.php';

header('Content-Type: application/json');

if (!isset($_GET['branch_id'])) {
    echo json_encode(['error' => 'Branch ID is required']);
    exit;
}

$branch_id = intval($_GET['branch_id']);
$today = date('Y-m-d');
$current_time = date('H:i:s');
$expiry_threshold = date('Y-m-d', strtotime('+30 days')); // Items expiring within 30 days

try {
    // First, check if the branch is currently operating
    $stmt = $pdo->prepare("
        SELECT 
            operating_hours,
            status
        FROM pos_branch 
        WHERE branch_id = ?
    ");
    $stmt->execute([$branch_id]);
    $branch_info = $stmt->fetch(PDO::FETCH_ASSOC);

    // Validate branch_info and operating_hours
    if (!$branch_info || empty($branch_info['operating_hours']) || strpos($branch_info['operating_hours'], ' - ') === false) {
        echo json_encode(['error' => 'Branch info or operating hours not set for this branch.']);
        exit;
    }

    // Parse operating hours
    $hours = explode(' - ', $branch_info['operating_hours']);
    $opening_time = date('H:i:s', strtotime($hours[0]));
    $closing_time = date('H:i:s', strtotime($hours[1]));

    // Get all cashiers assigned to this branch
    $stmt = $pdo->prepare("
        SELECT user_id, username, CONCAT(first_name, ' ', last_name) AS full_name
        FROM pos_user
        WHERE user_type = 'Cashier' AND branch_id = ?
    ");
    $stmt->execute([$branch_id]);
    $all_cashiers = $stmt->fetchAll(PDO::FETCH_ASSOC);

    // Get active cashier sessions for this branch today
    $stmt = $pdo->prepare("
        SELECT cs.user_id
        FROM pos_cashier_sessions cs
        WHERE cs.branch_id = ?
        AND cs.is_active = TRUE
        AND cs.logout_time IS NULL
        AND DATE(cs.login_time) = CURRENT_DATE()
    ");
    $stmt->execute([$branch_id]);
    $active_cashier_ids = $stmt->fetchAll(PDO::FETCH_COLUMN);

    // Mark which cashiers are active
    foreach ($all_cashiers as &$cashier) {
        $cashier['is_active'] = in_array($cashier['user_id'], $active_cashier_ids);
    }
    unset($cashier);

    // Check if branch is currently operating
    $is_operating = false;
    $active_cashiers = count($active_cashier_ids);
    if ($branch_info['status'] === 'Active' && $active_cashiers > 0) {
        if ($opening_time <= $closing_time) {
            $is_operating = ($current_time >= $opening_time && $current_time <= $closing_time);
        } else {
            // Handle cases where closing time is after midnight
            $is_operating = ($current_time >= $opening_time || $current_time <= $closing_time);
        }
    }

    // Get today's sales and orders (only if branch is operating)
    if ($is_operating) {
        $stmt = $pdo->prepare("
            SELECT 
                COUNT(DISTINCT order_id) as total_orders,
                COALESCE(SUM(order_total), 0) as total_sales
            FROM pos_order
            WHERE branch_id = ? 
            AND DATE(order_datetime) = ?
        ");
        $stmt->execute([$branch_id, $today]);
        $sales_data = $stmt->fetch(PDO::FETCH_ASSOC);
    } else {
        $sales_data = [
            'total_orders' => 0,
            'total_sales' => 0
        ];
    }

    // Get low stock count
    $stmt = $pdo->prepare("
        SELECT COUNT(*) 
        FROM pos_branch_inventory bi
        JOIN pos_inventory i ON bi.inventory_id = i.inventory_id
        WHERE bi.branch_id = ?
        AND bi.current_stock <= i.minimum_stock
    ");
    $stmt->execute([$branch_id]);
    $low_stock_count = $stmt->fetchColumn();

    // Get expiring items count
    $stmt = $pdo->prepare("
        SELECT COUNT(*)
        FROM pos_branch_inventory bi
        WHERE bi.branch_id = ?
        AND bi.expiry_date <= ?
        AND bi.current_stock > 0
    ");
    $stmt->execute([$branch_id, $expiry_threshold]);
    $expiring_count = $stmt->fetchColumn();

    echo json_encode([
        'today_sales' => floatval($sales_data['total_sales']),
        'today_orders' => intval($sales_data['total_orders']),
        'low_stock_count' => intval($low_stock_count),
        'expiring_count' => intval($expiring_count),
        'is_operating' => $is_operating,
        'active_cashiers' => $active_cashiers,
        'all_cashiers' => $all_cashiers
    ]);

} catch (PDOException $e) {
    echo json_encode(['error' => 'Database error: ' . $e->getMessage()]);
}
?> 