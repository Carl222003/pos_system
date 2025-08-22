<?php
require_once 'db_connect.php';

header('Content-Type: application/json');

try {
    // Simple inventory check - get all ingredients and calculate stats
    $query = "SELECT 
                ingredient_id,
                ingredient_name,
                ingredient_quantity,
                minimum_stock,
                consume_before,
                ingredient_status
              FROM ingredients 
              WHERE ingredient_status != 'archived'
              ORDER BY ingredient_name";
    
    $stmt = $pdo->query($query);
    $ingredients = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    $stats = [
        'total_items' => 0,
        'low_stock_items' => 0,
        'out_of_stock_items' => 0,
        'expiring_items' => 0,
        'low_stock_details' => [],
        'expiring_details' => []
    ];
    
    $today = time();
    $week_from_now = $today + (7 * 24 * 60 * 60);
    
    foreach ($ingredients as $ingredient) {
        $stats['total_items']++;
        
        $quantity = floatval($ingredient['ingredient_quantity']);
        $min_stock = floatval($ingredient['minimum_stock']);
        
        // Check low stock
        if ($quantity <= $min_stock) {
            $stats['low_stock_items']++;
            if (count($stats['low_stock_details']) < 5) {
                $stats['low_stock_details'][] = [
                    'name' => $ingredient['ingredient_name'],
                    'quantity' => $quantity,
                    'minimum' => $min_stock
                ];
            }
        }
        
        // Check out of stock
        if ($quantity <= 0) {
            $stats['out_of_stock_items']++;
        }
        
        // Check expiring
        if ($ingredient['consume_before']) {
            $expire_time = strtotime($ingredient['consume_before']);
            if ($expire_time && $expire_time <= $week_from_now) {
                $stats['expiring_items']++;
                if (count($stats['expiring_details']) < 5) {
                    $stats['expiring_details'][] = [
                        'name' => $ingredient['ingredient_name'],
                        'expire_date' => $ingredient['consume_before'],
                        'days_left' => ceil(($expire_time - $today) / (24 * 60 * 60))
                    ];
                }
            }
        }
    }
    
    echo json_encode([
        'success' => true,
        'stats' => $stats,
        'timestamp' => date('Y-m-d H:i:s')
    ]);
    
} catch (Exception $e) {
    echo json_encode([
        'success' => false,
        'error' => $e->getMessage()
    ]);
}
?>
