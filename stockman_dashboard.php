<?php
require_once 'db_connect.php';
require_once 'auth_function.php';

// Check if user is logged in and is a stockman
if (!isset($_SESSION['user_logged_in']) || $_SESSION['user_logged_in'] !== true || $_SESSION['user_type'] !== 'Stockman') {
    header('Location: login.php');
    exit();
}

// Debug: Ensure user type is set correctly
if (!isset($_SESSION['user_type']) || $_SESSION['user_type'] !== 'Stockman') {
    $_SESSION['user_type'] = 'Stockman';
}

// Force set user type to Stockman for debugging
$_SESSION['user_type'] = 'Stockman';

include('header.php');
?>

<!-- Debug Info (remove this after testing) -->
<div style="background: #f0f0f0; padding: 10px; margin: 10px; border: 1px solid #ccc;">
    <strong>Debug Info:</strong><br>
    User Type: <?php echo $_SESSION['user_type']; ?><br>
    User Logged In: <?php echo $_SESSION['user_logged_in'] ? 'Yes' : 'No'; ?><br>
    Current Page: stockman_dashboard.php
</div>

<style>
.stockman-dashboard-bg {
    background: #f8f5f5;
    min-height: 100vh;
    padding-bottom: 2rem;
}

.stockman-section-title {
    color: #8B4543;
    font-size: 2.2rem;
    font-weight: 700;
    letter-spacing: 0.7px;
    margin-bottom: 2rem;
    margin-top: 1.2rem;
    display: flex;
    align-items: center;
    gap: 0.7rem;
    position: relative;
    background: none;
    border: none;
    animation: fadeInDown 0.7s;
}

.stockman-section-title .section-icon {
    font-size: 1.5em;
    color: #8B4543;
    opacity: 0.92;
}

.stockman-section-title::after {
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

.stockman-overview-cards {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(280px, 1fr));
    gap: 1.5rem;
    margin-bottom: 2rem;
}

.stockman-overview-card {
    background: #fff;
    border-radius: 1rem;
    box-shadow: 0 2px 12px rgba(139, 69, 67, 0.07);
    padding: 1.5rem;
    display: flex;
    align-items: center;
    gap: 1rem;
    border-left: 6px solid #8B4543;
    transition: all 0.3s ease;
}

.stockman-overview-card:hover {
    transform: translateY(-3px);
    box-shadow: 0 4px 20px rgba(139, 69, 67, 0.12);
    border-left-color: #b97a6a;
}

.stockman-overview-card .icon {
    font-size: 2.5rem;
    color: #8B4543;
    opacity: 0.9;
}

.stockman-overview-card .card-content {
    flex: 1;
}

.stockman-overview-card .card-title {
    font-size: 0.9rem;
    color: #6c757d;
    font-weight: 600;
    margin: 0;
    text-transform: uppercase;
    letter-spacing: 0.5px;
}

.stockman-overview-card .card-value {
    font-size: 2rem;
    font-weight: 700;
    color: #8B4543;
    margin: 0.2rem 0;
}

.stockman-overview-card .card-trend {
    font-size: 0.8rem;
    font-weight: 500;
    margin: 0;
}

.stockman-overview-card .card-trend.positive {
    color: #28a745;
}

.stockman-overview-card .card-trend.negative {
    color: #dc3545;
}

.stockman-overview-card .card-trend.neutral {
    color: #6c757d;
}

.stockman-card {
    background: #fff;
    border-radius: 1rem;
    box-shadow: 0 2px 12px rgba(139, 69, 67, 0.07);
    margin-bottom: 2rem;
    border: 1px solid #e5d6d6;
}

.stockman-card .card-header {
    background: #8B4543;
    color: #fff;
    border-radius: 1rem 1rem 0 0;
    font-weight: 600;
    font-size: 1.1rem;
    padding: 1.2rem 1.5rem;
    border-bottom: none;
    display: flex;
    align-items: center;
    gap: 0.7rem;
}

.stockman-card .card-body {
    padding: 1.5rem;
}

.chart-container {
    background: #f8f9fa;
    border-radius: 0.5rem;
    padding: 1rem;
    height: 300px;
    position: relative;
    border: 1px solid #e9ecef;
}

