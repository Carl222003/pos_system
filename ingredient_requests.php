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

.completed-section h5 {
    color: #28a745;
    border-bottom-color: #28a745;
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

/* Action Button Styles */
.action-menu-btn {
    background: linear-gradient(135deg, #8B4543 0%, #723937 100%);
    border: none;
    color: white;
    padding: 0.5rem;
    border-radius: 0.5rem;
    transition: all 0.3s ease;
    box-shadow: 0 2px 8px rgba(139, 69, 67, 0.3);
}

.action-menu-btn:hover {
    background: linear-gradient(135deg, #723937 0%, #5a2e2c 100%);
    transform: translateY(-2px);
    box-shadow: 0 4px 15px rgba(139, 69, 67, 0.4);
    color: white;
}

/* Enhanced Manage Modal */
.enhanced-manage-modal {
    background: linear-gradient(135deg, #ffffff 0%, #f8f9fa 100%);
    border: none;
    border-radius: 1rem;
    box-shadow: 0 20px 60px rgba(139, 69, 67, 0.3);
    backdrop-filter: blur(10px);
}

.manage-icon {
    width: 50px;
    height: 50px;
    background: rgba(255, 255, 255, 0.2);
    border-radius: 50%;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 1.5rem;
    color: white;
    backdrop-filter: blur(10px);
    box-shadow: 0 4px 15px rgba(0, 0, 0, 0.2);
}

.enhanced-manage-modal .btn-success {
    background: linear-gradient(135deg, #28a745 0%, #20c997 100%);
    border: none;
    border-radius: 0.5rem;
    padding: 1rem 2rem;
    font-weight: 600;
    transition: all 0.3s ease;
    box-shadow: 0 4px 15px rgba(40, 167, 69, 0.3);
}

.enhanced-manage-modal .btn-success:hover {
    transform: translateY(-2px);
    box-shadow: 0 6px 20px rgba(40, 167, 69, 0.4);
    background: linear-gradient(135deg, #20c997 0%, #17a2b8 100%);
}

.enhanced-manage-modal .btn-danger {
    background: linear-gradient(135deg, #dc3545 0%, #e83e8c 100%);
    border: none;
    border-radius: 0.5rem;
    padding: 1rem 2rem;
    font-weight: 600;
    transition: all 0.3s ease;
    box-shadow: 0 4px 15px rgba(220, 53, 69, 0.3);
}

.enhanced-manage-modal .btn-danger:hover {
    transform: translateY(-2px);
    box-shadow: 0 6px 20px rgba(220, 53, 69, 0.4);
    background: linear-gradient(135deg, #e83e8c 0%, #6f42c1 100%);
}

/* Force modal visibility */
#manageRequestModal.show {
    display: block !important;
}
#manageRequestModal.modal.show {
    display: block !important;
}

/* Enhanced Modal Styling - Matching Design System */
.request-details-section {
    background: linear-gradient(135deg, #f8f9fa 0%, #e9ecef 100%);
    border-radius: 0.75rem;
    padding: 1.5rem;
    border: 1px solid #dee2e6;
    box-shadow: 0 2px 8px rgba(139, 69, 67, 0.08);
}


/* Ingredient item styling */
.ingredient-item {
    border: 1px solid #e9ecef;
    border-radius: 0.5rem;
    padding: 1rem;
    margin-bottom: 0.75rem;
    transition: all 0.2s ease;
    background: #ffffff;
}

.ingredient-item:hover {
    border-color: #8B4543;
    box-shadow: 0 2px 8px rgba(139, 69, 67, 0.1);
}

.ingredient-item.selected {
    border-color: #8B4543;
    background-color: #f8f9fa;
    box-shadow: 0 2px 8px rgba(139, 69, 67, 0.15);
}

.ingredient-info {
    flex: 1;
}

.ingredient-name {
    font-weight: 600;
    color: #2c3e50;
    font-size: 0.95rem;
}

/* Ensure proper spacing for stock badge */
.ingredient-item .ms-3 {
    margin-left: 1rem !important;
}

/* Stock input field styling */
.stock-input {
    min-width: 150px !important;
    text-align: center !important;
    font-weight: 600 !important;
    cursor: default !important;
}

.stock-input:focus {
    box-shadow: none !important;
    border-color: inherit !important;
}


.detail-item {
    margin-bottom: 1rem;
}

.detail-item:last-child {
    margin-bottom: 0;
}

.detail-label {
    font-weight: 600;
    color: #8B4543;
    font-size: 0.875rem;
    margin-bottom: 0.25rem;
    display: block;
}

.detail-value {
    color: #495057;
    font-size: 0.95rem;
    line-height: 1.4;
}

.action-selection-section {
    background: #ffffff;
    border-radius: 0.75rem;
    padding: 1.5rem;
    border: 1px solid #e9ecef;
    box-shadow: 0 2px 8px rgba(139, 69, 67, 0.08);
}

.rejection-notes-section {
    background: linear-gradient(135deg, #fff5f5 0%, #fed7d7 100%);
    border-radius: 0.75rem;
    padding: 1.5rem;
    border: 1px solid #feb2b2;
    animation: slideIn 0.3s ease-out;
    box-shadow: 0 4px 12px rgba(220, 53, 69, 0.15);
}

@keyframes slideIn {
    from {
        opacity: 0;
        transform: translateY(-10px);
    }
    to {
        opacity: 1;
        transform: translateY(0);
    }
}

#rejectionNotes {
    border: 2px solid #dc3545;
    border-radius: 0.5rem;
    transition: all 0.2s ease;
    background: #ffffff;
}

#rejectionNotes:focus {
    border-color: #8B4543;
    box-shadow: 0 0 0 0.2rem rgba(139, 69, 67, 0.25);
}

.form-text {
    color: #8B4543;
    font-size: 0.8rem;
    margin-top: 0.5rem;
    opacity: 0.8;
}

/* Enhanced Button Styling to Match Design System */
.approve-request-modal {
    background: linear-gradient(135deg, #28a745, #20c997);
    border: none;
    border-radius: 0.5rem;
    padding: 12px 24px;
    font-weight: 600;
    box-shadow: 0 4px 12px rgba(40, 167, 69, 0.3);
    transition: all 0.2s ease;
}

.approve-request-modal:hover {
    background: linear-gradient(135deg, #218838, #1ea085);
    transform: translateY(-2px);
    box-shadow: 0 6px 20px rgba(40, 167, 69, 0.4);
}

.reject-request-modal {
    background: linear-gradient(135deg, #dc3545, #c82333);
    border: none;
    border-radius: 0.5rem;
    padding: 12px 24px;
    font-weight: 600;
    box-shadow: 0 4px 12px rgba(220, 53, 69, 0.3);
    transition: all 0.2s ease;
}

.reject-request-modal:hover {
    background: linear-gradient(135deg, #c82333, #a71e2a);
    transform: translateY(-2px);
    box-shadow: 0 6px 20px rgba(220, 53, 69, 0.4);
}

#confirmRejectBtn {
    background: linear-gradient(135deg,rgb(247, 0, 25), #c82333);
    border: none;
    border-radius: 0.5rem;
    padding: 10px 20px;
    font-weight: 600;
    box-shadow: 0 4px 12px rgba(220, 53, 69, 0.3);
    transition: all 0.2s ease;
}

#confirmRejectBtn:hover {
    background: linear-gradient(135deg, #c82333, #a71e2a);
    transform: translateY(-2px);
    box-shadow: 0 6px 20px rgba(220, 53, 69, 0.4);
}

#cancelRejectBtn {
    background: linear-gradient(135deg, #6c757d, #8B4543);
    border: none;
    border-radius: 0.5rem;
    padding: 10px 20px;
    font-weight: 600;
    box-shadow: 0 4px 12px rgba(108, 117, 125, 0.3);
    transition: all 0.2s ease;
}

#cancelRejectBtn:hover {
    background: linear-gradient(135deg, #5a6268, #7a3d3b);
    transform: translateY(-2px);
    box-shadow: 0 6px 20px rgba(108, 117, 125, 0.4);
}

/* Section Title Styling */
.text-primary {
    color: #8B4543 !important;
}

.text-danger {
    color: #dc3545 !important;
}

/* Ingredient Checklist Styles */
.ingredient-checklist {
    max-height: 300px;
    overflow-y: auto;
    border: 1px solid #e9ecef;
    border-radius: 0.5rem;
    padding: 1rem;
    background: #f8f9fa;
}

.ingredient-item {
    display: flex;
    align-items: center;
    padding: 0.75rem;
    margin-bottom: 0.5rem;
    background: white;
    border: 1px solid #e9ecef;
    border-radius: 0.5rem;
    transition: all 0.2s ease;
}

.ingredient-item:hover {
    border-color: #8B4543;
    box-shadow: 0 2px 8px rgba(139, 69, 67, 0.1);
}

.ingredient-item:last-child {
    margin-bottom: 0;
}

.ingredient-checkbox {
    margin-right: 1rem;
    transform: scale(1.2);
}

.ingredient-checkbox:checked + .ingredient-info {
    color: #8B4543;
    font-weight: 600;
}

.ingredient-info {
    flex: 1;
    display: flex;
    justify-content: space-between;
    align-items: center;
}

.ingredient-name {
    font-weight: 500;
    color: #495057;
}

.ingredient-quantity {
    background: #8B4543;
    color: white;
    padding: 0.25rem 0.75rem;
    border-radius: 1rem;
    font-size: 0.875rem;
    font-weight: 600;
}

.ingredient-item.selected {
    background: linear-gradient(135deg, #f8f9fa 0%, #e9ecef 100%);
    border-color: #8B4543;
}

.ingredient-item.selected .ingredient-name {
    color: #8B4543;
    font-weight: 600;
}

/* Select All/Deselect All Buttons */
#selectAllIngredients, #deselectAllIngredients {
    font-size: 0.875rem;
    padding: 0.375rem 0.75rem;
    border-radius: 0.375rem;
    transition: all 0.2s ease;
}

#selectAllIngredients:hover {
    background: #8B4543;
    border-color: #8B4543;
    color: white;
}

#deselectAllIngredients:hover {
    background: #6c757d;
    border-color: #6c757d;
    color: white;
}

/* Modal Header Enhancement */
.bg-gradient-primary {
    background: linear-gradient(135deg, #8B4543, #a05252) !important;
}

.bg-gradient-primary .modal-title {
    color: white !important;
    font-weight: 600;
    text-shadow: 0 1px 2px rgba(0, 0, 0, 0.1);
}

.bg-gradient-primary .text-white-50 {
    color: rgba(255, 255, 255, 0.9) !important;
    font-weight: 500;
}

.manage-icon {
    width: 40px;
    height: 40px;
    background: rgba(255, 255, 255, 0.2);
    border-radius: 50%;
    display: flex;
    align-items: center;
    justify-content: center;
    color: white;
    font-size: 1.2rem;
    box-shadow: 0 2px 8px rgba(0, 0, 0, 0.1);
}

/* Hide the "Show" dropdown and search */
.dataTables_length {
    display: none !important;
}

.dataTables_filter {
    display: none !important;
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
                            <i class="fas fa-clock me-3"></i>
                            <h5 class="mb-0">Pending Requests</h5>
                        </div>
                        <div class="d-flex align-items-center gap-3">
                            <button id="ingredientFilterBtn" class="btn btn-outline-secondary d-flex align-items-center gap-2" style="border-radius: 25px; padding: 8px 16px; box-shadow: 0 2px 8px rgba(139,69,67,0.08); border: 1.5px solid #8B4543; color: #8B4543; font-weight: 600; transition: background 0.18s, color 0.18s; min-width: 120px; justify-content: center;" title="Show Filters">
                                <i class="fas fa-filter" style="color: #8B4543;"></i>
                                <span style="font-size: 14px; font-weight: 600;">Filter</span>
                            </button>
                        </div>
                    </div>
                </div>
                <div class="card-body">
                                         <!-- Pending Requests Table -->
                     <div class="table-section completed-section">
                        <div class="table-responsive">
                            <table id="completedRequestsTable" class="table table-bordered table-hover">
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
                    <option value="pending">Pending</option>
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

<!-- Enhanced Manage Request Modal -->
<div class="modal fade" id="manageRequestModal" tabindex="-1">
    <div class="modal-dialog modal-dialog-centered modal-lg">
        <div class="modal-content enhanced-manage-modal">
            <div class="modal-header bg-gradient-primary">
                <div class="d-flex align-items-center">
                    <div class="manage-icon me-3">
                        <i class="fas fa-clipboard-list"></i>
                    </div>
                    <div>
                        <h5 class="modal-title mb-0">Manage Request</h5>
                        <small class="text-white-50">Choose an action for this ingredient request</small>
                    </div>
                </div>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <!-- Request Details Section -->
                <div class="request-details-section mb-4">
                    <h6 class="text-primary mb-3">
                        <i class="fas fa-info-circle me-2"></i>Request Information
                    </h6>
                    <div class="row">
                        <div class="col-md-6">
                            <div class="detail-item">
                                <label class="detail-label">Branch:</label>
                                <span class="detail-value" id="modalBranchName">-</span>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="detail-item">
                                <label class="detail-label">Date Requested:</label>
                                <span class="detail-value" id="modalRequestDate">-</span>
                            </div>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-12">
                            <div class="detail-item">
                                <label class="detail-label">Ingredients:</label>
                                <div class="detail-value" id="modalIngredients">-</div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Ingredient Selection Section -->
                <div class="ingredient-selection-section mb-4">
                    <h6 class="text-primary mb-3">
                        <i class="fas fa-list-check me-2"></i>Select Ingredients to Approve
                    </h6>
                    <div class="mb-3">
                        <p class="mb-3">Choose which ingredients to approve from this request:</p>
                        <div class="d-flex justify-content-between align-items-center mb-2">
                            <small class="text-muted">Select ingredients to approve</small>
                            <div>
                                <button type="button" class="btn btn-sm btn-outline-primary" id="selectAllIngredients">
                                    <i class="fas fa-check-double me-1"></i>Select All
                                </button>
                                <button type="button" class="btn btn-sm btn-outline-secondary" id="deselectAllIngredients">
                                    <i class="fas fa-times me-1"></i>Deselect All
                                </button>
                            </div>
                        </div>
                    </div>
                    <div id="ingredientChecklist" class="ingredient-checklist">
                        <!-- Ingredient checkboxes will be populated here -->
                    </div>
                </div>

                <!-- Action Selection Section -->
                <div class="action-selection-section mb-4">
                    <h6 class="text-primary mb-3">
                        <i class="fas fa-cogs me-2"></i>Choose Action
                    </h6>
                <div class="text-center mb-3">
                    <p class="mb-3">What would you like to do with the selected ingredients?</p>
                </div>
                <div class="mb-3">
                    <label for="adminNotes" class="form-label">
                        <i class="fas fa-sticky-note me-2"></i>Admin Notes <span id="notesRequiredIndicator" class="text-muted">(Optional)</span>
                    </label>
                    <textarea class="form-control" id="adminNotes" rows="3" placeholder="Add any specific reasons or comments for this request..."></textarea>
                    <small class="form-text text-muted">
                        <i class="fas fa-info-circle me-1"></i>
                        Use this field to explain your approval decisions or provide additional context.
                    </small>
                </div>
                    <div class="d-grid gap-3">
                        <button type="button" class="btn btn-success btn-lg approve-request-modal" id="approveRequestBtn" disabled>
                            <i class="fas fa-check me-2"></i>Approve Selected Ingredients
                        </button>
                        <button type="button" class="btn btn-danger btn-lg reject-request-modal" id="rejectRequestBtn">
                            <i class="fas fa-times me-2"></i>Reject Entire Request
                        </button>
                    </div>
                </div>

                <!-- Rejection Notes Section (Hidden by default) -->
                <div class="rejection-notes-section" id="rejectionNotesSection" style="display: none;">
                    <h6 class="text-danger mb-3">
                        <i class="fas fa-exclamation-triangle me-2"></i>Rejection Reason
                    </h6>
                    <div class="mb-3">
                        <label for="rejectionNotes" class="form-label">Please provide a reason for rejection:</label>
                        <textarea class="form-control" id="rejectionNotes" rows="3" placeholder="Enter the reason for rejecting this request..."></textarea>
                        <div class="form-text">This note will be visible to the branch manager.</div>
                    </div>
                    <div class="d-flex gap-2">
                        <button type="button" class="btn btn-danger" id="confirmRejectBtn">
                            <i class="fas fa-times me-2"></i>Confirm Rejection
                        </button>
                        <button type="button" class="btn btn-secondary" id="cancelRejectBtn">
                            <i class="fas fa-arrow-left me-2"></i>Back to Actions
                        </button>
                    </div>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">
                    <i class="fas fa-times me-2"></i>Cancel
                </button>
            </div>
        </div>
    </div>
</div>

<script>
$(document).ready(function() {
    // Initialize Completed Requests DataTable
    const completedTable = $('#completedRequestsTable').DataTable({
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
                d.status = 'pending'; // Show only pending requests that need action
                d.ingredient = $('#filterIngredientSelect').val();
                d.delivery_status = $('#filterDeliveryStatusSelect').val();
                d.date_filter = $('#filterDateSelect').val();
                d.date_from = $('#filterDateFrom').val();
                d.date_to = $('#filterDateTo').val();
                d.table_type = 'completed'; // Add table type identifier
                console.log('Completed DataTable AJAX request:', d);
            },
            dataSrc: function(json) {
                console.log('Completed DataTable AJAX response:', json);
                console.log('Total records:', json.recordsTotal);
                console.log('Filtered records:', json.recordsFiltered);
                console.log('Data length:', json.data ? json.data.length : 0);
                console.log('Sample data:', json.data ? json.data[0] : 'No data');
                return json.data || [];
            },
            error: function(xhr, error, thrown) {
                console.error('Completed DataTable AJAX error:', error, thrown);
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
                    // For completed requests, show manage button for pending status
                    if (data.status === 'pending') {
                        buttons += `<button class="btn btn-secondary btn-sm action-menu-btn manage-request-btn" data-id="${data.request_id}" title="Manage Request">
                            <i class="fas fa-edit"></i>
                        </button>`;
                    } else {
                        // For approved/rejected, show view button
                        buttons += `<button class="btn btn-info btn-sm view-request me-1" data-id="${data.request_id}" title="View Details">
                            <i class="fas fa-eye"></i>
                        </button>`;
                    }
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

        // Close filter panel when clicking outside
        document.addEventListener('click', function(event) {
            if (!filterBtn.contains(event.target) && !filterPanel.contains(event.target)) {
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
            $('#completedRequestsTable').DataTable().ajax.reload();
            filterPanel.style.display = 'none';
            
            // Show active filter indicator
            updateActiveFilterIndicator();
        });

        // Clear filters
        clearBtn.addEventListener('click', function() {
            // Reset all filter values
            $('#filterBranchSelect').val('');
            $('#filterIngredientSelect').val('');
            $('#filterDeliveryStatusSelect').val('');
            $('#filterDateSelect').val('');
            $('#filterDateFrom').val('');
            $('#filterDateTo').val('');
            $('#customDateRange').hide();
            
            // Reload the DataTable to show all data
            $('#completedRequestsTable').DataTable().ajax.reload();
            
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
        const ingredient = $('#filterIngredientSelect').val();
        const deliveryStatus = $('#filterDeliveryStatusSelect').val();
        const dateFilter = $('#filterDateSelect').val();
        const dateFrom = $('#filterDateFrom').val();
        const dateTo = $('#filterDateTo').val();
        
        if (branch) {
            const branchName = $('#filterBranchSelect option:selected').text();
            activeFilters.push({ type: 'branch', value: branch, text: branchName });
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
        if ($.fn.DataTable.isDataTable('#completedRequestsTable')) {
            $('#completedRequestsTable').DataTable().ajax.reload();
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
        completedTable.ajax.reload(null, false); // false = stay on current page
    }, 30000);

    // Manual refresh on page focus (when user comes back to tab)
    $(window).focus(function() {
        completedTable.ajax.reload(null, false);
    });

    // Manage request button handler
    $(document).on('click', '.manage-request-btn', function() {
        const requestId = $(this).data('id');
        console.log('Manage request button clicked, request ID:', requestId);
        
        // Get the row data
        const row = completedTable.row($(this).closest('tr')).data();
        console.log('Row data:', row);
        
        // Check if modal exists
        const modal = $('#manageRequestModal');
        console.log('Modal element found:', modal.length > 0);
        
        if (modal.length > 0) {
            modal.data('request-id', requestId);
            
            // Populate modal with request details
            $('#modalBranchName').text(row.branch_name || 'N/A');
            $('#modalRequestDate').text(new Date(row.request_date).toLocaleString());
            $('#modalIngredients').html(row.ingredients || 'N/A');
            
            // Clear the notes field
            $('#adminNotes').val('');
            
            // Populate ingredient checklist
            populateIngredientChecklist(row);
            
            // Reset modal state
            $('#rejectionNotesSection').hide();
            $('#rejectionNotes').val('');
            $('.action-selection-section').show();
            $('.ingredient-selection-section').show();
            
            // Try different ways to show the modal
            try {
                modal.modal('show');
                console.log('Modal should be showing now');
            } catch (error) {
                console.error('Error showing modal:', error);
                // Fallback: show alert
                alert('Request ID: ' + requestId + '\nModal functionality needs to be fixed.');
            }
        } else {
            console.error('Modal element not found!');
            alert('Modal element not found!');
        }
    });

    // Function to populate ingredient checklist with stock information
    function populateIngredientChecklist(row) {
        const checklist = $('#ingredientChecklist');
        checklist.empty();
        
        // Show loading state
        checklist.html('<div class="text-center py-3"><i class="fas fa-spinner fa-spin me-2"></i>Loading ingredient stock information...</div>');
        
        // Parse ingredients from the row data
        let ingredients = [];
        try {
            // Try to get ingredients from ingredients_raw if available
            if (row.ingredients_raw) {
                ingredients = JSON.parse(row.ingredients_raw);
            } else if (row.ingredients) {
                // Parse from ingredients string format
                const ingredientText = row.ingredients;
                console.log('Parsing ingredient text:', ingredientText);
                
                // Try multiple parsing patterns
                let matches = [];
                
                // Pattern 1: "Ingredient Name (quantity unit)" format
                matches = ingredientText.match(/([^(]+)\s*\((\d+)\s*(\w+)\)/g);
                if (matches && matches.length > 0) {
                    ingredients = matches.map(match => {
                        const parts = match.match(/([^(]+)\s*\((\d+)\s*(\w+)\)/);
                        return {
                            ingredient_id: null, // Will be handled by backend
                            ingredient_name: parts[1].trim().replace(/^,\s*/, ''), // Remove leading comma and spaces
                            quantity: parts[2],
                            unit: parts[3]
                        };
                    });
                } else {
                    // Pattern 2: "Unknown Ingredient (ID: X) - Y" format
                    matches = ingredientText.match(/Unknown Ingredient \(ID: (\d+)\) - (\d+)/g);
                    if (matches && matches.length > 0) {
                        ingredients = matches.map(match => {
                            const parts = match.match(/Unknown Ingredient \(ID: (\d+)\) - (\d+)/);
                            return {
                                ingredient_id: parts[1],
                                ingredient_name: `Unknown Ingredient (ID: ${parts[1]})`.replace(/^,\s*/, ''), // Remove leading comma
                                quantity: parts[2],
                                unit: 'pieces'
                            };
                        });
                    } else {
                        // Pattern 3: Simple comma-separated format
                        const simpleMatches = ingredientText.split(',').map(item => {
                            const trimmed = item.trim().replace(/^,\s*/, ''); // Remove leading comma and spaces
                            if (trimmed) {
                                return {
                                    ingredient_id: null,
                                    ingredient_name: trimmed,
                                    quantity: '1',
                                    unit: 'pieces'
                                };
                            }
                            return null;
                        }).filter(item => item !== null);
                        
                        if (simpleMatches.length > 0) {
                            ingredients = simpleMatches;
                        }
                    }
                }
                
                // Clean up ingredient names (remove any remaining commas and extra spaces)
                ingredients = ingredients.map(ingredient => ({
                    ...ingredient,
                    ingredient_name: ingredient.ingredient_name.replace(/^,\s*/, '').replace(/,\s*$/, '').trim()
                }));
                
                console.log('Parsed ingredients:', ingredients);
            }
        } catch (e) {
            console.error('Error parsing ingredients:', e);
        }
        
        if (ingredients.length === 0) {
            checklist.html('<div class="text-center text-muted py-3">No ingredients found</div>');
            return;
        }
        
        // Fetch current stock information for all ingredients
        fetchIngredientStock(ingredients, checklist);
    }
    
    // Function to fetch stock information for ingredients
    function fetchIngredientStock(ingredients, checklist) {
        $.ajax({
            url: 'get_current_stock.php',
            type: 'GET',
            dataType: 'json',
            timeout: 10000,
            success: function(response) {
                console.log('Stock response:', response);
                
                if (response.success && response.ingredients) {
                    // Create a map of ingredient stock data
                    const stockMap = {};
                    response.ingredients.forEach(stockItem => {
                        stockMap[stockItem.ingredient_name] = {
                            quantity: stockItem.ingredient_quantity,
                            unit: stockItem.ingredient_unit,
                            status: stockItem.ingredient_status
                        };
                    });
                    
                    // Debug logging
                    console.log('Stock data received:', response.ingredients);
                    console.log('Stock map created:', stockMap);
                    console.log('Requested ingredients:', ingredients);
                    
                    // Create checklist items with stock information
                    createChecklistItems(ingredients, stockMap, checklist);
                } else {
                    console.log('No stock data received or API failed');
                    // Fallback: create checklist without stock info
                    createChecklistItems(ingredients, {}, checklist);
                }
            },
            error: function(xhr, status, error) {
                console.error('Error fetching stock:', error);
                // Fallback: create checklist without stock info
                createChecklistItems(ingredients, {}, checklist);
            }
        });
    }
    
    // Function to create checklist items with stock information
    function createChecklistItems(ingredients, stockMap, checklist) {
        checklist.empty();
        
        ingredients.forEach((ingredient, index) => {
            const stockInfo = stockMap[ingredient.ingredient_name] || { quantity: 0, unit: ingredient.unit || 'pieces', status: 'Unknown' };
            const availableStock = stockInfo.quantity || 0;
            const stockStatus = availableStock > 0 ? 'available' : 'unavailable';
            const stockColor = availableStock > 0 ? 'success' : 'danger';
            
            // Debug logging for each ingredient
            console.log(`Ingredient: ${ingredient.ingredient_name}, Stock found: ${stockInfo.quantity}, Status: ${stockInfo.status}`);
            
            const item = $(`
                <div class="ingredient-item d-flex align-items-center">
                    <input type="checkbox" class="ingredient-checkbox me-3" id="ingredient_${index}" 
                           data-ingredient-id="${ingredient.ingredient_id || ''}" 
                           data-ingredient-name="${ingredient.ingredient_name}"
                           data-quantity="${ingredient.quantity}" 
                           data-unit="${ingredient.unit || 'pieces'}">
                    <div class="ingredient-info flex-grow-1">
                        <div class="d-flex justify-content-between align-items-center">
                            <div>
                                <span class="ingredient-name">${ingredient.ingredient_name}</span>
                                <br>
                                <small class="text-muted">Requested: ${ingredient.quantity} ${ingredient.unit || 'pieces'}</small>
                            </div>
                            <div class="ms-3">
                                <input type="text" class="form-control stock-input" 
                                       value="Available: ${availableStock} ${stockInfo.unit}" 
                                       readonly 
                                       style="background-color: ${availableStock > 0 ? '#d1ecf1' : '#f8d7da'}; 
                                              color: ${availableStock > 0 ? '#0c5460' : '#721c24'}; 
                                              border-color: ${availableStock > 0 ? '#bee5eb' : '#f5c6cb'};
                                              text-align: center;
                                              font-weight: 600;
                                              min-width: 150px;">
                            </div>
                        </div>
                    </div>
                </div>
            `);
            checklist.append(item);
        });
        
        // Update approve button state
        updateApproveButtonState();
    }
    
    // Function to update approve button state
    function updateApproveButtonState() {
        const checkedIngredients = $('.ingredient-checkbox:checked').length;
        const approveBtn = $('#approveRequestBtn');
        
        if (checkedIngredients > 0) {
            approveBtn.prop('disabled', false);
            approveBtn.html(`<i class="fas fa-check me-2"></i>Approve Selected Ingredients (${checkedIngredients})`);
        } else {
            approveBtn.prop('disabled', true);
            approveBtn.html('<i class="fas fa-check me-2"></i>Approve Selected Ingredients');
        }
    }
    
    // Handle ingredient checkbox changes
    $(document).on('change', '.ingredient-checkbox', function() {
        const item = $(this).closest('.ingredient-item');
        if ($(this).is(':checked')) {
            item.addClass('selected');
        } else {
            item.removeClass('selected');
        }
        updateApproveButtonState();
        updateNotesRequirement();
    });
    
    // Function to update notes requirement based on ingredient selection
    function updateNotesRequirement() {
        const selectedCount = $('.ingredient-checkbox:checked').length;
        const totalCount = $('.ingredient-checkbox').length;
        const allSelected = selectedCount === totalCount;
        
        const notesIndicator = $('#notesRequiredIndicator');
        const adminNotesField = $('#adminNotes');
        
        if (allSelected) {
            // All ingredients selected - notes are optional
            notesIndicator.text('(Optional)').removeClass('text-danger').addClass('text-muted');
            // Clear auto-generated notes if all are selected
            if (adminNotesField.val().includes('Not approved:')) {
                adminNotesField.val('');
            }
        } else if (selectedCount > 0) {
            // Some ingredients selected - notes are required
            notesIndicator.text('(Required)').removeClass('text-muted').addClass('text-danger');
            
            // Auto-generate notes about unselected ingredients
            const unselectedIngredients = [];
            $('.ingredient-checkbox:not(:checked)').each(function() {
                const ingredientName = $(this).data('ingredient-name');
                const quantity = $(this).data('quantity');
                const unit = $(this).data('unit');
                unselectedIngredients.push(`${ingredientName} (${quantity} ${unit})`);
            });
            
            if (unselectedIngredients.length > 0) {
                const autoNotes = `Not approved: ${unselectedIngredients.join(', ')}. `;
                const currentNotes = adminNotesField.val();
                
                // Only auto-populate if the field is empty or doesn't already contain auto-generated content
                if (!currentNotes || !currentNotes.includes('Not approved:')) {
                    adminNotesField.val(autoNotes);
                }
            }
        } else {
            // No ingredients selected - reset to optional
            notesIndicator.text('(Optional)').removeClass('text-danger').addClass('text-muted');
        }
    }
    
    // Select all ingredients
    $(document).on('click', '#selectAllIngredients', function() {
        $('.ingredient-checkbox').prop('checked', true).trigger('change');
        updateNotesRequirement();
    });
    
    // Deselect all ingredients
    $(document).on('click', '#deselectAllIngredients', function() {
        $('.ingredient-checkbox').prop('checked', false).trigger('change');
        updateNotesRequirement();
    });

    // Approve request handler
    $(document).on('click', '.approve-request-modal', function() {
        const requestId = $('#manageRequestModal').data('request-id');
        console.log('Approve request clicked for ID:', requestId);
        
        if (!requestId) {
            Swal.fire({
                icon: 'error',
                title: 'Error',
                text: 'No request ID found',
                confirmButtonColor: '#8B4543'
            });
            return;
        }

        // Close the modal first
        $('#manageRequestModal').modal('hide');
        
        // Show loading
        Swal.fire({
            title: 'Processing...',
            text: 'Approving request...',
            allowOutsideClick: false,
            showConfirmButton: false,
            willOpen: () => {
                Swal.showLoading();
            }
        });

        // Get selected ingredients
        const selectedIngredients = [];
        $('.ingredient-checkbox:checked').each(function() {
            selectedIngredients.push({
                ingredient_id: $(this).data('ingredient-id') || null,
                ingredient_name: $(this).data('ingredient-name'),
                quantity: $(this).data('quantity'),
                unit: $(this).data('unit')
            });
        });
        
        if (selectedIngredients.length === 0) {
            Swal.fire({
                icon: 'warning',
                title: 'No Ingredients Selected',
                text: 'Please select at least one ingredient to approve.',
                confirmButtonColor: '#8B4543'
            });
            return;
        }

        // Check if all ingredients are selected
        const totalIngredients = $('.ingredient-checkbox').length;
        const allSelected = selectedIngredients.length === totalIngredients;
        
        // If not all ingredients are selected, require admin notes
        if (!allSelected) {
            const adminNotes = $('#adminNotes').val().trim();
            if (!adminNotes) {
                Swal.fire({
                    icon: 'warning',
                    title: 'Admin Notes Required',
                    text: 'Since you are not approving all ingredients, please provide notes explaining why only selected ingredients are being approved.',
                    confirmButtonColor: '#8B4543'
                });
                $('#adminNotes').focus();
                return;
            }
        }

        // Send approval request with selected ingredients
        // Get admin notes
        const adminNotes = $('#adminNotes').val().trim();
        
        $.ajax({
            url: 'update_ingredient_request.php',
            type: 'POST',
            data: {
                request_id: requestId,
                status: 'approved',
                notes: adminNotes || 'Request approved',
                selected_ingredients: JSON.stringify(selectedIngredients)
            },
            success: function(response) {
                console.log('Approval response:', response);
                if (response.success) {
                    Swal.fire({
                        icon: 'success',
                        title: 'Request Approved!',
                        text: response.message,
                        confirmButtonColor: '#8B4543',
                        timer: 3000,
                        showConfirmButton: false
                    }).then(() => {
                        // Refresh the table
                        $('#completedRequestsTable').DataTable().ajax.reload();
                    });
                } else {
                    Swal.fire({
                        icon: 'error',
                        title: 'Approval Failed',
                        text: response.message,
                        confirmButtonColor: '#8B4543'
                    });
                }
            },
            error: function() {
                Swal.fire({
                    icon: 'error',
                    title: 'Error',
                    text: 'Failed to approve request',
                    confirmButtonColor: '#8B4543'
                });
            }
        });
    });

    // Reject request handler - show notes section
    $(document).on('click', '.reject-request-modal', function() {
        // Hide action selection and show rejection notes section
        $('.action-selection-section').hide();
        $('#rejectionNotesSection').show();
        $('#rejectionNotes').focus();
    });

    // Cancel reject handler - go back to actions
    $(document).on('click', '#cancelRejectBtn', function() {
        $('#rejectionNotesSection').hide();
        $('.action-selection-section').show();
        $('#rejectionNotes').val('');
    });

    // Confirm reject handler
    $(document).on('click', '#confirmRejectBtn', function() {
        const requestId = $('#manageRequestModal').data('request-id');
        const rejectionNotes = $('#rejectionNotes').val().trim();
        
        console.log('Confirm reject clicked for ID:', requestId);
        console.log('Rejection notes:', rejectionNotes);
        
        if (!requestId) {
            Swal.fire({
                icon: 'error',
                title: 'Error',
                text: 'No request ID found',
                confirmButtonColor: '#8B4543'
            });
            return;
        }

        if (!rejectionNotes) {
            Swal.fire({
                icon: 'warning',
                title: 'Notes Required',
                text: 'Please provide a reason for rejecting this request.',
                confirmButtonColor: '#8B4543'
            });
            $('#rejectionNotes').focus();
            return;
        }

        // Close the modal first
        $('#manageRequestModal').modal('hide');
        
        // Show loading
        Swal.fire({
            title: 'Processing...',
            text: 'Rejecting request...',
            allowOutsideClick: false,
            showConfirmButton: false,
            willOpen: () => {
                Swal.showLoading();
            }
        });

        // Get admin notes from the main notes field
        const adminNotes = $('#adminNotes').val().trim();
        
        // Combine rejection notes with admin notes
        let combinedNotes = rejectionNotes;
        if (adminNotes) {
            combinedNotes = `REJECTION REASON: ${rejectionNotes}\n\nADMIN NOTES: ${adminNotes}`;
        }
        
        // Send rejection request with notes
        $.ajax({
            url: 'update_ingredient_request.php',
            type: 'POST',
            data: {
                request_id: requestId,
                status: 'rejected',
                notes: combinedNotes
            },
            success: function(response) {
                console.log('Rejection response:', response);
                if (response.success) {
                    Swal.fire({
                        icon: 'success',
                        title: 'Request Rejected!',
                        text: response.message,
                        confirmButtonColor: '#8B4543',
                        timer: 3000,
                        showConfirmButton: false
                    }).then(() => {
                        // Refresh the table
                        $('#completedRequestsTable').DataTable().ajax.reload();
                    });
                } else {
                    Swal.fire({
                        icon: 'error',
                        title: 'Rejection Failed',
                        text: response.message,
                        confirmButtonColor: '#8B4543'
                    });
                }
            },
            error: function() {
                Swal.fire({
                    icon: 'error',
                    title: 'Error',
                    text: 'Failed to reject request',
                    confirmButtonColor: '#8B4543'
                });
            }
        });
    });

    // View request handler
    $(document).on('click', '.view-request', function(e) {
        e.preventDefault();
        e.stopPropagation();
        
        const requestId = $(this).data('id');
        console.log('View request clicked:', requestId);
        
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
