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
}

/* Status badge styling */
.badge {
    font-size: 0.75rem;
    padding: 0.5rem 0.75rem;
    border-radius: 0.5rem;
    font-weight: 600;
}

.badge.bg-warning {
    background-color: #ffc107 !important;
    color: #212529;
}

.badge.bg-success {
    background-color: #28a745 !important;
    color: white;
}

.badge.bg-danger {
    background-color: #dc3545 !important;
    color: white;
}

.badge.bg-secondary {
    background-color: #6c757d !important;
    color: white;
}

/* Maroon Button */
.btn-maroon {
    background: #8B4543;
    border: none;
    color: white;
    border-radius: 0.5rem;
    padding: 0.625rem 1.25rem;
    font-weight: 500;
    transition: all 0.2s ease;
}

.btn-maroon:hover {
    background: #7a3d3b;
    color: white;
    transform: translateY(-1px);
    box-shadow: 0 0.15rem 1.75rem 0 rgba(139, 69, 67, 0.15);
}

/* Modal Header */
.bg-maroon {
    background: #8B4543 !important;
}
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
                    </div>
                    <div class="card-body">
                        <div class="table-responsive">
                            <table class="table table-bordered table-hover" id="stockTable">
                                <thead>
                                    <tr>
                                        <th>Ingredient Name</th>
                                        <th>Current Stock</th>
                                        <th>Category</th>
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
                        <i class="fas fa-clipboard-check me-1"></i>
                        Request Stock Updates
                    </div>
                    <div class="card-body">
                        <div class="table-responsive">
                            <table class="table table-bordered table-hover" id="requestsTable">
                                <thead>
                                    <tr>
                                        <th>Date Requested</th>
                                        <th>Ingredients</th>
                                        <th>Status</th>
                                        <th>Delivery Status</th>
                                        <th>Notes</th>
                                        <th>Updated By</th>
                                        <th>Actions</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <!-- Request updates will be loaded here dynamically -->
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
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

    $('#requestsTable').DataTable({
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
            if (response.success) {
                const tbody = $('#stockTable tbody');
                tbody.empty();

                if (response.items.length === 0) {
                    tbody.append(`
                        <tr>
                            <td colspan="5" class="text-center text-muted">No ingredients found</td>
                        </tr>
                    `);
                } else {
                    response.items.forEach(item => {
                        let statusClass = 'text-success';
                        let statusBadge = '';
                        
                        switch (item.status) {
                            case 'Out of Stock':
                                statusClass = 'text-danger';
                                statusBadge = '<span class="badge bg-danger">Out of Stock</span>';
                                break;
                            case 'Low Stock':
                                statusClass = 'text-warning';
                                statusBadge = '<span class="badge bg-warning">Low Stock</span>';
                                break;
                            case 'Adequate':
                                statusClass = 'text-success';
                                statusBadge = '<span class="badge bg-success">Adequate</span>';
                                break;
                        }
                        
                        tbody.append(`
                            <tr>
                                <td><strong>${item.item_name}</strong></td>
                                <td>${item.current_stock}</td>
                                <td>${item.category_name}</td>
                                <td>${statusBadge}</td>
                                <td>
                                    <div class="btn-group" role="group">
                                        <button class="btn btn-sm btn-outline-primary" onclick="adjustStock(${item.id})" title="Adjust Stock">
                                            <i class="fas fa-edit"></i>
                                        </button>
                                        <button class="btn btn-sm btn-outline-info" onclick="viewDetails(${item.id})" title="View Details">
                                            <i class="fas fa-eye"></i>
                                        </button>
                                        <button class="btn btn-sm btn-outline-warning" onclick="requestStock(${item.id})" title="Request Stock">
                                            <i class="fas fa-plus"></i>
                                        </button>
                                    </div>
                                </td>
                            </tr>
                        `);
                    });
                }
            } else {
                console.error('Error loading stock items:', response.error);
            }
        });

        // Update requests table
        $.get('get_stockman_requests.php', function(response) {
            if (response.success) {
                const tbody = $('#requestsTable tbody');
                tbody.empty();

                if (response.data.length === 0) {
                    tbody.append(`
                        <tr>
                            <td colspan="7" class="text-center text-muted">No requests found</td>
                        </tr>
                    `);
                } else {
                    response.data.forEach(request => {
                        // Add delivery update button for approved requests
                        let actionButton = '';
                        if (request.status.includes('APPROVED')) {
                            actionButton = `<button class="btn btn-info btn-sm update-delivery" data-id="${request.request_id}" onclick="updateDeliveryStatus(${request.request_id})">
                                <i class="fas fa-truck"></i> Update Delivery
                            </button>`;
                        }
                        
                        tbody.append(`
                            <tr>
                                <td>${request.request_date}</td>
                                <td>${request.ingredients}</td>
                                <td>${request.status}</td>
                                <td>${request.delivery_status}</td>
                                <td>${request.notes}</td>
                                <td>${request.updated_by}</td>
                                <td>${actionButton}</td>
                            </tr>
                        `);
                    });
                }
            } else {
                console.error('Error loading requests:', response.error);
            }
        });
    }



    // Action functions
    window.adjustStock = function(ingredientId) {
        // Show adjust stock modal
        $('#adjustStockModalBody').html('<div class="text-center p-4">Loading...</div>');
        $('#adjustStockModal').modal('show');
        $.get('adjust_stock_modal.php', { id: ingredientId }, function(data) {
            $('#adjustStockModalBody').html(data);
        });
    };

    window.viewDetails = function(ingredientId) {
        // Show ingredient details modal
        $('#viewDetailsModalBody').html('<div class="text-center p-4">Loading...</div>');
        $('#viewDetailsModal').modal('show');
        $.get('view_ingredient_details.php', { id: ingredientId }, function(data) {
            $('#viewDetailsModalBody').html(data);
        });
    };

    window.requestStock = function(ingredientId) {
        // Show request stock modal with pre-selected ingredient
        $('#requestStockModalBody').html('<div class="text-center p-4">Loading...</div>');
        $('#requestStockModal').modal('show');
        $.get('request_stock_modal.php', { ingredient_id: ingredientId }, function(data) {
            $('#requestStockModalBody').html(data);
        });
    };

    window.updateDeliveryStatus = function(requestId) {
        // Show delivery status update modal
        console.log('Updating delivery status for request ID:', requestId);
        $('#deliveryRequestId').val(requestId);
        $('#deliveryModal').modal('show');
    };

    // Initial load
    updateDashboard();

    // Refresh data every 5 minutes
    setInterval(updateDashboard, 300000);
    
    // Add modal hidden event handlers for proper cleanup
    $('#adjustStockModal').on('hidden.bs.modal', function () {
        $('body').removeClass('modal-open');
        $('.modal-backdrop').remove();
    });
    
    $('#viewDetailsModal').on('hidden.bs.modal', function () {
        $('body').removeClass('modal-open');
        $('.modal-backdrop').remove();
    });
    
    $('#requestStockModal').on('hidden.bs.modal', function () {
        $('body').removeClass('modal-open');
        $('.modal-backdrop').remove();
    });
    
    $('#deliveryModal').on('hidden.bs.modal', function () {
        $('body').removeClass('modal-open');
        $('.modal-backdrop').remove();
    });

    // Update delivery submission
    $('#updateDelivery').click(function() {
        const requestId = $('#deliveryRequestId').val();
        const deliveryStatus = $('#deliveryStatus').val();
        const deliveryDate = $('#deliveryDate').val();
        const deliveryNotes = $('#deliveryNotes').val();

        console.log('Sending delivery update:', {
            request_id: requestId,
            delivery_status: deliveryStatus,
            delivery_date: deliveryDate,
            delivery_notes: deliveryNotes
        });

        $.ajax({
            url: 'update_delivery_status.php',
            method: 'POST',
            data: {
                request_id: requestId,
                delivery_status: deliveryStatus,
                delivery_date: deliveryDate,
                delivery_notes: deliveryNotes
            },
            success: function(response) {
                console.log(response);
                if (response.success) {
                    $('#deliveryModal').modal('hide');
                    updateDashboard(); // Refresh the dashboard
                    // Show success message using SweetAlert
                    Swal.fire({
                        icon: 'success',
                        title: 'Success!',
                        text: 'Delivery status updated successfully',
                        confirmButtonColor: '#8B4543'
                    });
                } else {
                    // Show error message using SweetAlert
                    Swal.fire({
                        icon: 'error',
                        title: 'Error!',
                        text: response.message || 'Error updating delivery status',
                        confirmButtonColor: '#8B4543'
                    });
                }
            },
            error: function(xhr, status, error) {
                console.error('AJAX Error:', xhr.responseText);
                let errorMessage = 'Failed to update delivery status. Please try again.';
                
                // Try to parse error response
                try {
                    const response = JSON.parse(xhr.responseText);
                    if (response.message) {
                        errorMessage = response.message;
                    }
                } catch (e) {
                    // If parsing fails, use default message
                }
                
                // Show error message using SweetAlert
                Swal.fire({
                    icon: 'error',
                    title: 'Error!',
                    text: errorMessage,
                    confirmButtonColor: '#8B4543'
                });
            }
        });
    });
});
</script>

