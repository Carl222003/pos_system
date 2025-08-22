<?php
require_once 'db_connect.php';
require_once 'auth_function.php';

checkAdminLogin();

echo "<h2>Setting Up Branch Overview System</h2>";

try {
    // Step 1: Create cashier_sessions table
    echo "<h3>Step 1: Creating Cashier Sessions Table</h3>";
    $createTable = "
    CREATE TABLE IF NOT EXISTS cashier_sessions (
        session_id INT PRIMARY KEY AUTO_INCREMENT,
        user_id INT NOT NULL,
        branch_id INT,
        session_start DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
        session_end DATETIME NULL,
        ip_address VARCHAR(45),
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        FOREIGN KEY (user_id) REFERENCES pos_user(user_id),
        FOREIGN KEY (branch_id) REFERENCES pos_branch(branch_id),
        INDEX idx_user_active (user_id, session_end),
        INDEX idx_branch_active (branch_id, session_end)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
    ";
    
    $pdo->exec($createTable);
    echo "<p style='color: green;'>✓ cashier_sessions table created!</p>";
    
    // Step 2: Create active sessions for all logged-in cashiers
    echo "<h3>Step 2: Creating Active Sessions</h3>";
    
    // End all existing sessions first
    $pdo->exec("UPDATE cashier_sessions SET session_end = NOW() WHERE session_end IS NULL");
    
    // Get all active cashiers and create sessions
    $cashiers = $pdo->query("
        SELECT u.user_id, u.user_name, u.branch_id, b.branch_name 
        FROM pos_user u 
        LEFT JOIN pos_branch b ON u.branch_id = b.branch_id 
        WHERE u.user_type = 'Cashier' 
        AND u.user_status = 'Active'
        ORDER BY b.branch_name, u.user_name
    ")->fetchAll(PDO::FETCH_ASSOC);
    
    foreach ($cashiers as $cashier) {
        $pdo->prepare("INSERT INTO cashier_sessions (user_id, branch_id, session_start) VALUES (?, ?, NOW())")
            ->execute([$cashier['user_id'], $cashier['branch_id']]);
        
        $branchName = $cashier['branch_name'] ?? 'No Branch';
        echo "<p style='color: blue;'>✓ Created session for {$cashier['user_name']} ({$branchName})</p>";
    }
    
    // Step 3: Test the APIs
    echo "<h3>Step 3: Testing APIs</h3>";
    
    // Test cashiers API
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, 'http://' . $_SERVER['HTTP_HOST'] . dirname($_SERVER['REQUEST_URI']) . '/get_active_cashiers.php');
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_TIMEOUT, 10);
    $cashierResult = curl_exec($ch);
    curl_close($ch);
    
    if ($cashierResult) {
        $data = json_decode($cashierResult, true);
        if ($data['success']) {
            echo "<p style='color: green;'>✓ Cashiers API working! Found " . count($data['branches']) . " branches with cashier data</p>";
        } else {
            echo "<p style='color: red;'>✗ Cashiers API error: " . ($data['error'] ?? 'Unknown error') . "</p>";
        }
    } else {
        echo "<p style='color: red;'>✗ Could not connect to Cashiers API</p>";
    }
    
    // Test inventory API
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, 'http://' . $_SERVER['HTTP_HOST'] . dirname($_SERVER['REQUEST_URI']) . '/test_inventory_simple.php');
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_TIMEOUT, 10);
    $inventoryResult = curl_exec($ch);
    curl_close($ch);
    
    if ($inventoryResult) {
        $data = json_decode($inventoryResult, true);
        if ($data['success']) {
            $stats = $data['stats'];
            echo "<p style='color: green;'>✓ Inventory API working!</p>";
            echo "<ul>";
            echo "<li>Total Items: {$stats['total_items']}</li>";
            echo "<li>Low Stock: {$stats['low_stock_items']}</li>";
            echo "<li>Out of Stock: {$stats['out_of_stock_items']}</li>";
            echo "<li>Expiring: {$stats['expiring_items']}</li>";
            echo "</ul>";
        } else {
            echo "<p style='color: red;'>✗ Inventory API error: " . ($data['error'] ?? 'Unknown error') . "</p>";
        }
    } else {
        echo "<p style='color: red;'>✗ Could not connect to Inventory API</p>";
    }
    
    echo "<h3>Setup Complete!</h3>";
    echo "<p><a href='branch_overview.php' target='_blank' style='background: #007bff; color: white; padding: 10px 20px; text-decoration: none; border-radius: 5px;'>View Branch Overview</a></p>";
    
    // Step 4: Show current active sessions
    echo "<h3>Current Active Sessions:</h3>";
    $activeSessions = $pdo->query("
        SELECT cs.*, u.user_name, b.branch_name 
        FROM cashier_sessions cs 
        JOIN pos_user u ON cs.user_id = u.user_id 
        LEFT JOIN pos_branch b ON cs.branch_id = b.branch_id 
        WHERE cs.session_end IS NULL
        ORDER BY b.branch_name, u.user_name
    ")->fetchAll(PDO::FETCH_ASSOC);
    
    if ($activeSessions) {
        echo "<table border='1' style='border-collapse: collapse; width: 100%;'>";
        echo "<tr><th>Cashier</th><th>Branch</th><th>Session Start</th></tr>";
        foreach ($activeSessions as $session) {
            echo "<tr>";
            echo "<td>{$session['user_name']}</td>";
            echo "<td>" . ($session['branch_name'] ?? 'No Branch') . "</td>";
            echo "<td>{$session['session_start']}</td>";
            echo "</tr>";
        }
        echo "</table>";
    } else {
        echo "<p>No active sessions found.</p>";
    }
    
} catch (Exception $e) {
    echo "<p style='color: red; font-weight: bold;'>Error: " . $e->getMessage() . "</p>";
    echo "<pre>" . $e->getTraceAsString() . "</pre>";
}
?>
