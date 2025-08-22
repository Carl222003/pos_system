<?php
require_once 'db_connect.php';
require_once 'auth_function.php';

checkAdminLogin();

echo "<h2>🧪 Testing Branch Assignment Functionality</h2>";

try {
    // Check which tables exist
    $tables = $pdo->query("SHOW TABLES")->fetchAll(PDO::FETCH_COLUMN);
    echo "<h3>📋 Available Tables:</h3>";
    echo "<ul>";
    foreach ($tables as $table) {
        echo "<li>$table</li>";
    }
    echo "</ul>";
    
    // Check if branch assignment tables exist
    $usePosBranchProduct = in_array('pos_branch_product', $tables);
    $useProductBranch = in_array('product_branch', $tables);
    
    echo "<h3>🏪 Branch Assignment Tables:</h3>";
    echo "<p><strong>pos_branch_product:</strong> " . ($usePosBranchProduct ? "✅ EXISTS" : "❌ NOT FOUND") . "</p>";
    echo "<p><strong>product_branch:</strong> " . ($useProductBranch ? "✅ EXISTS" : "❌ NOT FOUND") . "</p>";
    
    // Test adding a product with branch assignments
    echo "<h3>🧪 Test: Add Product with Branch Assignments</h3>";
    
    if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['test_add'])) {
        echo "<div style='background: #f0f8ff; padding: 15px; border-radius: 5px; margin: 15px 0;'>";
        echo "<h4>Testing Product Addition...</h4>";
        
        // Simulate adding a product
        $testProductName = "Test Product " . date('Y-m-d H:i:s');
        
        // Insert test product
        $stmt = $pdo->prepare("
            INSERT INTO pos_product (category_id, product_name, product_price, product_status, created_at) 
            VALUES (1, ?, 99.99, 'Available', NOW())
        ");
        $stmt->execute([$testProductName]);
        $productId = $pdo->lastInsertId();
        
        echo "<p>✅ Test product created: <strong>$testProductName</strong> (ID: $productId)</p>";
        
        // Test branch assignments
        $testBranches = [1, 2]; // Assuming these branch IDs exist
        echo "<p>🔗 Testing branch assignments for branches: " . implode(', ', $testBranches) . "</p>";
        
        if ($usePosBranchProduct) {
            $branch_stmt = $pdo->prepare("INSERT INTO pos_branch_product (product_id, branch_id, quantity) VALUES (?, ?, 10)");
        } else {
            $branch_stmt = $pdo->prepare("INSERT INTO product_branch (product_id, branch_id) VALUES (?, ?)");
        }
        
        foreach ($testBranches as $branchId) {
            try {
                if ($usePosBranchProduct) {
                    $branch_stmt->execute([$productId, $branchId]);
                } else {
                    $branch_stmt->execute([$productId, $branchId]);
                }
                echo "<p>✅ Branch $branchId assigned successfully</p>";
            } catch (Exception $e) {
                echo "<p>❌ Failed to assign branch $branchId: " . $e->getMessage() . "</p>";
            }
        }
        
        // Verify assignments
        echo "<h5>🔍 Verifying Assignments:</h5>";
        if ($usePosBranchProduct) {
            $verify_stmt = $pdo->prepare("SELECT branch_id FROM pos_branch_product WHERE product_id = ?");
        } else {
            $verify_stmt = $pdo->prepare("SELECT branch_id FROM product_branch WHERE product_id = ?");
        }
        
        $verify_stmt->execute([$productId]);
        $assignedBranches = $verify_stmt->fetchAll(PDO::FETCH_COLUMN);
        
        if (empty($assignedBranches)) {
            echo "<p>❌ No branch assignments found!</p>";
        } else {
            echo "<p>✅ Found " . count($assignedBranches) . " branch assignments:</p>";
            echo "<ul>";
            foreach ($assignedBranches as $branchId) {
                echo "<li>Branch ID: $branchId</li>";
            }
            echo "</ul>";
        }
        
        // Clean up test product
        echo "<h5>🧹 Cleaning up test data...</h5>";
        if ($usePosBranchProduct) {
            $pdo->exec("DELETE FROM pos_branch_product WHERE product_id = $productId");
        } else {
            $pdo->exec("DELETE FROM product_branch WHERE product_id = $productId");
        }
        $pdo->exec("DELETE FROM pos_product WHERE product_id = $productId");
        echo "<p>✅ Test data cleaned up</p>";
        
        echo "</div>";
    }
    
    // Show test form
    echo "<form method='POST' style='background: #f9f9f9; padding: 20px; border-radius: 10px; margin: 20px 0;'>";
    echo "<h4>🚀 Run Branch Assignment Test</h4>";
    echo "<p>This will create a test product, assign it to branches, verify the assignments, and then clean up.</p>";
    echo "<button type='submit' name='test_add' class='btn btn-primary'>Run Test</button>";
    echo "</form>";
    
    // Show current branch assignments
    echo "<h3>📊 Current Branch Assignments</h3>";
    
    if ($usePosBranchProduct) {
        $stmt = $pdo->query("
            SELECT p.product_name, COUNT(bp.branch_id) as branch_count, 
                   GROUP_CONCAT(b.branch_name SEPARATOR ', ') as branch_names
            FROM pos_product p
            LEFT JOIN pos_branch_product bp ON p.product_id = bp.product_id
            LEFT JOIN pos_branch b ON bp.branch_id = b.branch_id
            GROUP BY p.product_id, p.product_name
            HAVING branch_count > 0
            ORDER BY branch_count DESC
            LIMIT 10
        ");
    } else {
        $stmt = $pdo->query("
            SELECT p.product_name, COUNT(pb.branch_id) as branch_count,
                   GROUP_CONCAT(b.branch_name SEPARATOR ', ') as branch_names
            FROM pos_product p
            LEFT JOIN product_branch pb ON p.product_id = pb.product_id
            LEFT JOIN pos_branch b ON pb.branch_id = b.branch_id
            GROUP BY p.product_id, p.product_name
            HAVING branch_count > 0
            ORDER BY branch_count DESC
            LIMIT 10
        ");
    }
    
    $results = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    if (empty($results)) {
        echo "<p>❌ No products with branch assignments found</p>";
    } else {
        echo "<table border='1' style='border-collapse: collapse; width: 100%;'>";
        echo "<tr><th>Product Name</th><th>Branch Count</th><th>Branch Names</th></tr>";
        foreach ($results as $row) {
            echo "<tr>";
            echo "<td>{$row['product_name']}</td>";
            echo "<td>{$row['branch_count']}</td>";
            echo "<td>{$row['branch_names']}</td>";
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
table { margin: 20px 0; }
th, td { padding: 8px; text-align: left; }
th { background-color: #f2f2f2; }
.btn { padding: 10px 20px; border: none; border-radius: 5px; cursor: pointer; }
.btn-primary { background-color: #007bff; color: white; }
.btn-primary:hover { background-color: #0056b3; }
</style>
