<?php
require_once 'db_connect.php';
require_once 'auth_function.php';

// This file should be called periodically to update user activity
// It can be called via AJAX or included in other pages

function updateUserActivity($pdo, $user_id) {
    try {
        $stmt = $pdo->prepare("
            UPDATE pos_user 
            SET last_activity = NOW() 
            WHERE user_id = ?
        ");
        $stmt->execute([$user_id]);
        return true;
    } catch (Exception $e) {
        error_log("Error updating user activity: " . $e->getMessage());
        return false;
    }
}

// If called directly via AJAX
if (isset($_POST['update_activity']) && isset($_SESSION['user_id'])) {
    header('Content-Type: application/json');
    
    $success = updateUserActivity($pdo, $_SESSION['user_id']);
    
    echo json_encode([
        'success' => $success,
        'timestamp' => date('Y-m-d H:i:s')
    ]);
}
?>
