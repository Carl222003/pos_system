<?php
require_once 'db_connect.php';
require_once 'auth_function.php';
checkAdminLogin();
header('Content-Type: application/json');

$sql = "SELECT b.branch_name, COALESCE(SUM(o.order_total), 0) as total_sales
        FROM pos_branch b
        LEFT JOIN pos_order o ON b.branch_id = o.branch_id
            AND DATE(o.order_datetime) = CURDATE()
        WHERE b.status = 'Active'
        GROUP BY b.branch_id, b.branch_name
        ORDER BY total_sales DESC";
$stmt = $pdo->query($sql);

$labels = [];
$data = [];
while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
    $labels[] = $row['branch_name'];
    $data[] = floatval($row['total_sales']);
}
echo json_encode(['labels' => $labels, 'data' => $data]); 