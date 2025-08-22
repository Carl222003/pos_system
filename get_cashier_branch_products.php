<?php
require_once 'db_connect.php';
require_once 'auth_function.php';

// Check if user is logged in as cashier
if (!checkCashierLogin()) {
    header('Content-Type: application/json');
    echo json_encode(['success' => false, 'error' => 'Unauthorized access']);
    exit;
}

header('Content-Type: application/json');

try {
    // Get cashier's branch ID from session
    $cashier_user_id = $_SESSION['user_id'];
    
    // Get cashier's branch assignment
    $stmt = $pdo->prepare("
        SELECT branch_id 
        FROM pos_user 
        WHERE user_id = ? AND user_type = 'Cashier'
    ");
    $stmt->execute([$cashier_user_id]);
    $cashier = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if (!$cashier || !$cashier['branch_id']) {
        echo json_encode([
            'success' => false, 
            'error' => 'Cashier not assigned to any branch'
        ]);
        exit;
    }
    
    $branch_id = $cashier['branch_id'];
    
    // Get search and filter parameters
    $search = $_GET['search'] ?? '';
    $category = $_GET['category'] ?? '';
    $status = $_GET['status'] ?? '';
    
    // Build the query to get products assigned to cashier's branch
    $query = "
        SELECT DISTINCT
            p.product_id,
            p.product_name,
            p.product_price,
            p.product_status,
            p.product_image,
            p.description,
            c.category_name,
            COALESCE(bp.quantity, 0) as branch_quantity
        FROM pos_product p
        LEFT JOIN pos_category c ON p.category_id = c.category_id
        INNER JOIN pos_branch_product bp ON p.product_id = bp.product_id
        WHERE bp.branch_id = ?
        AND p.product_status = 'Available'
    ";
    
    $params = [$branch_id];
    
    // Add search filter
    if (!empty($search)) {
        $query .= " AND (p.product_name LIKE ? OR p.description LIKE ?)";
        $params[] = "%$search%";
        $params[] = "%$search%";
    }
    
    // Add category filter
    if (!empty($category)) {
        $query .= " AND c.category_id = ?";
        $params[] = $category;
    }
    
    // Add status filter (though we're already filtering for Available only)
    if (!empty($status) && $status !== 'Available') {
        $query .= " AND p.product_status = ?";
        $params[] = $status;
    }
    
    $query .= " ORDER BY p.product_name";
    
    $stmt = $pdo->prepare($query);
    $stmt->execute($params);
    $products = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    // Get branch information
    $branchStmt = $pdo->prepare("SELECT branch_name, branch_code FROM pos_branch WHERE branch_id = ?");
    $branchStmt->execute([$branch_id]);
    $branch = $branchStmt->fetch(PDO::FETCH_ASSOC);
    
    echo json_encode([
        'success' => true,
        'products' => $products,
        'branch' => $branch,
        'total_products' => count($products),
        'filters' => [
            'search' => $search,
            'category' => $category,
            'status' => $status
        ]
    ]);
    
} catch (Exception $e) {
    error_log("Error in get_cashier_branch_products.php: " . $e->getMessage());
    echo json_encode([
        'success' => false,
        'error' => 'Database error occurred'
    ]);
}
?>
