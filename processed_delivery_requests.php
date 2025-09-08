<?php
require_once 'db_connect.php';
require_once 'auth_function.php';

checkAdminLogin();

$confData = getConfigData($pdo);

include('header.php');
?>

<style>
/* Modern Card and Table Styling */
:root {
    --primary-color: #8B4543;
    --primary-dark: #723937;
    --primary-light: #A65D5D;
    --accent-color: #D4A59A;
    --text-light: #F3E9E7;
    --text-dark: #3C2A2A;
    --border-color: #C4B1B1;
    --hover-color: #F5EDED;
    --danger-color: #B33A3A;
    --success-color: #4A7C59;
    --warning-color: #C4804D;
}

.card {
    box-shadow: 0 0.15rem 1.75rem 0 rgba(139, 69, 67, 0.15);
    border: none;
    border-radius: 0.75rem;
    background: #ffffff;
}

.card-header {
    background: var(--primary-color);
    color: var(--text-light);
    border-bottom: none;
    padding: 1.5rem;
    border-radius: 0.75rem 0.75rem 0 0;
}

.card-header i {
    color: var(--text-light);
}

.card-header h5 {
    color: var(--text-light);
    margin: 0;
}

.table {
    margin-bottom: 0;
    border-collapse: separate;
    border-spacing: 0;
}

