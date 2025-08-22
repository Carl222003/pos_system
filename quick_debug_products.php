<?php
require_once 'db_connect.php';
session_start();

echo "<h1>🔍 Quick Debug: Why No Products Showing?</h1>";

// Check session
echo "<h3>👤 Current Session:</h3>";
echo "<p><strong>User ID:</strong> " . ($_SESSION['user_id'] ?? 'Not set') . "</p>";
echo "<p><strong>User Type:</strong> " . ($_SESSION['user_type'] ?? 'Not set') . "</p>";
echo "<p><strong>Branch ID:</strong> " . ($_SESSION['branch_id'] ?? 'Not set') . "</p>";

if (!isset($_SESSION['user_id'])) {
    echo "<p style='color: red;'>❌ No user logged in!</p>";
    exit;
}

// Get user's branch from database
$stmt = $pdo->prepare('SELECT branch_id FROM pos_user WHERE user_id = ?');
$stmt->execute([$_SESSION['user_id']]);
$branch_id = $stmt->fetchColumn();

echo "<p><strong>Branch ID from DB:</strong> " . ($branch_id ?? 'Not set') . "</p>";

if (!$branch_id) {
    echo "<p style='color: red;'>❌ User has no branch assigned!</p>";
    exit;
}

// Check which tables exist and have data
echo "<h3>🔗 Product-Branch Tables Check:</h3>";

$tables_to_check = ['pos_branch_product', 'product_branch'];
$table_data = [];

foreach ($tables_to_check as $table) {
    $exists = $pdo->query("SHOW TABLES LIKE '$table'")->fetchAll();
    if (!empty($exists)) {
        $count = $pdo->query("SELECT COUNT(*) FROM $table")->fetchColumn();
        $branch_count = $pdo->prepare("SELECT COUNT(*) FROM $table WHERE branch_id = ?");
        $branch_count->execute([$branch_id]);
        $branch_specific = $branch_count->fetchColumn();
        
        $table_data[$table] = [
            'exists' => true,
            'total_count' => $count,
            'branch_count' => $branch_specific
        ];
        
        echo "<p>✅ <strong>$table:</strong> Exists ($count total, $branch_specific for your branch)</p>";
    } else {
        $table_data[$table] = ['exists' => false];
        echo "<p>❌ <strong>$table:</strong> Does not exist</p>";
    }
}

// Show products assigned to this branch
echo "<h3>📦 Products Assigned to Your Branch (ID: $branch_id):</h3>";

$found_products = false;

foreach ($tables_to_check as $table) {
    if ($table_data[$table]['exists'] && $table_data[$table]['branch_count'] > 0) {
        echo "<h4>From $table:</h4>";
        
        if ($table === 'pos_branch_product') {
            $stmt = $pdo->prepare("
                SELECT p.product_id, p.product_name, p.product_status, bp.quantity 
                FROM pos_branch_product bp 
                JOIN pos_product p ON bp.product_id = p.product_id 
                WHERE bp.branch_id = ?
                ORDER BY p.product_name
            ");
        } else {
            $stmt = $pdo->prepare("
                SELECT p.product_id, p.product_name, p.product_status 
                FROM product_branch pb 
                JOIN pos_product p ON pb.product_id = p.product_id 
                WHERE pb.branch_id = ?
                ORDER BY p.product_name
            ");
        }
        
        $stmt->execute([$branch_id]);
        $products = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        if (!empty($products)) {
            $found_products = true;
            echo "<table border='1' style='border-collapse: collapse; width: 100%; margin: 10px 0;'>";
            echo "<tr style='background: #f5f5f5;'><th>ID</th><th>Name</th><th>Status</th>";
            if ($table === 'pos_branch_product') echo "<th>Quantity</th>";
            echo "</tr>";
            
            foreach ($products as $product) {
                $statusColor = $product['product_status'] === 'Available' ? 'green' : 'red';
                echo "<tr>";
                echo "<td>{$product['product_id']}</td>";
                echo "<td><strong>{$product['product_name']}</strong></td>";
                echo "<td style='color: $statusColor;'>{$product['product_status']}</td>";
                if ($table === 'pos_branch_product') echo "<td>{$product['quantity']}</td>";
                echo "</tr>";
            }
            echo "</table>";
        }
    }
}

if (!$found_products) {
    echo "<p style='color: red;'>❌ No products found assigned to your branch!</p>";
    
    // Show total products in system
    $total_products = $pdo->query("SELECT COUNT(*) FROM pos_product")->fetchColumn();
    echo "<p><strong>Total products in system:</strong> $total_products</p>";
    
    if ($total_products > 0) {
        echo "<div style='background: #fff3cd; color: #856404; padding: 15px; border-radius: 5px; margin: 15px 0;'>";
        echo "<p><strong>⚠️ Problem:</strong> Products exist in the system but are not assigned to your branch!</p>";
        echo "</div>";
        
        echo "<form method='post'>";
        echo "<button type='submit' name='assign_all' style='background: #007bff; color: white; padding: 10px 20px; border: none; border-radius: 5px; cursor: pointer;'>Assign All Products to My Branch</button>";
        echo "</form>";
    }
} else {
    echo "<div style='background: #d4edda; color: #155724; padding: 15px; border-radius: 5px; margin: 15px 0;'>";
    echo "<p>✅ <strong>Products are assigned to your branch!</strong> The issue must be in the API.</p>";
    echo "</div>";
}

// Test the API directly
echo "<h3>🧪 Testing API Call:</h3>";
echo "<p><a href='get_stockman_products.php' target='_blank' style='background: #28a745; color: white; padding: 8px 15px; text-decoration: none; border-radius: 4px;'>Test API Directly</a></p>";

// Handle assignment
if (isset($_POST['assign_all'])) {
    $products = $pdo->query("SELECT product_id FROM pos_product")->fetchAll(PDO::FETCH_COLUMN);
    $assigned = 0;
    
    // Use pos_branch_product if it exists
    if ($table_data['pos_branch_product']['exists']) {
        $stmt = $pdo->prepare("INSERT INTO pos_branch_product (branch_id, product_id, quantity) VALUES (?, ?, 10) ON DUPLICATE KEY UPDATE quantity = 10");
        foreach ($products as $product_id) {
            $stmt->execute([$branch_id, $product_id]);
            $assigned++;
        }
    } elseif ($table_data['product_branch']['exists']) {
        $stmt = $pdo->prepare("INSERT INTO product_branch (branch_id, product_id) VALUES (?, ?) ON DUPLICATE KEY UPDATE branch_id = VALUES(branch_id)");
        foreach ($products as $product_id) {
            $stmt->execute([$branch_id, $product_id]);
            $assigned++;
        }
    }
    
    echo "<div style='background: #d4edda; color: #155724; padding: 15px; border-radius: 5px; margin: 15px 0;'>";
    echo "<p>✅ Assigned $assigned products to your branch!</p>";
    echo "<p><a href='stockman_products.php'>Go back to Available Products</a></p>";
    echo "</div>";
}

?>

<style>
body { font-family: Arial, sans-serif; margin: 20px; }
table { border-collapse: collapse; width: 100%; margin: 10px 0; }
th, td { padding: 8px 12px; text-align: left; border: 1px solid #ddd; }
th { background-color: #f2f2f2; font-weight: bold; }
</style>
