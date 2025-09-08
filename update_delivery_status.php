<?php
require_once 'db_connect.php';
require_once 'auth_function.php';

// Check if user is logged in and is a stockman
if (!isset($_SESSION['user_logged_in']) || $_SESSION['user_logged_in'] !== true || $_SESSION['user_type'] !== 'Stockman') {
    header('Content-Type: application/json');
    echo json_encode(['success' => false, 'message' => 'Unauthorized access. Only Stockman can update delivery status.']);
    exit();
}

header('Content-Type: application/json');

try {
    // Get JSON input from request body
    $input = json_decode(file_get_contents('php://input'), true);
    
    // Debug logging
    error_log("Delivery Update Debug - Input: " . json_encode($input));
    
    if (!$input || !isset($input['request_id']) || !isset($input['delivery_status'])) {
        throw new Exception('Missing required parameters');
    }

    $requestId = $input['request_id'];
    $deliveryStatus = $input['delivery_status'];
    $deliveryDate = isset($input['delivery_date']) && !empty($input['delivery_date']) ? $input['delivery_date'] : null;
    $deliveryNotes = isset($input['delivery_notes']) ? $input['delivery_notes'] : '';
    $itemChecklist = isset($input['item_checklist']) ? $input['item_checklist'] : [];
    $returnItems = isset($input['return_items']) ? $input['return_items'] : [];
    $updatedBy = $_SESSION['user_id'];
    $updateDate = date('Y-m-d H:i:s');
    $branchId = $_SESSION['branch_id'];

    // Validate delivery status (include all statuses for complete functionality)
    $validStatuses = ['pending', 'on_delivery', 'delivered', 'partially_delivered', 'returned', 'cancelled'];
    if (!in_array($deliveryStatus, $validStatuses)) {
        throw new Exception('Invalid delivery status');
    }

    // If status is delivered or partially delivered and no delivery date provided, use current date
    if (($deliveryStatus === 'delivered' || $deliveryStatus === 'partially_delivered') && empty($deliveryDate)) {
        $deliveryDate = date('Y-m-d H:i:s');
    }

    // Check if the request belongs to this stockman's branch
    $check_request = $pdo->prepare("SELECT request_id FROM ingredient_requests WHERE request_id = ? AND branch_id = ?");
    $check_request->execute([$requestId, $branchId]);
    if (!$check_request->fetch()) {
        throw new Exception('Request not found or not accessible for this branch');
    }

    // First, check if delivery_status column exists in ingredient_requests, if not create it
    $check_columns = $pdo->query("SHOW COLUMNS FROM ingredient_requests LIKE 'delivery_status'");
    if ($check_columns->rowCount() == 0) {
        // Add the missing columns
        $pdo->exec("ALTER TABLE ingredient_requests 
                   ADD COLUMN delivery_status ENUM('pending', 'on_delivery', 'delivered', 'partially_delivered', 'returned', 'cancelled') DEFAULT 'pending' 
                   AFTER status");
        $pdo->exec("ALTER TABLE ingredient_requests 
                   ADD COLUMN delivery_date TIMESTAMP NULL 
                   AFTER delivery_status");
        $pdo->exec("ALTER TABLE ingredient_requests 
                   ADD COLUMN delivery_notes TEXT NULL 
                   AFTER delivery_date");
    }
    
    // Check if delivery_status column exists in branch_ingredient table, if not create it
    $check_branch_columns = $pdo->query("SHOW COLUMNS FROM branch_ingredient LIKE 'delivery_status'");
    if ($check_branch_columns->rowCount() == 0) {
        // Add delivery status columns to branch_ingredient table
        $pdo->exec("ALTER TABLE branch_ingredient 
                   ADD COLUMN delivery_status ENUM('pending', 'on_delivery', 'delivered', 'partially_delivered', 'returned', 'cancelled') DEFAULT NULL 
                   AFTER status");
        $pdo->exec("ALTER TABLE branch_ingredient 
                   ADD COLUMN delivery_date TIMESTAMP NULL 
                   AFTER delivery_status");
        $pdo->exec("ALTER TABLE branch_ingredient 
                   ADD COLUMN delivery_notes TEXT NULL 
                   AFTER delivery_date");
    }

    // Update delivery status
    $query = "UPDATE ingredient_requests 
              SET delivery_status = :delivery_status,
                  delivery_date = :delivery_date,
                  delivery_notes = :delivery_notes,
                  updated_by = :updated_by,
                  updated_at = :updated_at
              WHERE request_id = :request_id AND branch_id = :branch_id";

    $stmt = $pdo->prepare($query);
    $stmt->bindParam(':delivery_status', $deliveryStatus);
    $stmt->bindParam(':delivery_date', $deliveryDate);
    $stmt->bindParam(':delivery_notes', $deliveryNotes);
    $stmt->bindParam(':updated_by', $updatedBy);
    $stmt->bindParam(':updated_at', $updateDate);
    $stmt->bindParam(':request_id', $requestId);
    $stmt->bindParam(':branch_id', $branchId);

    if ($stmt->execute()) {
        // Get request details for processing
        $request_info = $pdo->prepare("SELECT branch_id, ingredients, approved_ingredients FROM ingredient_requests WHERE request_id = ?");
        $request_info->execute([$requestId]);
        $request_data = $request_info->fetch(PDO::FETCH_ASSOC);
        
        if ($request_data) {
            $branch_name = $pdo->query("SELECT branch_name FROM pos_branch WHERE branch_id = " . $request_data['branch_id'])->fetchColumn();
            
            // Process only checked items from the item checklist
            $processed_ingredients = [];
            $total_received_quantity = 0;
            
            // First, collect return quantities for each ingredient
            $return_quantities = [];
            if (!empty($returnItems) && is_array($returnItems)) {
                foreach ($returnItems as $returnItem) {
                    if (isset($returnItem['item_name']) && isset($returnItem['return_quantity'])) {
                        $return_quantities[$returnItem['item_name']] = floatval($returnItem['return_quantity']);
                    }
                }
            }
            
            if (!empty($itemChecklist) && is_array($itemChecklist)) {
                error_log("Processing item checklist: " . json_encode($itemChecklist));
                foreach ($itemChecklist as $item) {
                    error_log("Processing item: " . json_encode($item));
                    if (isset($item['received']) && $item['received'] && isset($item['quantity']) && $item['quantity'] > 0) {
                        $ingredient_name = $item['name'];
                        $received_quantity = floatval($item['quantity']);
                        
                        // Subtract return quantity from received quantity
                        $return_quantity = isset($return_quantities[$ingredient_name]) ? $return_quantities[$ingredient_name] : 0;
                        $actual_received_quantity = $received_quantity - $return_quantity;
                        
                        error_log("Stock Update Debug - $ingredient_name: Received=$received_quantity, Return=$return_quantity, Net=$actual_received_quantity");
                        
                        // Only process if there's a positive quantity to add
                        if ($actual_received_quantity > 0) {
                            // Find the ingredient ID by name
                            $ingredient_stmt = $pdo->prepare("SELECT ingredient_id FROM ingredients WHERE ingredient_name = ?");
                            $ingredient_stmt->execute([$ingredient_name]);
                            $ingredient_id = $ingredient_stmt->fetchColumn();
                            
                            if ($ingredient_id) {
                                // Check if the ingredient exists in branch_ingredient
                                $check_branch_ingredient = $pdo->prepare("SELECT branch_ingredient_id, quantity FROM branch_ingredient WHERE ingredient_id = ? AND branch_id = ? AND status = 'active'");
                                $check_branch_ingredient->execute([$ingredient_id, $request_data['branch_id']]);
                                $branch_ingredient = $check_branch_ingredient->fetch(PDO::FETCH_ASSOC);
                                
                                if ($branch_ingredient) {
                                    // Update existing branch ingredient - ADD the actual received quantity to current stock
                                    $current_stock = floatval($branch_ingredient['quantity']);
                                    $new_quantity = $current_stock + $actual_received_quantity;
                                    
                                    error_log("Stock Update Debug - $ingredient_name: Current Stock=$current_stock, Adding=$actual_received_quantity, New Stock=$new_quantity");
                                    
                                    $update_branch_ingredient = $pdo->prepare("
                                        UPDATE branch_ingredient 
                                        SET quantity = ?, 
                                            delivery_status = ?, 
                                            delivery_date = ?, 
                                            delivery_notes = ?
                                        WHERE ingredient_id = ? AND branch_id = ? AND status = 'active'
                                    ");
                                    $update_branch_ingredient->execute([
                                        $new_quantity,
                                        $deliveryStatus,
                                        $deliveryDate,
                                        $deliveryNotes,
                                        $ingredient_id,
                                        $request_data['branch_id']
                                    ]);
                                } else {
                                    // Create new branch ingredient entry with actual received quantity
                                    $insert_branch_ingredient = $pdo->prepare("
                                        INSERT INTO branch_ingredient (
                                            branch_id, 
                                            ingredient_id, 
                                            quantity, 
                                            minimum_stock, 
                                            status, 
                                            delivery_status, 
                                            delivery_date, 
                                            delivery_notes
                                        ) VALUES (?, ?, ?, 5, 'active', ?, ?, ?)
                                    ");
                                    $insert_branch_ingredient->execute([
                                        $request_data['branch_id'],
                                        $ingredient_id,
                                        $actual_received_quantity,
                                        $deliveryStatus,
                                        $deliveryDate,
                                        $deliveryNotes
                                    ]);
                                }
                                
                                // Log stock movement for received items
                                $movement_stmt = $pdo->prepare("
                                    INSERT INTO stock_movements (
                                        ingredient_id, 
                                        branch_id, 
                                        movement_type, 
                                        quantity, 
                                        reason, 
                                        reference_id, 
                                        reference_type, 
                                        movement_date, 
                                        performed_by
                                    ) VALUES (?, ?, 'addition', ?, ?, ?, 'delivery_received', NOW(), ?)
                                ");
                                
                                $movement_reason = "Item received from delivery: $ingredient_name - Requested: $received_quantity, Returned: $return_quantity, Actual Received: $actual_received_quantity";
                                $movement_stmt->execute([
                                    $ingredient_id,
                                    $request_data['branch_id'],
                                    $actual_received_quantity,
                                    $movement_reason,
                                    $requestId,
                                    $updatedBy ?: null
                                ]);
                                
                                $processed_ingredients[] = $ingredient_name . " (" . $actual_received_quantity . " " . $item['unit'] . ")";
                                $total_received_quantity += $actual_received_quantity;
                            }
                        }
                    }
                }
            }
            
            // Process return items if any (for logging purposes only)
            if (!empty($returnItems) && is_array($returnItems)) {
                foreach ($returnItems as $returnItem) {
                    if (isset($returnItem['return_quantity']) && $returnItem['return_quantity'] > 0) {
                        $ingredient_name = $returnItem['item_name'];
                        $return_quantity = floatval($returnItem['return_quantity']);
                        $reasons = isset($returnItem['reasons']) ? implode(', ', $returnItem['reasons']) : 'Not specified';
                        
                        // Find the ingredient ID by name
                        $ingredient_stmt = $pdo->prepare("SELECT ingredient_id FROM ingredients WHERE ingredient_name = ?");
                        $ingredient_stmt->execute([$ingredient_name]);
                        $ingredient_id = $ingredient_stmt->fetchColumn();
                        
                        if ($ingredient_id) {
                            // Log return movement
                            $movement_stmt = $pdo->prepare("
                                INSERT INTO stock_movements (
                                    ingredient_id, 
                                    branch_id, 
                                    movement_type, 
                                    quantity, 
                                    reason, 
                                    reference_id, 
                                    reference_type, 
                                    movement_date, 
                                    performed_by
                                ) VALUES (?, ?, 'return', ?, ?, ?, 'delivery_return', NOW(), ?)
                            ");
                            
                            $movement_reason = "Item returned from delivery: $ingredient_name - Quantity: $return_quantity - Reasons: $reasons";
                            $movement_stmt->execute([
                                $ingredient_id,
                                $request_data['branch_id'],
                                $return_quantity,
                                $movement_reason,
                                $requestId,
                                $updatedBy ?: null
                            ]);
                        }
                    }
                }
            }
            
            $action_message = "Updated delivery status and processed received items";
            if (!empty($processed_ingredients)) {
                $action_message .= ": " . implode(", ", $processed_ingredients);
            }
            
            // Check if logActivity function exists before calling it
            if (function_exists('logActivity')) {
                logActivity($pdo, $updatedBy, $action_message, "Request ID: $requestId, Delivery Status: $deliveryStatus, Branch: $branch_name", $request_data['branch_id']);
            }
        }
        
        $message = 'Delivery status updated successfully. Only checked items were received and added to branch stock.';
        
        echo json_encode([
            'success' => true,
            'message' => $message
        ]);
    } else {
        throw new Exception('Failed to update delivery status');
    }

} catch (Exception $e) {
    http_response_code(400);
    echo json_encode([
        'success' => false,
        'message' => $e->getMessage(),
        'debug' => [
            'request_id' => $requestId ?? 'not set',
            'delivery_status' => $deliveryStatus ?? 'not set',
            'branch_id' => $branchId ?? 'not set',
            'user_id' => $updatedBy ?? 'not set'
        ]
    ]);
}
?> 