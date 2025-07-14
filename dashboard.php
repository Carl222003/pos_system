<?php

require_once 'db_connect.php';
require_once 'auth_function.php';

requireLogin();
if ($_SESSION['user_type'] !== 'Admin') {
    echo "Access denied. Only Admin can access this page.";
    exit();
}

checkAdminLogin();

$categorySql = "SELECT COUNT(*) FROM pos_category WHERE status = 'active'";
$productSql = "SELECT COUNT(*) FROM pos_product";
$userSql = "SELECT COUNT(*) FROM pos_user";
$branchSql = "SELECT COUNT(*) FROM pos_branch WHERE status = 'Active'";
$orderSql = isset($_SESSION['user_type']) && $_SESSION['user_type'] === 'User' ?
    "SELECT SUM(order_total) FROM pos_order WHERE order_created_by = '" . $_SESSION['user_id'] . "'" :
    "SELECT SUM(order_total) FROM pos_order";

// Add Grab Food queries
$grabFoodSalesSql = "SELECT COALESCE(SUM(order_total), 0) FROM pos_order WHERE service_type = 'grab'";
$grabFoodOrdersSql = "SELECT COUNT(*) FROM pos_order WHERE service_type = 'grab'";
$grabFoodLastMonthSql = "SELECT COALESCE(SUM(order_total), 0) FROM pos_order 
                         WHERE service_type = 'grab' 
                         AND MONTH(order_datetime) = MONTH(CURRENT_DATE - INTERVAL 1 MONTH)
                         AND YEAR(order_datetime) = YEAR(CURRENT_DATE - INTERVAL 1 MONTH)";

// Add service type sales queries
$dineInSalesSql = "SELECT COALESCE(SUM(order_total), 0) FROM pos_order WHERE service_type = 'dine-in'";
$takeoutSalesSql = "SELECT COALESCE(SUM(order_total), 0) FROM pos_order WHERE service_type = 'takeout'";
$deliverySalesSql = "SELECT COALESCE(SUM(order_total), 0) FROM pos_order WHERE service_type = 'delivery'";

$stmt = $pdo->prepare($categorySql);
$stmt->execute();
$total_category = $stmt->fetchColumn();

$stmt = $pdo->prepare($productSql);
$stmt->execute();
$total_product = $stmt->fetchColumn();

$stmt = $pdo->prepare($userSql);
$stmt->execute();
$total_user = $stmt->fetchColumn();

$stmt = $pdo->prepare($branchSql);
$stmt->execute();
$total_branch = $stmt->fetchColumn();

$stmt = $pdo->prepare($orderSql);
$stmt->execute();
$total_sales = $stmt->fetchColumn();

// Execute Grab Food queries
$stmt = $pdo->prepare($grabFoodSalesSql);
$stmt->execute();
$grab_food_sales = $stmt->fetchColumn() ?: 0;

$stmt = $pdo->prepare($grabFoodOrdersSql);
$stmt->execute();
$grab_food_orders = $stmt->fetchColumn() ?: 0;

$stmt = $pdo->prepare($grabFoodLastMonthSql);
$stmt->execute();
$grab_food_last_month = $stmt->fetchColumn() ?: 1; // Avoid division by zero

// Calculate Grab Food metrics
$grab_food_average = $grab_food_orders > 0 ? $grab_food_sales / $grab_food_orders : 0;
$grab_food_growth = $grab_food_last_month > 0 ? 
    (($grab_food_sales - $grab_food_last_month) / $grab_food_last_month) * 100 : 0;

// Execute service type queries
$stmt = $pdo->prepare($dineInSalesSql);
$stmt->execute();
$dine_in_sales = $stmt->fetchColumn() ?: 0;

$stmt = $pdo->prepare($takeoutSalesSql);
$stmt->execute();
$takeout_sales = $stmt->fetchColumn() ?: 0;

$stmt = $pdo->prepare($deliverySalesSql);
$stmt->execute();
$delivery_sales = $stmt->fetchColumn() ?: 0;

$confData = getConfigData($pdo);

// Get initial data
$stmt = $pdo->query("SELECT COUNT(*) FROM pos_user WHERE user_type = 'Cashier' AND user_status = 'Active'");
$total_cashiers = $stmt->fetchColumn();

include('header.php');
?>

