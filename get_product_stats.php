<?php
require_once 'db_connect.php';
require_once 'auth_function.php';

// Check if user is logged in
if (!isLoggedIn()) {
    http_response_code(401);
    echo json_encode(['success' => false, 'message' => 'Unauthorized']);
    exit;
}

// Check if product_id is provided
if (!isset($_GET['product_id']) || empty($_GET['product_id'])) {
    echo json_encode(['success' => false, 'message' => 'Product ID is required']);
    exit;
}

$product_id = intval($_GET['product_id']);

try {
    // Get view count
    $view_count = 0;
    $stmt = $pdo->query("SHOW TABLES LIKE 'product_views'");
    if ($stmt->rowCount() > 0) {
        $stmt = $pdo->prepare("SELECT COUNT(*) as view_count FROM product_views WHERE product_id = ?");
        $stmt->execute([$product_id]);
        $result = $stmt->fetch(PDO::FETCH_ASSOC);
        $view_count = intval($result['view_count']);
    }
    
    // Get order count from pos_order_item table
    $order_count = 0;
    $stmt = $pdo->prepare("
        SELECT COUNT(DISTINCT oi.order_id) as order_count 
        FROM pos_order_item oi 
        WHERE oi.product_id = ?
    ");
    $stmt->execute([$product_id]);
    $result = $stmt->fetch(PDO::FETCH_ASSOC);
    $order_count = intval($result['order_count']);
    
    echo json_encode([
        'success' => true,
        'data' => [
            'view_count' => $view_count,
            'order_count' => $order_count
        ]
    ]);
    
} catch (PDOException $e) {
    error_log("Database error in get_product_stats.php: " . $e->getMessage());
    echo json_encode([
        'success' => false,
        'message' => 'Database error occurred'
    ]);
}
?> 