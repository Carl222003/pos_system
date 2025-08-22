<?php
require_once 'db_connect.php';
require_once 'auth_function.php';

// Helper function to calculate time ago
function calculateTimeAgo($datetime) {
    if (!$datetime) {
        return 'Just now';
    }
    
    $request_time = strtotime($datetime);
    $current_time = time();
    $time_diff = $current_time - $request_time;
    
    if ($time_diff < 0) {
        return 'Just now';
    } elseif ($time_diff < 30) {
        return 'Just now';
    } elseif ($time_diff < 3600) {
        $minutes = floor($time_diff / 60);
        return $minutes . ' minute' . ($minutes != 1 ? 's' : '') . ' ago';
    } elseif ($time_diff < 86400) {
        $hours = floor($time_diff / 3600);
        return $hours . ' hour' . ($hours != 1 ? 's' : '') . ' ago';
    } else {
        $days = floor($time_diff / 86400);
        if ($days < 7) {
            return $days . ' day' . ($days != 1 ? 's' : '') . ' ago';
        } else {
            return date('M j, Y', $request_time);
        }
    }
}

// Function to get notifications for Admin users
function getAdminNotifications() {
    global $pdo;
    $notifications = array();
    
    try {
        // Check for low stock ingredients
        $lowStockQuery = "SELECT ingredient_id, ingredient_name, ingredient_quantity, ingredient_unit, minimum_stock 
                         FROM ingredients 
                         WHERE ingredient_quantity <= minimum_stock AND ingredient_status != 'archived'
                         ORDER BY ingredient_quantity ASC 
                         LIMIT 10";
        
        $lowStockStmt = $pdo->query($lowStockQuery);
        while ($row = $lowStockStmt->fetch(PDO::FETCH_ASSOC)) {
            $notifications[] = array(
                'id' => 'low_' . $row['ingredient_id'],
                'type' => 'low_stock',
                'icon' => 'fas fa-exclamation-triangle',
                'icon_color' => '#ffc107',
                'title' => 'Low Stock Alert',
                'message' => "{$row['ingredient_name']} is running low",
                'details' => "Only {$row['ingredient_quantity']} {$row['ingredient_unit']} remaining",
                'timestamp' => 'Just now',
                'priority' => 'high'
            );
        }
        
        // Check for out of stock ingredients
        $outOfStockQuery = "SELECT ingredient_id, ingredient_name, ingredient_unit 
                           FROM ingredients 
                           WHERE ingredient_quantity <= 0 AND ingredient_status != 'archived'
                           ORDER BY ingredient_name ASC 
                           LIMIT 10";
        
        $outOfStockStmt = $pdo->query($outOfStockQuery);
        while ($row = $outOfStockStmt->fetch(PDO::FETCH_ASSOC)) {
            $notifications[] = array(
                'id' => 'out_' . $row['ingredient_id'],
                'type' => 'out_of_stock',
                'icon' => 'fas fa-times-circle',
                'icon_color' => '#dc3545',
                'title' => 'Out of Stock',
                'message' => "{$row['ingredient_name']} is completely out",
                'details' => "0 {$row['ingredient_unit']} available",
                'timestamp' => 'Just now',
                'priority' => 'critical'
            );
        }
        
        // Check for pending ingredient requests
        $pendingRequestsQuery = "SELECT ir.request_id, b.branch_name, ir.request_date, ir.ingredients
                                FROM ingredient_requests ir
                                JOIN pos_branch b ON ir.branch_id = b.branch_id
                                WHERE ir.status = 'pending'
                                ORDER BY ir.request_date DESC
                                LIMIT 10";
        
        $pendingStmt = $pdo->query($pendingRequestsQuery);
        while ($row = $pendingStmt->fetch(PDO::FETCH_ASSOC)) {
            // Parse ingredients to count them
            $ingredients_json = json_decode($row['ingredients'], true);
            $ingredient_count = is_array($ingredients_json) ? count($ingredients_json) : 0;
            
            // Calculate time ago using helper function
            $time_ago = calculateTimeAgo($row['request_date']);
            
            $notifications[] = array(
                'id' => 'req_' . $row['request_id'],
                'type' => 'pending_request',
                'icon' => 'fas fa-shopping-cart',
                'icon_color' => '#17a2b8',
                'title' => 'Stock Request',
                'message' => "New stock request from {$row['branch_name']}",
                'details' => "{$ingredient_count} ingredients requested",
                'timestamp' => $time_ago,
                'priority' => 'high'
            );
        }
        
        // Check for approved/fulfilled requests (for stockmen to know their requests were processed)
        $processedRequestsQuery = "SELECT ir.request_id, b.branch_name, ir.status, ir.updated_date,
                                         u_updated.user_name as updated_by_name
                                  FROM ingredient_requests ir
                                  JOIN pos_branch b ON ir.branch_id = b.branch_id
                                  LEFT JOIN pos_user u_updated ON ir.updated_by = u_updated.user_id
                                  WHERE ir.status IN ('approved', 'fulfilled') 
                                  AND ir.updated_date >= DATE_SUB(NOW(), INTERVAL 24 HOUR)
                                  ORDER BY ir.updated_date DESC
                                  LIMIT 5";
        
        $processedStmt = $pdo->query($processedRequestsQuery);
        while ($row = $processedStmt->fetch(PDO::FETCH_ASSOC)) {
            $status_color = $row['status'] === 'approved' ? '#28a745' : '#6f42c1';
            $status_icon = $row['status'] === 'approved' ? 'fas fa-check-circle' : 'fas fa-truck';
            $status_text = ucfirst($row['status']);
            
            $updated_by = $row['updated_by_name'] ? " by {$row['updated_by_name']}" : "";
            
            $notifications[] = array(
                'id' => 'processed_' . $row['request_id'],
                'type' => 'request_processed',
                'icon' => $status_icon,
                'icon_color' => $status_color,
                'title' => "Request {$status_text}",
                'message' => "Your request from {$row['branch_name']} was {$row['status']}{$updated_by}",
                'details' => "Status updated recently",
                'timestamp' => calculateTimeAgo($row['updated_date']),
                'priority' => 'medium'
            );
        }
        
        // Check for expiring ingredients
        $expiringQuery = "SELECT ingredient_id, ingredient_name, ingredient_unit, consume_before
                         FROM ingredients 
                         WHERE consume_before IS NOT NULL 
                         AND consume_before <= DATE_ADD(CURDATE(), INTERVAL 7 DAY)
                         AND ingredient_status != 'archived'
                         ORDER BY consume_before ASC
                         LIMIT 5";
        
        $expiringStmt = $pdo->query($expiringQuery);
        while ($row = $expiringStmt->fetch(PDO::FETCH_ASSOC)) {
            $daysLeft = ceil((strtotime($row['consume_before']) - time()) / (60 * 60 * 24));
            $notifications[] = array(
                'id' => 'exp_' . $row['ingredient_id'],
                'type' => 'expiring',
                'icon' => 'fas fa-calendar-times',
                'icon_color' => '#fd7e14',
                'title' => 'Expiring Soon',
                'message' => "{$row['ingredient_name']} expires in {$daysLeft} days",
                'details' => "Expires: " . date('M j, Y', strtotime($row['consume_before'])),
                'timestamp' => 'Just now',
                'priority' => 'medium'
            );
        }
        
        // Check for active cashier sessions (Admin dashboard info)
        $activeCashiersQuery = "SELECT COUNT(*) as active_count 
                               FROM pos_cashier_sessions 
                               WHERE is_active = 1 
                               AND login_time >= DATE_SUB(NOW(), INTERVAL 4 HOUR)";
        
        $activeCashiersStmt = $pdo->query($activeCashiersQuery);
        $activeCashiers = $activeCashiersStmt->fetch(PDO::FETCH_ASSOC);
        
        if ($activeCashiers['active_count'] > 0) {
            $notifications[] = array(
                'id' => 'cashiers_active',
                'type' => 'active_cashiers',
                'icon' => 'fas fa-users',
                'icon_color' => '#28a745',
                'title' => 'Active Cashiers',
                'message' => "{$activeCashiers['active_count']} cashiers currently active",
                'details' => "System is being used",
                'timestamp' => 'Just now',
                'priority' => 'low'
            );
        }
        
    } catch (PDOException $e) {
        error_log("Error fetching admin notifications: " . $e->getMessage());
    }
    
    return $notifications;
}

