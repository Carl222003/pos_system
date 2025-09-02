<?php
require_once 'db_connect.php';

echo "<h2>Testing Cashier Display System</h2>";

try {
    // Check if we have orders with cashier information
    $query = "
        SELECT 
            o.order_id,
            o.order_number,
            o.order_type,
            o.status,
            o.created_at,
            u.user_name as cashier_name,
            u.first_name,
            u.last_name
        FROM pos_orders o
        LEFT JOIN pos_users u ON o.user_id = u.user_id
        ORDER BY o.created_at DESC
        LIMIT 10
    ";
    
    $stmt = $pdo->prepare($query);
    $stmt->execute();
    $orders = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    if (count($orders) > 0) {
        echo "<h3>Sample Orders with Cashier Information:</h3>";
        echo "<table border='1' style='border-collapse: collapse; width: 100%; margin: 20px 0;'>";
        echo "<tr style='background: #f8f9fa;'>";
        echo "<th>Order #</th>";
        echo "<th>Type</th>";
        echo "<th>Status</th>";
        echo "<th>Cashier</th>";
        echo "<th>Created</th>";
        echo "</tr>";
        
        foreach ($orders as $order) {
            $cashierName = $order['cashier_name'] ?: 
                          (($order['first_name'] || $order['last_name']) ? 
                           trim($order['first_name'] . ' ' . $order['last_name']) : 
                           'Unknown');
            
            echo "<tr>";
            echo "<td>#{$order['order_number']}</td>";
            echo "<td>{$order['order_type']}</td>";
            echo "<td>{$order['status']}</td>";
            echo "<td>{$cashierName}</td>";
            echo "<td>" . date('M d, Y H:i', strtotime($order['created_at'])) . "</td>";
            echo "</tr>";
        }
        echo "</table>";
        
        echo "<p style='color: green;'>✅ Orders found with cashier information. The KDS dashboard will display these actual cashier names.</p>";
        
    } else {
        echo "<p style='color: orange;'>⚠ No orders found in the database.</p>";
    }
    
    // Check user table structure
    echo "<h3>User Table Structure:</h3>";
    $stmt = $pdo->query("DESCRIBE pos_users");
    $columns = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    echo "<ul>";
    foreach ($columns as $column) {
        echo "<li><strong>{$column['Field']}</strong> - {$column['Type']} {$column['Null']} {$column['Key']}</li>";
    }
    echo "</ul>";
    
    // Check if we have users
    $stmt = $pdo->query("SELECT COUNT(*) as total FROM pos_users");
    $userCount = $stmt->fetch(PDO::FETCH_ASSOC)['total'];
    echo "<p><strong>Total Users:</strong> {$userCount}</p>";
    
    if ($userCount > 0) {
        $stmt = $pdo->query("SELECT user_name, first_name, last_name FROM pos_users LIMIT 5");
        $users = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        echo "<h3>Sample Users:</h3>";
        echo "<ul>";
        foreach ($users as $user) {
            $displayName = $user['user_name'] ?: 
                          (($user['first_name'] || $user['last_name']) ? 
                           trim($user['first_name'] . ' ' . $user['last_name']) : 
                           'No Name Set');
            echo "<li>{$displayName}</li>";
        }
        echo "</ul>";
    }
    
    echo "<h3>How It Works:</h3>";
    echo "<ol>";
    echo "<li><strong>Dynamic Cashier Names:</strong> Each column will show the actual cashier who created the orders in that column</li>";
    echo "<li><strong>Real-Time Updates:</strong> When orders are added/removed, cashier names update automatically</li>";
    echo "<li><strong>No Server Information:</strong> Server field has been completely removed</li>";
    echo "<li><strong>Fallback Handling:</strong> If cashier name is missing, shows 'Unknown Cashier'</li>";
    echo "</ol>";
    
    echo "<p style='color: blue;'>ℹ <strong>Next Steps:</strong></p>";
    echo "<ul>";
    echo "<li>Go to <a href='order.php'>order.php</a> to see the KDS dashboard</li>";
    echo "<li>Create some test orders with different cashiers to see the dynamic names</li>";
    echo "<li>The system will automatically show the actual cashier for each order type</li>";
    echo "</ul>";
    
} catch (PDOException $e) {
    echo "<p style='color: red;'>✗ Database Error: " . $e->getMessage() . "</p>";
} catch (Exception $e) {
    echo "<p style='color: red;'>✗ General Error: " . $e->getMessage() . "</p>";
}
?>

<style>
body {
    font-family: Arial, sans-serif;
    max-width: 1000px;
    margin: 50px auto;
    padding: 20px;
    background: #f5f5f5;
}

h2, h3 {
    color: #8B4543;
}

table {
    background: white;
    border-radius: 8px;
    overflow: hidden;
    box-shadow: 0 2px 8px rgba(0, 0, 0, 0.1);
}

th, td {
    padding: 12px;
    text-align: left;
    border-bottom: 1px solid #e9ecef;
}

th {
    background: #f8f9fa;
    font-weight: 600;
}

ul, ol {
    background: white;
    padding: 20px 40px;
    border-radius: 8px;
    box-shadow: 0 2px 8px rgba(0, 0, 0, 0.1);
}

li {
    margin: 8px 0;
}

a {
    color: #8B4543;
    text-decoration: none;
    font-weight: 500;
}

a:hover {
    text-decoration: underline;
}
</style>
