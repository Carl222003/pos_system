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
    <h1 class="section-title"><span class="section-icon"><i class="fas fa-chart-line"></i></span>Dashboard Overview</h1>

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
            <div class="sales-trend-card">
                <div class="sales-trend-header d-flex justify-content-between align-items-center">
                    <div>
                        <h5 class="mb-0 sales-trend-title">Sales Trend</h5>
                        <p class="text-muted mb-0 sales-trend-subtitle">Overview of sales performance</p>
                    </div>
                    <div class="btn-group sales-trend-period-group">
                        <button type="button" class="btn btn-sm sales-trend-period period-selector active" data-period="daily">Daily</button>
                        <button type="button" class="btn btn-sm sales-trend-period period-selector" data-period="weekly">Weekly</button>
                        <button type="button" class="btn btn-sm sales-trend-period period-selector" data-period="monthly">Monthly</button>
                    </div>
                </div>
                <div class="sales-trend-body">
                    <canvas id="salesTrendChart" class="sales-trend-canvas" height="320"></canvas>
                </div>
            </div>
        </div>

        <!-- Product Distribution Chart -->
        <div class="col-xl-4 col-lg-5">
          <div class="product-distribution-card">
            <div class="product-distribution-header">
              <h5 class="mb-0 product-distribution-title">Product Distribution</h5>
              <p class="text-muted mb-0 product-distribution-subtitle">Products by category</p>
                </div>
            <div class="product-distribution-body">
              <canvas id="productDistributionChart" class="product-distribution-canvas" height="320"></canvas>
                </div>
            </div>
        </div>
    </div>

    <!-- Branch Performance and Inventory Status -->
    <div class="row">
        <!-- Branch Performance - Made wider -->
        <div class="col-xl-8 col-lg-7">
            <div class="branch-performance-card">
                <div class="branch-performance-header d-flex justify-content-between align-items-center">
                    <div>
                        <h5 class="mb-0 branch-performance-title">Branch Performance</h5>
                        <p class="text-muted mb-0 branch-performance-subtitle">Total sales by branch</p>
                    </div>
                    <button type="button" class="btn btn-sm branch-performance-btn" data-bs-toggle="modal" data-bs-target="#branchComparisonModal">Compare Branches</button>
                </div>
                <div class="branch-performance-body">
                    <canvas id="branchPerformanceChart" class="branch-performance-canvas" height="320"></canvas>
                </div>
            </div>
        </div>

        <!-- Inventory Status - Made narrower -->
        <div class="col-xl-4 col-lg-5">
          <div class="inventory-status-card">
            <div class="inventory-status-header">
              <h5 class="mb-0 inventory-status-title">Inventory Status</h5>
              <p class="text-muted mb-0 inventory-status-subtitle">Low stock alerts</p>
                </div>
            <div class="inventory-status-body">
              <canvas id="inventoryStatusChart" class="inventory-status-canvas" height="320"></canvas>
                </div>
            </div>
        </div>
    </div>

    <!-- Cashier Performance Section - Full width -->
    <div class="row mt-4">
        <div class="col-12">
        <div class="cashier-performance-card-enhanced">
          <div class="cashier-performance-header d-flex justify-content-between align-items-center">
                    <div>
              <h5 class="mb-0 cashier-performance-title">Cashier Performance</h5>
              <p class="text-muted mb-0 cashier-performance-subtitle">Active cashiers and their performance metrics</p>
                    </div>
            <div class="d-flex gap-2 align-items-center">
              <select class="form-select form-select-sm period-select cashier-performance-select" id="cashierPeriod">
                            <option value="today">Today</option>
                            <option value="week">This Week</option>
                            <option value="month">This Month</option>
                        </select>
              <button class="btn btn-sm cashier-performance-refresh" id="refreshCashierStats">
                            <i class="fas fa-sync-alt"></i> Refresh
                        </button>
                    </div>
                </div>
          <div class="cashier-performance-summary-row row g-3 mb-4">
                        <!-- Active Cashiers Card -->
                        <div class="col-md-3">
              <div class="cashier-summary-card">
                                <div class="d-flex justify-content-between align-items-center">
                                    <div>
                                        <h6 class="mb-1">Active Cashiers</h6>
                                        <h4 class="mb-0" id="activeCashiers">0</h4>
                                    </div>
                  <div class="cashier-summary-icon bg-summary-blue">
                    <i class="fas fa-user-clock"></i>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <!-- Total Transactions Card -->
                        <div class="col-md-3">
              <div class="cashier-summary-card">
                                <div class="d-flex justify-content-between align-items-center">
                                    <div>
                                        <h6 class="mb-1">Total Transactions</h6>
                                        <h4 class="mb-0" id="totalTransactions">0</h4>
                                    </div>
                  <div class="cashier-summary-icon bg-summary-green">
                    <i class="fas fa-receipt"></i>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <!-- Average Transaction Time Card -->
                        <div class="col-md-3">
              <div class="cashier-summary-card">
                                <div class="d-flex justify-content-between align-items-center">
                                    <div>
                                        <h6 class="mb-1">Avg. Transaction Time</h6>
                                        <h4 class="mb-0" id="avgTransactionTime">0m</h4>
                                    </div>
                  <div class="cashier-summary-icon bg-summary-yellow">
                    <i class="fas fa-clock"></i>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <!-- Total Sales Card -->
                        <div class="col-md-3">
              <div class="cashier-summary-card">
                                <div class="d-flex justify-content-between align-items-center">
                                    <div>
                                        <h6 class="mb-1">Total Sales</h6>
                                        <h4 class="mb-0" id="totalCashierSales">₱0.00</h4>
                                    </div>
                  <div class="cashier-summary-icon bg-summary-purple">
                    <i class="fas fa-peso-sign"></i>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
          <div class="table-responsive cashier-table-enhanced">
            <table class="table table-hover cashier-performance-table" id="cashierPerformanceTable">
                            <thead>
                                <tr>
                                    <th>Cashier</th>
                                    <th>Branch</th>
                                    <th>Status</th>
                                    <th>Transactions</th>
                                    <th>Sales</th>
                                    <th>Avg. Time</th>
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

