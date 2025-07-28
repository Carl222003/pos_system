<?php
require_once 'db_connect.php';
require_once 'auth_function.php';

checkAdminLogin();

header('Content-Type: application/json');

try {
    // Get filter parameters
    $branch = isset($_POST['branch']) ? $_POST['branch'] : 'all';
    $status = isset($_POST['status']) ? $_POST['status'] : 'all';
    
    // Base query
    $query = "SELECT r.*, b.branch_name 
              FROM ingredient_requests r 
              LEFT JOIN pos_branch b ON r.branch_id = b.branch_id 
              WHERE 1=1";
    
    // Apply filters
    if ($branch !== 'all') {
        $query .= " AND r.branch_id = :branch";
    }
    if ($status !== 'all') {
        $query .= " AND r.status = :status";
    }
    
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
            'status' => $request['status']
        );
    }
    
    echo json_encode(array(
        'draw' => isset($_POST['draw']) ? intval($_POST['draw']) : 0,
        'recordsTotal' => count($data),
        'recordsFiltered' => count($data),
        'data' => $data
    ));

} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(array('error' => $e->getMessage()));
} 