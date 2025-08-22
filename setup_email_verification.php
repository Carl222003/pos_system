<?php
require_once 'db_connect.php';

try {
    // Create email verification table
    $sql = "
    CREATE TABLE IF NOT EXISTS pos_email_verification (
        id INT AUTO_INCREMENT PRIMARY KEY,
        email VARCHAR(255) NOT NULL,
        verification_code VARCHAR(6) NOT NULL,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        expires_at TIMESTAMP NOT NULL,
        is_verified BOOLEAN DEFAULT FALSE,
        attempt_count INT DEFAULT 0,
        INDEX idx_email (email),
        INDEX idx_code (verification_code),
        INDEX idx_expires (expires_at)
    )";
    
    $pdo->exec($sql);
    echo "✅ Email verification table created successfully!\n";
    
    // Test the table
    $stmt = $pdo->query("SHOW TABLES LIKE 'pos_email_verification'");
    if ($stmt->rowCount() > 0) {
        echo "✅ Table verification: pos_email_verification table exists\n";
        
        // Show table structure
        $stmt = $pdo->query("DESCRIBE pos_email_verification");
        $columns = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        echo "\n📋 Table structure:\n";
        foreach ($columns as $column) {
            echo "  - {$column['Field']}: {$column['Type']}\n";
        }
    } else {
        echo "❌ Table verification failed\n";
    }
    
} catch (Exception $e) {
    echo "❌ Error: " . $e->getMessage() . "\n";
}
?>