<?php
require_once 'db_connect.php';

echo "<h2>Recent Ingredient Requests</h2>";

try {
    // Get recent requests
    $stmt = $pdo->prepare("
        SELECT r.*, b.branch_name, u.user_name as stockman_name
        FROM ingredient_requests r 
        LEFT JOIN pos_branch b ON r.branch_id = b.branch_id 
        LEFT JOIN pos_user u ON r.branch_id = u.branch_id AND u.user_type = 'Stockman'
        ORDER BY r.request_date DESC 
        LIMIT 20
    ");
    $stmt->execute();
    $requests = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    if (empty($requests)) {
        echo "<p style='color: orange;'>⚠️ No requests found in the database</p>";
    } else {
        echo "<table border='1' style='border-collapse: collapse; width: 100%;'>";
        echo "<tr><th>ID</th><th>Branch</th><th>Stockman</th><th>Date</th><th>Ingredients</th><th>Status</th><th>Notes</th></tr>";
        foreach ($requests as $request) {
            // Parse ingredients
            $ingredients_list = [];
            $ingredients_json = json_decode($request['ingredients'], true);
            
            if ($ingredients_json && is_array($ingredients_json)) {
                foreach ($ingredients_json as $ingredient) {
                    if (isset($ingredient['ingredient_id']) && isset($ingredient['quantity'])) {
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
            
            echo "<tr>";
            echo "<td>" . $request['request_id'] . "</td>";
            echo "<td>" . ($request['branch_name'] ?? 'Unknown') . "</td>";
            echo "<td>" . ($request['stockman_name'] ?? 'Unknown') . "</td>";
            echo "<td>" . $request['request_date'] . "</td>";
            echo "<td>" . htmlspecialchars($ingredients_display) . "</td>";
            echo "<td>" . $request['status'] . "</td>";
            echo "<td>" . htmlspecialchars(substr($request['notes'] ?? '', 0, 50)) . "</td>";
            echo "</tr>";
        }
        echo "</table>";
    }
    
    // Check if admin can see these requests
    echo "<h3>Admin View Test:</h3>";
    $adminQuery = "SELECT r.*, b.branch_name, u.user_name as updated_by_name
                   FROM ingredient_requests r 
                   LEFT JOIN pos_branch b ON r.branch_id = b.branch_id 
                   LEFT JOIN pos_user u ON r.updated_by = u.user_id
                   ORDER BY r.request_date DESC 
                   LIMIT 5";
    
    $adminRequests = $pdo->query($adminQuery)->fetchAll(PDO::FETCH_ASSOC);
    echo "<p>Admin query found " . count($adminRequests) . " requests</p>";
    
    if (!empty($adminRequests)) {
        echo "<ul>";
        foreach ($adminRequests as $request) {
            echo "<li>ID: {$request['request_id']} - Branch: {$request['branch_name']} - Status: {$request['status']}</li>";
        }
        echo "</ul>";
    }
    
} catch (Exception $e) {
    echo "<p style='color: red;'>❌ Error: " . $e->getMessage() . "</p>";
}
?> 