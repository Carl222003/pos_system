<?php
// This script should be run periodically (via cron job) to clean up expired verification codes
require_once 'db_connect.php';

try {
    // Delete expired verification codes (older than 1 hour for safety)
    $stmt = $pdo->prepare("
        DELETE FROM pos_email_verification 
        WHERE expires_at < DATE_SUB(NOW(), INTERVAL 1 HOUR)
    ");
    $deleted = $stmt->execute();
    $rowsDeleted = $stmt->rowCount();
    
    echo "Cleanup completed. Deleted {$rowsDeleted} expired verification codes.\n";
    
} catch (Exception $e) {
    echo "Error during cleanup: " . $e->getMessage() . "\n";
}
?>