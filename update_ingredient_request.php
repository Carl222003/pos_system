<?php
require_once 'db_connect.php';
require_once 'auth_function.php';

checkAdminLogin();

header('Content-Type: application/json');

try {
    if (!isset($_POST['request_id']) || !isset($_POST['status'])) {
        throw new Exception('Missing required parameters');
    }

    $requestId = $_POST['request_id'];
    $status = $_POST['status'];
    $notes = isset($_POST['notes']) ? $_POST['notes'] : '';
    $updatedBy = $_SESSION['user_id'];
    $updateDate = date('Y-m-d H:i:s');

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

    if ($stmt->execute()) {
        // If status is approved, update ingredient quantities
        if ($status === 'approved') {
            $request_info = $pdo->prepare("SELECT branch_id, ingredients FROM ingredient_requests WHERE request_id = ?");
            $request_info->execute([$requestId]);
            $request_data = $request_info->fetch(PDO::FETCH_ASSOC);
            
            if ($request_data) {
                $ingredients_json = json_decode($request_data['ingredients'], true);
                
                if ($ingredients_json && is_array($ingredients_json)) {
                    foreach ($ingredients_json as $ingredient) {
                        if (isset($ingredient['ingredient_id']) && isset($ingredient['quantity'])) {
                            // Update ingredient quantity
                            $update_ingredient = $pdo->prepare("UPDATE ingredients SET ingredient_quantity = ingredient_quantity + ? WHERE ingredient_id = ?");
                            $update_ingredient->execute([$ingredient['quantity'], $ingredient['ingredient_id']]);
                        }
                    }
                }
            }
        }
        
        // Log the activity
        $request_info = $pdo->prepare("SELECT branch_id, ingredients FROM ingredient_requests WHERE request_id = ?");
        $request_info->execute([$requestId]);
        $request_data = $request_info->fetch(PDO::FETCH_ASSOC);
        
        if ($request_data) {
            $branch_name = $pdo->query("SELECT branch_name FROM pos_branch WHERE branch_id = " . $request_data['branch_id'])->fetchColumn();
            $action_message = $status === 'approved' ? "Approved and updated ingredient quantities" : "Updated ingredient request status";
            logActivity($pdo, $updatedBy, $action_message, "Request ID: $requestId, Status: $status, Branch: $branch_name");
        }
        
        $message = $status === 'approved' ? 'Request approved and ingredient quantities updated' : 'Status updated successfully';
        echo json_encode(array('success' => true, 'message' => $message));
    } else {
        throw new Exception('Failed to update request status');
    }

} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(array(
        'success' => false,
        'message' => $e->getMessage()
    ));
} 