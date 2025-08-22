<?php
require_once 'db_connect.php';
require_once 'auth_function.php';

// This function should be called when a cashier logs in
function startCashierSession($user_id, $branch_id) {
    global $pdo;
    
    try {
        // Check if cashier_sessions table exists
        $table_check = $pdo->query("SHOW TABLES LIKE 'cashier_sessions'");
        if ($table_check->rowCount() == 0) {
            return false; // Table doesn't exist
        }
        
        // End any existing active sessions for this user
        $pdo->prepare("UPDATE cashier_sessions SET session_end = NOW() WHERE user_id = ? AND session_end IS NULL")->execute([$user_id]);
        
        // Start new session
        $stmt = $pdo->prepare("INSERT INTO cashier_sessions (user_id, branch_id, session_start, ip_address) VALUES (?, ?, NOW(), ?)");
        $stmt->execute([$user_id, $branch_id, $_SERVER['REMOTE_ADDR'] ?? '']);
        
        return true;
    } catch (Exception $e) {
        error_log("Error starting cashier session: " . $e->getMessage());
        return false;
    }
}

function endCashierSession($user_id) {
    global $pdo;
    
    try {
        // End active session
        $stmt = $pdo->prepare("UPDATE cashier_sessions SET session_end = NOW() WHERE user_id = ? AND session_end IS NULL");
        $stmt->execute([$user_id]);
        
        return true;
    } catch (Exception $e) {
        error_log("Error ending cashier session: " . $e->getMessage());
        return false;
    }
}

// If called directly, create test sessions
if (basename(__FILE__) == basename($_SERVER['SCRIPT_NAME'])) {
    echo "<h2>Creating Test Cashier Sessions</h2>";
    
    // Get active cashiers
    $cashiers = $pdo->query("SELECT user_id, user_name, branch_id FROM pos_user WHERE user_type = 'Cashier' AND user_status = 'Active'")->fetchAll(PDO::FETCH_ASSOC);
    
    foreach ($cashiers as $cashier) {
        if (startCashierSession($cashier['user_id'], $cashier['branch_id'])) {
            echo "<p style='color: green;'>✓ Started session for {$cashier['user_name']}</p>";
        } else {
            echo "<p style='color: red;'>✗ Failed to start session for {$cashier['user_name']}</p>";
        }
    }
    
    echo "<p><a href='get_active_cashiers.php' target='_blank'>Test Active Cashiers API</a></p>";
}
?>
