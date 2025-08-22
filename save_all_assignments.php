<?php
require_once 'db_connect.php';
require_once 'auth_function.php';

checkAdminLogin();
header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(['success' => false, 'error' => 'POST method required']);
    exit;
}

// Get JSON input
$input = json_decode(file_get_contents('php://input'), true);

if (!isset($input['assignments']) || !is_array($input['assignments'])) {
    echo json_encode(['success' => false, 'error' => 'Invalid assignments data']);
    exit;
}

$assignments = $input['assignments'];

try {
    $pdo->beginTransaction();
    
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
    
    // Clear all existing assignments first
    if ($usePosBranchProduct) {
        $pdo->exec("DELETE FROM pos_branch_product");
    } else {
        $pdo->exec("DELETE FROM product_branch");
    }
    
    // Insert new assignments
    $insertedCount = 0;
    
    if ($usePosBranchProduct) {
        // Use pos_branch_product table
        $insertStmt = $pdo->prepare("
            INSERT INTO pos_branch_product (branch_id, product_id, quantity) 
            VALUES (?, ?, ?)
        ");
        
        foreach ($assignments as $assignment) {
            $branchId = intval($assignment['branch_id']);
            $productId = intval($assignment['product_id']);
            $quantity = intval($assignment['quantity']);
            
            if ($branchId > 0 && $productId > 0 && $quantity > 0) {
                $insertStmt->execute([$branchId, $productId, $quantity]);
                $insertedCount++;
            }
        }
    } else {
        // Use product_branch table
        $insertStmt = $pdo->prepare("
            INSERT INTO product_branch (product_id, branch_id) 
            VALUES (?, ?)
        ");
        
        foreach ($assignments as $assignment) {
            $branchId = intval($assignment['branch_id']);
            $productId = intval($assignment['product_id']);
            
            if ($branchId > 0 && $productId > 0) {
                $insertStmt->execute([$productId, $branchId]);
                $insertedCount++;
            }
        }
    }
    
    $pdo->commit();
    
    echo json_encode([
        'success' => true,
        'message' => 'All assignments saved successfully',
        'assignments_saved' => $insertedCount,
        'table_used' => $usePosBranchProduct ? 'pos_branch_product' : 'product_branch'
    ]);
    
} catch (Exception $e) {
    $pdo->rollBack();
    echo json_encode([
        'success' => false,
        'error' => $e->getMessage()
    ]);
}
?>
