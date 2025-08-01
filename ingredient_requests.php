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
    border-spacing: 0 8px;
}

.table thead th {
    background-color: var(--hover-color);
    border: none;
    font-weight: 600;
    text-transform: uppercase;
    font-size: 0.75rem;
    letter-spacing: 0.5px;
    color: var(--primary-color);
    padding: 1rem;
    white-space: nowrap;
}

.table tbody tr {
    background: white;
    box-shadow: 0 2px 4px rgba(139, 69, 67, 0.05);
    transition: transform 0.2s ease, box-shadow 0.2s ease;
}

.table tbody tr:hover {
    transform: translateY(-2px);
    box-shadow: 0 4px 8px rgba(139, 69, 67, 0.1);
    background: var(--hover-color);
}

.table tbody td {
    padding: 1rem;
    border: none;
    background: transparent;
}

.table tbody tr td:first-child {
    border-top-left-radius: 0.5rem;
    border-bottom-left-radius: 0.5rem;
}

.table tbody tr td:last-child {
    border-top-right-radius: 0.5rem;
    border-bottom-right-radius: 0.5rem;
}

/* Search and Length Menu */
.dataTables_wrapper {
    padding: 1.5rem;
}

/* Hide the "Show" dropdown */
.dataTables_length {
    display: none !important;
}

.dataTables_length select {
    padding: 0.5rem 2.5rem 0.5rem 1rem;
    font-size: 0.875rem;
    border: 1px solid var(--border-color);
    border-radius: 0.5rem;
    background-color: white;
    cursor: pointer;
    appearance: none;
    background-image: url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' width='16' height='16' viewBox='0 0 24 24' fill='none' stroke='%238B4543' stroke-width='2' stroke-linecap='round' stroke-linejoin='round'%3E%3Cpolyline points='6 9 12 15 18 9'%3E%3C/polyline%3E%3C/svg%3E");
    background-repeat: no-repeat;
    background-position: right 0.75rem center;
    background-size: 1rem;
    transition: all 0.2s ease;
}

.dataTables_length select:hover {
    border-color: var(--primary-color);
}

.dataTables_filter input {
    padding: 0.5rem 1rem;
    font-size: 0.875rem;
    border: 1px solid var(--border-color);
    border-radius: 0.5rem;
    background-color: white;
    min-width: 300px;
    transition: all 0.2s ease;
}

.dataTables_filter input:focus {
    border-color: var(--primary-color);
    outline: none;
    box-shadow: 0 0 0 0.2rem rgba(139, 69, 67, 0.25);
}

/* Pagination */
.dataTables_paginate {
    margin-top: 1.5rem;
    display: flex;
    justify-content: flex-end;
    align-items: center;
    gap: 0.5rem;
}

.dataTables_paginate .paginate_button {
    min-width: 36px;
    height: 36px;
    padding: 0;
    margin: 0 2px;
    display: inline-flex;
    align-items: center;
    justify-content: center;
    border-radius: 0.35rem;
    border: 1px solid transparent;
    font-size: 0.875rem;
    font-weight: 500;
    color: var(--text-dark) !important;
    background-color: white;
    transition: all 0.2s ease;
}

.dataTables_paginate .paginate_button:hover {
    color: var(--primary-color) !important;
    background: var(--hover-color);
    border-color: var(--primary-color);
}

.dataTables_paginate .paginate_button.current {
    background: var(--primary-color);
    color: white !important;
    border-color: var(--primary-color);
    font-weight: 600;
}

.dataTables_paginate .paginate_button.disabled {
    color: var(--border-color) !important;
    border-color: var(--border-color);
    cursor: not-allowed;
    opacity: 0.5;
}

/* Ensure pagination buttons are clickable when enabled */
.dataTables_paginate .paginate_button:not(.disabled) {
    cursor: pointer;
    opacity: 1;
}

.dataTables_paginate .paginate_button:not(.disabled):hover {
    color: var(--primary-color) !important;
    background: var(--hover-color);
    border-color: var(--primary-color);
    transform: translateY(-1px);
}

