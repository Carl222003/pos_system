<?php
require_once 'db_connect.php';
session_start();

echo "<h1>🔍 Debug: Why No Products Found?</h1>";

try {
    // Check session info
    echo "<h3>👤 Session Information:</h3>";
    echo "<p><strong>User ID:</strong> " . ($_SESSION['user_id'] ?? 'Not set') . "</p>";
    echo "<p><strong>User Type:</strong> " . ($_SESSION['user_type'] ?? 'Not set') . "</p>";
    echo "<p><strong>User Name:</strong> " . ($_SESSION['user_name'] ?? 'Not set') . "</p>";
    echo "<p><strong>Branch ID (Session):</strong> " . ($_SESSION['branch_id'] ?? 'Not set') . "</p>";
    
    // Check user's branch from database
    if (isset($_SESSION['user_id'])) {
        $stmt = $pdo->prepare('SELECT branch_id FROM pos_user WHERE user_id = ?');
        $stmt->execute([$_SESSION['user_id']]);
        $dbBranchId = $stmt->fetchColumn();
        echo "<p><strong>Branch ID (Database):</strong> " . ($dbBranchId ?? 'Not set') . "</p>";
        $branch_id = $dbBranchId;
    } else {
        echo "<p style='color: red;'>❌ No user logged in!</p>";
        exit;
    }
    
    if (!$branch_id) {
        echo "<div style='background: #f8d7da; color: #721c24; padding: 15px; border-radius: 5px;'>";
        echo "<p>❌ <strong>Problem:</strong> User has no branch assigned!</p>";
        echo "<p>The stockman needs to be assigned to a branch first.</p>";
        echo "</div>";
        
        // Show available branches
        $branches = $pdo->query("SELECT branch_id, branch_name FROM pos_branch")->fetchAll(PDO::FETCH_ASSOC);
        if (!empty($branches)) {
            echo "<h4>Available Branches:</h4>";
            echo "<ul>";
            foreach ($branches as $branch) {
                echo "<li>ID: {$branch['branch_id']} - {$branch['branch_name']}</li>";
            }
            echo "</ul>";
            
            echo "<form method='post'>";
            echo "<p>Assign this user to branch:</p>";
            echo "<select name='assign_branch'>";
            foreach ($branches as $branch) {
                echo "<option value='{$branch['branch_id']}'>{$branch['branch_name']}</option>";
            }
            echo "</select>";
            echo "<button type='submit' name='assign_user_branch' style='margin-left: 10px; padding: 5px 10px;'>Assign Branch</button>";
            echo "</form>";
        }
        exit;
    }
    
    // Get branch name
    $stmt = $pdo->prepare('SELECT branch_name FROM pos_branch WHERE branch_id = ?');
    $stmt->execute([$branch_id]);
    $branchName = $stmt->fetchColumn();
    echo "<p><strong>Branch Name:</strong> " . ($branchName ?? 'Unknown') . "</p>";
    
    echo "<hr>";
    
    // Check products in database
    echo "<h3>📦 Products in Database:</h3>";
    $totalProducts = $pdo->query("SELECT COUNT(*) FROM pos_product")->fetchColumn();
    echo "<p><strong>Total Products:</strong> $totalProducts</p>";
    
    if ($totalProducts == 0) {
        echo "<p style='color: red;'>❌ No products in database!</p>";
        exit;
    }
    
    // Check product-branch tables
    echo "<h3>🔗 Product-Branch Tables:</h3>";
    
    // Check pos_branch_product
    $tables = $pdo->query("SHOW TABLES LIKE 'pos_branch_product'")->fetchAll();
    $posBranchProductExists = !empty($tables);
    $posBranchProductCount = 0;
    
    if ($posBranchProductExists) {
        $posBranchProductCount = $pdo->query("SELECT COUNT(*) FROM pos_branch_product")->fetchColumn();
        echo "<p>✅ <strong>pos_branch_product table:</strong> Exists ($posBranchProductCount records)</p>";
    } else {
        echo "<p>❌ <strong>pos_branch_product table:</strong> Does not exist</p>";
    }
    
    // Check product_branch
    $tables = $pdo->query("SHOW TABLES LIKE 'product_branch'")->fetchAll();
    $productBranchExists = !empty($tables);
    $productBranchCount = 0;
    
    if ($productBranchExists) {
        $productBranchCount = $pdo->query("SELECT COUNT(*) FROM product_branch")->fetchColumn();
        echo "<p>✅ <strong>product_branch table:</strong> Exists ($productBranchCount records)</p>";
    } else {
        echo "<p>❌ <strong>product_branch table:</strong> Does not exist</p>";
    }
    
    // Check assignments for this branch
    echo "<h3>🎯 Products Assigned to This Branch (ID: $branch_id):</h3>";
    
    $branchProducts = [];
    
    if ($posBranchProductCount > 0) {
        $stmt = $pdo->prepare("
            SELECT p.product_id, p.product_name, bp.quantity 
            FROM pos_branch_product bp 
            JOIN pos_product p ON bp.product_id = p.product_id 
            WHERE bp.branch_id = ?
        ");
        $stmt->execute([$branch_id]);
        $branchProducts = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        echo "<p><strong>From pos_branch_product:</strong> " . count($branchProducts) . " products</p>";
        if (!empty($branchProducts)) {
            echo "<ul>";
            foreach ($branchProducts as $product) {
                echo "<li>{$product['product_name']} (ID: {$product['product_id']}, Qty: {$product['quantity']})</li>";
            }
            echo "</ul>";
        }
    }
    
    if ($productBranchCount > 0 && empty($branchProducts)) {
        $stmt = $pdo->prepare("
            SELECT p.product_id, p.product_name 
            FROM product_branch pb 
            JOIN pos_product p ON pb.product_id = p.product_id 
            WHERE pb.branch_id = ?
        ");
        $stmt->execute([$branch_id]);
        $branchProducts = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        echo "<p><strong>From product_branch:</strong> " . count($branchProducts) . " products</p>";
        if (!empty($branchProducts)) {
            echo "<ul>";
            foreach ($branchProducts as $product) {
                echo "<li>{$product['product_name']} (ID: {$product['product_id']})</li>";
            }
            echo "</ul>";
        }
    }
    
    if (empty($branchProducts)) {
        echo "<div style='background: #fff3cd; color: #856404; padding: 15px; border-radius: 5px;'>";
        echo "<p>⚠️ <strong>Problem Found:</strong> No products are assigned to this branch!</p>";
        echo "<p>This is why you see 'No Products Found'.</p>";
        echo "</div>";
        
        // Quick fix option
        echo "<h4>🔧 Quick Fix:</h4>";
        echo "<form method='post'>";
        echo "<p>Assign all products to this branch:</p>";
        echo "<button type='submit' name='assign_all_products' style='background: #007bff; color: white; padding: 10px 20px; border: none; border-radius: 5px; cursor: pointer;'>Assign All Products to My Branch</button>";
        echo "</form>";
    } else {
        echo "<div style='background: #d4edda; color: #155724; padding: 15px; border-radius: 5px;'>";
        echo "<p>✅ <strong>Products are assigned to this branch!</strong></p>";
        echo "<p>The issue might be in the API. Let's test it...</p>";
        echo "</div>";
        
        // Test the API
        echo "<h4>🧪 Testing API:</h4>";
        echo "<p><a href='get_stockman_products.php' target='_blank' style='background: #28a745; color: white; padding: 8px 15px; text-decoration: none; border-radius: 4px;'>Test API Call</a></p>";
    }
    
    // Handle form submissions
    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        if (isset($_POST['assign_user_branch'])) {
            $assignBranchId = intval($_POST['assign_branch']);
            $stmt = $pdo->prepare("UPDATE pos_user SET branch_id = ? WHERE user_id = ?");
            $stmt->execute([$assignBranchId, $_SESSION['user_id']]);
            
            echo "<div style='background: #d4edda; color: #155724; padding: 15px; border-radius: 5px; margin: 15px 0;'>";
            echo "<p>✅ User assigned to branch successfully!</p>";
            echo "<p><a href='debug_no_products.php'>Refresh to continue</a></p>";
            echo "</div>";
        }
        
        if (isset($_POST['assign_all_products'])) {
            $products = $pdo->query("SELECT product_id FROM pos_product")->fetchAll(PDO::FETCH_COLUMN);
            $assignedCount = 0;
            
            // Use pos_branch_product if it exists, otherwise product_branch
            if ($posBranchProductExists) {
                $stmt = $pdo->prepare("INSERT INTO pos_branch_product (branch_id, product_id, quantity) VALUES (?, ?, ?) ON DUPLICATE KEY UPDATE quantity = VALUES(quantity)");
                foreach ($products as $productId) {
                    $stmt->execute([$branch_id, $productId, 10]);
                    $assignedCount++;
                }
            } elseif ($productBranchExists) {
                $stmt = $pdo->prepare("INSERT INTO product_branch (branch_id, product_id) VALUES (?, ?) ON DUPLICATE KEY UPDATE branch_id = VALUES(branch_id)");
                foreach ($products as $productId) {
                    $stmt->execute([$branch_id, $productId]);
                    $assignedCount++;
                }
            }
            
            echo "<div style='background: #d4edda; color: #155724; padding: 15px; border-radius: 5px; margin: 15px 0;'>";
            echo "<p>✅ Assigned $assignedCount products to your branch!</p>";
            echo "<p><a href='stockman_products.php' target='_blank'>Go to Available Products</a></p>";
            echo "<p><a href='debug_no_products.php'>Refresh this page</a></p>";
            echo "</div>";
        }
    }
    
} catch (Exception $e) {
    echo "<p style='color: red;'>❌ Error: " . $e->getMessage() . "</p>";
}
?>

<style>
body { font-family: Arial, sans-serif; margin: 20px; }
code { background: #f5f5f5; padding: 2px 4px; border-radius: 3px; font-family: monospace; }
</style>
