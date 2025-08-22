<?php
require_once 'db_connect.php';

echo "<h1>🔍 Product-Branch Table Analysis</h1>";

try {
    // Check which tables exist
    $tables = $pdo->query("SHOW TABLES")->fetchAll(PDO::FETCH_COLUMN);
    
    $productBranchTables = [];
    foreach ($tables as $table) {
        if (stripos($table, 'product') !== false && stripos($table, 'branch') !== false) {
            $productBranchTables[] = $table;
        }
    }
    
    echo "<h3>📋 Product-Branch Related Tables:</h3>";
    if (empty($productBranchTables)) {
        echo "<p style='color: red;'>❌ No product-branch tables found!</p>";
        exit;
    }
    
    foreach ($productBranchTables as $table) {
        echo "<h4>Table: <code>$table</code></h4>";
        
        // Show table structure
        $structure = $pdo->query("DESCRIBE $table")->fetchAll(PDO::FETCH_ASSOC);
        echo "<table border='1' style='border-collapse: collapse; margin: 10px 0;'>";
        echo "<tr style='background: #f5f5f5;'><th>Column</th><th>Type</th><th>Null</th><th>Key</th><th>Default</th></tr>";
        foreach ($structure as $column) {
            echo "<tr>";
            echo "<td><strong>{$column['Field']}</strong></td>";
            echo "<td>{$column['Type']}</td>";
            echo "<td>{$column['Null']}</td>";
            echo "<td>{$column['Key']}</td>";
            echo "<td>{$column['Default']}</td>";
            echo "</tr>";
        }
        echo "</table>";
        
        // Show data count
        $count = $pdo->query("SELECT COUNT(*) FROM $table")->fetchColumn();
        echo "<p><strong>Records:</strong> $count</p>";
        
        // Show sample data
        if ($count > 0) {
            $sample = $pdo->query("SELECT * FROM $table LIMIT 5")->fetchAll(PDO::FETCH_ASSOC);
            echo "<p><strong>Sample Data (first 5 rows):</strong></p>";
            echo "<table border='1' style='border-collapse: collapse; margin: 10px 0;'>";
            
            if (!empty($sample)) {
                // Header
                echo "<tr style='background: #f5f5f5;'>";
                foreach (array_keys($sample[0]) as $header) {
                    echo "<th>$header</th>";
                }
                echo "</tr>";
                
                // Data
                foreach ($sample as $row) {
                    echo "<tr>";
                    foreach ($row as $value) {
                        echo "<td>" . ($value ?? 'NULL') . "</td>";
                    }
                    echo "</tr>";
                }
            }
            echo "</table>";
        } else {
            echo "<p style='color: orange;'>⚠️ No data in this table</p>";
        }
        
        echo "<hr>";
    }
    
    // Check which table the admin is actually using
    echo "<h3>🔍 Which Table Is The Admin Using?</h3>";
    
    // Check recent products to see which table has assignments
    $recentProducts = $pdo->query("SELECT product_id, product_name FROM pos_product ORDER BY created_at DESC LIMIT 5")->fetchAll(PDO::FETCH_ASSOC);
    
    if (!empty($recentProducts)) {
        echo "<h4>Recent Products and Their Branch Assignments:</h4>";
        
        foreach ($recentProducts as $product) {
            echo "<p><strong>Product:</strong> {$product['product_name']} (ID: {$product['product_id']})</p>";
            
            foreach ($productBranchTables as $table) {
                $assignments = $pdo->prepare("SELECT * FROM $table WHERE product_id = ?");
                $assignments->execute([$product['product_id']]);
                $results = $assignments->fetchAll(PDO::FETCH_ASSOC);
                
                if (!empty($results)) {
                    echo "<ul>";
                    echo "<li><strong>$table:</strong> " . count($results) . " assignments</li>";
                    foreach ($results as $assignment) {
                        $branchId = $assignment['branch_id'] ?? 'N/A';
                        $quantity = $assignment['quantity'] ?? 'N/A';
                        echo "<li style='margin-left: 20px;'>Branch ID: $branchId, Quantity: $quantity</li>";
                    }
                    echo "</ul>";
                } else {
                    echo "<ul><li><strong>$table:</strong> No assignments</li></ul>";
                }
            }
            echo "<hr>";
        }
    }
    
    // Recommendation
    echo "<h3>💡 Recommendation:</h3>";
    $productBranchCount = 0;
    $posBranchProductCount = 0;
    
    if (in_array('product_branch', $productBranchTables)) {
        $productBranchCount = $pdo->query("SELECT COUNT(*) FROM product_branch")->fetchColumn();
    }
    
    if (in_array('pos_branch_product', $productBranchTables)) {
        $posBranchProductCount = $pdo->query("SELECT COUNT(*) FROM pos_branch_product")->fetchColumn();
    }
    
    echo "<p><strong>product_branch table:</strong> $productBranchCount records</p>";
    echo "<p><strong>pos_branch_product table:</strong> $posBranchProductCount records</p>";
    
    if ($productBranchCount > $posBranchProductCount) {
        echo "<div style='background: #d4edda; color: #155724; padding: 15px; border-radius: 5px;'>";
        echo "<p>✅ <strong>Recommendation:</strong> Use <code>product_branch</code> table as it has more data ($productBranchCount vs $posBranchProductCount)</p>";
        echo "</div>";
    } elseif ($posBranchProductCount > $productBranchCount) {
        echo "<div style='background: #d4edda; color: #155724; padding: 15px; border-radius: 5px;'>";
        echo "<p>✅ <strong>Recommendation:</strong> Use <code>pos_branch_product</code> table as it has more data ($posBranchProductCount vs $productBranchCount)</p>";
        echo "</div>";
    } else {
        echo "<div style='background: #fff3cd; color: #856404; padding: 15px; border-radius: 5px;'>";
        echo "<p>⚠️ <strong>Both tables have equal data.</strong> Need to check which one the admin interface is actually using.</p>";
        echo "</div>";
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
