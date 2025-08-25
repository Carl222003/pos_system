<?php
require_once 'db_connect.php';
require_once 'auth_function.php';

header('Content-Type: application/json');

// Check if user is logged in and is an admin
checkAdminLogin();

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(['success' => false, 'error' => 'Invalid request method.']);
    exit();
}

try {
    // Validate required fields
    $required_fields = ['request_id', 'action', 'response_message'];
    foreach ($required_fields as $field) {
        if (!isset($_POST[$field]) || empty($_POST[$field])) {
            echo json_encode(['success' => false, 'error' => "Missing required field: $field"]);
            exit();
        }
    }
    
    $admin_id = $_SESSION['user_id'];
    $request_id = intval($_POST['request_id']);
    $action = $_POST['action'];
    $response_message = trim($_POST['response_message']);
    
    // Validate action
    if (!in_array($action, ['approve', 'reject', 'complete'])) {
        echo json_encode(['success' => false, 'error' => 'Invalid action.']);
        exit();
    }
    
    // Get request details
    $stmt = $pdo->prepare("
        SELECT sur.*, i.ingredient_name, i.ingredient_quantity as current_stock, i.ingredient_unit as current_unit,
               u.username as stockman_name, u.user_id as stockman_id
        FROM stock_update_requests sur
        JOIN ingredients i ON sur.ingredient_id = i.ingredient_id
        JOIN pos_user u ON sur.stockman_id = u.user_id
        WHERE sur.request_id = ?
    ");
    $stmt->execute([$request_id]);
    $request = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if (!$request) {
        echo json_encode(['success' => false, 'error' => 'Request not found.']);
        exit();
    }
    
    // Check if request can be processed
    if ($action === 'approve' && $request['status'] !== 'pending') {
        echo json_encode(['success' => false, 'error' => 'Only pending requests can be approved.']);
        exit();
    }
    
    if ($action === 'reject' && $request['status'] !== 'pending') {
        echo json_encode(['success' => false, 'error' => 'Only pending requests can be rejected.']);
        exit();
    }
    
    if ($action === 'complete' && $request['status'] !== 'approved') {
        echo json_encode(['success' => false, 'error' => 'Only approved requests can be completed.']);
        exit();
    }
    
    // Start transaction
    $pdo->beginTransaction();
    
    try {
        // Update request status
        $new_status = '';
        switch ($action) {
            case 'approve':
                $new_status = 'approved';
                break;
            case 'reject':
                $new_status = 'rejected';
                break;
            case 'complete':
                $new_status = 'completed';
                break;
        }
        
        $stmt = $pdo->prepare("
            UPDATE stock_update_requests 
            SET status = ?, admin_response = ?, response_date = NOW(), processed_by = ?, processed_date = NOW()
            WHERE request_id = ?
        ");
        $stmt->execute([$new_status, $response_message, $admin_id, $request_id]);
        
        // If completing the request, update the ingredient stock
        if ($action === 'complete') {
            $stock_quantity = isset($_POST['stock_quantity']) ? floatval($_POST['stock_quantity']) : 0;
            $stock_unit = isset($_POST['stock_unit']) ? $_POST['stock_unit'] : '';
            
            if ($stock_quantity > 0 && !empty($stock_unit)) {
                // Get current stock
                $current_stock = $request['current_stock'];
                $current_unit = $request['current_unit'];
                
                // Calculate new stock based on update type
                $new_stock = $current_stock;
                switch ($request['update_type']) {
                    case 'add':
                        $new_stock += $stock_quantity;
                        break;
                    case 'adjust':
                        $new_stock = $stock_quantity;
                        break;
                    case 'correct':
                        $new_stock = $stock_quantity;
                        break;
                }
                
                // Update ingredient stock
                $stmt = $pdo->prepare("
                    UPDATE ingredients 
                    SET ingredient_quantity = ?, ingredient_unit = ?
                    WHERE ingredient_id = ?
                ");
                $stmt->execute([$new_stock, $stock_unit, $request['ingredient_id']]);
                
                // Log the stock change
                $change_amount = $new_stock - $current_stock;
                $change_type = $change_amount > 0 ? 'add' : ($change_amount < 0 ? 'subtract' : 'set');
                
                $stmt = $pdo->prepare("
                    INSERT INTO stock_update_logs 
                    (request_id, ingredient_id, old_quantity, new_quantity, change_amount, change_type, updated_by, notes)
                    VALUES (?, ?, ?, ?, ?, ?, ?, ?)
                ");
                $stmt->execute([
                    $request_id,
                    $request['ingredient_id'],
                    $current_stock,
                    $new_stock,
                    abs($change_amount),
                    $change_type,
                    $admin_id,
                    "Stock updated by admin: " . $response_message
                ]);
            }
        }
        
        // Create notification for stockman
        $notification_message = "Your stock update request for " . $request['ingredient_name'] . " has been " . $new_status . " by admin.";
        
        $stmt = $pdo->prepare("
            INSERT INTO admin_stock_notifications 
            (request_id, admin_id, notification_type, message) 
            VALUES (?, ?, ?, ?)
        ");
        $stmt->execute([
            $request_id,
            $request['stockman_id'],
            'request_' . $new_status,
            $notification_message
        ]);
        
        // Log the admin action
        $log_message = "Admin " . $action . "ed stock update request: " . $request['ingredient_name'] . " (Request #" . $request_id . ")";
        $stmt = $pdo->prepare("INSERT INTO activity_log (user_id, action, details, ip_address) VALUES (?, ?, ?, ?)");
        $stmt->execute([$admin_id, 'admin_stock_response', $log_message, $_SERVER['REMOTE_ADDR']]);
        
        // Commit transaction
        $pdo->commit();
        
        $success_message = "Request " . $new_status . " successfully.";
        if ($action === 'complete') {
            $success_message .= " Stock has been updated.";
        }
        
        echo json_encode([
            'success' => true,
            'message' => $success_message,
            'request_id' => $request_id,
            'new_status' => $new_status
        ]);
        
    } catch (Exception $e) {
        // Rollback transaction on error
        $pdo->rollBack();
        throw $e;
    }
    
} catch (PDOException $e) {
    error_log("Database error in admin_respond_to_stock_request.php: " . $e->getMessage());
    echo json_encode(['success' => false, 'error' => 'Database error occurred. Please try again.']);
} catch (Exception $e) {
    error_log("Error in admin_respond_to_stock_request.php: " . $e->getMessage());
    echo json_encode(['success' => false, 'error' => 'An error occurred. Please try again.']);
}
?>
