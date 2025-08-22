<?php
require_once 'db_connect.php';
session_start();

echo "<h1>🔗 Assign Products to Branches</h1>";

try {
    // Check which table to use
    $useTable = null;
    
    // Check pos_branch_product
    $tables = $pdo->query("SHOW TABLES LIKE 'pos_branch_product'")->fetchAll();
    if (!empty($tables)) {
        $useTable = 'pos_branch_product';
        echo "<p>✅ Using <code>pos_branch_product</code> table</p>";
    } else {
        // Check product_branch
        $tables = $pdo->query("SHOW TABLES LIKE 'product_branch'")->fetchAll();
        if (!empty($tables)) {
            $useTable = 'product_branch';
            echo "<p>✅ Using <code>product_branch</code> table</p>";
        }
    }
    
    if (!$useTable) {
        echo "<p style='color: red;'>❌ No suitable product-branch table found!</p>";
        exit;
    }
    
    // Get branches
    $branches = $pdo->query("SELECT branch_id, branch_name FROM pos_branch ORDER BY branch_id")->fetchAll(PDO::FETCH_ASSOC);
    if (empty($branches)) {
        echo "<p style='color: red;'>❌ No branches found!</p>";
        exit;
    }
    
    // Get products
    $products = $pdo->query("SELECT product_id, product_name FROM pos_product ORDER BY product_id")->fetchAll(PDO::FETCH_ASSOC);
    if (empty($products)) {
        echo "<p style='color: red;'>❌ No products found!</p>";
        exit;
    }
    
    echo "<h3>📊 Current Status:</h3>";
    echo "<p><strong>Branches:</strong> " . count($branches) . "</p>";
    echo "<p><strong>Products:</strong> " . count($products) . "</p>";
    
    // Check current assignments
    $currentAssignments = $pdo->query("SELECT COUNT(*) FROM $useTable")->fetchColumn();
    echo "<p><strong>Current Assignments:</strong> $currentAssignments</p>";
    
    if ($currentAssignments == 0) {
        echo "<div style='background: #fff3cd; color: #856404; padding: 15px; border-radius: 5px; margin: 15px 0;'>";
        echo "<p><strong>⚠️ No products are assigned to any branches!</strong></p>";
        echo "<p>This is why stockmen see 'No Products Found'.</p>";
        echo "</div>";
    }
    
    // Show assignment form
    echo "<h3>🔧 Quick Assignment Options:</h3>";
    
    echo "<div style='display: flex; gap: 20px; margin: 20px 0;'>";
    
    // Option 1: Assign all products to all branches
    echo "<div style='border: 1px solid #ddd; padding: 15px; border-radius: 5px; flex: 1;'>";
    echo "<h4>Option 1: Assign All Products to All Branches</h4>";
    echo "<p>This will create " . (count($products) * count($branches)) . " assignments.</p>";
    echo "<form method='post'>";
    echo "<button type='submit' name='assign_all_to_all' style='background: #007bff; color: white; padding: 10px 20px; border: none; border-radius: 5px; cursor: pointer;'>Assign All to All</button>";
    echo "</form>";
    echo "</div>";
    
    // Option 2: Assign all products to first branch only
    echo "<div style='border: 1px solid #ddd; padding: 15px; border-radius: 5px; flex: 1;'>";
    echo "<h4>Option 2: Assign All Products to First Branch Only</h4>";
    echo "<p>Branch: <strong>{$branches[0]['branch_name']}</strong></p>";
    echo "<p>This will create " . count($products) . " assignments.</p>";
    echo "<form method='post'>";
    echo "<input type='hidden' name='target_branch' value='{$branches[0]['branch_id']}'>";
    echo "<button type='submit' name='assign_all_to_one' style='background: #28a745; color: white; padding: 10px 20px; border: none; border-radius: 5px; cursor: pointer;'>Assign All to First Branch</button>";
    echo "</form>";
    echo "</div>";
    
    echo "</div>";
    
    // Handle form submissions
    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        echo "<h3>🔄 Processing Assignment...</h3>";
        
        $assignedCount = 0;
        $errors = [];
        
        if (isset($_POST['assign_all_to_all'])) {
            // Assign all products to all branches
            foreach ($branches as $branch) {
                foreach ($products as $product) {
                    try {
                        if ($useTable === 'pos_branch_product') {
                            $stmt = $pdo->prepare("INSERT INTO pos_branch_product (branch_id, product_id, quantity) VALUES (?, ?, ?) ON DUPLICATE KEY UPDATE quantity = VALUES(quantity)");
                            $stmt->execute([$branch['branch_id'], $product['product_id'], 10]);
                        } else {
                            $stmt = $pdo->prepare("INSERT INTO product_branch (branch_id, product_id) VALUES (?, ?) ON DUPLICATE KEY UPDATE branch_id = VALUES(branch_id)");
                            $stmt->execute([$branch['branch_id'], $product['product_id']]);
                        }
                        $assignedCount++;
                    } catch (Exception $e) {
                        $errors[] = "Error assigning product {$product['product_id']} to branch {$branch['branch_id']}: " . $e->getMessage();
                    }
                }
            }
        } elseif (isset($_POST['assign_all_to_one'])) {
            // Assign all products to one branch
            $targetBranch = intval($_POST['target_branch']);
            foreach ($products as $product) {
                try {
                    if ($useTable === 'pos_branch_product') {
                        $stmt = $pdo->prepare("INSERT INTO pos_branch_product (branch_id, product_id, quantity) VALUES (?, ?, ?) ON DUPLICATE KEY UPDATE quantity = VALUES(quantity)");
                        $stmt->execute([$targetBranch, $product['product_id'], 10]);
                    } else {
                        $stmt = $pdo->prepare("INSERT INTO product_branch (branch_id, product_id) VALUES (?, ?) ON DUPLICATE KEY UPDATE branch_id = VALUES(branch_id)");
                        $stmt->execute([$targetBranch, $product['product_id']]);
                    }
                    $assignedCount++;
                } catch (Exception $e) {
                    $errors[] = "Error assigning product {$product['product_id']} to branch $targetBranch: " . $e->getMessage();
                }
            }
        }
        
        // Show results
        if (empty($errors)) {
            echo "<div style='background: #d4edda; color: #155724; padding: 15px; border-radius: 5px; margin: 15px 0;'>";
            echo "<p>✅ <strong>Success!</strong> Assigned $assignedCount product-branch relationships.</p>";
            echo "<p><a href='stockman_products.php' style='color: #155724; text-decoration: underline;'>🔗 Go to Available Products page</a></p>";
            echo "<p><a href='assign_products_to_branches.php' style='color: #155724; text-decoration: underline;'>🔄 Refresh this page</a></p>";
            echo "</div>";
        } else {
            echo "<div style='background: #f8d7da; color: #721c24; padding: 15px; border-radius: 5px; margin: 15px 0;'>";
            echo "<p>⚠️ <strong>Completed with errors.</strong> Assigned $assignedCount relationships.</p>";
            echo "<p><strong>Errors:</strong></p>";
            echo "<ul>";
            foreach ($errors as $error) {
                echo "<li>$error</li>";
            }
            echo "</ul>";
            echo "</div>";
        }
    }
    
    // Show current assignments summary
    if ($currentAssignments > 0) {
        echo "<h3>📋 Current Assignments Summary:</h3>";
        
        $summary = $pdo->query("
            SELECT 
                b.branch_name,
                COUNT(bp.product_id) as product_count
            FROM pos_branch b
            LEFT JOIN $useTable bp ON b.branch_id = bp.branch_id
            GROUP BY b.branch_id, b.branch_name
            ORDER BY b.branch_name
        ")->fetchAll(PDO::FETCH_ASSOC);
        
        echo "<table border='1' style='border-collapse: collapse; width: 100%; margin: 10px 0;'>";
        echo "<tr style='background: #f5f5f5;'><th>Branch</th><th>Products Assigned</th></tr>";
        foreach ($summary as $row) {
            echo "<tr>";
            echo "<td><strong>{$row['branch_name']}</strong></td>";
            echo "<td>{$row['product_count']}</td>";
            echo "</tr>";
        }
        echo "</table>";
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
code { background: #f5f5f5; padding: 2px 4px; border-radius: 3px; font-family: monospace; }
</style>
