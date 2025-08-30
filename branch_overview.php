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
                                    <div class="stat-value total-sales" id="sales-<?php echo $branch['branch_id']; ?>">₱0.00</div>
                                </div>
                            </div>
                            <div class="col-6">
                                <div class="stat-card bg-success text-white p-3 rounded">
                                    <div class="stat-label">Orders</div>
                                    <div class="stat-value total-orders" id="orders-<?php echo $branch['branch_id']; ?>">0</div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Inventory Alerts -->
                    <div class="inventory-alerts">
                        <h6 class="text-muted mb-3">Inventory Status</h6>
                        <div class="row g-3">
                            <div class="col-12">
                                <div class="alert alert-warning mb-0">
                                    <div class="d-flex align-items-center">
                                        <i class="fas fa-exclamation-triangle me-2"></i>
                                        <div>
                                            <div class="alert-label">Low Stock</div>
                                            <div class="alert-value low-stock-count" id="lowstock-<?php echo $branch['branch_id']; ?>">0</div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <!-- Expiring count removed since ingredients table doesn't have expiry_date column -->
                        </div>
                    </div>
                </div>
                <div class="card-footer bg-transparent border-top">
                    <div class="d-flex justify-content-between align-items-center">
                        <button class="btn btn-primary btn-sm view-sales-btn" data-bs-toggle="modal" data-bs-target="#salesModal" data-branch-id="<?php echo $branch['branch_id']; ?>" data-branch-name="<?php echo htmlspecialchars($branch['branch_name']); ?>">
                            <i class="fas fa-chart-line me-1"></i> View Sales
                        </button>
                        <button class="btn btn-secondary btn-sm view-inventory-btn" data-branch-id="<?php echo $branch['branch_id']; ?>">
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

<!-- Inventory Modal -->
<div class="modal fade" id="inventoryModal" tabindex="-1" aria-labelledby="inventoryModalLabel" aria-hidden="true">
  <div class="modal-dialog modal-lg modal-dialog-centered">
    <div class="modal-content">
      <div class="modal-header bg-maroon text-white">
        <h5 class="modal-title" id="inventoryModalLabel"><i class="fas fa-boxes me-2"></i>Branch Inventory</h5>
        <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
      </div>
      <div class="modal-body" id="inventoryModalBody">
        <div class="text-center p-4">Loading...</div>
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
let updateInterval = null;

// Real-time data functions
function loadActiveCashiers() {
    console.log('Loading active cashiers...');
    fetch('get_active_cashiers.php')
        .then(response => {
            if (!response.ok) {
                throw new Error(`HTTP ${response.status}: ${response.statusText}`);
            }
            return response.json();
        })
        .then(data => {
            console.log('Cashiers API response:', data);
            if (data.success) {
                updateCashierDisplay(data.branches);
                if (data.fallback) {
                    console.warn('Using fallback cashier data:', data.message);
                }
            } else {
                console.error('Error in cashiers API:', data.error);
                // Restore last good state instead of showing error
                restoreLastGoodCashierState();
            }
        })
        .catch(error => {
            console.error('Error loading active cashiers:', error);
            // Restore last good state instead of showing error
            restoreLastGoodCashierState();
            console.log('Restored last good cashier state due to connection error');
        });
}

function updateCashierDisplay(branches) {
    console.log('Updating cashier display with data:', branches);
    
    for (const branchId in branches) {
        const branch = branches[branchId];
        const cashierElement = document.getElementById(`cashiers-${branchId}`);
        
        if (cashierElement) {
            const cashierList = cashierElement.querySelector('.cashier-list');
            
            // Store the update in the element's data attribute for persistence
            if (branch.total_active > 0) {
                const activeNames = branch.active_cashiers.map(c => c.name).join(', ');
                const displayHTML = `<span class="text-success">${activeNames}</span> <span class="badge bg-success ms-1">${branch.total_active}</span>`;
                cashierList.innerHTML = displayHTML;
                cashierList.setAttribute('data-last-good-state', displayHTML);
            } else if (branch.total_cashiers > 0) {
                const displayHTML = '<span class="text-muted">No active cashiers</span>';
                cashierList.innerHTML = displayHTML;
                cashierList.setAttribute('data-last-good-state', displayHTML);
            } else {
                const displayHTML = '<span class="text-muted">No cashiers assigned</span>';
                cashierList.innerHTML = displayHTML;
                cashierList.setAttribute('data-last-good-state', displayHTML);
            }
        }
    }
}

