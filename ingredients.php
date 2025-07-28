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

/* Modal Design Styles (copied from Add Category) */
.modal-header {
    border-radius: 0.5rem 0.5rem 0 0;
}

.bg-maroon {
    background-color: #8B4543;
}

.btn-maroon {
    background-color: #8B4543;
    color: white;
}

.btn-maroon:hover {
    background-color: #723937;
    color: white;
}

/* Form Control Styles */
.form-control, .form-select {
    border-radius: 0.375rem;
    padding: 0.5rem 0.75rem;
    background-color: white;
    transition: all 0.2s ease-in-out;
    font-size: 0.9rem;
}

.form-control:focus, .form-select:focus {
    box-shadow: 0 0 0 0.25rem rgba(139, 69, 67, 0.25);
    border-color: rgba(139, 69, 67, 0.5);
}

/* Button Styles */
.btn-lg {
    border-radius: 0.375rem;
    padding: 0.5rem 1.25rem;
    font-weight: 500;
    transition: all 0.2s ease-in-out;
    font-size: 0.9rem;
}

.btn-light {
    background-color: #f8f9fa;
    border: none;
}

.btn-light:hover {
    background-color: #e9ecef;
}

/* Modal Animation */
.modal.fade .modal-dialog {
    transform: scale(0.95);
    transition: transform 0.2s ease-out;
}

