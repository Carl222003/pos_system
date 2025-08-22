<?php
require_once 'db_connect.php';
require_once 'auth_function.php';

// Check if user is logged in and is a stockman
if (!isset($_SESSION['user_logged_in']) || $_SESSION['user_logged_in'] !== true || $_SESSION['user_type'] !== 'Stockman') {
    header('Content-Type: application/json');
    echo json_encode(['success' => false, 'error' => 'Unauthorized access']);
    exit();
}

header('Content-Type: application/json');

try {
    // Get filter parameters
    $search = $_GET['search'] ?? '';
    $category = $_GET['category'] ?? '';
    $status = $_GET['status'] ?? '';

    // Get the stockman's branch ID
    $user_id = $_SESSION['user_id'];
    $branch_id = $_SESSION['branch_id'] ?? null;

    // If branch_id is not in session, try to fetch from user record
    if (!$branch_id) {
        $stmt = $pdo->prepare('SELECT branch_id FROM pos_user WHERE user_id = ?');
        $stmt->execute([$user_id]);
        $branch_id = $stmt->fetchColumn();
    }

    if (!$branch_id) {
        echo json_encode([
            'success' => false,
            'error' => 'Branch not found for this user',
            'products' => []
        ]);
        exit();
    }

    // First, check what columns exist in the pos_product table
    $stmt = $pdo->query("DESCRIBE pos_product");
    $columns = $stmt->fetchAll(PDO::FETCH_COLUMN);
    
    // Determine the correct status column name
    $statusColumn = 'product_status';
    if (in_array('status', $columns) && !in_array('product_status', $columns)) {
        $statusColumn = 'status';
    }

    // Check which product-branch table exists and has data
    $branchProductTable = null;
    $quantityColumn = null;
    
    // Check pos_branch_product table
    $tables = $pdo->query("SHOW TABLES LIKE 'pos_branch_product'")->fetchAll();
    if (!empty($tables)) {
        $count = $pdo->query("SELECT COUNT(*) FROM pos_branch_product")->fetchColumn();
        if ($count > 0) {
            $branchProductTable = 'pos_branch_product';
            $quantityColumn = 'quantity';
        }
    }
    
    // Check product_branch table if pos_branch_product is empty or doesn't exist
    if (!$branchProductTable) {
        $tables = $pdo->query("SHOW TABLES LIKE 'product_branch'")->fetchAll();
        if (!empty($tables)) {
            $count = $pdo->query("SELECT COUNT(*) FROM product_branch")->fetchColumn();
            if ($count > 0) {
                $branchProductTable = 'product_branch';
                $quantityColumn = 'NULL as quantity'; // product_branch doesn't have quantity
            }
        }
    }
    
    if (!$branchProductTable) {
        // Fallback: Show all products if no branch assignments exist
        error_log("No branch-product assignments found, showing all products as fallback");
        
        $baseQuery = "
            SELECT 
                p.product_id,
                p.product_name,
                p.product_price,
                p.description,
                p.ingredients,
                p.$statusColumn as product_status,
                p.product_image,
                p.created_at,
                p.updated_at,
                COALESCE(c.category_name, 'Uncategorized') as category_name,
                c.category_id,
                0 as branch_quantity,
                b.branch_name
            FROM pos_product p
            LEFT JOIN pos_category c ON p.category_id = c.category_id
            LEFT JOIN pos_branch b ON b.branch_id = :branch_id
            WHERE 1=1
        ";
        
        $params = [':branch_id' => $branch_id];
        $useFallback = true;
    } else {
        // Base query to get products assigned to this stockman's branch
        $baseQuery = "
            SELECT 
                p.product_id,
                p.product_name,
                p.product_price,
                p.description,
                p.ingredients,
                p.$statusColumn as product_status,
                p.product_image,
                p.created_at,
                p.updated_at,
                COALESCE(c.category_name, 'Uncategorized') as category_name,
                c.category_id,
                $quantityColumn as branch_quantity,
                b.branch_name
            FROM pos_product p
            LEFT JOIN pos_category c ON p.category_id = c.category_id
            INNER JOIN $branchProductTable bp ON p.product_id = bp.product_id
            LEFT JOIN pos_branch b ON bp.branch_id = b.branch_id
            WHERE bp.branch_id = :branch_id
        ";
        
        $params = [':branch_id' => $branch_id];
        $useFallback = false;
    }

    // Build WHERE conditions
    $conditions = [];
    // $params already set above based on fallback or normal mode

    // Always show both Available and Unavailable products for stockman visibility
    $conditions[] = "p.$statusColumn IN ('Available', 'Unavailable')";

    // Apply search filter
    if (!empty($search)) {
        $conditions[] = "(
            p.product_name LIKE :search 
            OR p.description LIKE :search 
            OR p.ingredients LIKE :search
            OR c.category_name LIKE :search
        )";
        $params[':search'] = "%{$search}%";
    }

    // Apply category filter
    if (!empty($category)) {
        $conditions[] = "p.category_id = :category";
        $params[':category'] = $category;
    }

    // Apply status filter
    if (!empty($status)) {
        $conditions[] = "p.$statusColumn = :status";
        $params[':status'] = $status;
    }

    // Combine query with conditions (baseQuery already has WHERE clause)
    $query = $baseQuery;
    if (!empty($conditions)) {
        $query .= " AND " . implode(" AND ", $conditions);
    }

    // Add ordering
    $query .= " ORDER BY c.category_name ASC, p.product_name ASC";

    // Execute query
    $stmt = $pdo->prepare($query);
    $stmt->execute($params);
    $products = $stmt->fetchAll(PDO::FETCH_ASSOC);

    // Format products data
    foreach ($products as &$product) {
        // Format price as number
        $product['product_price'] = number_format((float)$product['product_price'], 2, '.', '');
        
        // Ensure image path is correct
        if ($product['product_image']) {
            // Remove any leading path if it exists
            $product['product_image'] = basename($product['product_image']);
        }
        
        // Format dates
        if ($product['created_at']) {
            $product['created_at'] = date('Y-m-d H:i:s', strtotime($product['created_at']));
        }
        if ($product['updated_at']) {
            $product['updated_at'] = date('Y-m-d H:i:s', strtotime($product['updated_at']));
        }
    }

    echo json_encode([
        'success' => true,
        'products' => $products,
        'total_count' => count($products),
        'filters_applied' => [
            'search' => $search,
            'category' => $category,
            'status' => $status
        ],
        'branch_info' => [
            'branch_id' => $branch_id,
            'branch_name' => !empty($products) ? $products[0]['branch_name'] : 'Unknown'
        ],
        'debug_info' => [
            'status_column_used' => $statusColumn,
            'branch_product_table_used' => $branchProductTable ?? 'FALLBACK_MODE',
            'quantity_column' => $quantityColumn ?? 'NULL',
            'using_fallback' => $useFallback ?? false,
            'query_executed' => $query,
            'conditions' => $conditions,
            'params' => $params
        ]
    ]);

} catch (PDOException $e) {
    error_log("Database error in get_stockman_products.php: " . $e->getMessage());
    echo json_encode([
        'success' => false,
        'error' => 'Database error occurred',
        'products' => []
    ]);
} catch (Exception $e) {
    error_log("General error in get_stockman_products.php: " . $e->getMessage());
    echo json_encode([
        'success' => false,
        'error' => 'An error occurred while fetching products',
        'products' => []
    ]);
}
?>
