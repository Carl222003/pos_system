<?php
require_once 'db_connect.php';

echo "Fixing existing products branch assignments...\n";

try {
    // Get all active branches
    $branches = $pdo->query("SELECT branch_id FROM pos_branch WHERE status = 'Active'")->fetchAll(PDO::FETCH_COLUMN);
    
    if (empty($branches)) {
        echo "No active branches found.\n";
        exit;
    }
    
    echo "Found " . count($branches) . " active branches.\n";
    
    // Check which table to use
    $tables = $pdo->query("SHOW TABLES")->fetchAll(PDO::FETCH_COLUMN);
    $usePosBranchProduct = in_array('pos_branch_product', $tables);
    
    if ($usePosBranchProduct) {
        echo "Using pos_branch_product table.\n";
        
        // Get all products that don't have branch assignments
        $stmt = $pdo->prepare("
            SELECT p.product_id, p.product_quantity 
            FROM pos_product p 
            LEFT JOIN pos_branch_product bp ON p.product_id = bp.product_id 
            WHERE bp.product_id IS NULL
        ");
        $stmt->execute();
        $products = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        if (empty($products)) {
            echo "All products already have branch assignments.\n";
        } else {
            echo "Found " . count($products) . " products without branch assignments.\n";
            
            $insert_stmt = $pdo->prepare("INSERT INTO pos_branch_product (product_id, branch_id, quantity) VALUES (?, ?, ?)");
            
            foreach ($products as $product) {
                foreach ($branches as $branch_id) {
                    try {
                        $insert_stmt->execute([$product['product_id'], $branch_id, $product['product_quantity'] ?: 10]);
                        echo "Assigned product ID " . $product['product_id'] . " to branch ID " . $branch_id . "\n";
                    } catch (PDOException $e) {
                        if ($e->getCode() != 23000) { // Not duplicate key error
                            echo "Error assigning product " . $product['product_id'] . " to branch " . $branch_id . ": " . $e->getMessage() . "\n";
                        }
                    }
                }
            }
        }
    } else {
        echo "Using product_branch table.\n";
        
        // Get all products that don't have branch assignments
        $stmt = $pdo->prepare("
            SELECT p.product_id 
            FROM pos_product p 
            LEFT JOIN product_branch pb ON p.product_id = pb.product_id 
            WHERE pb.product_id IS NULL
        ");
        $stmt->execute();
        $products = $stmt->fetchAll(PDO::FETCH_COLUMN);
        
        if (empty($products)) {
            echo "All products already have branch assignments.\n";
        } else {
            echo "Found " . count($products) . " products without branch assignments.\n";
            
            $insert_stmt = $pdo->prepare("INSERT INTO product_branch (product_id, branch_id) VALUES (?, ?)");
            
            foreach ($products as $product_id) {
                foreach ($branches as $branch_id) {
                    try {
                        $insert_stmt->execute([$product_id, $branch_id]);
                        echo "Assigned product ID " . $product_id . " to branch ID " . $branch_id . "\n";
                    } catch (PDOException $e) {
                        if ($e->getCode() != 23000) { // Not duplicate key error
                            echo "Error assigning product " . $product_id . " to branch " . $branch_id . ": " . $e->getMessage() . "\n";
                        }
                    }
                }
            }
        }
    }
    
    echo "Branch assignment fix completed!\n";
    
} catch (Exception $e) {
    echo "Error: " . $e->getMessage() . "\n";
}
?>
