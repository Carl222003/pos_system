<?php
require_once 'db_connect.php';
require_once 'auth_function.php';
checkAdminLogin();

header('Content-Type: application/json');

$branch_id = isset($_POST['branch_id']) ? intval($_POST['branch_id']) : 0;
$branch_name = $_POST['branch_name'] ?? '';
$branch_code = $_POST['branch_code'] ?? '';
$contact_number = $_POST['contact_number'] ?? '';
$email = $_POST['email'] ?? '';
$complete_address = $_POST['complete_address'] ?? '';
$operating_hours = $_POST['operating_hours'] ?? '';
$status = $_POST['status'] ?? 'Active';

if ($branch_id > 0 && $branch_name && $branch_code) {
    $stmt = $pdo->prepare('UPDATE pos_branch SET branch_name=?, branch_code=?, contact_number=?, email=?, complete_address=?, operating_hours=?, status=? WHERE branch_id=?');
    $result = $stmt->execute([$branch_name, $branch_code, $contact_number, $email, $complete_address, $operating_hours, $status, $branch_id]);
    if ($result) {
        echo json_encode(['success' => true]);
        exit;
    } else {
        echo json_encode(['success' => false, 'message' => 'Failed to update branch.']);
        exit;
    }
} else {
    echo json_encode(['success' => false, 'message' => 'Missing or invalid data.']);
    exit;
} 