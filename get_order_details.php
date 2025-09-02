<?php
require_once 'db_connect.php';
require_once 'auth_function.php';

// Check if user has access to orders
checkOrderAccess();

header('Content-Type: application/json');

if (!isset($_GET['order_id']) || empty($_GET['order_id'])) {
    echo json_encode([
        'success' => false,
        'message' => 'Order ID is required'
    ]);
    exit;
}

$orderId = intval($_GET['order_id']);

try {
    // Get detailed order information
    $query = "
        SELECT 
            o.order_id,
            o.order_number,
            o.customer_name,
            o.order_type,
            o.order_total,
            o.status,
            o.created_at,
            o.user_id,
            o.special_instructions,
            u.user_name as cashier_name
        FROM pos_orders o
        LEFT JOIN pos_users u ON o.user_id = u.user_id
        WHERE o.order_id = ?
    ";
    
    $stmt = $pdo->prepare($query);
    $stmt->execute([$orderId]);
    $order = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if (!$order) {
        echo json_encode([
            'success' => false,
            'message' => 'Order not found'
        ]);
        exit;
    }
    
    // Get order items with detailed information
    $itemsQuery = "
        SELECT 
            oi.item_id,
            oi.quantity,
            oi.unit_price,
            oi.special_instructions,
            p.product_name as name,
            p.product_description,
            c.category_name
        FROM pos_order_items oi
        LEFT JOIN pos_products p ON oi.product_id = p.product_id
        LEFT JOIN pos_categories c ON p.category_id = c.category_id
        WHERE oi.order_id = ?
        ORDER BY oi.item_id
    ";
    
    $itemsStmt = $pdo->prepare($itemsQuery);
    $itemsStmt->execute([$orderId]);
    $items = $itemsStmt->fetchAll(PDO::FETCH_ASSOC);
    
    // Process special instructions to extract modifications
    foreach ($items as &$item) {
        $item['modifications'] = [];
        
        if (!empty($item['special_instructions'])) {
            $instructions = $item['special_instructions'];
            
            // Look for common modification patterns
            $modifications = [];
            
            // Check for additions (marked with * or +)
            if (preg_match_all('/\*([^*]+)/', $instructions, $matches)) {
                foreach ($matches[1] as $match) {
                    $modifications[] = [
                        'type' => 'add',
                        'description' => trim($match)
                    ];
                }
            }
            
            // Check for removals (marked with NO or WITHOUT)
            if (preg_match_all('/NO\s+([^,]+)/i', $instructions, $matches)) {
                foreach ($matches[1] as $match) {
                    $modifications[] = [
                        'type' => 'remove',
                        'description' => trim($match)
                    ];
                }
            }
            
            // Check for other patterns like "Extra", "Add", etc.
            if (preg_match_all('/(?:Extra|Add)\s+([^,]+)/i', $instructions, $matches)) {
                foreach ($matches[1] as $match) {
                    $modifications[] = [
                        'type' => 'add',
                        'description' => trim($match)
                    ];
                }
            }
            
            $item['modifications'] = $modifications;
        }
        
        // Calculate item total
        $item['item_total'] = $item['quantity'] * $item['unit_price'];
    }
    
    $order['items'] = $items;
    
    // Format timestamps
    $order['created_at'] = date('Y-m-d H:i:s', strtotime($order['created_at']));
    
    // Ensure status is properly set
    if (empty($order['status'])) {
        $order['status'] = 'PENDING';
    }
    
    // Ensure order type is properly set
    if (empty($order['order_type'])) {
        $order['order_type'] = 'TAKE OUT';
    }
    
    echo json_encode([
        'success' => true,
        'order' => $order
    ]);
    
} catch (PDOException $e) {
    error_log('Database error in get_order_details.php: ' . $e->getMessage());
    echo json_encode([
        'success' => false,
        'message' => 'Database error occurred',
        'error' => $e->getMessage()
    ]);
} catch (Exception $e) {
    error_log('General error in get_order_details.php: ' . $e->getMessage());
    echo json_encode([
        'success' => false,
        'message' => 'An error occurred',
        'error' => $e->getMessage()
    ]);
}
?>
