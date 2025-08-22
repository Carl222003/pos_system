<?php
require_once 'db_connect.php';
require_once 'auth_function.php';

checkAdminLogin();

header('Content-Type: application/json');

// Simple test response
echo json_encode([
    'success' => true,
    'message' => 'Test update successful',
    'timestamp' => date('Y-m-d H:i:s'),
    'test_data' => $_POST
]);
?>