.table thead th {
    background: linear-gradient(135deg, #f8f9fa 0%, #e9ecef 100%);
    color: var(--text-dark);
    font-weight: 600;
    border: none;
    padding: 1rem 0.75rem;
    border-bottom: 2px solid var(--border-color);
}

.table tbody tr {
    transition: all 0.2s ease;
}

.table tbody tr:hover {
    background: var(--hover-color);
    transform: translateY(-1px);
    box-shadow: 0 2px 8px rgba(139, 69, 67, 0.1);
}

.table tbody tr td {
    border: none;
    border-bottom: 1px solid rgba(139, 69, 67, 0.1);
    padding: 1rem 0.75rem;
    vertical-align: middle;
}

.table tbody tr:first-child td {
    border-top-left-radius: 0.5rem;
    border-top-right-radius: 0.5rem;
}

.table tbody tr:last-child td {
    border-bottom-left-radius: 0.5rem;
    border-bottom-right-radius: 0.5rem;
}

.table tbody tr td:first-child {
    border-top-left-radius: 0.5rem;
    border-bottom-left-radius: 0.5rem;
}

.table tbody tr td:last-child {
    border-top-right-radius: 0.5rem;
    border-bottom-right-radius: 0.5rem;
}

/* Table Section Styles */
.table-section {
    margin-bottom: 2rem;
}

.table-section h5 {
    font-weight: 600;
    margin-bottom: 1rem;
    padding-bottom: 0.5rem;
    border-bottom: 2px solid #e9ecef;
}

.table-section h5 i {
    margin-right: 0.5rem;
}

.processed-section h5 {
    color: #6c757d;
    border-bottom-color: #6c757d;
}

/* Filter Chips Styles */
.filter-chip {
    display: inline-flex;
    align-items: center;
    gap: 0.5rem;
    padding: 0.375rem 0.75rem;
    background: var(--primary-color);
    color: white;
    border-radius: 1.5rem;
    font-size: 0.875rem;
    font-weight: 500;
    margin: 0.25rem;
    box-shadow: 0 2px 4px rgba(139, 69, 67, 0.2);
}

.filter-chip .remove-filter {
    background: rgba(255, 255, 255, 0.2);
    border: none;
    color: white;
    border-radius: 50%;
    width: 20px;
    height: 20px;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 0.75rem;
    cursor: pointer;
    transition: all 0.2s ease;
}

.filter-chip .remove-filter:hover {
    background: rgba(255, 255, 255, 0.3);
    transform: scale(1.1);
}

/* Enhanced Filter Panel */
.enhanced-filter-panel {
    background: linear-gradient(135deg, #ffffff 0%, #f8f9fa 100%);
    border: 1px solid var(--border-color);
    border-radius: 1rem;
    padding: 1.5rem;
    margin-bottom: 1.5rem;
    box-shadow: 0 4px 20px rgba(139, 69, 67, 0.1);
}

.filter-section {
    margin-bottom: 1.5rem;
}

.filter-section:last-child {
    margin-bottom: 0;
}

.filter-section h6 {
    color: var(--primary-color);
    font-weight: 600;
    margin-bottom: 0.75rem;
    display: flex;
    align-items: center;
}

.filter-section h6 i {
    margin-right: 0.5rem;
    font-size: 1rem;
}

.form-control, .form-select {
    border: 1px solid var(--border-color);
    border-radius: 0.5rem;
    padding: 0.75rem;
    transition: all 0.2s ease;
}

.form-control:focus, .form-select:focus {
    border-color: var(--primary-color);
    box-shadow: 0 0 0 0.2rem rgba(139, 69, 67, 0.25);
}

.btn-primary {
    background: linear-gradient(135deg, var(--primary-color) 0%, var(--primary-dark) 100%);
    border: none;
    border-radius: 0.5rem;
    padding: 0.75rem 1.5rem;
    font-weight: 600;
    transition: all 0.3s ease;
}

.btn-primary:hover {
    transform: translateY(-2px);
    box-shadow: 0 4px 15px rgba(139, 69, 67, 0.3);
    background: linear-gradient(135deg, var(--primary-dark) 0%, #5a2e2c 100%);
}

.btn-secondary {
    background: linear-gradient(135deg, #6c757d 0%, #5a6268 100%);
    border: none;
    border-radius: 0.5rem;
    padding: 0.75rem 1.5rem;
    font-weight: 600;
    transition: all 0.3s ease;
}

.btn-secondary:hover {
    transform: translateY(-2px);
    box-shadow: 0 4px 15px rgba(108, 117, 125, 0.3);
    background: linear-gradient(135deg, #5a6268 0%, #495057 100%);
}

/* Badge Styles */
.badge {
    font-size: 0.75rem;
    font-weight: 600;
    padding: 0.5rem 0.75rem;
    border-radius: 0.5rem;
}

.bg-success {
    background: linear-gradient(135deg, #28a745 0%, #20c997 100%) !important;
}

.bg-danger {
    background: linear-gradient(135deg, #dc3545 0%, #e83e8c 100%) !important;
}

.bg-warning {
    background: linear-gradient(135deg, #ffc107 0%, #fd7e14 100%) !important;
}

.bg-info {
    background: linear-gradient(135deg, #17a2b8 0%, #6f42c1 100%) !important;
}

.bg-secondary {
    background: linear-gradient(135deg, #6c757d 0%, #495057 100%) !important;
}

/* Responsive Design */
@media (max-width: 768px) {
    .card-header {
        padding: 1rem;
    }
    
    .table thead th,
    .table tbody tr td {
        padding: 0.75rem 0.5rem;
        font-size: 0.875rem;
    }
    
    .enhanced-filter-panel {
        padding: 1rem;
    }
}
</style>

<div class="container-fluid px-4">
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-header">
                    <div class="d-flex justify-content-between align-items-center">
                        <div class="d-flex align-items-center">
                            <i class="fas fa-check-circle me-3"></i>
                            <h5 class="mb-0">Processed Delivery Requests</h5>
                        </div>
                        <div class="d-flex align-items-center gap-3">
                            <div class="search-container">
                                <label for="searchInput" class="form-label mb-0 me-2">Search:</label>
                                <input type="text" id="searchInput" class="form-control" placeholder="Search requests...">
                            </div>
                            <button class="btn btn-primary" id="ingredientFilterBtn">
                                <i class="fas fa-filter me-2"></i>Filter
                            </button>
                            <button class="btn btn-warning" id="testButton" onclick="alert('Test button works!')">
                                <i class="fas fa-bug me-2"></i>Test
                            </button>
                        </div>
                    </div>
                </div>
                <div class="card-body">
                    <!-- Processed Requests Table -->
                    <div class="table-section processed-section">
                        <h5>
                            <i class="fas fa-check-circle"></i>Processed Delivery Requests
                        </h5>
                        <div class="table-responsive">
                            <table id="processedRequestsTable" class="table table-bordered table-hover">
                                <thead>
                                    <tr>
                                        <th>Branch</th>
                                        <th>Date Requested</th>
                                        <th>Ingredients</th>
                                        <th>Request Status</th>
                                        <th>Delivery Status</th>
                                        <th>Notes</th>
                                        <th>Updated By</th>
                                        <th>Actions</th>
                                    </tr>
                                </thead>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Enhanced Filter Panel -->
<div class="enhanced-filter-panel" id="ingredientFilterPanel" style="display: none;">
    <div class="row">
        <div class="col-md-3">
            <div class="filter-section">
                <h6><i class="fas fa-building"></i>Branch</h6>
                <select class="form-select" id="filterBranchSelect">
                    <option value="">All Branches</option>
                    <?php
                    $branchStmt = $pdo->query("SELECT branch_id, branch_name FROM pos_branch WHERE status = 'active' ORDER BY branch_name");
                    while ($branch = $branchStmt->fetch(PDO::FETCH_ASSOC)) {
                        echo "<option value='{$branch['branch_id']}'>{$branch['branch_name']}</option>";
                    }
                    ?>
                </select>
            </div>
        </div>
        <div class="col-md-3">
            <div class="filter-section">
                <h6><i class="fas fa-tasks"></i>Request Status</h6>
                <select class="form-select" id="filterStatusSelect">
                    <option value="">All Statuses</option>
                    <option value="pending">Pending</option>
                    <option value="approved">Approved</option>
                    <option value="rejected">Rejected</option>
                </select>
            </div>
        </div>
        <div class="col-md-3">
            <div class="filter-section">
                <h6><i class="fas fa-carrot"></i>Ingredient</h6>
                <select class="form-select" id="filterIngredientSelect">
                    <option value="">All Ingredients</option>
                    <?php
                    $ingredientStmt = $pdo->query("SELECT ingredient_id, ingredient_name FROM ingredients WHERE ingredient_status = 'active' ORDER BY ingredient_name");
                    while ($ingredient = $ingredientStmt->fetch(PDO::FETCH_ASSOC)) {
                        echo "<option value='{$ingredient['ingredient_id']}'>{$ingredient['ingredient_name']}</option>";
                    }
                    ?>
                </select>
            </div>
        </div>
        <div class="col-md-3">
            <div class="filter-section">
                <h6><i class="fas fa-truck"></i>Delivery Status</h6>
                <select class="form-select" id="filterDeliveryStatusSelect">
                    <option value="">All Delivery Statuses</option>
                    <option value="on_delivery">On Delivery</option>
                    <option value="delivered">Delivered</option>
                    <option value="returned">Returned</option>
                    <option value="cancelled">Cancelled</option>
                </select>
            </div>
        </div>
    </div>
    <div class="row">
        <div class="col-md-6">
            <div class="filter-section">
                <h6><i class="fas fa-calendar"></i>Date Range</h6>
                <select class="form-select" id="filterDateSelect">
                    <option value="">All Dates</option>
                    <option value="today">Today</option>
                    <option value="yesterday">Yesterday</option>
                    <option value="this_week">This Week</option>
                    <option value="this_month">This Month</option>
                    <option value="custom">Custom Range</option>
                </select>
                <div id="customDateRange" style="display: none; margin-top: 1rem;">
                    <div class="row">
                        <div class="col-6">
                            <label class="form-label">From:</label>
                            <input type="date" class="form-control" id="filterDateFrom">
                        </div>
                        <div class="col-6">
                            <label class="form-label">To:</label>
                            <input type="date" class="form-control" id="filterDateTo">
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <div class="d-flex justify-content-end gap-2">
        <button class="btn btn-secondary" id="clearFiltersBtn">
            <i class="fas fa-times me-2"></i>Clear All
        </button>
        <button class="btn btn-primary" id="applyFiltersBtn">
            <i class="fas fa-check me-2"></i>Apply Filters
        </button>
    </div>
</div>

<!-- Active Filters Display -->
<div id="activeFiltersContainer" class="mb-3" style="display: none;">
    <h6 class="mb-2">Active Filters:</h6>
    <div id="activeFilters"></div>
</div>

<script>
$(document).ready(function() {
    // Initialize Processed Requests DataTable
    const processedTable = $('#processedRequestsTable').DataTable({
        processing: true,
        serverSide: false, // Client-side processing
        pageLength: 5, // Show only 5 records per page to force pagination
        lengthMenu: [[5, 10, 25, 50], [5, 10, 25, 50]], // Available page lengths
        paging: true, // Ensure pagination is enabled
        pagingType: 'simple', // Show only Previous/Next buttons
        ordering: false, // Disable client-side sorting since we sort on server
        ajax: {
            url: 'ingredient_requests_ajax.php',
            type: 'POST',
            data: function(d) {
                d.branch = $('#filterBranchSelect').val();
                d.status = $('#filterStatusSelect').val();
                d.ingredient = $('#filterIngredientSelect').val();
                d.delivery_status = 'processed'; // Show all non-pending requests
                d.date_filter = $('#filterDateSelect').val();
                d.date_from = $('#filterDateFrom').val();
                d.date_to = $('#filterDateTo').val();
                d.table_type = 'processed'; // Add table type identifier
                console.log('Processed DataTable AJAX request:', d);
            },
            dataSrc: function(json) {
                console.log('Processed DataTable AJAX response:', json);
                console.log('Total records:', json.recordsTotal);
                console.log('Filtered records:', json.recordsFiltered);
                console.log('Data length:', json.data ? json.data.length : 0);
                console.log('Sample data:', json.data ? json.data[0] : 'No data');
                return json.data || [];
            },
            error: function(xhr, error, thrown) {
                console.error('Processed DataTable AJAX error:', error, thrown);
                console.error('Response:', xhr.responseText);
            }
        },
        columns: [
            { data: 'branch_name' },
            { 
                data: 'request_date',
                render: function(data) {
                    const date = new Date(data);
                    return date.toLocaleDateString() + ' ' + date.toLocaleTimeString();
                },
                type: 'datetime'
            },
            { data: 'ingredients' },
            {
                data: 'status',
                render: function(data) {
                    const statusClasses = {
                        'pending': 'bg-warning',
                        'approved': 'bg-success',
                        'rejected': 'bg-danger'
                    };
                    return `<span class="badge ${statusClasses[data]}">${data.toUpperCase()}</span>`;
                }
            },
            {
                data: 'delivery_status',
                render: function(data) {
                    const deliveryStatusClasses = {
                        'pending': 'bg-secondary',
                        'on_delivery': 'bg-info',
                        'delivered': 'bg-success',
                        'returned': 'bg-warning',
                        'cancelled': 'bg-danger'
                    };
                    const deliveryStatusText = {
                        'pending': 'PENDING',
                        'on_delivery': 'ON DELIVERY',
                        'delivered': 'DELIVERED',
                        'returned': 'RETURNED',
                        'cancelled': 'CANCELLED'
                    };
                    return `<span class="badge ${deliveryStatusClasses[data] || 'bg-secondary'}">${deliveryStatusText[data] || 'PENDING'}</span>`;
                }
            },
            {
                data: 'delivery_notes',
                render: function(data) {
                    if (data && data.trim() !== '') {
                        // Truncate long notes and show full text on hover
                        const truncatedText = data.length > 50 ? data.substring(0, 50) + '...' : data;
                        return `<span title="${data.replace(/"/g, '&quot;')}" style="cursor: help;">${truncatedText}</span>`;
                    } else {
                        return '<span class="text-muted">-</span>';
                    }
                }
            },
            { 
                data: 'updated_by',
                render: function(data) {
                    return data || 'N/A';
                }
            },
            {
                data: null,
                render: function(data) {
                    console.log('Rendering action buttons for request:', data);
                    let buttons = '';
                    // For processed requests, only show view button (no edit actions)
                    buttons += `<button class="btn btn-info btn-sm view-request me-1" data-id="${data.request_id}" title="View Details" onclick="console.log('Button clicked directly!', ${data.request_id})">
                        <i class="fas fa-eye"></i>
                    </button>`;
                    console.log('Generated button HTML:', buttons);
                    return buttons;
                }
            }
        ],
        order: [[1, 'desc']]
    });

    // Filter panel logic
    (function() {
        var filterBtn = document.getElementById('ingredientFilterBtn');
        var filterPanel = document.getElementById('ingredientFilterPanel');
        var applyBtn = document.getElementById('applyFiltersBtn');
        var clearBtn = document.getElementById('clearFiltersBtn');
        var dateSelect = document.getElementById('filterDateSelect');
        var customDateRange = document.getElementById('customDateRange');

        // Toggle filter panel
        filterBtn.addEventListener('click', function() {
            if (filterPanel.style.display === 'none' || filterPanel.style.display === '') {
                filterPanel.style.display = 'block';
            } else {
                filterPanel.style.display = 'none';
            }
        });

        // Show/hide custom date range
        dateSelect.addEventListener('change', function() {
            if (this.value === 'custom') {
                customDateRange.style.display = 'block';
            } else {
                customDateRange.style.display = 'none';
            }
        });

        // Apply filter
        applyBtn.addEventListener('click', function() {
            // Reload the DataTable with new filter values
            $('#processedRequestsTable').DataTable().ajax.reload();
            filterPanel.style.display = 'none';
            
            // Show active filter indicator
            updateActiveFilterIndicator();
        });

        // Clear filters
        clearBtn.addEventListener('click', function() {
            // Reset all filter values
            $('#filterBranchSelect').val('');
            $('#filterStatusSelect').val('');
            $('#filterIngredientSelect').val('');
            $('#filterDeliveryStatusSelect').val('');
            $('#filterDateSelect').val('');
            $('#filterDateFrom').val('');
            $('#filterDateTo').val('');
            $('#customDateRange').hide();
            
            // Reload the DataTable to show all data
            $('#processedRequestsTable').DataTable().ajax.reload();
            
            // Close the filter panel
            filterPanel.style.display = 'none';
            
            // Update active filter indicator (this will hide it)
            updateActiveFilterIndicator();
        });
    })();

    // Active filter indicator functions
    function updateActiveFilterIndicator() {
        const activeFilters = [];
        
        // Check each filter
        const branch = $('#filterBranchSelect').val();
        const status = $('#filterStatusSelect').val();
        const ingredient = $('#filterIngredientSelect').val();
        const deliveryStatus = $('#filterDeliveryStatusSelect').val();
        const dateFilter = $('#filterDateSelect').val();
        const dateFrom = $('#filterDateFrom').val();
        const dateTo = $('#filterDateTo').val();
        
        if (branch) {
            const branchName = $('#filterBranchSelect option:selected').text();
            activeFilters.push({ type: 'branch', value: branch, text: branchName });
        }
        
        if (status) {
            activeFilters.push({ type: 'status', value: status, text: status.toUpperCase() });
        }
        
        if (ingredient) {
            const ingredientName = $('#filterIngredientSelect option:selected').text();
            activeFilters.push({ type: 'ingredient', value: ingredient, text: ingredientName });
        }
        
        if (deliveryStatus) {
            const deliveryStatusText = $('#filterDeliveryStatusSelect option:selected').text();
            activeFilters.push({ type: 'delivery_status', value: deliveryStatus, text: deliveryStatusText });
        }
        
        if (dateFilter) {
            if (dateFilter === 'custom' && dateFrom && dateTo) {
                activeFilters.push({ type: 'date', value: 'custom', text: `${dateFrom} to ${dateTo}` });
            } else if (dateFilter !== 'custom') {
                const dateText = $('#filterDateSelect option:selected').text();
                activeFilters.push({ type: 'date', value: dateFilter, text: dateText });
            }
        }
        
        // Update display
        const container = $('#activeFiltersContainer');
        const filtersDiv = $('#activeFilters');
        
        if (activeFilters.length > 0) {
            filtersDiv.empty();
            activeFilters.forEach(filter => {
                const chip = $(`
                    <span class="filter-chip">
                        ${filter.text}
                        <button class="remove-filter" data-filter-type="${filter.type}">
                            <i class="fas fa-times"></i>
                        </button>
                    </span>
                `);
                filtersDiv.append(chip);
            });
            container.show();
        } else {
            container.hide();
        }
    }

    // Remove individual filter
    function removeFilter(filterType) {
        switch(filterType) {
            case 'branch':
                $('#filterBranchSelect').val('');
                break;
            case 'status':
                $('#filterStatusSelect').val('');
                break;
            case 'ingredient':
                $('#filterIngredientSelect').val('');
                break;
            case 'delivery_status':
                $('#filterDeliveryStatusSelect').val('');
                break;
            case 'date':
                $('#filterDateSelect').val('');
                $('#filterDateFrom').val('');
                $('#filterDateTo').val('');
                $('#customDateRange').hide();
                break;
        }
        
        // Update the display and refresh table
        updateActiveFilterIndicator();
        
        // Use the DataTable API to reload
        if ($.fn.DataTable.isDataTable('#processedRequestsTable')) {
            $('#processedRequestsTable').DataTable().ajax.reload();
        }
        
        console.log('✅ Filter removed and table refreshed');
    }

    // Event delegation for remove filter buttons
    $(document).on('click', '.remove-filter', function() {
        const filterType = $(this).data('filter-type');
        removeFilter(filterType);
    });

    // Auto-refresh every 30 seconds to show new requests
    setInterval(function() {
        processedTable.ajax.reload(null, false); // false = stay on current page
    }, 30000);

    // Manual refresh on page focus (when user comes back to tab)
    $(window).focus(function() {
        processedTable.ajax.reload(null, false);
    });

    // View request handler
    $(document).on('click', '.view-request', function(e) {
        e.preventDefault();
        e.stopPropagation();
        
        const requestId = $(this).data('id');
        console.log('View request clicked:', requestId);
        console.log('Button element:', this);
        console.log('Data attributes:', $(this).data());
        
        // Show immediate feedback
        Swal.fire({
            title: 'Loading...',
            text: 'Fetching request details...',
            allowOutsideClick: false,
            showConfirmButton: false,
            willOpen: () => {
                Swal.showLoading();
            }
        });
        
        // Fetch request details and show modal
        fetch('get_ingredient_request_details.php', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
            },
            body: JSON.stringify({ request_id: requestId })
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                showViewRequestModal(data.request);
            } else {
                Swal.fire({
                    icon: 'error',
                    title: 'Error',
                    text: data.message || 'Failed to load request details',
                    confirmButtonColor: '#8B4543'
                });
            }
        })
        .catch(error => {
            console.error('Error fetching request details:', error);
            Swal.fire({
                icon: 'error',
                title: 'Error',
                text: 'Failed to load request details',
                confirmButtonColor: '#8B4543'
            });
        });
    });

    // Function to show view request modal
    function showViewRequestModal(request) {
        const modalHtml = `
            <div class="modal fade" id="viewRequestModal" tabindex="-1">
                <div class="modal-dialog modal-lg modal-dialog-centered">
                    <div class="modal-content">
                        <div class="modal-header bg-primary text-white">
                            <h5 class="modal-title">
                                <i class="fas fa-eye me-2"></i>Request Details
                            </h5>
                            <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                        </div>
                        <div class="modal-body">
                            <div class="row">
                                <div class="col-md-6">
                                    <h6><i class="fas fa-building me-2"></i>Branch</h6>
                                    <p class="text-muted">${request.branch_name || 'N/A'}</p>
                                </div>
                                <div class="col-md-6">
                                    <h6><i class="fas fa-calendar me-2"></i>Request Date</h6>
                                    <p class="text-muted">${new Date(request.request_date).toLocaleString()}</p>
                                </div>
                            </div>
                            <div class="row">
                                <div class="col-md-6">
                                    <h6><i class="fas fa-tasks me-2"></i>Request Status</h6>
                                    <span class="badge ${request.status === 'approved' ? 'bg-success' : request.status === 'rejected' ? 'bg-danger' : 'bg-warning'}">${request.status.toUpperCase()}</span>
                                </div>
                                <div class="col-md-6">
                                    <h6><i class="fas fa-truck me-2"></i>Delivery Status</h6>
                                    <span class="badge ${request.delivery_status === 'delivered' ? 'bg-success' : request.delivery_status === 'cancelled' ? 'bg-danger' : request.delivery_status === 'returned' ? 'bg-warning' : 'bg-info'}">${request.delivery_status.toUpperCase()}</span>
                                </div>
                            </div>
                            <div class="row">
                                <div class="col-md-6">
                                    <h6><i class="fas fa-user me-2"></i>Updated By</h6>
                                    <p class="text-muted">${request.updated_by || 'N/A'}</p>
                                </div>
                                <div class="col-md-6">
                                    <h6><i class="fas fa-clock me-2"></i>Last Updated</h6>
                                    <p class="text-muted">${request.updated_at ? new Date(request.updated_at).toLocaleString() : 'N/A'}</p>
                                </div>
                            </div>
                            <div class="row">
                                <div class="col-12">
                                    <h6><i class="fas fa-carrot me-2"></i>Requested Ingredients</h6>
                                    <div class="table-responsive">
                                        <table class="table table-sm table-bordered">
                                            <thead class="table-light">
                                                <tr>
                                                    <th>Ingredient</th>
                                                    <th>Quantity</th>
                                                    <th>Unit</th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                                ${request.ingredients ? request.ingredients.map(ingredient => `
                                                    <tr>
                                                        <td>${ingredient.ingredient_name || 'Unknown Ingredient'}</td>
                                                        <td>${ingredient.quantity || 'N/A'}</td>
                                                        <td>${ingredient.unit || 'N/A'}</td>
                                                    </tr>
                                                `).join('') : '<tr><td colspan="3" class="text-center text-muted">No ingredients found</td></tr>'}
                                            </tbody>
                                        </table>
                                    </div>
                                </div>
                            </div>
                            ${request.delivery_notes ? `
                            <div class="row">
                                <div class="col-12">
                                    <h6><i class="fas fa-sticky-note me-2"></i>Delivery Notes</h6>
                                    <p class="text-muted">${request.delivery_notes}</p>
                                </div>
                            </div>
                            ` : ''}
                        </div>
                        <div class="modal-footer">
                            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">
                                <i class="fas fa-times me-2"></i>Close
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        `;
        
        // Remove existing modal if any
        $('#viewRequestModal').remove();
        
        // Add modal to body
        $('body').append(modalHtml);
        
        // Show modal
        $('#viewRequestModal').modal('show');
        
        // Remove modal from DOM when hidden
        $('#viewRequestModal').on('hidden.bs.modal', function() {
            $(this).remove();
        });
    }
});
</script>

<?php include('footer.php'); ?>