// Function to get notifications for Stockman users
function getStockmanNotifications() {
    global $pdo;
    $notifications = array();
    
    if (!isset($_SESSION['user_id']) || !isset($_SESSION['branch_id'])) {
        return $notifications;
    }
    
    $user_id = $_SESSION['user_id'];
    $branch_id = $_SESSION['branch_id'];
    
    try {
        // Check for low stock ingredients in their branch
        $lowStockQuery = "SELECT ingredient_id, ingredient_name, ingredient_quantity, ingredient_unit, minimum_stock 
                         FROM ingredients 
                         WHERE ingredient_quantity <= minimum_stock 
                         AND ingredient_status != 'archived'
                         AND branch_id = ?
                         ORDER BY ingredient_quantity ASC 
                         LIMIT 5";
        
        $lowStockStmt = $pdo->prepare($lowStockQuery);
        $lowStockStmt->execute([$branch_id]);
        while ($row = $lowStockStmt->fetch(PDO::FETCH_ASSOC)) {
            $notifications[] = array(
                'id' => 'low_' . $row['ingredient_id'],
                'type' => 'low_stock',
                'icon' => 'fas fa-exclamation-triangle',
                'icon_color' => '#ffc107',
                'title' => 'Low Stock Alert',
                'message' => "{$row['ingredient_name']} is running low",
                'details' => "Only {$row['ingredient_quantity']} {$row['ingredient_unit']} remaining",
                'timestamp' => 'Just now',
                'priority' => 'high'
            );
        }
        
        // Check for their own request status updates (approved/fulfilled in last 24 hours)
        $myRequestsQuery = "SELECT ir.request_id, ir.status, ir.updated_date, ir.ingredients,
                                  u_updated.user_name as updated_by_name
                           FROM ingredient_requests ir
                           LEFT JOIN pos_user u_updated ON ir.updated_by = u_updated.user_id
                           WHERE ir.branch_id = ? 
                           AND ir.status IN ('approved', 'fulfilled', 'rejected')
                           AND ir.updated_date >= DATE_SUB(NOW(), INTERVAL 24 HOUR)
                           ORDER BY ir.updated_date DESC
                           LIMIT 5";
        
        $myRequestsStmt = $pdo->prepare($myRequestsQuery);
        $myRequestsStmt->execute([$branch_id]);
        while ($row = $myRequestsStmt->fetch(PDO::FETCH_ASSOC)) {
            $status_colors = [
                'approved' => '#28a745',
                'fulfilled' => '#6f42c1', 
                'rejected' => '#dc3545'
            ];
            $status_icons = [
                'approved' => 'fas fa-check-circle',
                'fulfilled' => 'fas fa-truck',
                'rejected' => 'fas fa-times-circle'
            ];
            
            $ingredients_json = json_decode($row['ingredients'], true);
            $ingredient_count = is_array($ingredients_json) ? count($ingredients_json) : 0;
            
            $updated_by = $row['updated_by_name'] ? " by {$row['updated_by_name']}" : "";
            $status_text = ucfirst($row['status']);
            
            $notifications[] = array(
                'id' => 'mystatus_' . $row['request_id'],
                'type' => 'my_request_status',
                'icon' => $status_icons[$row['status']],
                'icon_color' => $status_colors[$row['status']],
                'title' => "Request {$status_text}",
                'message' => "Your stock request was {$row['status']}{$updated_by}",
                'details' => "{$ingredient_count} ingredients in request",
                'timestamp' => calculateTimeAgo($row['updated_date']),
                'priority' => 'high'
            );
        }
        
        // Check for expiring ingredients in their branch
        $expiringQuery = "SELECT ingredient_id, ingredient_name, ingredient_unit, consume_before
                         FROM ingredients 
                         WHERE consume_before IS NOT NULL 
                         AND consume_before <= DATE_ADD(CURDATE(), INTERVAL 7 DAY)
                         AND ingredient_status != 'archived'
                         AND branch_id = ?
                         ORDER BY consume_before ASC
                         LIMIT 3";
        
        $expiringStmt = $pdo->prepare($expiringQuery);
        $expiringStmt->execute([$branch_id]);
        while ($row = $expiringStmt->fetch(PDO::FETCH_ASSOC)) {
            $daysLeft = ceil((strtotime($row['consume_before']) - time()) / (60 * 60 * 24));
            $notifications[] = array(
                'id' => 'exp_' . $row['ingredient_id'],
                'type' => 'expiring',
                'icon' => 'fas fa-calendar-times',
                'icon_color' => '#fd7e14',
                'title' => 'Expiring Soon',
                'message' => "{$row['ingredient_name']} expires in {$daysLeft} days",
                'details' => "Expires: " . date('M j, Y', strtotime($row['consume_before'])),
                'timestamp' => 'Just now',
                'priority' => 'medium'
            );
        }
        
    } catch (PDOException $e) {
        error_log("Error fetching stockman notifications: " . $e->getMessage());
    }
    
    return $notifications;
}

