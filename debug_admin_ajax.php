<?php
require_once 'db_connect.php';
require_once 'auth_function.php';

// Simulate admin login for testing
session_start();
$_SESSION['user_logged_in'] = true;
$_SESSION['user_type'] = 'Admin';

echo "<h2>Debug Admin AJAX Endpoint</h2>";

try {
    // Simulate the exact POST data that DataTables sends
    $_POST['draw'] = 1;
    $_POST['start'] = 0;
    $_POST['length'] = 10;
    $_POST['branch'] = 'all';
    $_POST['status'] = 'all';
    
    echo "<h3>1. POST Data:</h3>";
    echo "<pre>" . print_r($_POST, true) . "</pre>";
    
    // Get filter parameters
    $branch = isset($_POST['branch']) ? $_POST['branch'] : 'all';
    $status = isset($_POST['status']) ? $_POST['status'] : 'all';
    
    echo "<h3>2. Filter Parameters:</h3>";
    echo "<p>Branch: $branch</p>";
    echo "<p>Status: $status</p>";
    
    // Base query
    $query = "SELECT r.*, b.branch_name, u.user_name as updated_by_name
              FROM ingredient_requests r 
              LEFT JOIN pos_branch b ON r.branch_id = b.branch_id 
              LEFT JOIN pos_user u ON r.updated_by = u.user_id
              WHERE 1=1";
    
    // Apply filters
    if ($branch !== 'all') {
        $query .= " AND r.branch_id = :branch";
    }
    if ($status !== 'all') {
        $query .= " AND r.status = :status";
    }
    
    $query .= " ORDER BY r.request_date DESC";
    
    echo "<h3>3. Final Query:</h3>";
    echo "<pre>$query</pre>";
    
    // Prepare and execute query
    $stmt = $pdo->prepare($query);
    
    if ($branch !== 'all') {
        $stmt->bindParam(':branch', $branch);
    }
    if ($status !== 'all') {
        $stmt->bindParam(':status', $status);
    }
    
    $stmt->execute();
    $requests = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    echo "<h3>4. Raw Results:</h3>";
    echo "<p>Found " . count($requests) . " requests</p>";
    
    if (!empty($requests)) {
        echo "<table border='1' style='border-collapse: collapse; width: 100%;'>";
        echo "<tr><th>ID</th><th>Branch</th><th>Date</th><th>Status</th><th>Updated By</th><th>Ingredients</th></tr>";
        foreach ($requests as $request) {
            echo "<tr>";
            echo "<td>{$request['request_id']}</td>";
            echo "<td>" . ($request['branch_name'] ?? 'Unknown') . "</td>";
            echo "<td>{$request['request_date']}</td>";
            echo "<td>{$request['status']}</td>";
            echo "<td>" . ($request['updated_by_name'] ?? 'N/A') . "</td>";
            echo "<td>" . htmlspecialchars(substr($request['ingredients'], 0, 50)) . "...</td>";
            echo "</tr>";
        }
        echo "</table>";
    }
    
    // Format data for DataTables
    $data = array();
    foreach ($requests as $request) {
        // Parse ingredients JSON and get ingredient names
        $ingredients_list = [];
        $ingredients_json = json_decode($request['ingredients'], true);
        
        if ($ingredients_json && is_array($ingredients_json)) {
            foreach ($ingredients_json as $ingredient) {
                if (isset($ingredient['ingredient_id']) && isset($ingredient['quantity'])) {
                    // Get ingredient name from database
                    $stmt_ingredient = $pdo->prepare("SELECT ingredient_name, ingredient_unit FROM ingredients WHERE ingredient_id = ?");
                    $stmt_ingredient->execute([$ingredient['ingredient_id']]);
                    $ingredient_info = $stmt_ingredient->fetch(PDO::FETCH_ASSOC);
                    
                    if ($ingredient_info) {
                        $ingredients_list[] = $ingredient_info['ingredient_name'] . ' (' . $ingredient['quantity'] . ' ' . $ingredient_info['ingredient_unit'] . ')';
                    } else {
                        $ingredients_list[] = 'Unknown Ingredient (ID: ' . $ingredient['ingredient_id'] . ') - ' . $ingredient['quantity'];
                    }
                }
            }
        }
        
        $ingredients_display = !empty($ingredients_list) ? implode(', ', $ingredients_list) : 'No ingredients specified';
        
        $data[] = array(
            'request_id' => $request['request_id'],
            'branch_name' => $request['branch_name'],
            'request_date' => $request['request_date'],
            'ingredients' => $ingredients_display,
            'status' => $request['status'],
            'delivery_status' => isset($request['delivery_status']) ? $request['delivery_status'] : 'pending',
            'updated_by' => $request['updated_by_name'] ?: 'N/A'
        );
    }
    
    echo "<h3>5. Formatted Data for DataTables:</h3>";
    echo "<p>Formatted " . count($data) . " records</p>";
    
    if (!empty($data)) {
        echo "<table border='1' style='border-collapse: collapse; width: 100%;'>";
        echo "<tr><th>ID</th><th>Branch</th><th>Date</th><th>Status</th><th>Updated By</th><th>Ingredients</th></tr>";
        foreach ($data as $row) {
            echo "<tr>";
            echo "<td>{$row['request_id']}</td>";
            echo "<td>{$row['branch_name']}</td>";
            echo "<td>{$row['request_date']}</td>";
            echo "<td>{$row['status']}</td>";
            echo "<td>{$row['updated_by']}</td>";
            echo "<td>" . htmlspecialchars(substr($row['ingredients'], 0, 50)) . "...</td>";
            echo "</tr>";
        }
        echo "</table>";
    }
    
    // Final JSON response
    $response = array(
        'draw' => isset($_POST['draw']) ? intval($_POST['draw']) : 0,
        'recordsTotal' => count($data),
        'recordsFiltered' => count($data),
        'data' => $data
    );
    
    echo "<h3>6. Final JSON Response:</h3>";
    echo "<pre>" . json_encode($response, JSON_PRETTY_PRINT) . "</pre>";
    
    echo "<h3 style='color: green;'>✅ Debug completed!</h3>";
    
} catch (Exception $e) {
    echo "<p style='color: red;'>❌ Error: " . $e->getMessage() . "</p>";
}
?> 