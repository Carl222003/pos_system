<?php
require_once 'db_connect.php';
require_once 'auth_function.php';

// Only admin can run this
checkAdminLogin();

echo "<h2>🔗 Simple Cashier-Branch Assignment</h2>";

try {
    // Get all active branches
    $stmt = $pdo->query("SELECT branch_id, branch_name, branch_code FROM pos_branch WHERE status = 'Active' ORDER BY branch_name");
    $branches = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    if (empty($branches)) {
        echo "<p style='color: red;'>❌ No active branches found!</p>";
        exit;
    }
    
    // Get all cashiers
    $stmt = $pdo->query("SELECT user_id, user_name, user_email, branch_id FROM pos_user WHERE user_type = 'Cashier' ORDER BY user_name");
    $cashiers = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    if (empty($cashiers)) {
        echo "<p style='color: orange;'>⚠️ No cashiers found in the database.</p>";
        exit;
    }
    
    echo "<h3>🏢 Available Branches:</h3>";
    echo "<ul>";
    foreach ($branches as $branch) {
        echo "<li><strong>{$branch['branch_name']}</strong> ({$branch['branch_code']}) - ID: {$branch['branch_id']}</li>";
    }
    echo "</ul>";
    
    echo "<h3>👤 Current Cashiers:</h3>";
    echo "<table border='1' style='border-collapse: collapse; width: 100%; margin-bottom: 2rem;'>";
    echo "<tr style='background: #f8f9fa;'>";
    echo "<th style='padding: 0.5rem;'>Cashier ID</th>";
    echo "<th style='padding: 0.5rem;'>Name</th>";
    echo "<th style='padding: 0.5rem;'>Email</th>";
    echo "<th style='padding: 0.5rem;'>Current Branch</th>";
    echo "<th style='padding: 0.5rem;'>Action</th>";
    echo "</tr>";
    
    foreach ($cashiers as $cashier) {
        $currentBranch = '❌ No Branch Assigned';
        $currentBranchId = $cashier['branch_id'];
        
        if ($currentBranchId) {
            foreach ($branches as $branch) {
                if ($branch['branch_id'] == $currentBranchId) {
                    $currentBranch = "✅ {$branch['branch_name']} ({$branch['branch_code']})";
                    break;
                }
            }
        }
        
        echo "<tr>";
        echo "<td style='padding: 0.5rem;'>{$cashier['user_id']}</td>";
        echo "<td style='padding: 0.5rem;'>{$cashier['user_name']}</td>";
        echo "<td style='padding: 0.5rem;'>{$cashier['user_email']}</td>";
        echo "<td style='padding: 0.5rem;'>{$currentBranch}</td>";
        echo "<td style='padding: 0.5rem;'>";
        
        if (!$currentBranchId) {
            echo "<form method='POST' style='display: inline;'>";
            echo "<input type='hidden' name='cashier_id' value='{$cashier['user_id']}'>";
            echo "<select name='branch_id' style='margin-right: 0.5rem;'>";
            foreach ($branches as $branch) {
                echo "<option value='{$branch['branch_id']}'>{$branch['branch_name']}</option>";
            }
            echo "</select>";
            echo "<button type='submit' name='assign' class='btn btn-primary btn-sm'>Assign</button>";
            echo "</form>";
        } else {
            echo "<span style='color: green;'>✅ Already Assigned</span>";
        }
        
        echo "</td>";
        echo "</tr>";
    }
    echo "</table>";
    
    // Handle assignment
    if (isset($_POST['assign']) && isset($_POST['cashier_id']) && isset($_POST['branch_id'])) {
        $cashier_id = intval($_POST['cashier_id']);
        $branch_id = intval($_POST['branch_id']);
        
        // Update cashier's branch assignment
        $updateStmt = $pdo->prepare("UPDATE pos_user SET branch_id = ? WHERE user_id = ? AND user_type = 'Cashier'");
        $updateStmt->execute([$branch_id, $cashier_id]);
        
        if ($updateStmt->rowCount() > 0) {
            // Get branch name for confirmation
            $branchStmt = $pdo->prepare("SELECT branch_name FROM pos_branch WHERE branch_id = ?");
            $branchStmt->execute([$branch_id]);
            $branch = $branchStmt->fetch(PDO::FETCH_ASSOC);
            
            echo "<div style='background: #d4edda; color: #155724; padding: 1rem; border-radius: 0.5rem; margin: 1rem 0;'>";
            echo "✅ <strong>Success!</strong> Cashier ID {$cashier_id} has been assigned to branch: <strong>{$branch['branch_name']}</strong>";
            echo "</div>";
            
            // Refresh the page to show updated assignments
            echo "<script>setTimeout(function(){ window.location.reload(); }, 2000);</script>";
        } else {
            echo "<div style='background: #f8d7da; color: #721c24; padding: 1rem; border-radius: 0.5rem; margin: 1rem 0;'>";
            echo "❌ <strong>Error!</strong> Failed to assign cashier to branch.";
            echo "</div>";
        }
    }
    
    // Quick bulk assignment option
    echo "<hr>";
    echo "<h3>🚀 Quick Bulk Assignment</h3>";
    echo "<p>Assign all unassigned cashiers to a specific branch:</p>";
    echo "<form method='POST'>";
    echo "<select name='bulk_branch_id' style='margin-right: 0.5rem;'>";
    foreach ($branches as $branch) {
        echo "<option value='{$branch['branch_id']}'>{$branch['branch_name']}</option>";
    }
    echo "</select>";
    echo "<button type='submit' name='bulk_assign' class='btn btn-success'>Assign All Unassigned Cashiers</button>";
    echo "</form>";
    
    // Handle bulk assignment
    if (isset($_POST['bulk_assign']) && isset($_POST['bulk_branch_id'])) {
        $bulk_branch_id = intval($_POST['bulk_branch_id']);
        
        // Get branch name
        $branchStmt = $pdo->prepare("SELECT branch_name FROM pos_branch WHERE branch_id = ?");
        $branchStmt->execute([$bulk_branch_id]);
        $branch = $branchStmt->fetch(PDO::FETCH_ASSOC);
        
        // Update all unassigned cashiers
        $bulkUpdateStmt = $pdo->prepare("UPDATE pos_user SET branch_id = ? WHERE user_type = 'Cashier' AND branch_id IS NULL");
        $bulkUpdateStmt->execute([$bulk_branch_id]);
        
        $affectedRows = $bulkUpdateStmt->rowCount();
        
        if ($affectedRows > 0) {
            echo "<div style='background: #d4edda; color: #155724; padding: 1rem; border-radius: 0.5rem; margin: 1rem 0;'>";
            echo "✅ <strong>Bulk Assignment Complete!</strong> {$affectedRows} cashiers assigned to branch: <strong>{$branch['branch_name']}</strong>";
            echo "</div>";
            
            // Refresh the page
            echo "<script>setTimeout(function(){ window.location.reload(); }, 2000);</script>";
        } else {
            echo "<div style='background: #fff3cd; color: #856404; padding: 1rem; border-radius: 0.5rem; margin: 1rem 0;'>";
            echo "ℹ️ <strong>Info:</strong> All cashiers are already assigned to branches.";
            echo "</div>";
        }
    }
    
    echo "<hr>";
    echo "<h3>🎯 Next Steps:</h3>";
    echo "<ol>";
    echo "<li>✅ Assign cashiers to branches using the forms above</li>";
    echo "<li>🔗 Products are already connected to branches via the existing system</li>";
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