// Function to get notifications for Cashier users
function getCashierNotifications() {
    global $pdo;
    $notifications = array();
    
    if (!isset($_SESSION['user_id']) || !isset($_SESSION['branch_id'])) {
        return $notifications;
    }
    
    $user_id = $_SESSION['user_id'];
    $branch_id = $_SESSION['branch_id'];
    
    try {
        // Check for recent orders in their branch
        $recentOrdersQuery = "SELECT o.order_id, o.order_date, o.total_amount, o.order_status,
                                    COUNT(oi.order_item_id) as item_count
                             FROM pos_order o
                             LEFT JOIN pos_order_item oi ON o.order_id = oi.order_id
                             WHERE o.branch_id = ?
                             AND o.order_date >= DATE_SUB(NOW(), INTERVAL 2 HOUR)
                             GROUP BY o.order_id
                             ORDER BY o.order_date DESC
                             LIMIT 5";
        
        $recentOrdersStmt = $pdo->prepare($recentOrdersQuery);
        $recentOrdersStmt->execute([$branch_id]);
        while ($row = $recentOrdersStmt->fetch(PDO::FETCH_ASSOC)) {
            $status_colors = [
                'pending' => '#ffc107',
                'processing' => '#17a2b8',
                'completed' => '#28a745',
                'cancelled' => '#dc3545'
            ];
            $status_icons = [
                'pending' => 'fas fa-clock',
                'processing' => 'fas fa-cog',
                'completed' => 'fas fa-check-circle',
                'cancelled' => 'fas fa-times-circle'
            ];
            
            $notifications[] = array(
                'id' => 'order_' . $row['order_id'],
                'type' => 'recent_order',
                'icon' => $status_icons[$row['order_status']] ?? 'fas fa-shopping-cart',
                'icon_color' => $status_colors[$row['order_status']] ?? '#6c757d',
                'title' => 'Recent Order',
                'message' => "Order #{$row['order_id']} - " . ucfirst($row['order_status']),
                'details' => "₱{$row['total_amount']} • {$row['item_count']} items",
                'timestamp' => calculateTimeAgo($row['order_date']),
                'priority' => 'medium'
            );
        }
        
        // Check for low stock products that might affect their sales
        $lowStockProductsQuery = "SELECT p.product_id, p.product_name, bp.quantity, p.product_price
                                 FROM pos_product p
                                 JOIN pos_branch_product bp ON p.product_id = bp.product_id
                                 WHERE bp.branch_id = ?
                                 AND bp.quantity <= 5
                                 AND p.product_status = 'active'
                                 ORDER BY bp.quantity ASC
                                 LIMIT 3";
        
        $lowStockProductsStmt = $pdo->prepare($lowStockProductsQuery);
        $lowStockProductsStmt->execute([$branch_id]);
        while ($row = $lowStockProductsStmt->fetch(PDO::FETCH_ASSOC)) {
            $notifications[] = array(
                'id' => 'product_low_' . $row['product_id'],
                'type' => 'low_product_stock',
                'icon' => 'fas fa-exclamation-triangle',
                'icon_color' => '#ffc107',
                'title' => 'Low Product Stock',
                'message' => "{$row['product_name']} is running low",
                'details' => "Only {$row['quantity']} units remaining",
                'timestamp' => 'Just now',
                'priority' => 'high'
            );
        }
        
        // Check for their own session status
        $sessionQuery = "SELECT login_time, is_active
                        FROM pos_cashier_sessions
                        WHERE user_id = ?
                        ORDER BY login_time DESC
                        LIMIT 1";
        
        $sessionStmt = $pdo->prepare($sessionQuery);
        $sessionStmt->execute([$user_id]);
        $session = $sessionStmt->fetch(PDO::FETCH_ASSOC);
        
        if ($session && $session['is_active']) {
            $sessionDuration = time() - strtotime($session['login_time']);
            $hours = floor($sessionDuration / 3600);
            
            if ($hours >= 4) {
                $notifications[] = array(
                    'id' => 'session_long',
                    'type' => 'long_session',
                    'icon' => 'fas fa-clock',
                    'icon_color' => '#fd7e14',
                    'title' => 'Long Session',
                    'message' => "You've been logged in for {$hours} hours",
                    'details' => "Consider taking a break",
                    'timestamp' => 'Just now',
                    'priority' => 'low'
                );
            }
        }
        
        // Check for today's sales summary
        $todaySalesQuery = "SELECT COUNT(*) as order_count, SUM(total_amount) as total_sales
                           FROM pos_order
                           WHERE branch_id = ?
                           AND DATE(order_date) = CURDATE()
                           AND order_status != 'cancelled'";
        
        $todaySalesStmt = $pdo->prepare($todaySalesQuery);
        $todaySalesStmt->execute([$branch_id]);
        $todaySales = $todaySalesStmt->fetch(PDO::FETCH_ASSOC);
        
        if ($todaySales['order_count'] > 0) {
            $notifications[] = array(
                'id' => 'today_summary',
                'type' => 'daily_summary',
                'icon' => 'fas fa-chart-line',
                'icon_color' => '#28a745',
                'title' => 'Today\'s Summary',
                'message' => "{$todaySales['order_count']} orders processed today",
                'details' => "Total: ₱{$todaySales['total_sales']}",
                'timestamp' => 'Just now',
                'priority' => 'low'
            );
        }
        
    } catch (PDOException $e) {
        error_log("Error fetching cashier notifications: " . $e->getMessage());
    }
    
    return $notifications;
}

// Function to get notifications as JSON based on user role
function getNotificationsJson() {
    $user_type = $_SESSION['user_type'] ?? 'Guest';
    
    switch($user_type) {
        case 'Admin':
        case 'Manager':
            $notifications = getAdminNotifications();
            break;
        case 'Stockman':
            $notifications = getStockmanNotifications();
            break;
        case 'Cashier':
            $notifications = getCashierNotifications();
            break;
        default:
            $notifications = array();
    }
    
    header('Content-Type: application/json');
    echo json_encode(array(
        'success' => true,
        'count' => count($notifications),
        'notifications' => $notifications,
        'timestamp' => date('Y-m-d H:i:s')
    ));
}

// If this file is called directly via AJAX
if (isset($_GET['action']) && $_GET['action'] === 'get_notifications') {
    getNotificationsJson();
}
?> 