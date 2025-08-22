<?php
require_once 'db_connect.php';

echo "=== DATABASE TABLES ===\n";
$stmt = $pdo->query('SHOW TABLES');
while($row = $stmt->fetch()) {
    echo $row[0] . "\n";
}

echo "\n=== POS_BRANCH TABLE STRUCTURE ===\n";
$stmt = $pdo->query('DESCRIBE pos_branch');
while($row = $stmt->fetch()) {
    echo $row['Field'] . " - " . $row['Type'] . "\n";
}

echo "\n=== POS_USER TABLE STRUCTURE ===\n";
$stmt = $pdo->query('DESCRIBE pos_user');
while($row = $stmt->fetch()) {
    echo $row['Field'] . " - " . $row['Type'] . "\n";
}

echo "\n=== POS_ORDER TABLE STRUCTURE ===\n";
$stmt = $pdo->query('DESCRIBE pos_order');
while($row = $stmt->fetch()) {
    echo $row['Field'] . " - " . $row['Type'] . "\n";
}

echo "\n=== SAMPLE BRANCH DATA ===\n";
$stmt = $pdo->query('SELECT * FROM pos_branch LIMIT 3');
while($row = $stmt->fetch()) {
    echo "Branch ID: " . $row['branch_id'] . ", Name: " . $row['branch_name'] . ", Status: " . $row['status'] . "\n";
}

echo "\n=== SAMPLE USER DATA ===\n";
$stmt = $pdo->query('SELECT user_id, user_name, user_type, branch_id, user_status FROM pos_user LIMIT 3');
while($row = $stmt->fetch()) {
    echo "User ID: " . $row['user_id'] . ", Name: " . $row['user_name'] . ", Type: " . $row['user_type'] . ", Branch: " . $row['branch_id'] . ", Status: " . $row['user_status'] . "\n";
}
?> 