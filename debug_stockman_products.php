<?php
require_once 'db_connect.php';
require_once 'auth_function.php';

// Allow access for debugging
if (!isset($_SESSION['user_logged_in'])) {
    // Simulate a stockman session for debugging
    session_start();
    $_SESSION['user_logged_in'] = true;
    $_SESSION['user_type'] = 'Stockman';
    $_SESSION['user_id'] = 1; // Change this to actual stockman user ID
    $_SESSION['branch_id'] = 1; // Change this to actual branch ID
}

echo "<h2>🔍 Stockman Products Debug Information</h2>";

try {
    // Check session info
    echo "<h3>📋 Session Information:</h3>";
    echo "<p>User ID: " . ($_SESSION['user_id'] ?? 'Not set') . "</p>";
    echo "<p>User Type: " . ($_SESSION['user_type'] ?? 'Not set') . "</p>";
    echo "<p>Branch ID: " . ($_SESSION['branch_id'] ?? 'Not set') . "</p>";

    // Get user's branch info
    if (isset($_SESSION['user_id'])) {
        $stmt = $pdo->prepare("SELECT * FROM pos_user WHERE user_id = ?");
        $stmt->execute([$_SESSION['user_id']]);
        $user = $stmt->fetch(PDO::FETCH_ASSOC);
        
        echo "<h3>👤 User Information:</h3>";
        if ($user) {
            echo "<p>Name: {$user['user_name']}</p>";
            echo "<p>Type: {$user['user_type']}</p>";
            echo "<p>Branch ID in DB: {$user['branch_id']}</p>";
        } else {
            echo "<p style='color: red;'>User not found in database!</p>";
        }
    }

    // Check available tables
    echo "<h3>📊 Available Tables:</h3>";
    $tables = $pdo->query("SHOW TABLES")->fetchAll(PDO::FETCH_COLUMN);
    $relevantTables = array_filter($tables, function($table) {
        return strpos($table, 'product') !== false || strpos($table, 'branch') !== false;
    });
    
    foreach ($relevantTables as $table) {
        $count = $pdo->query("SELECT COUNT(*) FROM $table")->fetchColumn();
        echo "<p>- $table: $count records</p>";
    }

    // Check branch-product relationships
    echo "<h3>🔗 Branch-Product Relationships:</h3>";
    
    if (in_array('pos_branch_product', $tables)) {
        echo "<h4>pos_branch_product table:</h4>";
        $stmt = $pdo->query("
            SELECT 
                b.branch_name,
                p.product_name,
                bp.quantity
            FROM pos_branch_product bp
            JOIN pos_branch b ON bp.branch_id = b.branch_id
            JOIN pos_product p ON bp.product_id = p.product_id
            ORDER BY b.branch_name, p.product_name
        ");
        $assignments = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        if (empty($assignments)) {
            echo "<p style='color: red;'>❌ No products assigned to any branches!</p>";
        } else {
            foreach ($assignments as $assignment) {
                echo "<p>- {$assignment['branch_name']}: {$assignment['product_name']} (Qty: {$assignment['quantity']})</p>";
            }
        }
    }
    
    if (in_array('product_branch', $tables)) {
        echo "<h4>product_branch table:</h4>";
        $stmt = $pdo->query("
            SELECT 
                b.branch_name,
                p.product_name
            FROM product_branch pb
            JOIN pos_branch b ON pb.branch_id = b.branch_id
            JOIN pos_product p ON pb.product_id = p.product_id
            ORDER BY b.branch_name, p.product_name
        ");
        $assignments = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        if (empty($assignments)) {
            echo "<p style='color: red;'>❌ No products assigned to any branches!</p>";
        } else {
            foreach ($assignments as $assignment) {
                echo "<p>- {$assignment['branch_name']}: {$assignment['product_name']}</p>";
            }
        }
    }

    // Check products
    echo "<h3>🍽️ Available Products:</h3>";
    $stmt = $pdo->query("SELECT * FROM pos_product ORDER BY product_name");
    $products = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    foreach ($products as $product) {
        echo "<p>- {$product['product_name']} (ID: {$product['product_id']}, Status: {$product['product_status']})</p>";
    }

    // Check branches
    echo "<h3>🏢 Available Branches:</h3>";
    $stmt = $pdo->query("SELECT * FROM pos_branch ORDER BY branch_name");
    $branches = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    foreach ($branches as $branch) {
        echo "<p>- {$branch['branch_name']} (ID: {$branch['branch_id']}, Status: {$branch['status']})</p>";
    }

    // Test the actual API call
    echo "<h3>🧪 Testing get_stockman_products.php API:</h3>";
    
    // Simulate the API call
    $user_id = $_SESSION['user_id'];
    $branch_id = $_SESSION['branch_id'] ?? null;

    if (!$branch_id) {
        $stmt = $pdo->prepare('SELECT branch_id FROM pos_user WHERE user_id = ?');
        $stmt->execute([$user_id]);
        $branch_id = $stmt->fetchColumn();
    }

    echo "<p>Using Branch ID: $branch_id</p>";

    if ($branch_id) {
        // Check what the API would return
        $stmt = $pdo->prepare("
            SELECT 
                p.product_id,
                p.product_name,
                p.product_status,
                c.category_name
            FROM pos_product p
            LEFT JOIN pos_category c ON p.category_id = c.category_id
            LEFT JOIN pos_branch_product bp ON p.product_id = bp.product_id AND bp.branch_id = ?
            WHERE bp.product_id IS NOT NULL OR NOT EXISTS (SELECT 1 FROM pos_branch_product LIMIT 1)
            ORDER BY p.product_name
        ");
        $stmt->execute([$branch_id]);
        $apiProducts = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        echo "<p>API would return " . count($apiProducts) . " products:</p>";
        foreach ($apiProducts as $product) {
            echo "<p>- {$product['product_name']} ({$product['product_status']})</p>";
        }
    }

    echo "<hr>";
    echo "<h3>🛠️ Recommended Actions:</h3>";
    
    if (in_array('pos_branch_product', $tables)) {
        $assignmentCount = $pdo->query("SELECT COUNT(*) FROM pos_branch_product")->fetchColumn();
        if ($assignmentCount == 0) {
            echo "<p style='color: red;'>❌ No branch-product assignments found!</p>";
            echo "<p>👉 <a href='fix_branch_product_assignments.php'>Run the Fix Script</a></p>";
        } else {
            echo "<p style='color: green;'>✅ Branch-product assignments exist</p>";
        }
    } else {
        echo "<p style='color: orange;'>⚠️ pos_branch_product table doesn't exist</p>";
        echo "<p>👉 <a href='fix_branch_product_assignments.php'>Run the Fix Script</a></p>";
    }

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

a {
    color: #8B4543;
    text-decoration: none;
    font-weight: bold;
}

a:hover {
    text-decoration: underline;
}
</style>
