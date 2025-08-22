<?php
require_once 'db_connect.php';
require_once 'auth_function.php';

checkAdminLogin();
header('Content-Type: application/json');

try {
    // Check which table to use
    $tables = $pdo->query("SHOW TABLES")->fetchAll(PDO::FETCH_COLUMN);
    $usePosBranchProduct = in_array('pos_branch_product', $tables);
    $useProductBranch = in_array('product_branch', $tables);
    
    if (!$usePosBranchProduct && !$useProductBranch) {
        echo json_encode([
            'success' => true,
            'connections' => [],
            'message' => 'No connection table found'
        ]);
        exit;
    }
    
    // Get all products with their branch connections
    if ($usePosBranchProduct) {
        $query = "
            SELECT 
                p.product_id,
                p.product_name,
                p.product_price,
                p.product_image,
                c.category_name,
                GROUP_CONCAT(
                    JSON_OBJECT('branch_id', b.branch_id, 'branch_name', b.branch_name)
                    SEPARATOR ','
                ) as branches_json
            FROM pos_product p
            LEFT JOIN pos_category c ON p.category_id = c.category_id
            LEFT JOIN pos_branch_product bp ON p.product_id = bp.product_id
            LEFT JOIN pos_branch b ON bp.branch_id = b.branch_id AND b.status = 'Active'
            WHERE p.product_status IS NOT NULL AND p.product_status != ''
            GROUP BY p.product_id, p.product_name, p.product_price, p.product_image, c.category_name
            ORDER BY p.product_name
        ";
    } else {
        $query = "
            SELECT 
                p.product_id,
                p.product_name,
                p.product_price,
                p.product_image,
                c.category_name,
                GROUP_CONCAT(
                    JSON_OBJECT('branch_id', b.branch_id, 'branch_name', b.branch_name)
                    SEPARATOR ','
                ) as branches_json
            FROM pos_product p
            LEFT JOIN pos_category c ON p.category_id = c.category_id
            LEFT JOIN product_branch pb ON p.product_id = pb.product_id
            LEFT JOIN pos_branch b ON pb.branch_id = b.branch_id AND b.status = 'Active'
            WHERE p.product_status IS NOT NULL AND p.product_status != ''
            GROUP BY p.product_id, p.product_name, p.product_price, p.product_image, c.category_name
            ORDER BY p.product_name
        ";
    }
    
    $stmt = $pdo->query($query);
    $results = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    // Process results
    $connections = [];
    foreach ($results as $row) {
        $branches = [];
        if ($row['branches_json']) {
            // Parse the JSON-like string
            $branchesData = explode(',', $row['branches_json']);
            foreach ($branchesData as $branchData) {
                // Clean up the JSON string and decode
                $branchData = str_replace('"', '"', $branchData);
                $branchData = str_replace('"', '"', $branchData);
                $branch = json_decode($branchData, true);
                if ($branch && isset($branch['branch_id']) && isset($branch['branch_name'])) {
                    $branches[] = $branch;
                }
            }
        }
        
        $connections[] = [
            'product_id' => $row['product_id'],
            'product_name' => $row['product_name'],
            'product_price' => $row['product_price'],
            'product_image' => $row['product_image'],
            'category_name' => $row['category_name'],
            'branches' => $branches
        ];
    }
    
    echo json_encode([
        'success' => true,
        'connections' => $connections,
        'table_used' => $usePosBranchProduct ? 'pos_branch_product' : 'product_branch'
    ]);
    
} catch (Exception $e) {
    echo json_encode([
        'success' => false,
        'error' => $e->getMessage(),
        'connections' => []
    ]);
}
?>
