<?php
require_once 'db_connect.php';
require_once 'auth_function.php';

header('Content-Type: application/json');

// Check if user is logged in and is a stockman
if (!isset($_SESSION['user_logged_in']) || $_SESSION['user_logged_in'] !== true || $_SESSION['user_type'] !== 'Stockman') {
    echo json_encode(['success' => false, 'message' => 'Unauthorized access.']);
    exit();
}

// Only allow POST
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(['success' => false, 'message' => 'Invalid request method.']);
    exit();
}

try {
    // Get JSON input
    $input = json_decode(file_get_contents('php://input'), true);
    
    if (!$input) {
        echo json_encode(['success' => false, 'message' => 'Invalid JSON input.']);
        exit();
    }
    
    // Validate required fields
    $required_fields = ['ingredient_id', 'new_quantity', 'reason'];
    foreach ($required_fields as $field) {
        if (!isset($input[$field]) || ($field !== 'new_quantity' && empty($input[$field]))) {
            echo json_encode(['success' => false, 'message' => "Missing required field: $field"]);
            exit();
        }
    }
    
    // Special validation for new_quantity (allow 0)
    if (!isset($input['new_quantity']) || $input['new_quantity'] === '' || $input['new_quantity'] === null) {
        echo json_encode(['success' => false, 'message' => "Missing required field: new_quantity"]);
        exit();
    }
    
    $user_id = $_SESSION['user_id'];
    $branch_id = $_SESSION['branch_id'] ?? null;
    $ingredient_id = intval($input['ingredient_id']);
    $new_quantity = floatval($input['new_quantity']);
    $reason = trim($input['reason']);
    $notes = isset($input['notes']) ? trim($input['notes']) : '';
    
    // Validate quantity
    if ($new_quantity < 0) {
        echo json_encode(['success' => false, 'message' => 'Quantity cannot be negative.']);
        exit();
    }
    
    // If branch_id is not in session, try to fetch from user record
    if (!$branch_id) {
        $stmt = $pdo->prepare('SELECT branch_id FROM pos_user WHERE user_id = ?');
        $stmt->execute([$user_id]);
        $branch_id = $stmt->fetchColumn();
    }
    
    if (!$branch_id) {
        echo json_encode(['success' => false, 'message' => 'No branch available. Please contact administrator.']);
        exit();
    }
    
    // Check if ingredient exists and is assigned to stockman's branch
    $stmt = $pdo->prepare("SELECT i.ingredient_id, i.ingredient_name, bi.quantity as ingredient_quantity, i.ingredient_unit, b.branch_name
                          FROM ingredients i 
                          INNER JOIN branch_ingredient bi ON i.ingredient_id = bi.ingredient_id
                          JOIN pos_branch b ON bi.branch_id = b.branch_id
                          WHERE i.ingredient_id = ? AND bi.branch_id = ? AND bi.status = 'active'");
    $stmt->execute([$ingredient_id, $branch_id]);
    $ingredient = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if (!$ingredient) {
        echo json_encode(['success' => false, 'message' => 'Ingredient not found or not assigned to your branch.']);
        exit();
    }
    
    $old_quantity = $ingredient['ingredient_quantity'];
    $quantity_change = $new_quantity - $old_quantity;
    
    // Start transaction
    $pdo->beginTransaction();
    
    try {
        // Update ingredient quantity in branch_ingredient table
        $stmt = $pdo->prepare("UPDATE branch_ingredient SET quantity = ? WHERE ingredient_id = ? AND branch_id = ?");
        $stmt->execute([$new_quantity, $ingredient_id, $branch_id]);
        
        // Log the adjustment in activity_log
        $log_message = "Quantity adjusted for {$ingredient['ingredient_name']}: {$old_quantity} → {$new_quantity} {$ingredient['ingredient_unit']} (Change: " . ($quantity_change >= 0 ? '+' : '') . $quantity_change . ") - Reason: {$reason}";
        
        if (!empty($notes)) {
            $log_message .= " - Notes: {$notes}";
        }
        
        $stmt = $pdo->prepare("INSERT INTO activity_log (user_id, action, details, ip_address) VALUES (?, ?, ?, ?)");
        $stmt->execute([$user_id, 'quantity_adjustment', $log_message, $_SERVER['REMOTE_ADDR']]);
        
        // Create stock movement log entry
        $movement_type = 'adjustment';
        $movement_quantity = abs($quantity_change);
        
        if ($movement_quantity > 0) {
            $stmt = $pdo->prepare("INSERT INTO stock_movements (ingredient_id, branch_id, movement_type, quantity, reason, performed_by, movement_date) VALUES (?, ?, ?, ?, ?, ?, NOW())");
            $stmt->execute([
                $ingredient_id,
                $branch_id,
                $movement_type,
                $movement_quantity,
                $reason . ($notes ? " - Notes: " . $notes : ""),
                $user_id
            ]);
        }
        
        // Commit transaction
        $pdo->commit();
        
        // Prepare success message
        $change_text = $quantity_change > 0 ? "increased by " . $quantity_change : ($quantity_change < 0 ? "decreased by " . abs($quantity_change) : "remains unchanged");
        $success_message = "Quantity for {$ingredient['ingredient_name']} has been {$change_text} {$ingredient['ingredient_unit']}. New quantity: {$new_quantity} {$ingredient['ingredient_unit']}";
        
        echo json_encode([
            'success' => true,
            'message' => $success_message,
            'old_quantity' => $old_quantity,
            'new_quantity' => $new_quantity,
            'quantity_change' => $quantity_change
        ]);
        
    } catch (Exception $e) {
        // Rollback transaction on error
        $pdo->rollBack();
        throw $e;
    }
    
} catch (PDOException $e) {
    error_log("Database error in adjust_ingredient_quantity.php: " . $e->getMessage());
    echo json_encode(['success' => false, 'message' => 'Database error occurred. Please try again.']);
} catch (Exception $e) {
    error_log("Error in adjust_ingredient_quantity.php: " . $e->getMessage());
    echo json_encode(['success' => false, 'message' => 'An error occurred. Please try again.']);
}
?>
