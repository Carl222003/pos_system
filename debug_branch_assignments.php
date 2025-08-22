<?php
require_once 'db_connect.php';
require_once 'auth_function.php';

checkAdminLogin();

echo "<h2>🔍 Debugging Branch Assignment Issues</h2>";

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
    
    // Check table structures
    if ($usePosBranchProduct) {
        echo "<h3>📊 pos_branch_product Table Structure:</h3>";
        $stmt = $pdo->query("DESCRIBE pos_branch_product");
        $columns = $stmt->fetchAll(PDO::FETCH_ASSOC);
        echo "<table border='1' style='border-collapse: collapse; width: 100%;'>";
        echo "<tr><th>Field</th><th>Type</th><th>Null</th><th>Key</th><th>Default</th><th>Extra</th></tr>";
        foreach ($columns as $col) {
            echo "<tr>";
            echo "<td>{$col['Field']}</td>";
            echo "<td>{$col['Type']}</td>";
            echo "<td>{$col['Null']}</td>";
            echo "<td>{$col['Key']}</td>";
            echo "<td>{$col['Default']}</td>";
            echo "<td>{$col['Extra']}</td>";
            echo "</tr>";
        }
        echo "</table>";
        
        // Check sample data
        echo "<h3>📊 pos_branch_product Sample Data:</h3>";
        $stmt = $pdo->query("SELECT * FROM pos_branch_product LIMIT 10");
        $data = $stmt->fetchAll(PDO::FETCH_ASSOC);
        if (empty($data)) {
            echo "<p>❌ No data found in pos_branch_product table</p>";
        } else {
            echo "<table border='1' style='border-collapse: collapse; width: 100%;'>";
            echo "<tr><th>branch_product_id</th><th>branch_id</th><th>product_id</th><th>quantity</th></tr>";
            foreach ($data as $row) {
                echo "<tr>";
                echo "<td>{$row['branch_product_id']}</td>";
                echo "<td>{$row['branch_id']}</td>";
                echo "<td>{$row['product_id']}</td>";
                echo "<td>{$row['quantity']}</td>";
                echo "</tr>";
            }
            echo "</table>";
        }
    }
    
    if ($useProductBranch) {
        echo "<h3>📊 product_branch Table Structure:</h3>";
        $stmt = $pdo->query("DESCRIBE product_branch");
        $columns = $stmt->fetchAll(PDO::FETCH_ASSOC);
        echo "<table border='1' style='border-collapse: collapse; width: 100%;'>";
        echo "<tr><th>Field</th><th>Type</th><th>Null</th><th>Key</th><th>Default</th><th>Extra</th></tr>";
        foreach ($columns as $col) {
            echo "<tr>";
            echo "<td>{$col['Field']}</td>";
            echo "<td>{$col['Type']}</td>";
            echo "<td>{$col['Null']}</td>";
            echo "<td>{$col['Key']}</td>";
            echo "<td>{$col['Default']}</td>";
            echo "<td>{$col['Extra']}</td>";
            echo "</tr>";
        }
        echo "</table>";
        
        // Check sample data
        echo "<h3>📊 product_branch Sample Data:</h3>";
        $stmt = $pdo->query("SELECT * FROM product_branch LIMIT 10");
        $data = $stmt->fetchAll(PDO::FETCH_ASSOC);
        if (empty($data)) {
            echo "<p>❌ No data found in product_branch table</p>";
        } else {
            echo "<table border='1' style='border-collapse: collapse; width: 100%;'>";
            echo "<tr><th>product_id</th><th>branch_id</th></tr>";
            foreach ($data as $row) {
                echo "<tr>";
                echo "<td>{$row['product_id']}</td>";
                echo "<td>{$row['branch_id']}</td>";
                echo "</tr>";
            }
            echo "</table>";
        }
    }
    
    // Check products and their branch assignments
    echo "<h3>🔗 Current Product Branch Assignments:</h3>";
    
    if ($usePosBranchProduct) {
        $stmt = $pdo->query("
            SELECT 
                p.product_id,
                p.product_name,
                COUNT(bp.branch_id) as assigned_branches,
                GROUP_CONCAT(b.branch_name SEPARATOR ', ') as branch_names
            FROM pos_product p
            LEFT JOIN pos_branch_product bp ON p.product_id = bp.product_id
            LEFT JOIN pos_branch b ON bp.branch_id = b.branch_id
            GROUP BY p.product_id, p.product_name
            ORDER BY p.product_name
            LIMIT 20
        ");
    } else {
        $stmt = $pdo->query("
            SELECT 
                p.product_id,
                p.product_name,
                COUNT(pb.branch_id) as assigned_branches,
                GROUP_CONCAT(b.branch_name SEPARATOR ', ') as branch_names
            FROM pos_product p
            LEFT JOIN product_branch pb ON p.product_id = pb.product_id
            LEFT JOIN pos_branch b ON pb.branch_id = b.branch_id
            GROUP BY p.product_id, p.product_name
            ORDER BY p.product_name
            LIMIT 20
        ");
    }
    
    $results = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    if (empty($results)) {
        echo "<p>❌ No products found</p>";
    } else {
        echo "<table border='1' style='border-collapse: collapse; width: 100%;'>";
        echo "<tr><th>Product ID</th><th>Product Name</th><th>Assigned Branches</th><th>Branch Names</th></tr>";
        foreach ($results as $row) {
            $branchCount = $row['assigned_branches'] ?: 0;
            $branchNames = $row['branch_names'] ?: 'None';
            $statusClass = $branchCount > 0 ? 'style="background-color: #d4edda;"' : 'style="background-color: #f8d7da;"';
            
            echo "<tr $statusClass>";
            echo "<td>{$row['product_id']}</td>";
            echo "<td>{$row['product_name']}</td>";
            echo "<td>{$branchCount}</td>";
            echo "<td>{$branchNames}</td>";
            echo "</tr>";
        }
        echo "</table>";
    }
    
    // Test form submission simulation
    echo "<h3>🧪 Test Form Submission:</h3>";
    if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['test_form'])) {
        echo "<div style='background: #f0f8ff; padding: 15px; border-radius: 5px; margin: 15px 0;'>";
        echo "<h4>Testing Form Data:</h4>";
        
        echo "<p><strong>POST Data:</strong></p>";
        echo "<pre>" . print_r($_POST, true) . "</pre>";
        
        echo "<p><strong>FILES Data:</strong></p>";
        echo "<pre>" . print_r($_FILES, true) . "</pre>";
        
        // Simulate the branch assignment logic
        if (isset($_POST['test_branches']) && is_array($_POST['test_branches'])) {
            echo "<p><strong>Branch IDs Selected:</strong> " . implode(', ', $_POST['test_branches']) . "</p>";
            
            // Check which table to use
            $tables = $pdo->query("SHOW TABLES")->fetchAll(PDO::FETCH_COLUMN);
            $usePosBranchProduct = in_array('pos_branch_product', $tables);
            
            echo "<p><strong>Table to use:</strong> " . ($usePosBranchProduct ? 'pos_branch_product' : 'product_branch') . "</p>";
            
            // Test inserting into the correct table
            $testProductId = 1; // Use existing product ID for testing
            
            if ($usePosBranchProduct) {
                $stmt = $pdo->prepare("INSERT INTO pos_branch_product (product_id, branch_id, quantity) VALUES (?, ?, 10)");
            } else {
                $stmt = $pdo->prepare("INSERT INTO product_branch (product_id, branch_id) VALUES (?, ?)");
            }
            
            foreach ($_POST['test_branches'] as $branchId) {
                try {
                    if ($usePosBranchProduct) {
                        $stmt->execute([$testProductId, $branchId]);
                    } else {
                        $stmt->execute([$testProductId, $branchId]);
                    }
                    echo "<p>✅ Successfully inserted branch $branchId for product $testProductId</p>";
                } catch (Exception $e) {
                    echo "<p>❌ Failed to insert branch $branchId: " . $e->getMessage() . "</p>";
                }
            }
        }
        
        echo "</div>";
    }
    
    // Show test form
    echo "<form method='POST' style='background: #f9f9f9; padding: 20px; border-radius: 10px; margin: 20px 0;'>";
    echo "<h4>🚀 Test Form Submission</h4>";
    echo "<p>Select some branches and submit to test the form data:</p>";
    
    // Get available branches
    $branches = $pdo->query("SELECT branch_id, branch_name FROM pos_branch WHERE status = 'Active' ORDER BY branch_name")->fetchAll(PDO::FETCH_ASSOC);
    
    foreach ($branches as $branch) {
        echo "<div style='margin: 10px 0;'>";
        echo "<input type='checkbox' name='test_branches[]' value='{$branch['branch_id']}' id='test_branch_{$branch['branch_id']}'>";
        echo "<label for='test_branch_{$branch['branch_id']}'>{$branch['branch_name']} (ID: {$branch['branch_id']})</label>";
        echo "</div>";
    }
    
    echo "<button type='submit' name='test_form' class='btn btn-primary'>Test Form Submission</button>";
    echo "</form>";
    
} catch (Exception $e) {
    echo "<p style='color: red;'>❌ Error: " . $e->getMessage() . "</p>";
    echo "<p><strong>Stack trace:</strong></p>";
    echo "<pre>" . $e->getTraceAsString() . "</pre>";
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
pre { background-color: #f5f5f5; padding: 10px; border-radius: 5px; overflow-x: auto; }
</style>
