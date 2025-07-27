<?php
require_once 'db_connect.php';
require_once 'auth_function.php';

header('Content-Type: application/json');

// Only allow POST
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(['success' => false, 'message' => 'Invalid request method.']);
    exit();
}

// Check if user is logged in and is a Stockman
if (!isset($_SESSION['user_logged_in']) || $_SESSION['user_logged_in'] !== true || $_SESSION['user_type'] !== 'Stockman') {
    echo json_encode(['success' => false, 'message' => 'Unauthorized access.']);
    exit();
}

$user_id = $_SESSION['user_id'];
$branch_id = $_SESSION['branch_id'] ?? null;

// If branch_id is not in session, try to fetch from user record
if (!$branch_id) {
    $stmt = $pdo->prepare('SELECT branch_id FROM pos_user WHERE user_id = ?');
    $stmt->execute([$user_id]);
    $branch_id = $stmt->fetchColumn();
}

if (!$branch_id) {
    echo json_encode(['success' => false, 'message' => 'Branch not found for this user.']);
    exit();
}

// Validate ingredients and quantities
$ingredients = $_POST['ingredients'] ?? [];
$quantities = $_POST['quantity'] ?? [];
$notes = $_POST['notes'] ?? '';

if (empty($ingredients) || !is_array($ingredients)) {
    echo json_encode(['success' => false, 'message' => 'No ingredients selected.']);
    exit();
}

// Build a string or JSON of requested ingredients and quantities
$ingredient_list = [];
foreach ($ingredients as $ingredient_id) {
    $qty = isset($quantities[$ingredient_id]) ? intval($quantities[$ingredient_id]) : 0;
    if ($qty > 0) {
        $ingredient_list[] = [
            'ingredient_id' => $ingredient_id,
            'quantity' => $qty
        ];
    }
}

if (empty($ingredient_list)) {
    echo json_encode(['success' => false, 'message' => 'No valid ingredient quantities provided.']);
    exit();
}

// Store as JSON
$ingredients_json = json_encode($ingredient_list);

try {
    $stmt = $pdo->prepare('INSERT INTO ingredient_requests (branch_id, request_date, ingredients, status, notes) VALUES (?, NOW(), ?, ?, ?)');
    $stmt->execute([
        $branch_id,
        $ingredients_json,
        'pending',
        $notes
    ]);
    echo json_encode(['success' => true, 'message' => 'Request submitted successfully.']);
} catch (Exception $e) {
    echo json_encode(['success' => false, 'message' => 'Database error: ' . $e->getMessage()]);
} 