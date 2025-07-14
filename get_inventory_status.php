<?php
require_once 'db_connect.php';
require_once 'auth_function.php';
checkAdminLogin();
header('Content-Type: application/json');

$sql = "SELECT p.product_name, i.current_stock, i.minimum_stock
        FROM pos_product p
        LEFT JOIN pos_inventory i ON p.product_id = i.product_id";
$stmt = $pdo->query($sql);

$labels = [];
$current_stock = [];
$minimum_stock = [];
while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
    $labels[] = $row['product_name'];
    $current_stock[] = (int)$row['current_stock'];
    $minimum_stock[] = (int)$row['minimum_stock'];
}
echo json_encode(['labels' => $labels, 'current_stock' => $current_stock, 'minimum_stock' => $minimum_stock]); 