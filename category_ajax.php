<?php

require_once 'db_connect.php';

// Set JSON header
header('Content-Type: application/json');

try {
    // Handle active categories only
    $columns = [
        0 => 'category_id',
        1 => 'category_name',
        2 => 'status',
        3 => 'description'
    ];
    
    $limit = $_GET['length'];
    $start = $_GET['start'];
    $order = isset($_GET['order'][0]['column']) ? $columns[$_GET['order'][0]['column']] : 'category_id';
    $dir = isset($_GET['order'][0]['dir']) ? $_GET['order'][0]['dir'] : 'ASC';
    
    $searchValue = $_GET['search']['value'];
    
    // Get total active records (exclude those in archive table)
    $totalRecordsStmt = $pdo->query("SELECT COUNT(*) FROM pos_category WHERE category_id NOT IN (SELECT original_id FROM archive_category)");
    $totalRecords = $totalRecordsStmt->fetchColumn();
    
    // Get total filtered active records (exclude those in archive table)
    $filterQuery = "SELECT COUNT(*) FROM pos_category WHERE category_id NOT IN (SELECT original_id FROM archive_category)";
    if (!empty($searchValue)) {
        $filterQuery .= " AND (category_name LIKE '%$searchValue%' OR description LIKE '%$searchValue%' OR status LIKE '%$searchValue%')";
    }
    $totalFilteredRecordsStmt = $pdo->query($filterQuery);
    $totalFilteredRecords = $totalFilteredRecordsStmt->fetchColumn();
    
    // Fetch active data (exclude those in archive table)
    $dataQuery = "SELECT category_id, category_name, description, status FROM pos_category WHERE category_id NOT IN (SELECT original_id FROM archive_category)";
    if (!empty($searchValue)) {
        $dataQuery .= " AND (category_name LIKE '%$searchValue%' OR description LIKE '%$searchValue%' OR status LIKE '%$searchValue%')";
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

} catch (Exception $e) {
    // Return error response
    echo json_encode([
        "draw" => isset($_GET['draw']) ? intval($_GET['draw']) : 0,
        "recordsTotal" => 0,
        "recordsFiltered" => 0,
        "data" => [],
        "error" => "Database error: " . $e->getMessage()
    ]);
}
?>