<div class="container-fluid px-4">
    <h1 class="mt-4 mb-4" style="color: #8B4543; font-size: 1.5rem; font-weight: 600; display: flex; align-items: center; gap: 0.75rem;">
        <i class="fas fa-chart-line"></i>
        Dashboard Overview
    </h1>

    <!-- Main Stats Row -->
    <div class="row g-4 mb-4">
        <!-- Total Revenue -->
        <div class="col-xl-3 col-lg-3 col-md-6">
            <div class="main-stats-card h-100">
                <div class="d-flex justify-content-between align-items-center mb-3">
                    <div>
                        <h6>Total Revenue</h6>
                        <h2 style="color: #9C27B0;">₱<?php echo number_format($total_sales, 2); ?></h2>
                    </div>
                    <div class="p-3 rounded-circle" style="background: linear-gradient(135deg, rgba(156,39,176,0.1), rgba(156,39,176,0.05));">
                        <i class="fas fa-peso-sign" style="color: #9C27B0;"></i>
                    </div>
                </div>
                <a href="sales_report.php" class="btn btn-sm w-100" 
                   style="background: linear-gradient(135deg, #9C27B0, #8E24AA); color: white; border: none;">
                    View Sales Report
                </a>
            </div>
        </div>

        <!-- Categories -->
        <div class="col-xl-3 col-lg-3 col-md-6">
            <div class="main-stats-card h-100">
                <div class="d-flex justify-content-between align-items-center mb-3">
                    <div>
                        <h6>Categories</h6>
                        <h2 style="color: #FF6B6B;"><?php echo $total_category; ?></h2>
                    </div>
                    <div class="p-3 rounded-circle" style="background: rgba(255,107,107,0.1);">
                        <i class="fas fa-th-list" style="color: #FF6B6B;"></i>
                    </div>
                </div>
                <a href="category.php" class="btn btn-sm w-100" 
                   style="background: linear-gradient(135deg, #FF6B6B, #FF5252); color: white; border: none;">
                    View Categories
                </a>
            </div>
        </div>

        <!-- Products -->
        <div class="col-xl-3 col-lg-3 col-md-6">
            <div class="main-stats-card h-100">
                <div class="d-flex justify-content-between align-items-center mb-3">
                    <div>
                        <h6>Products</h6>
                        <h2 style="color: #4CAF50;"><?php echo $total_product; ?></h2>
                    </div>
                    <div class="p-3 rounded-circle" style="background: rgba(76,175,80,0.1);">
                        <i class="fas fa-box" style="color: #4CAF50;"></i>
                    </div>
                </div>
                <a href="product.php" class="btn btn-sm w-100" 
                   style="background: linear-gradient(135deg, #4CAF50, #43A047); color: white; border: none;">
                    View Products
                </a>
            </div>
        </div>

        <!-- Branches -->
        <div class="col-xl-3 col-lg-3 col-md-6">
            <div class="main-stats-card h-100">
                <div class="d-flex justify-content-between align-items-center mb-3">
                    <div>
                        <h6>Branches</h6>
                        <h2 style="color: #2196F3;"><?php echo $total_branch; ?></h2>
                    </div>
                    <div class="p-3 rounded-circle" style="background: rgba(33,150,243,0.1);">
                        <i class="fas fa-store" style="color: #2196F3;"></i>
                    </div>
                </div>
                <a href="branch.php" class="btn btn-sm w-100" 
                   style="background: linear-gradient(135deg, #2196F3, #1E88E5); color: white; border: none;">
                    View Branches
                </a>
            </div>
        </div>
    </div>

    <!-- Service Type Stats Row -->
    <div class="row g-4 mb-4">
        <!-- Dine In -->
        <div class="col-xl-3 col-lg-3 col-md-6">
            <div class="service-type-card h-100">
                <div class="d-flex justify-content-between align-items-center mb-3">
                    <div>
                        <h6 class="text-muted mb-2">Dine In Sales</h6>
                        <h2 class="sales-amount" style="color: #9C27B0;">₱<?php echo number_format($dine_in_sales, 2); ?></h2>
                    </div>
                    <div class="service-icon-wrapper" style="background: linear-gradient(135deg, rgba(156,39,176,0.1), rgba(156,39,176,0.05));">
                        <i class="fas fa-utensils" style="color: #9C27B0;"></i>
                    </div>
                </div>
                <a href="sales_report.php?type=dine-in" class="btn btn-sm w-100" 
                   style="background: linear-gradient(135deg, #9C27B0, #8E24AA); color: white; border: none;">
                    View Dine In Sales
                </a>
            </div>
        </div>

        <!-- Takeout -->
        <div class="col-xl-3 col-lg-3 col-md-6">
            <div class="service-type-card h-100">
                <div class="d-flex justify-content-between align-items-center mb-3">
                    <div>
                        <h6 class="text-muted mb-2">Takeout Sales</h6>
                        <h2 class="sales-amount" style="color: #FF6B6B;">₱<?php echo number_format($takeout_sales, 2); ?></h2>
                    </div>
                    <div class="service-icon-wrapper" style="background: rgba(255,107,107,0.1);">
                        <i class="fas fa-shopping-bag" style="color: #FF6B6B;"></i>
                    </div>
                </div>
                <a href="sales_report.php?type=takeout" class="btn btn-sm w-100" 
                   style="background: linear-gradient(135deg, #FF6B6B, #FF5252); color: white; border: none;">
                    View Takeout Sales
                </a>
            </div>
        </div>

        <!-- Delivery -->
        <div class="col-xl-3 col-lg-3 col-md-6">
            <div class="service-type-card h-100">
                <div class="d-flex justify-content-between align-items-center mb-3">
                    <div>
                        <h6 class="text-muted mb-2">Delivery Sales</h6>
                        <h2 class="sales-amount" style="color: #4CAF50;">₱<?php echo number_format($delivery_sales, 2); ?></h2>
                    </div>
                    <div class="service-icon-wrapper" style="background: rgba(76,175,80,0.1);">
                        <i class="fas fa-motorcycle" style="color: #4CAF50;"></i>
                    </div>
                </div>
                <a href="sales_report.php?type=delivery" class="btn btn-sm w-100" 
                   style="background: linear-gradient(135deg, #4CAF50, #43A047); color: white; border: none;">
                    View Delivery Sales
                </a>
            </div>
        </div>

        <!-- Grab Food -->
        <div class="col-xl-3 col-lg-3 col-md-6">
            <div class="service-type-card h-100">
                <div class="d-flex justify-content-between align-items-center mb-3">
                    <div>
                        <h6 class="text-muted mb-2">Grab Food Sales</h6>
                        <h2 class="sales-amount" style="color: #00B14F;">₱<?php echo number_format($grab_food_sales, 2); ?></h2>
                    </div>
                    <div class="service-icon-wrapper grab-icon">
                        <div class="grab-icon-container">
                            <i class="fas fa-motorcycle grab-motorcycle"></i>
                        </div>
                    </div>
                </div>
                <a href="sales_report.php?type=grab" class="btn btn-sm w-100" 
                   style="background: linear-gradient(135deg, #00B14F, #009F47); color: white; border: none;">
                    View Grab Food Sales
                </a>
            </div>
        </div>
    </div>

    <!-- Charts Row -->
    <div class="row g-4 mb-4">
        <!-- Sales Trend Chart -->
        <div class="col-xl-8 col-lg-7">
            <div class="chart-card">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <div>
                        <h5 class="mb-0">Sales Trend</h5>
                        <p class="text-muted mb-0">Overview of sales performance</p>
                    </div>
                    <div class="btn-group">
                        <button type="button" class="btn btn-sm btn-outline-secondary period-selector active" data-period="daily">Daily</button>
                        <button type="button" class="btn btn-sm btn-outline-secondary period-selector" data-period="weekly">Weekly</button>
                        <button type="button" class="btn btn-sm btn-outline-secondary period-selector" data-period="monthly">Monthly</button>
                    </div>
                </div>
                <div class="card-body">
                    <canvas id="salesTrendChart" height="300"></canvas>
                </div>
            </div>
        </div>

        <!-- Product Distribution Chart -->
        <div class="col-xl-4 col-lg-5">
            <div class="chart-card">
                <div class="card-header">
                    <h5 class="mb-0">Product Distribution</h5>
                    <p class="text-muted mb-0">Products by category</p>
                </div>
                <div class="card-body">
                    <canvas id="productDistributionChart" height="300"></canvas>
                </div>
            </div>
        </div>
    </div>

    <!-- Branch Performance and Inventory Status -->
    <div class="row">
        <!-- Branch Performance - Made wider -->
        <div class="col-xl-8 col-lg-7">
            <div class="card">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <div>
                        <h5 class="mb-0">Branch Performance</h5>
                        <p class="text-muted mb-0">Today's sales by branch</p>
                    </div>
                    <a href="branch_comparison.php" class="btn btn-sm btn-primary">Compare Branches</a>
                </div>
                <div class="card-body">
                    <canvas id="branchPerformanceChart" height="300"></canvas>
                </div>
            </div>
        </div>

        <!-- Inventory Status - Made narrower -->
        <div class="col-xl-4 col-lg-5">
            <div class="card">
                <div class="card-header">
                    <h5 class="mb-0">Inventory Status</h5>
                    <p class="text-muted mb-0">Low stock alerts</p>
                </div>
                <div class="card-body">
                    <canvas id="inventoryStatusChart" height="300"></canvas>
                </div>
            </div>
        </div>
    </div>

    <!-- Cashier Performance Section - Full width -->
    <div class="row mt-4">
        <div class="col-12">
            <div class="card cashier-performance-card">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <div>
                        <h5 class="mb-0">Cashier Performance</h5>
                        <p class="text-muted mb-0">Active cashiers and their performance metrics</p>
                    </div>
                    <div class="d-flex gap-2">
                        <select class="form-select form-select-sm period-select" id="cashierPeriod">
                            <option value="today">Today</option>
                            <option value="week">This Week</option>
                            <option value="month">This Month</option>
                        </select>
                        <button class="btn btn-sm btn-primary btn-refresh" id="refreshCashierStats">
                            <i class="fas fa-sync-alt"></i> Refresh
                        </button>
                    </div>
                </div>
                <div class="card-body">
                    <div class="row g-3 mb-4">
                        <!-- Active Cashiers Card -->
                        <div class="col-md-3">
                            <div class="p-3 bg-light rounded">
                                <div class="d-flex justify-content-between align-items-center">
                                    <div>
                                        <h6 class="mb-1">Active Cashiers</h6>
                                        <h4 class="mb-0" id="activeCashiers">0</h4>
                                    </div>
                                    <div class="rounded-circle p-3 bg-white">
                                        <i class="fas fa-user-clock text-primary"></i>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <!-- Total Transactions Card -->
                        <div class="col-md-3">
                            <div class="p-3 bg-light rounded">
                                <div class="d-flex justify-content-between align-items-center">
                                    <div>
                                        <h6 class="mb-1">Total Transactions</h6>
                                        <h4 class="mb-0" id="totalTransactions">0</h4>
                                    </div>
                                    <div class="rounded-circle p-3 bg-white">
                                        <i class="fas fa-receipt text-success"></i>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <!-- Average Transaction Time Card -->
                        <div class="col-md-3">
                            <div class="p-3 bg-light rounded">
                                <div class="d-flex justify-content-between align-items-center">
                                    <div>
                                        <h6 class="mb-1">Avg. Transaction Time</h6>
                                        <h4 class="mb-0" id="avgTransactionTime">0m</h4>
                                    </div>
                                    <div class="rounded-circle p-3 bg-white">
                                        <i class="fas fa-clock text-warning"></i>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <!-- Total Sales Card -->
                        <div class="col-md-3">
                            <div class="p-3 bg-light rounded">
                                <div class="d-flex justify-content-between align-items-center">
                                    <div>
                                        <h6 class="mb-1">Total Sales</h6>
                                        <h4 class="mb-0" id="totalCashierSales">₱0.00</h4>
                                    </div>
                                    <div class="rounded-circle p-3 bg-white">
                                        <i class="fas fa-peso-sign text-info"></i>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Cashier Performance Table -->
                    <div class="table-responsive cashier-table">
                        <table class="table table-hover" id="cashierPerformanceTable">
                            <thead>
                                <tr>
                                    <th>Item</th>
                                    <th>Total</th>
                                    <th>Qty</th>
                                </tr>
                            </thead>
                            <tbody>
                                <!-- Dynamically populated -->
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Cashier Details Modal -->
<div class="modal fade" id="cashierDetailsModal" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Cashier Performance Details</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <div class="row g-3 mb-4">
                    <div class="col-md-6">
                        <div class="card">
                            <div class="card-body">
                                <h6 class="card-title">Hourly Sales</h6>
                                <canvas id="cashierHourlySalesChart" height="200"></canvas>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="card">
                            <div class="card-body">
                                <h6 class="card-title">Payment Methods</h6>
                                <canvas id="cashierPaymentMethodsChart" height="200"></canvas>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="card">
                    <div class="card-body">
                        <h6 class="card-title">Recent Transactions</h6>
                        <div class="table-responsive">
                            <table class="table table-sm" id="cashierTransactionsTable">
                                <thead>
                                    <tr>
                                        <th>Time</th>
                                        <th>Order ID</th>
                                        <th>Items</th>
                                        <th>Total</th>
                                        <th>Payment</th>
                                        <th>Status</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <!-- Dynamically populated -->
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<style>
/* Enhanced Card Styling */
.card {
    border: none;
    border-radius: 1rem;
    box-shadow: 0 0.25rem 1.5rem rgba(139, 69, 67, 0.08);
    transition: all 0.3s ease;
    overflow: hidden;
}

