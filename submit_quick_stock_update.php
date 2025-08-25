<?php
require_once 'db_connect.php';
require_once 'auth_function.php';

requireLogin();
if ($_SESSION['user_type'] !== 'Stockman') {
    echo json_encode(['success' => false, 'error' => 'Access denied. Only Stockman can access this page.']);
    exit();
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(['success' => false, 'error' => 'Invalid request method']);
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

    // Validate required fields
    $required_fields = ['ingredient_id', 'quantity', 'unit', 'reason', 'update_type'];
    foreach ($required_fields as $field) {
        if (empty($_POST[$field])) {
            echo json_encode(['success' => false, 'error' => "Missing required field: $field"]);
            exit();
        }
    }

    $ingredient_id = $_POST['ingredient_id'];
    $quantity = floatval($_POST['quantity']);
    $unit = $_POST['unit'];
    $reason = $_POST['reason'];
    $notes = $_POST['notes'] ?? '';
    $update_type = $_POST['update_type'];

    // Validate quantity
    if ($quantity <= 0) {
        echo json_encode(['success' => false, 'error' => 'Quantity must be greater than 0']);
        exit();
    }

    // Get current ingredient data
    $stmt = $pdo->prepare("
        SELECT ingredient_id, ingredient_name, ingredient_quantity, ingredient_unit, ingredient_status
        FROM ingredients 
        WHERE ingredient_id = ? AND branch_id = ? AND ingredient_status = 'active'
    ");
    $stmt->execute([$ingredient_id, $branch_id]);
    $ingredient = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$ingredient) {
        echo json_encode(['success' => false, 'error' => 'Ingredient not found or not accessible']);
        exit();
    }

    // Calculate new quantity based on update type
    $current_quantity = floatval($ingredient['ingredient_quantity']);
    $new_quantity = $current_quantity;

    switch ($update_type) {
        case 'add':
            $new_quantity = $current_quantity + $quantity;
            $action = 'add';
            $message = "Added $quantity $unit to $ingredient[ingredient_name]. New total: $new_quantity $unit";
            break;
        case 'reduce':
            if ($quantity > $current_quantity) {
                echo json_encode(['success' => false, 'error' => "Cannot reduce more than current stock ($current_quantity $unit)"]);
                exit();
            }
            $new_quantity = $current_quantity - $quantity;
            $action = 'reduce';
            $message = "Reduced $quantity $unit from $ingredient[ingredient_name]. New total: $new_quantity $unit";
            break;
        case 'set':
            $new_quantity = $quantity;
            $action = 'set';
            $message = "Set stock for $ingredient[ingredient_name] to $new_quantity $unit";
            break;
        default:
            echo json_encode(['success' => false, 'error' => 'Invalid update type']);
            exit();
    }

    // Start transaction
    $pdo->beginTransaction();

    try {
        // Update ingredient quantity
        $stmt = $pdo->prepare("
            UPDATE ingredients 
            SET ingredient_quantity = ?, last_updated = NOW() 
            WHERE ingredient_id = ?
        ");
        $stmt->execute([$new_quantity, $ingredient_id]);

        // Log the stock movement
        $stmt = $pdo->prepare("
            INSERT INTO activity_log (
                user_id, 
                action, 
                table_name, 
                record_id, 
                old_value, 
                new_value, 
                notes, 
                created_at
            ) VALUES (?, ?, ?, ?, ?, ?, ?, NOW())
        ");
        $stmt->execute([
            $user_id,
            $action,
            'ingredients',
            $ingredient_id,
            $current_quantity . ' ' . $ingredient['ingredient_unit'],
            $new_quantity . ' ' . $unit,
            "Quick update: $reason" . ($notes ? " - $notes" : '')
        ]);

        // Commit transaction
        $pdo->commit();

        echo json_encode([
            'success' => true,
            'message' => $message,
            'old_quantity' => $current_quantity,
            'new_quantity' => $new_quantity,
            'ingredient_name' => $ingredient['ingredient_name']
        ]);

    } catch (Exception $e) {
        $pdo->rollBack();
        throw $e;
    }

} catch (Exception $e) {
    echo json_encode([
        'success' => false,
        'error' => 'Database error: ' . $e->getMessage()
    ]);
}
?>
