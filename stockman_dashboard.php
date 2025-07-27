<?php
require_once 'db_connect.php';
require_once 'auth_function.php';

// Check if user is logged in and is a stockman
if (!isset($_SESSION['user_logged_in']) || $_SESSION['user_logged_in'] !== true || $_SESSION['user_type'] !== 'Stockman') {
    header('Location: login.php');
    exit();
}

include('header.php');
?>

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
.stockman-card {
    background: #fff;
    border-radius: 1.1rem;
    box-shadow: 0 2px 12px rgba(139, 69, 67, 0.07);
    margin-bottom: 2rem;
    border: 1.5px solid #e5d6d6;
}
.stockman-card .card-header {
    background: #8B4543;
    color: #fff;
    border-radius: 1.1rem 1.1rem 0 0;
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
.stockman-overview-cards {
    display: flex;
    gap: 2rem;
    margin-bottom: 2.5rem;
    flex-wrap: wrap;
    justify-content: flex-start;
}
.stockman-overview-card {
    flex: 1 1 220px;
    background: #fff;
    border-radius: 1.1rem;
    box-shadow: 0 2px 12px rgba(139, 69, 67, 0.07);
    padding: 1.5rem 2.2rem 1.5rem 2.2rem;
    display: flex;
    align-items: center;
    gap: 1.2rem;
    min-width: 220px;
    max-width: 320px;
    border-left: 7px solid #8B4543;
    position: relative;
}
.stockman-overview-card .icon {
    font-size: 2.2rem;
    color: #8B4543;
    opacity: 0.85;
}
.stockman-overview-card .card-content {
    display: flex;
    flex-direction: column;
    gap: 0.2rem;
}
.stockman-overview-card .card-title {
    font-size: 1.1rem;
    color: #8B4543;
    font-weight: 600;
    margin-bottom: 0.2rem;
}
.stockman-overview-card .card-value {
    font-size: 2.1rem;
    font-weight: 700;
    color: #3C2A2A;
}
@media (max-width: 900px) {
    .stockman-overview-cards { flex-direction: column; gap: 1.2rem; }
    .stockman-overview-card { max-width: 100%; }
}
</style>
<div class="stockman-dashboard-bg">
    <div class="container-fluid px-4">
        <div class="stockman-section-title">
            <span class="section-icon"><i class="fas fa-clipboard-list"></i></span>
            Stock Management Dashboard
        </div>
        <div class="stockman-overview-cards">
            <div class="stockman-overview-card">
                <span class="icon"><i class="fas fa-boxes"></i></span>
                <div class="card-content">
                    <span class="card-title">Total Items</span>
                    <span class="card-value" id="totalItems">0</span>
                </div>
            </div>
            <div class="stockman-overview-card">
                <span class="icon"><i class="fas fa-exclamation-triangle"></i></span>
                <div class="card-content">
                    <span class="card-title">Low Stock Items</span>
                    <span class="card-value" id="lowStockItems">0</span>
                </div>
            </div>
            <div class="stockman-overview-card">
                <span class="icon"><i class="fas fa-exchange-alt"></i></span>
                <div class="card-content">
                    <span class="card-title">Stock Movements</span>
                    <span class="card-value" id="stockMovements">0</span>
                </div>
            </div>
            <div class="stockman-overview-card">
                <span class="icon"><i class="fas fa-clock"></i></span>
                <div class="card-content">
                    <span class="card-title">Expiring Items</span>
                    <span class="card-value" id="expiringItems">0</span>
                </div>
            </div>
        </div>
        <div class="row">
            <div class="col-xl-8">
                <div class="stockman-card mb-4">
                    <div class="card-header">
                        <i class="fas fa-boxes me-1"></i>
                        Stock Inventory
                        <button class="btn btn-primary btn-sm ms-auto" data-bs-toggle="modal" data-bs-target="#addStockModal">
                            <i class="fas fa-plus"></i> Add Stock
                        </button>
                    </div>
                    <div class="card-body">
                        <div class="table-responsive">
                            <table class="table table-bordered table-hover" id="stockTable">
                                <thead>
                                    <tr>
                                        <th>Item Name</th>
                                        <th>Current Stock</th>
                                        <th>Minimum Stock</th>
                                        <th>Status</th>
                                        <th>Actions</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <!-- Stock items will be loaded here dynamically -->
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-xl-4">
                <div class="stockman-card mb-4">
                    <div class="card-header">
                        <i class="fas fa-chart-pie me-1"></i>
                        Stock Status
                    </div>
                    <div class="card-body">
                        <canvas id="stockStatusChart" width="100%" height="300"></canvas>
                    </div>
                </div>
            </div>
        </div>
        <div class="row">
            <div class="col-12">
                <div class="stockman-card mb-4">
                    <div class="card-header">
                        <i class="fas fa-history me-1"></i>
                        Recent Stock Movements
                    </div>
                    <div class="card-body">
                        <div class="table-responsive">
                            <table class="table table-bordered table-hover" id="movementsTable">
                                <thead>
                                    <tr>
                                        <th>Date</th>
                                        <th>Item</th>
                                        <th>Type</th>
                                        <th>Quantity</th>
                                        <th>Previous Stock</th>
                                        <th>New Stock</th>
                                        <th>Reference</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <!-- Stock movements will be loaded here dynamically -->
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Add Stock Modal -->
<div class="modal fade" id="addStockModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Add Stock</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <form id="addStockForm">
                    <div class="mb-3">
                        <label for="itemName" class="form-label">Item Name</label>
                        <input type="text" class="form-control" id="itemName" required>
                    </div>
                    <div class="mb-3">
                        <label for="quantity" class="form-label">Quantity</label>
                        <input type="number" class="form-control" id="quantity" min="1" required>
                    </div>
                    <div class="mb-3">
                        <label for="minimumStock" class="form-label">Minimum Stock Level</label>
                        <input type="number" class="form-control" id="minimumStock" min="0" required>
                    </div>
                </form>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                <button type="button" class="btn btn-primary" id="saveStockBtn">Save</button>
            </div>
        </div>
    </div>
</div>

<script>
$(document).ready(function() {
    // Initialize DataTables
    $('#stockTable').DataTable({
        pageLength: 10,
        order: [[0, 'asc']]
    });

    $('#movementsTable').DataTable({
        pageLength: 10,
        order: [[0, 'desc']]
    });

    // Initialize stock status chart
    const ctx = document.getElementById('stockStatusChart').getContext('2d');
    const stockStatusChart = new Chart(ctx, {
        type: 'pie',
        data: {
            labels: ['Adequate', 'Low Stock', 'Out of Stock'],
            datasets: [{
                data: [0, 0, 0],
                backgroundColor: ['#28a745', '#ffc107', '#dc3545']
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false
        }
    });

    // Function to update dashboard data
    function updateDashboard() {
        // Update overview cards
        $.get('get_stockman_stats.php', function(response) {
            $('#totalItems').text(response.total_items);
            $('#lowStockItems').text(response.low_stock_items);
            $('#stockMovements').text(response.stock_movements);
            $('#expiringItems').text(response.expiring_items);

            // Update stock status chart
            stockStatusChart.data.datasets[0].data = [
                response.adequate_stock,
                response.low_stock,
                response.out_of_stock
            ];
            stockStatusChart.update();
        });

        // Update stock table
        $.get('get_stock_items.php', function(response) {
            const tbody = $('#stockTable tbody');
            tbody.empty();

            response.items.forEach(item => {
                const statusClass = item.current_stock <= item.minimum_stock ? 'text-danger' : 'text-success';
                tbody.append(`
                    <tr>
                        <td>${item.item_name}</td>
                        <td>${item.current_stock}</td>
                        <td>${item.minimum_stock}</td>
                        <td><span class="${statusClass}">${item.status}</span></td>
                        <td>
                            <button class="btn btn-sm btn-primary" onclick="adjustStock(${item.id})">
                                <i class="fa-solid fa-edit"></i>
                            </button>
                        </td>
                    </tr>
                `);
            });
        });

        // Update movements table
        $.get('get_stock_movements.php', function(response) {
            const tbody = $('#movementsTable tbody');
            tbody.empty();

            response.movements.forEach(movement => {
                const typeClass = movement.type === 'IN' ? 'text-success' : 'text-danger';
                tbody.append(`
                    <tr>
                        <td>${movement.date}</td>
                        <td>${movement.item_name}</td>
                        <td><span class="${typeClass}">${movement.type}</span></td>
                        <td>${movement.quantity}</td>
                        <td>${movement.previous_stock}</td>
                        <td>${movement.new_stock}</td>
                        <td>${movement.reference}</td>
                    </tr>
                `);
            });
        });
    }

    // Save stock button click handler
    $('#saveStockBtn').click(function() {
        const formData = {
            item_name: $('#itemName').val(),
            quantity: $('#quantity').val(),
            minimum_stock: $('#minimumStock').val()
        };

        $.post('add_stock_item.php', formData, function(response) {
            if (response.success) {
                $('#addStockModal').modal('hide');
                $('#addStockForm')[0].reset();
                updateDashboard();
                alert('Stock item added successfully!');
            } else {
                alert('Error: ' + response.message);
            }
        });
    });

    // Initial load
    updateDashboard();

    // Refresh data every 5 minutes
    setInterval(updateDashboard, 300000);
});
</script>

<?php include('footer.php'); ?> 