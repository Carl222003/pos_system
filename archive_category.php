<?php
require_once 'db_connect.php';
require_once 'auth_function.php';
checkAdminLogin();

header('Content-Type: application/json');

function debug_log($msg) {
    file_put_contents(__DIR__ . '/archive_category_debug.log', date('Y-m-d H:i:s') . ' ' . $msg . "\n", FILE_APPEND);
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    try {
        $admin_id = $_SESSION['user_id'] ?? null;
        $restore = isset($_POST['restore']) ? intval($_POST['restore']) : 0;
        $id = isset($_POST['id']) ? intval($_POST['id']) : (isset($_POST['category_id']) ? intval($_POST['category_id']) : 0);
        debug_log('POST received. id=' . $id . ', restore=' . $restore);

        if (!$id) {
            debug_log('No category ID provided.');
            echo json_encode(['success' => false, 'message' => 'Category ID is required.']);
            exit;
        }

        if ($restore) {
            debug_log('Restore requested for archive_id=' . $id);
            $stmt = $pdo->prepare("SELECT * FROM archive_category WHERE archive_id = ?");
            $stmt->execute([$id]);
            $archived = $stmt->fetch(PDO::FETCH_ASSOC);
            if ($archived) {
                $update = $pdo->prepare("UPDATE pos_category SET status = 'active' WHERE category_id = ?");
                $update->execute([$archived['original_id']]);
                debug_log('Restored pos_category.status to active for category_id=' . $archived['original_id']);
                $pdo->prepare('DELETE FROM archive_category WHERE archive_id = ?')->execute([$id]);
                debug_log('Deleted from archive_category where archive_id=' . $id);
                if ($admin_id) {
                    logActivity($pdo, $admin_id, 'Restored Category', 'Category: ' . $archived['category_name'] . ' (ID: ' . $archived['original_id'] . ')');
                }
                echo json_encode(['success' => true, 'step' => 'restore', 'archived' => $archived]);
            } else {
                debug_log('Archived category not found for restore.');
                echo json_encode(['success' => false, 'message' => 'Archived category not found.']);
            }
        } else {
            debug_log('Archive requested for category_id=' . $id);
            $check = $pdo->prepare("SELECT 1 FROM archive_category WHERE original_id = ?");
            $check->execute([$id]);
            if ($check->fetch()) {
                debug_log('Category is already archived.');
                echo json_encode(['success' => false, 'message' => 'Category is already archived.']);
                exit;
            }
            $stmt = $pdo->prepare("SELECT * FROM pos_category WHERE category_id = ?");
            $stmt->execute([$id]);
            $category = $stmt->fetch(PDO::FETCH_ASSOC);
            if ($category) {
                $desc = $category['description'] ?? '';
                $archiveStmt = $pdo->prepare("INSERT INTO archive_category (original_id, category_name, description, status, archived_by) VALUES (?, ?, ?, ?, ?)");
                $archiveStmt->execute([
                    $category['category_id'],
                    $category['category_name'],
                    $desc,
                    'archived',
                    $admin_id
                ]);
                debug_log('Inserted into archive_category: ' . json_encode([$category['category_id'], $category['category_name'], $desc, 'archived', $admin_id]));
                $pdo->prepare('UPDATE pos_category SET status = ? WHERE category_id = ?')->execute(['archived', $id]);
                debug_log('Updated pos_category.status to archived for category_id=' . $id);
                if ($admin_id) {
                    logActivity($pdo, $admin_id, 'Archived Category', 'Category: ' . $category['category_name'] . ' (ID: ' . $category['category_id'] . ')');
                }
                echo json_encode(['success' => true, 'step' => 'archive', 'category' => $category]);
            } else {
                debug_log('Category not found for archiving.');
                echo json_encode(['success' => false, 'message' => 'Category not found.']);
            }
        }
    } catch (PDOException $e) {
        debug_log('DB error: ' . $e->getMessage());
        error_log('Archive category error: ' . $e->getMessage());
        echo json_encode(['success' => false, 'message' => 'DB error: ' . $e->getMessage()]);
    }
} else {
    debug_log('Invalid request method.');
    echo json_encode(['success' => false, 'message' => 'Invalid request.']);
} 