<?php
require_once 'db_connect.php';
require_once 'auth_function.php';

// Check if user is logged in
if (!isset($_SESSION['user_logged_in']) || $_SESSION['user_logged_in'] !== true) {
    header('Content-Type: application/json');
    echo json_encode(['success' => false, 'error' => 'Unauthorized access']);
    exit();
}

header('Content-Type: application/json');

try {
    // Get product ID from request
    $product_id = $_GET['id'] ?? null;

    if (empty($product_id)) {
        echo json_encode(['success' => false, 'error' => 'Product ID is required']);
        exit();
    }

    // First, check what columns exist in the pos_product table
    $stmt = $pdo->query("DESCRIBE pos_product");
    $columns = $stmt->fetchAll(PDO::FETCH_COLUMN);
    
    // Build dynamic query based on available columns
    $selectColumns = [
        'p.product_id',
        'p.product_name'
    ];
    
    // Add columns if they exist
    if (in_array('product_price', $columns)) {
        $selectColumns[] = 'p.product_price';
    } elseif (in_array('price', $columns)) {
        $selectColumns[] = 'p.price as product_price';
    }
    
    if (in_array('description', $columns)) {
        $selectColumns[] = 'p.description';
    }
    
    if (in_array('ingredients', $columns)) {
        $selectColumns[] = 'p.ingredients';
    }
    
    if (in_array('product_status', $columns)) {
        $selectColumns[] = 'p.product_status';
    } elseif (in_array('status', $columns)) {
        $selectColumns[] = 'p.status as product_status';
    }
    
    if (in_array('product_image', $columns)) {
        $selectColumns[] = 'p.product_image';
    }
    
    if (in_array('created_at', $columns)) {
        $selectColumns[] = 'p.created_at';
    }
    
    if (in_array('updated_at', $columns)) {
        $selectColumns[] = 'p.updated_at';
    }
    
    // Add category information
    $selectColumns[] = 'COALESCE(c.category_name, \'Uncategorized\') as category_name';
    $selectColumns[] = 'c.category_id';
    
    $selectClause = implode(', ', $selectColumns);
    
    // Query to get product details with category information
    $query = "
        SELECT $selectClause
        FROM pos_product p
        LEFT JOIN pos_category c ON p.category_id = c.category_id
        WHERE p.product_id = :product_id
    ";

    $stmt = $pdo->prepare($query);
    $stmt->execute([':product_id' => $product_id]);
    $product = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$product) {
        echo json_encode(['success' => false, 'error' => 'Product not found']);
        exit();
    }

    // Format product data
    if (isset($product['product_price'])) {
        $product['product_price'] = number_format((float)$product['product_price'], 2, '.', '');
    }
    
    // Ensure image path is correct
    if (isset($product['product_image']) && $product['product_image']) {
        $product['product_image'] = basename($product['product_image']);
    }
    
    // Format dates
    if (isset($product['created_at']) && $product['created_at']) {
        $product['created_at'] = date('Y-m-d H:i:s', strtotime($product['created_at']));
    }
    if (isset($product['updated_at']) && $product['updated_at']) {
        $product['updated_at'] = date('Y-m-d H:i:s', strtotime($product['updated_at']));
    }

    // Set default values for missing fields
    if (!isset($product['description'])) {
        $product['description'] = '';
    }
    if (!isset($product['ingredients'])) {
        $product['ingredients'] = '';
    }
    if (!isset($product['product_status'])) {
        $product['product_status'] = 'Available';
    }
    if (!isset($product['product_image'])) {
        $product['product_image'] = '';
    }

    // For stockman users, also get branch availability information
    if (isset($_SESSION['user_type']) && $_SESSION['user_type'] === 'Stockman') {
        $user_id = $_SESSION['user_id'];
        $branch_id = $_SESSION['branch_id'] ?? null;

        // If branch_id is not in session, try to fetch from user record
        if (!$branch_id) {
            $stmt = $pdo->prepare('SELECT branch_id FROM pos_user WHERE user_id = ?');
            $stmt->execute([$user_id]);
            $branch_id = $stmt->fetchColumn();
        }

        if ($branch_id) {
            // Check if product is available in stockman's branch
            // Try different table names for branch products
            $branchTables = ['pos_branch_product', 'product_branch', 'branch_product'];
            $branch_info = null;
            
            foreach ($branchTables as $tableName) {
                try {
                    $branch_query = "
                        SELECT 
                            bp.quantity,
                            b.branch_name
                        FROM $tableName bp
                        JOIN pos_branch b ON bp.branch_id = b.branch_id
                        WHERE bp.product_id = :product_id AND bp.branch_id = :branch_id
                    ";
                    
                    $stmt = $pdo->prepare($branch_query);
                    $stmt->execute([
                        ':product_id' => $product_id,
                        ':branch_id' => $branch_id
                    ]);
                    $branch_info = $stmt->fetch(PDO::FETCH_ASSOC);
                    
                    if ($branch_info) {
                        break; // Found data, stop trying other tables
                    }
                } catch (PDOException $e) {
                    // Table doesn't exist, try next one
                    continue;
                }
            }

            if ($branch_info) {
                $product['branch_quantity'] = $branch_info['quantity'];
                $product['branch_name'] = $branch_info['branch_name'];
            } else {
                $product['branch_quantity'] = 0;
                // Get branch name
                try {
                    $stmt = $pdo->prepare('SELECT branch_name FROM pos_branch WHERE branch_id = ?');
                    $stmt->execute([$branch_id]);
                    $product['branch_name'] = $stmt->fetchColumn() ?: 'Unknown Branch';
                } catch (PDOException $e) {
                    $product['branch_name'] = 'Unknown Branch';
                }
            }
        }
    }

    echo json_encode([
        'success' => true,
        'product' => $product
    ]);

} catch (PDOException $e) {
    error_log("Database error in get_product_details.php: " . $e->getMessage());
    echo json_encode([
        'success' => false,
        'error' => 'Database error: ' . $e->getMessage(),
        'debug_info' => [
            'product_id' => $product_id ?? 'not set',
            'query_attempted' => isset($query) ? $query : 'query not built'
        ]
    ]);
} catch (Exception $e) {
    error_log("General error in get_product_details.php: " . $e->getMessage());
    echo json_encode([
        'success' => false,
        'error' => 'An error occurred: ' . $e->getMessage()
    ]);
}
?>
