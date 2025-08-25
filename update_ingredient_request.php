<?php
// Suppress warnings to prevent them from breaking JSON output
error_reporting(E_ERROR | E_PARSE);
ini_set('display_errors', 0);

require_once 'db_connect.php';
require_once 'auth_function.php';

checkAdminLogin();

header('Content-Type: application/json');

// Function to ensure stock_movements table exists
function ensureStockMovementsTable($pdo) {
    try {
        // Check if table exists
        $tableExists = $pdo->query("SHOW TABLES LIKE 'stock_movements'")->rowCount() > 0;
        
        if (!$tableExists) {
            // Create the table
            $createTable = "CREATE TABLE IF NOT EXISTS stock_movements (
                movement_id INT PRIMARY KEY AUTO_INCREMENT,
                ingredient_id INT NOT NULL,
                branch_id INT NOT NULL,
                movement_type ENUM('addition', 'deduction', 'adjustment', 'return') NOT NULL,
                quantity DECIMAL(10,2) NOT NULL,
                reason TEXT,
                reference_id INT,
                reference_type VARCHAR(50),
                movement_date TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                performed_by INT,
                FOREIGN KEY (ingredient_id) REFERENCES ingredients(ingredient_id) ON DELETE CASCADE,
                FOREIGN KEY (branch_id) REFERENCES pos_branch(branch_id) ON DELETE CASCADE,
                FOREIGN KEY (performed_by) REFERENCES pos_user(user_id) ON DELETE SET NULL
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4";
            
            $pdo->exec($createTable);
        }
        
        return true;
    } catch (Exception $e) {
        error_log("Error creating stock_movements table: " . $e->getMessage());
        return false;
    }
}

