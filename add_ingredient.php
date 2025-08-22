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

    // Validate consume before date
    if (!empty($_POST['consume_before']) && !empty($_POST['date_added'])) {
        $dateAdded = new DateTime($_POST['date_added']);
        $consumeBefore = new DateTime($_POST['consume_before']);
        
        if ($consumeBefore <= $dateAdded) {
            throw new Exception("Consume before date must be after the date added");
        }
    }

    // Check if ingredient already exists in this branch
    $stmt = $pdo->prepare("SELECT COUNT(*) FROM ingredients WHERE ingredient_name = ? AND branch_id = ? AND ingredient_status != 'archived'");
    $stmt->execute([$_POST['ingredient_name'], $_POST['branch_id']]);
    if ($stmt->fetchColumn() > 0) {
        throw new Exception("Ingredient already exists in this branch");
    }

    // First, try to add the new columns if they don't exist
    try {
        $pdo->exec("ALTER TABLE ingredients ADD COLUMN IF NOT EXISTS minimum_stock DECIMAL(10,2) DEFAULT 0");
        $pdo->exec("ALTER TABLE ingredients ADD COLUMN IF NOT EXISTS storage_location VARCHAR(255)");
        $pdo->exec("ALTER TABLE ingredients ADD COLUMN IF NOT EXISTS cost_per_unit DECIMAL(10,2)");
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

    // Update with additional fields if they exist
    try {
        $updateStmt = $pdo->prepare("
            UPDATE ingredients SET 
                minimum_stock = ?,
                storage_location = ?,
                cost_per_unit = ?
            WHERE ingredient_id = ?
        ");
        
        $updateStmt->execute([
            $_POST['minimum_stock'] ?? 0,
            $_POST['storage_location'] ?? null,
            $_POST['cost_per_unit'] ?? null,
            $ingredient_id
        ]);
    } catch (Exception $e) {
        // Additional fields might not exist yet, that's okay
    }

    // Log activity
    $admin_id = $_SESSION['user_id'] ?? null;
    if ($admin_id) {
        $ingredient_name = $_POST['ingredient_name'];
        $branch_name = $pdo->query("SELECT branch_name FROM pos_branch WHERE branch_id = " . $_POST['branch_id'])->fetchColumn();
        logActivity($pdo, $admin_id, "Added ingredient", "Ingredient: $ingredient_name, Branch: $branch_name");
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