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
    // Get count of branches where this product is available
    $stmt = $pdo->prepare("
        SELECT COUNT(DISTINCT bp.branch_id) as count
        FROM pos_branch_product bp
        INNER JOIN pos_branch b ON bp.branch_id = b.branch_id
        WHERE bp.product_id = ? AND b.status = 'Active'
    ");
    
    $stmt->execute([$product_id]);
    $result = $stmt->fetch(PDO::FETCH_ASSOC);
    
    echo json_encode([
        'success' => true,
        'count' => intval($result['count'])
    ]);
    
} catch (PDOException $e) {
    error_log("Database error in get_product_branch_count.php: " . $e->getMessage());
    echo json_encode([
        'success' => false,
        'message' => 'Database error occurred'
    ]);
}
?> 