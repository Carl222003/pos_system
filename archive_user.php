<?php
// Suppress any output that might interfere with JSON response
error_reporting(0);
ini_set('display_errors', 0);

// Set proper headers for JSON response
header('Content-Type: application/json');

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
            // Check if the original user still exists
            $checkOriginal = $pdo->prepare("SELECT user_id FROM pos_user WHERE user_id = ?");
            $checkOriginal->execute([$archived['original_id']]);
            
            if ($checkOriginal->fetch()) {
                // Original user exists, just update its status
                $update = $pdo->prepare("UPDATE pos_user SET user_status = 'Active' WHERE user_id = ?");
                $update->execute([$archived['original_id']]);
            } else {
                // Original user doesn't exist, recreate it
                $recreate = $pdo->prepare("INSERT INTO pos_user (user_id, user_name, user_email, user_type, contact_number, profile_image, user_status, branch_id, employee_id, shift_schedule, date_hired, emergency_contact, emergency_number, address, notes) VALUES (?, ?, ?, ?, ?, ?, 'Active', ?, ?, ?, ?, ?, ?, ?, ?)");
                $recreate->execute([
                    $archived['original_id'],
                    $archived['user_name'],
                    $archived['user_email'],
                    $archived['user_type'],
                    $archived['contact_number'],
                    $archived['profile_image'],
                    $archived['branch_id'],
                    $archived['employee_id'],
                    $archived['shift_schedule'],
                    $archived['date_hired'],
                    $archived['emergency_contact'],
                    $archived['emergency_number'],
                    $archived['address'],
                    $archived['notes']
                ]);
            }
            
            // Remove from archive
            $pdo->prepare('DELETE FROM archive_user WHERE archive_id = ?')->execute([$user_id]);
            // Log activity
            if ($admin_id) {
                try {
                    logActivity($pdo, $admin_id, 'Restored User', 'User: ' . $archived['user_name'] . ' (ID: ' . $archived['original_id'] . ')');
                } catch (Exception $e) {
                    // Log activity failed, but don't fail the restore operation
                }
            }
            echo json_encode(['success' => true]);
        } else {
            echo json_encode(['success' => false, 'message' => 'Archived user not found.']);
        }
    } else {
        // Archive logic
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
            // Set user_status to Inactive (archived users will be excluded from main list via AJAX query)
            $pdo->prepare('UPDATE pos_user SET user_status = ? WHERE user_id = ?')->execute(['Inactive', $user_id]);
            // Log activity
            if ($admin_id) {
                try {
                    logActivity($pdo, $admin_id, 'Archived User', 'User: ' . $user['user_name'] . ' (ID: ' . $user['user_id'] . ')');
                } catch (Exception $e) {
                    // Log activity failed, but don't fail the archive operation
                }
            }
            echo json_encode(['success' => true]);
        } else {
            echo json_encode(['success' => false, 'message' => 'User not found.']);
        }
    }
} else {
    echo json_encode(['success' => false, 'message' => 'Invalid request.']);
} 