<!-- Branch Comparison Modal -->
<div class="modal fade" id="branchComparisonModal" tabindex="-1">
  <div class="modal-dialog modal-xl modal-dialog-centered">
    <div class="modal-content branch-modal-content">
      <div class="modal-header branch-modal-header">
        <h5 class="modal-title branch-modal-title"><i class="fas fa-balance-scale"></i> Branch Comparison</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
      </div>
      <div class="modal-body branch-modal-body">
        <div class="d-flex gap-2 align-items-center mb-4">
          <select class="form-select form-select-sm branch-modal-select" id="periodSelect">
            <option value="daily">Today</option>
            <option value="weekly">This Week</option>
            <option value="monthly">This Month</option>
            <option value="yearly">This Year</option>
            <option value="custom">Custom</option>
          </select>
          <input type="date" id="startDate" class="form-control form-control-sm d-none branch-modal-date">
          <input type="date" id="endDate" class="form-control form-control-sm d-none branch-modal-date">
          <button class="btn btn-sm branch-modal-refresh" id="refreshComparison"><i class="fas fa-sync-alt"></i> Refresh</button>
        </div>
        <div class="branch-modal-card mb-4">
          <div class="table-responsive">
            <table class="table table-hover table-bordered branch-modal-table" id="branchComparisonTable">
              <thead>
                <tr>
                  <th>Branch</th>
                  <th>Total Sales</th>
                  <th>Total Orders</th>
                  <th>Average Sale</th>
                  <th>Active Cashiers</th>
                  <th>Top Products</th>
                </tr>
              </thead>
              <tbody>
                <!-- Data will be populated by JS -->
              </tbody>
            </table>
          </div>
        </div>
        <div class="branch-modal-card" style="width:100%; min-height:480px;">
          <canvas id="branchComparisonChart" style="max-width:100%; height:480px;"></canvas>
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
.cashier-performance-card-enhanced {
  background: linear-gradient(135deg, #fcf8f6 80%, #f7ece8 100%);
    border-radius: 1.25rem;
  box-shadow: 0 4px 24px rgba(139,69,67,0.10);
  padding: 2.2rem 2.2rem 1.5rem 2.2rem;
  margin-bottom: 2rem;
  border: 1px solid #f0e6e3;
}
.cashier-performance-header {
  border-bottom: 1px solid #f0e6e3;
  padding-bottom: 1.1rem;
  margin-bottom: 1.1rem;
}
.cashier-performance-title {
  color: #8B4543;
  font-weight: 700;
  font-size: 1.35rem;
  letter-spacing: 0.5px;
}
.cashier-performance-subtitle {
  font-size: 1.01rem;
  color: #A65D5D;
  font-weight: 500;
}
.cashier-performance-select {
  border-radius: 0.75rem;
  border: 1px solid #e0cfc7;
  font-weight: 500;
  color: #8B4543;
  background: #f7ece8;
  min-width: 120px;
}
.cashier-performance-refresh {
  background: linear-gradient(135deg, #8B4543 80%, #A65D5D 100%);
  color: #fff;
  box-shadow: 0 2px 8px rgba(139,69,67,0.08);
  border-radius: 0.75rem;
  font-weight: 600;
  letter-spacing: 0.5px;
  padding: 0.45rem 1.2rem;
  transition: background 0.2s;
  border: none;
}
.cashier-performance-refresh:hover {
  background: linear-gradient(135deg, #A65D5D 80%, #8B4543 100%);
}
.cashier-performance-summary-row {
  margin-bottom: 2rem;
}
.cashier-summary-card {
  background: #fff;
  border-radius: 1.25rem;
  box-shadow: 0 2px 16px rgba(139,69,67,0.07);
  padding: 1.5rem 1.5rem 1.2rem 1.5rem;
  border: 1px solid #f0e6e3;
  display: flex;
  align-items: center;
  justify-content: space-between;
  min-height: 110px;
}
.cashier-summary-icon {
  width: 2.8rem;
  height: 2.8rem;
  border-radius: 50%;
    display: flex;
    align-items: center;
    justify-content: center;
  font-size: 1.35rem;
  background: #f7ece8;
  box-shadow: 0 2px 8px rgba(139,69,67,0.08);
}
.bg-summary-blue { color: #2196F3; background: #e3f0fa !important; }
.bg-summary-green { color: #4CAF50; background: #eafae3 !important; }
.bg-summary-yellow { color: #FFC107; background: #fff8e1 !important; }
.bg-summary-purple { color: #9C27B0; background: #f3e6fa !important; }
.cashier-table-enhanced {
  margin-top: 1.5rem;
}
.cashier-performance-table {
  border-radius: 1rem;
  overflow: hidden;
  font-size: 1.05rem;
  color: #5E2F2D;
  background: #fff;
}
.cashier-performance-table thead {
  background: linear-gradient(90deg, #f7ece8 80%, #fcf8f6 100%);
  color: #8B4543;
  font-weight: 700;
  font-size: 1.08rem;
    letter-spacing: 0.5px;
}
.cashier-performance-table tbody tr {
  transition: background 0.18s;
}
.cashier-performance-table tbody tr:hover {
  background: #f7ece8;
  color: #8B4543;
}
.cashier-performance-table th, .cashier-performance-table td {
    vertical-align: middle;
  padding: 0.85rem 1.1rem;
  border: none;
}
.cashier-performance-table td {
    font-weight: 500;
}
.cashier-performance-table td:first-child {
  font-weight: 700;
    color: #8B4543;
}
.cashier-performance-table td span {
    font-weight: 500;
}
@media (max-width: 1200px) {
  .cashier-performance-card-enhanced { padding: 1.5rem 0.5rem; }
  .cashier-summary-card { padding: 1rem 0.5rem; }
}
.branch-modal-content {
  border-radius: 1.5rem;
  box-shadow: 0 8px 40px rgba(139,69,67,0.18), 0 1.5px 8px rgba(139,69,67,0.10);
  background: linear-gradient(135deg, #fcf8f6 80%, #f7ece8 100%);
  font-family: 'Segoe UI', 'Roboto', Arial, sans-serif;
}
.branch-modal-header {
  border-bottom: 1px solid #f0e6e3;
  background: transparent;
  border-top-left-radius: 1.5rem;
  border-top-right-radius: 1.5rem;
}
.branch-modal-title {
    color: #8B4543;
  font-weight: 700;
  letter-spacing: 0.5px;
  font-size: 1.45rem;
}
.branch-modal-body {
  padding: 2.5rem 3rem 2.5rem 3rem;
  background: transparent;
}
.branch-modal-select, .branch-modal-date {
  border-radius: 0.75rem;
  border: 1px solid #e0cfc7;
  font-weight: 500;
    color: #8B4543;
  background: #f7ece8;
  min-width: 120px;
}
.branch-modal-refresh {
  background: linear-gradient(135deg, #8B4543 80%, #A65D5D 100%);
  color: #fff;
  box-shadow: 0 2px 8px rgba(139,69,67,0.08);
  border-radius: 0.75rem;
    font-weight: 600;
  letter-spacing: 0.5px;
  padding: 0.45rem 1.2rem;
  transition: background 0.2s;
}
.branch-modal-refresh:hover {
  background: linear-gradient(135deg, #A65D5D 80%, #8B4543 100%);
}
.branch-modal-card {
  background: #fff;
  border-radius: 1.25rem;
  box-shadow: 0 2px 16px rgba(139,69,67,0.07);
  padding: 2rem 2rem 1.5rem 2rem;
  margin-bottom: 1.5rem;
  border: 1px solid #f0e6e3;
}
.branch-modal-table {
  border-radius: 1rem;
  overflow: hidden;
  font-size: 1.05rem;
  color: #5E2F2D;
  background: #fff;
}
.branch-modal-table thead {
  background: linear-gradient(90deg, #f7ece8 80%, #fcf8f6 100%);
    color: #8B4543;
  font-weight: 700;
  font-size: 1.08rem;
  letter-spacing: 0.5px;
}
.branch-modal-table tbody tr {
  transition: background 0.18s;
}
.branch-modal-table tbody tr:hover {
  background: #f7ece8;
  color: #8B4543;
}
.branch-modal-table th, .branch-modal-table td {
  vertical-align: middle;
  padding: 0.85rem 1.1rem;
  border: none;
}
.branch-modal-table td {
  font-weight: 500;
}
.branch-modal-table td:first-child {
  font-weight: 700;
  color: #8B4543;
}
.branch-modal-table td span {
  font-weight: 500;
}
.branch-modal-card canvas {
    border-radius: 1rem;
  background: #fcf8f6;
  box-shadow: 0 2px 12px rgba(139,69,67,0.06);
  border: 1px solid #f0e6e3;
}
@media (max-width: 1200px) {
  .branch-modal-body { padding: 1.5rem 0.5rem; }
  .branch-modal-card { padding: 1rem 0.5rem; }
}
.sales-trend-card {
  background: linear-gradient(135deg, #fcf8f6 80%, #f7ece8 100%);
  border-radius: 1.25rem;
  box-shadow: 0 4px 24px rgba(139,69,67,0.10);
  padding: 2.2rem 2.2rem 1.5rem 2.2rem;
  margin-bottom: 2rem;
  border: 1px solid #f0e6e3;
}
.sales-trend-header {
  border-bottom: 1px solid #f0e6e3;
  padding-bottom: 1.1rem;
  margin-bottom: 1.1rem;
}
.sales-trend-title {
    color: #8B4543;
  font-weight: 700;
  font-size: 1.35rem;
  letter-spacing: 0.5px;
}
.sales-trend-subtitle {
  font-size: 1.01rem;
  color: #A65D5D;
  font-weight: 500;
}
.sales-trend-period-group .sales-trend-period {
  background: #f7ece8;
  color: #8B4543;
  border: none;
    font-weight: 600;
  border-radius: 0.75rem !important;
  margin-left: 0.25rem;
  margin-right: 0.25rem;
  transition: background 0.18s, color 0.18s;
  box-shadow: 0 1px 4px rgba(139,69,67,0.06);
}
.sales-trend-period-group .sales-trend-period.active,
.sales-trend-period-group .sales-trend-period:focus,
.sales-trend-period-group .sales-trend-period:hover {
  background: linear-gradient(135deg, #8B4543 80%, #A65D5D 100%);
  color: #fff;
}
.sales-trend-body {
  padding-top: 1.2rem;
}
.sales-trend-canvas {
  background: #fff;
  border-radius: 1rem;
  box-shadow: 0 2px 12px rgba(139,69,67,0.06);
  border: 1px solid #f0e6e3;
  padding: 0.5rem;
}
.branch-performance-card {
  background: linear-gradient(135deg, #fcf8f6 80%, #f7ece8 100%);
    border-radius: 1.25rem;
  box-shadow: 0 4px 24px rgba(139,69,67,0.10);
  padding: 2.2rem 2.2rem 1.5rem 2.2rem;
  margin-bottom: 2rem;
  border: 1px solid #f0e6e3;
}
.branch-performance-header {
  border-bottom: 1px solid #f0e6e3;
  padding-bottom: 1.1rem;
  margin-bottom: 1.1rem;
}
.branch-performance-title {
  color: #8B4543;
  font-weight: 700;
  font-size: 1.35rem;
  letter-spacing: 0.5px;
}
.branch-performance-subtitle {
  font-size: 1.01rem;
  color: #A65D5D;
  font-weight: 500;
}
.branch-performance-btn {
  background: linear-gradient(135deg, #8B4543 80%, #A65D5D 100%);
  color: #fff;
  box-shadow: 0 2px 8px rgba(139,69,67,0.08);
  border-radius: 0.75rem;
    font-weight: 600;
  letter-spacing: 0.5px;
  padding: 0.45rem 1.2rem;
  transition: background 0.2s;
  border: none;
}
.branch-performance-btn:hover {
  background: linear-gradient(135deg, #A65D5D 80%, #8B4543 100%);
}
.branch-performance-body {
  padding-top: 1.2rem;
}
.branch-performance-canvas {
  background: #fff;
  border-radius: 1rem;
  box-shadow: 0 2px 12px rgba(139,69,67,0.06);
  border: 1px solid #f0e6e3;
  padding: 0.5rem;
}
.product-distribution-card {
  background: linear-gradient(135deg, #fcf8f6 80%, #f7ece8 100%);
  border-radius: 1.25rem;
  box-shadow: 0 4px 24px rgba(139,69,67,0.10);
  padding: 2.2rem 2.2rem 1.5rem 2.2rem;
  margin-bottom: 2rem;
  border: 1px solid #f0e6e3;
}
.product-distribution-header {
  border-bottom: 1px solid #f0e6e3;
  padding-bottom: 1.1rem;
  margin-bottom: 1.1rem;
}
.product-distribution-title {
    color: #8B4543;
  font-weight: 700;
  font-size: 1.35rem;
  letter-spacing: 0.5px;
}
.product-distribution-subtitle {
  font-size: 1.01rem;
  color: #A65D5D;
  font-weight: 500;
}
.product-distribution-body {
  padding-top: 1.2rem;
}
.product-distribution-canvas {
  background: #fff;
  border-radius: 1rem;
  box-shadow: 0 2px 12px rgba(139,69,67,0.06);
  border: 1px solid #f0e6e3;
    padding: 0.5rem;
}
/* Chart.js legend enhancements */
#productDistributionChart + div,
.product-distribution-card .chartjs-legend {
  display: flex !important;
    justify-content: center;
  gap: 2rem;
  margin-top: 1.2rem;
  font-size: 1.05rem;
  font-weight: 600;
    color: #8B4543;
}
#productDistributionChart + div li,
.product-distribution-card .chartjs-legend li {
    display: flex;
    align-items: center;
    gap: 0.5rem;
}
#productDistributionChart + div span,
.product-distribution-card .chartjs-legend span {
  display: inline-block;
  width: 18px;
  height: 18px;
  border-radius: 4px;
  margin-right: 0.5rem;
}
.inventory-status-card {
  background: linear-gradient(135deg, #fcf8f6 80%, #f7ece8 100%);
  border-radius: 1.25rem;
  box-shadow: 0 4px 24px rgba(139,69,67,0.10);
  padding: 2.2rem 2.2rem 1.5rem 2.2rem;
  margin-bottom: 2rem;
  border: 1px solid #f0e6e3;
}
.inventory-status-header {
  border-bottom: 1px solid #f0e6e3;
  padding-bottom: 1.1rem;
  margin-bottom: 1.1rem;
}
.inventory-status-title {
  color: #8B4543;
  font-weight: 700;
  font-size: 1.35rem;
  letter-spacing: 0.5px;
}
.inventory-status-subtitle {
  font-size: 1.01rem;
  color: #A65D5D;
  font-weight: 500;
}
.inventory-status-body {
  padding-top: 1.2rem;
}
.inventory-status-canvas {
  background: #fff;
  border-radius: 1rem;
  box-shadow: 0 2px 12px rgba(139,69,67,0.06);
  border: 1px solid #f0e6e3;
  padding: 0.5rem;
}
/* Chart.js legend enhancements */
#inventoryStatusChart + div,
.inventory-status-card .chartjs-legend {
  display: flex !important;
  justify-content: center;
  gap: 2rem;
  margin-top: 1.2rem;
  font-size: 1.05rem;
  font-weight: 600;
  color: #8B4543;
}
#inventoryStatusChart + div li,
.inventory-status-card .chartjs-legend li {
  display: flex;
  align-items: center;
  gap: 0.5rem;
}
#inventoryStatusChart + div span,
.inventory-status-card .chartjs-legend span {
  display: inline-block;
  width: 18px;
  height: 18px;
  border-radius: 4px;
  margin-right: 0.5rem;
}
.section-title {
    color: #8B4543;
    font-size: 2.2rem;
    font-weight: 700;
    letter-spacing: 0.7px;
    margin-bottom: 1.7rem;
    margin-top: 1.2rem;
    display: flex;
    align-items: center;
    gap: 0.7rem;
    position: relative;
    background: none;
    border: none;
    animation: fadeInDown 0.7s;
}
.section-title .section-icon {
    font-size: 1.5em;
    color: #8B4543;
    opacity: 0.92;
}
.section-title::after {
    content: '';
    display: block;
    position: absolute;
    left: 0;
    bottom: -7px;
    width: 100%;
    height: 5px;
    border-radius: 3px;
    background: linear-gradient(90deg, #8B4543 0%, #b97a6a 100%);
    opacity: 0.18;
}
@keyframes fadeInDown {
    from { opacity: 0; transform: translateY(-18px); }
    to { opacity: 1; transform: translateY(0); }
}
</style>

<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
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
                tension: 0.4,
                borderWidth: 3,
                pointRadius: 5,
                pointBackgroundColor: '#A65D5D',
                pointBorderColor: '#fff',
                pointHoverRadius: 7
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
                legend: {
                    position: 'bottom',
                    labels: {
                        boxWidth: 18,
                        boxHeight: 18,
                        borderRadius: 4,
                        color: '#8B4543',
                        font: { weight: 600, size: 15 }
                    }
                }
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
                legend: {
                    position: 'bottom',
                    labels: {
                        boxWidth: 18,
                        boxHeight: 18,
                        borderRadius: 4,
                        color: '#8B4543',
                        font: { weight: 600, size: 15 }
                    }
                }
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

function updateCashierPerformance() {
    const period = $('#cashierPeriod').val() || 'today';
    $.get('get_cashier_performance.php', { period: period }, function(data) {
        if (data.success) {
            // Update summary cards
            $('#activeCashiers').text(data.active_cashiers);
            $('#totalTransactions').text(data.total_transactions);
            $('#avgTransactionTime').text(data.avg_transaction_time);
            $('#totalCashierSales').text(formatCurrency(data.total_sales));

            // Update table
        const tbody = $('#cashierPerformanceTable tbody');
        tbody.empty();
            if (data.cashiers && data.cashiers.length > 0) {
                data.cashiers.forEach(cashier => {
                tbody.append(`
                    <tr>
                        <td>
                            <div class="item-name d-flex align-items-center">
                                <img src="${cashier.profile_image}" class="rounded-circle me-2" style="width: 32px; height: 32px;">
                                ${cashier.name}
                            </div>
                        </td>
                        <td>${cashier.branch || '-'}</td>
                        <td><span class="badge ${cashier.is_active ? 'bg-success' : 'bg-secondary'}">${cashier.is_active ? 'Active' : 'Inactive'}</span></td>
                        <td>${cashier.transactions}</td>
                            <td>${formatCurrency(cashier.sales)}</td>
                        <td>${cashier.avg_time}</td>
                    </tr>
                `);
            });
        } else {
            tbody.append('<tr><td colspan="6" class="text-center text-muted">No cashiers found for this period.</td></tr>');
        }
        }
    }, 'json');
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
    updateCashierPerformance();
    $('#cashierPeriod').change(updateCashierPerformance);
    $('#refreshCashierStats').click(updateCashierPerformance);
    setInterval(updateCashierPerformance, 300000); // Auto-refresh every 5 minutes
    });

let branchComparisonChart = null;
function getBarColors(count) {
    // Use a palette of visually distinct colors
    const palette = [
        '#8B4543', '#4A7C59', '#C4804D', '#3B7B9E', '#A65D5D',
        '#FFB347', '#6A5ACD', '#20B2AA', '#FF6F61', '#009688'
    ];
    let colors = [];
    for (let i = 0; i < count; i++) {
        colors.push(palette[i % palette.length]);
    }
    return colors;
}
function fetchBranchComparison() {
    const period = document.getElementById('periodSelect').value;
    let url = 'get_branch_comparison.php?period=' + period;
    if (period === 'custom') {
        const start = document.getElementById('startDate').value;
        const end = document.getElementById('endDate').value;
        if (start && end) {
            url += '&start_date=' + start + '&end_date=' + end;
        }
    }
    fetch(url)
        .then(res => res.json())
        .then(res => {
            if (res.success) {
                populateComparisonTable(res.data);
                populateComparisonChart(res.data);
            } else {
                populateComparisonTable([]);
                populateComparisonChart([]);
            }
        })
        .catch(() => {
            populateComparisonTable([]);
            populateComparisonChart([]);
        });
}
function populateComparisonTable(data) {
    const tbody = document.querySelector('#branchComparisonTable tbody');
    tbody.innerHTML = '';
    if (!data || data.length === 0) {
        tbody.innerHTML = '<tr><td colspan="6" class="text-center text-muted">No data available for this period.</td></tr>';
        return;
    }
    data.forEach(branch => {
        const topProducts = branch.top_products.map(p => `<span style='color:#8B4543;'>${p.product_name}</span> <span style='color:#bfa08e;'>(${p.total_quantity})</span>`).join(', ');
        tbody.innerHTML += `
            <tr>
                <td style="font-weight:600; color:#8B4543;">${branch.branch_name}</td>
                <td>${formatCurrency(branch.total_sales)}</td>
                <td>${branch.total_orders}</td>
                <td>${formatCurrency(branch.average_sale)}</td>
                <td>${branch.active_cashiers}</td>
                <td>${topProducts}</td>
            </tr>
        `;
    });
}
function populateComparisonChart(data) {
    const labels = data.map(b => b.branch_name);
    const sales = data.map(b => b.total_sales);
    const colors = getBarColors(labels.length);
    if (branchComparisonChart) branchComparisonChart.destroy();
    const ctx = document.getElementById('branchComparisonChart').getContext('2d');
    branchComparisonChart = new Chart(ctx, {
        type: 'bar',
        data: {
            labels: labels,
            datasets: [{
                label: 'Total Sales',
                data: sales,
                backgroundColor: colors,
                borderRadius: 8
            }]
        },
        options: {
            indexAxis: 'y', // Make the bar chart horizontal (landscape)
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
                x: {
                    beginAtZero: true,
                    grid: { color: '#f0e6e3' },
                    ticks: {
                        color: '#8B4543',
                        callback: function(value) {
                            return formatCurrency(value);
                        },
                        font: { weight: 600 }
                    }
                },
                y: {
                    grid: { display: false },
                    ticks: { color: '#8B4543', font: { weight: 600 } }
                }
            }
        }
    });
}
document.getElementById('refreshComparison').addEventListener('click', fetchBranchComparison);
document.getElementById('periodSelect').addEventListener('change', function() {
    const period = this.value;
    document.getElementById('startDate').classList.toggle('d-none', period !== 'custom');
    document.getElementById('endDate').classList.toggle('d-none', period !== 'custom');
    fetchBranchComparison();
});
document.getElementById('startDate').addEventListener('change', fetchBranchComparison);
document.getElementById('endDate').addEventListener('change', fetchBranchComparison);
// Load data when modal is shown
const branchComparisonModal = document.getElementById('branchComparisonModal');
branchComparisonModal.addEventListener('shown.bs.modal', fetchBranchComparison);
</script>

<!-- Cashier Profile Modal -->
<div class="modal fade" id="cashierProfileModal" tabindex="-1">
  <div class="modal-dialog modal-md modal-dialog-centered">
    <div class="modal-content profile-modal-content">
      <div class="modal-header profile-modal-header">
        <h5 class="modal-title profile-modal-title"><i class="fas fa-user"></i> Cashier Profile</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
      </div>
      <div class="modal-body profile-modal-body">
        <div id="cashierProfileContent" class="text-center">
          <!-- Profile details will be loaded here -->
          <div class="spinner-border text-secondary" role="status" id="profileLoadingSpinner">
            <span class="visually-hidden">Loading...</span>
          </div>
        </div>
      </div>
    </div>
  </div>
</div>
<style>
.profile-modal-content {
  border-radius: 1.25rem;
  box-shadow: 0 8px 32px rgba(139,69,67,0.14);
  background: linear-gradient(135deg, #fcf8f6 80%, #f7ece8 100%);
  font-family: 'Segoe UI', 'Roboto', Arial, sans-serif;
}
.profile-modal-header {
  border-bottom: 1px solid #f0e6e3;
  background: transparent;
  border-top-left-radius: 1.25rem;
  border-top-right-radius: 1.25rem;
}
.profile-modal-title {
  color: #8B4543;
  font-weight: 700;
  letter-spacing: 0.5px;
  font-size: 1.25rem;
}
.profile-modal-body {
  padding: 2rem 1.5rem 1.5rem 1.5rem;
  background: transparent;
}
.profile-avatar {
  width: 90px;
  height: 90px;
  border-radius: 50%;
  object-fit: cover;
  margin-bottom: 1rem;
  border: 3px solid #f0e6e3;
  box-shadow: 0 2px 8px rgba(139,69,67,0.08);
}
.profile-info-label {
  color: #A65D5D;
  font-weight: 600;
  font-size: 1.01rem;
  margin-bottom: 0.2rem;
}
.profile-info-value {
  color: #8B4543;
  font-weight: 700;
  font-size: 1.12rem;
  margin-bottom: 0.7rem;
}
</style>
<script>
// Make cashier names clickable and show profile modal
$(document).on('click', '.cashier-profile-link', function(e) {
  e.preventDefault();
  const userId = $(this).data('userid');
  $('#cashierProfileModal').modal('show');
  $('#cashierProfileContent').html('<div class="spinner-border text-secondary" role="status"><span class="visually-hidden">Loading...</span></div>');
  $.get('get_user_details.php', { user_id: userId }, function(data) {
    if (data && data.success) {
      const user = data.user;
      let html = `<img src="${user.profile_image || 'assets/img/default-profile.png'}" class="profile-avatar mb-2" alt="Profile">
        <div class="profile-info-label">Name</div>
        <div class="profile-info-value">${user.name}</div>
        <div class="profile-info-label">Username</div>
        <div class="profile-info-value">${user.username}</div>
        <div class="profile-info-label">Branch</div>
        <div class="profile-info-value">${user.branch || '-'}</div>
        <div class="profile-info-label">Status</div>
        <div class="profile-info-value">${user.user_status}</div>
        <div class="profile-info-label">User Type</div>
        <div class="profile-info-value">${user.user_type}</div>`;
      $('#cashierProfileContent').html(html);
    } else {
      $('#cashierProfileContent').html('<div class="text-danger">Failed to load profile details.</div>');
    }
  }, 'json');
});
// Enhance cashier name rendering in the table
function renderCashierName(name, profileImage, userId) {
  return `<a href="#" class="cashier-profile-link" data-userid="${userId}" style="color:#8B4543; font-weight:600; text-decoration:none; display:flex; align-items:center; gap:0.5rem;"><img src="${profileImage || 'assets/img/default-profile.png'}" style="width:32px; height:32px; border-radius:50%; object-fit:cover;">${name}</a>`;
}
// In the JS that populates the cashier table, use renderCashierName for the cashier column.
</script>

<?php include('footer.php'); ?>