.branch-indicator {
    text-align: center;
    margin-bottom: 2rem;
    padding: 1rem 2rem;
    background: rgba(139, 69, 67, 0.1);
    border-radius: 1rem;
    border: 1px solid rgba(139, 69, 67, 0.2);
    display: inline-block;
    margin-left: auto;
    margin-right: auto;
}

.branch-content {
    display: flex;
    align-items: center;
    justify-content: center;
    gap: 1rem;
}

.branch-icon {
    background: #8B4543;
    width: 40px;
    height: 40px;
    border-radius: 50%;
    display: flex;
    align-items: center;
    justify-content: center;
}

.branch-icon i {
    color: white;
    font-size: 1.2rem;
}

.branch-info {
    display: flex;
    flex-direction: column;
    align-items: flex-start;
}

.branch-label {
    color: #8B4543;
    font-size: 0.8rem;
    font-weight: 500;
    opacity: 0.7;
    text-transform: uppercase;
    letter-spacing: 0.5px;
}

.branch-name {
    color: #8B4543;
    font-weight: 600;
    font-size: 1.1rem;
    margin: 0;
}

@keyframes fadeInDown {
    from {
        opacity: 0;
        transform: translateY(-20px);
    }
    to {
        opacity: 1;
        transform: translateY(0);
    }
}

@media (max-width: 768px) {
    .stockman-overview-cards {
        grid-template-columns: 1fr;
        gap: 1rem;
    }
    
    .stockman-overview-card {
        padding: 1.2rem;
    }
    
    .stockman-overview-card .card-value {
        font-size: 1.8rem;
    }
}
</style>

<div class="stockman-dashboard-bg">
    <div class="container-fluid px-4">
        <div class="stockman-section-title">
            <span class="section-icon"><i class="fas fa-chart-line"></i></span>
            Stock Analytics Dashboard
        </div>
        
        <!-- Branch Indicator -->
        <div class="branch-indicator" id="branchIndicator">
            <div class="branch-content">
                <div class="branch-icon">
                    <i class="fas fa-building"></i>
                </div>
                <div class="branch-info">
                    <span class="branch-label">Current Branch</span>
                    <span class="branch-name" id="branchName">Loading branch...</span>
                </div>
                <div class="branch-status">
                    <div class="spinner-border spinner-border-sm" role="status" id="branchSpinner">
                        <span class="visually-hidden">Loading...</span>
                    </div>
                </div>
            </div>
        </div>
        
        <!-- Login Status Check -->
        <div id="loginStatus" class="alert alert-info mt-3" style="display: none;">
            <i class="fas fa-info-circle me-2"></i>
            <strong>Login Required:</strong> Please log in to view your branch analytics.
            <a href="login.php" class="btn btn-primary btn-sm ms-2">Login</a>
        </div>
        
        <!-- Analytics Overview Cards -->
        <div class="stockman-overview-cards">
            <div class="stockman-overview-card">
                <span class="icon"><i class="fas fa-boxes"></i></span>
                <div class="card-content">
                    <span class="card-title">Total Items</span>
                    <span class="card-value" id="totalItems">0</span>
                    <span class="card-trend positive" id="totalItemsTrend">+5% this week</span>
                </div>
            </div>
            <div class="stockman-overview-card">
                <span class="icon"><i class="fas fa-check-circle"></i></span>
                <div class="card-content">
                    <span class="card-title">Available Ingredients</span>
                    <span class="card-value" id="availableIngredients">0</span>
                    <span class="card-trend positive" id="availableIngredientsTrend">+0 this week</span>
                </div>
            </div>
            <div class="stockman-overview-card">
                <span class="icon"><i class="fas fa-exclamation-triangle"></i></span>
                <div class="card-content">
                    <span class="card-title">Low Stock Items</span>
                    <span class="card-value" id="lowStockItems">0</span>
                    <span class="card-trend negative" id="lowStockTrend">+2 this week</span>
                </div>
            </div>
            <div class="stockman-overview-card">
                <span class="icon"><i class="fas fa-exchange-alt"></i></span>
                <div class="card-content">
                    <span class="card-title">Stock Movements</span>
                    <span class="card-value" id="stockMovements">0</span>
                    <span class="card-trend positive" id="movementsTrend">+12% this week</span>
                </div>
            </div>
            <div class="stockman-overview-card">
                <span class="icon"><i class="fas fa-clock"></i></span>
                <div class="card-content">
                    <span class="card-title">Expiring Items</span>
                    <span class="card-value" id="expiringItems">0</span>
                    <span class="card-trend neutral" id="expiringTrend">No change</span>
                </div>
            </div>
            <div class="stockman-overview-card">
                <span class="icon"><i class="fas fa-chart-line"></i></span>
                <div class="card-content">
                    <span class="card-title">Stock Turnover</span>
                    <span class="card-value" id="stockTurnover">0%</span>
                    <span class="card-trend positive" id="turnoverTrend">+8% this month</span>
                </div>
            </div>
        </div>
        
        <!-- Analytics Grid -->
        <div class="row">
            <!-- Charts Section -->
            <div class="col-lg-8">
                <div class="row">
                    <div class="col-md-6 mb-3">
                        <div class="stockman-card">
                            <div class="card-header">
                                <i class="fas fa-chart-pie me-1"></i>
                                Stock Status Distribution
                            </div>
                            <div class="card-body">
                                <div class="chart-container">
                                    <canvas id="stockStatusChart" width="100%" height="300"></canvas>
                                </div>
                            </div>
                        </div>
                    </div>
                    
                    <div class="col-md-6 mb-3">
                        <div class="stockman-card">
                            <div class="card-header">
                                <i class="fas fa-chart-bar me-1"></i>
                                Category Performance
                            </div>
                            <div class="card-body">
                                <div class="chart-container">
                                    <canvas id="categoryChart" width="100%" height="300"></canvas>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            
            <!-- Insights Section -->
            <div class="col-lg-4">
                <div class="stockman-card">
                    <div class="card-header">
                        <i class="fas fa-exclamation-circle me-1"></i>
                        Critical Stock Alerts
                    </div>
                    <div class="card-body">
                        <div id="criticalAlerts">
                            <!-- Critical alerts will be loaded here -->
                        </div>
                    </div>
                </div>
                
                <div class="stockman-card">
                    <div class="card-header">
                        <i class="fas fa-lightbulb me-1"></i>
                        Stock Insights
                    </div>
                    <div class="card-body">
                        <div class="mb-3">
                            <small class="text-muted">Fastest Moving Items</small>
                            <div class="h5 mb-0" id="fastestMoving">0</div>
                        </div>
                        <div class="mb-3">
                            <small class="text-muted">Slowest Moving Items</small>
                            <div class="h5 mb-0" id="slowestMoving">0</div>
                        </div>
                        <div class="mb-3">
                            <small class="text-muted">Expiring Soon</small>
                            <div class="h5 mb-0" id="expiringSoon">0</div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
