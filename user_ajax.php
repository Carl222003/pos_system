<?php

require_once 'db_connect.php';

$columns = [
    0 => 'user_id',
    1 => 'user_name',
    2 => 'user_email',
    3 => 'user_type',
    4 => 'user_status',
    5 => null
];

$length = isset($_GET['length']) ? intval($_GET['length']) : 10;
$start = isset($_GET['start']) ? intval($_GET['start']) : 0;
$order = isset($_GET['order'][0]['column']) ? $columns[$_GET['order'][0]['column']] : 'user_id';
$dir = isset($_GET['order'][0]['dir']) ? $_GET['order'][0]['dir'] : 'ASC';
$searchValue = isset($_GET['search']['value']) ? $_GET['search']['value'] : '';

// Get total records (exclude those in archive table)
$totalRecordsStmt = $pdo->query("SELECT COUNT(*) FROM pos_user WHERE user_id NOT IN (SELECT original_id FROM archive_user)");
$totalRecords = $totalRecordsStmt->fetchColumn();

// Get total filtered records (exclude those in archive table)
$filterQuery = "SELECT COUNT(*) FROM pos_user WHERE user_id NOT IN (SELECT original_id FROM archive_user)";
if (!empty($searchValue)) {
    $filterQuery .= " AND (user_name LIKE '%$searchValue%' OR user_email LIKE '%$searchValue%' OR user_type LIKE '%$searchValue%' OR user_status LIKE '%$searchValue%')";
}
$totalFilteredRecordsStmt = $pdo->query($filterQuery);
$totalFilteredRecords = $totalFilteredRecordsStmt->fetchColumn();

// Fetch data (exclude those in archive table)
$dataQuery = "SELECT * FROM pos_user WHERE user_id NOT IN (SELECT original_id FROM archive_user)";
if (!empty($searchValue)) {
    $dataQuery .= " AND (user_name LIKE '%$searchValue%' OR user_email LIKE '%$searchValue%' OR user_type LIKE '%$searchValue%' OR user_status LIKE '%$searchValue%')";
}
$dataQuery .= " ORDER BY user_id ASC LIMIT $start, $length";
$dataStmt = $pdo->query($dataQuery);
$data = $dataStmt->fetchAll(PDO::FETCH_ASSOC);

$response = [
    "draw"              => isset($_GET['draw']) ? intval($_GET['draw']) : 1,
    "recordsTotal"      => intval($totalRecords),
    "recordsFiltered"   => intval($totalFilteredRecords),
    "data"              => $data
];

echo json_encode($response);

?>