.card:hover {
    transform: translateY(-3px);
    box-shadow: 0 0.5rem 2rem rgba(139, 69, 67, 0.12);
}

.card-header {
    background: linear-gradient(to right, rgba(255,255,255,0.95), rgba(255,255,255,0.98));
    border-bottom: 1px solid rgba(139, 69, 67, 0.08);
    padding: 1.25rem 1.5rem;
}

.card-body {
    padding: 1.5rem;
    background: rgba(255, 255, 255, 0.98);
}

/* Main Stats Cards */
.main-stats-card {
    background: rgba(255, 255, 255, 0.7);
    backdrop-filter: blur(10px);
    -webkit-backdrop-filter: blur(10px);
    border-radius: 1.25rem;
    padding: 1.5rem;
    border: 1px solid rgba(255, 255, 255, 0.4);
    box-shadow: 0 8px 32px rgba(0, 0, 0, 0.1);
    transition: all 0.4s cubic-bezier(0.4, 0, 0.2, 1);
    position: relative;
    overflow: hidden;
}

.main-stats-card::before {
    content: '';
    position: absolute;
    top: 0;
    left: 0;
    right: 0;
    bottom: 0;
    background: linear-gradient(
        135deg,
        rgba(255, 255, 255, 0.2),
        rgba(255, 255, 255, 0.1)
    );
    z-index: 1;
    opacity: 0;
    transition: opacity 0.4s ease;
}

