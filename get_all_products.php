<?php
require_once 'db_connect.php';
require_once 'auth_function.php';

checkAdminLogin();
header('Content-Type: application/json');

try {
    $stmt = $pdo->query("
        SELECT 
            p.product_id,
            p.product_name,
            p.product_price,
            p.product_status,
            p.product_image,
            c.category_name
        FROM pos_product p
        LEFT JOIN pos_category c ON p.category_id = c.category_id
        WHERE p.product_status IS NOT NULL AND p.product_status != ''
        ORDER BY p.product_name
    ");
    
    $products = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    echo json_encode([
        'success' => true,
        'products' => $products
    ]);
    
} catch (Exception $e) {
    echo json_encode([
        'success' => false,
        'error' => $e->getMessage(),
        'products' => []
    ]);
}
?>
