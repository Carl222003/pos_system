<?php
require_once 'db_connect.php';
require_once 'auth_function.php';

requireLogin();
if ($_SESSION['user_type'] !== 'Stockman') {
    echo json_encode(['success' => false, 'error' => 'Access denied. Only Stockman can access this page.']);
    exit();
}

try {
    $user_id = $_SESSION['user_id'];
    $branch_id = $_SESSION['branch_id'] ?? null;

    // If branch_id is not in session, try to fetch from user record
    if (!$branch_id) {
        $stmt = $pdo->prepare('SELECT branch_id FROM pos_user WHERE user_id = ?');
        $stmt->execute([$user_id]);
        $branch_id = $stmt->fetchColumn();
    }

    if (!$branch_id) {
        echo json_encode(['success' => false, 'error' => 'Branch not found']);
        exit();
    }

    // Fetch ingredients for this stockman's branch
    $stmt = $pdo->prepare("
        SELECT 
            i.ingredient_id, 
            i.ingredient_name, 
            i.ingredient_unit, 
            i.ingredient_quantity,
            i.ingredient_status,
            c.category_name
        FROM ingredients i
        LEFT JOIN pos_category c ON i.category_id = c.category_id
        WHERE i.branch_id = ? AND i.ingredient_status = 'active'
        ORDER BY c.category_name, i.ingredient_name
    ");
    $stmt->execute([$branch_id]);
    $ingredients = $stmt->fetchAll(PDO::FETCH_ASSOC);

    echo json_encode([
        'success' => true,
        'ingredients' => $ingredients
    ]);

} catch (Exception $e) {
    echo json_encode([
        'success' => false,
        'error' => 'Database error: ' . $e->getMessage()
    ]);
}
?>
