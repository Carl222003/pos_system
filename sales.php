<?php
require_once 'db_connect.php';
require_once 'auth_function.php';

checkCashierLogin();

$confData = getConfigData($pdo);

// Get today's date
$today = date('Y-m-d');

// Get daily sales summary
$stmt = $pdo->prepare("
    SELECT 
        COUNT(DISTINCT o.order_id) as total_orders,
        COALESCE(SUM(o.order_total), 0) as total_sales,
        COALESCE(MIN(o.order_total), 0) as min_sale,
        COALESCE(MAX(o.order_total), 0) as max_sale,
        COALESCE(AVG(o.order_total), 0) as avg_sale
    FROM pos_order o
    LEFT JOIN pos_order_item oi ON o.order_id = oi.order_id
    WHERE DATE(o.order_datetime) = ? 
    AND o.order_created_by = ?
");
$stmt->execute([$today, $_SESSION['user_id']]);
$daily_summary = $stmt->fetch(PDO::FETCH_ASSOC);

include('header.php');
?>

<div class="container-fluid px-4">
    <h1 class="mt-4">Sales Dashboard</h1>
    <ol class="breadcrumb mb-4">
        <li class="breadcrumb-item active">Sales Overview</li>
    </ol>

    <!-- Daily Sales Summary -->
    <div class="row mb-4">
        <div class="col-xl-3 col-md-6">
            <div class="stat-card bg-gradient-primary">
                <div class="stat-card-inner">
                    <div class="stat-icon">
                        <i class="fas fa-shopping-cart"></i>
                    </div>
                    <div class="stat-content">
                        <h4 class="stat-value"><?php echo $daily_summary['total_orders']; ?></h4>
                        <div class="stat-label">Total Orders Today</div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-xl-3 col-md-6">
            <div class="stat-card bg-gradient-success">
                <div class="stat-card-inner">
                    <div class="stat-icon">
                        <i class="fas fa-peso-sign"></i>
                    </div>
                    <div class="stat-content">
                        <h4 class="stat-value">₱<?php echo number_format($daily_summary['total_sales'], 2); ?></h4>
                        <div class="stat-label">Total Sales Today</div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-xl-3 col-md-6">
            <div class="stat-card bg-gradient-warning">
                <div class="stat-card-inner">
                    <div class="stat-icon">
                        <i class="fas fa-chart-line"></i>
                    </div>
                    <div class="stat-content">
                        <h4 class="stat-value">₱<?php echo number_format($daily_summary['avg_sale'], 2); ?></h4>
                        <div class="stat-label">Average Sale Today</div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-xl-3 col-md-6">
            <div class="stat-card bg-gradient-info">
                <div class="stat-card-inner">
                    <div class="stat-icon">
                        <i class="fas fa-trophy"></i>
                    </div>
                    <div class="stat-content">
                        <h4 class="stat-value">₱<?php echo number_format($daily_summary['max_sale'], 2); ?></h4>
                        <div class="stat-label">Highest Sale Today</div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Quick Actions -->
    <div class="row mb-4">
        <div class="col-12">
            <div class="action-card">
                <div class="action-card-header">
                    <h5 class="mb-0">Quick Actions</h5>
                </div>
                <div class="action-card-body">
                    <div class="d-flex gap-3">
                        <a href="add_order.php" class="action-button primary">
                            <i class="fas fa-plus-circle me-2"></i>New Order
                        </a>
                        <a href="order_history.php" class="action-button secondary">
                            <i class="fas fa-history me-2"></i>View History
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Today's Orders -->
    <div class="row">
        <div class="col-12">
            <div class="orders-card">
                <div class="orders-card-header">
                    <div class="d-flex justify-content-between align-items-center">
                        <h5 class="mb-0">Today's Orders</h5>
                        <a href="order_history.php" class="view-all-link">View All</a>
                    </div>
                </div>
                <div class="orders-card-body">
                    <div class="table-responsive">
                        <table class="table table-hover">
                            <thead>
                                <tr>
                                    <th>Time</th>
                                    <th>Order #</th>
                                    <th>Items</th>
                                    <th>Total</th>
                                    <th>Action</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php
                                $orders_stmt = $pdo->prepare("
                                    SELECT o.*, 
                                           GROUP_CONCAT(CONCAT(oi.product_qty, 'x ', oi.product_name) SEPARATOR ', ') as items
                                    FROM pos_order o
                                    LEFT JOIN pos_order_item oi ON o.order_id = oi.order_id
                                    WHERE DATE(o.order_datetime) = ?
                                    AND o.order_created_by = ?
                                    GROUP BY o.order_id
                                    ORDER BY o.order_datetime DESC
                                ");
                                $orders_stmt->execute([$today, $_SESSION['user_id']]);
                                
                                while ($order = $orders_stmt->fetch(PDO::FETCH_ASSOC)) {
                                    echo "<tr>";
                                    echo "<td>" . date('h:i A', strtotime($order['order_datetime'])) . "</td>";
                                    echo "<td>" . htmlspecialchars($order['order_number']) . "</td>";
                                    echo "<td>" . htmlspecialchars($order['items']) . "</td>";
                                    echo "<td>₱" . number_format($order['order_total'], 2) . "</td>";
                                    echo "<td>
                                            <a href='print_order.php?id=" . $order['order_id'] . "' class='btn btn-primary btn-sm print-btn' target='_blank'>
                                                <i class='fas fa-print'></i>
                                            </a>
                                        </td>";
                                    echo "</tr>";
                                }
                                ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<style>
/* Enhanced Card Styles */
.stat-card {
    border-radius: 15px;
    padding: 1.5rem;
    margin-bottom: 1rem;
    box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
    transition: transform 0.2s ease, box-shadow 0.2s ease;
}

.stat-card:hover {
    transform: translateY(-5px);
    box-shadow: 0 6px 12px rgba(0, 0, 0, 0.15);
}

.stat-card-inner {
    display: flex;
    align-items: center;
    gap: 1.5rem;
}

.stat-icon {
    font-size: 2.5rem;
    color: rgba(255, 255, 255, 0.9);
}

.stat-content {
    flex-grow: 1;
}

.stat-value {
    font-size: 1.8rem;
    font-weight: 700;
    margin: 0;
    color: #ffffff;
}

.stat-label {
    color: rgba(255, 255, 255, 0.9);
    font-size: 0.9rem;
    margin-top: 0.25rem;
}

/* Gradient Backgrounds */
.bg-gradient-primary {
    background: linear-gradient(135deg, #4e73df 0%, #224abe 100%);
}

.bg-gradient-success {
    background: linear-gradient(135deg, #1cc88a 0%, #13855c 100%);
}

.bg-gradient-warning {
    background: linear-gradient(135deg, #f6c23e 0%, #dda20a 100%);
}

.bg-gradient-info {
    background: linear-gradient(135deg, #36b9cc 0%, #258391 100%);
}

/* Action Card Styles */
.action-card {
    background: #ffffff;
    border-radius: 15px;
    box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
    margin-bottom: 1.5rem;
}

.action-card-header {
    padding: 1.25rem;
    border-bottom: 1px solid rgba(0, 0, 0, 0.05);
}

.action-card-body {
    padding: 1.25rem;
}

.action-button {
    padding: 0.75rem 1.5rem;
    border-radius: 10px;
    font-weight: 600;
    text-decoration: none;
    transition: all 0.3s ease;
    display: inline-flex;
    align-items: center;
}

.action-button.primary {
    background: #4e73df;
    color: #ffffff;
}

.action-button.secondary {
    background: #858796;
    color: #ffffff;
}

.action-button:hover {
    transform: translateY(-2px);
    color: #ffffff;
    opacity: 0.9;
}

/* Orders Card Styles */
.orders-card {
    background: #ffffff;
    border-radius: 15px;
    box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
}

.orders-card-header {
    padding: 1.25rem;
    border-bottom: 1px solid rgba(0, 0, 0, 0.05);
}

.orders-card-body {
    padding: 1.25rem;
}

.view-all-link {
    color: #4e73df;
    text-decoration: none;
    font-weight: 600;
    font-size: 0.9rem;
}

.view-all-link:hover {
    text-decoration: underline;
}

/* Table Styles */
.table {
    margin-bottom: 0;
}

.table th {
    font-weight: 600;
    color: #5a5c69;
    border-bottom: 2px solid #e3e6f0;
}

.table td {
    vertical-align: middle;
    color: #858796;
    border-color: #e3e6f0;
}

.print-btn {
    padding: 0.375rem 0.75rem;
    border-radius: 8px;
    transition: all 0.2s ease;
}

.print-btn:hover {
    transform: translateY(-2px);
}

/* Responsive Adjustments */
@media (max-width: 768px) {
    .stat-card {
        padding: 1rem;
    }

    .stat-icon {
        font-size: 2rem;
    }

    .stat-value {
        font-size: 1.5rem;
    }

    .action-button {
        padding: 0.5rem 1rem;
    }
}
</style>

<?php include('footer.php'); ?> 