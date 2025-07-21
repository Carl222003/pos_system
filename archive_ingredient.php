<?php
require_once 'db_connect.php';
require_once 'auth_function.php';
checkAdminLogin();

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['id'])) {
    $ingredient_id = intval($_POST['id']);
    $admin_id = $_SESSION['user_id'] ?? null;
    $restore = isset($_POST['restore']) ? intval($_POST['restore']) : 0;

    if ($restore) {
        // Restore logic
        $stmt = $pdo->prepare("SELECT * FROM archive_ingredient WHERE archive_id = ?");
        $stmt->execute([$ingredient_id]);
        $archived = $stmt->fetch(PDO::FETCH_ASSOC);
        if ($archived) {
            // Restore: set ingredient_status to 'Available'
            $update = $pdo->prepare("UPDATE ingredients SET ingredient_status = 'Available' WHERE ingredient_id = ?");
            $update->execute([$archived['original_id']]);
            // Remove from archive
            $pdo->prepare('DELETE FROM archive_ingredient WHERE archive_id = ?')->execute([$ingredient_id]);
            // Log activity
            logActivity($pdo, $admin_id, 'Restored Ingredient', 'Ingredient: ' . $archived['ingredient_name'] . ' (ID: ' . $archived['original_id'] . ')');
            echo json_encode(['success' => true]);
        } else {
            echo json_encode(['success' => false, 'message' => 'Archived ingredient not found.']);
        }
    } else {
        // Archive logic
        // Check if already archived
        $check = $pdo->prepare("SELECT 1 FROM archive_ingredient WHERE original_id = ?");
        $check->execute([$ingredient_id]);
        if ($check->fetch()) {
            echo json_encode(['success' => false, 'message' => 'Ingredient is already archived.']);
            exit;
        }
        $stmt = $pdo->prepare("SELECT * FROM ingredients WHERE ingredient_id = ?");
        $stmt->execute([$ingredient_id]);
        $ingredient = $stmt->fetch(PDO::FETCH_ASSOC);
        if ($ingredient) {
            $archiveStmt = $pdo->prepare("INSERT INTO archive_ingredient (original_id, category_id, ingredient_name, ingredient_quantity, ingredient_unit, ingredient_status, archived_by) VALUES (?, ?, ?, ?, ?, ?, ?)");
            $archiveStmt->execute([
                $ingredient['ingredient_id'],
                $ingredient['category_id'],
                $ingredient['ingredient_name'],
                $ingredient['ingredient_quantity'],
                $ingredient['ingredient_unit'],
                'archived',
                $admin_id
            ]);
            // Set ingredient_status to archived
            $pdo->prepare('UPDATE ingredients SET ingredient_status = ? WHERE ingredient_id = ?')->execute(['archived', $ingredient_id]);
            // Log activity
            logActivity($pdo, $admin_id, 'Archived Ingredient', 'Ingredient: ' . $ingredient['ingredient_name'] . ' (ID: ' . $ingredient['ingredient_id'] . ')');
            echo json_encode(['success' => true]);
        } else {
            echo json_encode(['success' => false, 'message' => 'Ingredient not found.']);
        }
    }
} else {
    echo json_encode(['success' => false, 'message' => 'Invalid request.']);
} 