<?php
require_once 'db_connect.php';
require_once 'auth_function.php';

checkAdminLogin();

header('Content-Type: application/json');

try {
    $branch_id = isset($_GET['branch_id']) ? intval($_GET['branch_id']) : null;
    $period = isset($_GET['period']) ? $_GET['period'] : 'today';
    
    // Define date ranges based on period
    switch ($period) {
        case 'today':
            $date_condition = "DATE(o.order_datetime) = CURDATE()";
            break;
        case 'week':
            $date_condition = "o.order_datetime >= DATE_SUB(CURDATE(), INTERVAL 7 DAY)";
            break;
        case 'month':
            $date_condition = "o.order_datetime >= DATE_SUB(CURDATE(), INTERVAL 30 DAY)";
            break;
        default:
            $date_condition = "DATE(o.order_datetime) = CURDATE()";
    }
    
    if ($branch_id) {
        // Get sales data for specific branch
        $query = "SELECT 
                    COUNT(DISTINCT o.order_id) as total_orders,
                    COALESCE(SUM(o.order_total), 0) as total_sales,
                    COALESCE(AVG(o.order_total), 0) as avg_order_value,
                    COALESCE(MIN(o.order_total), 0) as min_sale,
                    COALESCE(MAX(o.order_total), 0) as max_sale,
                    COUNT(DISTINCT o.cashier_id) as active_cashiers_count
                  FROM pos_order o
                  WHERE $date_condition";
        
        // Note: Add branch_id condition when order table has branch_id column
        // AND o.branch_id = ?
        
        $stmt = $pdo->prepare($query);
        $stmt->execute();
        $sales_data = $stmt->fetch(PDO::FETCH_ASSOC);
        
        // Get hourly sales for today (for real-time chart)
        if ($period === 'today') {
            $hourly_query = "SELECT 
                               HOUR(o.order_datetime) as hour,
                               COUNT(o.order_id) as orders,
                               COALESCE(SUM(o.order_total), 0) as sales
                             FROM pos_order o
                             WHERE DATE(o.order_datetime) = CURDATE()
                             GROUP BY HOUR(o.order_datetime)
                             ORDER BY hour";
            
            $hourly_stmt = $pdo->query($hourly_query);
            $hourly_data = $hourly_stmt->fetchAll(PDO::FETCH_ASSOC);
            
            // Fill in missing hours with zero values
            $complete_hourly = [];
            for ($h = 0; $h < 24; $h++) {
                $found = false;
                foreach ($hourly_data as $data) {
                    if ($data['hour'] == $h) {
                        $complete_hourly[] = [
                            'hour' => $h,
                            'orders' => intval($data['orders']),
                            'sales' => floatval($data['sales'])
                        ];
                        $found = true;
                        break;
                    }
                }
                if (!$found) {
                    $complete_hourly[] = [
                        'hour' => $h,
                        'orders' => 0,
                        'sales' => 0
                    ];
                }
            }
            
            $sales_data['hourly_data'] = $complete_hourly;
        }
        
        echo json_encode([
            'success' => true,
            'branch_id' => $branch_id,
            'period' => $period,
            'data' => $sales_data,
            'timestamp' => date('Y-m-d H:i:s')
        ]);
        
    } else {
        // Get sales data for all branches
        $query = "SELECT 
                    COUNT(DISTINCT o.order_id) as total_orders,
                    COALESCE(SUM(o.order_total), 0) as total_sales,
                    COALESCE(AVG(o.order_total), 0) as avg_order_value
                  FROM pos_order o
                  WHERE $date_condition";
        
        $stmt = $pdo->query($query);
        $overall_data = $stmt->fetch(PDO::FETCH_ASSOC);
        
        echo json_encode([
            'success' => true,
            'period' => $period,
            'overall_data' => $overall_data,
            'timestamp' => date('Y-m-d H:i:s')
        ]);
    }
    
} catch (Exception $e) {
    echo json_encode([
        'success' => false,
        'error' => $e->getMessage()
    ]);
}
?>
