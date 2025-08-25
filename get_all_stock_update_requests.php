<?php
require_once 'db_connect.php';
require_once 'auth_function.php';

header('Content-Type: application/json');

// Check if user is logged in and is an admin
checkAdminLogin();

try {
    // Get all requests with stockman and ingredient details
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
            i.branch_id,
            c.category_name,
            u.username as stockman_name,
            u.user_id as stockman_id,
            b.branch_name,
            admin.username as admin_name
        FROM stock_update_requests sur
        JOIN ingredients i ON sur.ingredient_id = i.ingredient_id
        LEFT JOIN pos_category c ON i.category_id = c.category_id
        JOIN pos_user u ON sur.stockman_id = u.user_id
        LEFT JOIN pos_branch b ON i.branch_id = b.branch_id
        LEFT JOIN pos_user admin ON sur.processed_by = admin.user_id
        ORDER BY 
            CASE 
                WHEN sur.urgency_level = 'critical' THEN 1
                WHEN sur.urgency_level = 'high' THEN 2
                WHEN sur.urgency_level = 'medium' THEN 3
                ELSE 4
            END,
            sur.request_date DESC
    ");
    
    $stmt->execute();
    $requests = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    // Process the requests to add additional information
    foreach ($requests as &$request) {
        // Format dates
        $request['request_date'] = date('Y-m-d H:i:s', strtotime($request['request_date']));
        if ($request['response_date']) {
            $request['response_date'] = date('Y-m-d H:i:s', strtotime($request['response_date']));
        }
        if ($request['processed_date']) {
            $request['processed_date'] = date('Y-m-d H:i:s', strtotime($request['processed_date']));
        }
        
        // Add status color classes
        $request['status_class'] = $request['status'];
        $request['urgency_class'] = $request['urgency_level'];
        $request['priority_class'] = $request['priority'];
        
        // Add is_urgent flag for high/critical urgency
        $request['is_urgent'] = ($request['urgency_level'] === 'high' || $request['urgency_level'] === 'critical');
        
        // Add formatted display values
        $request['formatted_request_date'] = date('M j, Y g:i A', strtotime($request['request_date']));
        if ($request['response_date']) {
            $request['formatted_response_date'] = date('M j, Y g:i A', strtotime($request['response_date']));
        }
        if ($request['processed_date']) {
            $request['formatted_processed_date'] = date('M j, Y g:i A', strtotime($request['processed_date']));
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
        
        // Add priority description
        $priority_descriptions = [
            'normal' => 'Standard priority',
            'high' => 'High priority',
            'urgent' => 'Urgent priority'
        ];
        $request['priority_description'] = $priority_descriptions[$request['priority']] ?? 'Unknown priority';
        
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
    }
    
    echo json_encode([
        'success' => true,
        'requests' => $requests,
        'total_count' => count($requests)
    ]);
    
} catch (PDOException $e) {
    error_log("Database error in get_all_stock_update_requests.php: " . $e->getMessage());
    echo json_encode(['success' => false, 'error' => 'Database error occurred. Please try again.']);
} catch (Exception $e) {
    error_log("Error in get_all_stock_update_requests.php: " . $e->getMessage());
    echo json_encode(['success' => false, 'error' => 'An error occurred. Please try again.']);
}
?>
