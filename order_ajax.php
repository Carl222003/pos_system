<?php

require_once 'db_connect.php';
require_once 'auth_function.php';

if(isset($_GET['action'])){
    $columns = [
        0 => 'order_number',
        1 => 'order_total',
        2 => 'user_name',
        3 => 'order_datetime',
        4 => null
    ];
    
    $limit = $_GET['length'];
    $start = $_GET['start'];
    $order = $columns[$_GET['order'][0]['column']];
    $dir = $_GET['order'][0]['dir'];
    
    $searchValue = $_GET['search']['value'];
    
    // Get total records
    $totalRecordsStmt = $pdo->query("SELECT COUNT(*) FROM pos_order");
    $totalRecords = $totalRecordsStmt->fetchColumn();
    
    // Get total filtered records
    $filterQuery = "SELECT COUNT(*) FROM pos_order INNER JOIN pos_user ON pos_order.order_created_by = pos_user.user_id WHERE 1=1";
    if (!empty($searchValue)) {
        $filterQuery .= " AND (order_number LIKE '%$searchValue%' OR user_name LIKE '%$searchValue%' OR order_total LIKE '%$searchValue%')";
    }
    $totalFilteredRecordsStmt = $pdo->query($filterQuery);
    $totalFilteredRecords = $totalFilteredRecordsStmt->fetchColumn();
    
    // Fetch data
    $dataQuery = "SELECT * FROM pos_order INNER JOIN pos_user ON pos_order.order_created_by = pos_user.user_id WHERE 1=1";
    if (!empty($searchValue)) {
        $dataQuery .= " AND (order_number LIKE '%$searchValue%' OR user_name LIKE '%$searchValue%' OR order_total LIKE '%$searchValue%')";
    }
    $dataQuery .= " ORDER BY $order $dir LIMIT $start, $limit";
    $dataStmt = $pdo->query($dataQuery);
    $data = $dataStmt->fetchAll(PDO::FETCH_ASSOC);
    
    $response = [
        "draw"              => intval($_GET['draw']),
        "recordsTotal"      => intval($totalRecords),
        "recordsFiltered"   => intval($totalFilteredRecords),
        "data"              => $data
    ];
    
    echo json_encode($response);
}

// Check if category_id is provided in POST data
$input = json_decode(file_get_contents('php://input'), true);
//print_r($input);
if (isset($input['action']) && $input['action'] === 'get_products') {
    $categoryId = $input['category_id'] ?? '';
    
    try {
        if($categoryId === 'all') {
            // Get all active products
            $stmt = $pdo->prepare("
                SELECT p.*, c.category_name 
                FROM pos_product p
                LEFT JOIN pos_category c ON p.category_id = c.category_id
                WHERE p.product_status = 'Available'
                ORDER BY p.product_name ASC
            ");
            $stmt->execute();
        } else {
            // Get products for specific category
            $stmt = $pdo->prepare("
                SELECT p.*, c.category_name 
                FROM pos_product p
                LEFT JOIN pos_category c ON p.category_id = c.category_id
                WHERE p.category_id = :category_id 
                AND p.product_status = 'Available'
                ORDER BY p.product_name ASC
            ");
            $stmt->bindParam(':category_id', $categoryId, PDO::PARAM_INT);
            $stmt->execute();
        }

        // Fetch all results
        $products = $stmt->fetchAll(PDO::FETCH_ASSOC);

        // Return results as JSON
        echo json_encode($products);
        exit;
    } catch (PDOException $e) {
        echo json_encode([
            'error' => true,
            'message' => 'Error loading products: ' . $e->getMessage()
        ]);
        exit;
    }
}

if(isset($input['order_number'])) {
    try {
        // Start transaction
        $pdo->beginTransaction();
        
        // Prepare to insert order into pos_order table
        $stmt = $pdo->prepare("INSERT INTO pos_order (order_number, order_total, order_created_by) VALUES (?, ?, ?)");

        // Execute with parameters
        $stmt->execute([
            $input['order_number'],
            $input['order_total'],
            $input['order_created_by']
        ]);

        // Get the last inserted order_id
        $order_id = $pdo->lastInsertId();

        // Insert each item into pos_order_item table
        $stmt = $pdo->prepare("INSERT INTO pos_order_item (order_id, product_id, product_qty, product_price) VALUES (?, ?, ?, ?)");

        foreach ($input['items'] as $item) {
            $stmt->execute([
                $order_id,
                $item['product_id'],
                $item['product_qty'],
                $item['product_price']
            ]);
        }

        // Commit transaction
        $pdo->commit();

        header('Content-Type: application/json');
        echo json_encode(['success' => true, 'order_id' => $order_id]);
        exit;
    } catch (Exception $e) {
        // Rollback transaction on error
        $pdo->rollBack();
        
        header('Content-Type: application/json');
        echo json_encode(['success' => false, 'error' => $e->getMessage()]);
        exit;
    }
}

if(isset($_POST['id'])) {
    try {
        // Start transaction
        $pdo->beginTransaction();
        
        // Delete items from pos_order_item table
        $stmt = $pdo->prepare("DELETE FROM pos_order_item WHERE order_id = ?");
        $stmt->execute([$_POST['id']]);

        // Delete the order from pos_order table
        $stmt = $pdo->prepare("DELETE FROM pos_order WHERE order_id = ?");
        $stmt->execute([$_POST['id']]);
        
        // Commit transaction
        $pdo->commit();
        
        // Return success response
        echo json_encode(['success' => true]);
        exit;
    } catch (Exception $e) {
        // Rollback transaction on error
        $pdo->rollBack();
        echo json_encode(['success' => false, 'error' => $e->getMessage()]);
        exit;
    }
}

?>