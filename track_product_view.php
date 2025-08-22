<?php
require_once 'db_connect.php';
require_once 'auth_function.php';

// Check if user is logged in
if (!isLoggedIn()) {
    http_response_code(401);
    echo json_encode(['success' => false, 'message' => 'Unauthorized']);
    exit;
}

// Check if product_id is provided
if (!isset($_POST['product_id']) || empty($_POST['product_id'])) {
    echo json_encode(['success' => false, 'message' => 'Product ID is required']);
    exit;
}

$product_id = intval($_POST['product_id']);
$user_id = $_SESSION['user_id'];
$branch_id = $_SESSION['branch_id'] ?? null;
$current_time = date('Y-m-d H:i:s');

try {
    // Check if we already have a views table, if not create it
    $stmt = $pdo->query("SHOW TABLES LIKE 'product_views'");
    if ($stmt->rowCount() == 0) {
        // Create product_views table
        $pdo->exec("
            CREATE TABLE product_views (
                view_id INT PRIMARY KEY AUTO_INCREMENT,
                product_id INT NOT NULL,
                user_id INT,
                branch_id INT,
                view_date DATETIME NOT NULL,
                ip_address VARCHAR(45),
                user_agent TEXT,
                INDEX idx_product_id (product_id),
                INDEX idx_view_date (view_date),
                INDEX idx_user_id (user_id),
                INDEX idx_branch_id (branch_id)
            )
        ");
    }
    
    // Insert the view record
    $stmt = $pdo->prepare("
        INSERT INTO product_views (product_id, user_id, branch_id, view_date, ip_address, user_agent) 
        VALUES (?, ?, ?, ?, ?, ?)
    ");
    
    $stmt->execute([
        $product_id,
        $user_id,
        $branch_id,
        $current_time,
        $_SERVER['REMOTE_ADDR'] ?? null,
        $_SERVER['HTTP_USER_AGENT'] ?? null
    ]);
    
    echo json_encode([
        'success' => true,
        'message' => 'View tracked successfully'
    ]);
    
} catch (PDOException $e) {
    error_log("Database error in track_product_view.php: " . $e->getMessage());
    echo json_encode([
        'success' => false,
        'message' => 'Database error occurred'
    ]);
}
?>
