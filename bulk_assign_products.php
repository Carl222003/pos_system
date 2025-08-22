<?php
require_once 'db_connect.php';
require_once 'auth_function.php';

checkAdminLogin();
header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(['success' => false, 'error' => 'POST method required']);
    exit;
}

try {
    $pdo->beginTransaction();
    
    // Check which table to use or create it
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
    
    // Get all active products
            $productsStmt = $pdo->query("SELECT product_id FROM pos_product WHERE product_status IS NOT NULL AND product_status != ''");
    $products = $productsStmt->fetchAll(PDO::FETCH_COLUMN);
    
    // Get all active branches
    $branchesStmt = $pdo->query("SELECT branch_id FROM pos_branch WHERE status = 'Active'");
    $branches = $branchesStmt->fetchAll(PDO::FETCH_COLUMN);
    
    if (empty($products)) {
        echo json_encode(['success' => false, 'error' => 'No active products found']);
        exit;
    }
    
    if (empty($branches)) {
        echo json_encode(['success' => false, 'error' => 'No active branches found']);
        exit;
    }
    
    // Clear existing assignments
    if ($usePosBranchProduct) {
        $pdo->exec("DELETE FROM pos_branch_product");
        
        // Insert all combinations
        $insertStmt = $pdo->prepare("
            INSERT INTO pos_branch_product (branch_id, product_id, quantity) 
            VALUES (?, ?, 10)
        ");
        
        $assignmentsCount = 0;
        foreach ($products as $productId) {
            foreach ($branches as $branchId) {
                try {
                    $insertStmt->execute([$branchId, $productId]);
                    $assignmentsCount++;
                } catch (PDOException $e) {
                    // Skip duplicates
                    if ($e->getCode() != 23000) {
                        throw $e;
                    }
                }
            }
        }
    } else {
        // Use product_branch table
        $pdo->exec("DELETE FROM product_branch");
        
        // Insert all combinations
        $insertStmt = $pdo->prepare("
            INSERT INTO product_branch (product_id, branch_id) 
            VALUES (?, ?)
        ");
        
        $assignmentsCount = 0;
        foreach ($products as $productId) {
            foreach ($branches as $branchId) {
                try {
                    $insertStmt->execute([$productId, $branchId]);
                    $assignmentsCount++;
                } catch (PDOException $e) {
                    // Skip duplicates
                    if ($e->getCode() != 23000) {
                        throw $e;
                    }
                }
            }
        }
    }
    
    $pdo->commit();
    
    echo json_encode([
        'success' => true,
        'message' => 'All products assigned to all branches successfully',
        'products_assigned' => count($products),
        'branches_count' => count($branches),
        'total_assignments' => $assignmentsCount,
        'table_used' => $usePosBranchProduct ? 'pos_branch_product' : 'product_branch'
    ]);
    
} catch (Exception $e) {
    $pdo->rollBack();
    error_log("Error in bulk_assign_products.php: " . $e->getMessage());
    echo json_encode([
        'success' => false,
        'error' => $e->getMessage()
    ]);
}
?>