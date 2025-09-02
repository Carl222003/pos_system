<?php
require_once 'db_connect.php';
require_once 'auth_function.php';

// Check if user has access to orders
checkOrderAccess();

header('Content-Type: application/json');

try {
    // Get orders with customer information and items, including the actual cashier who created the order
    $query = "
        SELECT 
            o.order_id as id,
            o.order_number,
            o.customer_name,
            o.order_type,
            o.order_total,
            o.status,
            o.created_at,
            o.user_id,
            u.user_name as cashier_name,
            u.first_name,
            u.last_name
        FROM pos_orders o
        LEFT JOIN pos_users u ON o.user_id = u.user_id
        WHERE o.status IN ('PENDING', 'PREPARING', 'READY')
        ORDER BY o.created_at ASC
    ";
    
    $stmt = $pdo->prepare($query);
    $stmt->execute();
    $orders = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    // Get items and modifications for each order
    foreach ($orders as &$order) {
        // Get order items
        $itemsQuery = "
            SELECT 
                oi.item_id,
                oi.quantity,
                p.product_name as name,
                oi.special_instructions
            FROM pos_order_items oi
            LEFT JOIN pos_products p ON oi.product_id = p.product_id
            WHERE oi.order_id = ?
        ";
        
        $itemsStmt = $pdo->prepare($itemsQuery);
        $itemsStmt->execute([$order['id']]);
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
        }
        
        $order['items'] = $items;
        
        // Format timestamps
        $order['created_at'] = date('Y-m-d H:i:s', strtotime($order['created_at']));
        
        // Ensure status is properly set
        if (empty($order['status'])) {
            $order['status'] = 'PENDING';
        }
        
        // Ensure order type is properly set and normalize
        if (empty($order['order_type'])) {
            $order['order_type'] = 'TAKE OUT';
        } else {
            // Normalize order types to match the KDS columns
            $orderType = strtoupper(trim($order['order_type']));
            switch ($orderType) {
                case 'DINE IN':
                case 'DINE-IN':
                case 'DINEIN':
                    $order['order_type'] = 'DINE IN';
                    break;
                case 'TAKE OUT':
                case 'TAKEOUT':
                case 'TAKE-OUT':
                    $order['order_type'] = 'TAKE OUT';
                    break;
                case 'DELIVERY':
                case 'DELIVER':
                    $order['order_type'] = 'DELIVERY';
                    break;
                case 'DRIVE THRU':
                case 'DRIVE-THRU':
                case 'DRIVETHRU':
                    $order['order_type'] = 'DRIVE THRU';
                    break;
                default:
                    $order['order_type'] = 'TAKE OUT';
                    break;
            }
        }
        
        // Ensure cashier name is properly set - use actual cashier who created the order
        if (empty($order['cashier_name'])) {
            // Try to construct name from first_name and last_name
            if (!empty($order['first_name']) || !empty($order['last_name'])) {
                $order['cashier_name'] = trim($order['first_name'] . ' ' . $order['last_name']);
            } else {
                $order['cashier_name'] = 'Unknown Cashier';
            }
        }
        
        // Remove server information - not needed
        unset($order['first_name'], $order['last_name']);
    }
    
    echo json_encode([
        'success' => true,
        'orders' => $orders,
        'total' => count($orders)
    ]);
    
} catch (PDOException $e) {
    error_log('Database error in get_order_data.php: ' . $e->getMessage());
    echo json_encode([
        'success' => false,
        'message' => 'Database error occurred',
        'error' => $e->getMessage()
    ]);
} catch (Exception $e) {
    error_log('General error in get_order_data.php: ' . $e->getMessage());
    echo json_encode([
        'success' => false,
        'message' => 'An error occurred',
        'error' => $e->getMessage()
    ]);
}
?>