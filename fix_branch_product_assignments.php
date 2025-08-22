<?php
require_once 'db_connect.php';
require_once 'auth_function.php';

// Check admin access
checkAdminLogin();

echo "<h2>🔧 Fix Branch-Product Assignments</h2>";
echo "<p>This script will ensure all products are assigned to all branches for proper stockman access.</p>";

try {
    // First, check what tables exist
    $tables = $pdo->query("SHOW TABLES")->fetchAll(PDO::FETCH_COLUMN);
    echo "<h3>📋 Available Tables:</h3>";
    echo "<ul>";
    foreach ($tables as $table) {
        echo "<li>$table</li>";
    }
    echo "</ul>";

    // Check if pos_branch_product table exists
    $branchProductExists = in_array('pos_branch_product', $tables);
    $productBranchExists = in_array('product_branch', $tables);
    
    if (!$branchProductExists && !$productBranchExists) {
        echo "<h3>🚨 Creating pos_branch_product table...</h3>";
        
        $sql = "CREATE TABLE IF NOT EXISTS pos_branch_product (
            branch_product_id INT PRIMARY KEY AUTO_INCREMENT,
            branch_id INT NOT NULL,
            product_id INT NOT NULL,
            quantity INT NOT NULL DEFAULT 0,
            created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
            updated_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
            UNIQUE KEY unique_branch_product (branch_id, product_id),
            FOREIGN KEY (branch_id) REFERENCES pos_branch(branch_id) ON DELETE CASCADE ON UPDATE CASCADE,
            FOREIGN KEY (product_id) REFERENCES pos_product(product_id) ON DELETE CASCADE ON UPDATE CASCADE
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4";
        
        $pdo->exec($sql);
        echo "<p style='color: green;'>✅ pos_branch_product table created successfully!</p>";
        $branchProductExists = true;
    }

    // Get all active branches
    $stmt = $pdo->query("SELECT branch_id, branch_name FROM pos_branch WHERE status = 'Active'");
    $branches = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    echo "<h3>🏢 Active Branches (" . count($branches) . "):</h3>";
    foreach ($branches as $branch) {
        echo "<p>- {$branch['branch_name']} (ID: {$branch['branch_id']})</p>";
    }

    // Get all products
    $stmt = $pdo->query("SELECT product_id, product_name, product_status FROM pos_product");
    $products = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    echo "<h3>🍽️ Available Products (" . count($products) . "):</h3>";
    foreach ($products as $product) {
        echo "<p>- {$product['product_name']} (ID: {$product['product_id']}, Status: {$product['product_status']})</p>";
    }

    if (empty($branches)) {
        echo "<p style='color: red;'>❌ No active branches found! Please create at least one branch first.</p>";
        exit;
    }

    if (empty($products)) {
        echo "<p style='color: red;'>❌ No products found! Please create some products first.</p>";
        exit;
    }

    // Choose which table to use
    $targetTable = $branchProductExists ? 'pos_branch_product' : 'product_branch';
    
    echo "<h3>🔄 Assigning Products to Branches (using $targetTable table)...</h3>";

    $assignedCount = 0;
    $skippedCount = 0;

    foreach ($branches as $branch) {
        foreach ($products as $product) {
            // Check if assignment already exists
            if ($targetTable === 'pos_branch_product') {
                $checkStmt = $pdo->prepare("SELECT COUNT(*) FROM pos_branch_product WHERE branch_id = ? AND product_id = ?");
                $checkStmt->execute([$branch['branch_id'], $product['product_id']]);
            } else {
                $checkStmt = $pdo->prepare("SELECT COUNT(*) FROM product_branch WHERE branch_id = ? AND product_id = ?");
                $checkStmt->execute([$branch['branch_id'], $product['product_id']]);
            }
            
            $exists = $checkStmt->fetchColumn();
            
            if ($exists == 0) {
                // Insert new assignment
                if ($targetTable === 'pos_branch_product') {
                    $insertStmt = $pdo->prepare("INSERT INTO pos_branch_product (branch_id, product_id, quantity) VALUES (?, ?, 10)");
                } else {
                    $insertStmt = $pdo->prepare("INSERT INTO product_branch (branch_id, product_id) VALUES (?, ?)");
                }
                
                try {
                    $insertStmt->execute([$branch['branch_id'], $product['product_id']]);
                    echo "<p style='color: green;'>✅ Assigned '{$product['product_name']}' to '{$branch['branch_name']}'</p>";
                    $assignedCount++;
                } catch (PDOException $e) {
                    echo "<p style='color: orange;'>⚠️ Failed to assign '{$product['product_name']}' to '{$branch['branch_name']}': " . $e->getMessage() . "</p>";
                }
            } else {
                $skippedCount++;
            }
        }
    }

    echo "<h3>📊 Summary:</h3>";
    echo "<p style='color: green;'>✅ New assignments created: $assignedCount</p>";
    echo "<p style='color: blue;'>ℹ️ Existing assignments skipped: $skippedCount</p>";

    // Verify the assignments
    if ($targetTable === 'pos_branch_product') {
        $verifyStmt = $pdo->query("
            SELECT 
                b.branch_name,
                COUNT(bp.product_id) as product_count
            FROM pos_branch b
            LEFT JOIN pos_branch_product bp ON b.branch_id = bp.branch_id
            WHERE b.status = 'Active'
            GROUP BY b.branch_id, b.branch_name
            ORDER BY b.branch_name
        ");
    } else {
        $verifyStmt = $pdo->query("
            SELECT 
                b.branch_name,
                COUNT(pb.product_id) as product_count
            FROM pos_branch b
            LEFT JOIN product_branch pb ON b.branch_id = pb.branch_id
            WHERE b.status = 'Active'
            GROUP BY b.branch_id, b.branch_name
            ORDER BY b.branch_name
        ");
    }
    
    $verification = $verifyStmt->fetchAll(PDO::FETCH_ASSOC);
    
    echo "<h3>✅ Verification - Products per Branch:</h3>";
    foreach ($verification as $result) {
        echo "<p>- {$result['branch_name']}: {$result['product_count']} products assigned</p>";
    }

    echo "<hr>";
    echo "<h3>🎉 Branch-Product Assignment Fix Complete!</h3>";
    echo "<p><a href='stockman_products.php' class='btn btn-primary'>Test Stockman Products Page</a></p>";
    echo "<p><a href='dashboard.php' class='btn btn-secondary'>Back to Dashboard</a></p>";

} catch (PDOException $e) {
    echo "<p style='color: red;'>❌ Database Error: " . $e->getMessage() . "</p>";
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

h2, h3 {
    color: #8B4543;
}

.btn {
    display: inline-block;
    padding: 10px 20px;
    margin: 5px;
    text-decoration: none;
    border-radius: 5px;
    font-weight: bold;
}

.btn-primary {
    background: #8B4543;
    color: white;
}

.btn-secondary {
    background: #6c757d;
    color: white;
}

.btn:hover {
    opacity: 0.8;
}
</style>
