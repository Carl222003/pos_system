<?php

require_once 'db_connect.php';
require_once 'product_functions.php';

// Get pagination parameters
$start = isset($_GET['start']) ? intval($_GET['start']) : 0;
$length = isset($_GET['length']) ? intval($_GET['length']) : 10;
$draw = isset($_GET['draw']) ? intval($_GET['draw']) : 1;

// Get search value
$search = isset($_GET['search']['value']) ? $_GET['search']['value'] : '';

// Get ordering parameters
$orderColumn = isset($_GET['order'][0]['column']) ? $_GET['order'][0]['column'] : 0;
$orderDir = isset($_GET['order'][0]['dir']) ? strtoupper($_GET['order'][0]['dir']) : 'DESC';

// Column mapping for ordering
$columns = [
    0 => 'p.product_id',
    1 => 'c.category_name',
    2 => 'p.product_name',
    3 => 'p.product_price',
    4 => 'p.description',
    5 => 'p.ingredients',
    6 => 'p.product_status',
    7 => 'p.product_image'
];

// Get the column name to order by
$orderColumnName = isset($columns[$orderColumn]) ? $columns[$orderColumn] : 'p.product_id';

try {
    // Base query
    $baseQuery = "FROM pos_product p 
                  LEFT JOIN pos_category c ON p.category_id = c.category_id";
    
    // Add status filter to exclude archived and inactive products
    $statusFilter = "p.product_status = 'Available'";

    // Search condition
    $searchCondition = "";
    $params = [];
    if (!empty($search)) {
        $searchCondition = " WHERE (" . $statusFilter . ") AND (\n"
            . "p.product_name LIKE :search \n"
            . "OR c.category_name LIKE :search\n"
            . "OR p.description LIKE :search\n"
            . "OR p.ingredients LIKE :search\n"
            . "OR p.product_status LIKE :search\n"
            . "OR CAST(p.product_price AS CHAR) LIKE :search\n"
            . ")";
        $params[':search'] = "%{$search}%";
    } else {
        $searchCondition = " WHERE " . $statusFilter;
    }

    // Get total records without filtering (excluding archived and inactive)
    $stmt = $pdo->query("SELECT COUNT(*) FROM pos_product WHERE product_status = 'Available'");
    $totalRecords = $stmt->fetchColumn();

    // Get filtered records count
    $stmt = $pdo->prepare("SELECT COUNT(*) " . $baseQuery . $searchCondition);
    if (!empty($search)) {
        $stmt->bindParam(':search', $params[':search']);
    }
    $stmt->execute();
    $filteredRecords = $stmt->fetchColumn();

    // Main query for data
    $query = "SELECT 
        p.product_id,
        COALESCE(c.category_name, 'Uncategorized') as category_name,
        p.product_name,
        p.product_price,
        p.description,
        p.ingredients,
        p.product_status,
        p.product_image
    " . $baseQuery . $searchCondition;
    
    // Add ordering
    $query .= " ORDER BY " . $orderColumnName . " " . $orderDir;
    
    // Add pagination
    if ($length > 0 && $length < 999999) {
        $query .= " LIMIT :start, :length";
    }

    // Prepare and execute the final query
    $stmt = $pdo->prepare($query);
    if (!empty($search)) {
        $stmt->bindParam(':search', $params[':search']);
    }
    if ($length > 0 && $length < 999999) {
        $stmt->bindParam(':start', $start, PDO::PARAM_INT);
        $stmt->bindParam(':length', $length, PDO::PARAM_INT);
    }
    $stmt->execute();
    $data = $stmt->fetchAll(PDO::FETCH_ASSOC);

    // Format the data for DataTables
    foreach ($data as &$row) {
        // Format the image URL
        $row['product_image'] = !empty($row['product_image']) ? $row['product_image'] : 'uploads/no-image.jpg';
        
        // Format empty values
        $row['description'] = !empty($row['description']) ? $row['description'] : '-';
        $row['ingredients'] = !empty($row['ingredients']) ? $row['ingredients'] : '-';
    }

    // Prepare the response
    $response = [
        "draw" => $draw,
        "recordsTotal" => $totalRecords,
        "recordsFiltered" => $filteredRecords,
        "data" => $data,
        "debug" => [
            "sql" => $query,
            "params" => $params,
            "start" => $start,
            "length" => $length
        ]
    ];

    header('Content-Type: application/json');
    echo json_encode($response);

} catch (PDOException $e) {
    // Handle any database errors
    $response = [
        "draw" => $draw,
        "error" => "Database error: " . $e->getMessage(),
        "recordsTotal" => 0,
        "recordsFiltered" => 0,
        "data" => []
    ];
    header('Content-Type: application/json');
    echo json_encode($response);
}

?>