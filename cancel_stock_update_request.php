<?php
require_once 'db_connect.php';
require_once 'auth_function.php';

header('Content-Type: application/json');

// Check if user is logged in and is a stockman
requireLogin();
if ($_SESSION['user_type'] !== 'Stockman') {
    echo json_encode(['success' => false, 'error' => 'Access denied. Only Stockman can cancel requests.']);
    exit();
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(['success' => false, 'error' => 'Invalid request method.']);
    exit();
}

// Get JSON input
$input = json_decode(file_get_contents('php://input'), true);

if (!isset($input['request_id']) || !is_numeric($input['request_id'])) {
    echo json_encode(['success' => false, 'error' => 'Invalid request ID.']);
    exit();
}

try {
    $stockman_id = $_SESSION['user_id'];
    $request_id = intval($input['request_id']);
    
    // Check if request exists and belongs to this stockman
    $stmt = $pdo->prepare("
        SELECT sur.request_id, sur.status, sur.ingredient_id, i.ingredient_name
        FROM stock_update_requests sur
        JOIN ingredients i ON sur.ingredient_id = i.ingredient_id
        WHERE sur.request_id = ? AND sur.stockman_id = ?
    ");
    $stmt->execute([$request_id, $stockman_id]);
    $request = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if (!$request) {
        echo json_encode(['success' => false, 'error' => 'Request not found or access denied.']);
        exit();
    }
    
    // Check if request can be cancelled (only pending requests)
    if ($request['status'] !== 'pending') {
        echo json_encode(['success' => false, 'error' => 'Only pending requests can be cancelled.']);
        exit();
    }
    
    // Delete the request
    $stmt = $pdo->prepare("DELETE FROM stock_update_requests WHERE request_id = ? AND stockman_id = ?");
    $stmt->execute([$request_id, $stockman_id]);
    
    if ($stmt->rowCount() > 0) {
        // Delete related notifications
        $stmt = $pdo->prepare("DELETE FROM admin_stock_notifications WHERE request_id = ?");
        $stmt->execute([$request_id]);
        
        // Log the cancellation
        $log_message = "Stock update request cancelled: " . $request['ingredient_name'];
        $stmt = $pdo->prepare("INSERT INTO activity_log (user_id, action, details, ip_address) VALUES (?, ?, ?, ?)");
        $stmt->execute([$stockman_id, 'stock_update_cancelled', $log_message, $_SERVER['REMOTE_ADDR']]);
        
        echo json_encode([
            'success' => true,
            'message' => 'Request cancelled successfully.'
        ]);
    } else {
        echo json_encode(['success' => false, 'error' => 'Failed to cancel request.']);
    }
    
} catch (PDOException $e) {
    error_log("Database error in cancel_stock_update_request.php: " . $e->getMessage());
    echo json_encode(['success' => false, 'error' => 'Database error occurred. Please try again.']);
} catch (Exception $e) {
    error_log("Error in cancel_stock_update_request.php: " . $e->getMessage());
    echo json_encode(['success' => false, 'error' => 'An error occurred. Please try again.']);
}
?>
