<?php
require_once 'db_connect.php';

echo "<h2>Setting up KDS Database...</h2>";

try {
    // Read and execute the SQL file
    $sqlFile = 'create_order_status_log_table.sql';
    if (file_exists($sqlFile)) {
        $sql = file_get_contents($sqlFile);
        
        // Split SQL into individual statements
        $statements = array_filter(array_map('trim', explode(';', $sql)));
        
        foreach ($statements as $statement) {
            if (!empty($statement)) {
                try {
                    $pdo->exec($statement);
                    echo "<p style='color: green;'>✓ Executed: " . substr($statement, 0, 50) . "...</p>";
                } catch (PDOException $e) {
                    echo "<p style='color: orange;'>⚠ Warning: " . $e->getMessage() . "</p>";
                }
            }
        }
    } else {
        echo "<p style='color: red;'>✗ SQL file not found: $sqlFile</p>";
    }
    
    // Verify table structure
    echo "<h3>Verifying Database Structure...</h3>";
    
    // Check if pos_order_status_log table exists
    $stmt = $pdo->query("SHOW TABLES LIKE 'pos_order_status_log'");
    if ($stmt->rowCount() > 0) {
        echo "<p style='color: green;'>✓ pos_order_status_log table exists</p>";
    } else {
        echo "<p style='color: red;'>✗ pos_order_status_log table missing</p>";
    }
    
    // Check pos_orders table structure
    $stmt = $pdo->query("DESCRIBE pos_orders");
    $columns = $stmt->fetchAll(PDO::FETCH_COLUMN);
    
    if (in_array('status', $columns)) {
        echo "<p style='color: green;'>✓ pos_orders.status column exists</p>";
    } else {
        echo "<p style='color: red;'>✗ pos_orders.status column missing</p>";
    }
    
    if (in_array('order_type', $columns)) {
        echo "<p style='color: green;'>✓ pos_orders.order_type column exists</p>";
    } else {
        echo "<p style='color: red;'>✗ pos_orders.order_type column missing</p>";
    }
    
    if (in_array('updated_at', $columns)) {
        echo "<p style='color: green;'>✓ pos_orders.updated_at column exists</p>";
    } else {
        echo "<p style='color: red;'>✗ pos_orders.updated_at column missing</p>";
    }
    
    // Check sample orders
    $stmt = $pdo->query("SELECT COUNT(*) as total FROM pos_orders");
    $totalOrders = $stmt->fetch(PDO::FETCH_ASSOC)['total'];
    echo "<p style='color: blue;'>ℹ Total orders in database: $totalOrders</p>";
    
    if ($totalOrders > 0) {
        $stmt = $pdo->query("SELECT order_type, COUNT(*) as count FROM pos_orders GROUP BY order_type");
        $orderTypes = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        echo "<p style='color: blue;'>ℹ Order type distribution:</p>";
        foreach ($orderTypes as $type) {
            echo "<p style='color: blue;'>  - {$type['order_type']}: {$type['count']}</p>";
        }
    }
    
    echo "<h3>KDS Database Setup Complete!</h3>";
    echo "<p style='color: green;'>✅ Your KDS dashboard is now ready to use.</p>";
    echo "<p><a href='order.php' style='background: #8B4543; color: white; padding: 10px 20px; text-decoration: none; border-radius: 5px;'>Go to KDS Dashboard</a></p>";
    
} catch (PDOException $e) {
    echo "<p style='color: red;'>✗ Database Error: " . $e->getMessage() . "</p>";
} catch (Exception $e) {
    echo "<p style='color: red;'>✗ General Error: " . $e->getMessage() . "</p>";
}
?>

<style>
body {
    font-family: Arial, sans-serif;
    max-width: 800px;
    margin: 50px auto;
    padding: 20px;
    background: #f5f5f5;
}

h2, h3 {
    color: #8B4543;
}

p {
    margin: 8px 0;
    padding: 5px;
    border-radius: 3px;
}
</style>
