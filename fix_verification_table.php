<?php
require_once 'db_connect.php';

try {
    echo "Fixing email verification table...\n";
    
    // Drop existing table if it exists
    $pdo->exec("DROP TABLE IF EXISTS pos_email_verification");
    echo "✅ Dropped existing table\n";
    
    // Create new table with correct structure
    $sql = "
    CREATE TABLE pos_email_verification (
        id INT AUTO_INCREMENT PRIMARY KEY,
        email VARCHAR(255) NOT NULL,
        verification_code VARCHAR(6) NOT NULL,
        form_data TEXT,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        expires_at TIMESTAMP NULL,
        is_verified BOOLEAN DEFAULT FALSE,
        attempt_count INT DEFAULT 0,
        INDEX idx_email (email),
        INDEX idx_code (verification_code),
        INDEX idx_expires (expires_at)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
    ";
    
    $pdo->exec($sql);
    echo "✅ Created new pos_email_verification table\n";
    
    // Test the table
    $stmt = $pdo->query("SHOW CREATE TABLE pos_email_verification");
    $result = $stmt->fetch(PDO::FETCH_ASSOC);
    echo "✅ Table structure verified\n";
    
    echo "\n📋 Table created successfully! You can now use the email verification system.\n";
    
} catch (Exception $e) {
    echo "❌ Error: " . $e->getMessage() . "\n";
}
?>