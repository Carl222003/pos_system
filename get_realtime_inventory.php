<?php
require_once 'db_connect.php';
require_once 'auth_function.php';

checkAdminLogin();

header('Content-Type: application/json');

try {
    $branch_id = isset($_GET['branch_id']) ? intval($_GET['branch_id']) : null;
    
    if ($branch_id) {
        // Check if ingredients table has branch_id column
        $columns_check = $pdo->query("SHOW COLUMNS FROM ingredients LIKE 'branch_id'");
        $has_branch_id = $columns_check->rowCount() > 0;
        
        if ($has_branch_id) {
            // Get inventory data for specific branch
            $inventory_query = "SELECT 
                                  COUNT(*) as total_items,
                                  COUNT(CASE WHEN ingredient_quantity <= minimum_stock THEN 1 END) as low_stock_items,
                                  COUNT(CASE WHEN ingredient_quantity <= 0 THEN 1 END) as out_of_stock_items,
                                  COUNT(CASE WHEN consume_before IS NOT NULL AND consume_before <= DATE_ADD(CURDATE(), INTERVAL 7 DAY) THEN 1 END) as expiring_items
                                FROM ingredients 
                                WHERE ingredient_status != 'archived' 
                                AND branch_id = ?";
            $stmt = $pdo->prepare($inventory_query);
            $stmt->execute([$branch_id]);
        } else {
            // Fallback: get all inventory data (no branch filtering)
            $inventory_query = "SELECT 
                                  COUNT(*) as total_items,
                                  COUNT(CASE WHEN ingredient_quantity <= minimum_stock THEN 1 END) as low_stock_items,
                                  COUNT(CASE WHEN ingredient_quantity <= 0 THEN 1 END) as out_of_stock_items,
                                  COUNT(CASE WHEN consume_before IS NOT NULL AND consume_before <= DATE_ADD(CURDATE(), INTERVAL 7 DAY) THEN 1 END) as expiring_items
                                FROM ingredients 
                                WHERE ingredient_status != 'archived'";
            $stmt = $pdo->query($inventory_query);
        }
        
        $inventory_summary = $stmt->fetch(PDO::FETCH_ASSOC);
        
        // Get detailed low stock items
        if ($has_branch_id) {
            $low_stock_query = "SELECT ingredient_name, ingredient_quantity, ingredient_unit, minimum_stock
                               FROM ingredients 
                               WHERE ingredient_quantity <= minimum_stock 
                               AND ingredient_status != 'archived' 
                               AND branch_id = ?
                               ORDER BY ingredient_quantity ASC 
                               LIMIT 5";
            $stmt = $pdo->prepare($low_stock_query);
            $stmt->execute([$branch_id]);
        } else {
            $low_stock_query = "SELECT ingredient_name, ingredient_quantity, ingredient_unit, minimum_stock
                               FROM ingredients 
                               WHERE ingredient_quantity <= minimum_stock 
                               AND ingredient_status != 'archived'
                               ORDER BY ingredient_quantity ASC 
                               LIMIT 5";
            $stmt = $pdo->query($low_stock_query);
        }
        $low_stock_items = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        // Get expiring items
        if ($has_branch_id) {
            $expiring_query = "SELECT ingredient_name, ingredient_quantity, ingredient_unit, consume_before
                              FROM ingredients 
                              WHERE consume_before IS NOT NULL 
                              AND consume_before <= DATE_ADD(CURDATE(), INTERVAL 7 DAY)
                              AND ingredient_status != 'archived' 
                              AND branch_id = ?
                              ORDER BY consume_before ASC 
                              LIMIT 5";
            $stmt = $pdo->prepare($expiring_query);
            $stmt->execute([$branch_id]);
        } else {
            $expiring_query = "SELECT ingredient_name, ingredient_quantity, ingredient_unit, consume_before
                              FROM ingredients 
                              WHERE consume_before IS NOT NULL 
                              AND consume_before <= DATE_ADD(CURDATE(), INTERVAL 7 DAY)
                              AND ingredient_status != 'archived'
                              ORDER BY consume_before ASC 
                              LIMIT 5";
            $stmt = $pdo->query($expiring_query);
        }
        $expiring_items = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        // Calculate inventory health score (0-100)
        $total = intval($inventory_summary['total_items']);
        $issues = intval($inventory_summary['low_stock_items']) + intval($inventory_summary['out_of_stock_items']);
        $health_score = $total > 0 ? max(0, 100 - (($issues / $total) * 100)) : 100;
        
        echo json_encode([
            'success' => true,
            'branch_id' => $branch_id,
            'summary' => $inventory_summary,
            'low_stock_items' => $low_stock_items,
            'expiring_items' => $expiring_items,
            'health_score' => round($health_score, 1),
            'timestamp' => date('Y-m-d H:i:s')
        ]);
        
    } else {
        // Get inventory summary for all branches
        $branches_query = "SELECT b.branch_id, b.branch_name,
                             COUNT(i.ingredient_id) as total_items,
                             COUNT(CASE WHEN i.ingredient_quantity <= i.minimum_stock THEN 1 END) as low_stock_items,
                             COUNT(CASE WHEN i.ingredient_quantity <= 0 THEN 1 END) as out_of_stock_items,
                             COUNT(CASE WHEN i.consume_before IS NOT NULL AND i.consume_before <= DATE_ADD(CURDATE(), INTERVAL 7 DAY) THEN 1 END) as expiring_items
                           FROM pos_branch b
                           LEFT JOIN ingredients i ON b.branch_id = i.branch_id AND i.ingredient_status != 'archived'
                           WHERE b.status = 'Active'
                           GROUP BY b.branch_id, b.branch_name
                           ORDER BY b.branch_name";
        
        $stmt = $pdo->query($branches_query);
        $branches_inventory = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        // Calculate health scores for each branch
        foreach ($branches_inventory as &$branch) {
            $total = intval($branch['total_items']);
            $issues = intval($branch['low_stock_items']) + intval($branch['out_of_stock_items']);
            $branch['health_score'] = $total > 0 ? max(0, 100 - (($issues / $total) * 100)) : 100;
            $branch['health_score'] = round($branch['health_score'], 1);
        }
        
        echo json_encode([
            'success' => true,
            'branches_inventory' => $branches_inventory,
            'timestamp' => date('Y-m-d H:i:s')
        ]);
    }
    
} catch (Exception $e) {
    echo json_encode([
        'success' => false,
        'error' => $e->getMessage()
    ]);
}
?>
