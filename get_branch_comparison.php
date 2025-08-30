<?php
require_once 'db_connect.php';
require_once 'auth_function.php';

checkAdminLogin();

header('Content-Type: application/json');

try {
    $period = $_GET['period'] ?? 'daily';
    $start_date = $_GET['start_date'] ?? null;
    $end_date = $_GET['end_date'] ?? null;

    // Set date range based on period
    switch ($period) {
        case 'daily':
            $start_date = date('Y-m-d');
            $end_date = date('Y-m-d');
            break;
        case 'weekly':
            $start_date = date('Y-m-d', strtotime('monday this week'));
            $end_date = date('Y-m-d', strtotime('sunday this week'));
            break;
        case 'monthly':
            $start_date = date('Y-m-01');
            $end_date = date('Y-m-t');
            break;
        case 'yearly':
            $start_date = date('Y-01-01');
            $end_date = date('Y-12-31');
            break;
        case 'custom':
            if (!$start_date || !$end_date) {
                throw new Exception('Start date and end date are required for custom range');
            }
            break;
    }

    // Get all active branches
    $stmt = $pdo->query("SELECT branch_id, branch_name FROM pos_branch WHERE status = 'Active' ORDER BY branch_name");
    $branches = $stmt->fetchAll(PDO::FETCH_ASSOC);

    $comparison_data = [];

    foreach ($branches as $branch) {
        $branch_id = $branch['branch_id'];

        // Get orders and sales for this branch in the date range
        $stmt = $pdo->prepare("
            SELECT 
                COUNT(*) as total_orders,
                COALESCE(SUM(total_amount), 0) as total_sales,
                COALESCE(AVG(total_amount), 0) as average_sale
            FROM pos_orders 
            WHERE branch_id = ? 
            AND DATE(order_date) BETWEEN ? AND ?
            AND order_status != 'cancelled'
        ");
        $stmt->execute([$branch_id, $start_date, $end_date]);
        $sales_data = $stmt->fetch(PDO::FETCH_ASSOC);

        // Get top products for this branch
        $stmt = $pdo->prepare("
            SELECT 
                p.product_name,
                COUNT(*) as order_count
            FROM pos_order_items oi
            JOIN pos_orders o ON oi.order_id = o.order_id
            JOIN pos_product p ON oi.product_id = p.product_id
            WHERE o.branch_id = ? 
            AND DATE(o.order_date) BETWEEN ? AND ?
            AND o.order_status != 'cancelled'
            GROUP BY p.product_id, p.product_name
            ORDER BY order_count DESC
            LIMIT 3
        ");
        $stmt->execute([$branch_id, $start_date, $end_date]);
        $top_products = $stmt->fetchAll(PDO::FETCH_ASSOC);

        // Get branch status (operating or closed) - only truly active cashiers
        $stmt = $pdo->prepare("
            SELECT COUNT(*) as active_cashiers
            FROM pos_user 
            WHERE branch_id = ? 
            AND user_type = 'Cashier' 
            AND user_status = 'Active'
            AND last_activity > DATE_SUB(NOW(), INTERVAL 15 MINUTE)
        ");
        $stmt->execute([$branch_id]);
        $active_cashiers = $stmt->fetchColumn();

        $status = $active_cashiers > 0 ? 'Operating' : 'Closed';

        $comparison_data[] = [
            'branch_name' => $branch['branch_name'],
            'status' => $status,
            'total_orders' => (int)$sales_data['total_orders'],
            'total_sales' => (float)$sales_data['total_sales'],
            'average_sale' => (float)$sales_data['average_sale'],
            'top_products' => array_map(function($product) {
                return $product['product_name'] . ' (' . $product['order_count'] . ')';
            }, $top_products)
        ];
    }

    // Sort by total sales (descending)
    usort($comparison_data, function($a, $b) {
        return $b['total_sales'] <=> $a['total_sales'];
    });

    // Add rank
    foreach ($comparison_data as $index => &$branch) {
        $branch['rank'] = $index + 1;
    }

    echo json_encode([
        'success' => true,
        'data' => $comparison_data,
        'period' => $period,
        'start_date' => $start_date,
        'end_date' => $end_date
    ]);

} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(['error' => $e->getMessage()]);
}
?> 