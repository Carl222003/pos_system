<?php
require_once 'db_connect.php';
require_once 'auth_function.php';

// Check if user is logged in and is a stockman
if (!isset($_SESSION['user_logged_in']) || $_SESSION['user_logged_in'] !== true || $_SESSION['user_type'] !== 'Stockman') {
    http_response_code(403);
    echo json_encode(['error' => 'Unauthorized access']);
    exit();
}

header('Content-Type: application/json');

try {
    $user_id = $_SESSION['user_id'];
    $branch_id = $_SESSION['branch_id'] ?? null;

    // If branch_id is not in session, try to fetch from user record
    if (!$branch_id) {
        $stmt = $pdo->prepare('SELECT branch_id FROM pos_user WHERE user_id = ?');
        $stmt->execute([$user_id]);
        $branch_id = $stmt->fetchColumn();
    }

    if (!$branch_id) {
        echo json_encode(['error' => 'Branch not found for this user']);
        exit();
    }

    // Get all requests for this stockman's branch
    $query = "SELECT r.*, b.branch_name, u.user_name as updated_by_name
              FROM ingredient_requests r 
              LEFT JOIN pos_branch b ON r.branch_id = b.branch_id 
              LEFT JOIN pos_user u ON r.updated_by = u.user_id
              WHERE r.branch_id = ?
              ORDER BY r.request_date DESC";
    
    $stmt = $pdo->prepare($query);
    $stmt->execute([$branch_id]);
    $requests = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    // Format data for display
    $data = array();
    foreach ($requests as $request) {
        // Parse ingredients JSON and get ingredient names
        $ingredients_list = [];
        $ingredients_json = json_decode($request['ingredients'], true);
        
        if ($ingredients_json && is_array($ingredients_json)) {
            foreach ($ingredients_json as $ingredient) {
                if (isset($ingredient['ingredient_id']) && isset($ingredient['quantity'])) {
                    // Get ingredient name from database
                    $stmt_ingredient = $pdo->prepare("SELECT ingredient_name, ingredient_unit FROM ingredients WHERE ingredient_id = ?");
                    $stmt_ingredient->execute([$ingredient['ingredient_id']]);
                    $ingredient_info = $stmt_ingredient->fetch(PDO::FETCH_ASSOC);
                    
                    if ($ingredient_info) {
                        $ingredients_list[] = $ingredient_info['ingredient_name'] . ' (' . $ingredient['quantity'] . ' ' . $ingredient_info['ingredient_unit'] . ')';
                    } else {
                        $ingredients_list[] = 'Unknown Ingredient (ID: ' . $ingredient['ingredient_id'] . ') - ' . $ingredient['quantity'];
                    }
                }
            }
        }
        
        $ingredients_display = !empty($ingredients_list) ? implode(', ', $ingredients_list) : 'No ingredients specified';
        
        // Format status with appropriate styling
        $status_badge = '';
        switch ($request['status']) {
            case 'pending':
                $status_badge = '<span class="badge bg-warning">PENDING</span>';
                break;
            case 'approved':
                $status_badge = '<span class="badge bg-success">APPROVED</span>';
                break;
            case 'rejected':
                $status_badge = '<span class="badge bg-danger">REJECTED</span>';
                break;
            default:
                $status_badge = '<span class="badge bg-secondary">' . strtoupper($request['status']) . '</span>';
        }
        
        // Format delivery status with appropriate styling
        $delivery_status_badge = '';
        $delivery_status = isset($request['delivery_status']) ? $request['delivery_status'] : 'pending';
        switch ($delivery_status) {
            case 'pending':
                $delivery_status_badge = '<span class="badge bg-secondary">PENDING</span>';
                break;
            case 'on_delivery':
                $delivery_status_badge = '<span class="badge bg-info">ON DELIVERY</span>';
                break;
            case 'delivered':
                $delivery_status_badge = '<span class="badge bg-success">DELIVERED</span>';
                break;
            case 'returned':
                $delivery_status_badge = '<span class="badge bg-warning">RETURNED</span>';
                break;
            case 'cancelled':
                $delivery_status_badge = '<span class="badge bg-danger">CANCELLED</span>';
                break;
            default:
                $delivery_status_badge = '<span class="badge bg-secondary">PENDING</span>';
        }
        
        $data[] = array(
            'request_id' => $request['request_id'],
            'request_date' => date('M j, Y g:i A', strtotime($request['request_date'])),
            'ingredients' => $ingredients_display,
            'status' => $status_badge,
            'delivery_status' => $delivery_status_badge,
            'notes' => $request['notes'] ?: '-',
            'updated_by' => $request['updated_by_name'] ?: '-'
        );
    }
    
    echo json_encode([
        'success' => true,
        'data' => $data
    ]);

} catch (Exception $e) {
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'error' => $e->getMessage()
    ]);
}
?> 