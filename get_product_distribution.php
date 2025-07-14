<?php
require_once 'db_connect.php';
require_once 'auth_function.php';
checkAdminLogin();
header('Content-Type: application/json');

$sql = "SELECT c.category_name, COUNT(p.product_id) as count
        FROM pos_category c
        LEFT JOIN pos_product p ON c.category_id = p.category_id
        GROUP BY c.category_id, c.category_name";
$stmt = $pdo->query($sql);

$labels = [];
$data = [];
while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
    $labels[] = $row['category_name'];
    $data[] = (int)$row['count'];
}
echo json_encode(['labels' => $labels, 'data' => $data]); 