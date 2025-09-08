<?php
require_once 'db_connect.php';
require_once 'auth_function.php';

requireLogin();
if ($_SESSION['user_type'] !== 'Stockman') {
    echo "Access denied. Only Stockman can access this page.";
    exit();
}

$isAjax = !empty($_SERVER['HTTP_X_REQUESTED_WITH']) && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) == 'xmlhttprequest';

if (!$isAjax) {
    include('header.php');
}

// Fetch all active categories
$categories = $pdo->query("SELECT category_id, category_name FROM pos_category WHERE status = 'active' ORDER BY category_name")->fetchAll(PDO::FETCH_ASSOC);

// Fetch all ingredients with category info for this stockman's branch
$user_id = $_SESSION['user_id'];
$branch_id = $_SESSION['branch_id'] ?? null;

// If branch_id is not in session, try to fetch from user record
if (!$branch_id) {
    $stmt = $pdo->prepare('SELECT branch_id FROM pos_user WHERE user_id = ?');
    $stmt->execute([$user_id]);
    $branch_id = $stmt->fetchColumn();
}

// Get pre-selected ingredient if provided
$pre_selected_ingredient = $_GET['ingredient_id'] ?? null;

if (!$branch_id) {
    $ingredients = [];
} else {
    $stmt = $pdo->prepare("SELECT i.ingredient_id, i.ingredient_name, i.ingredient_unit, i.ingredient_quantity, i.ingredient_status, i.category_id, c.category_name
        FROM ingredients i
        LEFT JOIN pos_category c ON i.category_id = c.category_id
        WHERE i.branch_id = ?
        ORDER BY c.category_name, i.ingredient_name");
    $stmt->execute([$branch_id]);
    $ingredients = $stmt->fetchAll(PDO::FETCH_ASSOC);
}

// Fetch existing stock update requests for this stockman
$stmt = $pdo->prepare("SELECT * FROM stock_update_requests WHERE stockman_id = ? ORDER BY request_date DESC");
$stmt->execute([$user_id]);
$existing_requests = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>

<style>
:root {
    --primary-color: #8B4543;
    --primary-dark: #723937;
    --success-color: #28a745;
    --warning-color: #ffc107;
    --danger-color: #dc3545;
    --info-color: #17a2b8;
}


.requests-table {
    background: white;
    border-radius: 12px;
    overflow: hidden;
    box-shadow: 0 4px 6px rgba(0,0,0,0.05);
    margin-bottom: 2rem;
}

.requests-table .table {
    margin: 0;
}

.requests-table .table th {
    background: var(--primary-color);
    color: white;
    border: none;
    padding: 1rem;
    font-weight: 600;
}

.requests-table .table td {
    padding: 1rem;
    vertical-align: middle;
    border-color: #eee;
}

.requests-table .btn-info {
    background: var(--info-color);
    border: none;
    color: white;
    padding: 0.375rem 0.75rem;
    border-radius: 0.5rem;
    font-size: 0.75rem;
    font-weight: 500;
    transition: all 0.2s ease;
}

.requests-table .btn-info:hover {
    background: #138496;
    transform: translateY(-1px);
    box-shadow: 0 2px 4px rgba(23, 162, 184, 0.3);
}

/* Pagination Controls Styling */
.pagination-controls .btn-outline-secondary {
    border: none;
    color: #6c757d;
    background: transparent;
    padding: 0.375rem 0.75rem;
    font-size: 0.875rem;
    transition: all 0.2s ease;
}

.pagination-controls .btn-outline-secondary:hover:not(:disabled) {
    background: #6c757d;
    color: white;
    transform: translateY(-1px);
}

.pagination-controls .btn-outline-secondary:disabled {
    opacity: 0.5;
    cursor: not-allowed;
}

.pagination-controls .btn-outline-secondary:not(:disabled):active {
    transform: translateY(0);
}

.table {
    margin: 0;
}

.table th {
    background: var(--primary-color);
    color: white;
    border: none;
    padding: 1rem;
    font-weight: 600;
}

.table td {
    padding: 1rem;
    vertical-align: middle;
    border-color: #eee;
}

.status-badge {
    padding: 0.375rem 0.75rem;
    border-radius: 0.5rem;
    font-size: 0.75rem;
    font-weight: 500;
    text-transform: uppercase;
    letter-spacing: 0.5px;
}

.status-badge.pending {
    background: linear-gradient(135deg, var(--warning-color), #fd7e14);
    color: #212529;
}

.status-badge.approved {
    background: linear-gradient(135deg, var(--success-color), #20c997);
    color: white;
}

.status-badge.rejected {
    background: linear-gradient(135deg, var(--danger-color), #e74c3c);
    color: white;
}

.status-badge.completed {
    background: linear-gradient(135deg, var(--info-color), #6f42c1);
    color: white;
}

.urgency-badge {
    padding: 0.25rem 0.5rem;
    border-radius: 0.25rem;
    font-size: 0.7rem;
    font-weight: 600;
    text-transform: uppercase;
}

.urgency-badge.low {
    background: #d4edda;
    color: #155724;
}

.urgency-badge.medium {
    background: #fff3cd;
    color: #856404;
}

.urgency-badge.high {
    background: #f8d7da;
    color: #721c24;
}

.urgency-badge.critical {
    background: #721c24;
    color: white;
}


.loading {
    text-align: center;
    padding: 2rem;
    color: #6c757d;
}

.no-data {
    text-align: center;
    padding: 3rem;
    color: #6c757d;
}

/* Enhanced Modal Styles */
.enhanced-delivery-modal {
    border: none;
    border-radius: 20px;
    box-shadow: 0 25px 50px rgba(0, 0, 0, 0.15), 0 0 0 1px rgba(255, 255, 255, 0.1);
    backdrop-filter: blur(10px);
    background: linear-gradient(135deg, #ffffff 0%, #f8f9fa 100%);
    overflow: hidden;
    animation: modalSlideIn 0.3s ease-out;
}

@keyframes modalSlideIn {
    from {
        opacity: 0;
        transform: translateY(-50px) scale(0.95);
    }
    to {
        opacity: 1;
        transform: translateY(0) scale(1);
    }
}

.enhanced-modal-header {
    background: linear-gradient(135deg, var(--primary-color) 0%, var(--primary-dark) 100%);
    border: none;
    padding: 2rem;
    position: relative;
    overflow: hidden;
}

.enhanced-modal-header::before {
    content: '';
    position: absolute;
    top: 0;
    left: 0;
    right: 0;
    bottom: 0;
    background: url('data:image/svg+xml,<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 100 100"><defs><pattern id="grain" width="100" height="100" patternUnits="userSpaceOnUse"><circle cx="25" cy="25" r="1" fill="rgba(255,255,255,0.1)"/><circle cx="75" cy="75" r="1" fill="rgba(255,255,255,0.1)"/><circle cx="50" cy="10" r="0.5" fill="rgba(255,255,255,0.05)"/></pattern></defs><rect width="100" height="100" fill="url(%23grain)"/></svg>');
    opacity: 0.3;
}

.modal-title-container {
    display: flex;
    align-items: center;
    gap: 1rem;
    position: relative;
    z-index: 1;
}

.modal-icon {
    width: 60px;
    height: 60px;
    background: rgba(255, 255, 255, 0.2);
    border-radius: 50%;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 1.5rem;
    color: white;
    backdrop-filter: blur(10px);
    border: 2px solid rgba(255, 255, 255, 0.3);
    animation: iconPulse 2s infinite;
}

@keyframes iconPulse {
    0%, 100% { transform: scale(1); }
    50% { transform: scale(1.05); }
}

.modal-title-text {
    flex: 1;
}

.modal-title {
    color: white;
    font-size: 1.5rem;
    font-weight: 700;
    margin: 0;
    text-shadow: 0 2px 4px rgba(0, 0, 0, 0.3);
}

.modal-subtitle {
    color: rgba(255, 255, 255, 0.9);
    font-size: 0.9rem;
    margin: 0.25rem 0 0 0;
    font-weight: 400;
}

.enhanced-close {
    background: rgba(255, 255, 255, 0.2);
    border: none;
    border-radius: 50%;
    width: 40px;
    height: 40px;
    display: flex;
    align-items: center;
    justify-content: center;
    color: white;
    font-size: 1rem;
    transition: all 0.3s ease;
    position: relative;
    z-index: 1;
}

.enhanced-close:hover {
    background: rgba(255, 255, 255, 0.3);
    transform: scale(1.1);
}

.enhanced-modal-body {
    padding: 2rem;
    background: white;
}

.enhanced-delivery-form {
    display: flex;
    flex-direction: column;
    gap: 2rem;
}

.form-section {
    background: linear-gradient(135deg, #f8f9fa 0%, #ffffff 100%);
    border-radius: 15px;
    padding: 1.5rem;
    border: 1px solid #e9ecef;
    position: relative;
    overflow: hidden;
    transition: all 0.3s ease;
}

.form-section:hover {
    transform: translateY(-2px);
    box-shadow: 0 8px 25px rgba(0, 0, 0, 0.1);
    border-color: var(--primary-color);
}

.form-section::before {
    content: '';
    position: absolute;
    top: 0;
    left: 0;
    width: 4px;
    height: 100%;
    background: linear-gradient(135deg, var(--primary-color), var(--info-color));
    border-radius: 0 2px 2px 0;
}

.section-header {
    display: flex;
    align-items: center;
    gap: 0.75rem;
    margin-bottom: 1.5rem;
    padding-bottom: 0.75rem;
    border-bottom: 2px solid #e9ecef;
}

.section-header i {
    color: var(--primary-color);
    font-size: 1.1rem;
}

.section-header h6 {
    color: var(--primary-color);
    font-weight: 600;
    margin: 0;
    font-size: 1rem;
    text-transform: uppercase;
    letter-spacing: 0.5px;
}

.form-group.enhanced {
    margin-bottom: 0;
}

.enhanced-label {
    display: flex;
    align-items: center;
    gap: 0.5rem;
    font-weight: 600;
    color: #495057;
    margin-bottom: 0.75rem;
    font-size: 0.9rem;
}

.enhanced-label i {
    color: var(--primary-color);
    font-size: 0.9rem;
}

.enhanced-select,
.enhanced-input {
    border: 2px solid #e9ecef;
    border-radius: 10px;
    padding: 0.75rem 1rem;
    font-size: 0.95rem;
    transition: all 0.3s ease;
    background: white;
}

.enhanced-select:focus,
.enhanced-input:focus {
    border-color: var(--primary-color);
    box-shadow: 0 0 0 4px rgba(139, 69, 67, 0.1);
    outline: none;
}

.enhanced-select:hover,
.enhanced-input:hover {
    border-color: var(--primary-color);
}

.input-group.enhanced {
    position: relative;
}

.enhanced-addon {
    background: var(--primary-color);
    border: 2px solid var(--primary-color);
    color: white;
    border-radius: 0 10px 10px 0;
    border-left: none;
}

.enhanced-textarea {
    border: 2px solid #e9ecef;
    border-radius: 10px;
    padding: 0.75rem 1rem;
    font-size: 0.95rem;
    transition: all 0.3s ease;
    background: white;
    resize: vertical;
    min-height: 100px;
}

.enhanced-textarea:focus {
    border-color: var(--primary-color);
    box-shadow: 0 0 0 4px rgba(139, 69, 67, 0.1);
    outline: none;
}

.enhanced-textarea:hover {
    border-color: var(--primary-color);
}

.enhanced-help {
    display: flex;
    align-items: center;
    gap: 0.5rem;
    color: #6c757d;
    font-size: 0.8rem;
    margin-top: 0.5rem;
    font-style: italic;
}

.enhanced-help i {
    color: var(--info-color);
}

.enhanced-modal-footer {
    background: linear-gradient(135deg, #f8f9fa 0%, #ffffff 100%);
    border: none;
    padding: 1.5rem 2rem;
    display: flex;
    justify-content: flex-end;
    gap: 1rem;
}

.enhanced-btn-secondary {
    background: #6c757d;
    border: 2px solid #6c757d;
    color: white;
    padding: 0.75rem 1.5rem;
    border-radius: 10px;
    font-weight: 600;
    transition: all 0.3s ease;
    display: flex;
    align-items: center;
    gap: 0.5rem;
}

.enhanced-btn-secondary:hover {
    background: #5a6268;
    border-color: #5a6268;
    transform: translateY(-2px);
    box-shadow: 0 4px 12px rgba(108, 117, 125, 0.3);
}

.enhanced-btn-primary {
    background: linear-gradient(135deg, var(--primary-color) 0%, var(--primary-dark) 100%);
    border: 2px solid var(--primary-color);
    color: white;
    padding: 0.75rem 1.5rem;
    border-radius: 10px;
    font-weight: 600;
    transition: all 0.3s ease;
    display: flex;
    align-items: center;
    gap: 0.5rem;
    position: relative;
    overflow: hidden;
}

.enhanced-btn-primary::before {
    content: '';
    position: absolute;
    top: 0;
    left: -100%;
    width: 100%;
    height: 100%;
    background: linear-gradient(90deg, transparent, rgba(255, 255, 255, 0.2), transparent);
    transition: left 0.5s ease;
}

.enhanced-btn-primary:hover::before {
    left: 100%;
}

.enhanced-btn-primary:hover {
    background: linear-gradient(135deg, var(--primary-dark) 0%, var(--primary-color) 100%);
    transform: translateY(-2px);
    box-shadow: 0 6px 20px rgba(139, 69, 67, 0.4);
}

.enhanced-btn-primary:active {
    transform: translateY(0);
}

/* True List Checklist Styles */
.item-checklist {
    background: transparent;
    border: none;
    padding: 0;
    max-height: 300px;
    overflow-y: auto;
}

.no-items-message {
    text-align: center;
    color: #6c757d;
    padding: 1.5rem;
    font-style: italic;
}

.item-checklist-item {
    display: flex;
    align-items: center;
    gap: 1rem;
    padding: 0.75rem 0;
    background: transparent;
    border: none;
    border-bottom: 1px solid #e9ecef;
    transition: none;
}

.item-checklist-item:hover {
    background: transparent;
}

.item-checklist-item:last-child {
    border-bottom: none;
}

.item-checkbox {
    width: 16px;
    height: 16px;
    border: 1px solid #6c757d;
    border-radius: 2px;
    cursor: pointer;
    position: relative;
    transition: none;
    flex-shrink: 0;
}

.item-checkbox.checked {
    background: var(--primary-color);
    border-color: var(--primary-color);
}

.item-checkbox.checked::after {
    content: '✓';
    position: absolute;
    top: 50%;
    left: 50%;
    transform: translate(-50%, -50%);
    color: white;
    font-size: 10px;
    font-weight: bold;
}

.item-info {
    flex: 1;
    display: flex;
    flex-direction: column;
    gap: 0.1rem;
}

.item-name {
    font-weight: 500;
    color: #212529;
    font-size: 0.9rem;
}

.item-details {
    font-size: 0.8rem;
    color: #6c757d;
    display: flex;
    gap: 1rem;
}

.item-quantity-group {
    display: flex;
    align-items: center;
    gap: 0.5rem;
    min-width: 120px;
}

.quantity-input {
    width: 50px;
    padding: 0.2rem 0.3rem;
    border: 1px solid #ced4da;
    border-radius: 2px;
    font-size: 0.8rem;
    text-align: center;
    transition: none;
}

.quantity-input[readonly] {
    background-color: #f8f9fa;
    cursor: not-allowed;
    color: #6c757d;
}

.quantity-input:not([readonly]) {
    background-color: #fff;
    cursor: text;
    color: #212529;
}

.quantity-unit {
    font-size: 0.8rem;
    color: #6c757d;
    min-width: 40px;
}

.return-toggle {
    display: flex;
    align-items: center;
    gap: 0.3rem;
    font-size: 0.8rem;
    color: #dc3545;
    cursor: pointer;
    padding: 0.2rem 0.5rem;
    border-radius: 2px;
    transition: none;
    background: transparent;
    border: 1px solid #dc3545;
}

.return-toggle:hover {
    background: rgba(220, 53, 69, 0.1);
}

.return-toggle.active {
    background: #dc3545;
    color: white;
}

/* Return Items Styles */
.return-items-list {
    display: flex;
    flex-direction: column;
    gap: 1rem;
}

.return-item {
    background: #fff5f5;
    border: 2px solid #fecaca;
    border-radius: 8px;
    padding: 1rem;
    animation: slideIn 0.3s ease-out;
}

@keyframes slideIn {
    from {
        opacity: 0;
        transform: translateX(-20px);
    }
    to {
        opacity: 1;
        transform: translateX(0);
    }
}

.return-item-header {
    display: flex;
    justify-content: between;
    align-items: center;
    margin-bottom: 0.75rem;
}

.return-item-name {
    font-weight: 600;
    color: #dc3545;
    flex: 1;
}

.return-reasons {
    display: flex;
    flex-direction: column;
    gap: 0.75rem;
}

.reason-group {
    display: flex;
    align-items: center;
    gap: 0.75rem;
}

.reason-checkbox {
    width: 18px;
    height: 18px;
    border: 2px solid #dc3545;
    border-radius: 3px;
    cursor: pointer;
    position: relative;
    transition: all 0.3s ease;
}

.reason-checkbox.checked {
    background: #dc3545;
    border-color: #dc3545;
}

.reason-checkbox.checked::after {
    content: '✓';
    position: absolute;
    top: 50%;
    left: 50%;
    transform: translate(-50%, -50%);
    color: white;
    font-size: 10px;
    font-weight: bold;
}

.reason-label {
    font-size: 0.85rem;
    color: #495057;
    cursor: pointer;
    flex: 1;
}

.return-quantity-group {
    display: flex;
    align-items: center;
    gap: 0.5rem;
    margin-top: 0.5rem;
}

.return-quantity-input {
    width: 70px;
    padding: 0.25rem 0.5rem;
    border: 1px solid #dc3545;
    border-radius: 4px;
    font-size: 0.8rem;
    text-align: center;
    background: white;
}

.return-quantity-unit {
    font-size: 0.75rem;
    color: #dc3545;
    font-weight: 500;
}

.return-notes {
    margin-top: 0.75rem;
}

.return-notes textarea {
    width: 100%;
    padding: 0.5rem;
    border: 1px solid #dc3545;
    border-radius: 4px;
    font-size: 0.8rem;
    resize: vertical;
    min-height: 60px;
    background: white;
}

.return-notes textarea:focus {
    outline: none;
    border-color: #dc3545;
    box-shadow: 0 0 0 2px rgba(220, 53, 69, 0.2);
}



/* Enhanced Return Reasons */
.return-priority {
    margin-top: 1rem;
    padding: 0.75rem;
    background: #fff8e1;
    border-radius: 8px;
    border: 1px solid #ffecb3;
}

.priority-label {
    display: flex;
    align-items: center;
    gap: 0.5rem;
    font-size: 0.85rem;
    font-weight: 600;
    color: #f57c00;
    margin-bottom: 0.5rem;
}

.priority-select {
    width: 100%;
    padding: 0.5rem;
    border: 1px solid #ffecb3;
    border-radius: 6px;
    background: white;
    font-size: 0.85rem;
    color: #495057;
}

.priority-select:focus {
    outline: none;
    border-color: #f57c00;
    box-shadow: 0 0 0 2px rgba(245, 124, 0, 0.2);
}

.reason-label {
    display: flex;
    align-items: center;
    gap: 0.5rem;
}

.reason-label i {
    font-size: 0.9rem;
    color: #dc3545;
}

/* Modal backdrop enhancement */
.modal-backdrop {
    background: rgba(0, 0, 0, 0.6);
    backdrop-filter: blur(5px);
}

/* Responsive design */
@media (max-width: 768px) {
    .stats-cards {
        grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
    }
    
    .dashboard-title {
        font-size: 1.8rem;
    }
    
    .branch-info {
        flex-direction: column;
        align-items: flex-start;
        gap: 1rem;
    }
    
    .branch-badge, .ingredients-count {
        font-size: 0.9rem;
    }
    
    /* Mobile modal adjustments */
    .enhanced-modal-header {
        padding: 1.5rem;
    }
    
    .modal-icon {
        width: 50px;
        height: 50px;
        font-size: 1.2rem;
    }
    
    .modal-title {
        font-size: 1.3rem;
    }
    
    .enhanced-modal-body {
        padding: 1.5rem;
    }
    
    .enhanced-delivery-form {
        gap: 1.5rem;
    }
    
    .form-section {
        padding: 1rem;
    }
    
    .enhanced-modal-footer {
        padding: 1rem 1.5rem;
        flex-direction: column;
    }
    
    .enhanced-btn-secondary,
    .enhanced-btn-primary {
        width: 100%;
        justify-content: center;
    }
}
</style>
        
        <!-- Stock Requests Updates Table -->
        <div class="requests-table">
            <div class="d-flex justify-content-between align-items-center p-3 border-bottom">
                <h5 class="mb-0">
                    <i class="fas fa-clipboard-check me-2"></i>
                    Stock Requests Updates
                </h5>
                <button class="btn btn-outline-primary btn-sm" onclick="refreshRequests()">
                    <i class="fas fa-sync-alt"></i> Refresh
                </button>
            </div>
            
            <div class="table-responsive">
                <table class="table table-hover" id="requestsTable">
                    <thead>
                        <tr>
                            <th>Date Requested</th>
                            <th>Ingredients</th>
                            <th>Status</th>
                            <th>Delivery Status</th>
                            <th>Delivery Notes</th>
                            <th>Updated By</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr>
                            <td colspan="7" class="loading">
                                <div class="spinner-border text-primary" role="status">
                                    <span class="visually-hidden">Loading...</span>
                                </div>
                                <p>Loading your requests...</p>
                            </td>
                        </tr>
                    </tbody>
                </table>
                
                <!-- Pagination Controls -->
                <div class="d-flex justify-content-between align-items-center p-3 border-top">
                    <div class="text-muted">
                        Showing <span id="startRecord">0</span> to <span id="endRecord">0</span> of <span id="totalRecords">0</span> entries
                    </div>
                    <div class="pagination-controls">
                        <button class="btn btn-outline-secondary btn-sm" id="prevBtn" onclick="previousPage()" disabled>
                            <i class="fas fa-chevron-left"></i> Previous
                        </button>
                        <button class="btn btn-outline-secondary btn-sm ms-2" id="nextBtn" onclick="nextPage()">
                            Next <i class="fas fa-chevron-right"></i>
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Enhanced Delivery Status Update Modal -->
<div class="modal fade" id="deliveryModal" tabindex="-1" data-bs-backdrop="static" data-bs-keyboard="false">
    <div class="modal-dialog modal-dialog-centered modal-lg">
        <div class="modal-content enhanced-delivery-modal">
            <div class="modal-header enhanced-modal-header">
                <div class="modal-title-container">
                    <div class="modal-icon">
                        <i class="fas fa-truck"></i>
            </div>
                    <div class="modal-title-text">
                        <h5 class="modal-title">Update Delivery Status</h5>
                        <p class="modal-subtitle">Track and update delivery information</p>
                    </div>
                </div>
                <button type="button" class="btn-close enhanced-close" data-bs-dismiss="modal" aria-label="Close">
                    <i class="fas fa-times"></i>
                </button>
            </div>
            <div class="modal-body enhanced-modal-body">
                <form id="deliveryForm" class="enhanced-delivery-form">
                    <input type="hidden" id="deliveryRequestId">
                    
                    <!-- Delivery Status Section -->
                    <div class="form-section">
                        <div class="section-header">
                            <i class="fas fa-clipboard-check"></i>
                            <h6>Delivery Status</h6>
                        </div>
                        <div class="form-group enhanced">
                            <label class="form-label enhanced-label">
                                <i class="fas fa-flag"></i>
                                Status
                            </label>
                            <select class="form-select enhanced-select" id="deliveryStatus">
                                <option value="delivered">
                                    <i class="fas fa-check-circle"></i> Delivered
                                </option>
                                <option value="partially_delivered">
                                    <i class="fas fa-clock"></i> Partially Delivered
                                </option>
                                <option value="returned">
                                    <i class="fas fa-undo"></i> Returned
                                </option>
                                <option value="cancelled">
                                    <i class="fas fa-times-circle"></i> Cancelled
                                </option>
                                <option value="on_hold">
                                    <i class="fas fa-pause-circle"></i> On Hold
                                </option>
                                <option value="rescheduled">
                                    <i class="fas fa-calendar-alt"></i> Rescheduled
                                </option>
                        </select>
                    </div>
                    </div>




                    <!-- Item Checklist Section -->
                    <div class="form-section">
                        <div class="section-header">
                            <i class="fas fa-clipboard-list"></i>
                            <h6>Item Checklist</h6>
                        </div>
                        <div class="form-group enhanced">
                            <label class="form-label enhanced-label">
                                <i class="fas fa-boxes"></i>
                                Received Items
                            </label>
                            <div id="itemChecklist" class="item-checklist">
                                <!-- Items will be dynamically loaded here -->
                                <div class="no-items-message">
                                    <i class="fas fa-info-circle"></i>
                                    Loading items from your request...
                                </div>
                            </div>
                            <div class="form-text enhanced-help">
                                <i class="fas fa-info-circle"></i>
                                Check items as received and specify quantities
                            </div>
                        </div>
                    </div>

                    <!-- Return Items Section -->
                    <div class="form-section" id="returnSection" style="display: none;">
                        <div class="section-header">
                            <i class="fas fa-undo"></i>
                            <h6>Return Items</h6>
                        </div>
                        <div class="form-group enhanced">
                            <label class="form-label enhanced-label">
                                <i class="fas fa-exclamation-triangle"></i>
                                Return Reasons
                            </label>
                            <div id="returnItemsList" class="return-items-list">
                                <!-- Return items will be dynamically added here -->
                            </div>
                        </div>
                    </div>

                    <!-- Delivery Notes Section -->
                    <div class="form-section">
                        <div class="section-header">
                            <i class="fas fa-sticky-note"></i>
                            <h6>Additional Information</h6>
                        </div>
                        <div class="form-group enhanced">
                            <label class="form-label enhanced-label" for="deliveryNotes">
                                <i class="fas fa-comment-alt"></i>
                                Delivery Notes
                            </label>
                            <textarea class="form-control enhanced-textarea" id="deliveryNotes" rows="4" 
                                placeholder="Enter delivery notes, return reasons, or cancellation details..."></textarea>
                            <div class="form-text enhanced-help">
                                <i class="fas fa-info-circle"></i>
                                Required for returned or cancelled deliveries
                            </div>
                        </div>
                    </div>
                </form>
            </div>
            <div class="modal-footer enhanced-modal-footer">
                <button type="button" class="btn btn-outline-secondary enhanced-btn-secondary" data-bs-dismiss="modal">
                    <i class="fas fa-times"></i>
                    Close
                </button>
                <button type="button" class="btn btn-primary enhanced-btn-primary" id="updateDelivery">
                    <i class="fas fa-save"></i>
                    Update Delivery
                </button>
            </div>
        </div>
    </div>
</div>

<script>
// Pagination variables - Updated to show 10 entries per page
let allRequests = [];
let currentPage = 1;
const itemsPerPage = 10; // Changed from 5 to 10

// Initialize page
document.addEventListener('DOMContentLoaded', function() {
    loadRequests();
    setupDeliveryHandlers();
});


function loadRequests() {
    fetch('get_stockman_requests.php')
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                allRequests = data.data;
                currentPage = 1;
                displayRequests();
            } else {
                document.getElementById('requestsTable').querySelector('tbody').innerHTML = 
                    `<tr><td colspan="7" class="no-data">❌ Error: ${data.error}</td></tr>`;
            }
        })
        .catch(error => {
            console.error('Error loading requests:', error);
            document.getElementById('requestsTable').querySelector('tbody').innerHTML = 
                `<tr><td colspan="7" class="no-data">❌ Error loading requests</td></tr>`;
        });
}

function displayRequests() {
    const tbody = document.getElementById('requestsTable').querySelector('tbody');
    
    if (allRequests.length === 0) {
        tbody.innerHTML = '<tr><td colspan="7" class="no-data">📝 No requests found</td></tr>';
        updatePaginationInfo(0, 0, 0);
        updatePaginationButtons(false, false);
        return;
    }
    
    // Calculate pagination
    const totalRecords = allRequests.length;
    const totalPages = Math.ceil(totalRecords / itemsPerPage);
    const startIndex = (currentPage - 1) * itemsPerPage;
    const endIndex = Math.min(startIndex + itemsPerPage, totalRecords);
    const currentPageData = allRequests.slice(startIndex, endIndex);
    
    // Display current page data
    tbody.innerHTML = currentPageData.map(request => {
        // Add delivery update button for approved requests that are not delivered, cancelled, or returned
        let actionButton = '';
        if (request.status.includes('APPROVED') && 
            request.delivery_status_raw !== 'delivered' && 
            request.delivery_status_raw !== 'cancelled' && 
            request.delivery_status_raw !== 'returned') {
            actionButton = `<button class="btn btn-info btn-sm update-delivery" data-id="${request.request_id}" onclick="console.log('Button clicked for request:', ${request.request_id}); updateDeliveryStatus(${request.request_id})">
                <i class="fas fa-truck"></i> Update Delivery
            </button>`;
        }
        
        return `
            <tr>
                <td>${request.request_date}</td>
                <td>${request.ingredients}</td>
                <td>${request.status}</td>
                <td>${request.delivery_status}</td>
                <td>${request.delivery_notes}</td>
                <td>${request.updated_by}</td>
                <td>${actionButton}</td>
            </tr>
        `;
    }).join('');
    
    // Update pagination info and buttons
    updatePaginationInfo(startIndex + 1, endIndex, totalRecords);
    updatePaginationButtons(currentPage > 1, currentPage < totalPages);
}

function updatePaginationInfo(start, end, total) {
    document.getElementById('startRecord').textContent = start;
    document.getElementById('endRecord').textContent = end;
    document.getElementById('totalRecords').textContent = total;
}

function updatePaginationButtons(hasPrevious, hasNext) {
    document.getElementById('prevBtn').disabled = !hasPrevious;
    document.getElementById('nextBtn').disabled = !hasNext;
}

function previousPage() {
    if (currentPage > 1) {
        currentPage--;
        displayRequests();
    }
}

function nextPage() {
    const totalPages = Math.ceil(allRequests.length / itemsPerPage);
    if (currentPage < totalPages) {
        currentPage++;
        displayRequests();
    }
}

function setupDeliveryHandlers() {
    // Delivery status change handler
    document.getElementById('deliveryStatus').addEventListener('change', function() {
        const status = this.value;
        const notesField = document.getElementById('deliveryNotes');
        const notesLabel = document.querySelector('label[for="deliveryNotes"]');
        const helpText = document.querySelector('.enhanced-help');
        
        if (status === 'returned' || status === 'cancelled') {
            notesField.setAttribute('required', 'required');
            notesLabel.innerHTML = '<i class="fas fa-comment-alt"></i> Delivery Notes <span class="text-danger">*</span>';
            notesField.classList.add('border-warning');
            helpText.innerHTML = '<i class="fas fa-exclamation-triangle"></i> Required for returned or cancelled deliveries';
            helpText.style.color = '#dc3545';
        } else {
            notesField.removeAttribute('required');
            notesLabel.innerHTML = '<i class="fas fa-comment-alt"></i> Delivery Notes';
            notesField.classList.remove('border-warning');
            helpText.innerHTML = '<i class="fas fa-info-circle"></i> Required for returned or cancelled deliveries';
            helpText.style.color = '#6c757d';
        }
    });
    
    // Update delivery button handler
    document.getElementById('updateDelivery').addEventListener('click', function() {
        const requestId = document.getElementById('deliveryRequestId').value;
        const deliveryStatus = document.getElementById('deliveryStatus').value;
        const deliveryNotes = document.getElementById('deliveryNotes').value.trim();
        
        // Automatically set delivery date/time to current timestamp
        const now = new Date();
        const deliveryDate = now.toISOString().slice(0, 19).replace('T', ' '); // Format: YYYY-MM-DD HH:MM:SS
        
        // Collect item checklist data
        const itemChecklistData = collectItemChecklistData();
        const returnItemsData = collectReturnItemsData();
        
        console.log('Item Checklist Data:', itemChecklistData);
        console.log('Return Items Data:', returnItemsData);
        
        // Validate required fields
        if ((deliveryStatus === 'returned' || deliveryStatus === 'cancelled') && !deliveryNotes) {
            Swal.fire({
                icon: 'warning',
                title: 'Delivery Notes Required',
                text: 'Please provide delivery notes when returning or cancelling a delivery.',
                confirmButtonColor: '#8B4543'
            });
            return;
        }
        
        // Validate return items have reasons
        if (returnItemsData.length > 0) {
            const hasValidReturns = returnItemsData.every(item => 
                item.reasons.length > 0 && item.return_quantity > 0
            );
            
            if (!hasValidReturns) {
                Swal.fire({
                    icon: 'warning',
                    title: 'Return Information Required',
                    text: 'Please provide return reasons and quantities for all returned items.',
                    confirmButtonColor: '#8B4543'
                });
                return;
            }
        }
        
        // Submit delivery update
        fetch('update_delivery_status.php', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
            },
            body: JSON.stringify({
                request_id: requestId,
                delivery_status: deliveryStatus,
                delivery_date: deliveryDate,
                delivery_notes: deliveryNotes,
                item_checklist: itemChecklistData,
                return_items: returnItemsData
            })
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                Swal.fire({
                    icon: 'success',
                    title: 'Delivery Updated!',
                    text: 'Delivery status has been updated successfully.',
                    confirmButtonColor: '#8B4543'
                });
                
                // Close modal and refresh requests
                bootstrap.Modal.getInstance(document.getElementById('deliveryModal')).hide();
                loadRequests();
            } else {
                Swal.fire({
                    icon: 'error',
                    title: 'Update Failed',
                    text: data.error || 'An error occurred while updating delivery status.',
                    confirmButtonColor: '#8B4543'
                });
            }
        })
        .catch(error => {
            console.error('Error updating delivery:', error);
            Swal.fire({
                icon: 'error',
                title: 'Update Failed',
                text: 'An error occurred while updating delivery status.',
                confirmButtonColor: '#8B4543'
            });
        });
    });
    
    // Modal hidden event
    document.getElementById('deliveryModal').addEventListener('hidden.bs.modal', function () {
        document.getElementById('deliveryForm').reset();
        document.getElementById('deliveryNotes').classList.remove('border-warning');
        const notesLabel = document.querySelector('label[for="deliveryNotes"]');
        const helpText = document.querySelector('.enhanced-help');
        notesLabel.innerHTML = '<i class="fas fa-comment-alt"></i> Delivery Notes';
        helpText.innerHTML = '<i class="fas fa-info-circle"></i> Required for returned or cancelled deliveries';
        helpText.style.color = '#6c757d';
        
        // Clear checklist data
        document.getElementById('itemChecklist').innerHTML = 
            '<div class="no-items-message"><i class="fas fa-info-circle"></i> Loading items from your request...</div>';
        document.getElementById('returnSection').style.display = 'none';
        document.getElementById('returnItemsList').innerHTML = '';
    });
}

function updateDeliveryStatus(requestId) {
    // Show delivery status update modal
    console.log('Updating delivery status for request ID:', requestId);
    
    // Check if modal exists
    const modalElement = document.getElementById('deliveryModal');
    if (!modalElement) {
        console.error('Modal element not found!');
        alert('Error: Modal not found. Please refresh the page.');
        return;
    }
    
    // Check if request ID input exists
    const requestIdInput = document.getElementById('deliveryRequestId');
    if (!requestIdInput) {
        console.error('Request ID input not found!');
        alert('Error: Request ID input not found. Please refresh the page.');
        return;
    }
    
    requestIdInput.value = requestId;
    
    // Load items for this request
    loadRequestItems(requestId);
    
    // Show modal
    const modal = new bootstrap.Modal(modalElement);
    modal.show();
    
    console.log('Modal should be showing now...');
}

function loadRequestItems(requestId) {
    console.log('Loading items for request ID:', requestId);
    
    // First, let's check if this is a valid request ID
    if (!requestId || requestId === 'undefined') {
        console.error('Invalid request ID:', requestId);
        displayItemChecklist({});
        return;
    }
    
    // Fetch request details to get the items
    fetch(`get_ingredient_request_details.php?request_id=${requestId}`)
        .then(response => {
            console.log('Response status:', response.status);
            if (!response.ok) {
                throw new Error(`HTTP error! status: ${response.status}`);
            }
            return response.json();
        })
        .then(data => {
            console.log('API Response:', data);
            if (data.success && data.request) {
                console.log('Request data:', data.request);
                console.log('Ingredients list:', data.request.ingredients_list);
                displayItemChecklist(data.request);
            } else {
                console.error('API Error:', data.error);
                // Try to load from the main requests list instead
                loadFromMainRequestsList(requestId);
            }
        })
        .catch(error => {
            console.error('Error loading request items:', error);
            // Try to load from the main requests list instead
            loadFromMainRequestsList(requestId);
        });
}

function loadFromMainRequestsList(requestId) {
    console.log('Trying to load from main requests list for ID:', requestId);
    
    // Try to get the data from the main requests list
    fetch('get_stockman_requests.php')
        .then(response => response.json())
        .then(data => {
            if (data.success && data.data) {
                const request = data.data.find(req => req.request_id == requestId);
                if (request) {
                    console.log('Found request in main list:', request);
                    // Parse the ingredients from the display string
                    parseIngredientsFromDisplay(request.ingredients, requestId);
                } else {
                    console.error('Request not found in main list');
                    displayItemChecklist({});
                }
            } else {
                console.error('Failed to load main requests list');
                displayItemChecklist({});
            }
        })
        .catch(error => {
            console.error('Error loading from main requests list:', error);
            displayItemChecklist({});
        });
}

function parseIngredientsFromDisplay(ingredientsDisplay, requestId) {
    console.log('Parsing ingredients from display:', ingredientsDisplay);
    
    // This is a fallback method to parse ingredients from the display string
    // Format: "Ingredient Name (quantity unit), Ingredient Name (quantity unit)"
    const items = [];
    
    if (ingredientsDisplay && ingredientsDisplay !== 'No ingredients specified') {
        const ingredientStrings = ingredientsDisplay.split(', ');
        
        ingredientStrings.forEach((ingredientString, index) => {
            // Parse format: "Ingredient Name (quantity unit)"
            const match = ingredientString.match(/^(.+?)\s*\((\d+)\s+(.+?)\)$/);
            if (match) {
                const [, name, quantity, unit] = match;
                items.push({
                    name: name.trim(),
                    quantity: parseInt(quantity),
                    unit: unit.trim(),
                    category: 'General'
                });
            }
        });
    }
    
    if (items.length > 0) {
        console.log('Parsed items:', items);
        displayItemChecklist({ ingredients_list: items });
    } else {
        console.log('No items parsed, showing sample data');
        displayItemChecklist({});
    }
}

function displayItemChecklist(request) {
    const checklist = document.getElementById('itemChecklist');
    
    // Create items array from the request data
    let items = [];
    
    if (request.ingredients_list && Array.isArray(request.ingredients_list)) {
        // Use the ingredients_list from the new API
        items = request.ingredients_list;
    } else if (request.ingredient_name) {
        // Single ingredient from the old API response
        items = [{
            name: request.ingredient_name,
            quantity: request.quantity || 1,
            unit: request.unit || 'pieces',
            category: request.category_name || 'General',
            current_stock: request.current_stock || 0,
            current_unit: request.current_unit || 'pieces'
        }];
    } else if (request.ingredients) {
        // Multiple ingredients (if stored as JSON or comma-separated)
        try {
            if (typeof request.ingredients === 'string') {
                items = JSON.parse(request.ingredients);
            } else {
                items = request.ingredients;
            }
        } catch (e) {
            // If not JSON, treat as comma-separated string
            items = request.ingredients.split(',').map(item => ({
                name: item.trim(),
                quantity: 1,
                unit: 'pieces'
            }));
        }
    } else {
        // Fallback: create sample data for testing
        items = [
            {
                name: 'Sample Ingredient 1',
                quantity: 5,
                unit: 'pieces',
                category: 'General'
            },
            {
                name: 'Sample Ingredient 2',
                quantity: 2,
                unit: 'gallons',
                category: 'Liquids'
            },
            {
                name: 'Sample Ingredient 3',
                quantity: 10,
                unit: 'kilos',
                category: 'Dry Goods'
            }
        ];
    }
    
    if (items.length === 0) {
        checklist.innerHTML = '<div class="no-items-message"><i class="fas fa-info-circle"></i> No items found in this request</div>';
        return;
    }
    
    checklist.innerHTML = items.map((item, index) => `
        <div class="item-checklist-item" data-item-index="${index}">
            <div class="item-checkbox" onclick="toggleItemReceived(${index})"></div>
            <div class="item-info">
                <div class="item-name">${item.name || 'Unknown Item'}</div>
                <div class="item-details">
                    <span>Requested: ${item.quantity || 1} ${item.unit || 'pieces'}</span>
                    ${item.category ? `<span>Category: ${item.category}</span>` : ''}
                    ${item.current_stock !== undefined ? `<span>Current Stock: ${item.current_stock} ${item.current_unit || item.unit || 'pieces'}</span>` : ''}
                </div>
            </div>
            <div class="item-quantity-group">
                <input type="number" class="quantity-input" 
                       id="quantity_${index}" 
                       min="0" 
                       max="${Math.round(item.quantity || 1)}"
                       step="1"
                       value="${Math.round(item.quantity || 1)}"
                       readonly
                       data-original-quantity="${Math.round(item.quantity || 1)}"
                       onchange="updateItemQuantity(${index})"
                       oninput="this.value = Math.round(this.value); updateReturnQuantityMax(this, ${index})">
                <span class="quantity-unit">${item.unit || 'pieces'}</span>
                <div class="quantity-display" style="font-size: 0.8em; color: #6c757d; margin-top: 2px;">
                    Net: ${Math.round(item.quantity || 1)} ${item.unit || 'pieces'}
                </div>
            </div>
            <div class="return-toggle" onclick="toggleItemReturn(${index})">
                <i class="fas fa-undo"></i>
                <span>Return</span>
            </div>
        </div>
    `).join('');
    
}

function toggleItemReceived(index) {
    const item = document.querySelector(`[data-item-index="${index}"]`);
    const checkbox = item.querySelector('.item-checkbox');
    const quantityInput = item.querySelector('.quantity-input');
    const returnToggle = item.querySelector('.return-toggle');
    
    checkbox.classList.toggle('checked');
    
    if (checkbox.classList.contains('checked')) {
        // Item is received - make quantity input editable
        quantityInput.disabled = false;
        quantityInput.style.opacity = '1';
        quantityInput.removeAttribute('readonly');
        quantityInput.style.backgroundColor = '#fff';
        quantityInput.style.cursor = 'text';
        
        // Set border color based on whether it's also marked for return
        if (returnToggle.classList.contains('active')) {
            quantityInput.style.borderColor = '#dc3545'; // Red for return
        } else {
            quantityInput.style.borderColor = 'var(--primary-color)'; // Normal for received
        }
        
        // Don't automatically remove from returns - allow both received and returned
        // User can manually toggle return if they want to return some of the received items
    } else {
        // Item is not received - make quantity input read-only
        quantityInput.disabled = true;
        quantityInput.style.opacity = '0.5';
        quantityInput.setAttribute('readonly', 'readonly');
        quantityInput.style.backgroundColor = '#f8f9fa';
        quantityInput.style.cursor = 'not-allowed';
        quantityInput.style.borderColor = '#ced4da';
        
        // Don't automatically mark for return - let user decide
        // User can manually toggle return if they want to return the item
    }
    
    // Automatically update delivery status based on selections
    updateDeliveryStatusAutomatically();
}

function updateItemQuantity(index) {
    const item = document.querySelector(`[data-item-index="${index}"]`);
    const quantityInput = item.querySelector('.quantity-input');
    const maxQuantity = parseInt(quantityInput.getAttribute('max'));
    const currentQuantity = Math.round(parseFloat(quantityInput.value) || 0);
    
    if (currentQuantity > maxQuantity) {
        quantityInput.value = maxQuantity;
    } else if (currentQuantity < 0) {
        quantityInput.value = 0;
    } else {
        quantityInput.value = currentQuantity;
    }
}

function toggleItemReturn(index) {
    const item = document.querySelector(`[data-item-index="${index}"]`);
    const returnToggle = item.querySelector('.return-toggle');
    const checkbox = item.querySelector('.item-checkbox');
    const quantityInput = item.querySelector('.quantity-input');
    
    returnToggle.classList.toggle('active');
    
    if (returnToggle.classList.contains('active')) {
        // Mark item for return - make quantity input editable
        quantityInput.removeAttribute('readonly');
        quantityInput.style.backgroundColor = '#fff';
        quantityInput.style.cursor = 'text';
        quantityInput.style.borderColor = '#dc3545'; // Red for return
        addReturnItem(index);
        
        // Don't automatically uncheck received status - allow both received and returned
        // User can have both: received some quantity AND returned some quantity
        
        // Update the received quantity display
        updateReceivedQuantity(index);
    } else {
        // Cancel return - adjust styling based on received status
        if (checkbox.classList.contains('checked')) {
            // Item is still received, so keep it editable
            quantityInput.removeAttribute('readonly');
            quantityInput.style.backgroundColor = '#fff';
            quantityInput.style.cursor = 'text';
            quantityInput.style.borderColor = 'var(--primary-color)'; // Normal for received
        } else {
            // Item is not received, make it read-only
            quantityInput.setAttribute('readonly', 'readonly');
            quantityInput.style.backgroundColor = '#f8f9fa';
            quantityInput.style.cursor = 'not-allowed';
            quantityInput.style.borderColor = '#ced4da';
        }
        
        // Reset to original quantity
        const originalQuantity = quantityInput.getAttribute('data-original-quantity');
        quantityInput.value = originalQuantity;
        removeReturnItem(index);
        
        // Update the received quantity display
        updateReceivedQuantity(index);
    }
    
    // Automatically update delivery status based on selections
    updateDeliveryStatusAutomatically();
}

function addReturnItem(index) {
    const item = document.querySelector(`[data-item-index="${index}"]`);
    const itemName = item.querySelector('.item-name').textContent;
    const quantityInput = item.querySelector('.quantity-input');
    const unit = item.querySelector('.quantity-unit').textContent;
    
    const returnSection = document.getElementById('returnSection');
    const returnItemsList = document.getElementById('returnItemsList');
    
    // Check if return item already exists
    if (document.getElementById(`return-item-${index}`)) {
        return;
    }
    
    const returnItemHTML = `
        <div class="return-item" id="return-item-${index}">
            <div class="return-item-header">
                <div class="return-item-name">${itemName}</div>
            </div>
            <div class="return-reasons">
                <div class="reason-group">
                    <div class="reason-checkbox" onclick="toggleReturnReason(${index}, 'damaged')"></div>
                    <label class="reason-label" onclick="toggleReturnReason(${index}, 'damaged')">
                        <i class="fas fa-exclamation-triangle"></i> Damaged/Broken
                    </label>
                </div>
                <div class="reason-group">
                    <div class="reason-checkbox" onclick="toggleReturnReason(${index}, 'wrong_item')"></div>
                    <label class="reason-label" onclick="toggleReturnReason(${index}, 'wrong_item')">
                        <i class="fas fa-exchange-alt"></i> Wrong Item
                    </label>
                </div>
                <div class="reason-group">
                    <div class="reason-checkbox" onclick="toggleReturnReason(${index}, 'expired')"></div>
                    <label class="reason-label" onclick="toggleReturnReason(${index}, 'expired')">
                        <i class="fas fa-calendar-times"></i> Expired
                    </label>
                </div>
                <div class="reason-group">
                    <div class="reason-checkbox" onclick="toggleReturnReason(${index}, 'quality_issue')"></div>
                    <label class="reason-label" onclick="toggleReturnReason(${index}, 'quality_issue')">
                        <i class="fas fa-star-half-alt"></i> Quality Issue
                    </label>
                </div>
                <div class="reason-group">
                    <div class="reason-checkbox" onclick="toggleReturnReason(${index}, 'overstock')"></div>
                    <label class="reason-label" onclick="toggleReturnReason(${index}, 'overstock')">
                        <i class="fas fa-boxes"></i> Overstock
                    </label>
                </div>
                <div class="reason-group">
                    <div class="reason-checkbox" onclick="toggleReturnReason(${index}, 'supplier_error')"></div>
                    <label class="reason-label" onclick="toggleReturnReason(${index}, 'supplier_error')">
                        <i class="fas fa-truck-loading"></i> Supplier Error
                    </label>
                </div>
                <div class="reason-group">
                    <div class="reason-checkbox" onclick="toggleReturnReason(${index}, 'other')"></div>
                    <label class="reason-label" onclick="toggleReturnReason(${index}, 'other')">
                        <i class="fas fa-ellipsis-h"></i> Other
                    </label>
                </div>
            </div>
            <div class="return-quantity-group">
                <label>Return Quantity:</label>
                <input type="number" class="return-quantity-input" 
                       id="return_quantity_${index}" 
                       min="0"
                       step="1"
                       value="1"
                       onchange="validateReturnQuantity(${index}); updateReceivedQuantity(${index})"
                       oninput="this.value = Math.round(this.value); validateReturnQuantity(${index}); updateReceivedQuantity(${index})">
                <span class="return-quantity-unit">${unit}</span>
            </div>
        </div>
    `;
    
    returnItemsList.insertAdjacentHTML('beforeend', returnItemHTML);
    returnSection.style.display = 'block';
    
}

function removeReturnItem(index) {
    const returnItem = document.getElementById(`return-item-${index}`);
    if (returnItem) {
        returnItem.remove();
        
        // Hide return section if no more return items
        const returnItemsList = document.getElementById('returnItemsList');
        if (returnItemsList.children.length === 0) {
            document.getElementById('returnSection').style.display = 'none';
        }
        
        // Update delivery summary
        updateDeliverySummary();
    }
}

function toggleReturnReason(itemIndex, reason) {
    const checkbox = document.querySelector(`#return-item-${itemIndex} .reason-checkbox[onclick*="${reason}"]`);
    checkbox.classList.toggle('checked');
}

function updateReturnQuantityMax(receivedQuantityInput, itemIndex) {
    const returnQuantityInput = document.querySelector(`#return_quantity_${itemIndex}`);
    if (returnQuantityInput) {
        // Remove any max limit - allow unlimited returns
        returnQuantityInput.removeAttribute('max');
        // Update the received quantity display
        updateReceivedQuantity(itemIndex);
    }
}

function updateReceivedQuantity(itemIndex) {
    // Find the original received quantity input for this item
    const checklistItems = document.querySelectorAll('.item-checklist-item');
    const originalItem = checklistItems[itemIndex];
    
    if (originalItem) {
        const originalQuantityInput = originalItem.querySelector('.quantity-input');
        const returnQuantityInput = document.querySelector(`#return_quantity_${itemIndex}`);
        const quantityUnit = originalItem.querySelector('.quantity-unit').textContent;
        
        if (originalQuantityInput && returnQuantityInput) {
            const originalQuantity = parseInt(originalQuantityInput.getAttribute('data-original-quantity')) || parseInt(originalQuantityInput.value) || 0;
            const returnQuantity = parseInt(returnQuantityInput.value) || 0;
            const calculatedNet = originalQuantity - returnQuantity;
            const netQuantity = Math.max(0, calculatedNet); // Store 0 minimum, but show calculation
            
            // Update the received quantity input to show the net amount (minimum 0)
            originalQuantityInput.value = netQuantity;
            
            // Update the visual display to show the calculation
            const quantityDisplay = originalItem.querySelector('.quantity-display');
            if (quantityDisplay) {
                if (returnQuantity > 0) {
                    if (calculatedNet < 0) {
                        quantityDisplay.textContent = `Net: 0 ${quantityUnit} (${originalQuantity} - ${returnQuantity} = ${calculatedNet}, but minimum is 0)`;
                        quantityDisplay.style.color = '#dc3545'; // Red color to indicate return
                    } else {
                        quantityDisplay.textContent = `Net: ${netQuantity} ${quantityUnit} (${originalQuantity} - ${returnQuantity})`;
                        quantityDisplay.style.color = '#dc3545'; // Red color to indicate return
                    }
                } else {
                    quantityDisplay.textContent = `Net: ${netQuantity} ${quantityUnit}`;
                    quantityDisplay.style.color = '#6c757d'; // Default color
                }
            }
        }
    }
}

function validateReturnQuantity(itemIndex) {
    const returnQuantityInput = document.querySelector(`#return_quantity_${itemIndex}`);
    
    if (returnQuantityInput) {
        const returnQty = Math.round(parseFloat(returnQuantityInput.value) || 0);
        
        // Ensure return quantity is a whole number and non-negative
        returnQuantityInput.value = Math.max(0, returnQty);
    }
}

function collectItemChecklistData() {
    const items = [];
    const checklistItems = document.querySelectorAll('.item-checklist-item');
    
    console.log('Found checklist items:', checklistItems.length);
    
    checklistItems.forEach((item, index) => {
        const checkbox = item.querySelector('.item-checkbox');
        const itemName = item.querySelector('.item-name').textContent;
        const quantityInput = item.querySelector('.quantity-input');
        const unit = item.querySelector('.quantity-unit').textContent;
        
        const isChecked = checkbox.classList.contains('checked');
        const currentQuantity = parseInt(quantityInput.value) || 0;
        const originalQuantity = parseInt(quantityInput.getAttribute('data-original-quantity')) || currentQuantity;
        
        console.log(`Item ${index}: ${itemName}, Checked: ${isChecked}, Current Quantity: ${currentQuantity}, Original Quantity: ${originalQuantity}`);
        
        items.push({
            index: index,
            name: itemName,
            received: isChecked,
            quantity: originalQuantity, // Send original received quantity, not net
            unit: unit,
            max_quantity: parseInt(quantityInput.getAttribute('max')) || 0
        });
    });
    
    console.log('Final items array:', items);
    return items;
}

function collectReturnItemsData() {
    const returnItems = [];
    const returnItemElements = document.querySelectorAll('.return-item');
    
    returnItemElements.forEach((returnItem) => {
        const itemId = returnItem.id.replace('return-item-', '');
        const itemName = returnItem.querySelector('.return-item-name').textContent;
        const returnQuantity = parseInt(returnItem.querySelector('.return-quantity-input').value) || 0;
        
        // Collect selected reasons
        const reasons = [];
        const reasonCheckboxes = returnItem.querySelectorAll('.reason-checkbox.checked');
        reasonCheckboxes.forEach(checkbox => {
            const onclick = checkbox.getAttribute('onclick');
            const reason = onclick.match(/'([^']+)'/)[1];
            reasons.push(reason);
        });
        
        returnItems.push({
            item_index: parseInt(itemId),
            item_name: itemName,
            return_quantity: returnQuantity,
            reasons: reasons
        });
    });
    
    return returnItems;
}




function refreshRequests() {
    loadRequests();
    
    // Show refresh animation
    const refreshBtn = document.querySelector('.btn-outline-primary');
    const originalText = refreshBtn.innerHTML;
    refreshBtn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Refreshing...';
    refreshBtn.disabled = true;
    
    setTimeout(() => {
        refreshBtn.innerHTML = originalText;
        refreshBtn.disabled = false;
    }, 1000);
}

// Automatically update delivery status based on item selections
function updateDeliveryStatusAutomatically() {
    const checklistItems = document.querySelectorAll('.item-checklist-item');
    const statusSelect = document.getElementById('deliveryStatus');
    
    if (!checklistItems.length || !statusSelect) return;
    
    let totalItems = checklistItems.length;
    let receivedItems = 0;
    let returnedItems = 0;
    
    checklistItems.forEach(item => {
        const checkbox = item.querySelector('.item-checkbox');
        const returnToggle = item.querySelector('.return-toggle');
        
        if (checkbox.classList.contains('checked')) {
            receivedItems++;
        }
        
        if (returnToggle.classList.contains('active')) {
            returnedItems++;
        }
    });
    
    // Determine status based on selections
    let newStatus = 'pending';
    
    if (receivedItems === totalItems && returnedItems === 0) {
        // All items received, none returned = Delivered
        newStatus = 'delivered';
    } else if (receivedItems > 0 && receivedItems < totalItems) {
        // Some items received = Partially Delivered
        newStatus = 'partially_delivered';
    } else if (returnedItems === totalItems) {
        // All items returned = Returned
        newStatus = 'returned';
    } else if (returnedItems > 0) {
        // Some items returned = Partially Delivered (with returns)
        newStatus = 'partially_delivered';
    } else if (receivedItems === 0) {
        // No items received = Pending
        newStatus = 'pending';
    }
    
    // Update the status select if it's different
    if (statusSelect.value !== newStatus) {
        statusSelect.value = newStatus;
        console.log('Delivery status automatically updated to:', newStatus);
        
        // Show a subtle notification
        const statusNames = {
            'delivered': 'Delivered',
            'partially_delivered': 'Partially Delivered',
            'returned': 'Returned',
            'pending': 'Pending'
        };
        
        // Optional: Show a brief notification
        console.log(`Status automatically updated to: ${statusNames[newStatus]}`);
    }
}
</script>

<?php include('footer.php'); ?>
