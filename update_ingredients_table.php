<?php
require_once 'db_connect.php';

try {
    // Add new columns to ingredients table
    $sql = "
    ALTER TABLE ingredients 
    ADD COLUMN IF NOT EXISTS minimum_stock DECIMAL(10,2) DEFAULT 0,
    ADD COLUMN IF NOT EXISTS supplier_name VARCHAR(255),
    ADD COLUMN IF NOT EXISTS supplier_contact VARCHAR(100),
    ADD COLUMN IF NOT EXISTS storage_location VARCHAR(255),
    ADD COLUMN IF NOT EXISTS cost_per_unit DECIMAL(10,2)
    ";
    
    $pdo->exec($sql);
    
    // Update existing records to have default values
    $pdo->exec("UPDATE ingredients SET minimum_stock = 0 WHERE minimum_stock IS NULL");
    
    echo "Ingredients table updated successfully!";
    
} catch (Exception $e) {
    echo "Error updating table: " . $e->getMessage();
}
?> 