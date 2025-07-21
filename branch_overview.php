<?php
require_once 'db_connect.php';
require_once 'auth_function.php';

checkAdminLogin();

// Get all active branches
$stmt = $pdo->query("SELECT branch_id, branch_name, branch_code FROM pos_branch WHERE status = 'Active' ORDER BY branch_name");
$branches = $stmt->fetchAll(PDO::FETCH_ASSOC);

include('header.php');
?>

<!-- Main Content -->
<div class="container-fluid px-4">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h1 class="section-title"><span class="section-icon"><i class="fas fa-eye"></i></span>Branch Overview</h1>
        <div class="d-flex gap-2">
            <button class="btn btn-primary" id="refreshStats">
                <i class="fas fa-sync-alt me-2"></i>Refresh Stats
            </button>
            <button class="btn btn-success" data-bs-toggle="modal" data-bs-target="#branchComparisonModal">
                <i class="fas fa-chart-bar me-2"></i>Compare Branches
            </button>
        </div>
    </div>

    <div class="row" id="branchCards">
        <?php foreach ($branches as $branch): ?>
        <div class="col-xl-4 col-md-6 mb-4">
            <div class="card branch-card h-100" data-branch-id="<?php echo $branch['branch_id']; ?>">
                <div class="card-header">
                    <div class="d-flex justify-content-between align-items-center">
                        <h5 class="mb-0"><?php echo htmlspecialchars($branch['branch_name']); ?></h5>
                        <span class="badge bg-info"><?php echo htmlspecialchars($branch['branch_code']); ?></span>
                    </div>
                </div>
                <div class="card-body">
                    <!-- Sales Summary -->
                    <div class="sales-summary mb-4">
                        <h6 class="text-muted mb-3">Today's Performance</h6>
                        <div class="operating-status mb-2" id="status-<?php echo $branch['branch_id']; ?>">
                            <span class="badge bg-secondary">Checking status...</span>
                        </div>
                        <div class="active-cashiers mb-2" id="cashiers-<?php echo $branch['branch_id']; ?>">
                            <small class="text-muted">Active Cashiers: </small>
                            <span class="cashier-list">Loading...</span>
                        </div>
                        <div class="row g-3">
                            <div class="col-6">
                                <div class="stat-card bg-primary text-white p-3 rounded">
                                    <div class="stat-label">Sales</div>
                                    <div class="stat-value" id="sales-<?php echo $branch['branch_id']; ?>">₱0.00</div>
                                </div>
                            </div>
                            <div class="col-6">
                                <div class="stat-card bg-success text-white p-3 rounded">
                                    <div class="stat-label">Orders</div>
                                    <div class="stat-value" id="orders-<?php echo $branch['branch_id']; ?>">0</div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Inventory Alerts -->
                    <div class="inventory-alerts">
                        <h6 class="text-muted mb-3">Inventory Status</h6>
                        <div class="row g-3">
                            <div class="col-6">
                                <div class="alert alert-warning mb-0">
                                    <div class="d-flex align-items-center">
                                        <i class="fas fa-exclamation-triangle me-2"></i>
                                        <div>
                                            <div class="alert-label">Low Stock</div>
                                            <div class="alert-value" id="lowstock-<?php echo $branch['branch_id']; ?>">0</div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <div class="col-6">
                                <div class="alert alert-danger mb-0">
                                    <div class="d-flex align-items-center">
                                        <i class="fas fa-clock me-2"></i>
                                        <div>
                                            <div class="alert-label">Expiring</div>
                                            <div class="alert-value" id="expiring-<?php echo $branch['branch_id']; ?>">0</div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="card-footer bg-transparent border-top">
                    <div class="d-flex justify-content-between align-items-center">
                        <button class="btn btn-primary btn-sm view-sales-btn" data-bs-toggle="modal" data-bs-target="#salesModal" data-branch-id="<?php echo $branch['branch_id']; ?>" data-branch-name="<?php echo htmlspecialchars($branch['branch_name']); ?>">
                            <i class="fas fa-chart-line me-1"></i> View Sales
                        </button>
                        <button class="btn btn-secondary btn-sm" onclick="window.location.href='branch_inventory.php?id=<?php echo $branch['branch_id']; ?>'">
                            <i class="fas fa-boxes me-1"></i> View Inventory
                        </button>
                    </div>
                </div>
            </div>
        </div>
        <?php endforeach; ?>
    </div>

    <!-- Sales Trend Chart -->
    <div class="row mb-4">
        <div class="col-12">
            <div class="card">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h5 class="mb-0">Sales Comparison</h5>
                    <select id="trendPeriod" class="form-select form-select-sm" style="width: auto;">
                        <option value="daily">Last 7 Days</option>
                        <option value="weekly">Last 4 Weeks</option>
                        <option value="monthly">Last 6 Months</option>
                    </select>
                </div>
                <div class="card-body">
                    <canvas id="salesComparisonChart" height="300"></canvas>
                </div>
            </div>
        </div>
    </div>

    <!-- Branch Comparison Section -->
    <div class="row mb-4">
        <div class="col-12">
            <div class="card">
                <div class="card-header">
                    <div class="d-flex justify-content-between align-items-center flex-wrap gap-2">
                        <h5 class="mb-0">Branch Performance Comparison</h5>
                        <div class="d-flex gap-2 align-items-center">
                            <div class="date-filter d-flex gap-2">
                                <input type="date" id="startDate" class="form-control form-control-sm">
                                <input type="date" id="endDate" class="form-control form-control-sm">
                            </div>
                            <select id="comparisonPeriod" class="form-select form-select-sm" style="width: auto;">
                                <option value="custom">Custom Range</option>
                                <option value="daily" selected>Today</option>
                                <option value="weekly">This Week</option>
                                <option value="monthly">This Month</option>
                                <option value="yearly">This Year</option>
                            </select>
                            <button id="applyFilter" class="btn btn-primary btn-sm">
                                <i class="fas fa-filter me-1"></i> Apply Filter
                            </button>
                        </div>
                    </div>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-hover" id="branchComparisonTable">
                            <thead>
                                <tr>
                                    <th>Rank</th>
                                    <th>Branch</th>
                                    <th>Status</th>
                                    <th class="text-end">Total Orders</th>
                                    <th class="text-end">Total Sales</th>
                                    <th class="text-end">Average Sale</th>
                                    <th>Top Products</th>
                                </tr>
                            </thead>
                            <tbody>
                                <!-- Comparison data will be loaded here -->
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Sales Modal -->
<div class="modal fade" id="salesModal" tabindex="-1">
    <div class="modal-dialog modal-xl">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Branch Sales</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <div class="row mb-4">
                    <div class="col-md-3">
                        <div class="stat-card bg-gradient-primary">
                            <div class="stat-card-inner">
                                <div class="stat-icon">
                                    <i class="fas fa-shopping-cart"></i>
                                </div>
                                <div class="stat-content">
                                    <h4 class="stat-value" id="modalTotalOrders">0</h4>
                                    <div class="stat-label">Total Orders Today</div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="stat-card bg-gradient-success">
                            <div class="stat-card-inner">
                                <div class="stat-icon">
                                    <i class="fas fa-peso-sign"></i>
                                </div>
                                <div class="stat-content">
                                    <h4 class="stat-value" id="modalTotalSales">₱0.00</h4>
                                    <div class="stat-label">Total Sales Today</div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="stat-card bg-gradient-warning">
                            <div class="stat-card-inner">
                                <div class="stat-icon">
                                    <i class="fas fa-chart-line"></i>
                                </div>
                                <div class="stat-content">
                                    <h4 class="stat-value" id="modalAverageSale">₱0.00</h4>
                                    <div class="stat-label">Average Sale Today</div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="stat-card bg-gradient-info">
                            <div class="stat-card-inner">
                                <div class="stat-icon">
                                    <i class="fas fa-trophy"></i>
                                </div>
                                <div class="stat-content">
                                    <h4 class="stat-value" id="modalHighestSale">₱0.00</h4>
                                    <div class="stat-label">Highest Sale Today</div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="row">
                    <div class="col-md-8">
                        <div class="card mb-4">
                            <div class="card-header d-flex justify-content-between align-items-center">
                                <h6 class="mb-0">Sales Trend</h6>
                                <select class="form-select form-select-sm w-auto" id="salesTrendPeriod">
                                    <option value="today">Today</option>
                                    <option value="week">This Week</option>
                                    <option value="month">This Month</option>
                                </select>
                            </div>
                            <div class="card-body">
                                <canvas id="salesTrendChart" height="300"></canvas>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="card mb-4">
                            <div class="card-header">
                                <h6 class="mb-0">Payment Methods</h6>
                            </div>
                            <div class="card-body">
                                <canvas id="paymentMethodsChart" height="300"></canvas>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<style>
