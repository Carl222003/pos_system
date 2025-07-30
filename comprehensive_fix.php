<?php
require_once 'db_connect.php';

echo "<h2>Comprehensive Database Fix</h2>";

try {
    echo "<h3>1. Checking and fixing pos_user table...</h3>";
    
    // Check if branch_id column exists in pos_user
    $checkUserColumn = $pdo->query("SHOW COLUMNS FROM pos_user LIKE 'branch_id'");
    if ($checkUserColumn->rowCount() == 0) {
        $pdo->exec("ALTER TABLE pos_user ADD COLUMN branch_id INT NULL AFTER user_type");
        echo "✅ Added branch_id column to pos_user table<br>";
    } else {
        echo "✓ branch_id column already exists in pos_user table<br>";
    }
    
    echo "<h3>2. Checking and fixing pos_activity_log table...</h3>";
    
    // Check if branch_id column exists in pos_activity_log
    $checkActivityColumn = $pdo->query("SHOW COLUMNS FROM pos_activity_log LIKE 'branch_id'");
    if ($checkActivityColumn->rowCount() == 0) {
        $pdo->exec("ALTER TABLE pos_activity_log ADD COLUMN branch_id INT NULL AFTER user_id");
        echo "✅ Added branch_id column to pos_activity_log table<br>";
    } else {
        echo "✓ branch_id column already exists in pos_activity_log table<br>";
    }
    
    echo "<h3>3. Assigning stockmen to branches...</h3>";
    
    // Get available branches
    $branches = $pdo->query("SELECT branch_id, branch_name FROM pos_branch")->fetchAll(PDO::FETCH_ASSOC);
    if (empty($branches)) {
        echo "❌ No branches found! Please create branches first.<br>";
    } else {
        echo "Available branches: ";
        foreach ($branches as $branch) {
            echo "{$branch['branch_name']} (ID: {$branch['branch_id']}), ";
        }
        echo "<br>";
        
        // Assign stockmen to first branch
        $firstBranchId = $branches[0]['branch_id'];
        $updateStmt = $pdo->prepare("UPDATE pos_user SET branch_id = ? WHERE user_type = 'Stockman' AND (branch_id IS NULL OR branch_id = 0)");
        $updateStmt->execute([$firstBranchId]);
        $affectedRows = $updateStmt->rowCount();
        echo "✅ Assigned $affectedRows stockmen to branch: {$branches[0]['branch_name']}<br>";
    }
    
    echo "<h3>4. Checking ingredient_requests table...</h3>";
    
    // Check if delivery_status columns exist in ingredient_requests
    $checkDeliveryStatus = $pdo->query("SHOW COLUMNS FROM ingredient_requests LIKE 'delivery_status'");
    if ($checkDeliveryStatus->rowCount() == 0) {
        $pdo->exec("ALTER TABLE ingredient_requests ADD COLUMN delivery_status ENUM('pending', 'on_delivery', 'delivered', 'returned', 'cancelled') DEFAULT 'pending' AFTER status");
        $pdo->exec("ALTER TABLE ingredient_requests ADD COLUMN delivery_date TIMESTAMP NULL AFTER delivery_status");
        $pdo->exec("ALTER TABLE ingredient_requests ADD COLUMN delivery_notes TEXT NULL AFTER delivery_date");
        echo "✅ Added delivery status columns to ingredient_requests table<br>";
    } else {
        echo "✓ Delivery status columns already exist in ingredient_requests table<br>";
    }
    
    echo "<h3>5. Current User Assignments:</h3>";
    
    $users = $pdo->query("SELECT u.user_id, u.user_name, u.user_type, u.branch_id, b.branch_name 
                          FROM pos_user u 
                          LEFT JOIN pos_branch b ON u.branch_id = b.branch_id 
                          ORDER BY u.user_type, u.user_name")->fetchAll(PDO::FETCH_ASSOC);
    
    echo "<table border='1' style='border-collapse: collapse; width: 100%; margin: 10px 0;'>";
    echo "<tr style='background-color: #f8f9fa;'><th>User ID</th><th>Name</th><th>Type</th><th>Branch ID</th><th>Branch Name</th><th>Status</th></tr>";
    foreach ($users as $user) {
        $status = $user['branch_id'] ? "✅ Assigned" : "❌ Not Assigned";
        $rowColor = $user['branch_id'] ? 'background-color: #d4edda;' : 'background-color: #f8d7da;';
        echo "<tr style='$rowColor'>";
        echo "<td>" . $user['user_id'] . "</td>";
        echo "<td>" . $user['user_name'] . "</td>";
        echo "<td>" . $user['user_type'] . "</td>";
        echo "<td>" . ($user['branch_id'] ?? 'NULL') . "</td>";
        echo "<td>" . ($user['branch_name'] ?? 'Not Assigned') . "</td>";
        echo "<td>" . $status . "</td>";
        echo "</tr>";
    }
    echo "</table>";
    
    echo "<h3 style='color: green;'>✅ Comprehensive fix completed successfully!</h3>";
    echo "<p><strong>Next steps:</strong></p>";
    echo "<ol>";
    echo "<li><a href='stockman_dashboard.php'>Go to Stockman Dashboard</a></li>";
    echo "<li>Try submitting a request stock</li>";
    echo "<li>Check if the error is resolved</li>";
    echo "</ol>";
    
} catch (Exception $e) {
    echo "<p style='color: red;'>❌ Error: " . $e->getMessage() . "</p>";
}
?> 