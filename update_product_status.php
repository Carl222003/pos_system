<?php
require_once 'db_connect.php';
require_once 'auth_function.php';

// Check if user is logged in and is a stockman
if (!isset($_SESSION['user_logged_in']) || $_SESSION['user_logged_in'] !== true || $_SESSION['user_type'] !== 'Stockman') {
    header('Content-Type: application/json');
    echo json_encode(['success' => false, 'message' => 'Unauthorized access. Only Stockman can update product status.']);
    exit();
}

header('Content-Type: application/json');

try {
    // Check if required parameters are provided
    if (!isset($_POST['product_id']) || !isset($_POST['status'])) {
        echo json_encode(['success' => false, 'message' => 'Missing required parameters']);
        exit();
    }

    $product_id = intval($_POST['product_id']);
    $new_status = $_POST['status'];

    // Validate status value
    if (!in_array($new_status, ['Available', 'Unavailable'])) {
        echo json_encode(['success' => false, 'message' => 'Invalid status value. Must be Available or Unavailable']);
        exit();
    }

    // Debug logging
    error_log("Product Status Update: Product ID = $product_id, New Status = $new_status");

    // Check what columns exist in the pos_product table
    $stmt = $pdo->query("DESCRIBE pos_product");
    $columns = $stmt->fetchAll(PDO::FETCH_COLUMN);
    
    // Determine the correct status column name
    $statusColumn = 'product_status';
    if (in_array('status', $columns) && !in_array('product_status', $columns)) {
        $statusColumn = 'status';
    }

    // Check if product exists
    $checkQuery = "SELECT product_id, product_name, $statusColumn FROM pos_product WHERE product_id = ?";
    $stmt = $pdo->prepare($checkQuery);
    $stmt->execute([$product_id]);
    $product = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$product) {
        echo json_encode(['success' => false, 'message' => 'Product not found']);
        exit();
    }

    // Check if the status is actually changing
    if ($product[$statusColumn] === $new_status) {
        echo json_encode([
            'success' => true, 
            'message' => 'Product status is already ' . strtolower($new_status),
            'no_change' => true
        ]);
        exit();
    }

    // Update the product status
    $updateQuery = "UPDATE pos_product SET $statusColumn = ? WHERE product_id = ?";
    $stmt = $pdo->prepare($updateQuery);
    $success = $stmt->execute([$new_status, $product_id]);

    if ($success) {
        // Log the status change activity
        $user_id = $_SESSION['user_id'];
        $activity_description = "Updated product '{$product['product_name']}' status from '{$product[$statusColumn]}' to '$new_status'";
        
        try {
            // Try to log to activity table if it exists
            $logQuery = "INSERT INTO pos_activity_log (user_id, activity_type, activity_description, created_at) VALUES (?, ?, ?, NOW())";
            $stmt = $pdo->prepare($logQuery);
            $stmt->execute([$user_id, 'product_status_update', $activity_description]);
        } catch (PDOException $e) {
            // Activity logging failed, but product update was successful
            // Continue without logging
        }

        echo json_encode([
            'success' => true,
            'message' => "Product status successfully updated to $new_status",
            'product_id' => $product_id,
            'new_status' => $new_status,
            'product_name' => $product['product_name']
        ]);
    } else {
        echo json_encode(['success' => false, 'message' => 'Failed to update product status']);
    }

} catch (PDOException $e) {
    error_log("Database error in update_product_status.php: " . $e->getMessage());
    echo json_encode([
        'success' => false,
        'message' => 'Database error occurred: ' . $e->getMessage()
    ]);
} catch (Exception $e) {
    error_log("General error in update_product_status.php: " . $e->getMessage());
    echo json_encode([
        'success' => false,
        'message' => 'An error occurred while updating product status: ' . $e->getMessage()
    ]);
}
?>
