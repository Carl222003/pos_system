<?php
require_once 'db_connect.php';
require_once 'auth_function.php';

checkAdminLogin();
header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] !== 'POST' || !isset($_POST['product_id'])) {
    echo json_encode(['success' => false, 'error' => 'Invalid request']);
    exit;
}

$productId = intval($_POST['product_id']);

try {
    $pdo->beginTransaction();
    
    // Get all active branches
    $branchesStmt = $pdo->query("SELECT branch_id FROM pos_branch WHERE status = 'Active'");
    $branches = $branchesStmt->fetchAll(PDO::FETCH_COLUMN);
    
    if (empty($branches)) {
        echo json_encode(['success' => false, 'error' => 'No active branches found']);
        exit;
    }
    
    // Check which table to use
    $tables = $pdo->query("SHOW TABLES")->fetchAll(PDO::FETCH_COLUMN);
    $usePosBranchProduct = in_array('pos_branch_product', $tables);
    
    if (!$usePosBranchProduct && !in_array('product_branch', $tables)) {
        // Create pos_branch_product table
        $createTableSQL = "CREATE TABLE IF NOT EXISTS pos_branch_product (
            branch_product_id INT PRIMARY KEY AUTO_INCREMENT,
            branch_id INT NOT NULL,
            product_id INT NOT NULL,
            quantity INT NOT NULL DEFAULT 10,
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
            UNIQUE KEY unique_branch_product (branch_id, product_id),
            FOREIGN KEY (branch_id) REFERENCES pos_branch(branch_id) ON DELETE CASCADE,
            FOREIGN KEY (product_id) REFERENCES pos_product(product_id) ON DELETE CASCADE
        )";
        $pdo->exec($createTableSQL);
        $usePosBranchProduct = true;
    }
    
    // Delete existing assignments for this product
    if ($usePosBranchProduct) {
        $deleteStmt = $pdo->prepare("DELETE FROM pos_branch_product WHERE product_id = ?");
        $deleteStmt->execute([$productId]);
        
        // Insert assignments for all branches
        $insertStmt = $pdo->prepare("
            INSERT INTO pos_branch_product (branch_id, product_id, quantity) 
            VALUES (?, ?, 10)
        ");
        
        foreach ($branches as $branchId) {
            $insertStmt->execute([$branchId, $productId]);
        }
    } else {
        // Use product_branch table
        $deleteStmt = $pdo->prepare("DELETE FROM product_branch WHERE product_id = ?");
        $deleteStmt->execute([$productId]);
        
        // Insert assignments for all branches
        $insertStmt = $pdo->prepare("
            INSERT INTO product_branch (product_id, branch_id) 
            VALUES (?, ?)
        ");
        
        foreach ($branches as $branchId) {
            $insertStmt->execute([$productId, $branchId]);
        }
    }
    
    $pdo->commit();
    
    echo json_encode([
        'success' => true,
        'message' => 'Product assigned to all branches successfully',
        'branches_assigned' => count($branches),
        'table_used' => $usePosBranchProduct ? 'pos_branch_product' : 'product_branch'
    ]);
    
} catch (Exception $e) {
    $pdo->rollBack();
    error_log("Error in quick_assign_product.php: " . $e->getMessage());
    echo json_encode([
        'success' => false,
        'error' => $e->getMessage()
    ]);
}
?>