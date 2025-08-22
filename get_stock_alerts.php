<?php
require_once 'db_connect.php';
require_once 'auth_function.php';

// Check if user is logged in
requireLogin();

header('Content-Type: application/json');

try {
    $user_type = $_SESSION['user_type'];
    $user_branch_id = $_SESSION['branch_id'] ?? null;
    
    // Build query based on user type
    if ($user_type === 'Admin') {
        // Admin sees alerts from all branches
        $alertQuery = "SELECT i.ingredient_id, i.ingredient_name, i.ingredient_quantity, 
                              COALESCE(i.minimum_stock, 0) as minimum_stock, 
                              c.category_name, b.branch_name, i.branch_id,
                              CASE 
                                  WHEN i.ingredient_quantity <= 0 THEN 'critical'
                                  WHEN i.ingredient_quantity <= i.minimum_stock THEN 'warning'
                                  ELSE 'normal'
                              END as alert_level
                       FROM ingredients i 
                       LEFT JOIN pos_category c ON i.category_id = c.category_id 
                       LEFT JOIN pos_branch b ON i.branch_id = b.branch_id
                       WHERE i.ingredient_status != 'archived' 
                       AND (i.ingredient_quantity <= 0 OR i.ingredient_quantity <= COALESCE(i.minimum_stock, 0))
                       AND COALESCE(i.minimum_stock, 0) > 0
                       ORDER BY 
                           CASE 
                               WHEN i.ingredient_quantity <= 0 THEN 1
                               WHEN i.ingredient_quantity <= i.minimum_stock THEN 2
                               ELSE 3
                           END, 
                           i.ingredient_quantity ASC";
    } else if ($user_type === 'Stockman' && $user_branch_id) {
        // Stockman sees alerts only from their branch
        $alertQuery = "SELECT i.ingredient_id, i.ingredient_name, i.ingredient_quantity, 
                              COALESCE(i.minimum_stock, 0) as minimum_stock, 
                              c.category_name, b.branch_name, i.branch_id,
                              CASE 
                                  WHEN i.ingredient_quantity <= 0 THEN 'critical'
                                  WHEN i.ingredient_quantity <= i.minimum_stock THEN 'warning'
                                  ELSE 'normal'
                              END as alert_level
                       FROM ingredients i 
                       LEFT JOIN pos_category c ON i.category_id = c.category_id 
                       LEFT JOIN pos_branch b ON i.branch_id = b.branch_id
                       WHERE i.ingredient_status != 'archived' 
                       AND i.branch_id = ?
                       AND (i.ingredient_quantity <= 0 OR i.ingredient_quantity <= COALESCE(i.minimum_stock, 0))
                       AND COALESCE(i.minimum_stock, 0) > 0
                       ORDER BY 
                           CASE 
                               WHEN i.ingredient_quantity <= 0 THEN 1
                               WHEN i.ingredient_quantity <= i.minimum_stock THEN 2
                               ELSE 3
                           END, 
                           i.ingredient_quantity ASC";
    } else {
        // Other user types get no alerts
        echo json_encode(['alerts' => [], 'count' => 0]);
        exit();
    }
    
    $stmt = $pdo->prepare($alertQuery);
    if ($user_type === 'Stockman' && $user_branch_id) {
        $stmt->execute([$user_branch_id]);
    } else {
        $stmt->execute();
    }
    
    $alerts = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    // Count alerts by severity
    $criticalCount = 0;
    $warningCount = 0;
    
    foreach ($alerts as $alert) {
        if ($alert['alert_level'] === 'critical') {
            $criticalCount++;
        } else if ($alert['alert_level'] === 'warning') {
            $warningCount++;
        }
    }
    
    $response = [
        'alerts' => $alerts,
        'count' => count($alerts),
        'critical_count' => $criticalCount,
        'warning_count' => $warningCount,
        'user_type' => $user_type,
        'branch_id' => $user_branch_id
    ];
    
    echo json_encode($response);
    
} catch (Exception $e) {
    echo json_encode(['error' => $e->getMessage()]);
}
?>