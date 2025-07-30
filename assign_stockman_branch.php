<?php
require_once 'db_connect.php';

echo "<h2>Assigning Stockmen to Branches</h2>";

try {
    // Get all stockmen
    $stockmen = $pdo->query("SELECT user_id, user_name FROM pos_user WHERE user_type = 'Stockman'")->fetchAll(PDO::FETCH_ASSOC);
    
    // Get all branches
    $branches = $pdo->query("SELECT branch_id, branch_name FROM pos_branch")->fetchAll(PDO::FETCH_ASSOC);
    
    if (empty($branches)) {
        echo "<p style='color: red;'>❌ No branches found in the database!</p>";
        exit;
    }
    
    echo "<h3>Available Branches:</h3>";
    echo "<ul>";
    foreach ($branches as $branch) {
        echo "<li>ID: {$branch['branch_id']} - {$branch['branch_name']}</li>";
    }
    echo "</ul>";
    
    echo "<h3>Stockmen to Assign:</h3>";
    if (empty($stockmen)) {
        echo "<p style='color: orange;'>⚠️ No stockmen found in the database.</p>";
    } else {
        echo "<ul>";
        foreach ($stockmen as $stockman) {
            echo "<li>ID: {$stockman['user_id']} - {$stockman['user_name']}</li>";
        }
        echo "</ul>";
        
        // Assign all stockmen to the first branch
        $firstBranchId = $branches[0]['branch_id'];
        $firstBranchName = $branches[0]['branch_name'];
        
        $updateStmt = $pdo->prepare("UPDATE pos_user SET branch_id = ? WHERE user_type = 'Stockman'");
        $updateStmt->execute([$firstBranchId]);
        
        $affectedRows = $updateStmt->rowCount();
        echo "<p style='color: green;'>✅ Successfully assigned $affectedRows stockmen to branch: $firstBranchName</p>";
    }
    
    // Show current assignments
    echo "<h3>Current User Branch Assignments:</h3>";
    $users = $pdo->query("SELECT u.user_id, u.user_name, u.user_type, u.branch_id, b.branch_name 
                          FROM pos_user u 
                          LEFT JOIN pos_branch b ON u.branch_id = b.branch_id 
                          ORDER BY u.user_type, u.user_name")->fetchAll(PDO::FETCH_ASSOC);
    
    echo "<table border='1' style='border-collapse: collapse; width: 100%;'>";
    echo "<tr><th>User ID</th><th>Name</th><th>Type</th><th>Branch ID</th><th>Branch Name</th></tr>";
    foreach ($users as $user) {
        $rowColor = $user['branch_id'] ? 'background-color: #d4edda;' : 'background-color: #f8d7da;';
        echo "<tr style='$rowColor'>";
        echo "<td>" . $user['user_id'] . "</td>";
        echo "<td>" . $user['user_name'] . "</td>";
        echo "<td>" . $user['user_type'] . "</td>";
        echo "<td>" . ($user['branch_id'] ?? 'NULL') . "</td>";
        echo "<td>" . ($user['branch_name'] ?? 'Not Assigned') . "</td>";
        echo "</tr>";
    }
    echo "</table>";
    
    echo "<h3 style='color: green;'>✅ Branch assignment completed!</h3>";
    echo "<p><a href='stockman_dashboard.php'>Go to Stockman Dashboard</a></p>";
    
} catch (Exception $e) {
    echo "<p style='color: red;'>❌ Error: " . $e->getMessage() . "</p>";
}
?> 