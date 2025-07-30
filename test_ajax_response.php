<?php
require_once 'db_connect.php';

echo "<h2>Test AJAX Response Format</h2>";

try {
    // Simulate the AJAX request
    $_POST['draw'] = 1;
    $_POST['start'] = 0;
    $_POST['length'] = 10;
    $_POST['branch'] = 'all';
    $_POST['status'] = 'all';
    
    // Get all requests
    $query = "SELECT r.*, b.branch_name, u.user_name as updated_by_name
              FROM ingredient_requests r 
              LEFT JOIN pos_branch b ON r.branch_id = b.branch_id 
              LEFT JOIN pos_user u ON r.updated_by = u.user_id
              WHERE 1=1
              ORDER BY r.request_date DESC";
    
    $stmt = $pdo->prepare($query);
    $stmt->execute();
    $requests = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    echo "<h3>1. Raw Database Results:</h3>";
    echo "<p>Total requests from database: " . count($requests) . "</p>";
    
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
    
    echo "<h3>2. Formatted Data:</h3>";
    echo "<p>Formatted data count: " . count($data) . "</p>";
    
    // Show first 5 entries
    echo "<table border='1' style='border-collapse: collapse; width: 100%;'>";
    echo "<tr><th>ID</th><th>Branch</th><th>Date</th><th>Status</th></tr>";
    for ($i = 0; $i < min(5, count($data)); $i++) {
        echo "<tr>";
        echo "<td>{$data[$i]['request_id']}</td>";
        echo "<td>{$data[$i]['branch_name']}</td>";
        echo "<td>{$data[$i]['request_date']}</td>";
        echo "<td>{$data[$i]['status']}</td>";
        echo "</tr>";
    }
    echo "</table>";
    
    // Final JSON response
    $response = array(
        'draw' => isset($_POST['draw']) ? intval($_POST['draw']) : 0,
        'recordsTotal' => count($data),
        'recordsFiltered' => count($data),
        'data' => $data
    );
    
    echo "<h3>3. Final JSON Response:</h3>";
    echo "<p>recordsTotal: " . $response['recordsTotal'] . "</p>";
    echo "<p>recordsFiltered: " . $response['recordsFiltered'] . "</p>";
    echo "<p>data count: " . count($response['data']) . "</p>";
    
    echo "<h3>4. Expected Pagination:</h3>";
    $pageLength = 10;
    $totalPages = ceil($response['recordsTotal'] / $pageLength);
    echo "<p>With $pageLength entries per page and {$response['recordsTotal']} total records:</p>";
    echo "<p>Total pages: $totalPages</p>";
    
    if ($totalPages > 1) {
        echo "<p style='color: green;'>✅ Pagination should work! Previous/Next buttons should be clickable.</p>";
    } else {
        echo "<p style='color: orange;'>⚠️ Only one page of data. Pagination buttons will be disabled.</p>";
    }
    
    echo "<h3 style='color: green;'>✅ Test completed!</h3>";
    echo "<p><a href='ingredient_requests.php'>Go to Admin Ingredient Requests Page</a></p>";
    
} catch (Exception $e) {
    echo "<p style='color: red;'>❌ Error: " . $e->getMessage() . "</p>";
}
?> 