$(document).ready(function() {
    // Check if Chart.js is available
    if (typeof Chart === 'undefined') {
        console.error('Chart.js is not loaded');
        return;
    }

    // Initialize stock status chart
    const ctx = document.getElementById('stockStatusChart');
    if (!ctx) {
        console.error('Stock status chart canvas not found');
        return;
    }
    
    const stockStatusChart = new Chart(ctx.getContext('2d'), {
        type: 'doughnut',
        data: {
            labels: ['Adequate', 'Low Stock', 'Out of Stock'],
            datasets: [{
                data: [0, 0, 0],
                backgroundColor: [
                    'rgba(40, 167, 69, 0.9)',
                    'rgba(255, 193, 7, 0.9)',
                    'rgba(220, 53, 69, 0.9)'
                ],
                borderColor: [
                    '#28a745',
                    '#ffc107',
                    '#dc3545'
                ],
                borderWidth: 2
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: {
                legend: {
                    position: 'bottom'
                }
            }
        }
    });

    // Initialize category performance chart
    const ctx2 = document.getElementById('categoryChart').getContext('2d');
    const categoryChart = new Chart(ctx2, {
        type: 'bar',
        data: {
            labels: ['Category 1', 'Category 2', 'Category 3'],
            datasets: [{
                label: 'Items Count',
                data: [0, 0, 0],
                backgroundColor: [
                    'rgba(139, 69, 67, 0.8)',
                    'rgba(185, 122, 106, 0.8)',
                    'rgba(220, 53, 69, 0.8)'
                ],
                borderColor: [
                    '#8B4543',
                    '#b97a6a',
                    '#dc3545'
                ],
                borderWidth: 1
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: {
                legend: {
                    display: false
                }
            },
            scales: {
                y: {
                    beginAtZero: true
                }
            }
        }
    });

    // Function to update dashboard data
    function updateDashboard() {
        $.ajax({
            url: 'get_stockman_analytics.php',
            method: 'GET',
            dataType: 'json',
            timeout: 10000,
            success: function(response) {
                console.log('Analytics Response:', response);
            
                // Update branch indicator
                if (response.branch_name) {
                    $('#branchName').text(response.branch_name);
                } else {
                    $('#branchName').text('Main Branch');
                }
                
                // Hide loading spinner
                $('#branchSpinner').hide();
                
                // Update overview cards
                $('#totalItems').text(response.total_items || 0);
                $('#availableIngredients').text(response.available_ingredients || 0);
                $('#lowStockItems').text(response.low_stock_items || 0);
                $('#stockMovements').text(response.stock_movements || 0);
                $('#expiringItems').text(response.expiring_items || 0);
                $('#stockTurnover').text((response.stock_turnover || 0) + '%');

                // Update trends
                $('#totalItemsTrend').text(response.total_items_trend || 'No change');
                $('#availableIngredientsTrend').text(response.available_ingredients_trend || 'No change');
                $('#lowStockTrend').text(response.low_stock_trend || 'No change');
                $('#movementsTrend').text(response.movements_trend || 'No change');
                $('#expiringTrend').text(response.expiring_trend || 'No change');
                $('#turnoverTrend').text(response.turnover_trend || 'No change');

                // Update stock status chart
                stockStatusChart.data.datasets[0].data = [
                    response.adequate_stock || 0,
                    response.low_stock || 0,
                    response.out_of_stock || 0
                ];
                stockStatusChart.update();

                // Update insights
                $('#fastestMoving').text(response.fastest_moving || 0);
                $('#slowestMoving').text(response.slowest_moving || 0);
                $('#expiringSoon').text(response.expiring_soon || 0);

                // Update category chart
                if (response.category_labels && response.category_data) {
                    categoryChart.data.labels = response.category_labels;
                    categoryChart.data.datasets[0].data = response.category_data;
                    categoryChart.update();
                }

                // Update critical alerts
                updateCriticalAlerts(response.critical_alerts || []);
            },
            error: function(xhr, status, error) {
                console.error('Error loading analytics:', error);
                
                let errorMessage = 'Error loading data';
                let showLoginMessage = false;
                
                try {
                    const response = JSON.parse(xhr.responseText);
                    if (response.error) {
                        errorMessage = response.error;
                        if (response.error.includes('not logged in') || response.error.includes('Access denied')) {
                            showLoginMessage = true;
                        }
                    }
                } catch (e) {
                    errorMessage = xhr.responseText || error;
                }
                
                if (showLoginMessage) {
                    $('#loginStatus').show();
                    $('#branchName').text('Please Login');
                } else {
                    $('#branchName').text('Error: ' + errorMessage);
                }
                
                $('#branchSpinner').hide();
                
                // Set default values
                $('#totalItems').text('0');
                $('#availableIngredients').text('0');
                $('#lowStockItems').text('0');
                $('#stockMovements').text('0');
                $('#expiringItems').text('0');
                $('#stockTurnover').text('0%');
            }
        });
    }

    // Function to update critical alerts
    function updateCriticalAlerts(alerts) {
        const container = $('#criticalAlerts');
        container.empty();

        if (alerts.length === 0) {
            container.append(`
                <div class="text-center text-muted py-3">
                    <i class="fas fa-check-circle fa-2x mb-2"></i>
                    <p>No critical alerts at this time</p>
                </div>
            `);
        } else {
            alerts.forEach(alert => {
                container.append(`
                    <div class="alert alert-${alert.severity} d-flex align-items-center mb-2" role="alert">
                        <i class="fas fa-exclamation-triangle me-2"></i>
                        <div>
                            <strong>${alert.title}</strong><br>
                            <small>${alert.description}</small>
                        </div>
                    </div>
                `);
            });
        }
    }

    // Initial load
    updateDashboard();

    // Refresh data every 5 minutes
    setInterval(updateDashboard, 300000);
});
</script>

<?php include('footer.php'); ?> 