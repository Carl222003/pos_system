<?php
require_once 'db_connect.php';
require_once 'auth_function.php';

checkAdminLogin();

header('Content-Type: application/json');

try {
    if (!isset($_POST['archive_id'])) {
        throw new Exception('Missing archive ID');
    }

    $archiveId = $_POST['archive_id'];
    $adminId = $_SESSION['user_id'];

    // Get the archived request details
    $stmt = $pdo->prepare("SELECT * FROM archive_ingredient_requests WHERE archive_id = ?");
    $stmt->execute([$archiveId]);
    $archivedRequest = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$archivedRequest) {
        throw new Exception('Archived request not found');
    }

    // Start transaction
    $pdo->beginTransaction();

    // Insert back into original table
    $restoreStmt = $pdo->prepare("
        INSERT INTO ingredient_requests (
            request_id, branch_id, request_date, ingredients, status, 
            delivery_status, delivery_date, delivery_notes, notes, 
            updated_by, updated_at
        ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)
    ");

    $restoreStmt->execute([
        $archivedRequest['original_id'],
        $archivedRequest['branch_id'],
        $archivedRequest['request_date'],
        $archivedRequest['ingredients'],
        $archivedRequest['status'],
        $archivedRequest['delivery_status'] ?? 'pending',
        $archivedRequest['delivery_date'],
        $archivedRequest['delivery_notes'],
        $archivedRequest['notes'],
        $archivedRequest['updated_by'],
        $archivedRequest['updated_at']
    ]);

    // Delete from archive table
    $deleteStmt = $pdo->prepare("DELETE FROM archive_ingredient_requests WHERE archive_id = ?");
    $deleteStmt->execute([$archiveId]);

    // Commit transaction
    $pdo->commit();

    // Log the activity
    $branch_name = $pdo->query("SELECT branch_name FROM pos_branch WHERE branch_id = " . $archivedRequest['branch_id'])->fetchColumn();
    if (function_exists('logActivity')) {
        logActivity($pdo, $adminId, "Restored ingredient request", "Request ID: " . $archivedRequest['original_id'] . ", Branch: $branch_name");
    }

    echo json_encode([
        'success' => true,
        'message' => 'Request stock restored successfully'
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