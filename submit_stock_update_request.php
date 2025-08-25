<?php
require_once 'db_connect.php';
require_once 'auth_function.php';

header('Content-Type: application/json');

// Check if user is logged in and is a stockman
requireLogin();
if ($_SESSION['user_type'] !== 'Stockman') {
    echo json_encode(['success' => false, 'error' => 'Access denied. Only Stockman can submit requests.']);
    exit();
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(['success' => false, 'error' => 'Invalid request method.']);
    exit();
}

try {
    // Validate required fields
    $required_fields = ['ingredient_id', 'update_type', 'quantity', 'unit', 'urgency_level', 'priority', 'reason'];
    foreach ($required_fields as $field) {
        if (!isset($_POST[$field]) || empty($_POST[$field])) {
            echo json_encode(['success' => false, 'error' => "Missing required field: $field"]);
            exit();
        }
    }
    
    $stockman_id = $_SESSION['user_id'];
    $ingredient_id = intval($_POST['ingredient_id']);
    $update_type = $_POST['update_type'];
    $quantity = floatval($_POST['quantity']);
    $unit = $_POST['unit'];
    $urgency_level = $_POST['urgency_level'];
    $priority = $_POST['priority'];
    $reason = trim($_POST['reason']);
    $notes = isset($_POST['notes']) ? trim($_POST['notes']) : '';
    
    // Validate update type
    if (!in_array($update_type, ['add', 'adjust', 'correct'])) {
        echo json_encode(['success' => false, 'error' => 'Invalid update type.']);
        exit();
    }
    
    // Validate urgency level
    if (!in_array($urgency_level, ['low', 'medium', 'high', 'critical'])) {
        echo json_encode(['success' => false, 'error' => 'Invalid urgency level.']);
        exit();
    }
    
    // Validate priority
    if (!in_array($priority, ['normal', 'high', 'urgent'])) {
        echo json_encode(['success' => false, 'error' => 'Invalid priority level.']);
        exit();
    }
    
    // Validate quantity
    if ($quantity <= 0) {
        echo json_encode(['success' => false, 'error' => 'Quantity must be greater than 0.']);
        exit();
    }
    
    // Get stockman's branch information
    $stmt = $pdo->prepare("SELECT branch_id, user_name FROM pos_user WHERE user_id = ?");
    $stmt->execute([$stockman_id]);
    $stockman_info = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if (!$stockman_info || !$stockman_info['branch_id']) {
        echo json_encode(['success' => false, 'error' => 'You are not assigned to any branch. Please contact administrator.']);
        exit();
    }
    
    // Check if ingredient exists and belongs to stockman's branch
    $stmt = $pdo->prepare("SELECT i.ingredient_id, i.ingredient_name, i.branch_id, b.branch_name
                          FROM ingredients i 
                          JOIN pos_branch b ON i.branch_id = b.branch_id
                          WHERE i.ingredient_id = ? AND i.branch_id = ?");
    $stmt->execute([$ingredient_id, $stockman_info['branch_id']]);
    $ingredient = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if (!$ingredient) {
        echo json_encode(['success' => false, 'error' => 'Ingredient not found or does not belong to your assigned branch.']);
        exit();
    }
    
    // Check if there's already a pending request for this ingredient
    $stmt = $pdo->prepare("SELECT COUNT(*) FROM stock_update_requests 
                          WHERE stockman_id = ? AND ingredient_id = ? AND status = 'pending'");
    $stmt->execute([$stockman_id, $ingredient_id]);
    $pending_count = $stmt->fetchColumn();
    
    if ($pending_count > 0) {
        echo json_encode(['success' => false, 'error' => 'You already have a pending request for this ingredient.']);
        exit();
    }
    
    // Insert the request
    $stmt = $pdo->prepare("INSERT INTO stock_update_requests 
                          (stockman_id, ingredient_id, update_type, quantity, unit, urgency_level, priority, reason, notes) 
                          VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)");
    
    $stmt->execute([
        $stockman_id,
        $ingredient_id,
        $update_type,
        $quantity,
        $unit,
        $urgency_level,
        $priority,
        $reason,
        $notes
    ]);
    
    $request_id = $pdo->lastInsertId();
    
    // Create notification for admins
    $notification_message = "New stock update request from " . $_SESSION['username'] . 
                           " for " . $ingredient['ingredient_name'] . 
                           " (Urgency: " . ucfirst($urgency_level) . ")";
    
    $notification_type = ($urgency_level === 'critical' || $urgency_level === 'high') ? 'urgent_request' : 'new_request';
    
    // Get all admin users
    $stmt = $pdo->prepare("SELECT user_id FROM pos_user WHERE user_type = 'Admin' AND status = 'active'");
    $stmt->execute();
    $admins = $stmt->fetchAll(PDO::FETCH_COLUMN);
    
    // Create notifications for each admin
    $stmt = $pdo->prepare("INSERT INTO admin_stock_notifications 
                          (request_id, admin_id, notification_type, message) VALUES (?, ?, ?, ?)");
    
    foreach ($admins as $admin_id) {
        $stmt->execute([$request_id, $admin_id, $notification_type, $notification_message]);
    }
    
    // Log the request
    $log_message = "Stock update request submitted: " . $ingredient['ingredient_name'] . 
                   " - " . $quantity . " " . $unit . " (" . $update_type . ")";
    
    $stmt = $pdo->prepare("INSERT INTO activity_log (user_id, action, details, ip_address) VALUES (?, ?, ?, ?)");
    $stmt->execute([$stockman_id, 'stock_update_request', $log_message, $_SERVER['REMOTE_ADDR']]);
    
    echo json_encode([
        'success' => true,
        'message' => 'Stock update request submitted successfully.',
        'request_id' => $request_id
    ]);
    
} catch (PDOException $e) {
    error_log("Database error in submit_stock_update_request.php: " . $e->getMessage());
    echo json_encode(['success' => false, 'error' => 'Database error occurred. Please try again.']);
} catch (Exception $e) {
    error_log("Error in submit_stock_update_request.php: " . $e->getMessage());
    echo json_encode(['success' => false, 'error' => 'An error occurred. Please try again.']);
}
?>
