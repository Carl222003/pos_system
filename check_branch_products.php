<?php
require_once 'db_connect.php';
session_start();

echo "<h1>🏢 Branch-Product Assignment Check</h1>";

try {
    // Check if pos_branch_product table exists
    $tables = $pdo->query("SHOW TABLES LIKE 'pos_branch_product'")->fetchAll();
    if (empty($tables)) {
        echo "<p style='color: red;'>❌ pos_branch_product table does not exist!</p>";
        echo "<p>This table is needed to link products to branches.</p>";
        exit;
    }

    echo "<p style='color: green;'>✅ pos_branch_product table exists</p>";

    // Check branches
    $branches = $pdo->query("SELECT branch_id, branch_name FROM pos_branch ORDER BY branch_id")->fetchAll(PDO::FETCH_ASSOC);
    echo "<h3>🏢 Available Branches:</h3>";
    if (empty($branches)) {
        echo "<p style='color: red;'>❌ No branches found!</p>";
        exit;
    }

    echo "<ul>";
    foreach ($branches as $branch) {
        echo "<li><strong>ID {$branch['branch_id']}:</strong> {$branch['branch_name']}</li>";
    }
    echo "</ul>";

    // Check users and their branch assignments
    $users = $pdo->query("SELECT user_id, user_name, user_type, branch_id FROM pos_user WHERE user_type = 'Stockman' ORDER BY user_id")->fetchAll(PDO::FETCH_ASSOC);
    echo "<h3>👥 Stockman Users:</h3>";
    if (empty($users)) {
        echo "<p style='color: orange;'>⚠️ No stockman users found!</p>";
    } else {
        echo "<ul>";
        foreach ($users as $user) {
            $branchName = 'Not Assigned';
            if ($user['branch_id']) {
                foreach ($branches as $branch) {
                    if ($branch['branch_id'] == $user['branch_id']) {
                        $branchName = $branch['branch_name'];
                        break;
                    }
                }
            }
            echo "<li><strong>{$user['user_name']}</strong> (ID: {$user['user_id']}) → Branch: $branchName</li>";
        }
        echo "</ul>";
    }

    // Check products
    $products = $pdo->query("SELECT product_id, product_name FROM pos_product ORDER BY product_id LIMIT 10")->fetchAll(PDO::FETCH_ASSOC);
    echo "<h3>🛍️ Products (First 10):</h3>";
    if (empty($products)) {
        echo "<p style='color: red;'>❌ No products found!</p>";
        exit;
    }

    echo "<ul>";
    foreach ($products as $product) {
        echo "<li><strong>ID {$product['product_id']}:</strong> {$product['product_name']}</li>";
    }
    echo "</ul>";

    // Check branch-product assignments
    $branchProducts = $pdo->query("
        SELECT 
            bp.branch_id, 
            b.branch_name, 
            bp.product_id, 
            p.product_name, 
            bp.quantity
        FROM pos_branch_product bp
        LEFT JOIN pos_branch b ON bp.branch_id = b.branch_id
        LEFT JOIN pos_product p ON bp.product_id = p.product_id
        ORDER BY bp.branch_id, bp.product_id
        LIMIT 20
    ")->fetchAll(PDO::FETCH_ASSOC);

    echo "<h3>🔗 Branch-Product Assignments (First 20):</h3>";
    if (empty($branchProducts)) {
        echo "<div style='background: #fff3cd; color: #856404; padding: 15px; border-radius: 5px; margin: 10px 0;'>";
        echo "<p><strong>⚠️ No products are assigned to any branches!</strong></p>";
        echo "<p>This is why you see 'No Products Found'. Products need to be assigned to branches first.</p>";
        echo "</div>";
        
        echo "<h4>🔧 Quick Fix - Assign All Products to All Branches:</h4>";
        echo "<form method='post'>";
        echo "<button type='submit' name='assign_all' style='background: #007bff; color: white; padding: 10px 20px; border: none; border-radius: 5px; cursor: pointer;'>Assign All Products to All Branches</button>";
        echo "</form>";
        
    } else {
        echo "<table border='1' style='border-collapse: collapse; width: 100%;'>";
        echo "<tr style='background: #f5f5f5;'>";
        echo "<th>Branch ID</th><th>Branch Name</th><th>Product ID</th><th>Product Name</th><th>Quantity</th>";
        echo "</tr>";
        
        foreach ($branchProducts as $assignment) {
            echo "<tr>";
            echo "<td>{$assignment['branch_id']}</td>";
            echo "<td>{$assignment['branch_name']}</td>";
            echo "<td>{$assignment['product_id']}</td>";
            echo "<td>{$assignment['product_name']}</td>";
            echo "<td>{$assignment['quantity']}</td>";
            echo "</tr>";
        }
        echo "</table>";
    }

    // Handle assignment action
    if (isset($_POST['assign_all'])) {
        echo "<h3>🔄 Assigning Products to Branches...</h3>";
        
        $assignedCount = 0;
        foreach ($branches as $branch) {
            foreach ($products as $product) {
                try {
                    $stmt = $pdo->prepare("INSERT INTO pos_branch_product (branch_id, product_id, quantity) VALUES (?, ?, ?) ON DUPLICATE KEY UPDATE quantity = quantity");
                    $stmt->execute([$branch['branch_id'], $product['product_id'], 10]); // Default quantity of 10
                    $assignedCount++;
                } catch (Exception $e) {
                    echo "<p style='color: orange;'>⚠️ Error assigning product {$product['product_id']} to branch {$branch['branch_id']}: " . $e->getMessage() . "</p>";
                }
            }
        }
        
        echo "<div style='background: #d4edda; color: #155724; padding: 15px; border-radius: 5px; margin: 10px 0;'>";
        echo "<p>✅ Successfully assigned products to branches! ($assignedCount assignments)</p>";
        echo "<p><a href='check_branch_products.php' style='color: #155724; text-decoration: underline;'>🔄 Refresh to see changes</a></p>";
        echo "</div>";
    }

    // Test the API for current user
    echo "<h3>🧪 API Test for Current Session:</h3>";
    if (isset($_SESSION['user_id']) && $_SESSION['user_type'] === 'Stockman') {
        echo "<p><strong>Current User:</strong> {$_SESSION['user_name']} (ID: {$_SESSION['user_id']})</p>";
        echo "<p><strong>Session Branch ID:</strong> " . ($_SESSION['branch_id'] ?? 'Not set') . "</p>";
        
        // Get user's branch from database
        $stmt = $pdo->prepare('SELECT branch_id FROM pos_user WHERE user_id = ?');
        $stmt->execute([$_SESSION['user_id']]);
        $dbBranchId = $stmt->fetchColumn();
        echo "<p><strong>Database Branch ID:</strong> " . ($dbBranchId ?? 'Not set') . "</p>";
        
        if ($dbBranchId) {
            $branchName = 'Unknown';
            foreach ($branches as $branch) {
                if ($branch['branch_id'] == $dbBranchId) {
                    $branchName = $branch['branch_name'];
                    break;
                }
            }
            echo "<p><strong>Branch Name:</strong> $branchName</p>";
            
            // Test API call
            echo "<p><a href='get_stockman_products.php' target='_blank' style='background: #28a745; color: white; padding: 8px 15px; text-decoration: none; border-radius: 4px;'>🔗 Test API Call</a></p>";
        }
    } else {
        echo "<p style='color: orange;'>⚠️ Not logged in as stockman. Please login first.</p>";
    }

} catch (Exception $e) {
    echo "<p style='color: red;'>❌ Error: " . $e->getMessage() . "</p>";
}
?>

<style>
body { font-family: Arial, sans-serif; margin: 20px; }
table { border-collapse: collapse; width: 100%; margin: 10px 0; }
th, td { padding: 8px 12px; text-align: left; border: 1px solid #ddd; }
th { background-color: #f2f2f2; font-weight: bold; }
</style>
