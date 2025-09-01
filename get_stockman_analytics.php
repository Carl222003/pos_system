<?php
require_once 'db_connect.php';
require_once 'auth_function.php';

// Check if user is logged in and is a stockman
if (!isset($_SESSION['user_logged_in']) || $_SESSION['user_logged_in'] !== true) {
    http_response_code(403);
    echo json_encode(['error' => 'User not logged in']);
    exit();
}

if ($_SESSION['user_type'] !== 'Stockman') {
    http_response_code(403);
    echo json_encode(['error' => 'Access denied - Stockman only']);
    exit();
}

$user_id = $_SESSION['user_id'];
$branch_id = $_SESSION['branch_id'] ?? null;

// If branch_id is not in session, try to fetch from user record
if (!$branch_id) {
    $stmt = $pdo->prepare('SELECT branch_id FROM pos_user WHERE user_id = ?');
    $stmt->execute([$user_id]);
    $branch_id = $stmt->fetchColumn();
}

// If still no branch_id, try to get the first available branch
if (!$branch_id) {
    $stmt = $pdo->prepare('SELECT branch_id FROM pos_branch LIMIT 1');
    $stmt->execute();
    $branch_id = $stmt->fetchColumn();
    
    if ($branch_id) {
        // Update the user with this branch
        $stmt = $pdo->prepare('UPDATE pos_user SET branch_id = ? WHERE user_id = ?');
        $stmt->execute([$branch_id, $user_id]);
    }
}

// Debug logging (remove in production)
error_log("Stockman Analytics - User ID: $user_id, Branch ID: $branch_id");
error_log("Session data: " . print_r($_SESSION, true));

// Additional debugging
if (!$branch_id) {
    error_log("No branch_id found for user $user_id");
} else {
    error_log("User $user_id assigned to branch $branch_id");
}

if (!$branch_id) {
    echo json_encode([
        'error' => 'No branch assigned',
        'branch_id' => null,
        'branch_name' => 'No Branch',
        'total_items' => 0,
        'available_ingredients' => 0,
        'low_stock_items' => 0,
        'stock_movements' => 0,
        'expiring_items' => 0,
        'stock_turnover' => 0,
        'adequate_stock' => 0,
        'low_stock' => 0,
        'out_of_stock' => 0,
        'weekly_movements' => [0, 0, 0, 0, 0, 0, 0],
        'fastest_moving' => 0,
        'slowest_moving' => 0,
        'expiring_soon' => 0,
        'total_stock_value' => '₱0',
        'turnover_rate' => 0,
        'average_age' => 0,
        'category_labels' => [],
        'category_data' => [],
        'high_turnover' => 0,
        'medium_turnover' => 0,
        'low_turnover' => 0,
        'critical_alerts' => [],
        'reorder_recommendations' => [],
        'category_cards' => [],
        'total_items_trend' => '+0% this week',
        'available_ingredients_trend' => '+0 this week',
        'low_stock_trend' => '+0 this week',
        'movements_trend' => '+0% this week',
        'expiring_trend' => 'No change',
        'turnover_trend' => '+0% this month'
    ]);
    exit();
}

