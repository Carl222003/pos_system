<?php
require_once 'db_connect.php';

echo "<h2>Fixing User Branch ID Column</h2>";

try {
    // Check if branch_id column exists in pos_user table
    $checkColumn = $pdo->query("SHOW COLUMNS FROM pos_user LIKE 'branch_id'");
    $columnExists = $checkColumn->rowCount() > 0;
    
    if (!$columnExists) {
        // Add the branch_id column
        $pdo->exec("ALTER TABLE pos_user ADD COLUMN branch_id INT NULL AFTER user_type");
        echo "<p style='color: green;'>✅ branch_id column added successfully to pos_user table.</p>";
        
        // Update existing stockman users with their branch assignments
        // First, let's see what branches exist
        $branches = $pdo->query("SELECT branch_id, branch_name FROM pos_branch")->fetchAll(PDO::FETCH_ASSOC);
        
        if (!empty($branches)) {
            echo "<h3>Available Branches:</h3>";
            echo "<ul>";
            foreach ($branches as $branch) {
                echo "<li>ID: {$branch['branch_id']} - {$branch['branch_name']}</li>";
            }
            echo "</ul>";
            
            // For now, assign all stockmen to the first branch (you can manually update later)
            $firstBranchId = $branches[0]['branch_id'];
            $updateStmt = $pdo->prepare("UPDATE pos_user SET branch_id = ? WHERE user_type = 'Stockman' AND branch_id IS NULL");
            $updateStmt->execute([$firstBranchId]);
            
            $affectedRows = $updateStmt->rowCount();
            echo "<p style='color: blue;'>✓ Assigned $affectedRows stockman users to branch: {$branches[0]['branch_name']}</p>";
            echo "<p style='color: orange;'>⚠️ Note: You may need to manually update branch assignments for specific users.</p>";
        }
    } else {
        echo "<p style='color: blue;'>✓ branch_id column already exists in pos_user table.</p>";
    }
    
    // Show current table structure
    echo "<h3>Current pos_user table structure:</h3>";
    $columns = $pdo->query("SHOW COLUMNS FROM pos_user")->fetchAll(PDO::FETCH_ASSOC);
    echo "<table border='1' style='border-collapse: collapse; width: 100%;'>";
    echo "<tr><th>Field</th><th>Type</th><th>Null</th><th>Key</th><th>Default</th><th>Extra</th></tr>";
    foreach ($columns as $column) {
        echo "<tr>";
        echo "<td>" . $column['Field'] . "</td>";
        echo "<td>" . $column['Type'] . "</td>";
        echo "<td>" . $column['Null'] . "</td>";
        echo "<td>" . $column['Key'] . "</td>";
        echo "<td>" . $column['Default'] . "</td>";
        echo "<td>" . $column['Extra'] . "</td>";
        echo "</tr>";
    }
    echo "</table>";
    
    // Show current user assignments
    echo "<h3>Current User Branch Assignments:</h3>";
    $users = $pdo->query("SELECT u.user_id, u.user_name, u.user_type, u.branch_id, b.branch_name 
                          FROM pos_user u 
                          LEFT JOIN pos_branch b ON u.branch_id = b.branch_id 
                          ORDER BY u.user_type, u.user_name")->fetchAll(PDO::FETCH_ASSOC);
    
    echo "<table border='1' style='border-collapse: collapse; width: 100%;'>";
    echo "<tr><th>User ID</th><th>Name</th><th>Type</th><th>Branch ID</th><th>Branch Name</th></tr>";
    foreach ($users as $user) {
        echo "<tr>";
        echo "<td>" . $user['user_id'] . "</td>";
        echo "<td>" . $user['user_name'] . "</td>";
        echo "<td>" . $user['user_type'] . "</td>";
        echo "<td>" . ($user['branch_id'] ?? 'NULL') . "</td>";
        echo "<td>" . ($user['branch_name'] ?? 'Not Assigned') . "</td>";
        echo "</tr>";
    }
    echo "</table>";
    
    echo "<h3 style='color: green;'>✅ Database fix completed successfully!</h3>";
    echo "<p><a href='stockman_dashboard.php'>Go to Stockman Dashboard</a></p>";
    
} catch (Exception $e) {
    echo "<p style='color: red;'>❌ Error: " . $e->getMessage() . "</p>";
}
?> 