.modal.show .modal-dialog {
    transform: scale(1);
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

.action-buttons {
    display: flex;
    gap: 0.5rem; /* Adjust the gap as needed */
    justify-content: center;
    align-items: center;
}
.action-buttons .btn {
    margin: 0; /* Remove any default margin */
    padding: 0.4rem 0.7rem; /* Consistent button size */
}

.table td, .table th {
    padding: 1rem 0.75rem;
    vertical-align: middle;
}

.table td img {
    display: block;
    margin: 0 auto;           /* Center horizontally */
    margin-top: 4px;          /* Add a little space above */
    margin-bottom: 4px;       /* Add a little space below */
    width: 48px;              /* Consistent size */
    height: 48px;
    object-fit: cover;
    border-radius: 10px;      /* Soft corners */
    box-shadow: 0 2px 8px rgba(0,0,0,0.08);
    border: 2px solid #f3e6e6;
    background: #fff;
}

.btn-edit {
    background: #C4804D !important;
    color: #fff !important;
    border: none;
    border-radius: 0.75rem;
    display: inline-flex;
    align-items: center;
    gap: 0.4em;
    font-weight: 500;
    font-size: 1rem;
    padding: 0.5rem 1.25rem;
    box-shadow: 0 0.15rem 1.75rem 0 rgba(196, 128, 77, 0.10);
    transition: background 0.2s, color 0.2s;
}
.btn-edit i {
    color: #fff !important;
    font-size: 1.2em;
}
.btn-edit:hover, .btn-edit:focus {
    background: #a96a3d !important;
    color: #fff !important;
    text-decoration: none;
}
.btn-edit:active {
    background: #8a5632 !important;
    color: #fff !important;
}
.btn-archive {
    background: #6c757d !important;
    color: #fff !important;
    border: none;
    border-radius: 0.75rem;
    display: inline-flex;
    align-items: center;
    gap: 0.4em;
    font-weight: 500;
    font-size: 1rem;
    padding: 0.5rem 1.25rem;
    box-shadow: 0 0.15rem 1.75rem 0 rgba(108, 117, 125, 0.10);
    transition: background 0.2s, color 0.2s;
}
.btn-archive i {
    color: #fff !important;
    font-size: 1.2em;
}
.btn-archive:hover, .btn-archive:focus {
    background: #5a6268 !important;
    color: #fff !important;
    text-decoration: none;
}
.btn-archive:active {
    background: #545b62 !important;
    color: #fff !important;
}
.swal2-confirm-archive {
    background-color: #B33A3A !important;
    color: #fff !important;
    border-radius: 0.75rem !important;
    padding: 0.75rem 1.5rem !important;
    font-size: 1rem !important;
    font-weight: 500 !important;
    box-shadow: 0 0.15rem 1.75rem 0 rgba(179, 58, 58, 0.15) !important;
    display: inline-flex !important;
    align-items: center;
    gap: 0.4em;
}
.swal2-confirm-archive:focus {
    box-shadow: 0 0 0 0.25rem rgba(179, 58, 58, 0.25) !important;
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

<div class="container-fluid px-4">
    <h1 class="section-title"><span class="section-icon"><i class="fas fa-carrot"></i></span>Ingredient Management</h1>
    <div class="row">
        <div class="col-12">
            <div class="card mb-4">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <div>
                        <i class="fas fa-mortar-pestle me-1"></i>
                        Ingredient List
                    </div>
                    <div>
                        <a href="#" class="btn btn-success" id="addIngredientBtn">
                            <i class="fas fa-plus me-1"></i> Add Ingredient
                        </a>
                        <a href="#" class="btn btn-primary ms-2" id="importCsvBtn">
                            <i class="fas fa-file-import me-1"></i> Insert CSV
                        </a>
                    </div>
                </div>
                <div class="card-body">
                    <table id="ingredientTable" class="table table-bordered table-hover">
                        <thead>
                            <tr>
                                <!--<th>ID</th>-->
                                <th>Category</th>
                                <th>Ingredient Name</th>
                                <th>Quantity</th>
                                <th>Unit</th>
                                <th>Branch</th>
                                <th>Action</th>
                            </tr>
                        </thead>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Edit Ingredient Modal -->
<div class="modal fade" id="editIngredientModal" tabindex="-1" aria-labelledby="editIngredientModalLabel" aria-hidden="true">
  <div class="modal-dialog">
    <div class="modal-content">
      <div class="modal-body" id="editIngredientModalBody">
        <!-- AJAX-loaded content here -->
      </div>
    </div>
  </div>
</div>

<!-- Add Ingredient Modal -->
<div class="modal fade" id="addIngredientModal" tabindex="-1" aria-labelledby="addIngredientModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content border-0 shadow" style="border-radius: 0.5rem;">
            <!-- Modal Header -->
            <div class="modal-header bg-maroon text-white py-3">
                <h5 class="modal-title" id="addIngredientModalLabel">
                    <i class="fas fa-carrot me-2"></i>Add Ingredient
                </h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>

            <!-- Modal Body -->
            <div class="modal-body bg-light p-3">
                <form id="addIngredientForm">
                    <div class="row">
                        <!-- Left Column -->
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label for="category_id" class="form-label fw-medium">Category Name</label>
                                <select name="category_id" id="category_id" class="form-select border-0 shadow-sm" required>
                                    <option value="">Select Category</option>
                                    <?php 
                                    $categories = $pdo->query("SELECT category_id, category_name FROM pos_category WHERE status = 'active' ORDER BY category_name")->fetchAll(PDO::FETCH_ASSOC);
                                    foreach ($categories as $category): ?>
                                        <option value="<?php echo htmlspecialchars($category['category_id']); ?>"><?php echo htmlspecialchars($category['category_name']); ?></option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                            <div class="mb-3">
                                <label for="ingredient_name" class="form-label fw-medium">Ingredient Name</label>
                                <input type="text" name="ingredient_name" id="ingredient_name" class="form-control border-0 shadow-sm" required>
                            </div>
                            <div class="mb-3">
                                <label for="ingredient_quantity" class="form-label fw-medium">Quantity</label>
                                <input type="number" name="ingredient_quantity" id="ingredient_quantity" class="form-control border-0 shadow-sm" min="0" step="0.01" required>
                            </div>
                            <div class="mb-3">
                                <label for="date_added" class="form-label fw-medium">Date Added</label>
                                <input type="date" name="date_added" id="date_added" class="form-control border-0 shadow-sm" value="<?php echo date('Y-m-d'); ?>" readonly>
                            </div>
                        </div>
                        
                        <!-- Right Column -->
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label for="ingredient_unit" class="form-label fw-medium">Unit</label>
                                <input type="text" name="ingredient_unit" id="ingredient_unit" class="form-control border-0 shadow-sm" required>
                            </div>
                            <div class="mb-3">
                                <label for="consume_before" class="form-label fw-medium">Consume Before Date</label>
                                <input type="date" name="consume_before" id="consume_before" class="form-control border-0 shadow-sm" required>
                            </div>
                            <div class="mb-3">
                                <label for="branch_id" class="form-label fw-medium">Branch</label>
                                <select name="branch_id" id="branch_id" class="form-select border-0 shadow-sm" required>
                                    <option value="">Select Branch</option>
                                    <?php 
                                    $branches = $pdo->query("SELECT branch_id, branch_name FROM pos_branch WHERE status = 'Active' ORDER BY branch_name")->fetchAll(PDO::FETCH_ASSOC);
                                    foreach ($branches as $branch): ?>
                                        <option value="<?php echo htmlspecialchars($branch['branch_id']); ?>"><?php echo htmlspecialchars($branch['branch_name']); ?></option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                            <div class="mb-3">
                                <label for="ingredient_status" class="form-label fw-medium">Status</label>
                                <select name="ingredient_status" id="ingredient_status" class="form-select border-0 shadow-sm">
                                    <option value="Available">Available</option>
                                    <option value="Out of Stock">Out of Stock</option>
                                </select>
                            </div>
                        </div>
                    </div>
                </form>
            </div>

            <!-- Modal Footer -->
            <div class="modal-footer border-0 px-3 pb-3 pt-0">
                <button type="button" class="btn btn-light px-4" data-bs-dismiss="modal">
                    <i class="fas fa-times me-2"></i>Cancel
                </button>
                <button type="button" class="btn btn-maroon px-4" id="saveIngredient">
                    <i class="fas fa-save me-2"></i>Save Ingredient
                </button>
            </div>
        </div>
    </div>
</div>

<!-- Import CSV/Excel Modal -->
<div class="modal fade" id="importCsvModal" tabindex="-1" aria-labelledby="importCsvModalLabel" aria-hidden="true">
  <div class="modal-dialog">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title" id="importCsvModalLabel">Import Ingredients from CSV/Excel</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
      </div>
      <form id="importCsvForm" enctype="multipart/form-data">
        <div class="modal-body">
          <div class="mb-3">
            <label for="csvFile" class="form-label">Select CSV or Excel file</label>
            <input type="file" class="form-control" id="csvFile" name="csvFile" accept=".csv, application/vnd.openxmlformats-officedocument.spreadsheetml.sheet, application/vnd.ms-excel" required>
            <div class="form-text">Accepted formats: .csv, .xlsx</div>
          </div>
          <div class="alert alert-info small">
            <b>Template columns:</b> category_id, ingredient_name, ingredient_quantity, ingredient_unit, [notes]<br>
            <a href="sample_ingredients_import.csv" download>Download sample CSV</a>
          </div>
        </div>
        <div class="modal-footer">
          <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
          <button type="submit" class="btn btn-primary">Import</button>
        </div>
      </form>
    </div>
  </div>
</div>

<?php include('footer.php'); ?>

<!-- Add SweetAlert2 CDN -->
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

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

$(document).ready(function() {
    $('#ingredientTable').DataTable({
        "processing": true,
        "serverSide": true,
        "ajax": {
            "url": "ingredients_ajax.php",
            "type": "GET"
        },
        "columns": [
            //{ "data": "ingredient_id" },
            { "data": "category_name" },
            { "data": "ingredient_name" },
            { "data": "ingredient_quantity" },
            { "data": "ingredient_unit" },
            { "data": "branch_name" },
            {
                "data": null,
                "render": function(data, type, row) {
                    return `
                        <div class="text-center">
                            <button class="btn btn-edit btn-sm edit-ingredient-btn" data-id="${row.ingredient_id}">
                                <i class="fas fa-edit"></i> Edit
                            </button>
                            <button class="btn btn-archive btn-sm archive-btn" data-id="${row.ingredient_id}">
                                <i class="fas fa-box-archive"></i> Archive
                            </button>
                        </div>`;
                }
            }
        ]
    });

    // Handle Delete Button Click
    $(document).on('click', '.archive-btn', function() {
        let ingredientId = $(this).data('id');
        Swal.fire({
            icon: 'warning',
            title: 'Are you sure?',
            text: "You can restore this ingredient from the archive.",
            showCancelButton: true,
            confirmButtonColor: '#6c757d',
            cancelButtonColor: '#8B4543',
            confirmButtonText: '<i class="fas fa-box-archive"></i> Yes, archive it!',
            cancelButtonText: 'Cancel',
            customClass: {popup: 'rounded-4'}
        }).then((result) => {
            if (result.isConfirmed) {
                $.ajax({
                    url: 'archive_ingredient.php',
                    type: 'POST',
                    data: { id: ingredientId },
                    success: function(response) {
                        showFeedbackModal('success', 'Archived!', 'Ingredient has been archived successfully.');
                        $('#ingredientTable').DataTable().ajax.reload();
                    },
                    error: function(xhr, status, error) {
                        showFeedbackModal('error', 'Error!', 'An error occurred while archiving the ingredient.');
                    }
                });
            }
        });
    });
});

$(document).on('click', '.edit-ingredient-btn', function(e) {
    e.preventDefault();
    var ingredientId = $(this).data('id');
    $('#editIngredientModalBody').html('<div class="text-center p-4">Loading...</div>');
    $('#editIngredientModal').modal('show');
    $.get('edit_ingredient.php', { id: ingredientId, modal: 1 }, function(data) {
        $('#editIngredientModalBody').html(data);
    });
});

$(document).on('submit', '#editIngredientModalBody form', function(e) {
    e.preventDefault();
    var form = $(this);
    var formData = form.serialize();
    $.post(form.attr('action'), formData, function(response) {
        showFeedbackModal('success', 'Saved!', 'Ingredient has been updated successfully.');
        $('#editIngredientModal').modal('hide');
        $('#ingredientTable').DataTable().ajax.reload();
    }, 'json');
});
// Intercept Cancel button
$(document).on('click', '#editIngredientModalBody .btn-cancel', function(e) {
    e.preventDefault();
    Swal.fire({
        icon: 'warning',
        title: 'Are you sure?',
        text: "You won't be able to revert this!",
        showCancelButton: true,
        confirmButtonColor: '#B33A3A',
        cancelButtonColor: '#8B4543',
        confirmButtonText: 'Yes, cancel!',
        cancelButtonText: 'No, keep editing',
        customClass: {popup: 'rounded-4'}
    }).then((result) => {
        if (result.dismiss === Swal.DismissReason.cancel) {
            // Re-open the edit modal or form here
            $('#editIngredientModal').modal('show');
        }
    });
});

$(document).on('click', '#addIngredientBtn', function(e) {
    e.preventDefault();
    $('#addIngredientModal').modal('show');
});

// Add modal hidden event handlers
$('#addIngredientModal').on('hidden.bs.modal', function () {
    $('body').removeClass('modal-open');
    $('.modal-backdrop').remove();
});



// Handle Add Ingredient button click
$('#saveIngredient').click(function() {
    var formData = $('#addIngredientForm').serialize();
    
    $.ajax({
        url: 'add_ingredient.php',
        type: 'POST',
        data: formData,
        success: function(response) {
            if (response.success) {
                // Properly hide modal and remove backdrop
                $('#addIngredientModal').modal('hide');
                $('body').removeClass('modal-open');
                $('.modal-backdrop').remove();
                
                $('#addIngredientForm')[0].reset();
                $('#ingredientTable').DataTable().ajax.reload();
                Swal.fire('Success', 'Ingredient saved successfully!', 'success');
            } else {
                Swal.fire('Error', response.message || 'Failed to add ingredient.', 'error');
            }
        },
        error: function() {
            Swal.fire('Error', 'An error occurred while processing your request.', 'error');
        }
    });
});

$(document).on('click', '#importCsvBtn', function(e) {
    e.preventDefault();
    $('#importCsvModal').modal('show');
});

$('#importCsvForm').on('submit', function(e) {
    e.preventDefault();
    var formData = new FormData(this);
    $.ajax({
        url: 'import_ingredients.php',
        type: 'POST',
        data: formData,
        processData: false,
        contentType: false,
        success: function(response) {
            if (response.success) {
                $('#importCsvModal').modal('hide');
                $('#ingredientTable').DataTable().ajax.reload();
                Swal.fire('Success', 'Ingredients imported!', 'success');
            } else {
                Swal.fire('Error', response.message || 'Failed to import ingredients.', 'error');
            }
        },
        error: function() {
            Swal.fire('Error', 'Failed to import ingredients.', 'error');
        }
    });
});
</script>
