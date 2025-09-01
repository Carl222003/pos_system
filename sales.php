<?php
require_once 'db_connect.php';
require_once 'auth_function.php';

// Check if user is logged in as cashier and has an active session
if (!checkCashierLogin()) {
    // If no active session, but they're logged in, show error
    if (isset($_SESSION['error'])) {
        $error_message = $_SESSION['error'];
        unset($_SESSION['error']);
        // Display error in a user-friendly way
        ?>
        <!DOCTYPE html>
        <html>
        <head>
            <title>Session Error</title>
            <link href="asset/vendor/bootstrap/bootstrap.min.css" rel="stylesheet">
        </head>
        <body>
            <div class="container mt-5">
                <div class="alert alert-danger">
                    <h4>Error</h4>
                    <p><?php echo htmlspecialchars($error_message); ?></p>
                    <a href="logout.php" class="btn btn-primary">Return to Login</a>
                </div>
            </div>
        </body>
        </html>
        <?php
        exit();
    }
    // If not logged in at all, redirect to login
    header('Location: login.php');
    exit();
}

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
    WHERE DATE(o.order_datetime) = ? 
    AND o.order_created_by = ?
");
$stmt->execute([$today, $_SESSION['user_id']]);
$daily_summary = $stmt->fetch(PDO::FETCH_ASSOC);

