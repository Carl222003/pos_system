<?php
require_once 'db_connect.php';
require_once 'auth_function.php';
checkAdminLogin();

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['id'])) {
    $user_id = intval($_POST['id']);
    $admin_id = $_SESSION['user_id'] ?? null;
    $restore = isset($_POST['restore']) ? intval($_POST['restore']) : 0;

    if ($restore) {
        // Restore logic
        $stmt = $pdo->prepare("SELECT * FROM archive_user WHERE archive_id = ?");
        $stmt->execute([$user_id]);
        $archived = $stmt->fetch(PDO::FETCH_ASSOC);
        if ($archived) {
            // Restore: set user_status to 'Active'
            $update = $pdo->prepare("UPDATE pos_user SET user_status = 'Active' WHERE user_id = ?");
            $update->execute([$archived['original_id']]);
            // Remove from archive
            $pdo->prepare('DELETE FROM archive_user WHERE archive_id = ?')->execute([$user_id]);
            // Log activity
            if ($admin_id) {
                logActivity($pdo, $admin_id, 'Restored User', 'User: ' . $archived['user_name'] . ' (ID: ' . $archived['original_id'] . ')');
            }
            echo json_encode(['success' => true]);
        } else {
            echo json_encode(['success' => false, 'message' => 'Archived user not found.']);
        }
    } else {
        // Archive logic
        // Check if already archived
        $check = $pdo->prepare("SELECT 1 FROM archive_user WHERE original_id = ?");
        $check->execute([$user_id]);
        if ($check->fetch()) {
            echo json_encode(['success' => false, 'message' => 'User is already archived.']);
            exit;
        }
        $stmt = $pdo->prepare("SELECT * FROM pos_user WHERE user_id = ?");
        $stmt->execute([$user_id]);
        $user = $stmt->fetch(PDO::FETCH_ASSOC);
        if ($user) {
            $archiveStmt = $pdo->prepare("INSERT INTO archive_user (original_id, user_name, user_email, user_type, contact_number, profile_image, user_status, branch_id, employee_id, shift_schedule, date_hired, emergency_contact, emergency_number, address, notes, archived_by) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");
            $archiveStmt->execute([
                $user['user_id'],
                $user['user_name'],
                $user['user_email'],
                $user['user_type'],
                $user['contact_number'],
                $user['profile_image'],
                'Inactive',
                $user['branch_id'],
                $user['employee_id'],
                $user['shift_schedule'],
                $user['date_hired'],
                $user['emergency_contact'],
                $user['emergency_number'],
                $user['address'],
                $user['notes'],
                $admin_id
            ]);
            // Set user_status to Inactive
            $pdo->prepare('UPDATE pos_user SET user_status = ? WHERE user_id = ?')->execute(['Inactive', $user_id]);
            // Log activity
            if ($admin_id) {
                logActivity($pdo, $admin_id, 'Archived User', 'User: ' . $user['user_name'] . ' (ID: ' . $user['user_id'] . ')');
            }
            echo json_encode(['success' => true]);
        } else {
            echo json_encode(['success' => false, 'message' => 'User not found.']);
        }
    }
} else {
    echo json_encode(['success' => false, 'message' => 'Invalid request.']);
} 