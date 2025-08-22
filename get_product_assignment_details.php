<?php
require_once 'db_connect.php';
require_once 'auth_function.php';

checkAdminLogin();
header('Content-Type: application/json');

if (!isset($_GET['product_id'])) {
    echo json_encode(['success' => false, 'error' => 'Product ID required']);
    exit;
}

$productId = intval($_GET['product_id']);

try {
    // Get product details
    $productStmt = $pdo->prepare("
        SELECT p.*, c.category_name 
        FROM pos_product p 
        LEFT JOIN pos_category c ON p.category_id = c.category_id 
        WHERE p.product_id = ?
    ");
    $productStmt->execute([$productId]);
    $product = $productStmt->fetch(PDO::FETCH_ASSOC);
    
    if (!$product) {
        echo json_encode(['success' => false, 'error' => 'Product not found']);
        exit;
    }
    
    // Get all branches
    $branchesStmt = $pdo->query("
        SELECT branch_id, branch_name 
        FROM pos_branch 
        WHERE status = 'Active' 
        ORDER BY branch_name
    ");
    $allBranches = $branchesStmt->fetchAll(PDO::FETCH_ASSOC);
    
    // Check which table to use for assignments
    $tables = $pdo->query("SHOW TABLES")->fetchAll(PDO::FETCH_COLUMN);
    $usePosBranchProduct = in_array('pos_branch_product', $tables);
    $useProductBranch = in_array('product_branch', $tables);
    
    // Get current assignments
    $assignedBranches = [];
    if ($usePosBranchProduct) {
        $assignmentStmt = $pdo->prepare("
            SELECT branch_id FROM pos_branch_product WHERE product_id = ?
        ");
        $assignmentStmt->execute([$productId]);
        $assignedBranches = $assignmentStmt->fetchAll(PDO::FETCH_COLUMN);
    } elseif ($useProductBranch) {
        $assignmentStmt = $pdo->prepare("
            SELECT branch_id FROM product_branch WHERE product_id = ?
        ");
        $assignmentStmt->execute([$productId]);
        $assignedBranches = $assignmentStmt->fetchAll(PDO::FETCH_COLUMN);
    }
    
    echo json_encode([
        'success' => true,
        'product' => $product,
        'all_branches' => $allBranches,
        'assigned_branches' => array_map('intval', $assignedBranches),
        'table_used' => $usePosBranchProduct ? 'pos_branch_product' : ($useProductBranch ? 'product_branch' : 'none')
    ]);
    
} catch (Exception $e) {
    error_log("Error in get_product_assignment_details.php: " . $e->getMessage());
    echo json_encode([
        'success' => false,
        'error' => $e->getMessage()
    ]);
}
?>