<?php
require_once 'db_connect.php';
require_once 'auth_function.php';

checkAdminLogin();

header('Content-Type: application/json');

try {
    $branch_id = $_GET['branch_id'] ?? null;
    $period = $_GET['period'] ?? 'daily';
    
    if (!$branch_id) {
        throw new Exception('Branch ID is required');
    }

    $today = date('Y-m-d');

    // Get today's statistics
    $stmt = $pdo->prepare("
        SELECT 
            COUNT(*) as total_orders,
            COALESCE(SUM(total_amount), 0) as total_sales,
            COALESCE(AVG(total_amount), 0) as average_sale,
            COALESCE(MAX(total_amount), 0) as highest_sale
        FROM pos_orders 
        WHERE branch_id = ? 
        AND DATE(order_date) = ?
        AND order_status != 'cancelled'
    ");
    $stmt->execute([$branch_id, $today]);
    $today_stats = $stmt->fetch(PDO::FETCH_ASSOC);

    // Get sales trend data based on period
    $sales_trend = ['labels' => [], 'data' => []];
    
    switch ($period) {
        case 'daily':
            // Last 7 days
            for ($i = 6; $i >= 0; $i--) {
                $date = date('Y-m-d', strtotime("-$i days"));
                $day_name = date('D', strtotime($date));
                
                $stmt = $pdo->prepare("
                    SELECT COALESCE(SUM(total_amount), 0) as daily_sales
                    FROM pos_orders 
                    WHERE branch_id = ? 
                    AND DATE(order_date) = ?
                    AND order_status != 'cancelled'
                ");
                $stmt->execute([$branch_id, $date]);
                $daily_sales = $stmt->fetchColumn();
                
                $sales_trend['labels'][] = $day_name;
                $sales_trend['data'][] = (float)$daily_sales;
            }
            break;
            
        case 'weekly':
            // Last 4 weeks
            for ($i = 3; $i >= 0; $i--) {
                $week_start = date('Y-m-d', strtotime("-$i weeks"));
                $week_end = date('Y-m-d', strtotime("-$i weeks +6 days"));
                $week_label = 'Week ' . (4 - $i);
                
                $stmt = $pdo->prepare("
                    SELECT COALESCE(SUM(total_amount), 0) as weekly_sales
                    FROM pos_orders 
                    WHERE branch_id = ? 
                    AND DATE(order_date) BETWEEN ? AND ?
                    AND order_status != 'cancelled'
                ");
                $stmt->execute([$branch_id, $week_start, $week_end]);
                $weekly_sales = $stmt->fetchColumn();
                
                $sales_trend['labels'][] = $week_label;
                $sales_trend['data'][] = (float)$weekly_sales;
            }
            break;
            
        case 'monthly':
            // Last 6 months
            for ($i = 5; $i >= 0; $i--) {
                $month_start = date('Y-m-01', strtotime("-$i months"));
                $month_end = date('Y-m-t', strtotime("-$i months"));
                $month_label = date('M Y', strtotime("-$i months"));
                
                $stmt = $pdo->prepare("
                    SELECT COALESCE(SUM(total_amount), 0) as monthly_sales
                    FROM pos_orders 
                    WHERE branch_id = ? 
                    AND DATE(order_date) BETWEEN ? AND ?
                    AND order_status != 'cancelled'
                ");
                $stmt->execute([$branch_id, $month_start, $month_end]);
                $monthly_sales = $stmt->fetchColumn();
                
                $sales_trend['labels'][] = $month_label;
                $sales_trend['data'][] = (float)$monthly_sales;
            }
            break;
    }

    // Get payment methods breakdown (simplified - assuming all orders are cash for now)
    $stmt = $pdo->prepare("
        SELECT 
            COUNT(*) as total_orders
        FROM pos_orders 
        WHERE branch_id = ? 
        AND DATE(order_date) = ?
        AND order_status != 'cancelled'
    ");
    $stmt->execute([$branch_id, $today]);
    $total_orders = $stmt->fetchColumn();

    // For now, assume all payments are cash (you can modify this based on your payment_method field)
    $payment_methods = [
        'cash' => $total_orders,
        'credit_card' => 0,
        'e_wallet' => 0
    ];

    $response = [
        'today_stats' => [
            'total_orders' => (int)$today_stats['total_orders'],
            'total_sales' => (float)$today_stats['total_sales'],
            'average_sale' => (float)$today_stats['average_sale'],
            'highest_sale' => (float)$today_stats['highest_sale']
        ],
        'sales_trend' => $sales_trend,
        'payment_methods' => $payment_methods
    ];

    echo json_encode($response);

} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(['error' => $e->getMessage()]);
}
?> 