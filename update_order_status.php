<?php
require_once 'db_connect.php';
require_once 'auth_function.php';

// Check if user has access to orders
checkOrderAccess();

header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode([
        'success' => false,
        'message' => 'Invalid request method'
    ]);
    exit;
}

$input = json_decode(file_get_contents('php://input'), true);

if (!isset($input['order_id']) || !isset($input['status'])) {
    echo json_encode([
        'success' => false,
        'message' => 'Order ID and status are required'
    ]);
    exit;
}

$orderId = intval($input['order_id']);
$newStatus = $input['status'];
$userId = $_SESSION['user_id'] ?? null;

// Validate status
$validStatuses = ['PENDING', 'PREPARING', 'READY', 'COMPLETED'];
if (!in_array($newStatus, $validStatuses)) {
    echo json_encode([
        'success' => false,
        'message' => 'Invalid status'
    ]);
    exit;
}

try {
    // Update order status
    $updateQuery = "
        UPDATE pos_orders 
        SET status = ?, updated_at = CURRENT_TIMESTAMP
        WHERE order_id = ?
    ";
    
    $stmt = $pdo->prepare($updateQuery);
    $result = $stmt->execute([$newStatus, $orderId]);
    
    if ($result) {
        // Log the status change
        $logQuery = "
            INSERT INTO pos_order_status_log (order_id, user_id, old_status, new_status, changed_at)
            SELECT ?, ?, status, ?, CURRENT_TIMESTAMP
            FROM pos_orders 
            WHERE order_id = ?
        ";
        
        $logStmt = $pdo->prepare($logQuery);
        $logStmt->execute([$orderId, $userId, $newStatus, $orderId]);
        
        echo json_encode([
            'success' => true,
            'message' => 'Order status updated successfully',
            'order_id' => $orderId,
            'new_status' => $newStatus
        ]);
    } else {
        echo json_encode([
            'success' => false,
            'message' => 'Failed to update order status'
        ]);
    }
    
} catch (PDOException $e) {
    error_log('Database error in update_order_status.php: ' . $e->getMessage());
    echo json_encode([
        'success' => false,
        'message' => 'Database error occurred',
        'error' => $e->getMessage()
    ]);
} catch (Exception $e) {
    error_log('General error in update_order_status.php: ' . $e->getMessage());
    echo json_encode([
        'success' => false,
        'message' => 'An error occurred',
        'error' => $e->getMessage()
    ]);
}
?>
