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
            'success' => false,
            'error' => 'No branch-product assignment tables found',
            'assignments' => []
        ]);
        exit;
    }
    
    // Determine table and query structure
    if ($usePosBranchProduct) {
        $assignmentQuery = "
            SELECT 
                p.product_id,
                p.product_name,
                p.product_price,
                p.product_status,
                p.product_image,
                p.created_at,
                COALESCE(c.category_name, 'Uncategorized') as category_name,
                COALESCE(
                    GROUP_CONCAT(
                        DISTINCT CONCAT('{\"branch_id\":', b.branch_id, ',\"branch_name\":\"', b.branch_name, '\"}')
                        ORDER BY b.branch_name
                        SEPARATOR ','
                    ), ''
                ) as assigned_branches_json
            FROM pos_product p
            LEFT JOIN pos_category c ON p.category_id = c.category_id
            LEFT JOIN pos_branch_product bp ON p.product_id = bp.product_id
            LEFT JOIN pos_branch b ON bp.branch_id = b.branch_id AND b.status = 'Active'
            WHERE p.product_status IS NOT NULL AND p.product_status != ''
            GROUP BY p.product_id, p.product_name, p.product_price, p.product_status, 
                     p.product_image, p.created_at, c.category_name
            ORDER BY p.product_name
        ";
    } else {
        $assignmentQuery = "
            SELECT 
                p.product_id,
                p.product_name,
                p.product_price,
                p.product_status,
                p.product_image,
                p.created_at,
                COALESCE(c.category_name, 'Uncategorized') as category_name,
                COALESCE(
                    GROUP_CONCAT(
                        DISTINCT CONCAT('{\"branch_id\":', b.branch_id, ',\"branch_name\":\"', b.branch_name, '\"}')
                        ORDER BY b.branch_name
                        SEPARATOR ','
                    ), ''
                ) as assigned_branches_json
            FROM pos_product p
            LEFT JOIN pos_category c ON p.category_id = c.category_id
            LEFT JOIN product_branch pb ON p.product_id = pb.product_id
            LEFT JOIN pos_branch b ON bp.branch_id = b.branch_id AND b.status = 'Active'
            WHERE p.product_status IS NOT NULL AND p.product_status != ''
            GROUP BY p.product_id, p.product_name, p.product_price, p.product_status, 
                     p.product_image, p.created_at, c.category_name
            ORDER BY p.product_name
        ";
    }
    
    $stmt = $pdo->query($assignmentQuery);
    $results = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    // Process results
    $assignments = [];
    foreach ($results as $row) {
        $assignedBranches = [];
        if ($row['assigned_branches_json'] && !empty(trim($row['assigned_branches_json']))) {
            $branchesJson = '[' . $row['assigned_branches_json'] . ']';
            $decodedBranches = json_decode($branchesJson, true);
            
            if (is_array($decodedBranches)) {
                $assignedBranches = $decodedBranches;
            } else {
                // Fallback: try to parse individual branch entries
                $branchEntries = explode(',', $row['assigned_branches_json']);
                foreach ($branchEntries as $entry) {
                    $entry = trim($entry);
                    if (!empty($entry)) {
                        $decodedEntry = json_decode($entry, true);
                        if (is_array($decodedEntry) && isset($decodedEntry['branch_id']) && isset($decodedEntry['branch_name'])) {
                            $assignedBranches[] = $decodedEntry;
                        }
                    }
                }
            }
        }
        
        $assignments[] = [
            'product_id' => $row['product_id'],
            'product_name' => $row['product_name'],
            'product_price' => $row['product_price'],
            'product_status' => $row['product_status'],
            'product_image' => $row['product_image'],
            'category_name' => $row['category_name'],
            'created_at' => $row['created_at'],
            'assigned_branches' => $assignedBranches
        ];
    }
    
    // Debug information
    $debugInfo = [];
    if (isset($_GET['debug']) && $_GET['debug'] === '1') {
        $debugInfo = [
            'table_used' => $usePosBranchProduct ? 'pos_branch_product' : 'product_branch',
            'raw_results_sample' => array_slice($results, 0, 2), // Show first 2 results for debugging
            'processed_assignments_sample' => array_slice($assignments, 0, 2), // Show first 2 processed results
            'total_products' => count($assignments),
            'products_with_branches' => count(array_filter($assignments, function($a) { return !empty($a['assigned_branches']); }))
        ];
    }
    
    echo json_encode([
        'success' => true,
        'assignments' => $assignments,
        'table_used' => $usePosBranchProduct ? 'pos_branch_product' : 'product_branch',
        'debug' => $debugInfo
    ]);
    
} catch (Exception $e) {
    error_log("Error in get_product_assignments.php: " . $e->getMessage());
    echo json_encode([
        'success' => false,
        'error' => $e->getMessage(),
        'assignments' => []
    ]);
}
?>