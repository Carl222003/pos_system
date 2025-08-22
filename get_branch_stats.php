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

try {
    // First, check if the branch exists and get basic info
    $stmt = $pdo->prepare("
        SELECT 
            branch_id,
            branch_name,
            branch_code,
            status
        FROM pos_branch 
        WHERE branch_id = ?
    ");
    $stmt->execute([$branch_id]);
    $branch_info = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$branch_info) {
        echo json_encode(['error' => 'Branch not found']);
        exit;
    }

    // Get all cashiers assigned to this branch
    $stmt = $pdo->prepare("
        SELECT 
            user_id, 
            user_name as full_name,
            user_status
        FROM pos_user
        WHERE user_type = 'Cashier' 
        AND branch_id = ? 
        AND user_status = 'Active'
    ");
    $stmt->execute([$branch_id]);
    $all_cashiers = $stmt->fetchAll(PDO::FETCH_ASSOC);

    // For now, assume all active cashiers are "active" (since we don't have session tracking)
    foreach ($all_cashiers as &$cashier) {
        $cashier['is_active'] = ($cashier['user_status'] === 'Active');
    }
    unset($cashier);

    // Check if branch is currently operating (simplified logic)
    $is_operating = ($branch_info['status'] === 'Active' && count($all_cashiers) > 0);
    $active_cashiers = count($all_cashiers);

    // Get today's sales and orders (with table existence check)
    $sales_data = ['total_orders' => 0, 'total_sales' => 0];
    $stmt = $pdo->query("SHOW TABLES LIKE 'pos_order'");
    if ($stmt->rowCount() > 0) {
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
    }

    // Get low stock count (with table existence check)
    $low_stock_count = 0;
    $stmt = $pdo->query("SHOW TABLES LIKE 'pos_branch_product'");
    if ($stmt->rowCount() > 0) {
        $stmt = $pdo->prepare("
            SELECT COUNT(*) 
            FROM pos_branch_product bp
            JOIN pos_product p ON bp.product_id = p.product_id
            WHERE bp.branch_id = ?
            AND bp.stock_quantity <= 10
            AND bp.stock_quantity > 0
        ");
        $stmt->execute([$branch_id]);
        $low_stock_count = $stmt->fetchColumn();
    }

    // Get expiring items count (with table existence check)
    $expiring_count = 0;
    $stmt = $pdo->query("SHOW TABLES LIKE 'pos_branch_ingredient'");
    if ($stmt->rowCount() > 0) {
        $stmt = $pdo->prepare("
            SELECT COUNT(*)
            FROM pos_branch_ingredient bi
            WHERE bi.branch_id = ?
            AND bi.expiry_date <= DATE_ADD(CURDATE(), INTERVAL 30 DAY)
            AND bi.expiry_date >= CURDATE()
            AND bi.quantity > 0
        ");
        $stmt->execute([$branch_id]);
        $expiring_count = $stmt->fetchColumn();
    }

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