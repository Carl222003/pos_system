<?php
require_once 'db_connect.php';
require_once 'auth_function.php';

// Only admin can run this
checkAdminLogin();

echo "<h2>🔗 Assigning Cashiers to Branches</h2>";

try {
    // Get all active branches
    $stmt = $pdo->query("SELECT branch_id, branch_name, branch_code FROM pos_branch WHERE status = 'Active' ORDER BY branch_name");
    $branches = $stmt->fetchAll(PDO::FETCH_COLUMN);
    
    if (empty($branches)) {
        echo "<p style='color: red;'>❌ No active branches found!</p>";
        exit;
    }
    
    // Get cashiers without branch assignments
    $stmt = $pdo->query("SELECT user_id, user_name, user_email FROM pos_user WHERE branch_id IS NULL AND user_type = 'Cashier' ORDER BY user_name");
    $cashiers = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    if (empty($cashiers)) {
        echo "<p style='color: green;'>✅ All cashiers already have branch assignments!</p>";
    } else {
        echo "<h3>📋 Cashiers without Branch Assignments:</h3>";
        echo "<ul>";
        foreach ($cashiers as $cashier) {
            echo "<li><strong>{$cashier['user_name']}</strong> ({$cashier['user_email']}) - ID: {$cashier['user_id']}</li>";
        }
        echo "</ul>";
        
        // Assign all cashiers to the first branch for testing
        $firstBranchId = $branches[0]['branch_id'];
        $firstBranchName = $branches[0]['branch_name'];
        
        $updateStmt = $pdo->prepare("UPDATE pos_user SET branch_id = ? WHERE user_id = ? AND user_type = 'Cashier'");
        
        $assignedCount = 0;
        foreach ($cashiers as $cashier) {
            $updateStmt->execute([$firstBranchId, $cashier['user_id']]);
            $assignedCount++;
        }
        
        echo "<p style='color: green;'>✅ Successfully assigned $assignedCount cashiers to branch: <strong>$firstBranchName</strong></p>";
    }
    
    // Show current assignments
    echo "<h3>🔍 Current Cashier Branch Assignments:</h3>";
    $stmt = $pdo->query("
        SELECT 
            u.user_id, 
            u.user_name, 
            u.user_email, 
            u.branch_id, 
            b.branch_name,
            b.branch_code
        FROM pos_user u 
        LEFT JOIN pos_branch b ON u.branch_id = b.branch_id 
        WHERE u.user_type = 'Cashier' 
        ORDER BY b.branch_name, u.user_name
    ");
    $assignments = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    if (empty($assignments)) {
        echo "<p style='color: orange;'>⚠️ No cashiers found in the database.</p>";
    } else {
        echo "<table border='1' style='border-collapse: collapse; width: 100%; margin-top: 1rem;'>";
        echo "<tr style='background: #f8f9fa;'>";
        echo "<th style='padding: 0.5rem;'>Cashier ID</th>";
        echo "<th style='padding: 0.5rem;'>Name</th>";
        echo "<th style='padding: 0.5rem;'>Email</th>";
        echo "<th style='padding: 0.5rem;'>Branch ID</th>";
        echo "<th style='padding: 0.5rem;'>Branch Name</th>";
        echo "<th style='padding: 0.5rem;'>Branch Code</th>";
        echo "</tr>";
        
        foreach ($assignments as $assignment) {
            $branchInfo = $assignment['branch_name'] ? 
                "{$assignment['branch_name']} ({$assignment['branch_code']})" : 
                '<span style="color: red;">❌ No Branch Assigned</span>';
            
            echo "<tr>";
            echo "<td style='padding: 0.5rem;'>{$assignment['user_id']}</td>";
            echo "<td style='padding: 0.5rem;'>{$assignment['user_name']}</td>";
            echo "<td style='padding: 0.5rem;'>{$assignment['user_email']}</td>";
            echo "<td style='padding: 0.5rem;'>{$assignment['branch_id']}</td>";
            echo "<td style='padding: 0.5rem;'>{$branchInfo}</td>";
            echo "<td style='padding: 0.5rem;'>{$assignment['branch_code']}</td>";
            echo "</tr>";
        }
        echo "</table>";
    }
    
    // Show available branches
    echo "<h3>🏢 Available Branches:</h3>";
    echo "<ul>";
    foreach ($branches as $branch) {
        echo "<li><strong>{$branch['branch_name']}</strong> ({$branch['branch_code']}) - ID: {$branch['branch_id']}</li>";
    }
    echo "</ul>";
    
    echo "<hr>";
    echo "<h3>🚀 Next Steps:</h3>";
    echo "<ol>";
    echo "<li>✅ Cashiers are now assigned to branches</li>";
    echo "<li>🔗 Products are connected to branches via the assignment system</li>";
    echo "<li>👤 Cashiers can now access <code>cashier_products.php</code> to see only their branch's products</li>";
    echo "<li>🔒 Each cashier will only see products assigned to their specific branch</li>";
    echo "</ol>";
    
    echo "<p><strong>Access URLs:</strong></p>";
    echo "<ul>";
    echo "<li><strong>Cashier Products:</strong> <code>cashier_products.php</code></li>";
    echo "<li><strong>Branch Assignment Management:</strong> <code>simple_branch_product_connect.php</code></li>";
    echo "</ul>";
    
} catch (Exception $e) {
    echo "<p style='color: red;'>❌ Error: " . htmlspecialchars($e->getMessage()) . "</p>";
}
?>
