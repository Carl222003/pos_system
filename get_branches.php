<?php
require_once 'db_connect.php';
require_once 'auth_function.php';

checkAdminLogin();

header('Content-Type: application/json');

try {
    // Get all active branches
    $stmt = $pdo->query("SELECT branch_id, branch_name, branch_code FROM pos_branch WHERE status = 'Active' ORDER BY branch_name");
    $branches = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    echo json_encode([
        'success' => true,
        'data' => $branches
    ]);
} catch (PDOException $e) {
    error_log("Database error in get_branches.php: " . $e->getMessage());
    echo json_encode([
        'success' => false,
        'message' => 'Database error occurred'
    ]);
}
?>
