<?php
require_once 'db_connect.php';
require_once 'auth_function.php';

// Check admin login
checkAdminLogin();

// Set header for JSON response
header('Content-Type: application/json');

try {
    // Check if it's a POST request
    if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
        throw new Exception('Invalid request method');
    }

    // Get user ID
    $user_id = $_POST['user_id'] ?? '';
    if (empty($user_id)) {
        throw new Exception('User ID is required');
    }

    // Get form data
    $user_name = trim($_POST['user_name'] ?? '');
    $user_email = trim($_POST['user_email'] ?? '');
    $contact_number = trim($_POST['contact_number'] ?? '');
    $branch_id = $_POST['branch_id'] ?? '';
    $user_type = $_POST['user_type'] ?? '';
    $user_status = $_POST['user_status'] ?? '';
    $user_password = trim($_POST['user_password'] ?? '');
    $address = trim($_POST['address'] ?? '');

    // Validate required fields
    if (empty($user_name)) {
        throw new Exception('User name is required');
    }

    if (empty($user_email)) {
        throw new Exception('Email is required');
    }

    if (!filter_var($user_email, FILTER_VALIDATE_EMAIL)) {
        throw new Exception('Invalid email format');
    }

    if (empty($user_type)) {
        throw new Exception('User type is required');
    }

    if (empty($user_status)) {
        throw new Exception('User status is required');
    }

    // Check if email already exists for another user
    $stmt = $pdo->prepare("SELECT COUNT(*) FROM pos_user WHERE user_email = :user_email AND user_id != :user_id");
    $stmt->execute(['user_email' => $user_email, 'user_id' => $user_id]);
    $count = $stmt->fetchColumn();
    
    if ($count > 0) {
        throw new Exception('Email already exists for another user');
    }

    // Handle profile image upload
    $profile_image = null;
    if (isset($_FILES['profile_image']) && $_FILES['profile_image']['error'] === 0) {
        $allowed_types = ['image/jpeg', 'image/jpg', 'image/png', 'image/gif'];
        $file_type = $_FILES['profile_image']['type'];
        
        if (!in_array($file_type, $allowed_types)) {
            throw new Exception('Invalid file type. Only JPG, PNG, and GIF are allowed.');
        }

        $file_size = $_FILES['profile_image']['size'];
        if ($file_size > 5 * 1024 * 1024) { // 5MB limit
            throw new Exception('File size too large. Maximum size is 5MB.');
        }

        $filename = $_FILES['profile_image']['name'];
        $ext = strtolower(pathinfo($filename, PATHINFO_EXTENSION));
        $upload_name = 'profile_' . time() . '.' . $ext;
        $upload_path = 'uploads/profiles/' . $upload_name;

        // Create directory if it doesn't exist
        if (!is_dir('uploads/profiles/')) {
            mkdir('uploads/profiles/', 0777, true);
        }

        if (move_uploaded_file($_FILES['profile_image']['tmp_name'], $upload_path)) {
            $profile_image = $upload_path;
        } else {
            throw new Exception('Failed to upload profile image');
        }
    }

    // Build update query
    $sql = "UPDATE pos_user SET 
            user_name = :user_name,
            user_email = :user_email,
            contact_number = :contact_number,
            branch_id = :branch_id,
            user_type = :user_type,
            user_status = :user_status,
            address = :address";

    $params = [
        'user_name' => $user_name,
        'user_email' => $user_email,
        'contact_number' => $contact_number,
        'branch_id' => $branch_id,
        'user_type' => $user_type,
        'user_status' => $user_status,
        'address' => $address,
        'user_id' => $user_id
    ];

    // Add profile image to update if uploaded
    if ($profile_image) {
        $sql .= ", profile_image = :profile_image";
        $params['profile_image'] = $profile_image;
    }

    // Add password to update if provided
    if (!empty($user_password)) {
        $sql .= ", user_password = :user_password";
        $params['user_password'] = password_hash($user_password, PASSWORD_DEFAULT);
    }

    $sql .= " WHERE user_id = :user_id";

    // Execute update
    $stmt = $pdo->prepare($sql);
    $result = $stmt->execute($params);

    if ($result) {
        // If user is a cashier, also update pos_cashier_details table
        if ($user_type === 'Cashier') {
            // Check if cashier details exist
            $checkStmt = $pdo->prepare("SELECT COUNT(*) FROM pos_cashier_details WHERE user_id = ?");
            $checkStmt->execute([$user_id]);
            $exists = $checkStmt->fetchColumn() > 0;
            
            if ($exists) {
                // Update existing cashier details
                $cashierSql = "UPDATE pos_cashier_details SET branch_id = :branch_id WHERE user_id = :user_id";
                $cashierStmt = $pdo->prepare($cashierSql);
                $cashierStmt->execute(['branch_id' => $branch_id, 'user_id' => $user_id]);
            } else {
                // Insert new cashier details
                $cashierSql = "INSERT INTO pos_cashier_details (user_id, branch_id, created_at) VALUES (:user_id, :branch_id, NOW())";
                $cashierStmt = $pdo->prepare($cashierSql);
                $cashierStmt->execute(['user_id' => $user_id, 'branch_id' => $branch_id]);
            }
        }

        // If user is a stockman, also update pos_stockman_details table
        if ($user_type === 'Stockman') {
            // Check if stockman details exist
            $checkStmt = $pdo->prepare("SELECT COUNT(*) FROM pos_stockman_details WHERE user_id = ?");
            $checkStmt->execute([$user_id]);
            $exists = $checkStmt->fetchColumn() > 0;
            
            if ($exists) {
                // Update existing stockman details
                $stockmanSql = "UPDATE pos_stockman_details SET branch_id = :branch_id WHERE user_id = :user_id";
                $stockmanStmt = $pdo->prepare($stockmanSql);
                $stockmanStmt->execute(['branch_id' => $branch_id, 'user_id' => $user_id]);
            } else {
                // Insert new stockman details
                $stockmanSql = "INSERT INTO pos_stockman_details (user_id, branch_id, created_at) VALUES (:user_id, :branch_id, NOW())";
                $stockmanStmt = $pdo->prepare($stockmanSql);
                $stockmanStmt->execute(['user_id' => $user_id, 'branch_id' => $branch_id]);
            }
        }
        
        echo json_encode([
            'success' => true,
            'message' => 'User updated successfully'
        ]);
    } else {
        throw new Exception('Failed to update user');
    }

} catch (Exception $e) {
    echo json_encode([
        'success' => false,
        'message' => $e->getMessage()
    ]);
}
?> 