/* Buttons */
.btn-success {
    background: var(--success-color);
    border: none;
    border-radius: 0.5rem;
    padding: 0.625rem 1.25rem;
    font-weight: 500;
    color: white;
    transition: all 0.2s ease;
}

.btn-success:hover {
    background: darken(var(--success-color), 10%);
    transform: translateY(-1px);
    box-shadow: 0 0.15rem 1.75rem 0 rgba(74, 124, 89, 0.15);
}

/* Status Badges */
.badge {
    padding: 0.5rem 1rem;
    font-size: 0.75rem;
    font-weight: 600;
    border-radius: 0.35rem;
}

.badge.bg-success {
    background: var(--success-color) !important;
    color: white;
}

.badge.bg-danger {
    background: var(--danger-color) !important;
    color: white;
}

.badge.bg-warning {
    background: var(--warning-color) !important;
    color: white;
}

/* Action Buttons */
.btn-group .btn, .btn {
    padding: 0.375rem 0.75rem;
    font-size: 0.875rem;
    border-radius: 0.35rem;
    margin: 0 0.125rem;
    border: none;
    transition: all 0.2s ease;
}

.btn-warning {
    background: var(--warning-color);
    color: white;
}

.btn-warning:hover {
    background: darken(var(--warning-color), 10%);
    transform: translateY(-1px);
    box-shadow: 0 0.15rem 1.75rem 0 rgba(196, 128, 77, 0.15);
}

.btn-danger {
    background: var(--danger-color);
    color: white;
}

.btn-danger:hover {
    background: darken(var(--danger-color), 10%);
    transform: translateY(-1px);
    box-shadow: 0 0.15rem 1.75rem 0 rgba(179, 58, 58, 0.15);
}

.btn-primary {
    background: var(--primary-color);
    border-color: var(--primary-color);
    color: white;
}

.btn-primary:hover {
    background: var(--primary-dark);
    border-color: var(--primary-dark);
    transform: translateY(-1px);
    box-shadow: 0 0.15rem 1.75rem 0 rgba(139, 69, 67, 0.15);
}

/* Form Controls */
.form-select, .form-control {
    border: 1px solid var(--border-color);
    border-radius: 0.5rem;
    padding: 0.5rem 1rem;
    font-size: 0.875rem;
    transition: all 0.2s ease;
}

.form-select:focus, .form-control:focus {
    border-color: var(--primary-color);
    box-shadow: 0 0 0 0.2rem rgba(139, 69, 67, 0.25);
}

/* Modal Styling */
.modal-content {
    border: none;
    border-radius: 0.75rem;
    box-shadow: 0 0.15rem 1.75rem 0 rgba(139, 69, 67, 0.15);
}

.modal-header {
    background: var(--primary-color);
    color: var(--text-light);
    border: none;
    padding: 1.5rem;
    border-radius: 0.75rem 0.75rem 0 0;
}

.modal-header .btn-close {
    color: var(--text-light);
    opacity: 0.8;
}

.modal-body {
    padding: 1.5rem;
}

.modal-footer {
    background-color: var(--hover-color);
    border-top: 1px solid var(--border-color);
    padding: 1.25rem;
    border-radius: 0 0 0.75rem 0.75rem;
}

/* Info Text */
.dataTables_info {
    color: var(--text-dark);
    font-size: 0.875rem;
    padding-top: 1.5rem;
}

/* Breadcrumb */
.breadcrumb {
    padding: 0.75rem 1rem;
    background: var(--hover-color);
    border-radius: 0.35rem;
    margin-bottom: 1.5rem;
}

.breadcrumb-item a {
    color: var(--primary-color);
    text-decoration: none;
}

.breadcrumb-item.active {
    color: var(--text-dark);
}

/* Page Title */
h1 {
    color: var(--text-dark);
    font-weight: 400;
    margin-bottom: 1.5rem;
}

/* Filter Controls */
.form-select-sm {
    padding: 0.4rem 2rem 0.4rem 0.75rem;
    font-size: 0.875rem;
}

.gap-2 {
    gap: 0.5rem !important;
}

