<?php
require_once 'db_connect.php';
require_once 'auth_function.php';

checkAdminLogin();
header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(['success' => false, 'error' => 'POST method required']);
    exit;
}

if (!isset($_POST['product_id']) || !isset($_POST['branch_id'])) {
    echo json_encode(['success' => false, 'error' => 'Missing product_id or branch_id']);
    exit;
}

$productId = intval($_POST['product_id']);
$branchId = intval($_POST['branch_id']);
$quantity = isset($_POST['quantity']) ? intval($_POST['quantity']) : 10;

try {
    // Check if connection already exists
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
    
    if ($usePosBranchProduct) {
        // Use pos_branch_product table
        $stmt = $pdo->prepare("
            INSERT INTO pos_branch_product (branch_id, product_id, quantity) 
            VALUES (?, ?, ?) 
            ON DUPLICATE KEY UPDATE quantity = ?
        ");
        $stmt->execute([$branchId, $productId, $quantity, $quantity]);
    } else {
        // Use product_branch table
        $stmt = $pdo->prepare("
            INSERT INTO product_branch (product_id, branch_id) 
            VALUES (?, ?) 
            ON DUPLICATE KEY UPDATE product_id = product_id
        ");
        $stmt->execute([$productId, $branchId]);
    }
    
    echo json_encode([
        'success' => true,
        'message' => 'Product connected to branch successfully',
        'product_id' => $productId,
        'branch_id' => $branchId,
        'quantity' => $quantity
    ]);
    
} catch (Exception $e) {
    echo json_encode([
        'success' => false,
        'error' => $e->getMessage()
    ]);
}
?>
