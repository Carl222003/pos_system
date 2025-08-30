<?php
require_once 'db_connect.php';
require_once 'auth_function.php';

// Check admin login
checkAdminLogin();

header('Content-Type: application/json');

try {
    // Validate required fields
    $required_fields = ['category_id', 'ingredient_name', 'ingredient_quantity', 'ingredient_unit'];
    
    foreach ($required_fields as $field) {
        if (empty($_POST[$field])) {
            throw new Exception("$field is required");
        }
    }

    // Validate numeric fields
    if (!is_numeric($_POST['ingredient_quantity']) || $_POST['ingredient_quantity'] < 0) {
        throw new Exception("Quantity must be a positive number");
    }

    // Validate consume before date
    if (!empty($_POST['consume_before']) && !empty($_POST['date_added'])) {
        $dateAdded = new DateTime($_POST['date_added']);
        $consumeBefore = new DateTime($_POST['consume_before']);
        
        if ($consumeBefore <= $dateAdded) {
            throw new Exception("Consume before date must be after the date added");
        }
    }

    // Check if ingredient already exists (without branch restriction)
    $stmt = $pdo->prepare("SELECT COUNT(*) FROM ingredients WHERE ingredient_name = ? AND ingredient_status != 'archived'");
    $stmt->execute([$_POST['ingredient_name']]);
    if ($stmt->fetchColumn() > 0) {
        throw new Exception("Ingredient already exists");
    }

    // First, try to add the new columns if they don't exist
    try {
        $pdo->exec("ALTER TABLE ingredients ADD COLUMN IF NOT EXISTS minimum_stock DECIMAL(10,2) DEFAULT 0");
    } catch (Exception $e) {
        // Columns might already exist, continue
    }

    // Insert new ingredient with basic fields first
    $stmt = $pdo->prepare("
        INSERT INTO ingredients (
            category_id, 
            ingredient_name, 
            ingredient_quantity, 
            ingredient_unit, 
            ingredient_status,
            date_added,
            consume_before,
            notes
        ) VALUES (?, ?, ?, ?, ?, ?, ?, ?)
    ");

    $stmt->execute([
        $_POST['category_id'],
        $_POST['ingredient_name'],
        $_POST['ingredient_quantity'],
        $_POST['ingredient_unit'],
        $_POST['ingredient_status'] ?? 'Available',
        $_POST['date_added'] ?? date('Y-m-d'),
        $_POST['consume_before'] ?? null,
        $_POST['notes'] ?? null
    ]);

    $ingredient_id = $pdo->lastInsertId();

    // Update with additional fields if they exist
    try {
        $updateStmt = $pdo->prepare("
            UPDATE ingredients SET 
                minimum_stock = ?
            WHERE ingredient_id = ?
        ");
        
        $updateStmt->execute([
            $_POST['minimum_stock'] ?? 0,
            $ingredient_id
        ]);
    } catch (Exception $e) {
        // Additional fields might not exist yet, that's okay
    }

    // Log activity
    $admin_id = $_SESSION['user_id'] ?? null;
    if ($admin_id) {
        $ingredient_name = $_POST['ingredient_name'];
        logActivity($pdo, $admin_id, "Added ingredient", "Ingredient: $ingredient_name");
    }

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