function restoreLastGoodCashierState() {
    // Restore last known good state for all cashier displays
    document.querySelectorAll('.cashier-list').forEach(element => {
        const lastGoodState = element.getAttribute('data-last-good-state');
        if (lastGoodState) {
            element.innerHTML = lastGoodState;
        } else {
            element.innerHTML = '<span class="text-muted">No active cashiers</span>';
        }
    });
}

function loadRealtimeSales() {
    const branchCards = document.querySelectorAll('.branch-card');
    
    branchCards.forEach(card => {
        const branchId = card.dataset.branchId;
        
        fetch(`get_realtime_sales.php?branch_id=${branchId}&period=today`)
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    updateSalesDisplay(branchId, data.data);
                }
            })
            .catch(error => console.error('Error loading sales data:', error));
    });
}

function updateSalesDisplay(branchId, salesData) {
    const card = document.querySelector(`[data-branch-id="${branchId}"]`);
    if (!card) return;
    
    // Update sales metrics
    const totalSalesElement = card.querySelector('.total-sales');
    const totalOrdersElement = card.querySelector('.total-orders');
    const avgOrderElement = card.querySelector('.avg-order');
    
    if (totalSalesElement) {
        totalSalesElement.textContent = `₱${parseFloat(salesData.total_sales).toLocaleString('en-US', {minimumFractionDigits: 2})}`;
    }
    
    if (totalOrdersElement) {
        totalOrdersElement.textContent = salesData.total_orders;
    }
    
    if (avgOrderElement) {
        const avgValue = salesData.total_orders > 0 ? salesData.total_sales / salesData.total_orders : 0;
        avgOrderElement.textContent = `₱${avgValue.toLocaleString('en-US', {minimumFractionDigits: 2})}`;
    }
}

// Initialize sales comparison chart
let salesComparisonChart = null;