:root {
    --primary-color: #8B4543;
    --primary-dark: #723937;
    --primary-light: #A65D5D;
    --success-color: #4A7C59;
    --warning-color: #C4804D;
    --info-color: #36b9cc;
    --danger-color: #B33A3A;
    --text-light: #F3E9E7;
    --text-dark: #3C2A2A;
    --border-radius: 15px;
    --card-shadow: 0 4px 20px rgba(168, 102, 102, 0.1);
    --hover-shadow: 0 8px 30px rgba(168, 102, 102, 0.15);
    --transition-speed: 0.3s;
}

/* Branch Card Styles */
.branch-card {
    background: #fff;
    border-radius: var(--border-radius);
    box-shadow: var(--card-shadow);
    transition: all var(--transition-speed) cubic-bezier(0.4, 0, 0.2, 1);
    border: 1px solid rgba(168, 102, 102, 0.1);
    overflow: hidden;
    margin-bottom: 1.5rem;
}

.branch-card:hover {
    transform: translateY(-5px);
    box-shadow: var(--hover-shadow);
}

.branch-header {
    padding: 1.5rem;
    background: linear-gradient(135deg, var(--primary-color) 0%, var(--primary-dark) 100%);
    color: white;
}

.branch-title {
    font-size: 1.25rem;
    font-weight: 600;
    margin-bottom: 0.5rem;
    display: flex;
    align-items: center;
    gap: 0.75rem;
}

