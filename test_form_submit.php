<?php
/**
 * Minimal test version of form processing
 */
require_once 'db_connect.php';

header('Content-Type: application/json');

try {
    echo json_encode([
        'success' => true,
        'message' => 'Test form processing is working!',
        'test_mode' => true,
        'verification_code' => '123456',
        'requires_verification' => true,
        'verification_id' => 1,
        'email' => $_POST['user_email'] ?? 'test@example.com'
    ]);
    
} catch (Exception $e) {
    echo json_encode([
        'success' => false,
        'message' => 'Error: ' . $e->getMessage()
    ]);
}
?>