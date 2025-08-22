<?php
require_once 'db_connect.php';
require_once 'auth_function.php';

checkAdminLogin();
header('Content-Type: application/json');

try {
    // Check which tables exist
    $tables = $pdo->query("SHOW TABLES")->fetchAll(PDO::FETCH_COLUMN);
    $usePosBranchProduct = in_array('pos_branch_product', $tables);
    $useProductBranch = in_array('product_branch', $tables);
    
    // Get total products
    $totalProducts = $pdo->query("SELECT COUNT(*) FROM pos_product")->fetchColumn();
    
    // Get total active branches
    $totalBranches = $pdo->query("SELECT COUNT(*) FROM pos_branch WHERE status = 'Active'")->fetchColumn();
    
    // Get total assignments
    $totalAssignments = 0;
    if ($usePosBranchProduct) {
        $totalAssignments = $pdo->query("SELECT COUNT(*) FROM pos_branch_product")->fetchColumn();
    } elseif ($useProductBranch) {
        $totalAssignments = $pdo->query("SELECT COUNT(*) FROM product_branch")->fetchColumn();
    }
    
    // Get unassigned products
    $unassignedProducts = 0;
    if ($usePosBranchProduct) {
        $unassignedProducts = $pdo->query("
            SELECT COUNT(*) FROM pos_product p 
            WHERE p.product_id NOT IN (SELECT DISTINCT product_id FROM pos_branch_product)
        ")->fetchColumn();
    } elseif ($useProductBranch) {
        $unassignedProducts = $pdo->query("
            SELECT COUNT(*) FROM pos_product p 
            WHERE p.product_id NOT IN (SELECT DISTINCT product_id FROM product_branch)
        ")->fetchColumn();
    } else {
        $unassignedProducts = $totalProducts; // All products are unassigned if no assignment table exists
    }
    
    echo json_encode([
        'success' => true,
        'stats' => [
            'total_products' => (int)$totalProducts,
            'total_branches' => (int)$totalBranches,
            'total_assignments' => (int)$totalAssignments,
            'unassigned_products' => (int)$unassignedProducts
        ],
        'table_used' => $usePosBranchProduct ? 'pos_branch_product' : ($useProductBranch ? 'product_branch' : 'none')
    ]);
    
} catch (Exception $e) {
    error_log("Error in get_assignment_stats.php: " . $e->getMessage());
    echo json_encode([
        'success' => false,
        'error' => $e->getMessage(),
        'stats' => [
            'total_products' => 0,
            'total_branches' => 0,
            'total_assignments' => 0,
            'unassigned_products' => 0
        ]
    ]);
}
?>