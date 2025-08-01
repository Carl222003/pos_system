<?php
require_once 'db_connect.php';
require_once 'auth_function.php';

checkAdminLogin();

include('header.php');
?>

<div class="container-fluid px-4">
    <h1 class="section-title"><span class="section-icon"><i class="fas fa-info-circle"></i></span>Branch Details</h1>
    
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

<!-- Edit Branch Modal -->
<div class="modal fade" id="editBranchModal" tabindex="-1" aria-labelledby="editBranchModalLabel" aria-hidden="true">
  <div class="modal-dialog modal-lg modal-dialog-centered">
    <div class="modal-content" style="margin: 0 auto;">
      <div class="modal-header" style="background: #8B4543; color: #fff;">
        <h5 class="modal-title" id="editBranchModalLabel">Edit Branch</h5>
        <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
      </div>
      <form id="editBranchForm">
        <div class="modal-body">
          <input type="hidden" name="branch_id" id="editBranchId">
          <div class="row mb-3">
            <div class="col-md-6">
              <label class="form-label">Branch Name</label>
              <input type="text" name="branch_name" id="editBranchName" class="form-control" required>
            </div>
            <div class="col-md-6">
              <label class="form-label">Branch Code</label>
              <input type="text" name="branch_code" id="editBranchCode" class="form-control" required>
            </div>
          </div>
          <div class="row mb-3">
            <div class="col-md-6">
              <label class="form-label">Contact Number</label>
              <input type="text" name="contact_number" id="editContactNumber" class="form-control">
            </div>
            <div class="col-md-6">
              <label class="form-label">Email</label>
              <input type="email" name="email" id="editEmail" class="form-control">
            </div>
          </div>
          <div class="row mb-3">
            <div class="col-md-6">
              <label class="form-label">Complete Address</label>
              <input type="text" name="complete_address" id="editCompleteAddress" class="form-control">
            </div>
            <div class="col-md-6">
              <label class="form-label">Operating Hours</label>
              <input type="text" name="operating_hours" id="editOperatingHours" class="form-control">
            </div>
          </div>
          <div class="row mb-3">
            <div class="col-md-6">
              <label class="form-label">Status</label>
              <select name="status" id="editStatus" class="form-select">
                <option value="Active">Active</option>
                <option value="Inactive">Inactive</option>
                <option value="archived">Archived</option>
              </select>
            </div>
          </div>
        </div>
        <div class="modal-footer">
          <button type="button" class="btn btn-secondary" data-bs-dismiss="modal" style="background: #6c757d; color: #fff;">Close</button>
          <button type="submit" class="btn" style="background: #8B4543; color: #fff;">Update Branch</button>
        </div>
      </form>
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
  /* Center the modal content and adjust width for better appearance */
  @media (min-width: 992px) {
    #editBranchModal .modal-dialog {
      max-width: 650px;
    }
  }
</style>

<script>
function showFeedbackModal(type, title, text) {
  Swal.fire({
    icon: type,
    title: title,
    text: text,
    confirmButtonText: 'OK',
    customClass: { confirmButton: 'swal2-confirm-archive' },
    buttonsStyling: false
  });
}

// Custom function for Archive button
function archiveBranchCustomAction(branchId) {
    console.log('Archive button clicked for branch ID:', branchId);
    // Add more custom actions here if needed
}

// Function to handle archive errors
function handleArchiveError(message) {
    showFeedbackModal('error', 'Error!', message);
    // Add more custom error handling here if needed
}

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
                            <a href="#" class="btn btn-warning btn-sm edit-branch-btn" data-id="${row.branch_id}">
                                <i class="fas fa-edit"></i> Edit
                            </a>
                            <button class="btn btn-secondary btn-sm archive-btn" data-id="${row.branch_id}">Archive</button>
                        </div>`;
                }
            }
        ],
        "order": [[1, "asc"]],
        "pageLength": 10,
        "responsive": true
    });

    // Handle Delete Button Click
    $(document).on('click', '.archive-btn', function() {
        let branchId = $(this).data('id');
        
        // Custom function before confirmation dialog
        archiveBranchCustomAction(branchId);
        
        Swal.fire({
            title: 'Are you sure?',
            text: "You can restore this branch from the archive.",
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#6c757d',
            cancelButtonColor: '#f8f9fa',
            confirmButtonText: '<i class="fas fa-box-archive me-2"></i>Yes, archive it!',
            cancelButtonText: '<i class="fas fa-times me-2"></i>Cancel',
            customClass: {
                confirmButton: 'btn btn-secondary btn-lg',
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
                    url: 'archive_branch.php',
                    type: 'POST',
                    data: { id: branchId },
                    dataType: 'json',
                    success: function(response) {
                        if (response.success) {
                            $('#branchTable').DataTable().ajax.reload();
                            
                            showFeedbackModal('success', 'Archived!', 'Branch has been archived successfully.');
                        } else {
                            handleArchiveError(response.message || 'Failed to archive branch.');
                        }
                    },
                    error: function(xhr, status, error) {
                        let errorMsg = 'An error occurred while archiving the branch.';
                        if (xhr.responseJSON && xhr.responseJSON.message) {
                            errorMsg = xhr.responseJSON.message;
                        }
                        handleArchiveError(errorMsg);
                    }
                });
            }
        });
    });

    // Edit button handler for modal
    $(document).on('click', '.edit-branch-btn', function(e) {
        e.preventDefault();
        const branchId = $(this).data('id');
        // Fetch branch data via AJAX
        $.get('get_branch_details.php', { id: branchId }, function(response) {
            if (response.success) {
                const b = response.data;
                $('#editBranchId').val(b.branch_id);
                $('#editBranchName').val(b.branch_name);
                $('#editBranchCode').val(b.branch_code);
                $('#editContactNumber').val(b.contact_number);
                $('#editEmail').val(b.email);
                $('#editCompleteAddress').val(b.complete_address);
                $('#editOperatingHours').val(b.operating_hours);
                $('#editStatus').val(b.status);
                $('#editBranchModal').modal('show');
            } else {
                showFeedbackModal('error', 'Error!', response.message || 'Failed to fetch branch details.');
            }
        }, 'json');
    });
    // Submit edit form via AJAX
    $('#editBranchForm').on('submit', function(e) {
        e.preventDefault();
        $.post('update_branch.php', $(this).serialize(), function(response) {
            if (response.success) {
                $('#editBranchModal').modal('hide');
                $('#branchTable').DataTable().ajax.reload();
                showFeedbackModal('success', 'Updated!', 'Branch details updated successfully.');
            } else {
                showFeedbackModal('error', 'Error!', response.message || 'Failed to update branch.');
            }
        }, 'json');
    });
});
</script>

<?php include('footer.php'); ?> 