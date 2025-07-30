<?php
require_once 'db_connect.php';
require_once 'auth_function.php';

checkAdminLogin();

header('Content-Type: application/json');

try {
    // Validate required fields
    $required_fields = [
        'category_id',
        'product_name',
        'product_price',
        'product_status'
    ];

    foreach ($required_fields as $field) {
        if (!isset($_POST[$field]) || empty($_POST[$field])) {
            throw new Exception("$field is required");
        }
    }

    // Validate numeric fields
    if (!is_numeric($_POST['product_price']) || $_POST['product_price'] <= 0) {
        throw new Exception('Invalid product price');
    }

    // Handle image upload if provided
    $product_image = null;
    if (isset($_FILES['product_image']) && $_FILES['product_image']['error'] === 0) {
        $allowed = ['jpg', 'jpeg', 'png', 'gif'];
        $filename = $_FILES['product_image']['name'];
        $ext = strtolower(pathinfo($filename, PATHINFO_EXTENSION));
        
        if (!in_array($ext, $allowed)) {
            throw new Exception("Invalid image format. Allowed formats: " . implode(', ', $allowed));
        }

        $upload_name = 'product_' . time() . '.' . $ext;
        $upload_path = 'uploads/products/' . $upload_name;
        
        if (!file_exists('uploads/products')) {
            mkdir('uploads/products', 0777, true);
        }
        
        if (!move_uploaded_file($_FILES['product_image']['tmp_name'], $upload_path)) {
            throw new Exception("Failed to upload product image");
        }
        
        $product_image = $upload_path;
    }

    // Insert into pos_product table
    $stmt = $pdo->prepare("
        INSERT INTO pos_product (
            category_id,
            product_name,
            product_price,
            description,
            ingredients,
            product_image,
            product_status,
            created_at
        ) VALUES (
            :category_id,
            :product_name,
            :product_price,
            :description,
            :ingredients,
            :product_image,
            :product_status,
            NOW()
        )
    ");

    $stmt->execute([
        'category_id' => $_POST['category_id'],
        'product_name' => $_POST['product_name'],
        'product_price' => $_POST['product_price'],
        'description' => !empty($_POST['description']) ? $_POST['description'] : null,
        'ingredients' => !empty($_POST['ingredients']) ? $_POST['ingredients'] : null,
        'product_image' => $product_image,
        'product_status' => $_POST['product_status']
    ]);

    $product_id = $pdo->lastInsertId();

    // Handle branch assignments
    if (isset($_POST['branches']) && is_array($_POST['branches'])) {
        $branch_stmt = $pdo->prepare("INSERT INTO product_branch (product_id, branch_id) VALUES (?, ?)");
        
        foreach ($_POST['branches'] as $branch_id) {
            if (is_numeric($branch_id)) {
                try {
                    $branch_stmt->execute([$product_id, $branch_id]);
                } catch (PDOException $e) {
                    // Ignore duplicate entry errors
                    if ($e->getCode() != 23000) {
                        throw $e;
                    }
                }
            }
        }
    }

    echo json_encode([
        'success' => true,
        'message' => 'Product added successfully',
        'product_id' => $product_id
    ]);

} catch (Exception $e) {
    echo json_encode([
        'success' => false,
        'message' => $e->getMessage()
    ]);
}
?> 