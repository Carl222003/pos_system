<?php
require_once 'db_connect.php';
require_once 'auth_function.php';

// Check if user is logged in and is either Admin or Stockman
if (!isset($_SESSION['user_logged_in']) || $_SESSION['user_logged_in'] !== true) {
    header('Content-Type: application/json');
    echo json_encode(['success' => false, 'message' => 'Unauthorized access']);
    exit();
}

if ($_SESSION['user_type'] !== 'Admin' && $_SESSION['user_type'] !== 'Stockman') {
    header('Content-Type: application/json');
    echo json_encode(['success' => false, 'message' => 'Unauthorized access']);
    exit();
}

header('Content-Type: application/json');

try {
    // Get filter parameters
    $branch = isset($_POST['branch']) ? $_POST['branch'] : '';
    $status = isset($_POST['status']) ? $_POST['status'] : '';
    $ingredient = isset($_POST['ingredient']) ? $_POST['ingredient'] : '';
    $delivery_status = isset($_POST['delivery_status']) ? $_POST['delivery_status'] : '';
    $date_filter = isset($_POST['date_filter']) ? $_POST['date_filter'] : '';
    $date_from = isset($_POST['date_from']) ? $_POST['date_from'] : '';
    $date_to = isset($_POST['date_to']) ? $_POST['date_to'] : '';
    
    // Base query
    $query = "SELECT r.*, b.branch_name, u.user_name as updated_by_name
              FROM ingredient_requests r 
              LEFT JOIN pos_branch b ON r.branch_id = b.branch_id 
              LEFT JOIN pos_user u ON r.updated_by = u.user_id
              WHERE 1=1";
    
    $params = array();
    
    // For stockman users, only show completed requests for their branch
    if ($_SESSION['user_type'] === 'Stockman') {
        $query .= " AND r.branch_id = :stockman_branch";
        $params[':stockman_branch'] = $_SESSION['branch_id'];
        
        // Only show completed requests (approved and delivered)
        $query .= " AND r.status = 'approved' AND r.delivery_status IN ('delivered', 'partially_delivered')";
        
        // Debug logging for stockman
        error_log("Stockman completed requests query - Branch ID: " . $_SESSION['branch_id'] . ", User: " . $_SESSION['user_name']);
    }
    
    // Apply filters
    if (!empty($branch)) {
        $query .= " AND r.branch_id = :branch";
        $params[':branch'] = $branch;
    }
    
    if (!empty($status)) {
        if ($status === 'pending') {
            $query .= " AND r.status = 'pending'";
        } else {
            $query .= " AND r.status = :status";
            $params[':status'] = $status;
        }
    }
    
    if (!empty($delivery_status)) {
        if ($delivery_status === 'pending') {
            $query .= " AND r.delivery_status = 'pending'";
        } elseif ($delivery_status === 'processed') {
            $query .= " AND r.delivery_status != 'pending'";
        } elseif ($delivery_status === 'non-pending') {
            $query .= " AND r.status IN ('approved', 'rejected')";
        } else {
            $query .= " AND r.delivery_status = :delivery_status";
            $params[':delivery_status'] = $delivery_status;
        }
    }
    
    // Apply date filters
    if (!empty($date_filter)) {
        switch ($date_filter) {
            case 'today':
                $query .= " AND DATE(r.request_date) = CURDATE()";
                break;
            case 'yesterday':
                $query .= " AND DATE(r.request_date) = DATE_SUB(CURDATE(), INTERVAL 1 DAY)";
                break;
            case 'this_week':
                $query .= " AND YEARWEEK(r.request_date, 1) = YEARWEEK(CURDATE(), 1)";
                break;
            case 'this_month':
                $query .= " AND YEAR(r.request_date) = YEAR(CURDATE()) AND MONTH(r.request_date) = MONTH(CURDATE())";
                break;
            case 'custom':
                if (!empty($date_from)) {
                    $query .= " AND DATE(r.request_date) >= :date_from";
                    $params[':date_from'] = $date_from;
                }
                if (!empty($date_to)) {
                    $query .= " AND DATE(r.request_date) <= :date_to";
                    $params[':date_to'] = $date_to;
                }
                break;
        }
    }
    
    // Add ORDER BY clause to sort by request_date in ascending order (oldest first)
    $query .= " ORDER BY r.request_date ASC";
    
    // Debug logging for stockman
    if ($_SESSION['user_type'] === 'Stockman') {
        error_log("Stockman completed requests final query: " . $query);
        error_log("Stockman completed requests parameters: " . json_encode($params));
    }
    
    // Prepare and execute query
    $stmt = $pdo->prepare($query);
    
    // Bind all parameters
    foreach ($params as $key => $value) {
        $stmt->bindValue($key, $value);
    }
    
    $stmt->execute();
    $requests = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    // Apply ingredient filter after fetching data (since ingredients are stored as JSON)
    if (!empty($ingredient)) {
        $filtered_requests = array();
        foreach ($requests as $request) {
            $ingredients_json = json_decode($request['ingredients'], true);
            if ($ingredients_json && is_array($ingredients_json)) {
                foreach ($ingredients_json as $ingredient_item) {
                    if (isset($ingredient_item['ingredient_id']) && $ingredient_item['ingredient_id'] == $ingredient) {
                        $filtered_requests[] = $request;
                        break;
                    }
                }
            }
        }
        $requests = $filtered_requests;
    }
    
    // Format data for DataTables
    $data = array();
    foreach ($requests as $request) {
        // Parse ingredients JSON and get ingredient names
        $ingredients_list = [];
        
        // Check if this request has approved ingredients (for completed requests)
        $ingredients_to_display = null;
        if (isset($request['approved_ingredients']) && !empty($request['approved_ingredients']) && $request['status'] === 'approved') {
            // Use approved ingredients for completed requests
            $ingredients_to_display = json_decode($request['approved_ingredients'], true);
        } else {
            // Use original ingredients for pending requests or when no approved ingredients
            $ingredients_to_display = json_decode($request['ingredients'], true);
        }
        
        // Fallback: if approved_ingredients column doesn't exist, always use original ingredients
        if (!isset($request['approved_ingredients'])) {
            $ingredients_to_display = json_decode($request['ingredients'], true);
        }
        
        if ($ingredients_to_display && is_array($ingredients_to_display)) {
            foreach ($ingredients_to_display as $ingredient) {
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
                } elseif (isset($ingredient['ingredient_name']) && isset($ingredient['quantity'])) {
                    // Handle new format with ingredient_name
                    $unit = isset($ingredient['unit']) ? $ingredient['unit'] : 'pieces';
                    $ingredients_list[] = $ingredient['ingredient_name'] . ' (' . $ingredient['quantity'] . ' ' . $unit . ')';
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
            'delivery_notes' => isset($request['delivery_notes']) ? $request['delivery_notes'] : '',
            'updated_by' => $request['updated_by_name'] ?: 'N/A'
        );
    }
    
    // For client-side processing, return all data
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