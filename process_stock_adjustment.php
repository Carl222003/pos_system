<?php
require_once 'db_connect.php';
require_once 'auth_function.php';

// Check if user is logged in and is a stockman
if (!isset($_SESSION['user_logged_in']) || $_SESSION['user_logged_in'] !== true || $_SESSION['user_type'] !== 'Stockman') {
    header('Content-Type: application/json');
    echo json_encode(['success' => false, 'message' => 'Unauthorized access']);
    exit();
}

header('Content-Type: application/json');

try {
    // Validate required fields
    $required_fields = ['ingredient_id', 'adjustment_type', 'adjustment_quantity', 'adjustment_reason'];
    
    foreach ($required_fields as $field) {
        if (empty($_POST[$field])) {
            throw new Exception("$field is required");
        }
    }

    // Validate status if provided
    $new_status = $_POST['ingredient_status'] ?? null;
    if ($new_status && !in_array($new_status, ['Available', 'Unavailable'])) {
        throw new Exception("Invalid status value");
    }

    $ingredient_id = $_POST['ingredient_id'];
    $adjustment_type = $_POST['adjustment_type'];
    $adjustment_quantity = floatval($_POST['adjustment_quantity']);
    $adjustment_reason = $_POST['adjustment_reason'];
    $branch_id = $_SESSION['branch_id'];
    $stockman_id = $_SESSION['user_id'];

    // Validate adjustment quantity
    if ($adjustment_quantity <= 0) {
        throw new Exception("Adjustment quantity must be greater than 0");
    }

    // Get current ingredient details
    $stmt = $pdo->prepare("
        SELECT ingredient_id, ingredient_name, ingredient_quantity, ingredient_unit, ingredient_status
        FROM ingredients 
        WHERE ingredient_id = ? AND branch_id = ?
    ");
    $stmt->execute([$ingredient_id, $branch_id]);
    $ingredient = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$ingredient) {
        throw new Exception("Ingredient not found or not accessible");
    }

    $current_quantity = floatval($ingredient['ingredient_quantity']);
    $new_quantity = 0;

    // Calculate new quantity based on adjustment type
    switch ($adjustment_type) {
        case 'add':
            $new_quantity = $current_quantity + $adjustment_quantity;
            break;
        case 'subtract':
            if ($adjustment_quantity > $current_quantity) {
                throw new Exception("Cannot subtract more than current stock");
            }
            $new_quantity = $current_quantity - $adjustment_quantity;
            break;
        case 'set':
            $new_quantity = $adjustment_quantity;
            break;
        default:
            throw new Exception("Invalid adjustment type");
    }

    // Update ingredient quantity and status
    $final_status = $new_status;
    if (!$new_status) {
        // Auto-determine status based on quantity if not manually set
        $final_status = $new_quantity == 0 ? 'Out of Stock' : 
                       ($new_quantity <= 5 ? 'Low Stock' : 'Available');
    }
    
    $stmt = $pdo->prepare("
        UPDATE ingredients 
        SET ingredient_quantity = ?, 
            ingredient_status = ?
        WHERE ingredient_id = ? AND branch_id = ?
    ");
    $stmt->execute([$new_quantity, $final_status, $ingredient_id, $branch_id]);

    // Log the adjustment
    $stmt = $pdo->prepare("
        INSERT INTO stock_adjustments (
            ingredient_id, 
            branch_id, 
            stockman_id, 
            adjustment_type, 
            old_quantity, 
            new_quantity, 
            adjustment_quantity, 
            old_status,
            new_status,
            reason, 
            adjustment_date
        ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, NOW())
    ");
    $stmt->execute([
        $ingredient_id,
        $branch_id,
        $stockman_id,
        $adjustment_type,
        $current_quantity,
        $new_quantity,
        $adjustment_quantity,
        $ingredient['ingredient_status'],
        $final_status,
        $adjustment_reason
    ]);

    // Log activity
    $ingredient_name = $ingredient['ingredient_name'];
    $status_change = $ingredient['ingredient_status'] !== $final_status ? " (Status: {$ingredient['ingredient_status']} → $final_status)" : "";
    $activity_message = "Stock adjustment: $ingredient_name - $adjustment_type $adjustment_quantity {$ingredient['ingredient_unit']}$status_change (Reason: $adjustment_reason)";
    logActivity($pdo, $stockman_id, "Stock Adjustment", $activity_message);

    $status_message = $ingredient['ingredient_status'] !== $final_status ? " Status updated to: $final_status." : "";
    echo json_encode([
        'success' => true,
        'message' => "Stock adjusted successfully. New quantity: $new_quantity {$ingredient['ingredient_unit']}.$status_message"
    ]);

} catch (Exception $e) {
    echo json_encode([
        'success' => false,
        'message' => $e->getMessage()
    ]);
}
?> 