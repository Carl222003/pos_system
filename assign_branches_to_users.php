<?php
require_once 'db_connect.php';
require_once 'auth_function.php';

// Only admin can run this
checkAdminLogin();

echo "<h2>Assigning Branches to Users</h2>";

try {
    // Get all active branches
    $stmt = $pdo->query("SELECT branch_id, branch_name, branch_code FROM pos_branch WHERE status = 'Active' ORDER BY branch_name");
    $branches = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    if (empty($branches)) {
        echo "<p style='color: red;'>No active branches found!</p>";
        exit;
    }
    
    // Get users without branch assignments
    $stmt = $pdo->query("SELECT user_id, user_name, user_type FROM pos_user WHERE branch_id IS NULL AND user_type IN ('Cashier', 'Stockman') ORDER BY user_type, user_name");
    $users = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    if (empty($users)) {
        echo "<p style='color: green;'>✅ All users already have branch assignments!</p>";
        exit;
    }
    
    echo "<h3>Available Branches:</h3>";
    echo "<ul>";
    foreach ($branches as $branch) {
        echo "<li><strong>{$branch['branch_name']}</strong> ({$branch['branch_code']}) - ID: {$branch['branch_id']}</li>";
    }
    echo "</ul>";
    
    echo "<h3>Users without Branch Assignments:</h3>";
    echo "<form method='POST' action=''>";
    echo "<table border='1' style='border-collapse: collapse; width: 100%;'>";
    echo "<tr><th>User ID</th><th>Name</th><th>Type</th><th>Assign Branch</th></tr>";
    
    foreach ($users as $user) {
        echo "<tr>";
        echo "<td>{$user['user_id']}</td>";
        echo "<td>{$user['user_name']}</td>";
        echo "<td>{$user['user_type']}</td>";
        echo "<td>";
        echo "<select name='branch_assignments[{$user['user_id']}]' required>";
        echo "<option value=''>Select Branch</option>";
        foreach ($branches as $branch) {
            echo "<option value='{$branch['branch_id']}'>{$branch['branch_name']} ({$branch['branch_code']})</option>";
        }
        echo "</select>";
        echo "</td>";
        echo "</tr>";
    }
    echo "</table>";
    echo "<br><input type='submit' value='Assign Branches' style='padding: 10px 20px; background: #8B4543; color: white; border: none; border-radius: 5px; cursor: pointer;'>";
    echo "</form>";
    
    // Handle form submission
    if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['branch_assignments'])) {
        $pdo->beginTransaction();
        
        $successCount = 0;
        $errors = [];
        
        foreach ($_POST['branch_assignments'] as $user_id => $branch_id) {
            if (empty($branch_id)) {
                $errors[] = "User ID $user_id: No branch selected";
                continue;
            }
            
            try {
                $stmt = $pdo->prepare("UPDATE pos_user SET branch_id = ? WHERE user_id = ?");
                $stmt->execute([$branch_id, $user_id]);
                
                if ($stmt->rowCount() > 0) {
                    $successCount++;
                    
                    // Log the activity
                    $user_name = $pdo->query("SELECT user_name FROM pos_user WHERE user_id = $user_id")->fetchColumn();
                    $branch_name = $pdo->query("SELECT branch_name FROM pos_branch WHERE branch_id = $branch_id")->fetchColumn();
                    $admin_id = $_SESSION['user_id'] ?? null;
                    if ($admin_id) {
                        logActivity($pdo, $admin_id, "Assigned branch to user", "User: $user_name assigned to branch: $branch_name");
                    }
                }
            } catch (Exception $e) {
                $errors[] = "User ID $user_id: " . $e->getMessage();
            }
        }
        
        if (empty($errors)) {
            $pdo->commit();
            echo "<p style='color: green;'>✅ Successfully assigned branches to $successCount users!</p>";
            echo "<p><a href='user.php'>Go back to User Management</a></p>";
        } else {
            $pdo->rollBack();
            echo "<p style='color: red;'>❌ Errors occurred:</p>";
            echo "<ul>";
            foreach ($errors as $error) {
                echo "<li>$error</li>";
            }
            echo "</ul>";
        }
    }
    
} catch (Exception $e) {
    echo "<p style='color: red;'>Error: " . $e->getMessage() . "</p>";
}
?> 