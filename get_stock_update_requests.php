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
    
    // Get all requests for this stockman with ingredient details
    $stmt = $pdo->prepare("
        SELECT 
            sur.request_id,
            sur.ingredient_id,
            sur.update_type,
            sur.quantity,
            sur.unit,
            sur.urgency_level,
            sur.priority,
            sur.reason,
            sur.notes,
            sur.status,
            sur.admin_response,
            sur.request_date,
            sur.response_date,
            i.ingredient_name,
            i.ingredient_quantity as current_stock,
            i.ingredient_unit as current_unit,
            c.category_name
        FROM stock_update_requests sur
        JOIN ingredients i ON sur.ingredient_id = i.ingredient_id
        LEFT JOIN pos_category c ON i.category_id = c.category_id
        WHERE sur.stockman_id = ?
        ORDER BY sur.request_date DESC
    ");
    
    $stmt->execute([$stockman_id]);
    $requests = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    // Process the requests to add additional information
    foreach ($requests as &$request) {
        // Format dates
        $request['request_date'] = date('Y-m-d H:i:s', strtotime($request['request_date']));
        if ($request['response_date']) {
            $request['response_date'] = date('Y-m-d H:i:s', strtotime($request['response_date']));
        }
        
        // Add status color classes
        $request['status_class'] = $request['status'];
        $request['urgency_class'] = $request['urgency_level'];
        
        // Add is_new flag for recent requests (within 24 hours)
        $request_date = new DateTime($request['request_date']);
        $now = new DateTime();
        $diff = $now->diff($request_date);
        $request['is_new'] = ($diff->days === 0 && $diff->h < 24);
        
        // Add formatted display values
        $request['formatted_request_date'] = date('M j, Y g:i A', strtotime($request['request_date']));
        if ($request['response_date']) {
            $request['formatted_response_date'] = date('M j, Y g:i A', strtotime($request['response_date']));
        }
        
        // Add status description
        $status_descriptions = [
            'pending' => 'Awaiting admin approval',
            'approved' => 'Approved by admin',
            'rejected' => 'Rejected by admin',
            'completed' => 'Stock has been updated'
        ];
        $request['status_description'] = $status_descriptions[$request['status']] ?? 'Unknown status';
        
        // Add urgency description
        $urgency_descriptions = [
            'low' => 'Normal restocking',
            'medium' => 'Running low',
            'high' => 'Critical level',
            'critical' => 'Out of stock'
        ];
        $request['urgency_description'] = $urgency_descriptions[$request['urgency_level']] ?? 'Unknown urgency';
    }
    
    echo json_encode([
        'success' => true,
        'requests' => $requests,
        'total_count' => count($requests)
    ]);
    
} catch (PDOException $e) {
    error_log("Database error in get_stock_update_requests.php: " . $e->getMessage());
    echo json_encode(['success' => false, 'error' => 'Database error occurred. Please try again.']);
} catch (Exception $e) {
    error_log("Error in get_stock_update_requests.php: " . $e->getMessage());
    echo json_encode(['success' => false, 'error' => 'An error occurred. Please try again.']);
}
?>
