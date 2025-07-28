<?php
require_once 'db_connect.php';
require_once 'auth_function.php';
checkAdminLogin();

header('Content-Type: application/json');

$branch_id = isset($_GET['id']) ? intval($_GET['id']) : 0;
if ($branch_id > 0) {
    $stmt = $pdo->prepare('SELECT * FROM pos_branch WHERE branch_id = ?');
    $stmt->execute([$branch_id]);
    $branch = $stmt->fetch(PDO::FETCH_ASSOC);
    if ($branch) {
        echo json_encode(['success' => true, 'data' => $branch]);
        exit;
    } else {
        echo json_encode(['success' => false, 'message' => 'Branch not found.']);
        exit;
    }
} else {
    echo json_encode(['success' => false, 'message' => 'Invalid branch ID.']);
    exit;
} 