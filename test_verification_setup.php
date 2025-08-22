<?php
require_once 'db_connect.php';

header('Content-Type: application/json');

try {
    // Test database connection
    echo json_encode([
        'success' => true,
        'message' => 'Database connection successful',
        'step' => 'connection_test'
    ]);
    
} catch (Exception $e) {
    echo json_encode([
        'success' => false,
        'message' => 'Database error: ' . $e->getMessage(),
        'step' => 'connection_test'
    ]);
}
?>