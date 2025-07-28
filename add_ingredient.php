<?php
require_once 'db_connect.php';
require_once 'auth_function.php';

// Check admin login
checkAdminLogin();

header('Content-Type: application/json');

try {
    // Validate required fields
    $required_fields = ['category_id', 'ingredient_name', 'ingredient_quantity', 'ingredient_unit', 'branch_id'];
    
    foreach ($required_fields as $field) {
        if (empty($_POST[$field])) {
            throw new Exception("$field is required");
        }
    }

    // Validate numeric fields
    if (!is_numeric($_POST['ingredient_quantity']) || $_POST['ingredient_quantity'] < 0) {
        throw new Exception("Quantity must be a positive number");
    }

    // Check if ingredient already exists in this branch
    $stmt = $pdo->prepare("SELECT COUNT(*) FROM ingredients WHERE ingredient_name = ? AND branch_id = ? AND ingredient_status != 'archived'");
    $stmt->execute([$_POST['ingredient_name'], $_POST['branch_id']]);
    if ($stmt->fetchColumn() > 0) {
        throw new Exception("Ingredient already exists in this branch");
    }

    // Insert new ingredient
    $stmt = $pdo->prepare("
        INSERT INTO ingredients (
            category_id, 
            ingredient_name, 
            ingredient_quantity, 
            ingredient_unit, 
            ingredient_status,
            branch_id,
            date_added,
            consume_before,
            notes
        ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)
    ");

    $stmt->execute([
        $_POST['category_id'],
        $_POST['ingredient_name'],
        $_POST['ingredient_quantity'],
        $_POST['ingredient_unit'],
        $_POST['ingredient_status'] ?? 'Available',
        $_POST['branch_id'],
        $_POST['date_added'] ?? date('Y-m-d'),
        $_POST['consume_before'] ?? null,
        $_POST['notes'] ?? null
    ]);

    $ingredient_id = $pdo->lastInsertId();

    // Log activity
    $admin_id = $_SESSION['user_id'];
    $ingredient_name = $_POST['ingredient_name'];
    $branch_name = $pdo->query("SELECT branch_name FROM pos_branch WHERE branch_id = " . $_POST['branch_id'])->fetchColumn();
    logActivity($pdo, $admin_id, "Added ingredient", "Ingredient: $ingredient_name, Branch: $branch_name");

    echo json_encode([
        'success' => true,
        'message' => 'Ingredient added successfully'
    ]);

} catch (Exception $e) {
    echo json_encode([
        'success' => false,
        'message' => $e->getMessage()
    ]);
}
?> 