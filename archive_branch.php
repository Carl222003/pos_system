<?php
require_once 'db_connect.php';
require_once 'auth_function.php';
checkAdminLogin();

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['id'])) {
    $branch_id = intval($_POST['id']);
    $admin_id = $_SESSION['user_id'] ?? null;
    $restore = isset($_POST['restore']) ? intval($_POST['restore']) : 0;

    if ($restore) {
        // Restore logic
        $stmt = $pdo->prepare("SELECT * FROM archive_branch WHERE archive_id = ?");
        $stmt->execute([$branch_id]);
        $archived = $stmt->fetch(PDO::FETCH_ASSOC);
        if ($archived) {
            // Restore: set status to 'Active'
            $update = $pdo->prepare("UPDATE pos_branch SET status = 'Active' WHERE branch_id = ?");
            $update->execute([$archived['original_id']]);
            // Remove from archive
            $pdo->prepare('DELETE FROM archive_branch WHERE archive_id = ?')->execute([$branch_id]);
            // Log activity
            if ($admin_id) {
                logActivity($pdo, $admin_id, 'Restored Branch', 'Branch: ' . $archived['branch_name'] . ' (ID: ' . $archived['original_id'] . ')');
            }
            echo json_encode(['success' => true]);
        } else {
            echo json_encode(['success' => false, 'message' => 'Archived branch not found.']);
        }
    } else {
        // Archive logic
        // Check if already archived
        $check = $pdo->prepare("SELECT 1 FROM archive_branch WHERE original_id = ?");
        $check->execute([$branch_id]);
        if ($check->fetch()) {
            echo json_encode(['success' => false, 'message' => 'Branch is already archived.']);
            exit;
        }
        $stmt = $pdo->prepare("SELECT * FROM pos_branch WHERE branch_id = ?");
        $stmt->execute([$branch_id]);
        $branch = $stmt->fetch(PDO::FETCH_ASSOC);
        if ($branch) {
            $archiveStmt = $pdo->prepare("INSERT INTO archive_branch (original_id, branch_name, branch_code, contact_number, email, street_address, barangay, city, province, complete_address, manager_name, opening_date, operating_hours, seating_capacity, notes, status, archived_by) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");
            $archiveStmt->execute([
                $branch['branch_id'],
                $branch['branch_name'],
                $branch['branch_code'],
                $branch['contact_number'],
                $branch['email'],
                $branch['street_address'],
                $branch['barangay'],
                $branch['city'],
                $branch['province'],
                $branch['complete_address'],
                $branch['manager_name'],
                $branch['opening_date'],
                $branch['operating_hours'],
                $branch['seating_capacity'],
                $branch['notes'],
                'archived',
                $admin_id
            ]);
            // Set status to archived
            $pdo->prepare('UPDATE pos_branch SET status = ? WHERE branch_id = ?')->execute(['archived', $branch_id]);
            // Log activity
            if ($admin_id) {
                logActivity($pdo, $admin_id, 'Archived Branch', 'Branch: ' . $branch['branch_name'] . ' (ID: ' . $branch['branch_id'] . ')');
            }
            echo json_encode(['success' => true]);
        } else {
            echo json_encode(['success' => false, 'message' => 'Branch not found.']);
        }
    }
} else {
    echo json_encode(['success' => false, 'message' => 'Invalid request.']);
} 