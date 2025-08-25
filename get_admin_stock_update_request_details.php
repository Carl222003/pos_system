<?php
require_once 'db_connect.php';
require_once 'auth_function.php';

header('Content-Type: application/json');

// Check if user is logged in and is an admin
checkAdminLogin();

if (!isset($_GET['request_id']) || !is_numeric($_GET['request_id'])) {
    echo json_encode(['success' => false, 'error' => 'Invalid request ID.']);
    exit();
}

try {
    $request_id = intval($_GET['request_id']);
    
    // Get request details with all related information
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
            sur.processed_by,
            sur.processed_date,
            i.ingredient_name,
            i.ingredient_quantity as current_stock,
            i.ingredient_unit as current_unit,
            i.ingredient_status,
            i.branch_id,
            c.category_name,
            u.username as stockman_name,
            u.user_id as stockman_id,
            u.email as stockman_email,
            b.branch_name,
            b.branch_address,
            admin.username as admin_name,
            admin.email as admin_email
        FROM stock_update_requests sur
        JOIN ingredients i ON sur.ingredient_id = i.ingredient_id
        LEFT JOIN pos_category c ON i.category_id = c.category_id
        JOIN pos_user u ON sur.stockman_id = u.user_id
        LEFT JOIN pos_branch b ON i.branch_id = b.branch_id
        LEFT JOIN pos_user admin ON sur.processed_by = admin.user_id
        WHERE sur.request_id = ?
    ");
    
    $stmt->execute([$request_id]);
    $request = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if (!$request) {
        echo json_encode(['success' => false, 'error' => 'Request not found.']);
        exit();
    }
    
    // Format dates
    $request['request_date'] = date('Y-m-d H:i:s', strtotime($request['request_date']));
    if ($request['response_date']) {
        $request['response_date'] = date('Y-m-d H:i:s', strtotime($request['response_date']));
    }
    if ($request['processed_date']) {
        $request['processed_date'] = date('Y-m-d H:i:s', strtotime($request['processed_date']));
    }
    
    // Add formatted display values
    $request['formatted_request_date'] = date('M j, Y g:i A', strtotime($request['request_date']));
    if ($request['response_date']) {
        $request['formatted_response_date'] = date('M j, Y g:i A', strtotime($request['response_date']));
    }
    if ($request['processed_date']) {
        $request['formatted_processed_date'] = date('M j, Y g:i A', strtotime($request['processed_date']));
    }
    
    // Add status and urgency descriptions
    $status_descriptions = [
        'pending' => 'Awaiting admin approval',
        'approved' => 'Approved by admin',
        'rejected' => 'Rejected by admin',
        'completed' => 'Stock has been updated'
    ];
    $request['status_description'] = $status_descriptions[$request['status']] ?? 'Unknown status';
    
    $urgency_descriptions = [
        'low' => 'Normal restocking',
        'medium' => 'Running low',
        'high' => 'Critical level',
        'critical' => 'Out of stock'
    ];
    $request['urgency_description'] = $urgency_descriptions[$request['urgency_level']] ?? 'Unknown urgency';
    
    $priority_descriptions = [
        'normal' => 'Standard priority',
        'high' => 'High priority',
        'urgent' => 'Urgent priority'
    ];
    $request['priority_description'] = $priority_descriptions[$request['priority']] ?? 'Unknown priority';
    
    // Get related stock movement logs if any
    $stmt = $pdo->prepare("
        SELECT 
            sul.log_id,
            sul.old_quantity,
            sul.new_quantity,
            sul.change_amount,
            sul.change_type,
            sul.update_date,
            sul.notes,
            u.username as updated_by_name
        FROM stock_update_logs sul
        JOIN pos_user u ON sul.updated_by = u.user_id
        WHERE sul.request_id = ?
        ORDER BY sul.update_date DESC
    ");
    $stmt->execute([$request_id]);
    $stock_logs = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    // Format stock log dates
    foreach ($stock_logs as &$log) {
        $log['update_date'] = date('Y-m-d H:i:s', strtotime($log['update_date']));
        $log['formatted_update_date'] = date('M j, Y g:i A', strtotime($log['update_date']));
    }
    
    // Calculate time since request
    $request_date = new DateTime($request['request_date']);
    $now = new DateTime();
    $time_diff = $now->diff($request_date);
    
    $request['time_since_request'] = [
        'days' => $time_diff->days,
        'hours' => $time_diff->h,
        'minutes' => $time_diff->i,
        'formatted' => $time_diff->days > 0 ? 
            $time_diff->days . ' day' . ($time_diff->days > 1 ? 's' : '') . ' ago' :
            ($time_diff->h > 0 ? $time_diff->h . ' hour' . ($time_diff->h > 1 ? 's' : '') . ' ago' :
            $time_diff->i . ' minute' . ($time_diff->i > 1 ? 's' : '') . ' ago')
    ];
    
    // Add response time if available
    if ($request['response_date']) {
        $response_date = new DateTime($request['response_date']);
        $response_diff = $response_date->diff($request_date);
        $request['response_time'] = [
            'hours' => $response_diff->h + ($response_diff->days * 24),
            'minutes' => $response_diff->i,
            'formatted' => ($response_diff->h + ($response_diff->days * 24)) > 0 ? 
                ($response_diff->h + ($response_diff->days * 24)) . ' hour' . (($response_diff->h + ($response_diff->days * 24)) > 1 ? 's' : '') :
                $response_diff->i . ' minute' . ($response_diff->i > 1 ? 's' : '')
        ];
    }
    
    // Get similar requests from the same stockman
    $stmt = $pdo->prepare("
        SELECT 
            sur.request_id,
            sur.ingredient_id,
            sur.status,
            sur.request_date,
            i.ingredient_name
        FROM stock_update_requests sur
        JOIN ingredients i ON sur.ingredient_id = i.ingredient_id
        WHERE sur.stockman_id = ? AND sur.request_id != ?
        ORDER BY sur.request_date DESC
        LIMIT 5
    ");
    $stmt->execute([$request['stockman_id'], $request_id]);
    $similar_requests = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    // Format similar request dates
    foreach ($similar_requests as &$similar) {
        $similar['request_date'] = date('Y-m-d H:i:s', strtotime($similar['request_date']));
        $similar['formatted_request_date'] = date('M j, Y g:i A', strtotime($similar['request_date']));
    }
    
    echo json_encode([
        'success' => true,
        'request' => $request,
        'stock_logs' => $stock_logs,
        'similar_requests' => $similar_requests
    ]);
    
} catch (PDOException $e) {
    error_log("Database error in get_admin_stock_update_request_details.php: " . $e->getMessage());
    echo json_encode(['success' => false, 'error' => 'Database error occurred. Please try again.']);
} catch (Exception $e) {
    error_log("Error in get_admin_stock_update_request_details.php: " . $e->getMessage());
    echo json_encode(['success' => false, 'error' => 'An error occurred. Please try again.']);
}
?>