<!-- Adjust Stock Modal -->
<div class="modal fade" id="adjustStockModal" tabindex="-1" aria-labelledby="adjustStockModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-body" id="adjustStockModalBody">
                <!-- AJAX-loaded content here -->
            </div>
        </div>
    </div>
</div>

<!-- View Details Modal -->
<div class="modal fade" id="viewDetailsModal" tabindex="-1" aria-labelledby="viewDetailsModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-xl">
        <div class="modal-content">
            <div class="modal-body" id="viewDetailsModalBody">
                <!-- AJAX-loaded content here -->
            </div>
        </div>
    </div>
</div>

<!-- Request Stock Modal -->
<div class="modal fade" id="requestStockModal" tabindex="-1" aria-labelledby="requestStockModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-body" id="requestStockModalBody">
                <!-- AJAX-loaded content here -->
            </div>
        </div>
    </div>
</div>

<!-- Delivery Status Update Modal -->
<div class="modal fade" id="deliveryModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">
                    <i class="fas fa-truck me-1"></i>
                    Update Delivery Status
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <form id="deliveryForm">
                    <input type="hidden" id="deliveryRequestId">
                    <div class="mb-3">
                        <label class="form-label">Delivery Status</label>
                        <select class="form-select" id="deliveryStatus">
                            <option value="pending">Pending</option>
                            <option value="on_delivery">On Delivery</option>
                            <option value="delivered">Delivered</option>
                            <option value="returned">Returned</option>
                            <option value="cancelled">Cancelled</option>
                        </select>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Delivery Date</label>
                        <input type="datetime-local" class="form-control" id="deliveryDate">
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Delivery Notes</label>
                        <textarea class="form-control" id="deliveryNotes" rows="3" placeholder="Enter delivery notes, return reasons, or cancellation details..."></textarea>
                    </div>
                </form>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                <button type="button" class="btn btn-info" id="updateDelivery">
                    <i class="fas fa-save me-1"></i>
                    Update Delivery
                </button>
            </div>
        </div>
    </div>
</div>

<?php include('footer.php'); ?> 