.branch-status {
    display: inline-flex;
    align-items: center;
    gap: 0.5rem;
    padding: 0.5rem 1rem;
    border-radius: 50px;
    font-size: 0.875rem;
    font-weight: 500;
    margin-top: 0.5rem;
}

.branch-status.operating {
    background: rgba(74, 124, 89, 0.2);
    color: #4A7C59;
}

.branch-status.closed {
    background: rgba(179, 58, 58, 0.2);
    color: #B33A3A;
}

.branch-body {
    padding: 1.5rem;
}

.branch-stats {
    display: grid;
    grid-template-columns: repeat(2, 1fr);
    gap: 1rem;
    margin-bottom: 1.5rem;
}

.stat-item {
    padding: 1rem;
    background: rgba(168, 102, 102, 0.05);
    border-radius: 12px;
    text-align: center;
}

.stat-value {
    font-size: 1.5rem;
    font-weight: 700;
    color: var(--primary-color);
    margin-bottom: 0.25rem;
}

.stat-label {
    font-size: 0.875rem;
    color: var(--text-dark);
    opacity: 0.8;
}

.branch-footer {
    padding: 1rem 1.5rem;
    background: rgba(168, 102, 102, 0.05);
    border-top: 1px solid rgba(168, 102, 102, 0.1);
    display: flex;
    justify-content: space-between;
    align-items: center;
}

.cashier-info {
    font-size: 0.875rem;
    color: var(--text-dark);
}

.branch-actions .btn {
    padding: 0.5rem 1rem;
    border-radius: 8px;
    font-weight: 500;
    transition: all var(--transition-speed) ease;
}

.btn-primary {
    background: var(--primary-color);
    border-color: var(--primary-color);
}

.btn-primary:hover {
    background: var(--primary-dark);
    border-color: var(--primary-dark);
    transform: translateY(-2px);
}

.btn-success {
    background: var(--success-color);
    border-color: var(--success-color);
}

.btn-success:hover {
    background: darken(var(--success-color), 10%);
    border-color: darken(var(--success-color), 10%);
    transform: translateY(-2px);
}

/* Modal Styles */
.modal-content {
    border: none;
    border-radius: var(--border-radius);
    overflow: hidden;
}

.modal-header {
    background: linear-gradient(135deg, var(--primary-color) 0%, var(--primary-dark) 100%);
    color: white;
    border: none;
    padding: 1.5rem;
}

.modal-body {
    padding: 1.5rem;
    background: #f8f9fa;
}

.btn-close {
    filter: brightness(0) invert(1);
}

