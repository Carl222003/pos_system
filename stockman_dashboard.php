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
/* Enhanced Stockman Dashboard Styles */
.stockman-dashboard-bg {
    background: linear-gradient(135deg, #f8f5f5 0%, #f0f0f0 100%);
    min-height: 100vh;
    padding-bottom: 2rem;
}

.stockman-section-title {
    color: #8B4543;
    font-size: 2.5rem;
    font-weight: 800;
    letter-spacing: 0.5px;
    margin-bottom: 2rem;
    margin-top: 1.5rem;
    display: flex;
    align-items: center;
    gap: 1rem;
    position: relative;
    background: none;
    border: none;
    animation: fadeInDown 0.8s ease-out;
}

.stockman-section-title .section-icon {
    font-size: 2.5rem;
    color: #8B4543;
    opacity: 0.9;
    background: linear-gradient(135deg, #8B4543 0%, #b97a6a 100%);
    -webkit-background-clip: text;
    -webkit-text-fill-color: transparent;
    background-clip: text;
}

.stockman-section-title::after {
    content: '';
    display: block;
    position: absolute;
    left: 0;
    bottom: -10px;
    width: 100%;
    height: 6px;
    border-radius: 4px;
    background: linear-gradient(90deg, #8B4543 0%, #b97a6a 50%, #8B4543 100%);
    opacity: 0.25;
    animation: slideInLeft 1s ease-out 0.5s both;
}

/* Enhanced Overview Cards */
.stockman-overview-cards {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(280px, 1fr));
    gap: 2rem;
    margin-bottom: 3rem;
    animation: fadeInUp 0.8s ease-out 0.3s both;
}

.stockman-overview-card {
    background: linear-gradient(135deg, #ffffff 0%, #f8f9fa 100%);
    border-radius: 1.5rem;
    box-shadow: 0 8px 32px rgba(139, 69, 67, 0.1);
    padding: 2rem;
    display: flex;
    align-items: center;
    gap: 1.5rem;
    border-left: 8px solid #8B4543;
    position: relative;
    transition: all 0.3s ease;
    overflow: hidden;
}

.stockman-overview-card::before {
    content: '';
    position: absolute;
    top: 0;
    left: -100%;
    width: 100%;
    height: 100%;
    background: linear-gradient(90deg, transparent, rgba(139, 69, 67, 0.05), transparent);
    transition: left 0.6s ease;
}

.stockman-overview-card:hover::before {
    left: 100%;
}

.stockman-overview-card:hover {
    transform: translateY(-8px);
    box-shadow: 0 16px 48px rgba(139, 69, 67, 0.15);
    border-left-color: #b97a6a;
}

.stockman-overview-card .icon {
    font-size: 3rem;
    color: #8B4543;
    opacity: 0.9;
    transition: all 0.3s ease;
}

.stockman-overview-card:hover .icon {
    transform: scale(1.1);
    color: #b97a6a;
}

.stockman-overview-card .card-content {
    flex: 1;
    display: flex;
    flex-direction: column;
    gap: 0.5rem;
}

.stockman-overview-card .card-title {
    font-size: 1.1rem;
    color: #6c757d;
    font-weight: 600;
    margin: 0;
    text-transform: uppercase;
    letter-spacing: 0.5px;
}

.stockman-overview-card .card-value {
    font-size: 2.5rem;
    font-weight: 800;
    color: #8B4543;
    margin: 0;
    line-height: 1;
}

/* Enhanced Cards */
.stockman-card {
    background: #fff;
    border-radius: 1.5rem;
    box-shadow: 0 8px 32px rgba(139, 69, 67, 0.08);
    margin-bottom: 2rem;
    border: none;
    overflow: hidden;
    transition: all 0.3s ease;
}

.stockman-card:hover {
    transform: translateY(-4px);
    box-shadow: 0 12px 40px rgba(139, 69, 67, 0.12);
}

.stockman-card .card-header {
    background: linear-gradient(135deg, #8B4543 0%, #b97a6a 100%);
    color: #fff;
    border-radius: 0;
    font-weight: 700;
    font-size: 1.2rem;
    padding: 1.5rem 2rem;
    border-bottom: none;
    display: flex;
    align-items: center;
    gap: 0.8rem;
}

.stockman-card .card-header i {
    font-size: 1.3rem;
    opacity: 0.9;
}

.stockman-card .card-body {
    padding: 2rem;
}

/* Enhanced Tables */
.table {
    margin-bottom: 0;
    border-collapse: separate;
    border-spacing: 0 8px;
}

.table thead th {
    background: linear-gradient(135deg, #f8f9fa 0%, #e9ecef 100%);
    border: none;
    font-weight: 700;
    text-transform: uppercase;
    font-size: 0.8rem;
    letter-spacing: 1px;
    color: #8B4543;
    padding: 1.2rem 1rem;
    white-space: nowrap;
    position: relative;
}

.table thead th::after {
    content: '';
    position: absolute;
    bottom: 0;
    left: 50%;
    transform: translateX(-50%);
    width: 60%;
    height: 3px;
    background: linear-gradient(90deg, #8B4543 0%, #b97a6a 100%);
    border-radius: 2px;
    opacity: 0.3;
}

.table tbody tr {
    background: white;
    box-shadow: 0 4px 12px rgba(139, 69, 67, 0.05);
    transition: all 0.3s ease;
    border-radius: 12px;
}

.table tbody tr:hover {
    transform: translateY(-2px);
    box-shadow: 0 8px 24px rgba(139, 69, 67, 0.1);
    background: linear-gradient(135deg, #ffffff 0%, #f8f9fa 100%);
}

.table tbody td {
    padding: 1.2rem 1rem;
    border: none;
    background: transparent;
    vertical-align: middle;
}

.table tbody tr td:first-child {
    border-top-left-radius: 12px;
    border-bottom-left-radius: 12px;
}

.table tbody tr td:last-child {
    border-top-right-radius: 12px;
    border-bottom-right-radius: 12px;
}

/* Enhanced Status Badges */
.badge {
    font-size: 0.75rem;
    padding: 0.6rem 1rem;
    border-radius: 20px;
    font-weight: 700;
    text-transform: uppercase;
    letter-spacing: 0.5px;
    box-shadow: 0 2px 8px rgba(0, 0, 0, 0.1);
}

.badge.bg-warning {
    background: linear-gradient(135deg, #ffc107 0%, #ffb300 100%) !important;
    color: #212529;
}

.badge.bg-success {
    background: linear-gradient(135deg, #28a745 0%, #20c997 100%) !important;
    color: white;
}

.badge.bg-danger {
    background: linear-gradient(135deg, #dc3545 0%, #c82333 100%) !important;
    color: white;
}

.badge.bg-secondary {
    background: linear-gradient(135deg, #6c757d 0%, #5a6268 100%) !important;
    color: white;
}

.badge.bg-info {
    background: linear-gradient(135deg, #17a2b8 0%, #138496 100%) !important;
    color: white;
}

/* Enhanced Buttons */
.btn-maroon {
    background: linear-gradient(135deg, #8B4543 0%, #b97a6a 100%);
    border: none;
    color: white;
    border-radius: 12px;
    padding: 0.75rem 1.5rem;
    font-weight: 600;
    transition: all 0.3s ease;
    box-shadow: 0 4px 16px rgba(139, 69, 67, 0.2);
}

.btn-maroon:hover {
    background: linear-gradient(135deg, #7a3d3b 0%, #a65d5d 100%);
    color: white;
    transform: translateY(-2px);
    box-shadow: 0 8px 24px rgba(139, 69, 67, 0.3);
}

.btn-group .btn {
    border-radius: 8px;
    margin: 0 0.2rem;
    transition: all 0.3s ease;
}

.btn-group .btn:hover {
    transform: translateY(-1px);
    box-shadow: 0 4px 12px rgba(0, 0, 0, 0.15);
}

/* Enhanced Chart Container */
#stockStatusChart {
    border-radius: 12px;
    background: linear-gradient(135deg, #f8f9fa 0%, #e9ecef 100%);
    padding: 1rem;
}

/* Loading States */
.loading-overlay {
    position: absolute;
    top: 0;
    left: 0;
    right: 0;
    bottom: 0;
    background: rgba(255, 255, 255, 0.9);
    display: flex;
    align-items: center;
    justify-content: center;
    border-radius: 1.5rem;
    z-index: 10;
}

.loading-spinner {
    width: 40px;
    height: 40px;
    border: 4px solid #f3f3f3;
    border-top: 4px solid #8B4543;
    border-radius: 50%;
    animation: spin 1s linear infinite;
}

/* Animations */
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

@keyframes slideInLeft {
    from {
        width: 0;
        opacity: 0;
    }
    to {
        width: 100%;
        opacity: 0.25;
    }
}

@keyframes spin {
    0% { transform: rotate(0deg); }
    100% { transform: rotate(360deg); }
}

/* Responsive Design */
@media (max-width: 1200px) {
    .stockman-overview-cards {
        grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
        gap: 1.5rem;
    }
}

@media (max-width: 768px) {
    .stockman-section-title {
        font-size: 2rem;
        flex-direction: column;
        text-align: center;
        gap: 0.5rem;
    }
    
    .stockman-overview-cards {
        grid-template-columns: 1fr;
        gap: 1rem;
    }
    
    .stockman-overview-card {
        padding: 1.5rem;
    }
    
    .stockman-overview-card .card-value {
        font-size: 2rem;
    }
    
    .stockman-card .card-body {
        padding: 1.5rem;
    }
    
    .table-responsive {
        border-radius: 12px;
        overflow: hidden;
    }
}

/* DataTables Customization */
.dataTables_wrapper {
    padding: 0;
}

.dataTables_filter input {
    border: 2px solid #e9ecef;
    border-radius: 8px;
    padding: 0.5rem 1rem;
    transition: all 0.3s ease;
}

.dataTables_filter input:focus {
    border-color: #8B4543;
    box-shadow: 0 0 0 0.2rem rgba(139, 69, 67, 0.25);
    outline: none;
}

.dataTables_length select {
    border: 2px solid #e9ecef;
    border-radius: 8px;
    padding: 0.5rem 2rem 0.5rem 1rem;
    transition: all 0.3s ease;
}

.dataTables_length select:focus {
    border-color: #8B4543;
    box-shadow: 0 0 0 0.2rem rgba(139, 69, 67, 0.25);
    outline: none;
}

.dataTables_paginate .paginate_button {
    border-radius: 8px;
    margin: 0 0.2rem;
    transition: all 0.3s ease;
}

.dataTables_paginate .paginate_button.current {
    background: linear-gradient(135deg, #8B4543 0%, #b97a6a 100%);
    border-color: #8B4543;
}

.dataTables_paginate .paginate_button:hover:not(.current) {
    background: #f8f9fa;
    border-color: #8B4543;
    color: #8B4543 !important;
}

/* Empty State Styling */
.empty-state {
    text-align: center;
    padding: 3rem 2rem;
    color: #6c757d;
}

.empty-state i {
    font-size: 4rem;
    color: #dee2e6;
    margin-bottom: 1rem;
}

.empty-state h5 {
    color: #8B4543;
    margin-bottom: 0.5rem;
}

.empty-state p {
    color: #6c757d;
    margin-bottom: 0;
}
</style>

<div class="stockman-dashboard-bg">
    <div class="container-fluid px-4">
        <div class="stockman-section-title">
            <span class="section-icon"><i class="fas fa-clipboard-list"></i></span>
            Stock Management Dashboard
        </div>
        
        <!-- Overview Cards -->
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
        
        <!-- Main Content Row -->
        <div class="row">
            <!-- Stock Inventory Table -->
            <div class="col-xl-8">
                <div class="stockman-card mb-4">
                    <div class="card-header">
                        <i class="fas fa-boxes me-1"></i>
                        Stock Inventory
                    </div>
                    <div class="card-body position-relative">
                        <div class="loading-overlay" id="stockLoadingOverlay" style="display: none;">
                            <div class="loading-spinner"></div>
                        </div>
                        <div class="table-responsive">
                            <table class="table table-hover" id="stockTable">
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
            
            <!-- Stock Status Chart -->
            <div class="col-xl-4">
                <div class="stockman-card mb-4">
                    <div class="card-header">
                        <i class="fas fa-chart-pie me-1"></i>
                        Stock Status Overview
                    </div>
                    <div class="card-body">
                        <canvas id="stockStatusChart" width="100%" height="300"></canvas>
                    </div>
                </div>
            </div>
        </div>
        
        <!-- Request Stock Updates Table -->
        <div class="row">
            <div class="col-12">
                <div class="stockman-card mb-4">
                    <div class="card-header">
                        <i class="fas fa-clipboard-check me-1"></i>
                        Request Stock Updates
                    </div>
                    <div class="card-body position-relative">
                        <div class="loading-overlay" id="requestsLoadingOverlay" style="display: none;">
                            <div class="loading-spinner"></div>
                        </div>
                        <div class="table-responsive">
                            <table class="table table-hover" id="requestsTable">
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
                                        <button class="btn btn-sm btn-outline-secondary" onclick="viewStockMovements(${item.id})" title="View Stock History">
                                            <i class="fas fa-history"></i>
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
                        // Add delivery update button for approved requests that are not delivered yet
                        let actionButton = '';
                        if (request.status.includes('APPROVED') && request.delivery_status_raw !== 'delivered') {
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
        $('#adjustStockModalBody').html('<div class="text-center p-4"><div class="loading-spinner"></div><p class="mt-2">Loading adjustment form...</p></div>');
        $('#adjustStockModal').modal('show');
        $.get('adjust_stock_modal.php', { id: ingredientId }, function(data) {
            $('#adjustStockModalBody').html(data);
        });
    };

    window.viewStockMovements = function(ingredientId) {
        // Show stock movements modal
        $('#stockMovementsModalBody').html('<div class="text-center p-4"><div class="loading-spinner"></div><p class="mt-2">Loading stock history...</p></div>');
        $('#stockMovementsModal').modal('show');
        $.get('stock_movements_modal.php', { id: ingredientId }, function(data) {
            $('#stockMovementsModalBody').html(data);
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
        
        // Set current date/time as default and minimum date
        const now = new Date();
        const currentDateTime = now.toISOString().slice(0, 16); // Format: YYYY-MM-DDTHH:MM
        $('#deliveryDate').val(currentDateTime);
        $('#deliveryDate').attr('min', currentDateTime);
        
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
    
    $('#stockMovementsModal').on('hidden.bs.modal', function () {
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
        <div class="modal-content modal-no-padding">
            <div class="modal-body modal-body-no-padding" id="adjustStockModalBody">
                <!-- AJAX-loaded content here -->
            </div>
        </div>
    </div>
</div>

<!-- View Details Modal -->
<div class="modal fade" id="viewDetailsModal" tabindex="-1" aria-labelledby="viewDetailsModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-xl">
        <div class="modal-content modal-no-padding">
            <div class="modal-body modal-body-no-padding" id="viewDetailsModalBody">
                <!-- AJAX-loaded content here -->
            </div>
        </div>
    </div>
</div>

<style>
.modal-xl {
    max-width: 1000px;
    width: 1000px;
}

.modal-no-padding {
    border-radius: 20px;
    overflow: hidden;
    border: none;
    box-shadow: 0 20px 60px rgba(0, 0, 0, 0.15);
}

.modal-body-no-padding {
    padding: 0 !important;
    border-radius: 20px;
}

@media (max-width: 1200px) {
    .modal-xl {
        max-width: 90vw;
        width: 90vw;
    }
}

@media (max-width: 768px) {
    .modal-xl {
        max-width: 95vw;
        width: 95vw;
        margin: 1rem auto;
    }
}
</style>

<!-- Request Stock Modal -->
<div class="modal fade" id="requestStockModal" tabindex="-1" aria-labelledby="requestStockModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-request-landscape">
        <div class="modal-content request-landscape-content">
            <div class="modal-body modal-body-no-padding" id="requestStockModalBody">
                <!-- AJAX-loaded content here -->
            </div>
        </div>
    </div>
</div>

<style>
.modal-request-landscape {
    max-width: 900px;
    width: 900px;
    margin: 0 auto;
    display: flex;
    align-items: center;
    justify-content: center;
    min-height: calc(100vh - 3.5rem);
}

.request-landscape-content {
    border-radius: 20px;
    overflow: hidden;
    border: none;
    box-shadow: 0 20px 60px rgba(0, 0, 0, 0.15);
}

@media (max-width: 1200px) {
    .modal-request-landscape {
        max-width: 85vw;
        width: 85vw;
    }
}

@media (max-width: 768px) {
    .modal-request-landscape {
        max-width: 95vw;
        width: 95vw;
        min-height: calc(100vh - 2rem);
    }
}
</style>

<!-- Stock Movements Modal -->
<div class="modal fade" id="stockMovementsModal" tabindex="-1" aria-labelledby="stockMovementsModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-xl">
        <div class="modal-content">
            <div class="modal-body" id="stockMovementsModalBody">
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