.main-stats-card:hover {
    transform: translateY(-10px) scale(1.02);
    box-shadow: 0 15px 45px rgba(0, 0, 0, 0.15);
    border: 1px solid rgba(255, 255, 255, 0.6);
}

.main-stats-card:hover::before {
    opacity: 1;
}

.main-stats-card h6 {
    font-size: 0.875rem;
    font-weight: 500;
    color: #6c757d;
    margin-bottom: 0.5rem;
    letter-spacing: 0.5px;
    transition: all 0.3s ease;
    position: relative;
    z-index: 2;
}

.main-stats-card:hover h6 {
    color: #333;
}

.main-stats-card h2 {
    font-size: 2.5rem;
    font-weight: 700;
    margin-bottom: 0;
    line-height: 1.2;
    transition: all 0.3s ease;
    position: relative;
    z-index: 2;
}

.main-stats-card:hover h2 {
    transform: scale(1.05);
    text-shadow: 2px 2px 4px rgba(0, 0, 0, 0.1);
}

.main-stats-card .rounded-circle {
    width: 3.5rem;
    height: 3.5rem;
    display: flex;
    align-items: center;
    justify-content: center;
    transition: all 0.4s cubic-bezier(0.4, 0, 0.2, 1);
    position: relative;
    z-index: 2;
    background: rgba(255, 255, 255, 0.9) !important;
    box-shadow: 0 4px 15px rgba(0, 0, 0, 0.1);
}

.main-stats-card:hover .rounded-circle {
    transform: scale(1.1) rotate(10deg);
    box-shadow: 0 8px 25px rgba(0, 0, 0, 0.15);
}

.main-stats-card .rounded-circle i {
    font-size: 1.5rem;
    transition: all 0.4s ease;
}

.main-stats-card:hover .rounded-circle i {
    transform: scale(1.1);
}

.main-stats-card .btn {
    border-radius: 0.75rem;
    font-weight: 500;
    padding: 0.75rem;
    transition: all 0.4s cubic-bezier(0.4, 0, 0.2, 1);
    text-transform: uppercase;
    font-size: 0.75rem;
    letter-spacing: 0.5px;
    position: relative;
    overflow: hidden;
    z-index: 2;
}

.main-stats-card .btn::before {
    content: '';
    position: absolute;
    top: 0;
    left: 0;
    width: 100%;
    height: 100%;
    background: linear-gradient(
        135deg,
        rgba(255, 255, 255, 0.2),
        rgba(255, 255, 255, 0.1)
    );
    transform: translateX(-100%);
    transition: transform 0.4s ease;
}

.main-stats-card .btn:hover {
    transform: translateY(-2px);
    box-shadow: 0 5px 15px rgba(0, 0, 0, 0.2);
}

.main-stats-card .btn:hover::before {
    transform: translateX(0);
}

/* Responsive Adjustments for Main Stats Cards */
@media (max-width: 768px) {
    .main-stats-card {
        padding: 1.25rem;
    }
    
    .main-stats-card h2 {
        font-size: 2rem;
    }
    
    .main-stats-card .rounded-circle {
        width: 3rem;
        height: 3rem;
    }
    
    .main-stats-card .rounded-circle i {
        font-size: 1.25rem;
    }
}

/* Secondary Stats Cards */
.secondary-stats-card {
    background: rgba(255,255,255,0.95);
    border-radius: 1rem;
    padding: 1.25rem;
    height: 100%;
}

.secondary-stats-card h6 {
    font-size: 0.75rem;
    font-weight: 500;
    color: #6c757d;
    margin-bottom: 0.25rem;
}

.secondary-stats-card h3 {
    font-size: 1.75rem;
    font-weight: 600;
    margin-bottom: 0;
}

/* Grab Food Card Styling */
.grab-card {
    background: linear-gradient(135deg, rgba(255,255,255,0.98), rgba(255,255,255,0.95));
    border-radius: 1.25rem;
    box-shadow: 0 0.5rem 2rem rgba(0,177,79,0.08);
}

.grab-card:hover {
    box-shadow: 0 0.75rem 3rem rgba(0,177,79,0.12);
}

.grab-stats {
    background: rgba(0,177,79,0.03);
    border-radius: 1rem;
    padding: 1.25rem;
    transition: all 0.3s ease;
}

.grab-stats:hover {
    background: rgba(0,177,79,0.05);
    transform: translateY(-2px);
}

/* Chart Cards */
.chart-card {
    background: linear-gradient(135deg, rgba(255,255,255,0.98), rgba(255,255,255,0.95));
    border-radius: 1.25rem;
}

.chart-card .card-header {
    padding: 1.5rem;
}

.chart-card .card-body {
    padding: 1.5rem;
}

/* Button Styling */
.btn {
    border-radius: 0.75rem;
    padding: 0.5rem 1.25rem;
    font-weight: 500;
    transition: all 0.3s ease;
}

