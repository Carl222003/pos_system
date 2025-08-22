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
            UNIQUE KEY unique_branch_product (branch_id, product_id)
        )";
        $pdo->exec($createTableSQL);
        $usePosBranchProduct = true;
    }
    
    $connectionsCreated = 0;
    
    if ($usePosBranchProduct) {
        // Use pos_branch_product table
        $insertStmt = $pdo->prepare("
            INSERT INTO pos_branch_product (branch_id, product_id, quantity) 
            VALUES (?, ?, 10) 
            ON DUPLICATE KEY UPDATE quantity = 10
        ");
        
        foreach ($products as $productId) {
            foreach ($branches as $branchId) {
                try {
                    $insertStmt->execute([$branchId, $productId]);
                    $connectionsCreated++;
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
        $insertStmt = $pdo->prepare("
            INSERT INTO product_branch (product_id, branch_id) 
            VALUES (?, ?) 
            ON DUPLICATE KEY UPDATE product_id = product_id
        ");
        
        foreach ($products as $productId) {
            foreach ($branches as $branchId) {
                try {
                    $insertStmt->execute([$productId, $branchId]);
                    $connectionsCreated++;
                } catch (PDOException $e) {
                    // Skip duplicates
                    if ($e->getCode() != 23000) {
                        throw $e;
                    }
                }
            }
        }
    }
    
    echo json_encode([
        'success' => true,
        'message' => 'All products connected to all branches successfully',
        'connections_created' => $connectionsCreated,
        'products_count' => count($products),
        'branches_count' => count($branches),
        'table_used' => $usePosBranchProduct ? 'pos_branch_product' : 'product_branch'
    ]);
    
} catch (Exception $e) {
    echo json_encode([
        'success' => false,
        'error' => $e->getMessage()
    ]);
}
?>
