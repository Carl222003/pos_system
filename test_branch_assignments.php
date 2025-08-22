<?php
require_once 'db_connect.php';
require_once 'auth_function.php';

checkAdminLogin();

echo "<h2>🔍 Branch Assignment Debug Test</h2>";

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
    
    if ($usePosBranchProduct) {
        echo "<h3>📊 pos_branch_product Table Contents:</h3>";
        $stmt = $pdo->query("SELECT * FROM pos_branch_product ORDER BY product_id, branch_id LIMIT 20");
        $results = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        if (empty($results)) {
            echo "<p>❌ No records found in pos_branch_product table</p>";
        } else {
            echo "<table border='1' style='border-collapse: collapse; width: 100%;'>";
            echo "<tr><th>product_id</th><th>branch_id</th><th>quantity</th></tr>";
            foreach ($results as $row) {
                echo "<tr>";
                echo "<td>{$row['product_id']}</td>";
                echo "<td>{$row['branch_id']}</td>";
                echo "<td>{$row['quantity'] ?? 'N/A'}</td>";
                echo "</tr>";
            }
            echo "</table>";
        }
    }
    
    if ($useProductBranch) {
        echo "<h3>📊 product_branch Table Contents:</h3>";
        $stmt = $pdo->query("SELECT * FROM product_branch ORDER BY product_id, branch_id LIMIT 20");
        $results = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        if (empty($results)) {
            echo "<p>❌ No records found in product_branch table</p>";
        } else {
            echo "<table border='1' style='border-collapse: collapse; width: 100%;'>";
            echo "<tr><th>product_id</th><th>branch_id</th></tr>";
            foreach ($results as $row) {
                echo "<tr>";
                echo "<td>{$row['product_id']}</td>";
                echo "<td>{$row['branch_id']}</td>";
                echo "</tr>";
            }
            echo "</table>";
        }
    }
    
    // Test the actual query that's used in get_product_assignments.php
    echo "<h3>🧪 Testing Main Query:</h3>";
    
    if ($usePosBranchProduct) {
        $testQuery = "
            SELECT 
                p.product_id,
                p.product_name,
                COALESCE(c.category_name, 'Uncategorized') as category_name,
                GROUP_CONCAT(
                    DISTINCT CONCAT('{\"branch_id\":', b.branch_id, ',\"branch_name\":\"', b.branch_name, '\"}')
                    ORDER BY b.branch_name
                    SEPARATOR ','
                ) as assigned_branches_json
            FROM pos_product p
            LEFT JOIN pos_category c ON p.category_id = c.category_id
            LEFT JOIN pos_branch_product bp ON p.product_id = bp.product_id
            LEFT JOIN pos_branch b ON bp.branch_id = b.branch_id AND b.status = 'Active'
            WHERE p.product_status IS NOT NULL AND p.product_status != ''
            GROUP BY p.product_id, p.product_name, c.category_name
            ORDER BY p.product_name
            LIMIT 5
        ";
    } else {
        $testQuery = "
            SELECT 
                p.product_id,
                p.product_name,
                COALESCE(c.category_name, 'Uncategorized') as category_name,
                GROUP_CONCAT(
                    DISTINCT CONCAT('{\"branch_id\":', b.branch_id, ',\"branch_name\":\"', b.branch_name, '\"}')
                    ORDER BY b.branch_name
                    SEPARATOR ','
                ) as assigned_branches_json
            FROM pos_product p
            LEFT JOIN pos_category c ON p.category_id = c.category_id
            LEFT JOIN product_branch pb ON p.product_id = pb.product_id
            LEFT JOIN pos_branch b ON pb.branch_id = b.branch_id AND b.status = 'Active'
            WHERE p.product_status IS NOT NULL AND p.product_status != ''
            GROUP BY p.product_id, p.product_name, c.category_name
            ORDER BY p.product_name
            LIMIT 5
        ";
    }
    
    echo "<p><strong>Test Query:</strong></p>";
    echo "<pre>" . htmlspecialchars($testQuery) . "</pre>";
    
    $stmt = $pdo->query($testQuery);
    $testResults = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    if (empty($testResults)) {
        echo "<p>❌ No results from test query</p>";
    } else {
        echo "<h4>📋 Test Query Results:</h4>";
        echo "<table border='1' style='border-collapse: collapse; width: 100%;'>";
        echo "<tr><th>Product ID</th><th>Product Name</th><th>Category</th><th>Assigned Branches JSON</th></tr>";
        foreach ($testResults as $row) {
            echo "<tr>";
            echo "<td>{$row['product_id']}</td>";
            echo "<td>{$row['product_name']}</td>";
            echo "<td>{$row['category_name']}</td>";
            echo "<td style='max-width: 300px; word-wrap: break-word;'>";
            if ($row['assigned_branches_json']) {
                echo htmlspecialchars($row['assigned_branches_json']);
                
                // Test JSON parsing
                $branchesJson = '[' . $row['assigned_branches_json'] . ']';
                $decodedBranches = json_decode($branchesJson, true);
                if (is_array($decodedBranches)) {
                    echo "<br><strong>✅ Parsed successfully:</strong> " . count($decodedBranches) . " branches";
                    foreach ($decodedBranches as $branch) {
                        echo "<br>  - {$branch['branch_name']} (ID: {$branch['branch_id']})";
                    }
                } else {
                    echo "<br><strong>❌ JSON parsing failed:</strong> " . json_last_error_msg();
                }
            } else {
                echo "<em>No branches assigned</em>";
            }
            echo "</td>";
            echo "</tr>";
        }
        echo "</table>";
    }
    
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
pre { background-color: #f5f5f5; padding: 10px; border-radius: 5px; overflow-x: auto; }
</style>
