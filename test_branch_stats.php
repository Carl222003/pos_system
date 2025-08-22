<?php
require_once 'db_connect.php';

// Test with a specific branch ID
$branch_id = 1; // Test with first branch
$today = date('Y-m-d');

echo "Testing Branch Stats for Branch ID: $branch_id\n";
echo "Today: $today\n\n";

try {
    // Test 1: Check if branch exists
    echo "=== TEST 1: Branch Info ===\n";
    $stmt = $pdo->prepare("SELECT branch_id, branch_name, branch_code, status FROM pos_branch WHERE branch_id = ?");
    $stmt->execute([$branch_id]);
    $branch_info = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if ($branch_info) {
        echo "Branch found: " . $branch_info['branch_name'] . " (Status: " . $branch_info['status'] . ")\n";
    } else {
        echo "Branch not found!\n";
        exit;
    }

    // Test 2: Check cashiers
    echo "\n=== TEST 2: Cashiers ===\n";
    $stmt = $pdo->prepare("SELECT user_id, user_name, user_status FROM pos_user WHERE user_type = 'Cashier' AND branch_id = ? AND user_status = 'Active'");
    $stmt->execute([$branch_id]);
    $cashiers = $stmt->fetchAll(PDO::FETCH_ASSOC);
    echo "Active cashiers found: " . count($cashiers) . "\n";
    foreach ($cashiers as $cashier) {
        echo "- " . $cashier['user_name'] . " (ID: " . $cashier['user_id'] . ")\n";
    }

    // Test 3: Check orders
    echo "\n=== TEST 3: Orders ===\n";
    $stmt = $pdo->prepare("SELECT COUNT(DISTINCT order_id) as total_orders, COALESCE(SUM(order_total), 0) as total_sales FROM pos_order WHERE branch_id = ? AND DATE(order_datetime) = ?");
    $stmt->execute([$branch_id, $today]);
    $sales_data = $stmt->fetch(PDO::FETCH_ASSOC);
    echo "Orders today: " . $sales_data['total_orders'] . "\n";
    echo "Sales today: ₱" . number_format($sales_data['total_sales'], 2) . "\n";

    // Test 4: Check if pos_branch_product table exists
    echo "\n=== TEST 4: Check Tables ===\n";
    $tables = ['pos_branch_product', 'pos_product', 'pos_branch_ingredient'];
    foreach ($tables as $table) {
        $stmt = $pdo->query("SHOW TABLES LIKE '$table'");
        if ($stmt->rowCount() > 0) {
            echo "$table table exists\n";
        } else {
            echo "$table table does NOT exist\n";
        }
    }

    // Test 5: Check low stock (if table exists)
    echo "\n=== TEST 5: Low Stock ===\n";
    $stmt = $pdo->query("SHOW TABLES LIKE 'pos_branch_product'");
    if ($stmt->rowCount() > 0) {
        $stmt = $pdo->prepare("SELECT COUNT(*) FROM pos_branch_product bp JOIN pos_product p ON bp.product_id = p.product_id WHERE bp.branch_id = ? AND bp.stock_quantity <= 10 AND bp.stock_quantity > 0");
        $stmt->execute([$branch_id]);
        $low_stock = $stmt->fetchColumn();
        echo "Low stock items: $low_stock\n";
    } else {
        echo "pos_branch_product table not found\n";
    }

    // Test 6: Check expiring items (if table exists)
    echo "\n=== TEST 6: Expiring Items ===\n";
    $stmt = $pdo->query("SHOW TABLES LIKE 'pos_branch_ingredient'");
    if ($stmt->rowCount() > 0) {
        $stmt = $pdo->prepare("SELECT COUNT(*) FROM pos_branch_ingredient bi WHERE bi.branch_id = ? AND bi.expiry_date <= DATE_ADD(CURDATE(), INTERVAL 30 DAY) AND bi.expiry_date >= CURDATE() AND bi.quantity > 0");
        $stmt->execute([$branch_id]);
        $expiring = $stmt->fetchColumn();
        echo "Expiring items: $expiring\n";
    } else {
        echo "pos_branch_ingredient table not found\n";
    }

} catch (PDOException $e) {
    echo "Database error: " . $e->getMessage() . "\n";
}
?> 