/* Responsive Adjustments */
@media (max-width: 768px) {
    .branch-stats {
        grid-template-columns: 1fr;
    }
    
    .modal-dialog {
        margin: 0.5rem;
    }
}

/* Animation */
@keyframes fadeIn {
    from {
        opacity: 0;
        transform: translateY(20px);
    }
    to {
        opacity: 1;
        transform: translateY(0);
    }
}

.branch-card {
    animation: fadeIn 0.5s ease-out forwards;
}

.branch-card:nth-child(2) { animation-delay: 0.1s; }
.branch-card:nth-child(3) { animation-delay: 0.2s; }
.branch-card:nth-child(4) { animation-delay: 0.3s; }

/* Add these new styles */
.stat-card {
    border-radius: 15px;
    padding: 1.5rem;
    margin-bottom: 1rem;
    box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
    transition: transform 0.2s ease, box-shadow 0.2s ease;
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

.bg-gradient-primary {
    background: linear-gradient(135deg, #8B4543 0%, #723937 100%);
}

.bg-gradient-success {
    background: linear-gradient(135deg, #4A7C59 0%, #3A6246 100%);
}

.bg-gradient-warning {
    background: linear-gradient(135deg, #C4804D 0%, #A66B3F 100%);
}

.bg-gradient-info {
    background: linear-gradient(135deg, #36b9cc 0%, #258391 100%);
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

<script>
let modalSalesChart = null;
let modalPaymentChart = null;
let currentBranchId = null;

function destroyCharts() {
    if (modalSalesChart) {
        modalSalesChart.destroy();
        modalSalesChart = null;
    }
    if (modalPaymentChart) {
        modalPaymentChart.destroy();
        modalPaymentChart = null;
    }
}

function initializeModalCharts() {
    const salesCtx = document.getElementById('salesTrendChart').getContext('2d');
    modalSalesChart = new Chart(salesCtx, {
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
                legend: {
                    display: false
                },
                tooltip: {
                    callbacks: {
                        label: function(context) {
                            return '₱' + context.parsed.y.toLocaleString('en-US', {
                                minimumFractionDigits: 2,
                                maximumFractionDigits: 2
                            });
                        }
                    }
                }
            },
            scales: {
                y: {
                    beginAtZero: true,
                    ticks: {
                        callback: function(value) {
                            return '₱' + value.toLocaleString('en-US');
                        }
                    }
                }
            }
        }
    });

    const paymentCtx = document.getElementById('paymentMethodsChart').getContext('2d');
    modalPaymentChart = new Chart(paymentCtx, {
        type: 'doughnut',
        data: {
            labels: ['Cash', 'Credit Card', 'E-Wallet'],
            datasets: [{
                data: [0, 0, 0],
                backgroundColor: ['#4A7C59', '#C4804D', '#8B4543']
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: {
                legend: {
                    position: 'bottom'
                },
                tooltip: {
                    callbacks: {
                        label: function(context) {
                            const total = context.dataset.data.reduce((a, b) => a + b, 0);
                            const percentage = total > 0 ? ((context.parsed / total) * 100).toFixed(1) : 0;
                            return `${context.label}: ${context.parsed} (${percentage}%)`;
                        }
                    }
                }
            }
        }
    });
}

function updateModalCharts(branchId) {
    const period = $('#salesTrendPeriod').val();
    
    $.ajax({
        url: 'get_branch_sales_data.php',
        method: 'GET',
        data: {
            branch_id: branchId,
            period: period
        },
        success: function(response) {
            if (response.error) {
                console.error('Error:', response.error);
                return;
            }

            // Update statistics
            $('#modalTotalOrders').text(response.today_stats.total_orders);
            $('#modalTotalSales').text('₱' + parseFloat(response.today_stats.total_sales).toLocaleString('en-US', {
                minimumFractionDigits: 2,
                maximumFractionDigits: 2
            }));
            $('#modalAverageSale').text('₱' + parseFloat(response.today_stats.average_sale).toLocaleString('en-US', {
                minimumFractionDigits: 2,
                maximumFractionDigits: 2
            }));
            $('#modalHighestSale').text('₱' + parseFloat(response.today_stats.highest_sale).toLocaleString('en-US', {
                minimumFractionDigits: 2,
                maximumFractionDigits: 2
            }));

            // Update sales trend chart
            modalSalesChart.data.labels = response.sales_trend.labels;
            modalSalesChart.data.datasets[0].data = response.sales_trend.data;
            modalSalesChart.update();

            // Update payment methods chart
            modalPaymentChart.data.datasets[0].data = [
                response.payment_methods.cash,
                response.payment_methods.credit_card,
                response.payment_methods.e_wallet
            ];
            modalPaymentChart.update();
        },
        error: function(xhr, status, error) {
            console.error('Ajax error:', error);
        }
    });
}

$(document).ready(function() {
    // Initialize tooltips
    const tooltipTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="tooltip"]'));
    tooltipTriggerList.map(function(tooltipTriggerEl) {
        return new bootstrap.Tooltip(tooltipTriggerEl);
    });

    // Real-time branch stats update
    function updateBranchStats() {
        $('.branch-card').each(function() {
            var branchId = $(this).data('branch-id');
            $.get('get_branch_stats.php', { branch_id: branchId }, function(data) {
                if (data && !data.error) {
                    // Update sales and orders
                    $('#sales-' + branchId).text('₱' + parseFloat(data.today_sales).toFixed(2));
                    $('#orders-' + branchId).text(data.today_orders);

                    // Update cashier status: show all assigned cashiers, highlight active ones
                    let cashierHtml = '';
                    if (data.all_cashiers && data.all_cashiers.length > 0) {
                        cashierHtml = data.all_cashiers.map(function(cashier) {
                            if (cashier.is_active) {
                                return `<span style="color: #218838; font-weight: bold;"><i class='fas fa-circle' style='font-size:8px;color:#28a745;margin-right:4px;'></i>${cashier.full_name}</span>`;
                            } else {
                                return `<span style="color: #888;">${cashier.full_name}</span>`;
                            }
                        }).join(', ');
                    } else {
                        cashierHtml = '<span style="color:#888;">None assigned</span>';
                    }
                    $('#cashiers-' + branchId + ' .cashier-list').html(cashierHtml);

                    // Update operating status
                    let statusHtml = data.is_operating
                        ? '<span class="badge bg-success">Operating</span>'
                        : '<span class="badge bg-secondary">Closed</span>';
                    $('#status-' + branchId).html(statusHtml);

                    // Update inventory alerts
                    $('#lowstock-' + branchId).text(data.low_stock_count);
                    $('#expiring-' + branchId).text(data.expiring_count);
                } else {
                    // Show error in all fields for this branch
                    $('#sales-' + branchId).text('Error');
                    $('#orders-' + branchId).text('Error');
                    $('#cashiers-' + branchId + ' .cashier-list').text('Error');
                    $('#status-' + branchId).html('<span class="badge bg-danger">Error</span>');
                    $('#lowstock-' + branchId).text('!');
                    $('#expiring-' + branchId).text('!');
                }
            }, 'json').fail(function(jqXHR, textStatus, errorThrown) {
                // Show error in all fields for this branch
                $('#sales-' + branchId).text('Error');
                $('#orders-' + branchId).text('Error');
                $('#cashiers-' + branchId + ' .cashier-list').text('Error');
                $('#status-' + branchId).html('<span class="badge bg-danger">Error</span>');
                $('#lowstock-' + branchId).text('!');
                $('#expiring-' + branchId).text('!');
                // Debug log
                console.error('AJAX error for branch_id=' + branchId, textStatus, errorThrown, jqXHR.responseText);
            });
        });
    }
    setInterval(updateBranchStats, 5000);
    updateBranchStats();

    // Handle View Sales button click
    $('.view-sales-btn').on('click', function() {
        const branchId = $(this).data('branch-id');
        const branchName = $(this).data('branch-name');
        currentBranchId = branchId;
        
        // Update modal title
        $('.modal-title').text(branchName + ' - Sales Report');
        
        // Initialize charts if they don't exist
        if (!modalSalesChart || !modalPaymentChart) {
            initializeModalCharts();
        }
        
        // Update charts and statistics
        updateModalCharts(branchId);
    });

    // Handle period change
    $('#salesTrendPeriod').on('change', function() {
        if (currentBranchId) {
            updateModalCharts(currentBranchId);
        }
    });

    // Clean up when modal is hidden
    $('#salesModal').on('hidden.bs.modal', function() {
        currentBranchId = null;
        destroyCharts();
    });
});
</script>

<?php include('footer.php'); ?> 