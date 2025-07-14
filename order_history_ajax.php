<?php
// DEBUG MODE: Show all errors directly in the response
error_reporting(E_ALL);
ini_set('display_errors', 1);

require_once 'db_connect.php';
require_once 'auth_function.php';

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

checkCashierLogin();

// Get parameters from DataTables
$start = isset($_POST['start']) ? intval($_POST['start']) : 0;
$length = isset($_POST['length']) ? intval($_POST['length']) : 10;
$search = isset($_POST['search']['value']) ? $_POST['search']['value'] : '';
$start_date = isset($_POST['start_date']) ? $_POST['start_date'] : date('Y-m-d', strtotime('-30 days'));

$month = isset($_POST['month']) ? $_POST['month'] : '';
$day = isset($_POST['day']) ? $_POST['day'] : '';
$start_time = isset($_POST['start_time']) ? $_POST['start_time'] : '';
$end_time = isset($_POST['end_time']) ? $_POST['end_time'] : '';

// Base query
$base_query = "
    FROM pos_order o
    LEFT JOIN pos_order_item oi ON o.order_id = oi.order_id
    LEFT JOIN pos_product p ON oi.product_id = p.product_id
    WHERE o.order_created_by = :user_id
    AND DATE(o.order_datetime) >= :start_date
";

// Search condition
if (!empty($search)) {
    $base_query .= " AND (
        o.order_number LIKE :search
        OR p.product_name LIKE :search
    )";
}

if ($month !== '') {
    $base_query .= " AND MONTH(o.order_datetime) = :month";
}
if ($day !== '') {
    $base_query .= " AND DAY(o.order_datetime) = :day";
}
if ($start_time !== '' && $end_time !== '') {
    $base_query .= " AND TIME(o.order_datetime) BETWEEN :start_time AND :end_time";
}

// Count total records (no filters)
$count_all_query = "SELECT COUNT(DISTINCT o.order_id) as total FROM pos_order o WHERE o.order_created_by = :user_id";
$stmt_all = $pdo->prepare($count_all_query);
$stmt_all->bindValue(':user_id', $_SESSION['user_id'], PDO::PARAM_INT);
$stmt_all->execute();
$total_records = $stmt_all->fetch(PDO::FETCH_ASSOC)['total'];

// Count filtered records (with filters)
$count_query = "SELECT COUNT(DISTINCT o.order_id) as total " . $base_query;
$stmt = $pdo->prepare($count_query);
$stmt->bindValue(':user_id', $_SESSION['user_id'], PDO::PARAM_INT);
$stmt->bindValue(':start_date', $start_date);
if (!empty($search)) {
    $stmt->bindValue(':search', "%$search%");
}
if ($month !== '') {
    $stmt->bindValue(':month', $month, PDO::PARAM_INT);
}
if ($day !== '') {
    $stmt->bindValue(':day', $day, PDO::PARAM_INT);
}
if ($start_time !== '' && $end_time !== '') {
    $stmt->bindValue(':start_time', $start_time);
    $stmt->bindValue(':end_time', $end_time);
}
$stmt->execute();
$filtered_records = $stmt->fetch(PDO::FETCH_ASSOC)['total'];

// Get filtered records
$query = "
    SELECT 
        o.order_id,
        o.order_number,
        o.order_datetime,
        o.order_total,
        GROUP_CONCAT(CONCAT(oi.product_qty, 'x ', p.product_name) SEPARATOR ', ') as items
    " . $base_query . "
    GROUP BY o.order_id
    ORDER BY o.order_datetime DESC
    LIMIT :start, :length
";

$stmt = $pdo->prepare($query);
$stmt->bindValue(':user_id', $_SESSION['user_id'], PDO::PARAM_INT);
$stmt->bindValue(':start_date', $start_date);
$stmt->bindValue(':start', $start, PDO::PARAM_INT);
$stmt->bindValue(':length', $length, PDO::PARAM_INT);
if (!empty($search)) {
    $stmt->bindValue(':search', "%$search%");
}
if ($month !== '') {
    $stmt->bindValue(':month', $month, PDO::PARAM_INT);
}
if ($day !== '') {
    $stmt->bindValue(':day', $day, PDO::PARAM_INT);
}
if ($start_time !== '' && $end_time !== '') {
    $stmt->bindValue(':start_time', $start_time);
    $stmt->bindValue(':end_time', $end_time);
}
$stmt->execute();
$records = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Prepare response
$response = [
    'draw' => isset($_POST['draw']) ? intval($_POST['draw']) : 1,
    'recordsTotal' => $total_records,
    'recordsFiltered' => $filtered_records,
    'data' => $records
];

// Add try-catch for error handling
try {
    header('Content-Type: application/json');
    echo json_encode($response);
    exit;
} catch (Exception $e) {
    header('Content-Type: application/json');
    echo json_encode([
        'draw' => isset($_POST['draw']) ? intval($_POST['draw']) : 1,
        'recordsTotal' => 0,
        'recordsFiltered' => 0,
        'data' => [],
        'error' => $e->getMessage()
    ]);
    exit;
} 