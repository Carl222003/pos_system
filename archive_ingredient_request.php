<?php
require_once 'db_connect.php';
require_once 'auth_function.php';

checkAdminLogin();

header('Content-Type: application/json');

try {
    if (!isset($_POST['request_id'])) {
        throw new Exception('Missing request ID');
    }

    $requestId = $_POST['request_id'];
    $adminId = $_SESSION['user_id'];

    // Create archive table if it doesn't exist
    $pdo->exec("
        CREATE TABLE IF NOT EXISTS archive_ingredient_requests (
            archive_id INT PRIMARY KEY AUTO_INCREMENT,
            original_id INT NOT NULL,
            branch_id INT,
            request_date DATETIME,
            ingredients TEXT,
            status VARCHAR(50),
            delivery_status ENUM('pending', 'on_delivery', 'delivered', 'returned', 'cancelled') DEFAULT 'pending',
            delivery_date TIMESTAMP NULL,
            delivery_notes TEXT,
            notes TEXT,
            updated_by INT,
            updated_at TIMESTAMP NULL,
            archived_by INT,
            archived_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            INDEX idx_original_id (original_id),
            INDEX idx_branch_id (branch_id),
            INDEX idx_archived_at (archived_at)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4
    ");

    // Get the request details before archiving
    $stmt = $pdo->prepare("SELECT * FROM ingredient_requests WHERE request_id = ?");
    $stmt->execute([$requestId]);
    $request = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$request) {
        throw new Exception('Request not found');
    }

    // Start transaction
    $pdo->beginTransaction();

    // Insert into archive table
    $archiveStmt = $pdo->prepare("
        INSERT INTO archive_ingredient_requests (
            original_id, branch_id, request_date, ingredients, status, 
            delivery_status, delivery_date, delivery_notes, notes, 
            updated_by, updated_at, archived_by, archived_at
        ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, NOW())
    ");

    $archiveStmt->execute([
        $request['request_id'],
        $request['branch_id'],
        $request['request_date'],
        $request['ingredients'],
        $request['status'],
        $request['delivery_status'] ?? 'pending',
        $request['delivery_date'],
        $request['delivery_notes'],
        $request['notes'],
        $request['updated_by'],
        $request['updated_at'],
        $adminId
    ]);

    // Delete from original table
    $deleteStmt = $pdo->prepare("DELETE FROM ingredient_requests WHERE request_id = ?");
    $deleteStmt->execute([$requestId]);

    // Commit transaction
    $pdo->commit();

    // Log the activity
    $branch_name = $pdo->query("SELECT branch_name FROM pos_branch WHERE branch_id = " . $request['branch_id'])->fetchColumn();
    if (function_exists('logActivity')) {
        logActivity($pdo, $adminId, "Archived ingredient request", "Request ID: $requestId, Branch: $branch_name");
    }

    echo json_encode([
        'success' => true,
        'message' => 'Request archived successfully'
    ]);

} catch (Exception $e) {
    // Rollback transaction on error
    if ($pdo->inTransaction()) {
        $pdo->rollBack();
    }
    
    http_response_code(400);
    echo json_encode([
        'success' => false,
        'message' => $e->getMessage()
    ]);
}
?> 