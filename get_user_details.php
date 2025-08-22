<?php
require_once 'db_connect.php';
require_once 'auth_function.php';

header('Content-Type: application/json');

try {
    if (!isset($_GET['id'])) {
        throw new Exception('User ID is required');
    }

    $user_id = $_GET['id'];
    
    // Get user details with branch information
    $stmt = $pdo->prepare("
        SELECT 
            u.*,
            CASE 
                WHEN u.profile_image IS NULL OR u.profile_image = '' THEN 'uploads/profiles/default.png'
                ELSE u.profile_image 
            END as profile_image,
            b.branch_name,
            b.branch_code
        FROM pos_user u 
        LEFT JOIN pos_branch b ON b.branch_id = u.branch_id
        WHERE u.user_id = ?
    ");
    $stmt->execute([$user_id]);
    $user = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$user) {
        throw new Exception('User not found');
    }

    // If user is a cashier, get additional details
    if ($user['user_type'] === 'Cashier') {
        $stmt = $pdo->prepare("
            SELECT 
                cd.*,
                b.branch_name
            FROM pos_cashier_details cd
            LEFT JOIN pos_branch b ON b.branch_id = cd.branch_id
            WHERE cd.user_id = ?
        ");
        $stmt->execute([$user_id]);
        $cashier_details = $stmt->fetch(PDO::FETCH_ASSOC);
        
        if ($cashier_details) {
            $user['cashier_details'] = $cashier_details;
        }
    }

    // If user is a stockman, get additional details from main user table
    if ($user['user_type'] === 'Stockman') {
        // Create stockman details from the main user table data
        $stockman_details = [
            'employee_id' => $user['employee_id'] ?: 'Not Assigned',
            'branch_name' => $user['branch_name'] ?: 'Not Assigned',
            'date_hired' => $user['date_hired'] ?: 'Not Set',
            'emergency_contact' => $user['emergency_contact'] ?: 'Not Provided',
            'emergency_number' => $user['emergency_number'] ?: 'Not Provided',
            'address' => $user['address'] ?: 'Not Provided'
        ];
        
        $user['stockman_details'] = $stockman_details;
    }

    echo json_encode([
        'success' => true,
        'data' => $user
    ]);

} catch (Exception $e) {
    http_response_code(400);
    echo json_encode([
        'success' => false,
        'message' => $e->getMessage()
    ]);
}
?> 