try {
    if (!isset($_POST['request_id']) || !isset($_POST['status'])) {
        throw new Exception('Missing required parameters');
    }

    $requestId = $_POST['request_id'];
    $status = $_POST['status'];
    $notes = isset($_POST['notes']) ? $_POST['notes'] : '';
    $updatedBy = $_SESSION['user_id'];
    $updateDate = date('Y-m-d H:i:s');

    // Ensure stock_movements table exists
    ensureStockMovementsTable($pdo);

    // Get request details first
    $request_info = $pdo->prepare("SELECT branch_id, ingredients FROM ingredient_requests WHERE request_id = ?");
    $request_info->execute([$requestId]);
    $request_data = $request_info->fetch(PDO::FETCH_ASSOC);
    
    if (!$request_data) {
        throw new Exception('Request not found');
    }

    // If status is approved, we'll allow approval regardless of current stock availability
    // The admin can approve requests and add ingredients to the branch later
    if ($status === 'approved') {
        $ingredients_json = json_decode($request_data['ingredients'], true);
        
        if ($ingredients_json && is_array($ingredients_json)) {
            // Log which ingredients are being approved (for admin reference)
            foreach ($ingredients_json as $ingredient) {
                if (isset($ingredient['ingredient_id']) && isset($ingredient['quantity'])) {
                    $ingredient_id = $ingredient['ingredient_id'];
                    $requested_quantity = floatval($ingredient['quantity']);
                    
                    // Get ingredient name for logging
                    $ingredient_name_stmt = $pdo->prepare("SELECT ingredient_name FROM ingredients WHERE ingredient_id = ?");
                    $ingredient_name_stmt->execute([$ingredient_id]);
                    $ingredient_name = $ingredient_name_stmt->fetchColumn() ?: "Unknown ingredient";
                    
                    // Check if ingredient exists in branch (for logging purposes only)
                    $stock_check = $pdo->prepare("SELECT i.ingredient_name, bi.quantity as ingredient_quantity, i.ingredient_unit 
                                                  FROM ingredients i 
                                                  INNER JOIN branch_ingredient bi ON i.ingredient_id = bi.ingredient_id 
                                                  WHERE i.ingredient_id = ? AND bi.branch_id = ? AND bi.status = 'active'");
                    $stock_check->execute([$ingredient_id, $request_data['branch_id']]);
                    $current_stock = $stock_check->fetch(PDO::FETCH_ASSOC);
                    
                    if (!$current_stock) {
                        // Log that this ingredient needs to be added to the branch
                        error_log("Approved request for ingredient '$ingredient_name' that is not currently in branch. Admin should add this ingredient to the branch.");
                    }
                }
            }
        }
    }

    // Start transaction for data consistency
    $pdo->beginTransaction();

    try {
        // Update request status
        $query = "UPDATE ingredient_requests 
                  SET status = :status,
                      notes = :notes,
                      updated_by = :updated_by,
                      updated_at = :updated_at
                  WHERE request_id = :request_id";

        $stmt = $pdo->prepare($query);
        $stmt->bindParam(':status', $status);
        $stmt->bindParam(':notes', $notes);
        $stmt->bindParam(':updated_by', $updatedBy);
        $stmt->bindParam(':updated_at', $updateDate);
        $stmt->bindParam(':request_id', $requestId);

        if (!$stmt->execute()) {
            throw new Exception('Failed to update request status');
        }

        // If status is approved, handle ingredient quantities (deduct from main branch, add to requesting branch)
        if ($status === 'approved') {
            $ingredients_json = json_decode($request_data['ingredients'], true);
            
            if ($ingredients_json && is_array($ingredients_json)) {
                foreach ($ingredients_json as $ingredient) {
                    if (isset($ingredient['ingredient_id']) && isset($ingredient['quantity'])) {
                        $ingredient_id = $ingredient['ingredient_id'];
                        $requested_quantity = floatval($ingredient['quantity']);
                        $requesting_branch_id = $request_data['branch_id'];
                        $main_branch_id = 5; // Main branch ID (More Bites Main)
                        
                        // 1. DEDUCT FROM MAIN BRANCH (branch_id = 5)
                        // Check if ingredient exists in main branch
                        $main_branch_check = $pdo->prepare("SELECT quantity FROM branch_ingredient WHERE ingredient_id = ? AND branch_id = ? AND status = 'active'");
                        $main_branch_check->execute([$ingredient_id, $main_branch_id]);
                        $main_branch_quantity = $main_branch_check->fetchColumn();
                        
                        if ($main_branch_quantity !== false) {
                            // Deduct from main branch
                            $new_main_quantity = $main_branch_quantity - $requested_quantity;
                            
                            // Update main branch ingredient quantity
                            $update_main_branch = $pdo->prepare("
                                UPDATE branch_ingredient 
                                SET quantity = ?
                                WHERE ingredient_id = ? AND branch_id = ? AND status = 'active'
                            ");
                            
                            if (!$update_main_branch->execute([$new_main_quantity, $ingredient_id, $main_branch_id])) {
                                throw new Exception('Failed to update main branch ingredient quantity');
                            }
                            
                            // Log deduction from main branch
                            $main_movement_stmt = $pdo->prepare("
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
                                ) VALUES (?, ?, 'deduction', ?, ?, ?, 'ingredient_request', NOW(), ?)
                            ");
                            
                            $main_movement_stmt->execute([
                                $ingredient_id,
                                $main_branch_id,
                                $requested_quantity,
                                "Stockman request approved - transferred to branch $requesting_branch_id - ID: $requestId",
                                $requestId,
                                $updatedBy ?: null
                            ]);
                        } else {
                            // Main branch doesn't have this ingredient - create it with 0 quantity
                            $insert_main_branch = $pdo->prepare("
                                INSERT INTO branch_ingredient (branch_id, ingredient_id, quantity, minimum_stock, status)
                                VALUES (?, ?, 0, 5, 'active')
                            ");
                            
                            if (!$insert_main_branch->execute([$main_branch_id, $ingredient_id])) {
                                throw new Exception('Failed to create ingredient in main branch');
                            }
                            
                            // Log creation in main branch
                            $main_movement_stmt = $pdo->prepare("
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
                                ) VALUES (?, ?, 'addition', ?, ?, ?, 'ingredient_request', NOW(), ?)
                            ");
                            
                            $main_movement_stmt->execute([
                                $ingredient_id,
                                $main_branch_id,
                                0,
                                "Created ingredient in main branch for request - ID: $requestId",
                                $requestId,
                                $updatedBy ?: null
                            ]);
                        }
                        
                        // 2. ADD TO REQUESTING BRANCH
                        // Check if ingredient exists in requesting branch
                        $requesting_branch_check = $pdo->prepare("SELECT quantity FROM branch_ingredient WHERE ingredient_id = ? AND branch_id = ? AND status = 'active'");
                        $requesting_branch_check->execute([$ingredient_id, $requesting_branch_id]);
                        $requesting_branch_quantity = $requesting_branch_check->fetchColumn();
                        
                        if ($requesting_branch_quantity !== false) {
                            // Add to existing ingredient in requesting branch
                            $new_requesting_quantity = $requesting_branch_quantity + $requested_quantity;
                            
                            // Update requesting branch ingredient quantity
                            $update_requesting_branch = $pdo->prepare("
                                UPDATE branch_ingredient 
                                SET quantity = ?
                                WHERE ingredient_id = ? AND branch_id = ? AND status = 'active'
                            ");
                            
                            if (!$update_requesting_branch->execute([$new_requesting_quantity, $ingredient_id, $requesting_branch_id])) {
                                throw new Exception('Failed to update requesting branch ingredient quantity');
                            }
                        } else {
                            // Create ingredient in requesting branch with requested quantity
                            $insert_requesting_branch = $pdo->prepare("
                                INSERT INTO branch_ingredient (branch_id, ingredient_id, quantity, minimum_stock, status)
                                VALUES (?, ?, ?, 5, 'active')
                            ");
                            
                            if (!$insert_requesting_branch->execute([$requesting_branch_id, $ingredient_id, $requested_quantity])) {
                                throw new Exception('Failed to create ingredient in requesting branch');
                            }
                        }
                        
                        // Log addition to requesting branch
                        $requesting_movement_stmt = $pdo->prepare("
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
                            ) VALUES (?, ?, 'addition', ?, ?, ?, 'ingredient_request', NOW(), ?)
                        ");
                        
                        $requesting_movement_stmt->execute([
                            $ingredient_id,
                            $requesting_branch_id,
                            $requested_quantity,
                            "Stockman request approved - received from main branch - ID: $requestId",
                            $requestId,
                            $updatedBy ?: null
                        ]);
                    }
                }
            }
        }
        
        // If status is returned, restore ingredient quantities to inventory
        if ($status === 'returned') {
            $ingredients_json = json_decode($request_data['ingredients'], true);
            
            if ($ingredients_json && is_array($ingredients_json)) {
                foreach ($ingredients_json as $ingredient) {
                    if (isset($ingredient['ingredient_id']) && isset($ingredient['quantity'])) {
                        $ingredient_id = $ingredient['ingredient_id'];
                        $returned_quantity = floatval($ingredient['quantity']);
                        
                        // Get current quantity before restoration from branch_ingredient table
                        $current_qty_stmt = $pdo->prepare("SELECT quantity FROM branch_ingredient WHERE ingredient_id = ? AND branch_id = ? AND status = 'active'");
                        $current_qty_stmt->execute([$ingredient_id, $request_data['branch_id']]);
                        $current_quantity = $current_qty_stmt->fetchColumn();
                        $new_quantity = $current_quantity + $returned_quantity;
                        
                        // Restore ingredient quantity back to inventory in branch_ingredient table
                        $update_ingredient = $pdo->prepare("
                            UPDATE branch_ingredient 
                            SET quantity = ?
                            WHERE ingredient_id = ? AND branch_id = ? AND status = 'active'
                        ");
                        
                        if (!$update_ingredient->execute([$new_quantity, $ingredient_id, $request_data['branch_id']])) {
                            throw new Exception('Failed to update ingredient quantity');
                        }
                        
                        // Log the stock restoration
                        $stock_movement_stmt = $pdo->prepare("
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
                            ) VALUES (?, ?, 'return', ?, ?, ?, 'ingredient_request', NOW(), ?)
                        ");
                        
                        $stock_movement_stmt->execute([
                            $ingredient_id,
                            $request_data['branch_id'],
                            $returned_quantity,
                            "Stockman request returned - ID: $requestId",
                            $requestId,
                            $updatedBy ?: null
                        ]);
                    }
                }
            }
        }
        
        // Commit transaction
        $pdo->commit();
        
        // Log the activity
        $branch_name = $pdo->query("SELECT branch_name FROM pos_branch WHERE branch_id = " . $request_data['branch_id'])->fetchColumn();
        
        if ($status === 'approved') {
            $action_message = "Approved ingredient request and transferred quantities from main branch";
            $log_details = "Request ID: $requestId, Status: $status, Branch: $branch_name, Ingredients transferred from main branch";
        } elseif ($status === 'returned') {
            $action_message = "Processed ingredient return and restored quantities to inventory";
            $log_details = "Request ID: $requestId, Status: $status, Branch: $branch_name, Ingredients restored to inventory";
        } else {
            $action_message = "Updated ingredient request status";
            $log_details = "Request ID: $requestId, Status: $status, Branch: $branch_name";
        }
        
        if (function_exists('logActivity')) {
            logActivity($pdo, $updatedBy, $action_message, $log_details, $request_data['branch_id']);
        }
        
        // Set appropriate success message based on status
        if ($status === 'approved') {
            $message = 'Request approved and ingredients transferred from main branch successfully';
        } elseif ($status === 'returned') {
            $message = 'Return processed and ingredients restored to inventory successfully';
        } else {
            $message = 'Status updated successfully';
        }
        
        echo json_encode(array('success' => true, 'message' => $message));
        
    } catch (Exception $e) {
        // Rollback transaction on error
        $pdo->rollBack();
        throw $e;
    }

} catch (PDOException $e) {
    error_log("Database error in update_ingredient_request.php: " . $e->getMessage());
    http_response_code(500);
    echo json_encode(array(
        'success' => false,
        'message' => 'Database error: ' . $e->getMessage(),
        'debug_info' => [
            'request_id' => $requestId ?? 'not set',
            'status' => $status ?? 'not set',
            'branch_id' => $request_data['branch_id'] ?? 'not set'
        ]
    ));
} catch (Exception $e) {
    error_log("General error in update_ingredient_request.php: " . $e->getMessage());
    http_response_code(500);
    echo json_encode(array(
        'success' => false,
        'message' => 'An error occurred: ' . $e->getMessage()
    ));
} 