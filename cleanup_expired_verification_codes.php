<?php
// This script should be run periodically (via cron job) to clean up expired verification codes
require_once 'db_connect.php';

try {
    // Set timezone to Asia/Manila for accurate time comparison
    date_default_timezone_set('Asia/Manila');
    
    // Delete expired verification codes (older than 1 hour for safety) using Asia/Manila timezone
    $stmt = $pdo->prepare("
        DELETE FROM pos_email_verification 
        WHERE expires_at < DATE_SUB(CONVERT_TZ(NOW(), @@session.time_zone, '+08:00'), INTERVAL 1 HOUR)
    ");
    $deleted = $stmt->execute();
    $rowsDeleted = $stmt->rowCount();
    
    echo "Cleanup completed. Deleted {$rowsDeleted} expired verification codes.\n";
    
} catch (Exception $e) {
    echo "Error during cleanup: " . $e->getMessage() . "\n";
}
?>