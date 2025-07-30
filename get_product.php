<?php
require_once 'db_connect.php';
require_once 'auth_function.php';

checkAdminLogin();

header('Content-Type: application/json');

try {
    if (!isset($_GET['id'])) {
        throw new Exception('Product ID is required');
    }

    $productId = intval($_GET['id']);

    // Get product details with category name
    $stmt = $pdo->prepare("
        SELECT 
            p.*,
            c.category_name
        FROM pos_product p
        LEFT JOIN pos_category c ON p.category_id = c.category_id
        WHERE p.product_id = ?
    ");
    
    $stmt->execute([$productId]);
    $product = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$product) {
        throw new Exception('Product not found');
    }

    // Format image path
    if (!empty($product['product_image'])) {
        $product['product_image'] = $product['product_image'];
    }

    // Get branch assignments for this product
    $branch_stmt = $pdo->prepare("
        SELECT branch_id 
        FROM product_branch 
        WHERE product_id = ?
    ");
    $branch_stmt->execute([$productId]);
    $branches = $branch_stmt->fetchAll(PDO::FETCH_COLUMN);
    
    $product['branches'] = $branches;

    echo json_encode([
        'success' => true,
        'data' => $product
    ]);

} catch (Exception $e) {
    http_response_code(400);
    echo json_encode([
        'success' => false,
        'message' => $e->getMessage()
    ]);
} 