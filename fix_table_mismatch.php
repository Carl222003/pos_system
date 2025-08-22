<?php
require_once 'db_connect.php';
require_once 'auth_function.php';

// Allow admin access for debugging
checkAdminLogin();

echo "<h2>🔧 Fix Branch-Product Table Mismatch</h2>";
echo "<p>This script will identify and fix the table mismatch issue between product assignment and stockman product display.</p>";

try {
    // Check what tables exist
    $tables = $pdo->query("SHOW TABLES")->fetchAll(PDO::FETCH_COLUMN);
    
    $hasPosBranchProduct = in_array('pos_branch_product', $tables);
    $hasProductBranch = in_array('product_branch', $tables);
    
    echo "<h3>📋 Table Status:</h3>";
    echo "<p>pos_branch_product exists: " . ($hasPosBranchProduct ? "✅ Yes" : "❌ No") . "</p>";
    echo "<p>product_branch exists: " . ($hasProductBranch ? "✅ Yes" : "❌ No") . "</p>";
    
    // Check data in each table
    if ($hasPosBranchProduct) {
        $count = $pdo->query("SELECT COUNT(*) FROM pos_branch_product")->fetchColumn();
        echo "<p>pos_branch_product records: $count</p>";
        
        if ($count > 0) {
            echo "<h4>pos_branch_product sample data:</h4>";
            $sample = $pdo->query("SELECT * FROM pos_branch_product LIMIT 3")->fetchAll(PDO::FETCH_ASSOC);
            echo "<pre>" . print_r($sample, true) . "</pre>";
        }
    }
    
    if ($hasProductBranch) {
        $count = $pdo->query("SELECT COUNT(*) FROM product_branch")->fetchColumn();
        echo "<p>product_branch records: $count</p>";
        
        if ($count > 0) {
            echo "<h4>product_branch sample data:</h4>";
            $sample = $pdo->query("SELECT * FROM product_branch LIMIT 3")->fetchAll(PDO::FETCH_ASSOC);
            echo "<pre>" . print_r($sample, true) . "</pre>";
        }
    }
    
    echo "<hr>";
    echo "<h3>🔍 Problem Analysis:</h3>";
    
    // Analyze the problem
    if (!$hasPosBranchProduct && !$hasProductBranch) {
        echo "<p style='color: red;'>❌ Neither table exists! This is why no products show up.</p>";
        echo "<h4>Solution: Create pos_branch_product table</h4>";
    } elseif ($hasProductBranch && !$hasPosBranchProduct) {
        echo "<p style='color: orange;'>⚠️ Only product_branch exists, but stockman query looks for pos_branch_product first.</p>";
        echo "<h4>Solution: Either create pos_branch_product or modify the query logic</h4>";
    } elseif ($hasPosBranchProduct && !$hasProductBranch) {
        echo "<p style='color: orange;'>⚠️ Only pos_branch_product exists, but product assignment uses product_branch.</p>";
        echo "<h4>Solution: Modify product assignment to use pos_branch_product</h4>";
    } else {
        echo "<p style='color: blue;'>ℹ️ Both tables exist. Need to check which one has data and is being used.</p>";
    }
    
    echo "<hr>";
    echo "<h3>🛠️ Automated Fix Options:</h3>";
    
    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        if (isset($_POST['create_pos_branch_product'])) {
            // Create pos_branch_product table
            echo "<h4>Creating pos_branch_product table...</h4>";
            
            $sql = "CREATE TABLE IF NOT EXISTS pos_branch_product (
                branch_product_id INT PRIMARY KEY AUTO_INCREMENT,
                branch_id INT NOT NULL,
                product_id INT NOT NULL,
                quantity INT NOT NULL DEFAULT 10,
                created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
                updated_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
                UNIQUE KEY unique_branch_product (branch_id, product_id),
                FOREIGN KEY (branch_id) REFERENCES pos_branch(branch_id) ON DELETE CASCADE,
                FOREIGN KEY (product_id) REFERENCES pos_product(product_id) ON DELETE CASCADE
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4";
            
            $pdo->exec($sql);
            echo "<p style='color: green;'>✅ pos_branch_product table created!</p>";
            $hasPosBranchProduct = true;
        }
        
        if (isset($_POST['migrate_data'])) {
            // Migrate data from product_branch to pos_branch_product
            echo "<h4>Migrating data from product_branch to pos_branch_product...</h4>";
            
            if (!$hasPosBranchProduct) {
                echo "<p style='color: red;'>❌ pos_branch_product table doesn't exist. Create it first.</p>";
            } elseif (!$hasProductBranch) {
                echo "<p style='color: red;'>❌ product_branch table doesn't exist. Nothing to migrate.</p>";
            } else {
                $migrateSQL = "
                    INSERT IGNORE INTO pos_branch_product (branch_id, product_id, quantity)
                    SELECT branch_id, product_id, 10 as quantity
                    FROM product_branch
                ";
                
                $result = $pdo->exec($migrateSQL);
                echo "<p style='color: green;'>✅ Migrated $result records from product_branch to pos_branch_product</p>";
            }
        }
        
        if (isset($_POST['assign_all_products'])) {
            // Assign all products to all branches in pos_branch_product
            echo "<h4>Assigning all products to all branches...</h4>";
            
            if (!$hasPosBranchProduct) {
                echo "<p style='color: red;'>❌ pos_branch_product table doesn't exist. Create it first.</p>";
            } else {
                $assignSQL = "
                    INSERT IGNORE INTO pos_branch_product (branch_id, product_id, quantity)
                    SELECT b.branch_id, p.product_id, 10 as quantity
                    FROM pos_branch b
                    CROSS JOIN pos_product p
                    WHERE b.status = 'Active'
                ";
                
                $result = $pdo->exec($assignSQL);
                echo "<p style='color: green;'>✅ Created $result new product-branch assignments</p>";
            }
        }
        
        if (isset($_POST['test_stockman_query'])) {
            // Test the stockman query
            echo "<h4>Testing stockman query...</h4>";
            
            // Simulate a stockman session
            $testBranchId = 1; // Use first branch for testing
            
            $testSQL = "
                SELECT 
                    p.product_id,
                    p.product_name,
                    p.product_status,
                    bp.quantity as branch_quantity
                FROM pos_product p
                INNER JOIN pos_branch_product bp ON p.product_id = bp.product_id
                WHERE bp.branch_id = ?
                ORDER BY p.product_name
            ";
            
            $stmt = $pdo->prepare($testSQL);
            $stmt->execute([$testBranchId]);
            $results = $stmt->fetchAll(PDO::FETCH_ASSOC);
            
            echo "<p>Query returned " . count($results) . " products for branch ID $testBranchId:</p>";
            if (!empty($results)) {
                echo "<ul>";
                foreach ($results as $product) {
                    echo "<li>{$product['product_name']} (Status: {$product['product_status']}, Qty: {$product['branch_quantity']})</li>";
                }
                echo "</ul>";
                echo "<p style='color: green;'>✅ Query is working! Products should show up for stockmen.</p>";
            } else {
                echo "<p style='color: red;'>❌ No products found. The assignments might not be working.</p>";
            }
        }
        
        echo "<hr>";
        echo "<p><strong>After making changes, test the stockman products page:</strong></p>";
        echo "<p><a href='stockman_products.php' style='background: #8B4543; color: white; padding: 10px 20px; text-decoration: none; border-radius: 5px;'>Test Stockman Products Page</a></p>";
    }
    
    // Show form options
    echo "<form method='POST'>";
    
    if (!$hasPosBranchProduct) {
        echo "<div style='margin: 10px 0; padding: 15px; border: 1px solid #ddd; border-radius: 5px;'>";
        echo "<h4>Step 1: Create pos_branch_product table</h4>";
        echo "<p>This table is needed for stockman product queries.</p>";
        echo "<button type='submit' name='create_pos_branch_product' style='background: #007bff; color: white; padding: 10px 20px; border: none; border-radius: 5px; cursor: pointer;'>Create pos_branch_product Table</button>";
        echo "</div>";
    }
    
    if ($hasProductBranch && $hasPosBranchProduct) {
        echo "<div style='margin: 10px 0; padding: 15px; border: 1px solid #ddd; border-radius: 5px;'>";
        echo "<h4>Step 2: Migrate existing assignments</h4>";
        echo "<p>Copy assignments from product_branch to pos_branch_product.</p>";
        echo "<button type='submit' name='migrate_data' style='background: #28a745; color: white; padding: 10px 20px; border: none; border-radius: 5px; cursor: pointer;'>Migrate Data</button>";
        echo "</div>";
    }
    
    if ($hasPosBranchProduct) {
        echo "<div style='margin: 10px 0; padding: 15px; border: 1px solid #ddd; border-radius: 5px;'>";
        echo "<h4>Step 3: Assign all products to all branches</h4>";
        echo "<p>Ensure all products are available to all branches.</p>";
        echo "<button type='submit' name='assign_all_products' style='background: #ffc107; color: black; padding: 10px 20px; border: none; border-radius: 5px; cursor: pointer;'>Assign All Products</button>";
        echo "</div>";
        
        echo "<div style='margin: 10px 0; padding: 15px; border: 1px solid #ddd; border-radius: 5px;'>";
        echo "<h4>Step 4: Test the query</h4>";
        echo "<p>Test if the stockman query will return products.</p>";
        echo "<button type='submit' name='test_stockman_query' style='background: #17a2b8; color: white; padding: 10px 20px; border: none; border-radius: 5px; cursor: pointer;'>Test Query</button>";
        echo "</div>";
    }
    
    echo "</form>";
    
} catch (Exception $e) {
    echo "<p style='color: red;'>❌ Error: " . $e->getMessage() . "</p>";
}
?>

<style>
body {
    font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
    max-width: 1200px;
    margin: 0 auto;
    padding: 20px;
    background: #f5f5f5;
}

h2, h3, h4 {
    color: #8B4543;
}

pre {
    background: #f8f9fa;
    padding: 10px;
    border-radius: 5px;
    overflow-x: auto;
    border: 1px solid #dee2e6;
}

button:hover {
    opacity: 0.9;
}
</style>