try {
    // Get current date and week start
    $current_date = date('Y-m-d');
    $week_start = date('Y-m-d', strtotime('monday this week'));
    $month_start = date('Y-m-01');
    
    // Check if ingredients table exists
    $stmt = $pdo->query("SHOW TABLES LIKE 'ingredients'");
    if ($stmt->rowCount() == 0) {
        throw new Exception("Ingredients table does not exist");
    }
    
    // 1. Total Items Count (All ingredients in the system)
    $stmt = $pdo->prepare("SELECT COUNT(*) as total FROM ingredients WHERE branch_id = ?");
    $stmt->execute([$branch_id]);
    $total_items = $stmt->fetchColumn();
    
    // 2. Available Ingredients (Only ingredients with stock > 0)
    $stmt = $pdo->prepare("SELECT COUNT(*) as available FROM ingredients WHERE branch_id = ? AND ingredient_quantity > 0");
    $stmt->execute([$branch_id]);
    $available_ingredients = $stmt->fetchColumn();
    
    // 3. Low Stock Items (less than 10% of max quantity or less than 5 pieces)
    $stmt = $pdo->prepare("
        SELECT COUNT(*) as low_stock_count 
        FROM ingredients 
        WHERE branch_id = ? 
        AND (ingredient_quantity < 5 OR ingredient_quantity < (ingredient_max_quantity * 0.1))
        AND ingredient_quantity > 0
    ");
    $stmt->execute([$branch_id]);
    $low_stock_items = $stmt->fetchColumn();
    
    // 4. Out of Stock Items
    $stmt = $pdo->prepare("SELECT COUNT(*) as out_of_stock FROM ingredients WHERE branch_id = ? AND ingredient_quantity <= 0");
    $stmt->execute([$branch_id]);
    $out_of_stock = $stmt->fetchColumn();
    
    // 5. Adequate Stock Items
    $adequate_stock = $total_items - $low_stock_items - $out_of_stock;
    
    // 5. Stock Movements (this week)
    // Check if stock_movements table exists
    $stmt = $pdo->query("SHOW TABLES LIKE 'stock_movements'");
    if ($stmt->rowCount() > 0) {
    $stmt = $pdo->prepare("
        SELECT COUNT(*) as movements 
            FROM stock_movements 
            WHERE branch_id = ? 
            AND DATE(movement_date) >= ?
        ");
        $stmt->execute([$branch_id, $week_start]);
        $stock_movements = $stmt->fetchColumn();
    } else {
        $stock_movements = 0;
    }
    
    // 6. Weekly Movements (last 7 days)
    $weekly_movements = [];
    if ($stmt->query("SHOW TABLES LIKE 'stock_movements'")->rowCount() > 0) {
        for ($i = 6; $i >= 0; $i--) {
            $date = date('Y-m-d', strtotime("-$i days"));
            $stmt = $pdo->prepare("
                SELECT COUNT(*) as count 
                FROM stock_movements 
                WHERE branch_id = ? 
                AND DATE(movement_date) = ?
            ");
            $stmt->execute([$branch_id, $date]);
            $weekly_movements[] = (int)$stmt->fetchColumn();
        }
    } else {
        $weekly_movements = [0, 0, 0, 0, 0, 0, 0];
    }
    
    // 7. Expiring Items (within 30 days)
        $stmt = $pdo->prepare("
            SELECT COUNT(*) as expiring 
            FROM ingredients 
        WHERE branch_id = ? 
            AND expiry_date IS NOT NULL 
        AND expiry_date BETWEEN ? AND DATE_ADD(?, INTERVAL 30 DAY)
    ");
    $stmt->execute([$branch_id, $current_date, $current_date]);
    $expiring_items = $stmt->fetchColumn();
    
    // 8. Stock Turnover Rate (monthly)
    if ($stmt->query("SHOW TABLES LIKE 'stock_movements'")->rowCount() > 0) {
    $stmt = $pdo->prepare("
        SELECT 
                COALESCE(SUM(CASE WHEN movement_type = 'out' THEN quantity ELSE 0 END), 0) as total_out,
                COALESCE(SUM(CASE WHEN movement_type = 'in' THEN quantity ELSE 0 END), 0) as total_in
            FROM stock_movements 
            WHERE branch_id = ? 
            AND DATE(movement_date) >= ?
        ");
        $stmt->execute([$branch_id, $month_start]);
        $turnover_data = $stmt->fetch(PDO::FETCH_ASSOC);
        
        $total_out = $turnover_data['total_out'] ?? 0;
        $total_in = $turnover_data['total_in'] ?? 0;
        $stock_turnover = $total_items > 0 ? round(($total_out / $total_items) * 100, 1) : 0;
    } else {
        $total_out = 0;
        $total_in = 0;
        $stock_turnover = 0;
    }
    
    // 9. Category Performance
    $stmt = $pdo->query("SHOW TABLES LIKE 'pos_category'");
    if ($stmt->rowCount() > 0) {
    $stmt = $pdo->prepare("
            SELECT 
                c.category_name,
                COUNT(i.ingredient_id) as item_count
            FROM pos_category c
            LEFT JOIN ingredients i ON c.category_id = i.category_id AND i.branch_id = ?
            WHERE c.status = 'active'
            GROUP BY c.category_id, c.category_name
            ORDER BY item_count DESC
            LIMIT 5
        ");
        $stmt->execute([$branch_id]);
        $category_data = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        $category_labels = array_column($category_data, 'category_name');
        $category_counts = array_column($category_data, 'item_count');
    } else {
        $category_data = [];
        $category_labels = [];
        $category_counts = [];
    }
    
    // 10. Turnover Analysis
    $high_turnover = 0;
    $medium_turnover = 0;
    $low_turnover = 0;
    
    if ($total_items > 0) {
    $stmt = $pdo->prepare("
        SELECT 
                ingredient_id,
                ingredient_name,
                ingredient_quantity,
                ingredient_max_quantity
            FROM ingredients 
            WHERE branch_id = ?
        ");
        $stmt->execute([$branch_id]);
        $ingredients = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        foreach ($ingredients as $ingredient) {
            $max_qty = $ingredient['ingredient_max_quantity'] ?: 100;
            $current_qty = $ingredient['ingredient_quantity'];
            $turnover_rate = $max_qty > 0 ? ($current_qty / $max_qty) * 100 : 0;
            
            if ($turnover_rate >= 70) {
                $high_turnover++;
            } elseif ($turnover_rate >= 30) {
                $medium_turnover++;
    } else {
                $low_turnover++;
            }
        }
    }
    
    // 11. Critical Alerts
    $critical_alerts = [];
    
    // Low stock alerts
    if ($low_stock_items > 0) {
        $critical_alerts[] = [
            'severity' => 'warning',
            'title' => 'Low Stock Alert',
            'description' => "$low_stock_items items are running low on stock"
        ];
    }
    
    // Out of stock alerts
    if ($out_of_stock > 0) {
        $critical_alerts[] = [
            'severity' => 'danger',
            'title' => 'Out of Stock Alert',
            'description' => "$out_of_stock items are completely out of stock"
        ];
    }
    
    // Expiring items alerts
    if ($expiring_items > 0) {
        $critical_alerts[] = [
            'severity' => 'warning',
            'title' => 'Expiring Items Alert',
            'description' => "$expiring_items items will expire within 30 days"
        ];
    }
    
    // 12. Reorder Recommendations
    $stmt = $pdo->prepare("
        SELECT 
            ingredient_name,
            ingredient_quantity,
            ingredient_max_quantity,
            ingredient_unit
        FROM ingredients 
        WHERE branch_id = ? 
        AND (ingredient_quantity < 5 OR ingredient_quantity < (ingredient_max_quantity * 0.1))
        AND ingredient_quantity > 0
        ORDER BY ingredient_quantity ASC
        LIMIT 5
    ");
    $stmt->execute([$branch_id]);
    $low_stock_ingredients = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    $reorder_recommendations = [];
    foreach ($low_stock_ingredients as $ingredient) {
        $max_qty = $ingredient['ingredient_max_quantity'] ?: 100;
        $current_qty = $ingredient['ingredient_quantity'];
        $recommended_qty = max(10, $max_qty * 0.3);
        
        if ($current_qty < 5) {
            $priority = 'danger';
        } elseif ($current_qty < $max_qty * 0.2) {
            $priority = 'warning';
        } else {
            $priority = 'info';
        }
        
        $reorder_recommendations[] = [
            'item_name' => $ingredient['ingredient_name'],
            'current_stock' => $current_qty . ' ' . $ingredient['ingredient_unit'],
            'recommended_quantity' => round($recommended_qty) . ' ' . $ingredient['ingredient_unit'],
            'priority' => $priority,
            'reason' => 'Stock level below recommended threshold'
        ];
    }
    
    // 13. Category Cards
    if ($stmt->query("SHOW TABLES LIKE 'pos_category'")->rowCount() > 0) {
    $stmt = $pdo->prepare("
        SELECT 
                c.category_id,
                c.category_name,
            COUNT(i.ingredient_id) as total_items,
                SUM(CASE WHEN i.ingredient_quantity > 0 AND (i.ingredient_quantity >= 5 AND i.ingredient_quantity >= (COALESCE(i.ingredient_max_quantity, 100) * 0.1)) THEN 1 ELSE 0 END) as healthy,
                SUM(CASE WHEN i.ingredient_quantity > 0 AND (i.ingredient_quantity < 5 OR i.ingredient_quantity < (COALESCE(i.ingredient_max_quantity, 100) * 0.1)) THEN 1 ELSE 0 END) as low_stock,
                SUM(CASE WHEN i.ingredient_quantity <= 0 THEN 1 ELSE 0 END) as out_of_stock
            FROM pos_category c
            LEFT JOIN ingredients i ON c.category_id = i.category_id AND i.branch_id = ?
            WHERE c.status = 'active'
            GROUP BY c.category_id, c.category_name
        ORDER BY total_items DESC
            LIMIT 6
        ");
        $stmt->execute([$branch_id]);
        $category_cards = $stmt->fetchAll(PDO::FETCH_ASSOC);
    } else {
        $category_cards = [];
    }
    
    // 14. Stock Value Analytics
    $stmt = $pdo->prepare("
        SELECT 
            COALESCE(SUM(ingredient_quantity * COALESCE(ingredient_cost, 0)), 0) as total_value
        FROM ingredients 
        WHERE branch_id = ?
    ");
    $stmt->execute([$branch_id]);
    $total_stock_value = $stmt->fetchColumn();
    
    // 15. Average Stock Age (simplified - using last movement date)
    $stmt = $pdo->prepare("
        SELECT 
            COALESCE(AVG(DATEDIFF(?, COALESCE(last_movement_date, created_date))), 0) as avg_age
        FROM ingredients 
        WHERE branch_id = ?
    ");
    $stmt->execute([$current_date, $branch_id]);
    $average_age = round($stmt->fetchColumn());
    
    // 16. Fastest and Slowest Moving Items
    $stmt = $pdo->prepare("
        SELECT COUNT(*) as moving_items
        FROM ingredients 
        WHERE branch_id = ? 
        AND last_movement_date IS NOT NULL 
        AND last_movement_date >= DATE_SUB(?, INTERVAL 7 DAY)
    ");
    $stmt->execute([$branch_id, $current_date]);
    $fastest_moving = $stmt->fetchColumn();
    
    $slowest_moving = $total_items - $fastest_moving;
    
    // 17. Expiring Soon (within 7 days)
        $stmt = $pdo->prepare("
            SELECT COUNT(*) as expiring_soon
            FROM ingredients 
        WHERE branch_id = ? 
            AND expiry_date IS NOT NULL 
        AND expiry_date BETWEEN ? AND DATE_ADD(?, INTERVAL 7 DAY)
    ");
    $stmt->execute([$branch_id, $current_date, $current_date]);
    $expiring_soon = $stmt->fetchColumn();
    
    // 18. Trends (simplified calculations)
    $last_week_start = date('Y-m-d', strtotime('monday last week'));
    
    // Total items trend
    $stmt = $pdo->prepare("SELECT COUNT(*) as last_week_total FROM ingredients WHERE branch_id = ?");
    $stmt->execute([$branch_id]);
    $last_week_total = $stmt->fetchColumn();
    
    $total_items_change = $last_week_total > 0 ? round((($total_items - $last_week_total) / $last_week_total) * 100) : 0;
    $total_items_trend = ($total_items_change >= 0 ? '+' : '') . $total_items_change . '% this week';
    
    // Available ingredients trend
    $stmt = $pdo->prepare("SELECT COUNT(*) as last_week_available FROM ingredients WHERE branch_id = ? AND ingredient_quantity > 0");
    $stmt->execute([$branch_id]);
    $last_week_available = $stmt->fetchColumn();
    
    $available_change = $last_week_available > 0 ? $available_ingredients - $last_week_available : 0;
    $available_ingredients_trend = ($available_change >= 0 ? '+' : '') . $available_change . ' this week';
    
    // Low stock trend
    $stmt = $pdo->prepare("
        SELECT COUNT(*) as last_week_low 
        FROM ingredients 
        WHERE branch_id = ? 
        AND (ingredient_quantity < 5 OR ingredient_quantity < (ingredient_max_quantity * 0.1))
        AND ingredient_quantity > 0
    ");
    $stmt->execute([$branch_id]);
    $last_week_low = $stmt->fetchColumn();
    
    $low_stock_change = $last_week_low > 0 ? $low_stock_items - $last_week_low : 0;
    $low_stock_trend = ($low_stock_change >= 0 ? '+' : '') . $low_stock_change . ' this week';
    
    // Movements trend
    if ($stmt->query("SHOW TABLES LIKE 'stock_movements'")->rowCount() > 0) {
        $stmt = $pdo->prepare("
            SELECT COUNT(*) as last_week_movements 
            FROM stock_movements 
            WHERE branch_id = ? 
            AND DATE(movement_date) >= ? AND DATE(movement_date) < ?
        ");
        $stmt->execute([$branch_id, $last_week_start, $week_start]);
        $last_week_movements = $stmt->fetchColumn();
        
        $movements_change = $last_week_movements > 0 ? round((($stock_movements - $last_week_movements) / $last_week_movements) * 100) : 0;
        $movements_trend = ($movements_change >= 0 ? '+' : '') . $movements_change . '% this week';
    } else {
        $movements_trend = '+0% this week';
    }
    
    // Expiring trend
    $stmt = $pdo->prepare("
        SELECT COUNT(*) as last_week_expiring 
        FROM ingredients 
        WHERE branch_id = ? 
        AND expiry_date IS NOT NULL 
        AND expiry_date BETWEEN ? AND DATE_ADD(?, INTERVAL 30 DAY)
    ");
    $stmt->execute([$branch_id, date('Y-m-d', strtotime('-7 days')), date('Y-m-d', strtotime('-7 days'))]);
    $last_week_expiring = $stmt->fetchColumn();
    
    $expiring_change = $last_week_expiring > 0 ? $expiring_items - $last_week_expiring : 0;
    $expiring_trend = $expiring_change == 0 ? 'No change' : ($expiring_change > 0 ? '+' . $expiring_change : $expiring_change) . ' this week';
    
    // Turnover trend
    if ($stmt->query("SHOW TABLES LIKE 'stock_movements'")->rowCount() > 0) {
        $last_month_start = date('Y-m-01', strtotime('-1 month'));
    $stmt = $pdo->prepare("
        SELECT 
                COALESCE(SUM(CASE WHEN movement_type = 'out' THEN quantity ELSE 0 END), 0) as last_month_out
            FROM stock_movements 
            WHERE branch_id = ? 
            AND DATE(movement_date) >= ? AND DATE(movement_date) < ?
        ");
        $stmt->execute([$branch_id, $last_month_start, $month_start]);
        $last_month_out = $stmt->fetchColumn();
        
        $last_month_turnover = $total_items > 0 ? round(($last_month_out / $total_items) * 100, 1) : 0;
        $turnover_change = $last_month_turnover > 0 ? round((($stock_turnover - $last_month_turnover) / $last_month_turnover) * 100) : 0;
        $turnover_trend = ($turnover_change >= 0 ? '+' : '') . $turnover_change . '% this month';
    } else {
        $turnover_trend = '+0% this month';
    }
    
    // Get branch name from pos_branch table
    $stmt = $pdo->prepare("SELECT branch_name FROM pos_branch WHERE branch_id = ?");
    $stmt->execute([$branch_id]);
    $branch_name = $stmt->fetchColumn();
    
    // Fallback if branch name not found
    if (!$branch_name) {
        $branch_name = "Branch #$branch_id";
    }
    
    // Prepare response
    $response = [
        'branch_id' => $branch_id,
        'branch_name' => $branch_name,
        'total_items' => $total_items,
        'available_ingredients' => $available_ingredients,
        'low_stock_items' => $low_stock_items,
        'stock_movements' => $stock_movements,
        'expiring_items' => $expiring_items,
        'stock_turnover' => $stock_turnover,
        'adequate_stock' => $adequate_stock,
        'low_stock' => $low_stock_items,
        'out_of_stock' => $out_of_stock,
        'weekly_movements' => $weekly_movements,
        'fastest_moving' => $fastest_moving,
        'slowest_moving' => $slowest_moving,
        'expiring_soon' => $expiring_soon,
        'total_stock_value' => '₱' . number_format($total_stock_value, 2),
        'turnover_rate' => $stock_turnover,
        'average_age' => $average_age,
        'category_labels' => $category_labels,
        'category_data' => $category_counts,
        'high_turnover' => $high_turnover,
        'medium_turnover' => $medium_turnover,
        'low_turnover' => $low_turnover,
        'critical_alerts' => $critical_alerts,
        'reorder_recommendations' => $reorder_recommendations,
        'category_cards' => $category_cards,
        'total_items_trend' => $total_items_trend,
        'available_ingredients_trend' => $available_ingredients_trend,
        'low_stock_trend' => $low_stock_trend,
        'movements_trend' => $movements_trend,
        'expiring_trend' => $expiring_trend,
        'turnover_trend' => $turnover_trend
    ];
    
    echo json_encode($response);
    
} catch (Exception $e) {
    error_log("Error in get_stockman_analytics: " . $e->getMessage());
    http_response_code(500);
    echo json_encode(['error' => 'Internal server error']);
}
?>
