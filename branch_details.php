<?php
require_once 'db_connect.php';
require_once 'auth_function.php';

checkAdminLogin();

include('header.php');
?>

<div class="container-fluid px-4">
    <h1 class="mt-4" style="color: #8B4543; font-size: 1.25rem; font-family: 'Segoe UI', system-ui, -apple-system, sans-serif; font-weight: 500; background-color: #F8F9FA; padding: 1rem;">| Branch Details</h1>
    
    <div class="row">
        <div class="col-12">
            <div class="card mb-4">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <div>
                        <i class="fas fa-store me-1"></i>
                        Branch List
                    </div>
                    <a href="add_branch.php" class="btn btn-success">
                        <i class="fas fa-plus me-1"></i> Add Branch
                    </a>
                </div>
                <div class="card-body">
                    <table id="branchTable" class="table table-bordered table-hover">
                        <thead>
                            <tr>
                                <th>Branch Code</th>
                                <th>Branch Name</th>
                                <th>Contact</th>
                                <th>Email</th>
                                <th>Address</th>
                                <th>Operating Hours</th>
                                <th>Status</th>
                                <th>Action</th>
                            </tr>
                        </thead>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>

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
    vertical-align: middle;
}

.table tbody tr td:first-child {
    border-top-left-radius: 0.5rem;
    border-bottom-left-radius: 0.5rem;
}

.table tbody tr td:last-child {
    border-top-right-radius: 0.5rem;
    border-bottom-right-radius: 0.5rem;
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

/* Search and Length Menu */
.dataTables_wrapper {
    padding: 1.5rem;
}

.dataTables_length select {
    padding: 0.5rem 2.5rem 0.5rem 1rem;
    font-size: 0.875rem;
    border: 1px solid var(--border-color);
    border-radius: 0.5rem;
    background-color: white;
    cursor: pointer;
}

.dataTables_filter input {
    padding: 0.5rem 1rem;
    font-size: 0.875rem;
    border: 1px solid var(--border-color);
    border-radius: 0.5rem;
    background-color: white;
    min-width: 300px;
}

.dataTables_filter input:focus {
    border-color: var(--primary-color);
    outline: none;
    box-shadow: 0 0 0 0.2rem rgba(139, 69, 67, 0.25);
}

/* SweetAlert2 Custom Styles */
.swal2-popup {
    border-radius: 1rem;
    padding: 2rem;
}

.swal2-title {
    color: var(--text-dark);
    font-size: 1.5rem;
    font-weight: 600;
    margin-bottom: 0.5rem;
}

.swal2-html-container {
    color: #6c757d;
    font-size: 1rem;
    margin: 1rem 0;
}

.swal2-icon {
    border-color: var(--warning-color);
    color: var(--warning-color);
}

.swal2-confirm {
    background-color: var(--danger-color) !important;
    border-radius: 0.75rem !important;
    padding: 0.75rem 1.5rem !important;
    font-size: 1rem !important;
    font-weight: 500 !important;
    box-shadow: 0 0.15rem 1.75rem 0 rgba(179, 58, 58, 0.15) !important;
}

.swal2-confirm:focus {
    box-shadow: 0 0 0 0.25rem rgba(179, 58, 58, 0.25) !important;
}

.swal2-cancel {
    background-color: #f8f9fa !important;
    color: var(--text-dark) !important;
    border-radius: 0.75rem !important;
    padding: 0.75rem 1.5rem !important;
    font-size: 1rem !important;
    font-weight: 500 !important;
    margin-right: 0.5rem !important;
}

.swal2-cancel:hover {
    background-color: #e9ecef !important;
}
</style>

<script>
$(document).ready(function() {
    $('#branchTable').DataTable({
        "processing": true,
        "serverSide": true,
        "ajax": {
            "url": "branch_ajax.php",
            "type": "GET"
        },
        "columns": [
            { "data": "branch_code" },
            { "data": "branch_name" },
            { "data": "contact_number" },
            { "data": "email" },
            { "data": "complete_address" },
            { "data": "operating_hours" },
            { 
                "data": "status",
                "render": function(data, type, row) {
                    return data === 'Active' ? 
                        '<span class="badge bg-success">Active</span>' : 
                        '<span class="badge bg-danger">Inactive</span>';
                }
            },
            {
                "data": null,
                "render": function(data, type, row) {
                    return `
                        <div class="text-center">
                            <a href="edit_branch.php?id=${row.branch_id}" class="btn btn-warning btn-sm">
                                <i class="fas fa-edit"></i> Edit
                            </a>
                            <button class="btn btn-danger btn-sm delete-btn" data-id="${row.branch_id}">
                                <i class="fas fa-trash"></i> Delete
                            </button>
                        </div>`;
                }
            }
        ],
        "order": [[1, "asc"]],
        "pageLength": 10,
        "responsive": true
    });

    // Handle Delete Button Click
    $(document).on('click', '.delete-btn', function() {
        let branchId = $(this).data('id');
        
        Swal.fire({
            title: 'Are you sure?',
            text: "You won't be able to revert this! All associated data will be permanently removed.",
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#B33A3A',
            cancelButtonColor: '#f8f9fa',
            confirmButtonText: '<i class="fas fa-trash me-2"></i>Yes, delete it!',
            cancelButtonText: '<i class="fas fa-times me-2"></i>Cancel',
            customClass: {
                confirmButton: 'btn btn-danger btn-lg',
                cancelButton: 'btn btn-light btn-lg'
            },
            buttonsStyling: false,
            padding: '2rem',
            width: 400,
            showClass: {
                popup: 'animate__animated animate__fadeInDown animate__faster'
            },
            hideClass: {
                popup: 'animate__animated animate__fadeOutUp animate__faster'
            }
        }).then((result) => {
            if (result.isConfirmed) {
                $.ajax({
                    url: 'delete_branch.php',
                    type: 'POST',
                    data: { id: branchId },
                    dataType: 'json',
                    success: function(response) {
                        if (response.success) {
                            $('#branchTable').DataTable().ajax.reload();
                            
                            Swal.fire({
                                icon: 'success',
                                title: 'Deleted!',
                                text: 'Branch has been deleted successfully.',
                                timer: 2000,
                                showConfirmButton: false,
                                customClass: {
                                    popup: 'animate__animated animate__fadeInDown animate__faster'
                                }
                            });
                        } else {
                            Swal.fire({
                                icon: 'error',
                                title: 'Error!',
                                text: response.message || 'Failed to delete branch.',
                                customClass: {
                                    popup: 'animate__animated animate__fadeInDown animate__faster'
                                }
                            });
                        }
                    },
                    error: function() {
                        Swal.fire({
                            icon: 'error',
                            title: 'Error!',
                            text: 'An error occurred while deleting the branch.',
                            customClass: {
                                popup: 'animate__animated animate__fadeInDown animate__faster'
                            }
                        });
                    }
                });
            }
        });
    });
});
</script>

<?php include('footer.php'); ?> 