<?php
require_once 'db_connect.php';
require_once 'auth_function.php';

header('Content-Type: application/json');

// Check if user is logged in and is an admin
checkAdminLogin();

try {
    // Get total requests
    $stmt = $pdo->prepare("SELECT COUNT(*) FROM stock_update_requests");
    $stmt->execute();
    $total_requests = $stmt->fetchColumn();
    
    // Get pending requests
    $stmt = $pdo->prepare("SELECT COUNT(*) FROM stock_update_requests WHERE status = 'pending'");
    $stmt->execute();
    $pending_requests = $stmt->fetchColumn();
    
    // Get urgent requests (high or critical urgency)
    $stmt = $pdo->prepare("SELECT COUNT(*) FROM stock_update_requests WHERE urgency_level IN ('high', 'critical')");
    $stmt->execute();
    $urgent_requests = $stmt->fetchColumn();
    
    // Get approved requests today
    $stmt = $pdo->prepare("SELECT COUNT(*) FROM stock_update_requests WHERE status = 'approved' AND DATE(response_date) = CURDATE()");
    $stmt->execute();
    $approved_today = $stmt->fetchColumn();
    
    // Get rejected requests
    $stmt = $pdo->prepare("SELECT COUNT(*) FROM stock_update_requests WHERE status = 'rejected'");
    $stmt->execute();
    $rejected_requests = $stmt->fetchColumn();
    
    // Get completed requests
    $stmt = $pdo->prepare("SELECT COUNT(*) FROM stock_update_requests WHERE status = 'completed'");
    $stmt->execute();
    $completed_requests = $stmt->fetchColumn();
    
    // Get requests by urgency level
    $stmt = $pdo->prepare("
        SELECT urgency_level, COUNT(*) as count 
        FROM stock_update_requests 
        GROUP BY urgency_level
    ");
    $stmt->execute();
    $urgency_breakdown = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    // Get requests by status
    $stmt = $pdo->prepare("
        SELECT status, COUNT(*) as count 
        FROM stock_update_requests 
        GROUP BY status
    ");
    $stmt->execute();
    $status_breakdown = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    // Get requests by update type
    $stmt = $pdo->prepare("
        SELECT update_type, COUNT(*) as count 
        FROM stock_update_requests 
        GROUP BY update_type
    ");
    $stmt->execute();
    $update_type_breakdown = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    // Get recent activity (last 7 days)
    $stmt = $pdo->prepare("
        SELECT COUNT(*) 
        FROM stock_update_requests 
        WHERE request_date >= DATE_SUB(NOW(), INTERVAL 7 DAY)
    ");
    $stmt->execute();
    $recent_activity = $stmt->fetchColumn();
    
    // Get average response time for completed requests
    $stmt = $pdo->prepare("
        SELECT AVG(TIMESTAMPDIFF(HOUR, request_date, response_date)) as avg_response_hours
        FROM stock_update_requests 
        WHERE status IN ('approved', 'rejected', 'completed')
        AND response_date IS NOT NULL
    ");
    $stmt->execute();
    $avg_response_time = $stmt->fetchColumn();
    
    // Get requests by branch
    $stmt = $pdo->prepare("
        SELECT b.branch_name, COUNT(*) as count 
        FROM stock_update_requests sur
        JOIN ingredients i ON sur.ingredient_id = i.ingredient_id
        LEFT JOIN pos_branch b ON i.branch_id = b.branch_id
        GROUP BY i.branch_id, b.branch_name
        ORDER BY count DESC
    ");
    $stmt->execute();
    $branch_breakdown = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    // Get top requesting stockmen
    $stmt = $pdo->prepare("
        SELECT u.username as stockman_name, COUNT(*) as count 
        FROM stock_update_requests sur
        JOIN pos_user u ON sur.stockman_id = u.user_id
        GROUP BY sur.stockman_id, u.username
        ORDER BY count DESC
        LIMIT 5
    ");
    $stmt->execute();
    $top_stockmen = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    // Get critical requests (out of stock)
    $stmt = $pdo->prepare("SELECT COUNT(*) FROM stock_update_requests WHERE urgency_level = 'critical' AND status = 'pending'");
    $stmt->execute();
    $critical_pending = $stmt->fetchColumn();
    
    echo json_encode([
        'success' => true,
        'stats' => [
            'total_requests' => intval($total_requests),
            'pending_requests' => intval($pending_requests),
            'urgent_requests' => intval($urgent_requests),
            'approved_today' => intval($approved_today),
            'rejected_requests' => intval($rejected_requests),
            'completed_requests' => intval($completed_requests),
            'recent_activity' => intval($recent_activity),
            'critical_pending' => intval($critical_pending),
            'avg_response_hours' => $avg_response_time ? round($avg_response_time, 1) : 0,
            'urgency_breakdown' => $urgency_breakdown,
            'status_breakdown' => $status_breakdown,
            'update_type_breakdown' => $update_type_breakdown,
            'branch_breakdown' => $branch_breakdown,
            'top_stockmen' => $top_stockmen
        ]
    ]);
    
} catch (PDOException $e) {
    error_log("Database error in get_admin_stock_update_stats.php: " . $e->getMessage());
    echo json_encode(['success' => false, 'error' => 'Database error occurred. Please try again.']);
} catch (Exception $e) {
    error_log("Error in get_admin_stock_update_stats.php: " . $e->getMessage());
    echo json_encode(['success' => false, 'error' => 'An error occurred. Please try again.']);
}
?>