function initializeSalesComparisonChart() {
    const ctx = document.getElementById('salesComparisonChart');
    if (!ctx) return;
    
    salesComparisonChart = new Chart(ctx, {
        type: 'bar',
        data: {
            labels: [],
            datasets: [{
                label: 'Sales',
                data: [],
                backgroundColor: 'rgba(139, 69, 67, 0.8)',
                borderColor: '#8B4543',
                borderWidth: 1
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
}

function loadSalesComparison() {
    const period = $('#trendPeriod').val();
    
    $.ajax({
        url: 'get_branch_comparison.php',
        method: 'GET',
        data: { period: period },
        success: function(response) {
            if (response.success) {
                updateSalesComparisonChart(response.data);
                updateComparisonTable(response.data);
            } else {
                console.error('Error loading sales comparison:', response.error);
            }
        },
        error: function(xhr, status, error) {
            console.error('Ajax error:', error);
        }
    });
}

function updateSalesComparisonChart(data) {
    if (!salesComparisonChart) {
        initializeSalesComparisonChart();
    }
    
    const labels = data.map(branch => branch.branch_name);
    const sales = data.map(branch => branch.total_sales);
    
    salesComparisonChart.data.labels = labels;
    salesComparisonChart.data.datasets[0].data = sales;
    salesComparisonChart.update();
}

function updateComparisonTable(data) {
    const tbody = $('#branchComparisonTable tbody');
    tbody.empty();
    
    data.forEach(branch => {
        const statusClass = branch.status === 'Operating' ? 'success' : 'secondary';
        const topProducts = branch.top_products.length > 0 ? branch.top_products.join(', ') : 'No data';
        
        tbody.append(`
            <tr>
                <td>
                    <span class="badge bg-primary">#${branch.rank}</span>
                </td>
                <td><strong>${branch.branch_name}</strong></td>
                <td>
                    <span class="badge bg-${statusClass}">${branch.status}</span>
                </td>
                <td class="text-end">${branch.total_orders}</td>
                <td class="text-end">₱${parseFloat(branch.total_sales).toLocaleString('en-US', {minimumFractionDigits: 2})}</td>
                <td class="text-end">₱${parseFloat(branch.average_sale).toLocaleString('en-US', {minimumFractionDigits: 2})}</td>
                <td><small>${topProducts}</small></td>
            </tr>
        `);
    });
}

function loadRealtimeInventory() {
    // For now, use the simple inventory API that works system-wide
    fetch('test_inventory_simple.php')
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                // Update all branch cards with the same inventory data
                const branchCards = document.querySelectorAll('.branch-card');
                branchCards.forEach(card => {
                    const branchId = card.dataset.branchId;
                    updateInventoryDisplaySimple(branchId, data.stats);
                });
            } else {
                console.error('Inventory API error:', data.error);
            }
        })
        .catch(error => {
            console.error('Error loading inventory data:', error);
            // Show error in UI
            document.querySelectorAll('.low-stock-count, .expiring-count').forEach(element => {
                element.textContent = '?';
                element.style.color = '#dc3545';
            });
        });
}

function updateInventoryDisplay(branchId, inventoryData) {
    const card = document.querySelector(`[data-branch-id="${branchId}"]`);
    if (!card) return;
    
    // Update inventory status
    const lowStockElement = card.querySelector('.low-stock-count');
    const expiringElement = card.querySelector('.expiring-count');
    
    if (lowStockElement) {
        lowStockElement.textContent = inventoryData.summary.low_stock_items;
        const parent = lowStockElement.closest('.inventory-alert');
        if (parent) {
            parent.className = `inventory-alert ${inventoryData.summary.low_stock_items > 0 ? 'alert-warning' : 'alert-success'}`;
        }
    }
    
    if (expiringElement) {
        expiringElement.textContent = inventoryData.summary.expiring_items;
        const parent = expiringElement.closest('.inventory-alert');
        if (parent) {
            parent.className = `inventory-alert ${inventoryData.summary.expiring_items > 0 ? 'alert-danger' : 'alert-success'}`;
        }
    }
}

function updateInventoryDisplaySimple(branchId, stats) {
    const card = document.querySelector(`[data-branch-id="${branchId}"]`);
    if (!card) return;
    
    // Update low stock count
    const lowStockElement = card.querySelector('.low-stock-count');
    if (lowStockElement) {
        lowStockElement.textContent = stats.low_stock_items;
        lowStockElement.style.color = stats.low_stock_items > 0 ? '#856404' : '#155724';
        
        // Update parent alert styling
        const alertParent = lowStockElement.closest('.alert');
        if (alertParent) {
            alertParent.className = `alert ${stats.low_stock_items > 0 ? 'alert-warning' : 'alert-success'} mb-0`;
        }
    }
    
    // Update expiring count
    const expiringElement = card.querySelector('.expiring-count');
    if (expiringElement) {
        expiringElement.textContent = stats.expiring_items;
        expiringElement.style.color = stats.expiring_items > 0 ? '#721c24' : '#155724';
        
        // Update parent alert styling
        const alertParent = expiringElement.closest('.alert');
        if (alertParent) {
            alertParent.className = `alert ${stats.expiring_items > 0 ? 'alert-danger' : 'alert-success'} mb-0`;
        }
    }
}

function startRealtimeUpdates() {
    // Load initial data
    loadActiveCashiers();
    loadRealtimeSales();
    loadRealtimeInventory();
    
    // Set up auto-refresh every 60 seconds (less aggressive)
    if (updateInterval) {
        clearInterval(updateInterval);
    }
    
    updateInterval = setInterval(() => {
        console.log('Auto-refreshing data...');
        // Only refresh cashiers every other cycle to reduce errors
        if (Math.floor(Date.now() / 60000) % 2 === 0) {
            loadActiveCashiers();
        }
        loadRealtimeSales();
        loadRealtimeInventory();
    }, 60000);
}

function stopRealtimeUpdates() {
    if (updateInterval) {
        clearInterval(updateInterval);
        updateInterval = null;
    }
}

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
    
    // Start real-time updates
    startRealtimeUpdates();
    
    // Refresh button click
    document.getElementById('refreshStats').addEventListener('click', function() {
        // Manual refresh of all data
        loadActiveCashiers();
        loadRealtimeSales();
        loadRealtimeInventory();
        
        // Show refresh feedback
        const btn = this;
        const originalText = btn.innerHTML;
        btn.innerHTML = '<i class="fas fa-spinner fa-spin me-2"></i>Refreshing...';
        btn.disabled = true;
        
        setTimeout(() => {
            btn.innerHTML = originalText;
            btn.disabled = false;
        }, 2000);
    });

    // Real-time branch stats update
    function updateBranchStats() {
        console.log('Updating branch stats...');
        $('.branch-card').each(function() {
            var branchId = $(this).data('branch-id');
            $.get('get_branch_stats.php', { branch_id: branchId }, function(data) {
                console.log('Branch ' + branchId + ' data:', data);
                
                if (data && !data.error) {
                    // Update sales and orders
                    $('#sales-' + branchId).text('₱' + parseFloat(data.today_sales || 0).toFixed(2));
                    $('#orders-' + branchId).text(data.today_orders || 0);

                    // Update cashier status: show only active (online) cashiers
                    let cashierHtml = '';
                    if (data.active_cashiers && data.active_cashiers.length > 0) {
                        cashierHtml = data.active_cashiers.map(function(cashier) {
                            return `<span style="color: #218838; font-weight: bold;"><i class='fas fa-circle' style='font-size:8px;color:#28a745;margin-right:4px;'></i>${cashier.full_name}</span>`;
                        }).join(', ');
                        cashierHtml += ` <span class="badge bg-success ms-1">${data.total_active_cashiers}</span>`;
                    } else {
                        cashierHtml = '<span style="color:#888;">No active cashiers</span>';
                        if (data.total_assigned_cashiers > 0) {
                            cashierHtml += ` <span class="badge bg-secondary ms-1">${data.total_assigned_cashiers} assigned</span>`;
                        }
                    }
                    $('#cashiers-' + branchId + ' .cashier-list').html(cashierHtml);

                    // Update operating status
                    let statusHtml = data.is_operating
                        ? '<span class="badge bg-success">Operating</span>'
                        : '<span class="badge bg-secondary">Closed</span>';
                    $('#status-' + branchId).html(statusHtml);

                    // Update inventory alerts
                    $('#lowstock-' + branchId).text(data.low_stock_count || 0);
                    // Expiring count removed since ingredients table doesn't have expiry_date column
                } else {
                    // Show error in all fields for this branch
                    $('#sales-' + branchId).text('Error');
                    $('#orders-' + branchId).text('Error');
                    $('#cashiers-' + branchId + ' .cashier-list').text('Error');
                    $('#status-' + branchId).html('<span class="badge bg-danger">Error</span>');
                    $('#lowstock-' + branchId).text('!');
                    // Expiring count removed
                    console.error('Data error for branch_id=' + branchId, data);
                }
            }, 'json').fail(function(jqXHR, textStatus, errorThrown) {
                // Show error in all fields for this branch
                $('#sales-' + branchId).text('Error');
                $('#orders-' + branchId).text('Error');
                $('#cashiers-' + branchId + ' .cashier-list').text('Error');
                $('#status-' + branchId).html('<span class="badge bg-danger">Error</span>');
                $('#lowstock-' + branchId).text('!');
                                    // Expiring count removed
                // Debug log
                console.error('AJAX error for branch_id=' + branchId, textStatus, errorThrown, jqXHR.responseText);
            });
        });
    }
    setInterval(updateBranchStats, 5000);
    updateBranchStats();

    // Handle refresh button click
    $('#refreshStats').on('click', function() {
        $(this).find('i').addClass('fa-spin');
        updateBranchStats();
        setTimeout(() => {
            $(this).find('i').removeClass('fa-spin');
        }, 1000);
    });

    // Initialize sales comparison chart
    initializeSalesComparisonChart();
    
    // Load initial sales comparison data
    loadSalesComparison();
    
    // Handle trend period change
    $('#trendPeriod').on('change', function() {
        loadSalesComparison();
    });
    
    // Handle comparison period change
    $('#comparisonPeriod').on('change', function() {
        const period = $(this).val();
        if (period === 'custom') {
            $('#startDate, #endDate').prop('disabled', false);
        } else {
            $('#startDate, #endDate').prop('disabled', true);
            loadSalesComparison();
        }
    });
    
    // Handle apply filter button
    $('#applyFilter').on('click', function() {
        const period = $('#comparisonPeriod').val();
        const startDate = $('#startDate').val();
        const endDate = $('#endDate').val();
        
        if (period === 'custom' && (!startDate || !endDate)) {
            alert('Please select both start and end dates for custom range.');
            return;
        }
        
        $.ajax({
            url: 'get_branch_comparison.php',
            method: 'GET',
            data: { 
                period: period,
                start_date: startDate,
                end_date: endDate
            },
            success: function(response) {
                if (response.success) {
                    updateSalesComparisonChart(response.data);
                    updateComparisonTable(response.data);
                } else {
                    console.error('Error loading comparison data:', response.error);
                }
            },
            error: function(xhr, status, error) {
                console.error('Ajax error:', error);
            }
        });
    });
    
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

    $(document).on('click', '.view-inventory-btn', function(e) {
        e.preventDefault();
        var branchId = $(this).data('branch-id');
        $('#inventoryModalBody').html('<div class="text-center p-4">Loading...</div>');
        $('#inventoryModal').modal('show');
        $.get('get_branch_inventory.php', { id: branchId }, function(data) {
            $('#inventoryModalBody').html(data);
        });
    });
});
</script>

<?php include('footer.php'); ?> 
<?php include('footer.php'); ?> 