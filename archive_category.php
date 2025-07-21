<?php
require_once 'db_connect.php';
require_once 'auth_function.php';
checkAdminLogin();

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['id'])) {
    $category_id = intval($_POST['id']);
    $admin_id = $_SESSION['user_id'] ?? null;
    $restore = isset($_POST['restore']) ? intval($_POST['restore']) : 0;

    if ($restore) {
        // Restore logic
        $stmt = $pdo->prepare("SELECT * FROM archive_category WHERE archive_id = ?");
        $stmt->execute([$category_id]);
        $archived = $stmt->fetch(PDO::FETCH_ASSOC);
        if ($archived) {
            // Restore: set status to 'active'
            $update = $pdo->prepare("UPDATE pos_category SET status = 'active' WHERE category_id = ?");
            $update->execute([$archived['original_id']]);
            // Remove from archive
            $pdo->prepare('DELETE FROM archive_category WHERE archive_id = ?')->execute([$category_id]);
            // Log activity
            logActivity($pdo, $admin_id, 'Restored Category', 'Category: ' . $archived['category_name'] . ' (ID: ' . $archived['original_id'] . ')');
            echo json_encode(['success' => true]);
        } else {
            echo json_encode(['success' => false, 'message' => 'Archived category not found.']);
        }
    } else {
        // Archive logic
        // Check if already archived
        $check = $pdo->prepare("SELECT 1 FROM archive_category WHERE original_id = ?");
        $check->execute([$category_id]);
        if ($check->fetch()) {
            echo json_encode(['success' => false, 'message' => 'Category is already archived.']);
            exit;
        }
        $stmt = $pdo->prepare("SELECT * FROM pos_category WHERE category_id = ?");
        $stmt->execute([$category_id]);
        $category = $stmt->fetch(PDO::FETCH_ASSOC);
        if ($category) {
            $archiveStmt = $pdo->prepare("INSERT INTO archive_category (original_id, category_name, description, status, archived_by) VALUES (?, ?, ?, ?, ?)");
            $archiveStmt->execute([
                $category['category_id'],
                $category['category_name'],
                $category['description'],
                'archived',
                $admin_id
            ]);
            // Set status to archived
            $pdo->prepare('UPDATE pos_category SET status = ? WHERE category_id = ?')->execute(['archived', $category_id]);
            // Log activity
            logActivity($pdo, $admin_id, 'Archived Category', 'Category: ' . $category['category_name'] . ' (ID: ' . $category['category_id'] . ')');
            echo json_encode(['success' => true]);
        } else {
            echo json_encode(['success' => false, 'message' => 'Category not found.']);
        }
    }
} else {
    echo json_encode(['success' => false, 'message' => 'Invalid request.']);
} 