.big-section-title {
    color: #8B4543;
    font-size: 2.5rem;
    font-weight: 800;
    display: flex;
    align-items: center;
    gap: 1rem;
    margin-bottom: 0.5rem;
    margin-top: 0.5rem;
    letter-spacing: 0.5px;
}
.big-section-icon {
    font-size: 2.5rem;
    color: #8B4543;
    display: flex;
    align-items: center;
}
.big-section-underline {
    border: none;
    border-top: 5px solid #e5d6d6;
    margin-top: -10px;
    margin-bottom: 20px;
    width: 100%;
}
</style>

<div class="container-fluid px-4">
    <div class="big-section-title">
      <span class="big-section-icon"><i class="fas fa-list"></i></span>
      List of Request Ingredients
    </div>
    <hr class="big-section-underline">
    <div class="row">
        <div class="col-12">
            <div class="card mb-4">
                <div class="card-header">
                    <div class="row align-items-center">
                        <div class="col">
                            <h5 class="mb-0">
                                <i class="fas fa-clipboard-list me-1"></i>
                                Branch Ingredient Requests
                            </h5>
                        </div>
                        <div class="col-auto">
                            <div class="d-flex gap-2">
                                <select id="branchFilter" class="form-select form-select-sm">
                                    <option value="all">All Branches</option>
                                    <option value="1">Branch 1</option>
                                    <option value="2">Branch 2</option>
                                    <option value="3">Branch 3</option>
                                </select>
                                <select id="statusFilter" class="form-select form-select-sm">
                                    <option value="all">All Status</option>
                                    <option value="pending">Pending</option>
                                    <option value="approved">Approved</option>
                                    <option value="rejected">Rejected</option>
                                </select>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table id="requestsTable" class="table table-bordered table-hover">
                            <thead>
                                <tr>
                                    <th>Branch</th>
                                    <th>Date Requested</th>
                                    <th>Ingredients</th>
                                    <th>Status</th>
                                    <th>Delivery Status</th>
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

<!-- Status Update Modal -->
<div class="modal fade" id="statusModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">
                    <i class="fas fa-edit me-1"></i>
                    Update Request Status
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <form id="statusForm">
                    <input type="hidden" id="requestId">
                    <div class="mb-3">
                        <label class="form-label">Status</label>
                        <select class="form-select" id="requestStatus">
                            <option value="approved">Approve</option>
                            <option value="rejected">Reject</option>
                        </select>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Notes</label>
                        <textarea class="form-control" id="statusNotes" rows="3"></textarea>
                    </div>
                </form>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                <button type="button" class="btn btn-primary" id="updateStatus">
                    <i class="fas fa-save me-1"></i>
                    Update Status
                </button>
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

