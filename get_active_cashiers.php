<?php
require_once 'db_connect.php';
require_once 'auth_function.php';

// Start session if not already started
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

checkAdminLogin();

header('Content-Type: application/json');

try {
    $branch_id = isset($_GET['branch_id']) ? intval($_GET['branch_id']) : null;
    
    if ($branch_id) {
        // First check if pos_cashier_sessions table exists
        $table_check = $pdo->query("SHOW TABLES LIKE 'pos_cashier_sessions'");
        $sessions_table_exists = $table_check->rowCount() > 0;
        
        if ($sessions_table_exists) {
            // Get active cashiers for specific branch with session tracking
            $query = "SELECT u.user_id, u.user_name, u.user_status, 
                             CASE 
                                 WHEN cs.login_time IS NOT NULL AND cs.is_active = TRUE THEN 'active'
                                 ELSE 'inactive'
                             END as session_status,
                             cs.login_time as session_start
                      FROM pos_user u
                      LEFT JOIN pos_cashier_sessions cs ON u.user_id = cs.user_id 
                          AND cs.is_active = TRUE
                      WHERE u.user_type = 'Cashier' 
                      AND u.branch_id = ? 
                      AND u.user_status = 'Active'
                      ORDER BY u.user_name";
        } else {
            // Fallback: Check if there are any cashiers logged in today (check recent activity)
            // For now, show all active cashiers as potentially active
            $query = "SELECT u.user_id, u.user_name, u.user_status, 'active' as session_status, NOW() as session_start
                      FROM pos_user u
                      WHERE u.user_type = 'Cashier' 
                      AND u.branch_id = ? 
                      AND u.user_status = 'Active'
                      ORDER BY u.user_name";
        }
        
        $stmt = $pdo->prepare($query);
        $stmt->execute([$branch_id]);
        $cashiers = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        $active_cashiers = [];
        $inactive_cashiers = [];
        
        foreach ($cashiers as $cashier) {
            if ($cashier['session_status'] === 'active') {
                $active_cashiers[] = [
                    'user_id' => $cashier['user_id'],
                    'name' => $cashier['user_name'],
                    'session_start' => $cashier['session_start'],
                    'status' => 'active'
                ];
            } else {
                $inactive_cashiers[] = [
                    'user_id' => $cashier['user_id'],
                    'name' => $cashier['user_name'],
                    'status' => 'inactive'
                ];
            }
        }
        
        echo json_encode([
            'success' => true,
            'branch_id' => $branch_id,
            'active_cashiers' => $active_cashiers,
            'inactive_cashiers' => $inactive_cashiers,
            'total_active' => count($active_cashiers),
            'total_cashiers' => count($cashiers)
        ]);
        
    } else {
        // Get active cashiers for all branches
        $table_check = $pdo->query("SHOW TABLES LIKE 'pos_cashier_sessions'");
        $sessions_table_exists = $table_check->rowCount() > 0;
        
        if ($sessions_table_exists) {
            $query = "SELECT u.user_id, u.user_name, u.branch_id, b.branch_name,
                             CASE 
                                 WHEN cs.login_time IS NOT NULL AND cs.is_active = TRUE THEN 'active'
                                 ELSE 'inactive'
                             END as session_status,
                             cs.login_time as session_start
                      FROM pos_user u
                      LEFT JOIN pos_branch b ON u.branch_id = b.branch_id
                      LEFT JOIN pos_cashier_sessions cs ON u.user_id = cs.user_id 
                          AND cs.is_active = TRUE
                      WHERE u.user_type = 'Cashier' 
                      AND u.user_status = 'Active'
                      ORDER BY b.branch_name, u.user_name";
        } else {
            $query = "SELECT u.user_id, u.user_name, u.branch_id, b.branch_name, 'inactive' as session_status, NULL as session_start
                      FROM pos_user u
                      LEFT JOIN pos_branch b ON u.branch_id = b.branch_id
                      WHERE u.user_type = 'Cashier' 
                      AND u.user_status = 'Active'
                      ORDER BY b.branch_name, u.user_name";
        }
        
        $stmt = $pdo->query($query);
        $cashiers = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        $branches = [];
        foreach ($cashiers as $cashier) {
            $branch_id = $cashier['branch_id'];
            if (!isset($branches[$branch_id])) {
                $branches[$branch_id] = [
                    'branch_name' => $cashier['branch_name'],
                    'active_cashiers' => [],
                    'inactive_cashiers' => [],
                    'total_active' => 0,
                    'total_cashiers' => 0
                ];
            }
            
            if ($cashier['session_status'] === 'active') {
                $branches[$branch_id]['active_cashiers'][] = [
                    'user_id' => $cashier['user_id'],
                    'name' => $cashier['user_name'],
                    'session_start' => $cashier['session_start']
                ];
                $branches[$branch_id]['total_active']++;
            } else {
                $branches[$branch_id]['inactive_cashiers'][] = [
                    'user_id' => $cashier['user_id'],
                    'name' => $cashier['user_name']
                ];
            }
            $branches[$branch_id]['total_cashiers']++;
        }
        
        echo json_encode([
            'success' => true,
            'branches' => $branches
        ]);
    }
    
} catch (Exception $e) {
    error_log("Active Cashiers API Error: " . $e->getMessage());
    
    // Provide fallback data instead of just failing
    try {
        // Simple fallback: just get all cashiers and assume some are active
        $fallback_query = "SELECT u.user_id, u.user_name, u.branch_id, b.branch_name
                          FROM pos_user u
                          LEFT JOIN pos_branch b ON u.branch_id = b.branch_id
                          WHERE u.user_type = 'Cashier' 
                          AND u.user_status = 'Active'
                          ORDER BY b.branch_name, u.user_name";
        
        $stmt = $pdo->query($fallback_query);
        $cashiers = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        $branches = [];
        foreach ($cashiers as $cashier) {
            $branch_id = $cashier['branch_id'];
            if (!isset($branches[$branch_id])) {
                $branches[$branch_id] = [
                    'branch_name' => $cashier['branch_name'],
                    'active_cashiers' => [],
                    'inactive_cashiers' => [],
                    'total_active' => 0,
                    'total_cashiers' => 0
                ];
            }
            
            // For fallback, show no active cashiers (safer assumption)
            $branches[$branch_id]['inactive_cashiers'][] = [
                'user_id' => $cashier['user_id'],
                'name' => $cashier['user_name']
            ];
            $branches[$branch_id]['total_cashiers']++;
        }
        
        echo json_encode([
            'success' => true,
            'branches' => $branches,
            'fallback' => true,
            'message' => 'Using fallback data due to session tracking error'
        ]);
        
    } catch (Exception $fallback_error) {
        echo json_encode([
            'success' => false,
            'error' => 'Complete system failure: ' . $fallback_error->getMessage()
        ]);
    }
}
?>