// Get today's orders with items
$orders_stmt = $pdo->prepare("
    SELECT 
        o.order_id,
        o.order_number,
        o.order_datetime,
        o.order_total,
        GROUP_CONCAT(
            CONCAT(oi.product_qty, 'x ', p.product_name)
            SEPARATOR ', '
        ) as items
    FROM pos_order o
    LEFT JOIN pos_order_item oi ON o.order_id = oi.order_id
    LEFT JOIN pos_product p ON oi.product_id = p.product_id
    WHERE DATE(o.order_datetime) = ?
    AND o.order_created_by = ?
    GROUP BY o.order_id, o.order_number, o.order_datetime, o.order_total
    ORDER BY o.order_datetime DESC
");
$orders_stmt->execute([$today, $_SESSION['user_id']]);

include('header.php');
?>

<div class="cashier-dashboard-bg">
<div class="container-fluid px-4">
        <h1 class="dashboard-title">
            <i class="fas fa-chart-line me-3"></i>
            Sales Dashboard
        </h1>
    

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

    <!-- Enhanced Sales Chart and Top Products -->
    <div class="row mb-4">
        <!-- Daily Sales Chart -->
        <div class="col-lg-8">
            <div class="chart-card enhanced">
                <div class="chart-card-header">
                    <div class="chart-title-section">
                        <h5 class="mb-0">
                            <i class="fas fa-chart-line me-2"></i>
                            Daily Sales Overview
                        </h5>
                        <span class="chart-subtitle">Track your daily performance</span>
                    </div>
                    <div class="chart-actions">
                        <select id="chartType" class="form-select form-select-sm enhanced">
                            <option value="quantity">📊 Order Quantity</option>
                            <option value="total">💰 Sales Total</option>
                        </select>
                    </div>
                </div>
                <div class="chart-card-body">
                    <div class="chart-placeholder">
                    <canvas id="salesChart"></canvas>
                    </div>
                </div>
            </div>
        </div>

        <!-- Top Products -->
        <div class="col-lg-4">
            <div class="top-products-card enhanced">
                <div class="top-products-header">
                    <h5 class="mb-0">
                        <i class="fas fa-trophy me-2"></i>
                        Top 3 Products
                    </h5>
                    <span class="products-subtitle">Best performers today</span>
                </div>
                <div class="top-products-body" id="topProductsList">
                    <div class="empty-state">
                        <i class="fas fa-box-open"></i>
                        <p>No products data yet</p>
                        <small>Products will appear here once orders are placed</small>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Enhanced Today's Orders -->
    <div class="row">
        <div class="col-12">
            <div class="orders-card enhanced">
                <div class="orders-card-header">
                    <div class="orders-header-content">
                        <div class="orders-title-section">
                            <h5 class="mb-0">
                                <i class="fas fa-list-alt me-2"></i>
                                Today's Orders
                            </h5>
                            <span class="orders-subtitle">Monitor your daily transactions</span>
                        </div>
                        <div class="orders-actions">
                            <a href="order_history.php" class="view-all-link enhanced">
                                <i class="fas fa-external-link-alt me-1"></i>
                                View All Orders
                            </a>
                        </div>
                    </div>
                </div>
                <div class="orders-card-body">
                    <div class="table-responsive">
                        <table class="table table-hover enhanced">
                            <thead>
                                <tr>
                                    <th><i class="fas fa-clock me-1"></i>Time</th>
                                    <th><i class="fas fa-hashtag me-1"></i>Order #</th>
                                    <th><i class="fas fa-box me-1"></i>Items</th>
                                    <th><i class="fas fa-peso-sign me-1"></i>Total</th>
                                    <th><i class="fas fa-cogs me-1"></i>Action</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php
                                if ($orders_stmt->rowCount() > 0) {
                                while ($order = $orders_stmt->fetch(PDO::FETCH_ASSOC)) {
                                        echo "<tr class='order-row'>";
                                        echo "<td><span class='time-badge'>" . date('h:i A', strtotime($order['order_datetime'])) . "</span></td>";
                                        echo "<td><span class='order-number'>" . htmlspecialchars($order['order_number']) . "</span></td>";
                                        echo "<td><span class='items-text'>" . htmlspecialchars($order['items']) . "</span></td>";
                                        echo "<td><span class='total-amount'>₱" . number_format($order['order_total'], 2) . "</span></td>";
                                    echo "<td>
                                                <a href='print_order.php?id=" . $order['order_id'] . "' class='btn btn-primary btn-sm print-btn enhanced' target='_blank'>
                                                    <i class='fas fa-print me-1'></i>
                                                    Print
                                            </a>
                                        </td>";
                                    echo "</tr>";
                                    }
                                } else {
                                    echo "<tr><td colspan='5' class='text-center no-orders'>
                                            <div class='empty-orders-state'>
                                                <i class='fas fa-inbox'></i>
                                                <h6>No Orders Today</h6>
                                                <p>Start creating orders to see them here</p>
                                            </div>
                                          </td></tr>";
                                }
                                ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>

        <!-- Enhanced Create Order Section -->
    <div class="row mb-4">
        <div class="col-12">
            <div class="create-order-section">
            <div class="d-flex justify-content-end">
                    <a href="add_order.php" class="btn btn-primary btn-lg create-order-btn">
                        <i class="fas fa-plus-circle me-2"></i>
                        Create New Order
                    </a>
                </div>
            </div>
            </div>
        </div>
    </div>
</div>

<style>
/* MoreBites POS System - Maroon Enhanced Dashboard Styles */
.cashier-dashboard-bg {
    background: linear-gradient(135deg, #fef7f7 0%, #fef2f2 50%, #fef7f7 100%);
    min-height: 100vh;
    padding-bottom: 2rem;
    position: relative;
    overflow-x: hidden;
}

.cashier-dashboard-bg::before {
    content: '';
    position: absolute;
    top: 0;
    left: 0;
    right: 0;
    height: 100%;
    background: 
        radial-gradient(circle at 20% 80%, rgba(139, 69, 67, 0.04) 0%, transparent 50%),
        radial-gradient(circle at 80% 20%, rgba(34, 197, 94, 0.04) 0%, transparent 50%),
        radial-gradient(circle at 40% 40%, rgba(245, 158, 11, 0.03) 0%, transparent 50%),
        url('data:image/svg+xml,<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 100 100"><defs><pattern id="food-pattern" x="0" y="0" width="20" height="20" patternUnits="userSpaceOnUse"><circle cx="10" cy="10" r="1" fill="rgba(139,69,67,0.02)"/><path d="M5,5 L15,15 M15,5 L5,15" stroke="rgba(34,197,94,0.02)" stroke-width="0.5"/></pattern></defs><rect width="100" height="100" fill="url(%23food-pattern)"/></svg>');
    pointer-events: none;
    z-index: 0;
    animation: subtleFloat 20s ease-in-out infinite;
}

.cashier-dashboard-bg > * {
    position: relative;
    z-index: 1;
}

/* MoreBites Maroon Enhanced Dashboard Title */
.dashboard-title {
    color: #8B4543;
    font-size: 2.8rem;
    font-weight: 900;
    letter-spacing: 1px;
    margin-bottom: 2.5rem;
    margin-top: 2rem;
    display: flex;
    align-items: center;
    gap: 1.5rem;
    position: relative;
    animation: fadeInDown 0.8s ease-out;
    text-shadow: 0 2px 4px rgba(139, 69, 67, 0.1);
}

.dashboard-title i {
    background: linear-gradient(135deg, #8B4543 0%, #b97a6a 50%, #d4a574 100%);
    -webkit-background-clip: text;
    -webkit-text-fill-color: transparent;
    background-clip: text;
    font-size: 3rem;
    filter: drop-shadow(0 2px 4px rgba(139, 69, 67, 0.2));
    animation: iconPulse 2s ease-in-out infinite;
}

.dashboard-title::after {
    content: '';
    display: block;
    position: absolute;
    left: 0;
    bottom: -12px;
    width: 100%;
    height: 6px;
    border-radius: 3px;
    background: linear-gradient(90deg, #8B4543 0%, #22c55e 50%, #f59e0b 100%);
    opacity: 0.4;
    animation: slideInLeft 1s ease-out 0.5s both;
    box-shadow: 0 2px 8px rgba(139, 69, 67, 0.2);
}

.dashboard-title::before {
    content: '🍽️';
    position: absolute;
    right: -60px;
    top: 50%;
    transform: translateY(-50%);
    font-size: 2rem;
    opacity: 0.6;
    animation: foodBounce 3s ease-in-out infinite;
}

/* Enhanced Card Styles */
.stat-card {
    border-radius: 24px;
    padding: 2rem;
    margin-bottom: 1.5rem;
    box-shadow: 0 8px 32px rgba(0, 0, 0, 0.1);
    transition: all 0.4s cubic-bezier(0.175, 0.885, 0.32, 1.275);
    position: relative;
    overflow: hidden;
    border: 2px solid rgba(255, 255, 255, 0.1);
}

.stat-card::before {
    content: '';
    position: absolute;
    top: 0;
    left: -100%;
    width: 100%;
    height: 100%;
    background: linear-gradient(90deg, transparent, rgba(255, 255, 255, 0.1), transparent);
    transition: left 0.6s ease;
}

.stat-card:hover {
    transform: translateY(-8px) scale(1.02);
    box-shadow: 0 20px 60px rgba(0, 0, 0, 0.2);
    border-color: rgba(255, 255, 255, 0.3);
}

.stat-card:hover::before {
    left: 100%;
}

.stat-card-inner {
    display: flex;
    align-items: center;
    gap: 2rem;
    position: relative;
    z-index: 2;
}

.stat-icon {
    font-size: 3rem;
    color: rgba(255, 255, 255, 0.95);
    transition: all 0.3s ease;
    filter: drop-shadow(0 2px 4px rgba(0, 0, 0, 0.2));
}

.stat-card:hover .stat-icon {
    transform: scale(1.1) rotate(5deg);
    filter: drop-shadow(0 4px 8px rgba(0, 0, 0, 0.3));
}

.stat-content {
    flex-grow: 1;
}

.stat-value {
    font-size: 2.2rem;
    font-weight: 900;
    margin: 0;
    color: #ffffff;
    text-shadow: 0 2px 4px rgba(0, 0, 0, 0.2);
    line-height: 1;
}

.stat-label {
    color: rgba(255, 255, 255, 0.95);
    font-size: 0.85rem;
    margin-top: 0.5rem;
    font-weight: 600;
    text-transform: uppercase;
    letter-spacing: 0.5px;
    opacity: 0.9;
}

/* MoreBites Maroon Food-Themed Gradient Backgrounds */
.bg-gradient-primary {
    background: linear-gradient(135deg, #8B4543 0%, #b97a6a 100%);
    box-shadow: 0 8px 32px rgba(139, 69, 67, 0.3);
    position: relative;
    overflow: hidden;
}

.bg-gradient-primary::after {
    content: '🛒';
    position: absolute;
    right: -20px;
    top: -20px;
    font-size: 4rem;
    opacity: 0.1;
    animation: floatIcon 4s ease-in-out infinite;
}

.bg-gradient-success {
    background: linear-gradient(135deg, #22c55e 0%, #16a34a 100%);
    box-shadow: 0 8px 32px rgba(34, 197, 94, 0.3);
    position: relative;
    overflow: hidden;
}

.bg-gradient-success::after {
    content: '💰';
    position: absolute;
    right: -20px;
    top: -20px;
    font-size: 4rem;
    opacity: 0.1;
    animation: floatIcon 4s ease-in-out infinite 1s;
}

.bg-gradient-warning {
    background: linear-gradient(135deg, #f59e0b 0%, #d97706 100%);
    box-shadow: 0 8px 32px rgba(245, 158, 11, 0.3);
    position: relative;
    overflow: hidden;
}

.bg-gradient-warning::after {
    content: '📊';
    position: absolute;
    right: -20px;
    top: -20px;
    font-size: 4rem;
    opacity: 0.1;
    animation: floatIcon 4s ease-in-out infinite 2s;
}

.bg-gradient-info {
    background: linear-gradient(135deg, #06b6d4 0%, #0891b2 100%);
    box-shadow: 0 8px 32px rgba(6, 182, 212, 0.3);
    position: relative;
    overflow: hidden;
}

.bg-gradient-info::after {
    content: '🏆';
    position: absolute;
    right: -20px;
    top: -20px;
    font-size: 4rem;
    opacity: 0.1;
    animation: floatIcon 4s ease-in-out infinite 3s;
}

/* MoreBites Enhanced Orders Card Styles */
.orders-card.enhanced {
    background: linear-gradient(135deg, rgba(255, 255, 255, 0.95) 0%, rgba(254, 249, 195, 0.95) 100%);
    border-radius: 24px;
    box-shadow: 0 8px 32px rgba(245, 158, 11, 0.08);
    border: 2px solid rgba(245, 158, 11, 0.05);
    transition: all 0.4s cubic-bezier(0.175, 0.885, 0.32, 1.275);
    backdrop-filter: blur(10px);
    overflow: hidden;
    position: relative;
}

.orders-card.enhanced::before {
    content: '📋';
    position: absolute;
    right: -20px;
    top: -20px;
    font-size: 3rem;
    opacity: 0.05;
    animation: foodBounce 4s ease-in-out infinite 2s;
}

.orders-card.enhanced:hover {
    transform: translateY(-4px);
    box-shadow: 0 16px 48px rgba(245, 158, 11, 0.15);
    border-color: rgba(245, 158, 11, 0.2);
}

.orders-card-header {
    padding: 1.5rem 2rem;
    border-bottom: 2px solid rgba(245, 158, 11, 0.08);
    background: linear-gradient(135deg, rgba(245, 158, 11, 0.02) 0%, rgba(217, 119, 6, 0.02) 100%);
}

.orders-header-content {
    display: flex;
    justify-content: space-between;
    align-items: center;
    width: 100%;
}

.orders-title-section {
    display: flex;
    flex-direction: column;
    gap: 0.25rem;
}

.orders-title-section h5 {
    font-weight: 700;
    color: #2c3e50;
    margin: 0;
    font-size: 1.3rem;
    display: flex;
    align-items: center;
}

.orders-subtitle {
    font-size: 0.85rem;
    color: #6c757d;
    font-weight: 500;
}

.orders-actions {
    display: flex;
    gap: 1rem;
}

.orders-card-header h5 {
    font-weight: 700;
    color: #2c3e50;
    margin: 0;
    font-size: 1.3rem;
}

.orders-card-body {
    padding: 2rem;
    background: linear-gradient(135deg, #fafafa 0%, #f0f0f0 100%);
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

/* MoreBites Maroon Enhanced Chart Card Styles */
.chart-card.enhanced {
    background: linear-gradient(135deg, rgba(255, 255, 255, 0.95) 0%, rgba(254, 242, 242, 0.95) 100%);
    border-radius: 24px;
    box-shadow: 0 8px 32px rgba(139, 69, 67, 0.08);
    height: 100%;
    border: 2px solid rgba(139, 69, 67, 0.05);
    transition: all 0.4s cubic-bezier(0.175, 0.885, 0.32, 1.275);
    backdrop-filter: blur(10px);
    overflow: hidden;
    position: relative;
}

.chart-card.enhanced::before {
    content: '📈';
    position: absolute;
    right: -20px;
    top: -20px;
    font-size: 3rem;
    opacity: 0.05;
    animation: foodBounce 4s ease-in-out infinite;
}

.chart-card.enhanced:hover {
    transform: translateY(-4px);
    box-shadow: 0 16px 48px rgba(139, 69, 67, 0.15);
    border-color: rgba(139, 69, 67, 0.2);
}

.chart-card-header {
    padding: 1.5rem 2rem;
    border-bottom: 2px solid rgba(139, 69, 67, 0.08);
    display: flex;
    justify-content: space-between;
    align-items: center;
    background: linear-gradient(135deg, rgba(139, 69, 67, 0.02) 0%, rgba(34, 197, 94, 0.02) 100%);
}

.chart-title-section {
    display: flex;
    flex-direction: column;
    gap: 0.25rem;
}

.chart-title-section h5 {
    font-weight: 700;
    color: #2c3e50;
    margin: 0;
    font-size: 1.3rem;
    display: flex;
    align-items: center;
}

.chart-subtitle {
    font-size: 0.85rem;
    color: #6c757d;
    font-weight: 500;
}

.chart-actions {
    display: flex;
    gap: 1rem;
}

.chart-card-body {
    padding: 2rem;
    height: 400px;
    position: relative;
    background: linear-gradient(135deg, #fafafa 0%, #f0f0f0 100%);
}

.chart-placeholder {
    height: 100%;
    display: flex;
    align-items: center;
    justify-content: center;
}

.chart-actions {
    display: flex;
    gap: 1rem;
}

/* MoreBites Enhanced Top Products Card Styles */
.top-products-card.enhanced {
    background: linear-gradient(135deg, rgba(255, 255, 255, 0.95) 0%, rgba(240, 253, 244, 0.95) 100%);
    border-radius: 24px;
    box-shadow: 0 8px 32px rgba(34, 197, 94, 0.08);
    height: 100%;
    border: 2px solid rgba(34, 197, 94, 0.05);
    transition: all 0.4s cubic-bezier(0.175, 0.885, 0.32, 1.275);
    backdrop-filter: blur(10px);
    overflow: hidden;
    position: relative;
}

.top-products-card.enhanced::before {
    content: '🥇';
    position: absolute;
    right: -20px;
    top: -20px;
    font-size: 3rem;
    opacity: 0.05;
    animation: foodBounce 4s ease-in-out infinite 1s;
}

.top-products-card.enhanced:hover {
    transform: translateY(-4px);
    box-shadow: 0 16px 48px rgba(34, 197, 94, 0.15);
    border-color: rgba(34, 197, 94, 0.2);
}

.top-products-header {
    padding: 1.5rem 2rem;
    border-bottom: 2px solid rgba(34, 197, 94, 0.08);
    background: linear-gradient(135deg, rgba(34, 197, 94, 0.02) 0%, rgba(22, 163, 74, 0.02) 100%);
}

.top-products-header h5 {
    font-weight: 700;
    color: #2c3e50;
    margin: 0;
    font-size: 1.3rem;
    display: flex;
    align-items: center;
}

.products-subtitle {
    font-size: 0.85rem;
    color: #6c757d;
    font-weight: 500;
    margin-top: 0.25rem;
    display: block;
}

.top-products-body {
    padding: 2rem;
    background: linear-gradient(135deg, #fafafa 0%, #f0f0f0 100%);
    min-height: 300px;
    display: flex;
    align-items: center;
    justify-content: center;
}

.empty-state {
    text-align: center;
    color: #6c757d;
}

.empty-state i {
    font-size: 3rem;
    color: #dee2e6;
    margin-bottom: 1rem;
}

.empty-state p {
    font-size: 1.1rem;
    font-weight: 600;
    margin-bottom: 0.5rem;
}

.empty-state small {
    color: #adb5bd;
}

.product-card {
    display: flex;
    align-items: center;
    padding: 1.5rem;
    border-radius: 16px;
    margin-bottom: 1.5rem;
    background: linear-gradient(135deg, #ffffff 0%, #f8f9fa 100%);
    transition: all 0.4s cubic-bezier(0.175, 0.885, 0.32, 1.275);
    border: 2px solid rgba(28, 200, 138, 0.08);
    box-shadow: 0 4px 16px rgba(28, 200, 138, 0.08);
    position: relative;
    overflow: hidden;
}

.product-card::before {
    content: '';
    position: absolute;
    top: 0;
    left: -100%;
    width: 100%;
    height: 100%;
    background: linear-gradient(90deg, transparent, rgba(28, 200, 138, 0.05), transparent);
    transition: left 0.6s ease;
}

.product-card:hover {
    transform: translateY(-4px) scale(1.02);
    box-shadow: 0 12px 32px rgba(28, 200, 138, 0.15);
    border-color: rgba(28, 200, 138, 0.2);
}

.product-card:hover::before {
    left: 100%;
}

.product-image {
    width: 80px;
    height: 80px;
    border-radius: 10px;
    object-fit: cover;
    margin-right: 1rem;
}

.product-info {
    flex: 1;
}

.product-name {
    font-weight: 600;
    color: #2c3e50;
    margin-bottom: 0.25rem;
}

.product-stats {
    font-size: 0.9rem;
    color: #6c757d;
}

.product-rank {
    font-size: 1.5rem;
    font-weight: 700;
    color: #4e73df;
    margin-left: 1rem;
}

/* MoreBites Maroon Enhanced Form Control Styles */
.form-select-sm {
    padding: 0.5rem 2.5rem 0.5rem 1rem;
    font-size: 0.9rem;
    border-radius: 12px;
    border: 2px solid rgba(139, 69, 67, 0.1);
    background: linear-gradient(135deg, #ffffff 0%, #f8f9fa 100%);
    cursor: pointer;
    transition: all 0.3s ease;
    font-weight: 500;
    color: #2c3e50;
    box-shadow: 0 2px 8px rgba(139, 69, 67, 0.1);
}

.form-select-sm:hover {
    border-color: rgba(139, 69, 67, 0.3);
    box-shadow: 0 4px 16px rgba(139, 69, 67, 0.15);
    transform: translateY(-1px);
}

.form-select-sm:focus {
    border-color: #8B4543;
    box-shadow: 0 0 0 0.3rem rgba(139, 69, 67, 0.25);
    outline: none;
    transform: translateY(-1px);
}

/* MoreBites Maroon Enhanced Navigation Card */
.navigation-card {
    background: linear-gradient(135deg, rgba(255, 255, 255, 0.95) 0%, rgba(254, 242, 242, 0.95) 100%);
    border-radius: 24px;
    box-shadow: 0 8px 32px rgba(139, 69, 67, 0.08);
    border: 2px solid rgba(139, 69, 67, 0.05);
    transition: all 0.4s cubic-bezier(0.175, 0.885, 0.32, 1.275);
    backdrop-filter: blur(10px);
    overflow: hidden;
    animation: fadeInUp 0.8s ease-out 0.3s both;
    position: relative;
}

.navigation-card::before {
    content: '🍽️';
    position: absolute;
    right: -30px;
    top: -30px;
    font-size: 6rem;
    opacity: 0.03;
    animation: foodRotate 10s linear infinite;
}

.navigation-card:hover {
    transform: translateY(-4px);
    box-shadow: 0 16px 48px rgba(139, 69, 67, 0.15);
    border-color: rgba(139, 69, 67, 0.2);
}

.navigation-card-body {
    padding: 2rem;
    background: linear-gradient(135deg, rgba(139, 69, 67, 0.02) 0%, rgba(34, 197, 94, 0.02) 100%);
}

.navigation-title {
    font-weight: 700;
    color: #8B4543;
    font-size: 1.1rem;
    text-transform: uppercase;
    letter-spacing: 0.5px;
    display: flex;
    align-items: center;
    gap: 0.5rem;
}

.navigation-title i {
    color: #f59e0b;
    animation: compassSpin 3s linear infinite;
}

/* MoreBites Maroon Enhanced Button Styles */
.btn-primary {
    background: linear-gradient(135deg, #8B4543 0%, #b97a6a 100%);
    border: none;
    border-radius: 16px;
    padding: 0.875rem 2rem;
    font-weight: 700;
    transition: all 0.4s cubic-bezier(0.175, 0.885, 0.32, 1.275);
    box-shadow: 0 6px 20px rgba(139, 69, 67, 0.3);
    position: relative;
    overflow: hidden;
    color: white;
    text-transform: uppercase;
    letter-spacing: 0.5px;
}

.btn-primary::before {
    content: '';
    position: absolute;
    top: 0;
    left: -100%;
    width: 100%;
    height: 100%;
    background: linear-gradient(90deg, transparent, rgba(255, 255, 255, 0.2), transparent);
    transition: left 0.6s ease;
}

.btn-primary:hover {
    transform: translateY(-3px) scale(1.05);
    box-shadow: 0 12px 32px rgba(139, 69, 67, 0.4);
}

.btn-primary:hover::before {
    left: 100%;
}

.btn-outline-primary {
    border: 3px solid #8B4543;
    color: #8B4543;
    background: transparent;
    border-radius: 16px;
    padding: 0.875rem 2rem;
    font-weight: 700;
    transition: all 0.4s cubic-bezier(0.175, 0.885, 0.32, 1.275);
    text-transform: uppercase;
    letter-spacing: 0.5px;
    position: relative;
    overflow: hidden;
}

.btn-outline-primary::before {
    content: '';
    position: absolute;
    top: 0;
    left: -100%;
    width: 100%;
    height: 100%;
    background: linear-gradient(135deg, #8B4543 0%, #b97a6a 100%);
    transition: left 0.6s ease;
    z-index: -1;
}

.btn-outline-primary:hover {
    color: white;
    transform: translateY(-3px) scale(1.05);
    box-shadow: 0 12px 32px rgba(139, 69, 67, 0.3);
}

.btn-outline-primary:hover::before {
    left: 0;
}

/* MoreBites Maroon Enhanced Table Styles */
.table.enhanced {
    margin-bottom: 0;
    border-radius: 12px;
    overflow: hidden;
    border: none;
}

.table.enhanced th {
    font-weight: 700;
    color: #2c3e50;
    border-bottom: 2px solid rgba(139, 69, 67, 0.1);
    background: linear-gradient(135deg, rgba(139, 69, 67, 0.02) 0%, rgba(34, 197, 94, 0.02) 100%);
    padding: 1.25rem 1.5rem;
    font-size: 0.85rem;
    text-transform: uppercase;
    letter-spacing: 0.5px;
    border-top: none;
}

.table.enhanced th i {
    color: #8B4543;
    opacity: 0.7;
}

.table.enhanced td {
    vertical-align: middle;
    color: #5a5c69;
    border-color: rgba(139, 69, 67, 0.05);
    padding: 1.25rem 1.5rem;
    transition: all 0.2s ease;
    border-top: none;
}

.table.enhanced tbody tr.order-row {
    transition: all 0.3s ease;
}

.table.enhanced tbody tr.order-row:hover {
    background: linear-gradient(135deg, rgba(139, 69, 67, 0.03) 0%, rgba(34, 197, 94, 0.03) 100%);
    transform: translateX(5px);
    box-shadow: 0 2px 8px rgba(139, 69, 67, 0.1);
}

/* Enhanced Table Cell Elements */
.time-badge {
    background: linear-gradient(135deg, #8B4543 0%, #b97a6a 100%);
    color: white;
    padding: 0.5rem 1rem;
    border-radius: 20px;
    font-size: 0.85rem;
    font-weight: 600;
    display: inline-block;
}

.order-number {
    font-family: 'Courier New', monospace;
    font-weight: 700;
    color: #8B4543;
    background: rgba(139, 69, 67, 0.1);
    padding: 0.25rem 0.75rem;
    border-radius: 8px;
    font-size: 0.9rem;
}

.items-text {
    max-width: 200px;
    overflow: hidden;
    text-overflow: ellipsis;
    white-space: nowrap;
    display: block;
    color: #495057;
    font-size: 0.9rem;
}

.total-amount {
    font-weight: 700;
    color: #22c55e;
    font-size: 1.1rem;
}

/* Empty Orders State */
.no-orders {
    padding: 3rem 1rem;
}

.empty-orders-state {
    text-align: center;
    color: #6c757d;
}

.empty-orders-state i {
    font-size: 3rem;
    color: #dee2e6;
    margin-bottom: 1rem;
}

.empty-orders-state h6 {
    font-size: 1.2rem;
    font-weight: 600;
    margin-bottom: 0.5rem;
    color: #495057;
}

.empty-orders-state p {
    color: #adb5bd;
    margin-bottom: 0;
}

/* MoreBites Maroon Enhanced Print Button */
.print-btn {
    padding: 0.5rem 1rem;
    border-radius: 12px;
    transition: all 0.3s ease;
    background: linear-gradient(135deg, #8B4543 0%, #b97a6a 100%);
    border: none;
    color: white;
    font-weight: 600;
}

.print-btn:hover {
    transform: translateY(-2px);
    box-shadow: 0 6px 20px rgba(139, 69, 67, 0.4);
}

/* MoreBites Maroon Enhanced View All Link */
.view-all-link.enhanced {
    color: #8B4543;
    text-decoration: none;
    font-weight: 700;
    font-size: 0.9rem;
    padding: 0.75rem 1.5rem;
    border-radius: 12px;
    transition: all 0.3s ease;
    background: rgba(139, 69, 67, 0.1);
    border: 2px solid rgba(139, 69, 67, 0.2);
    display: inline-flex;
    align-items: center;
    gap: 0.5rem;
}

.view-all-link.enhanced:hover {
    background: rgba(139, 69, 67, 0.2);
    color: #8B4543;
    text-decoration: none;
    transform: translateY(-2px);
    box-shadow: 0 4px 16px rgba(139, 69, 67, 0.2);
    border-color: rgba(139, 69, 67, 0.4);
}

/* Enhanced Create Order Section */
.create-order-section {
    margin-top: 2rem;
}

.create-order-btn {
    background: linear-gradient(135deg, #8B4543 0%, #b97a6a 100%);
    border: none;
    border-radius: 20px;
    padding: 1rem 2.5rem;
    font-weight: 700;
    font-size: 1.1rem;
    transition: all 0.4s cubic-bezier(0.175, 0.885, 0.32, 1.275);
    box-shadow: 0 8px 32px rgba(139, 69, 67, 0.3);
    position: relative;
    overflow: hidden;
    color: white;
    text-transform: uppercase;
    letter-spacing: 0.5px;
}

.create-order-btn::before {
    content: '';
    position: absolute;
    top: 0;
    left: -100%;
    width: 100%;
    height: 100%;
    background: linear-gradient(90deg, transparent, rgba(255, 255, 255, 0.2), transparent);
    transition: left 0.6s ease;
}

.create-order-btn:hover {
    transform: translateY(-4px) scale(1.05);
    box-shadow: 0 16px 48px rgba(139, 69, 67, 0.4);
}

.create-order-btn:hover::before {
    left: 100%;
}

.create-order-btn i {
    font-size: 1.2rem;
    margin-right: 0.5rem;
}

/* MoreBites Special Effects & Animations */
@keyframes fadeInDown {
    from {
        opacity: 0;
        transform: translateY(-30px);
    }
    to {
        opacity: 1;
        transform: translateY(0);
    }
}

@keyframes slideInLeft {
    from {
        opacity: 0;
        transform: translateX(-30px);
    }
    to {
        opacity: 1;
        transform: translateX(0);
    }
}

@keyframes fadeInUp {
    from {
        opacity: 0;
        transform: translateY(30px);
    }
    to {
        opacity: 1;
        transform: translateY(0);
    }
}

/* MoreBites Unique Animations */
@keyframes iconPulse {
    0%, 100% { transform: scale(1); }
    50% { transform: scale(1.1); }
}

@keyframes foodBounce {
    0%, 100% { transform: translateY(-50%) rotate(0deg); }
    25% { transform: translateY(-50%) rotate(10deg); }
    75% { transform: translateY(-50%) rotate(-10deg); }
}

@keyframes floatIcon {
    0%, 100% { transform: translateY(0px) rotate(0deg); opacity: 0.1; }
    50% { transform: translateY(-10px) rotate(180deg); opacity: 0.2; }
}

@keyframes foodRotate {
    0% { transform: rotate(0deg); }
    100% { transform: rotate(360deg); }
}

@keyframes compassSpin {
    0% { transform: rotate(0deg); }
    100% { transform: rotate(360deg); }
}

@keyframes subtleFloat {
    0%, 100% { transform: translateY(0px); }
    50% { transform: translateY(-5px); }
}

/* Particle Effects */
.stat-card::after {
    content: '';
    position: absolute;
    top: 0;
    left: 0;
    right: 0;
    bottom: 0;
    background: 
        radial-gradient(circle at 20% 80%, rgba(255, 255, 255, 0.1) 0%, transparent 50%),
        radial-gradient(circle at 80% 20%, rgba(255, 255, 255, 0.1) 0%, transparent 50%);
    opacity: 0;
    transition: opacity 0.3s ease;
}

.stat-card:hover::after {
    opacity: 1;
}

/* Animation Classes */
.stat-card {
    animation: fadeInUp 0.6s ease-out;
}

.stat-card:nth-child(1) { animation-delay: 0.1s; }
.stat-card:nth-child(2) { animation-delay: 0.2s; }
.stat-card:nth-child(3) { animation-delay: 0.3s; }
.stat-card:nth-child(4) { animation-delay: 0.4s; }

.chart-card {
    animation: fadeInUp 0.8s ease-out 0.5s both;
}

.top-products-card {
    animation: fadeInUp 0.8s ease-out 0.6s both;
}

.orders-card {
    animation: fadeInUp 0.8s ease-out 0.7s both;
}

/* Responsive Enhancements */
@media (max-width: 768px) {
    .stat-card {
        padding: 1.5rem;
        margin-bottom: 1rem;
    }

    .stat-icon {
        font-size: 2.5rem;
    }

    .stat-value {
        font-size: 1.8rem;
    }

    .dashboard-title {
        font-size: 2rem;
    }

    .chart-card-body {
        height: 300px;
        padding: 1.5rem;
    }
}
</style>

<script>
// Initialize charts when document is ready
document.addEventListener('DOMContentLoaded', function() {
    let salesChart = null;
    const chartCtx = document.getElementById('salesChart').getContext('2d');
    
    // Function to load dashboard data
    function loadDashboardData() {
        const chartType = document.getElementById('chartType').value;
        
        fetch('get_dashboard_data.php?chart_type=' + chartType)
            .then(response => response.json())
            .then(data => {
                updateSalesChart(data);
                updateTopProducts(data.topProducts);
                updateSummaryCards(data.summary);
            })
            .catch(error => console.error('Error loading dashboard data:', error));
    }
    
    // Function to update summary cards
    function updateSummaryCards(summary) {
        document.querySelector('.stat-card:nth-child(1) .stat-value').textContent = summary.total_orders;
        document.querySelector('.stat-card:nth-child(2) .stat-value').textContent = '₱' + parseFloat(summary.total_sales).toFixed(2);
        document.querySelector('.stat-card:nth-child(3) .stat-value').textContent = '₱' + parseFloat(summary.avg_sale).toFixed(2);
        document.querySelector('.stat-card:nth-child(4) .stat-value').textContent = '₱' + parseFloat(summary.max_sale).toFixed(2);
    }
    
    // Function to update sales chart
    function updateSalesChart(data) {
        const chartConfig = {
            type: 'line',
            data: {
                labels: data.purchaseTrend.labels,
                datasets: [{
                    label: 'Sales',
                    data: data.purchaseTrend.data,
                    borderColor: '#4e73df',
                    backgroundColor: 'rgba(78, 115, 223, 0.1)',
                    borderWidth: 2,
                    fill: true,
                    tension: 0.4
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: {
                        display: false
                    },
                    tooltip: {
                        mode: 'index',
                        intersect: false,
                        callbacks: {
                            label: function(context) {
                                return '₱' + context.parsed.y.toFixed(2);
                            }
                        }
                    }
                },
                scales: {
                    y: {
                        beginAtZero: true,
                        ticks: {
                            callback: function(value) {
                                return '₱' + value.toFixed(2);
                            }
                        }
                    }
                }
            }
        };

        if (salesChart) {
            salesChart.destroy();
        }
        salesChart = new Chart(chartCtx, chartConfig);
    }
    
    // Function to update top products
    function updateTopProducts(topProducts) {
        const container = document.getElementById('topProductsList');
        container.innerHTML = '';
        
        topProducts.labels.forEach((product, index) => {
            const card = document.createElement('div');
            card.className = 'product-card';
            card.innerHTML = `
                <img src="${topProducts.images[index]}" alt="${product}" class="product-image">
                <div class="product-info">
                    <div class="product-name">${product}</div>
                    <div class="product-stats">
                        Quantity: ${topProducts.data[index]}<br>
                        Revenue: ₱${topProducts.revenue[index].toFixed(2)}
                    </div>
                </div>
                <div class="product-rank">#${index + 1}</div>
            `;
            container.appendChild(card);
        });
    }
    
    // Add event listener for chart type change
    document.getElementById('chartType').addEventListener('change', loadDashboardData);
    
    // Initial load
    loadDashboardData();
});

document.addEventListener('DOMContentLoaded', function() {
    loadTopProducts();
});

function loadTopProducts() {
    fetch('get_top_products.php?period=daily')
        .then(response => response.json())
        .then(products => {
            const container = document.getElementById('topProductsList');
            container.innerHTML = '';
            products.forEach(product => {
                const productCard = document.createElement('div');
                productCard.className = 'product-card';
                productCard.innerHTML = `
                    <img src="${product.image}" alt="${product.name}" class="product-image">
                    <div class="product-info">
                        <div class="product-name">${product.name}</div>
                        <div class="product-stats">
                            Quantity Sold: ${product.quantity}<br>
                            Revenue: ₱${product.revenue}
                        </div>
                        <a href="add_order.php?product_id=${product.id}" class="btn btn-primary mt-2">Order Now</a>
                    </div>
                `;
                container.appendChild(productCard);
            });
        })
        .catch(error => console.error('Error loading top products:', error));
}
</script>

<?php include('footer.php'); ?> 