<script>
$(document).ready(function() {
    // Initialize DataTable
    const table = $('#requestsTable').DataTable({
        processing: true,
        serverSide: false, // Client-side processing
        pageLength: 10, // Reduced page length to show pagination
        lengthMenu: [[5, 10, 25, 50], [5, 10, 25, 50]], // Available page lengths
        ajax: {
            url: 'ingredient_requests_ajax.php',
            type: 'POST',
            data: function(d) {
                d.branch = $('#branchFilter').val();
                d.status = $('#statusFilter').val();
                console.log('DataTable AJAX request:', d);
            },
            dataSrc: function(json) {
                console.log('DataTable AJAX response:', json);
                console.log('Total records:', json.recordsTotal);
                console.log('Filtered records:', json.recordsFiltered);
                console.log('Data length:', json.data.length);
                return json.data || [];
            },
            error: function(xhr, error, thrown) {
                console.error('DataTable AJAX error:', error, thrown);
                console.error('Response:', xhr.responseText);
            }
        },
        columns: [
            { data: 'branch_name' },
            { 
                data: 'request_date',
                render: function(data) {
                    return new Date(data).toLocaleString();
                }
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
                data: 'updated_by',
                render: function(data) {
                    return data || 'N/A';
                }
            },
            {
                data: null,
                render: function(data) {
                    let buttons = '';
                    if (data.status === 'pending') {
                        buttons += `<button class="btn btn-primary btn-sm update-status me-1" data-id="${data.request_id}">
                            <i class="fas fa-edit"></i> Update Status
                        </button>`;
                    }
                    // Add archive button for all requests
                    buttons += `<button class="btn btn-secondary btn-sm archive-request" data-id="${data.request_id}">
                        <i class="fas fa-box-archive"></i> Archive
                    </button>`;
                    return buttons;
                }
            }
        ],
        order: [[2, 'desc']]
    });

    // Filter change handlers
    $('#branchFilter, #statusFilter').change(function() {
        table.ajax.reload();
    });

    // Auto-refresh every 30 seconds to show new requests
    setInterval(function() {
        table.ajax.reload(null, false); // false = stay on current page
    }, 30000);

    // Manual refresh on page focus (when user comes back to tab)
    $(window).focus(function() {
        table.ajax.reload(null, false);
    });

    // Status update handler
    $(document).on('click', '.update-status', function() {
        const requestId = $(this).data('id');
        $('#requestId').val(requestId);
        $('#statusModal').modal('show');
    });

    // Archive request handler
    $(document).on('click', '.archive-request', function() {
        const requestId = $(this).data('id');
        
        Swal.fire({
            title: 'Are you sure?',
            text: "This request will be archived and moved to the archive list.",
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#8B4543',
            cancelButtonColor: '#6c757d',
            confirmButtonText: '<i class="fas fa-box-archive me-2"></i>Yes, archive it!',
            cancelButtonText: '<i class="fas fa-times me-2"></i>Cancel'
        }).then((result) => {
            if (result.isConfirmed) {
                $.ajax({
                    url: 'archive_ingredient_request.php',
                    method: 'POST',
                    data: { request_id: requestId },
                    success: function(response) {
                        if (response.success) {
                            table.ajax.reload();
                            Swal.fire({
                                icon: 'success',
                                title: 'Archived!',
                                text: 'Request has been archived successfully.',
                                confirmButtonColor: '#8B4543'
                            });
                        } else {
                            Swal.fire({
                                icon: 'error',
                                title: 'Error!',
                                text: response.message || 'Failed to archive request.',
                                confirmButtonColor: '#8B4543'
                            });
                        }
                    },
                    error: function() {
                        Swal.fire({
                            icon: 'error',
                            title: 'Error!',
                            text: 'Failed to archive request. Please try again.',
                            confirmButtonColor: '#8B4543'
                        });
                    }
                });
            }
        });
    });

    // Update status submission
    $('#updateStatus').click(function() {
        const requestId = $('#requestId').val();
        const status = $('#requestStatus').val();
        const notes = $('#statusNotes').val();

        $.ajax({
            url: 'update_ingredient_request.php',
            method: 'POST',
            data: {
                request_id: requestId,
                status: status,
                notes: notes
            },
            success: function(response) {
                console.log(response);
                if (response.success) {
                    $('#statusModal').modal('hide');
                    table.ajax.reload();
                    // Show success message using SweetAlert
                    Swal.fire({
                        icon: 'success',
                        title: 'Success!',
                        text: 'Request status updated successfully',
                        confirmButtonColor: '#8B4543'
                    });
                } else {
                    // Show error message using SweetAlert
                    Swal.fire({
                        icon: 'error',
                        title: 'Error!',
                        text: response.message || 'Error updating status',
                        confirmButtonColor: '#8B4543'
                    });
                }
            },
            error: function(xhr, status, error) {
                console.error('AJAX Error:', xhr.responseText);
                // Show error message using SweetAlert
                Swal.fire({
                    icon: 'error',
                    title: 'Error!',
                    text: 'Failed to update status. Please try again.',
                    confirmButtonColor: '#8B4543'
                });
            }
        });
    });

    // Update delivery submission
    $('#updateDelivery').click(function() {
        const requestId = $('#deliveryRequestId').val();
        const deliveryStatus = $('#deliveryStatus').val();
        const deliveryDate = $('#deliveryDate').val();
        const deliveryNotes = $('#deliveryNotes').val();

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
                    table.ajax.reload();
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
                // Show error message using SweetAlert
                Swal.fire({
                    icon: 'error',
                    title: 'Error!',
                    text: 'Failed to update delivery status. Please try again.',
                    confirmButtonColor: '#8B4543'
                });
            }
        });
    });
});
</script>

<?php include('footer.php'); ?> 