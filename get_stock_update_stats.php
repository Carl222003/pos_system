<?php
require_once 'db_connect.php';
require_once 'auth_function.php';

header('Content-Type: application/json');

// Check if user is logged in and is a stockman
requireLogin();
if ($_SESSION['user_type'] !== 'Stockman') {
    echo json_encode(['success' => false, 'error' => 'Access denied. Only Stockman can access this data.']);
    exit();
}

try {
    $stockman_id = $_SESSION['user_id'];
    
    // Get total requests
    $stmt = $pdo->prepare("SELECT COUNT(*) FROM stock_update_requests WHERE stockman_id = ?");
    $stmt->execute([$stockman_id]);
    $total_requests = $stmt->fetchColumn();
    
    // Get pending requests
    $stmt = $pdo->prepare("SELECT COUNT(*) FROM stock_update_requests WHERE stockman_id = ? AND status = 'pending'");
    $stmt->execute([$stockman_id]);
    $pending_requests = $stmt->fetchColumn();
    
    // Get approved requests
    $stmt = $pdo->prepare("SELECT COUNT(*) FROM stock_update_requests WHERE stockman_id = ? AND status = 'approved'");
    $stmt->execute([$stockman_id]);
    $approved_requests = $stmt->fetchColumn();
    
    // Get completed requests
    $stmt = $pdo->prepare("SELECT COUNT(*) FROM stock_update_requests WHERE stockman_id = ? AND status = 'completed'");
    $stmt->execute([$stockman_id]);
    $completed_requests = $stmt->fetchColumn();
    
    // Get rejected requests
    $stmt = $pdo->prepare("SELECT COUNT(*) FROM stock_update_requests WHERE stockman_id = ? AND status = 'rejected'");
    $stmt->execute([$stockman_id]);
    $rejected_requests = $stmt->fetchColumn();
    
    // Get urgent requests (high or critical urgency)
    $stmt = $pdo->prepare("SELECT COUNT(*) FROM stock_update_requests WHERE stockman_id = ? AND urgency_level IN ('high', 'critical')");
    $stmt->execute([$stockman_id]);
    $urgent_requests = $stmt->fetchColumn();
    
    // Get requests by urgency level
    $stmt = $pdo->prepare("
        SELECT urgency_level, COUNT(*) as count 
        FROM stock_update_requests 
        WHERE stockman_id = ? 
        GROUP BY urgency_level
    ");
    $stmt->execute([$stockman_id]);
    $urgency_breakdown = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    // Get requests by update type
    $stmt = $pdo->prepare("
        SELECT update_type, COUNT(*) as count 
        FROM stock_update_requests 
        WHERE stockman_id = ? 
        GROUP BY update_type
    ");
    $stmt->execute([$stockman_id]);
    $update_type_breakdown = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    // Get recent activity (last 7 days)
    $stmt = $pdo->prepare("
        SELECT COUNT(*) 
        FROM stock_update_requests 
        WHERE stockman_id = ? 
        AND request_date >= DATE_SUB(NOW(), INTERVAL 7 DAY)
    ");
    $stmt->execute([$stockman_id]);
    $recent_activity = $stmt->fetchColumn();
    
    // Get average response time for completed requests
    $stmt = $pdo->prepare("
        SELECT AVG(TIMESTAMPDIFF(HOUR, request_date, response_date)) as avg_response_hours
        FROM stock_update_requests 
        WHERE stockman_id = ? 
        AND status IN ('approved', 'rejected', 'completed')
        AND response_date IS NOT NULL
    ");
    $stmt->execute([$stockman_id]);
    $avg_response_time = $stmt->fetchColumn();
    
    echo json_encode([
        'success' => true,
        'stats' => [
            'total_requests' => intval($total_requests),
            'pending_requests' => intval($pending_requests),
            'approved_requests' => intval($approved_requests),
            'completed_requests' => intval($completed_requests),
            'rejected_requests' => intval($rejected_requests),
            'urgent_requests' => intval($urgent_requests),
            'recent_activity' => intval($recent_activity),
            'avg_response_hours' => $avg_response_time ? round($avg_response_time, 1) : 0,
            'urgency_breakdown' => $urgency_breakdown,
            'update_type_breakdown' => $update_type_breakdown
        ]
    ]);
    
} catch (PDOException $e) {
    error_log("Database error in get_stock_update_stats.php: " . $e->getMessage());
    echo json_encode(['success' => false, 'error' => 'Database error occurred. Please try again.']);
} catch (Exception $e) {
    error_log("Error in get_stock_update_stats.php: " . $e->getMessage());
    echo json_encode(['success' => false, 'error' => 'An error occurred. Please try again.']);
}
?>