.btn-primary {
    background: linear-gradient(135deg, #8B4543, #723937);
    border: none;
    box-shadow: 0 0.25rem 1rem rgba(139, 69, 67, 0.15);
}

.btn-primary:hover {
    background: linear-gradient(135deg, #723937, #5E2F2D);
    box-shadow: 0 0.5rem 1.5rem rgba(139, 69, 67, 0.2);
    transform: translateY(-1px);
}

.btn-outline-secondary {
    border: 1px solid rgba(139, 69, 67, 0.2);
    color: #8B4543;
}

.btn-outline-secondary:hover,
.btn-outline-secondary.active {
    background: linear-gradient(135deg, #8B4543, #723937);
    border-color: transparent;
    color: white;
    box-shadow: 0 0.25rem 1rem rgba(139, 69, 67, 0.15);
}

/* Table Styling */
.table {
    margin-bottom: 0;
}

.table th {
    font-weight: 600;
    font-size: 0.8125rem;
    text-transform: uppercase;
    letter-spacing: 0.5px;
    color: #6c757d;
    padding: 1rem;
    border-bottom: 2px solid rgba(139, 69, 67, 0.08);
}

.table td {
    padding: 1rem;
    vertical-align: middle;
    border-bottom: 1px solid rgba(139, 69, 67, 0.05);
}

/* Status Badges */
.status-badge {
    padding: 0.375rem 0.75rem;
    border-radius: 2rem;
    font-size: 0.75rem;
    font-weight: 600;
    display: inline-flex;
    align-items: center;
    gap: 0.375rem;
}

.status-badge.active {
    background: rgba(74, 124, 89, 0.1);
    color: #4A7C59;
}

.status-badge.inactive {
    background: rgba(139, 69, 67, 0.1);
    color: #8B4543;
}

/* Animation Effects */
@keyframes fadeIn {
    from { opacity: 0; transform: translateY(10px); }
    to { opacity: 1; transform: translateY(0); }
}

.card {
    animation: fadeIn 0.5s ease-out;
}

/* Custom Scrollbar */
::-webkit-scrollbar {
    width: 8px;
    height: 8px;
}

::-webkit-scrollbar-track {
    background: rgba(139, 69, 67, 0.05);
    border-radius: 4px;
}

::-webkit-scrollbar-thumb {
    background: rgba(139, 69, 67, 0.2);
    border-radius: 4px;
}

::-webkit-scrollbar-thumb:hover {
    background: rgba(139, 69, 67, 0.3);
}

/* Add styles for service type breakdown */
.service-type-breakdown {
    background: rgba(0,177,79,0.02);
    border-radius: 1rem;
    padding: 1.25rem;
}

.service-stat {
    background: white;
    border-radius: 0.75rem;
    padding: 1rem;
    transition: all 0.3s ease;
}

.service-stat:hover {
    transform: translateY(-2px);
    box-shadow: 0 0.5rem 1.5rem rgba(0,0,0,0.05);
}

.service-stat small {
    font-size: 0.75rem;
    font-weight: 500;
}

.service-stat h5 {
    font-size: 1.25rem;
    font-weight: 600;
    margin-top: 0.25rem;
}

/* Enhanced Service Type Cards */
.service-type-card {
    background: rgba(255, 255, 255, 0.7);
    backdrop-filter: blur(10px);
    -webkit-backdrop-filter: blur(10px);
    border-radius: 1.25rem;
    padding: 1.5rem;
    border: 1px solid rgba(255, 255, 255, 0.4);
    box-shadow: 0 8px 32px rgba(0, 0, 0, 0.1);
    transition: all 0.4s cubic-bezier(0.4, 0, 0.2, 1);
    position: relative;
    overflow: hidden;
}

.service-type-card::before {
    content: '';
    position: absolute;
    top: 0;
    left: 0;
    right: 0;
    bottom: 0;
    background: linear-gradient(
        135deg,
        rgba(255, 255, 255, 0.1),
        rgba(255, 255, 255, 0.05)
    );
    z-index: 1;
    opacity: 0;
    transition: opacity 0.4s ease;
}

.service-type-card:hover {
    transform: translateY(-10px) scale(1.02);
    box-shadow: 0 15px 45px rgba(0, 0, 0, 0.15);
    border: 1px solid rgba(255, 255, 255, 0.6);
}

.service-type-card:hover::before {
    opacity: 1;
}

.service-type-card .text-muted {
    font-size: 0.875rem;
    font-weight: 500;
    letter-spacing: 0.5px;
    transition: all 0.3s ease;
}

.service-type-card:hover .text-muted {
    color: #333 !important;
}

.service-type-card .sales-amount {
    font-size: 2rem;
    font-weight: 700;
    line-height: 1.2;
    margin-bottom: 0;
    transition: all 0.3s ease;
    position: relative;
    z-index: 2;
}

.service-type-card:hover .sales-amount {
    transform: scale(1.05);
    text-shadow: 2px 2px 4px rgba(0, 0, 0, 0.1);
}

.service-icon-wrapper {
    width: 3.5rem;
    height: 3.5rem;
    border-radius: 1rem;
    display: flex;
    align-items: center;
    justify-content: center;
    transition: all 0.4s cubic-bezier(0.4, 0, 0.2, 1);
    position: relative;
    z-index: 2;
    background: rgba(255, 255, 255, 0.9) !important;
    box-shadow: 0 4px 15px rgba(0, 0, 0, 0.1);
}

.service-icon-wrapper i {
    font-size: 1.5rem;
    transition: all 0.4s ease;
}

.service-type-card:hover .service-icon-wrapper {
    transform: scale(1.1) rotate(10deg);
    box-shadow: 0 8px 25px rgba(0, 0, 0, 0.15);
}

.service-type-card:hover .service-icon-wrapper i {
    transform: scale(1.1);
}

/* Grab Food Specific Styles */
.grab-icon {
    padding: 0;
    background: #00B14F !important;
    border-radius: 0.75rem;
    width: 3.5rem;
    height: 3.5rem;
    display: flex;
    align-items: center;
    justify-content: center;
    overflow: hidden;
    position: relative;
    box-shadow: 0 4px 15px rgba(0, 177, 79, 0.2);
    transition: all 0.4s cubic-bezier(0.4, 0, 0.2, 1);
}

.grab-icon-container {
    width: 100%;
    height: 100%;
    display: flex;
    align-items: center;
    justify-content: center;
    position: relative;
    z-index: 2;
}

.grab-motorcycle {
    color: white;
    font-size: 1.75rem;
    transition: all 0.4s cubic-bezier(0.4, 0, 0.2, 1);
}

.service-type-card:hover .grab-icon {
    transform: scale(1.1) rotate(10deg);
    box-shadow: 0 8px 25px rgba(0, 177, 79, 0.3);
}

.service-type-card:hover .grab-motorcycle {
    animation: rideMotorcycle 1s ease-in-out infinite;
}

@keyframes rideMotorcycle {
    0% { transform: translateX(0) rotate(0); }
    25% { transform: translateX(4px) rotate(5deg); }
    75% { transform: translateX(-4px) rotate(-5deg); }
    100% { transform: translateX(0) rotate(0); }
}

/* Button Enhancements */
.service-type-card .btn {
    border-radius: 0.75rem;
    font-weight: 500;
    padding: 0.75rem;
    transition: all 0.4s cubic-bezier(0.4, 0, 0.2, 1);
    text-transform: uppercase;
    font-size: 0.75rem;
    letter-spacing: 0.5px;
    position: relative;
    overflow: hidden;
    z-index: 2;
}

.service-type-card .btn::before {
    content: '';
    position: absolute;
    top: 0;
    left: 0;
    width: 100%;
    height: 100%;
    background: linear-gradient(
        135deg,
        rgba(255, 255, 255, 0.2),
        rgba(255, 255, 255, 0.1)
    );
    transform: translateX(-100%);
    transition: transform 0.4s ease;
}

.service-type-card .btn:hover {
    transform: translateY(-2px);
    box-shadow: 0 5px 15px rgba(0, 0, 0, 0.2);
}

.service-type-card .btn:hover::before {
    transform: translateX(0);
}

/* Responsive Adjustments */
@media (max-width: 768px) {
    .service-type-card {
        padding: 1.25rem;
    }
    
    .service-type-card .sales-amount {
        font-size: 1.75rem;
    }
    
    .service-icon-wrapper {
        width: 3rem;
        height: 3rem;
    }
}

/* Cashier Performance Section Styling */
.cashier-performance-card {
    background: rgba(255, 255, 255, 0.7);
    backdrop-filter: blur(10px);
    -webkit-backdrop-filter: blur(10px);
    border-radius: 1.25rem;
    border: 1px solid rgba(255, 255, 255, 0.4);
    box-shadow: 0 8px 32px rgba(0, 0, 0, 0.1);
    transition: all 0.4s cubic-bezier(0.4, 0, 0.2, 1);
    overflow: hidden;
}

.cashier-performance-card::before {
    content: '';
    position: absolute;
    top: 0;
    left: 0;
    right: 0;
    bottom: 0;
    background: linear-gradient(135deg, rgba(255, 255, 255, 0.2), rgba(255, 255, 255, 0.1));
    opacity: 0;
    transition: opacity 0.4s ease;
    z-index: 1;
}

.cashier-performance-card:hover::before {
    opacity: 1;
}

.cashier-stats-card {
    background: rgba(255, 255, 255, 0.8);
    backdrop-filter: blur(8px);
    -webkit-backdrop-filter: blur(8px);
    border-radius: 1rem;
    padding: 1.25rem;
    border: 1px solid rgba(255, 255, 255, 0.4);
    transition: all 0.3s ease;
    position: relative;
    overflow: hidden;
}

.cashier-stats-card:hover {
    transform: translateY(-5px);
    box-shadow: 0 12px 28px rgba(0, 0, 0, 0.12);
    border: 1px solid rgba(255, 255, 255, 0.6);
}

.cashier-stats-card .rounded-circle {
    width: 3rem;
    height: 3rem;
    display: flex;
    align-items: center;
    justify-content: center;
    background: white;
    box-shadow: 0 4px 12px rgba(0, 0, 0, 0.08);
    transition: all 0.3s ease;
}

.cashier-stats-card:hover .rounded-circle {
    transform: scale(1.1) rotate(10deg);
    box-shadow: 0 6px 16px rgba(0, 0, 0, 0.12);
}

.cashier-stats-card i {
    font-size: 1.25rem;
    transition: all 0.3s ease;
}

.cashier-stats-card:hover i {
    transform: scale(1.1);
}

/* Enhanced Table Styling */
.cashier-table {
    border-collapse: separate;
    border-spacing: 0 0.5rem;
    margin-top: -0.5rem;
}

.cashier-table thead th {
    background: rgba(255, 255, 255, 0.8);
    backdrop-filter: blur(8px);
    -webkit-backdrop-filter: blur(8px);
    border: none;
    padding: 1rem 1.5rem;
    font-weight: 600;
    color: #333;
    text-transform: uppercase;
    font-size: 0.75rem;
    letter-spacing: 0.5px;
}

.cashier-table tbody tr {
    background: rgba(255, 255, 255, 0.7);
    backdrop-filter: blur(8px);
    -webkit-backdrop-filter: blur(8px);
    border-radius: 0.75rem;
    transition: all 0.3s ease;
}

.cashier-table tbody tr:hover {
    transform: translateY(-3px) scale(1.01);
    background: rgba(255, 255, 255, 0.9);
    box-shadow: 0 8px 24px rgba(0, 0, 0, 0.12);
}

.cashier-table td {
    padding: 1rem 1.5rem;
    border: none;
    vertical-align: middle;
}

.cashier-table td:first-child {
    border-top-left-radius: 0.75rem;
    border-bottom-left-radius: 0.75rem;
    font-weight: 500;
}

.cashier-table td:last-child {
    border-top-right-radius: 0.75rem;
    border-bottom-right-radius: 0.75rem;
    text-align: center;
}

.cashier-table td:nth-child(2) {
    font-weight: 600;
    color: #8B4543;
}

.cashier-table td:nth-child(3) {
    font-weight: 500;
    color: #4A7C59;
    text-align: center;
}

/* Quantity Badge */
.qty-badge {
    background: rgba(74, 124, 89, 0.1);
    color: #4A7C59;
    padding: 0.25rem 0.75rem;
    border-radius: 2rem;
    font-size: 0.875rem;
    font-weight: 600;
    display: inline-flex;
    align-items: center;
    justify-content: center;
    min-width: 2.5rem;
    transition: all 0.3s ease;
}

.qty-badge:hover {
    background: rgba(74, 124, 89, 0.2);
    transform: translateY(-2px);
}

/* Item Name */
.item-name {
    display: flex;
    align-items: center;
    gap: 0.75rem;
}

.item-name i {
    color: #8B4543;
    font-size: 1rem;
    opacity: 0.7;
    transition: all 0.3s ease;
}

.cashier-table tr:hover .item-name i {
    opacity: 1;
    transform: scale(1.1);
}

/* Total Amount */
.total-amount {
    color: #8B4543;
    font-weight: 600;
    transition: all 0.3s ease;
}

.cashier-table tr:hover .total-amount {
    transform: scale(1.05);
    text-shadow: 1px 1px 2px rgba(139, 69, 67, 0.1);
}

/* Menu Items Section Styling */
.menu-section {
    padding: 2rem 0;
}

.menu-header {
    display: flex;
    align-items: center;
    gap: 1rem;
    margin-bottom: 2rem;
}

.menu-header i {
    font-size: 1.5rem;
    color: #8B4543;
}

.menu-header h2 {
    font-size: 1.75rem;
    font-weight: 600;
    color: #2D3436;
    margin: 0;
}

.menu-categories {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
    gap: 1.5rem;
    margin-bottom: 3rem;
}

.category-card {
    background: rgba(255, 255, 255, 0.7);
    backdrop-filter: blur(10px);
    -webkit-backdrop-filter: blur(10px);
    border-radius: 1.25rem;
    padding: 1.5rem;
    border: 1px solid rgba(255, 255, 255, 0.4);
    box-shadow: 0 8px 32px rgba(0, 0, 0, 0.1);
    transition: all 0.4s cubic-bezier(0.4, 0, 0.2, 1);
    text-align: center;
    position: relative;
    overflow: hidden;
    cursor: pointer;
}

.category-card::before {
    content: '';
    position: absolute;
    top: 0;
    left: 0;
    right: 0;
    bottom: 0;
    background: linear-gradient(135deg, rgba(255, 255, 255, 0.2), rgba(255, 255, 255, 0.1));
    opacity: 0;
    transition: opacity 0.4s ease;
    z-index: 1;
}

.category-card:hover,
.category-card.active {
    transform: translateY(-10px) scale(1.02);
    box-shadow: 0 15px 45px rgba(0, 0, 0, 0.15);
    border: 1px solid rgba(255, 255, 255, 0.6);
}

.category-card:hover::before,
.category-card.active::before {
    opacity: 1;
}

.category-icon {
    width: 3.5rem;
    height: 3.5rem;
    background: rgba(139, 69, 67, 0.1);
    border-radius: 1rem;
    display: flex;
    align-items: center;
    justify-content: center;
    margin: 0 auto 1rem;
    transition: all 0.4s ease;
}

.category-card:hover .category-icon,
.category-card.active .category-icon {
    transform: scale(1.1) rotate(10deg);
    background: rgba(139, 69, 67, 0.2);
}

.category-icon i {
    font-size: 1.5rem;
    color: #8B4543;
    transition: all 0.4s ease;
}

.category-card:hover .category-icon i,
.category-card.active .category-icon i {
    transform: scale(1.1);
}

.category-name {
    font-size: 1.125rem;
    font-weight: 600;
    color: #2D3436;
    margin-bottom: 0.5rem;
    transition: all 0.3s ease;
}

.category-card:hover .category-name,
.category-card.active .category-name {
    color: #8B4543;
}

.item-count {
    font-size: 0.875rem;
    color: #636E72;
    transition: all 0.3s ease;
}

.category-card:hover .item-count,
.category-card.active .item-count {
    color: #2D3436;
}

/* Menu Items Grid */
.menu-items-grid {
    display: grid;
    grid-template-columns: repeat(auto-fill, minmax(280px, 1fr));
    gap: 2rem;
}

.menu-item-card {
    background: rgba(255, 255, 255, 0.7);
    backdrop-filter: blur(10px);
    -webkit-backdrop-filter: blur(10px);
    border-radius: 1.25rem;
    overflow: hidden;
    border: 1px solid rgba(255, 255, 255, 0.4);
    box-shadow: 0 8px 32px rgba(0, 0, 0, 0.1);
    transition: all 0.4s cubic-bezier(0.4, 0, 0.2, 1);
}

.menu-item-card:hover {
    transform: translateY(-10px);
    box-shadow: 0 15px 45px rgba(0, 0, 0, 0.15);
    border: 1px solid rgba(255, 255, 255, 0.6);
}

.item-image {
    width: 100%;
    height: 200px;
    object-fit: cover;
    border-radius: 1rem 1rem 0 0;
    transition: all 0.4s ease;
}

.menu-item-card:hover .item-image {
    transform: scale(1.05);
}

.item-details {
    padding: 1.5rem;
}

.item-name {
    font-size: 1.25rem;
    font-weight: 600;
    color: #2D3436;
    margin-bottom: 0.5rem;
    transition: all 0.3s ease;
}

.menu-item-card:hover .item-name {
    color: #8B4543;
}

.item-description {
    font-size: 0.875rem;
    color: #636E72;
    margin-bottom: 1rem;
    line-height: 1.5;
}

.item-price {
    font-size: 1.5rem;
    font-weight: 700;
    color: #8B4543;
    margin-bottom: 1rem;
    transition: all 0.3s ease;
}

.menu-item-card:hover .item-price {
    transform: scale(1.05);
    text-shadow: 2px 2px 4px rgba(139, 69, 67, 0.1);
}

.item-actions {
    display: flex;
    align-items: center;
    justify-content: space-between;
    gap: 1rem;
}

.quantity-control {
    display: flex;
    align-items: center;
    gap: 0.5rem;
    background: rgba(139, 69, 67, 0.1);
    padding: 0.5rem;
    border-radius: 0.75rem;
}

.qty-btn {
    width: 2rem;
    height: 2rem;
    display: flex;
    align-items: center;
    justify-content: center;
    border: none;
    background: white;
    border-radius: 0.5rem;
    color: #8B4543;
    font-size: 1rem;
    cursor: pointer;
    transition: all 0.3s ease;
}

.qty-btn:hover {
    background: #8B4543;
    color: white;
    transform: scale(1.1);
}

.qty-input {
    width: 3rem;
    text-align: center;
    border: none;
    background: transparent;
    font-weight: 600;
    color: #2D3436;
}

.add-to-cart-btn {
    flex: 1;
    padding: 0.75rem 1.5rem;
    border: none;
    background: linear-gradient(135deg, #8B4543, #723937);
    color: white;
    border-radius: 0.75rem;
    font-weight: 600;
    cursor: pointer;
    transition: all 0.3s ease;
    display: flex;
    align-items: center;
    justify-content: center;
    gap: 0.5rem;
}

.add-to-cart-btn:hover {
    background: linear-gradient(135deg, #723937, #5E2F2D);
    transform: translateY(-2px);
    box-shadow: 0 5px 15px rgba(139, 69, 67, 0.2);
}

.add-to-cart-btn i {
    font-size: 1.125rem;
    transition: all 0.3s ease;
}

.add-to-cart-btn:hover i {
    transform: scale(1.1);
}

/* Responsive Adjustments */
@media (max-width: 768px) {
    .menu-categories {
        grid-template-columns: repeat(auto-fit, minmax(150px, 1fr));
        gap: 1rem;
    }

    .menu-items-grid {
        grid-template-columns: repeat(auto-fill, minmax(250px, 1fr));
        gap: 1.5rem;
    }

    .item-image {
        height: 180px;
    }

    .item-details {
        padding: 1.25rem;
    }

    .item-name {
        font-size: 1.125rem;
    }

    .item-price {
        font-size: 1.25rem;
    }
}

@media (max-width: 480px) {
    .menu-categories {
        grid-template-columns: repeat(2, 1fr);
    }

    .menu-items-grid {
        grid-template-columns: 1fr;
    }
}
</style>

<script>
let salesTrendChart = null;
let productDistributionChart = null;
let branchPerformanceChart = null;
let inventoryStatusChart = null;

function formatCurrency(value) {
    return '₱' + parseFloat(value).toLocaleString('en-PH', {
        minimumFractionDigits: 2,
        maximumFractionDigits: 2
    });
}

function initializeCharts() {
    // Sales Trend Chart
    const salesTrendCtx = document.getElementById('salesTrendChart').getContext('2d');
    salesTrendChart = new Chart(salesTrendCtx, {
        type: 'line',
        data: {
            labels: [],
            datasets: [{
                label: 'Sales',
                data: [],
                borderColor: '#8B4543',
                backgroundColor: 'rgba(139, 69, 67, 0.1)',
                fill: true,
                tension: 0.4
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: {
                legend: { display: false },
                tooltip: {
                    callbacks: {
                        label: function(context) {
                            return formatCurrency(context.raw);
                        }
                    }
                }
            },
            scales: {
                y: {
                    beginAtZero: true,
                    ticks: {
                        callback: function(value) {
                            return formatCurrency(value);
                        }
                    }
                }
            }
        }
    });

    // Product Distribution Chart
    const productDistributionCtx = document.getElementById('productDistributionChart').getContext('2d');
    productDistributionChart = new Chart(productDistributionCtx, {
        type: 'doughnut',
        data: {
            labels: [],
            datasets: [{
                data: [],
                backgroundColor: [
                    '#8B4543', '#4A7C59', '#C4804D', '#3B7B9E', '#A65D5D'
                ]
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: {
                legend: { position: 'bottom' }
            }
        }
    });

    // Branch Performance Chart
    const branchPerformanceCtx = document.getElementById('branchPerformanceChart').getContext('2d');
    branchPerformanceChart = new Chart(branchPerformanceCtx, {
        type: 'bar',
        data: {
            labels: [],
            datasets: [{
                label: 'Sales',
                data: [],
                backgroundColor: '#8B4543'
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: {
                legend: { display: false },
                tooltip: {
                    callbacks: {
                        label: function(context) {
                            return formatCurrency(context.raw);
                        }
                    }
                }
            },
            scales: {
                y: {
                    beginAtZero: true,
                    ticks: {
                        callback: function(value) {
                            return formatCurrency(value);
                        }
                    }
                }
            }
        }
    });

    // Inventory Status Chart
    const inventoryStatusCtx = document.getElementById('inventoryStatusChart').getContext('2d');
    inventoryStatusChart = new Chart(inventoryStatusCtx, {
        type: 'bar',
        data: {
            labels: [],
            datasets: [
                {
                    label: 'Current Stock',
                    data: [],
                    backgroundColor: '#8B4543'
                },
                {
                    label: 'Minimum Stock',
                    data: [],
                    backgroundColor: '#C4804D'
                }
            ]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: {
                legend: { position: 'bottom' }
            },
            scales: {
                y: { beginAtZero: true }
            }
        }
    });
}

function updateDashboard() {
    // Sales Trend
    const period = $('.period-selector.active').data('period');
    $.get('get_sales_trend.php', { period: period }, function(response) {
        salesTrendChart.data.labels = response.labels;
        salesTrendChart.data.datasets[0].data = response.data;
        salesTrendChart.update();
    });
    // Product Distribution
    $.get('get_product_distribution.php', function(response) {
        productDistributionChart.data.labels = response.labels;
        productDistributionChart.data.datasets[0].data = response.data;
        productDistributionChart.update();
    });
    // Branch Performance
    $.get('get_branch_performance.php', function(response) {
        branchPerformanceChart.data.labels = response.labels;
        branchPerformanceChart.data.datasets[0].data = response.data;
        branchPerformanceChart.update();
    });
    // Inventory Status
    $.get('get_inventory_status.php', function(response) {
        inventoryStatusChart.data.labels = response.labels;
        inventoryStatusChart.data.datasets[0].data = response.current_stock;
        inventoryStatusChart.data.datasets[1].data = response.minimum_stock;
        inventoryStatusChart.update();
    });
}

$(document).ready(function() {
    initializeCharts();
    updateDashboard();
    $('.period-selector').click(function() {
        $('.period-selector').removeClass('active');
        $(this).addClass('active');
        updateDashboard();
    });
    setInterval(updateDashboard, 300000); // Auto-refresh every 5 minutes
});

// ... existing code ...
</script>

<?php include('footer.php'); ?>