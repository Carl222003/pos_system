<?php
require_once 'db_connect.php';
require_once 'auth_function.php';

// Check if user is logged in and is a stockman
if (!isset($_SESSION['user_logged_in']) || $_SESSION['user_logged_in'] !== true || $_SESSION['user_type'] !== 'Stockman') {
    http_response_code(401);
    echo json_encode(['error' => 'Unauthorized access']);
    exit();
}

try {
    $stockman_id = $_SESSION['user_id'];
    
    // Get basic statistics
    $stats = [];
    
    // Total items
    $stmt = $pdo->prepare("SELECT COUNT(*) as total FROM ingredients WHERE branch_id = (SELECT branch_id FROM pos_user WHERE user_id = ?)");
    $stmt->execute([$stockman_id]);
    $stats['total_items'] = $stmt->fetch()['total'];
    
    // Low stock items (less than minimum stock)
    $stmt = $pdo->prepare("
        SELECT COUNT(*) as low_stock 
        FROM ingredients 
        WHERE branch_id = (SELECT branch_id FROM pos_user WHERE user_id = ?) 
        AND current_stock <= minimum_stock
    ");
    $stmt->execute([$stockman_id]);
    $stats['low_stock_items'] = $stmt->fetch()['low_stock'];
    
    // Out of stock items
    $stmt = $pdo->prepare("
        SELECT COUNT(*) as out_of_stock 
        FROM ingredients 
        WHERE branch_id = (SELECT branch_id FROM pos_user WHERE user_id = ?) 
        AND current_stock = 0
    ");
    $stmt->execute([$stockman_id]);
    $stats['out_of_stock'] = $stmt->fetch()['out_of_stock'];
    
    // Adequate stock items
    $stmt = $pdo->prepare("
        SELECT COUNT(*) as adequate_stock 
        FROM ingredients 
        WHERE branch_id = (SELECT branch_id FROM pos_user WHERE user_id = ?) 
        AND current_stock > minimum_stock
    ");
    $stmt->execute([$stockman_id]);
    $stats['adequate_stock'] = $stmt->fetch()['adequate_stock'];
    
    // Stock movements (from activity log)
    $stmt = $pdo->prepare("
        SELECT COUNT(*) as movements 
        FROM activity_log 
        WHERE user_id = ? 
        AND (action LIKE '%stock%' OR action LIKE '%inventory%' OR action LIKE '%adjust%')
        AND DATE(timestamp) >= DATE_SUB(CURDATE(), INTERVAL 7 DAY)
    ");
    $stmt->execute([$stockman_id]);
    $stats['stock_movements'] = $stmt->fetch()['movements'];
    
    // Expiring items (if expiry_date column exists)
    try {
        $stmt = $pdo->prepare("
            SELECT COUNT(*) as expiring 
            FROM ingredients 
            WHERE branch_id = (SELECT branch_id FROM pos_user WHERE user_id = ?) 
            AND expiry_date IS NOT NULL 
            AND expiry_date <= DATE_ADD(CURDATE(), INTERVAL 30 DAY)
        ");
        $stmt->execute([$stockman_id]);
        $stats['expiring_items'] = $stmt->fetch()['expiring'];
    } catch (PDOException $e) {
        // If expiry_date column doesn't exist, set to 0
        $stats['expiring_items'] = 0;
    }
    
    // Stock turnover calculation
    $stmt = $pdo->prepare("
        SELECT 
            COUNT(DISTINCT i.ingredient_id) as total_items,
            COUNT(CASE WHEN al.log_id IS NOT NULL THEN 1 END) as moved_items
        FROM ingredients i
        LEFT JOIN activity_log al ON al.action LIKE CONCAT('%', i.ingredient_name, '%')
        AND al.timestamp >= DATE_SUB(CURDATE(), INTERVAL 30 DAY)
        WHERE i.branch_id = (SELECT branch_id FROM pos_user WHERE user_id = ?)
    ");
    $stmt->execute([$stockman_id]);
    $turnover_data = $stmt->fetch();
    
    $total_items = $turnover_data['total_items'];
    $moved_items = $turnover_data['moved_items'];
    $stats['stock_turnover'] = $total_items > 0 ? round(($moved_items / $total_items) * 100, 1) : 0;
    
    // Stock value calculation
    $stmt = $pdo->prepare("
        SELECT SUM(current_stock * unit_price) as total_value
        FROM ingredients 
        WHERE branch_id = (SELECT branch_id FROM pos_user WHERE user_id = ?)
        AND unit_price IS NOT NULL
    ");
    $stmt->execute([$stockman_id]);
    $value_data = $stmt->fetch();
    $stats['total_stock_value'] = '₱' . number_format($value_data['total_value'] ?? 0, 2);
    
    // Calculate trends (comparing current week vs previous week)
    $current_week_movements = $stats['stock_movements'];
    
    $stmt = $pdo->prepare("
        SELECT COUNT(*) as prev_movements 
        FROM activity_log 
        WHERE user_id = ? 
        AND (action LIKE '%stock%' OR action LIKE '%inventory%' OR action LIKE '%adjust%')
        AND DATE(timestamp) BETWEEN DATE_SUB(CURDATE(), INTERVAL 14 DAY) AND DATE_SUB(CURDATE(), INTERVAL 7 DAY)
    ");
    $stmt->execute([$stockman_id]);
    $prev_week_movements = $stmt->fetch()['prev_movements'];
    
    // Calculate percentage change
    if ($prev_week_movements > 0) {
        $movement_change = round((($current_week_movements - $prev_week_movements) / $prev_week_movements) * 100);
        $stats['movements_trend'] = ($movement_change >= 0 ? '+' : '') . $movement_change . '% this week';
    } else {
        $stats['movements_trend'] = 'New this week';
    }
    
    // Get current week vs previous week for total items
    $stmt = $pdo->prepare("
        SELECT COUNT(*) as current_items 
        FROM ingredients 
        WHERE branch_id = (SELECT branch_id FROM pos_user WHERE user_id = ?) 
        AND DATE(created_at) >= DATE_SUB(CURDATE(), INTERVAL 7 DAY)
    ");
    $stmt->execute([$stockman_id]);
    $current_week_items = $stmt->fetch()['current_items'];
    
    $stmt = $pdo->prepare("
        SELECT COUNT(*) as prev_items 
        FROM ingredients 
        WHERE branch_id = (SELECT branch_id FROM pos_user WHERE user_id = ?) 
        AND DATE(created_at) BETWEEN DATE_SUB(CURDATE(), INTERVAL 14 DAY) AND DATE_SUB(CURDATE(), INTERVAL 7 DAY)
    ");
    $stmt->execute([$stockman_id]);
    $prev_week_items = $stmt->fetch()['prev_items'];
    
    if ($prev_week_items > 0) {
        $item_change = round((($current_week_items - $prev_week_items) / $prev_week_items) * 100);
        $stats['total_items_trend'] = ($item_change >= 0 ? '+' : '') . $item_change . '% this week';
    } else {
        $stats['total_items_trend'] = 'No change';
    }
    
    // Low stock trend
    $stmt = $pdo->prepare("
        SELECT COUNT(*) as current_low 
        FROM ingredients 
        WHERE branch_id = (SELECT branch_id FROM pos_user WHERE user_id = ?) 
        AND current_stock <= minimum_stock
        AND DATE(updated_at) >= DATE_SUB(CURDATE(), INTERVAL 7 DAY)
    ");
    $stmt->execute([$stockman_id]);
    $current_low = $stmt->fetch()['current_low'];
    
    $stmt = $pdo->prepare("
        SELECT COUNT(*) as prev_low 
        FROM ingredients 
        WHERE branch_id = (SELECT branch_id FROM pos_user WHERE user_id = ?) 
        AND current_stock <= minimum_stock
        AND DATE(updated_at) BETWEEN DATE_SUB(CURDATE(), INTERVAL 14 DAY) AND DATE_SUB(CURDATE(), INTERVAL 7 DAY)
    ");
    $stmt->execute([$stockman_id]);
    $prev_low = $stmt->fetch()['prev_low'];
    
    $low_stock_change = $current_low - $prev_low;
    $stats['low_stock_trend'] = ($low_stock_change >= 0 ? '+' : '') . $low_stock_change . ' this week';
    
    // Turnover trend
    $stmt = $pdo->prepare("
        SELECT 
            COUNT(CASE WHEN al.log_id IS NOT NULL THEN 1 END) as prev_moved_items
        FROM ingredients i
        LEFT JOIN activity_log al ON al.action LIKE CONCAT('%', i.ingredient_name, '%')
        AND al.timestamp BETWEEN DATE_SUB(CURDATE(), INTERVAL 60 DAY) AND DATE_SUB(CURDATE(), INTERVAL 30 DAY)
        WHERE i.branch_id = (SELECT branch_id FROM pos_user WHERE user_id = ?)
    ");
    $stmt->execute([$stockman_id]);
    $prev_turnover_data = $stmt->fetch();
    
    $prev_moved_items = $prev_turnover_data['prev_moved_items'];
    if ($prev_moved_items > 0) {
        $turnover_change = round((($moved_items - $prev_moved_items) / $prev_moved_items) * 100);
        $stats['turnover_trend'] = ($turnover_change >= 0 ? '+' : '') . $turnover_change . '% this month';
    } else {
        $stats['turnover_trend'] = 'New this month';
    }
    
    // Value trend
    $stmt = $pdo->prepare("
        SELECT SUM(current_stock * unit_price) as prev_value
        FROM ingredients 
        WHERE branch_id = (SELECT branch_id FROM pos_user WHERE user_id = ?)
        AND unit_price IS NOT NULL
        AND DATE(updated_at) < DATE_SUB(CURDATE(), INTERVAL 30 DAY)
    ");
    $stmt->execute([$stockman_id]);
    $prev_value_data = $stmt->fetch();
    $prev_value = $prev_value_data['prev_value'] ?? 0;
    $current_value = $value_data['total_value'] ?? 0;
    
    if ($prev_value > 0) {
        $value_change = round((($current_value - $prev_value) / $prev_value) * 100);
        $stats['value_trend'] = ($value_change >= 0 ? '+' : '') . $value_change . '% this month';
    } else {
        $stats['value_trend'] = 'New this month';
    }
    
    // Expiring trend
    $stats['expiring_trend'] = 'No change';
    
    // Weekly movements data for chart
    $weekly_movements = [];
    for ($i = 6; $i >= 0; $i--) {
        $date = date('Y-m-d', strtotime("-$i days"));
        $day_name = date('D', strtotime($date));
        
        $stmt = $pdo->prepare("
            SELECT COUNT(*) as movements 
            FROM activity_log 
            WHERE user_id = ? 
            AND (action LIKE '%stock%' OR action LIKE '%inventory%' OR action LIKE '%adjust%')
            AND DATE(timestamp) = ?
        ");
        $stmt->execute([$stockman_id, $date]);
        $movements = $stmt->fetch()['movements'];
        
        $weekly_movements[] = $movements;
    }
    $stats['weekly_movements'] = $weekly_movements;
    
    // Category performance data
    $stmt = $pdo->prepare("
        SELECT 
            pc.category_name,
            COUNT(i.ingredient_id) as item_count
        FROM ingredients i
        JOIN pos_category pc ON i.category_id = pc.category_id
        WHERE i.branch_id = (SELECT branch_id FROM pos_user WHERE user_id = ?)
        GROUP BY pc.category_id, pc.category_name
        ORDER BY item_count DESC
        LIMIT 5
    ");
    $stmt->execute([$stockman_id]);
    $category_data = $stmt->fetchAll();
    
    $stats['category_labels'] = array_column($category_data, 'category_name');
    $stats['category_data'] = array_column($category_data, 'item_count');
    
    // Category cards data for the new section
    $stmt = $pdo->prepare("
        SELECT 
            pc.category_id,
            pc.category_name,
            COUNT(i.ingredient_id) as total_items,
            COUNT(CASE WHEN i.current_stock > i.minimum_stock THEN 1 END) as available,
            COUNT(CASE WHEN i.current_stock <= i.minimum_stock AND i.current_stock > 0 THEN 1 END) as low_stock,
            COUNT(CASE WHEN i.current_stock = 0 THEN 1 END) as out_of_stock
        FROM pos_category pc
        LEFT JOIN ingredients i ON pc.category_id = i.category_id 
        AND i.branch_id = (SELECT branch_id FROM pos_user WHERE user_id = ?)
        GROUP BY pc.category_id, pc.category_name
        HAVING total_items > 0
        ORDER BY total_items DESC
        LIMIT 10
    ");
    $stmt->execute([$stockman_id]);
    $category_cards_data = $stmt->fetchAll();
    
    // Calculate healthy stock (available + low stock)
    foreach ($category_cards_data as &$category) {
        $category['healthy'] = $category['available'] + $category['low_stock'];
    }
    
    $stats['category_cards'] = $category_cards_data;
    
    // Turnover analysis (high, medium, low)
    $stmt = $pdo->prepare("
        SELECT 
            COUNT(CASE WHEN movement_count >= 10 THEN 1 END) as high_turnover,
            COUNT(CASE WHEN movement_count BETWEEN 3 AND 9 THEN 1 END) as medium_turnover,
            COUNT(CASE WHEN movement_count < 3 THEN 1 END) as low_turnover
        FROM (
            SELECT i.ingredient_id, COUNT(al.log_id) as movement_count
            FROM ingredients i
            LEFT JOIN activity_log al ON al.action LIKE CONCAT('%', i.ingredient_name, '%')
            AND al.timestamp >= DATE_SUB(CURDATE(), INTERVAL 30 DAY)
            WHERE i.branch_id = (SELECT branch_id FROM pos_user WHERE user_id = ?)
            GROUP BY i.ingredient_id
        ) as turnover_analysis
    ");
    $stmt->execute([$stockman_id]);
    $turnover_analysis = $stmt->fetch();
    
    $stats['high_turnover'] = $turnover_analysis['high_turnover'];
    $stats['medium_turnover'] = $turnover_analysis['medium_turnover'];
    $stats['low_turnover'] = $turnover_analysis['low_turnover'];
    
    // Get insights
    // Fastest moving items (highest turnover)
    $stmt = $pdo->prepare("
        SELECT COUNT(*) as fastest_moving
        FROM (
            SELECT i.ingredient_id, COUNT(al.log_id) as movement_count
            FROM ingredients i
            LEFT JOIN activity_log al ON al.action LIKE CONCAT('%', i.ingredient_name, '%')
            WHERE i.branch_id = (SELECT branch_id FROM pos_user WHERE user_id = ?)
            AND al.timestamp >= DATE_SUB(CURDATE(), INTERVAL 30 DAY)
            GROUP BY i.ingredient_id
            ORDER BY movement_count DESC
            LIMIT 5
        ) as top_movers
    ");
    $stmt->execute([$stockman_id]);
    $stats['fastest_moving'] = $stmt->fetch()['fastest_moving'];
    
    // Slowest moving items (lowest turnover)
    $stmt = $pdo->prepare("
        SELECT COUNT(*) as slowest_moving
        FROM (
            SELECT i.ingredient_id, COUNT(al.log_id) as movement_count
            FROM ingredients i
            LEFT JOIN activity_log al ON al.action LIKE CONCAT('%', i.ingredient_name, '%')
            WHERE i.branch_id = (SELECT branch_id FROM pos_user WHERE user_id = ?)
            AND al.timestamp >= DATE_SUB(CURDATE(), INTERVAL 30 DAY)
            GROUP BY i.ingredient_id
            ORDER BY movement_count ASC
            LIMIT 5
        ) as slow_movers
    ");
    $stmt->execute([$stockman_id]);
    $stats['slowest_moving'] = $stmt->fetch()['slowest_moving'];
    
    // Expiring soon
    try {
        $stmt = $pdo->prepare("
            SELECT COUNT(*) as expiring_soon
            FROM ingredients 
            WHERE branch_id = (SELECT branch_id FROM pos_user WHERE user_id = ?) 
            AND expiry_date IS NOT NULL 
            AND expiry_date <= DATE_ADD(CURDATE(), INTERVAL 7 DAY)
        ");
        $stmt->execute([$stockman_id]);
        $stats['expiring_soon'] = $stmt->fetch()['expiring_soon'];
    } catch (PDOException $e) {
        $stats['expiring_soon'] = 0;
    }
    
    // Stock value analytics
    $stats['turnover_rate'] = $stats['stock_turnover'];
    
    // Average stock age
    $stmt = $pdo->prepare("
        SELECT AVG(DATEDIFF(CURDATE(), DATE(created_at))) as avg_age
        FROM ingredients 
        WHERE branch_id = (SELECT branch_id FROM pos_user WHERE user_id = ?)
        AND created_at IS NOT NULL
    ");
    $stmt->execute([$stockman_id]);
    $age_data = $stmt->fetch();
    $stats['average_age'] = round($age_data['avg_age'] ?? 0);
    
    // Reorder recommendations
    $stmt = $pdo->prepare("
        SELECT 
            i.ingredient_name,
            i.current_stock,
            i.minimum_stock,
            i.maximum_stock,
            CASE 
                WHEN i.current_stock = 0 THEN 'danger'
                WHEN i.current_stock <= i.minimum_stock THEN 'warning'
                WHEN i.current_stock <= (i.minimum_stock + i.maximum_stock) / 2 THEN 'info'
                ELSE 'success'
            END as priority,
            CASE 
                WHEN i.current_stock = 0 THEN 'Out of stock - immediate reorder required'
                WHEN i.current_stock <= i.minimum_stock THEN 'Below minimum stock level'
                WHEN i.current_stock <= (i.minimum_stock + i.maximum_stock) / 2 THEN 'Approaching minimum stock'
                ELSE 'Stock level adequate'
            END as reason,
            COALESCE(i.maximum_stock - i.current_stock, i.minimum_stock * 2) as recommended_quantity
        FROM ingredients i
        WHERE i.branch_id = (SELECT branch_id FROM pos_user WHERE user_id = ?)
        AND (i.current_stock = 0 OR i.current_stock <= i.minimum_stock)
        ORDER BY 
            CASE 
                WHEN i.current_stock = 0 THEN 1
                WHEN i.current_stock <= i.minimum_stock THEN 2
                ELSE 3
            END,
            i.ingredient_name
        LIMIT 10
    ");
    $stmt->execute([$stockman_id]);
    $stats['reorder_recommendations'] = $stmt->fetchAll();
    
    // Critical alerts
    $critical_alerts = [];
    
    // Out of stock alert
    if ($stats['out_of_stock'] > 0) {
        $critical_alerts[] = [
            'severity' => 'danger',
            'title' => 'Out of Stock Items',
            'description' => $stats['out_of_stock'] . ' items are completely out of stock. Immediate action required.'
        ];
    }
    
    // Low stock alert
    if ($stats['low_stock_items'] > 5) {
        $critical_alerts[] = [
            'severity' => 'warning',
            'title' => 'Multiple Low Stock Items',
            'description' => $stats['low_stock_items'] . ' items are running low on stock. Consider reordering.'
        ];
    }
    
    // Expiring items alert
    if ($stats['expiring_soon'] > 0) {
        $critical_alerts[] = [
            'severity' => 'info',
            'title' => 'Items Expiring Soon',
            'description' => $stats['expiring_soon'] . ' items will expire within 7 days. Check expiry dates.'
        ];
    }
    
    // No recent activity alert
    if ($stats['stock_movements'] == 0) {
        $critical_alerts[] = [
            'severity' => 'secondary',
            'title' => 'No Recent Activity',
            'description' => 'No stock movements recorded in the past week. Consider updating inventory.'
        ];
    }
    
    // Low turnover alert
    if ($stats['stock_turnover'] < 20) {
        $critical_alerts[] = [
            'severity' => 'warning',
            'title' => 'Low Stock Turnover',
            'description' => 'Stock turnover rate is ' . $stats['stock_turnover'] . '%. Consider reviewing slow-moving items.'
        ];
    }
    
    $stats['critical_alerts'] = $critical_alerts;
    
    // Return the complete analytics data
    header('Content-Type: application/json');
    echo json_encode($stats);
    
} catch (PDOException $e) {
    http_response_code(500);
    echo json_encode(['error' => 'Database error: ' . $e->getMessage()]);
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(['error' => 'Server error: ' . $e->getMessage()]);
}
?>
