<?php
require_once 'db_connect.php';
require_once 'auth_function.php';
checkAdminLogin();

header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    try {
        $admin_id = $_SESSION['user_id'] ?? null;
        $restore = isset($_POST['restore']) ? intval($_POST['restore']) : 0;
        $id = isset($_POST['id']) ? intval($_POST['id']) : 0;

        if (!$id) {
            echo json_encode(['success' => false, 'message' => 'Ingredient ID is required.']);
            exit;
        }

        if ($restore) {
            // Restore logic
            $stmt = $pdo->prepare("SELECT * FROM archive_ingredient WHERE archive_id = ?");
            $stmt->execute([$id]);
            $archived = $stmt->fetch(PDO::FETCH_ASSOC);
            if ($archived) {
                // Restore: set ingredient_status to 'Available'
                $update = $pdo->prepare("UPDATE ingredients SET ingredient_status = 'Available' WHERE ingredient_id = ?");
                $update->execute([$archived['original_id']]);
                // Remove from archive
                $pdo->prepare('DELETE FROM archive_ingredient WHERE archive_id = ?')->execute([$id]);
                // Log activity (optional)
                // logActivity($pdo, $admin_id, 'Restored Ingredient', 'Ingredient: ' . $archived['ingredient_name'] . ' (ID: ' . $archived['original_id'] . ')');
                echo json_encode(['success' => true]);
            } else {
                echo json_encode(['success' => false, 'message' => 'Archived ingredient not found.']);
            }
        } else {
            // Archive logic
            $check = $pdo->prepare("SELECT 1 FROM archive_ingredient WHERE original_id = ?");
            $check->execute([$id]);
            if ($check->fetch()) {
                echo json_encode(['success' => false, 'message' => 'Ingredient is already archived.']);
                exit;
            }
            $stmt = $pdo->prepare("SELECT * FROM ingredients WHERE ingredient_id = ?");
            $stmt->execute([$id]);
            $ingredient = $stmt->fetch(PDO::FETCH_ASSOC);
            if ($ingredient) {
                $archiveStmt = $pdo->prepare("INSERT INTO archive_ingredient (original_id, category_id, ingredient_name, quantity, unit, status, archived_by, notes) VALUES (?, ?, ?, ?, ?, 'archived', ?, ?)");
                $archiveStmt->execute([
                    $ingredient['ingredient_id'],
                    $ingredient['category_id'],
                    $ingredient['ingredient_name'],
                    $ingredient['ingredient_quantity'],
                    $ingredient['ingredient_unit'],
                    $admin_id,
                    isset($ingredient['notes']) ? $ingredient['notes'] : null
                ]);
                $pdo->prepare('UPDATE ingredients SET ingredient_status = ? WHERE ingredient_id = ?')->execute(['archived', $id]);
                echo json_encode(['success' => true]);
            } else {
                echo json_encode(['success' => false, 'message' => 'Ingredient not found.']);
            }
        }
    } catch (PDOException $e) {
        echo json_encode(['success' => false, 'message' => $e->getMessage()]);
    }
} else {
    echo json_encode(['success' => false, 'message' => 'Invalid request.']);
} 