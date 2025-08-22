<?php
require_once 'db_connect.php';
require_once 'auth_function.php';
checkAdminLogin();

header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    try {
        $admin_id = $_SESSION['user_id'] ?? null;
        $restore = isset($_POST['restore']) ? intval($_POST['restore']) : 0;
        $id = isset($_POST['id']) ? intval($_POST['id']) : (isset($_POST['product_id']) ? intval($_POST['product_id']) : 0);

        if (!$id) {
            echo json_encode(['success' => false, 'message' => 'Product ID is required.']);
            exit;
        }

        if ($restore) {
            // Restore logic
            $stmt = $pdo->prepare("SELECT * FROM archive_product WHERE archive_id = ?");
            $stmt->execute([$id]);
            $archived = $stmt->fetch(PDO::FETCH_ASSOC);
            if ($archived) {
                // Restore: set product_status to 'Available'
                $update = $pdo->prepare("UPDATE pos_product SET product_status = 'Available' WHERE product_id = ?");
                $update->execute([$archived['original_id']]);
                // Remove from archive
                $pdo->prepare('DELETE FROM archive_product WHERE archive_id = ?')->execute([$id]);
                // Log activity
                if ($admin_id) {
                    logActivity($pdo, $admin_id, 'Restored Product', 'Product: ' . $archived['product_name'] . ' (ID: ' . $archived['original_id'] . ')');
                }
                echo json_encode(['success' => true]);
            } else {
                echo json_encode(['success' => false, 'message' => 'Archived product not found.']);
            }
        } else {
            // Archive logic
            // Check if already archived
            $check = $pdo->prepare("SELECT 1 FROM archive_product WHERE original_id = ?");
            $check->execute([$id]);
            if ($check->fetch()) {
                echo json_encode(['success' => false, 'message' => 'Product is already archived.']);
                exit;
            }
            $stmt = $pdo->prepare("SELECT * FROM pos_product WHERE product_id = ?");
            $stmt->execute([$id]);
            $product = $stmt->fetch(PDO::FETCH_ASSOC);
            if ($product) {
                $archiveStmt = $pdo->prepare("INSERT INTO archive_product (original_id, category_id, product_name, product_price, description, ingredients, product_image, product_status, archived_by) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)");
                $archiveStmt->execute([
                    $product['product_id'],
                    $product['category_id'],
                    $product['product_name'],
                    $product['product_price'],
                    $product['description'],
                    $product['ingredients'],
                    $product['product_image'],
                    'archived',
                    $admin_id
                ]);
                // Set product_status to archived
                $pdo->prepare('UPDATE pos_product SET product_status = ? WHERE product_id = ?')->execute(['archived', $id]);
                // Log activity
                if ($admin_id) {
                    logActivity($pdo, $admin_id, 'Archived Product', 'Product: ' . $product['product_name'] . ' (ID: ' . $product['product_id'] . ')');
                }
                echo json_encode(['success' => true]);
            } else {
                echo json_encode(['success' => false, 'message' => 'Product not found.']);
            }
        }
    } catch (PDOException $e) {
        echo json_encode(['success' => false, 'message' => $e->getMessage()]);
    }
} else {
    echo json_encode(['success' => false, 'message' => 'Invalid request.']);
} 