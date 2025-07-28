<?php

require_once 'db_connect.php';

// Check if DataTables parameters exist before using them
$draw = isset($_GET['draw']) ? intval($_GET['draw']) : 0;
$limit = isset($_GET['length']) ? intval($_GET['length']) : 10;
$start = isset($_GET['start']) ? intval($_GET['start']) : 0;
$orderColumnIndex = isset($_GET['order'][0]['column']) ? intval($_GET['order'][0]['column']) : 0;
$orderDir = isset($_GET['order'][0]['dir']) ? $_GET['order'][0]['dir'] : 'ASC';
$searchValue = isset($_GET['search']['value']) ? $_GET['search']['value'] : '';

// Column mapping for ordering
$columns = [
    0 => 'ingredient_id',
    1 => 'category_name',
    2 => 'ingredient_name',
    3 => 'ingredient_quantity',
    4 => 'ingredient_unit',
    5 => 'ingredient_status'
];

$orderColumn = isset($columns[$orderColumnIndex]) ? $columns[$orderColumnIndex] : 'ingredient_id';

// Get total records
$totalRecordsStmt = $pdo->query("SELECT COUNT(*) FROM ingredients WHERE ingredient_status != 'archived'");
$totalRecords = $totalRecordsStmt->fetchColumn();

// Get total filtered records
$filterQuery = "SELECT COUNT(*) FROM ingredients i
                LEFT JOIN pos_category c ON i.category_id = c.category_id
                LEFT JOIN pos_branch b ON i.branch_id = b.branch_id
                WHERE i.ingredient_status != 'archived'";

if (!empty($searchValue)) {
    $filterQuery .= " AND (i.ingredient_name LIKE :search 
                           OR c.category_name LIKE :search 
                           OR i.ingredient_status LIKE :search
                           OR b.branch_name LIKE :search)";
}

$filterStmt = $pdo->prepare($filterQuery);
if (!empty($searchValue)) {
    $filterStmt->bindValue(':search', "%$searchValue%", PDO::PARAM_STR);
}
$filterStmt->execute();
$totalFilteredRecords = $filterStmt->fetchColumn();

// Fetch data with ordering and pagination
$dataQuery = "SELECT i.ingredient_id, c.category_name, i.ingredient_name, 
                     i.ingredient_quantity, i.ingredient_unit, i.ingredient_status,
                     b.branch_name, i.branch_id
              FROM ingredients i 
              LEFT JOIN pos_category c ON i.category_id = c.category_id 
              LEFT JOIN pos_branch b ON i.branch_id = b.branch_id
              WHERE i.ingredient_status != 'archived'";

if (!empty($searchValue)) {
    $dataQuery .= " AND (i.ingredient_name LIKE :search 
                         OR c.category_name LIKE :search 
                         OR i.ingredient_status LIKE :search
                         OR b.branch_name LIKE :search)";
}

$dataQuery .= " ORDER BY $orderColumn $orderDir LIMIT :start, :limit";
$dataStmt = $pdo->prepare($dataQuery);

if (!empty($searchValue)) {
    $dataStmt->bindValue(':search', "%$searchValue%", PDO::PARAM_STR);
}
$dataStmt->bindValue(':start', $start, PDO::PARAM_INT);
$dataStmt->bindValue(':limit', $limit, PDO::PARAM_INT);
$dataStmt->execute();
$data = $dataStmt->fetchAll(PDO::FETCH_ASSOC);

// Return JSON response
$response = [
    "draw"              => $draw,
    "recordsTotal"      => intval($totalRecords),
    "recordsFiltered"   => intval($totalFilteredRecords),
    "data"              => $data
];

echo json_encode($response);
?>
