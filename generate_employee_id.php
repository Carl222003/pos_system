<?php
require_once 'db_connect.php';

function generateEmployeeID($pdo, $user_type) {
    // Get current year
    $year = date('Y');
    
    // Get prefix based on user type
    $prefix = '';
    switch(strtolower($user_type)) {
        case 'cashier':
            $prefix = 'CSH';
            break;
        case 'stockman':
            $prefix = 'STM';
            break;
        case 'admin':
            $prefix = 'ADM';
            break;
        default:
            $prefix = 'EMP';
    }
    
    // Get the latest employee ID for this type and year
    $stmt = $pdo->prepare("
        SELECT employee_id 
        FROM pos_user 
        WHERE employee_id LIKE :pattern 
        ORDER BY employee_id DESC 
        LIMIT 1
    ");
    
    $pattern = $prefix . $year . '%';
    $stmt->execute(['pattern' => $pattern]);
    $latest = $stmt->fetchColumn();
    
    if ($latest) {
        // Extract the number part and increment
        $number = intval(substr($latest, -4)) + 1;
    } else {
        // Start with 0001 for this year
        $number = 1;
    }
    
    // Format: CSH20250001, STM20250001, etc.
    return $prefix . $year . str_pad($number, 4, '0', STR_PAD_LEFT);
}

// Function to check if employee ID is unique
function isEmployeeIDUnique($pdo, $employee_id) {
    $stmt = $pdo->prepare("SELECT COUNT(*) FROM pos_user WHERE employee_id = :employee_id");
    $stmt->execute(['employee_id' => $employee_id]);
    return $stmt->fetchColumn() == 0;
}
?> 