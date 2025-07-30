<?php
require_once 'db_connect.php';
require_once 'auth_function.php';

// Check if user is logged in and is a stockman
if (!isset($_SESSION['user_logged_in']) || $_SESSION['user_logged_in'] !== true || $_SESSION['user_type'] !== 'Stockman') {
    header('Content-Type: application/json');
    echo json_encode(['success' => false, 'message' => 'Unauthorized access. Only Stockman can update delivery status.']);
    exit();
}

header('Content-Type: application/json');

try {
    if (!isset($_POST['request_id']) || !isset($_POST['delivery_status'])) {
        throw new Exception('Missing required parameters');
    }

    $requestId = $_POST['request_id'];
    $deliveryStatus = $_POST['delivery_status'];
    $deliveryDate = isset($_POST['delivery_date']) && !empty($_POST['delivery_date']) ? $_POST['delivery_date'] : null;
    $deliveryNotes = isset($_POST['delivery_notes']) ? $_POST['delivery_notes'] : '';
    $updatedBy = $_SESSION['user_id'];
    $updateDate = date('Y-m-d H:i:s');
    $branchId = $_SESSION['branch_id'];

    // Validate delivery status
    $validStatuses = ['pending', 'on_delivery', 'delivered', 'returned', 'cancelled'];
    if (!in_array($deliveryStatus, $validStatuses)) {
        throw new Exception('Invalid delivery status');
    }

    // If status is delivered and no delivery date provided, use current date
    if ($deliveryStatus === 'delivered' && empty($deliveryDate)) {
        $deliveryDate = date('Y-m-d H:i:s');
    }

    // Check if the request belongs to this stockman's branch
    $check_request = $pdo->prepare("SELECT request_id FROM ingredient_requests WHERE request_id = ? AND branch_id = ?");
    $check_request->execute([$requestId, $branchId]);
    if (!$check_request->fetch()) {
        throw new Exception('Request not found or not accessible for this branch');
    }

    // First, check if delivery_status column exists, if not create it
    $check_columns = $pdo->query("SHOW COLUMNS FROM ingredient_requests LIKE 'delivery_status'");
    if ($check_columns->rowCount() == 0) {
        // Add the missing columns
        $pdo->exec("ALTER TABLE ingredient_requests 
                   ADD COLUMN delivery_status ENUM('pending', 'on_delivery', 'delivered', 'returned', 'cancelled') DEFAULT 'pending' 
                   AFTER status");
        $pdo->exec("ALTER TABLE ingredient_requests 
                   ADD COLUMN delivery_date TIMESTAMP NULL 
                   AFTER delivery_status");
        $pdo->exec("ALTER TABLE ingredient_requests 
                   ADD COLUMN delivery_notes TEXT NULL 
                   AFTER delivery_date");
    }

    // Update delivery status
    $query = "UPDATE ingredient_requests 
              SET delivery_status = :delivery_status,
                  delivery_date = :delivery_date,
                  delivery_notes = :delivery_notes,
                  updated_by = :updated_by,
                  updated_at = :updated_at
              WHERE request_id = :request_id AND branch_id = :branch_id";

    $stmt = $pdo->prepare($query);
    $stmt->bindParam(':delivery_status', $deliveryStatus);
    $stmt->bindParam(':delivery_date', $deliveryDate);
    $stmt->bindParam(':delivery_notes', $deliveryNotes);
    $stmt->bindParam(':updated_by', $updatedBy);
    $stmt->bindParam(':updated_at', $updateDate);
    $stmt->bindParam(':request_id', $requestId);
    $stmt->bindParam(':branch_id', $branchId);

    if ($stmt->execute()) {
        // Log the activity
        $request_info = $pdo->prepare("SELECT branch_id, ingredients FROM ingredient_requests WHERE request_id = ?");
        $request_info->execute([$requestId]);
        $request_data = $request_info->fetch(PDO::FETCH_ASSOC);
        
        if ($request_data) {
            $branch_name = $pdo->query("SELECT branch_name FROM pos_branch WHERE branch_id = " . $request_data['branch_id'])->fetchColumn();
            $action_message = "Updated delivery status for ingredient request";
            
            // Check if logActivity function exists before calling it
            if (function_exists('logActivity')) {
                logActivity($pdo, $updatedBy, $action_message, "Request ID: $requestId, Delivery Status: $deliveryStatus, Branch: $branch_name", $request_data['branch_id']);
            }
        }
        
        $message = 'Delivery status updated successfully';
        
        echo json_encode([
            'success' => true,
            'message' => $message
        ]);
    } else {
        throw new Exception('Failed to update delivery status');
    }

} catch (Exception $e) {
    http_response_code(400);
    echo json_encode([
        'success' => false,
        'message' => $e->getMessage(),
        'debug' => [
            'request_id' => $requestId ?? 'not set',
            'delivery_status' => $deliveryStatus ?? 'not set',
            'branch_id' => $branchId ?? 'not set',
            'user_id' => $updatedBy ?? 'not set'
        ]
    ]);
}
?> 