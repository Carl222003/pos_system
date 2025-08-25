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

.stock-updates-dashboard {
    background: #f8f9fa;
    min-height: 100vh;
    padding: 20px 0;
}

.dashboard-title {
    color: var(--primary-color);
    font-size: 2.2rem;
    font-weight: 700;
    margin-bottom: 2rem;
    display: flex;
    align-items: center;
    gap: 0.7rem;
}

.dashboard-title::after {
    content: '';
    width: 4px;
    height: 24px;
    background: var(--primary-color);
    border-radius: 2px;
}

/* Branch Information Styles */
.branch-info {
    background: linear-gradient(135deg, #ffffff 0%, #f8f9fa 100%);
    border-radius: 12px;
    padding: 1.5rem;
    margin-bottom: 2rem;
    box-shadow: 0 4px 6px rgba(0,0,0,0.05);
    border-left: 4px solid var(--primary-color);
    display: flex;
    justify-content: space-between;
    align-items: center;
    flex-wrap: wrap;
    gap: 1rem;
}

.branch-badge {
    display: flex;
    align-items: center;
    gap: 0.5rem;
    color: var(--primary-color);
    font-size: 1rem;
}

.branch-badge i {
    font-size: 1.2rem;
}

.branch-id {
    color: #6c757d;
    font-size: 0.9rem;
    font-weight: normal;
}

.ingredients-count {
    display: flex;
    align-items: center;
    gap: 0.5rem;
    color: var(--info-color);
    font-size: 1rem;
}

.ingredients-count i {
    font-size: 1.2rem;
}

.branch-warning {
    background: linear-gradient(135deg, #fff3cd 0%, #ffeaa7 100%);
    border-radius: 12px;
    padding: 1.5rem;
    margin-bottom: 2rem;
    box-shadow: 0 4px 6px rgba(0,0,0,0.05);
    border-left: 4px solid var(--warning-color);
    color: #856404;
    display: flex;
    align-items: center;
    gap: 0.5rem;
    font-size: 1rem;
}

.branch-warning i {
    font-size: 1.2rem;
}

.stats-cards {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
    gap: 1.5rem;
    margin-bottom: 2rem;
}

.stat-card {
    background: white;
    border-radius: 12px;
    padding: 1.5rem;
    box-shadow: 0 4px 6px rgba(0,0,0,0.05);
    border-left: 4px solid var(--primary-color);
    transition: transform 0.2s ease;
}

.stat-card:hover {
    transform: translateY(-2px);
}

.stat-number {
    font-size: 2.5rem;
    font-weight: 700;
    color: var(--primary-color);
    margin: 0;
}

.stat-label {
    color: #6c757d;
    font-size: 0.9rem;
    margin: 0;
    text-transform: uppercase;
    letter-spacing: 0.5px;
}

.request-form-section {
    background: white;
    border-radius: 12px;
    padding: 2rem;
    margin-bottom: 2rem;
    box-shadow: 0 4px 6px rgba(0,0,0,0.05);
    border: 1px solid #f0f0f0;
}

.form-header {
    color: var(--primary-color);
    font-size: 1.5rem;
    font-weight: 600;
    margin-bottom: 1.5rem;
    display: flex;
    align-items: center;
    gap: 0.5rem;
}

.form-control, .form-select {
    border: 1px solid #dee2e6;
    border-radius: 8px;
    transition: border-color 0.2s ease, box-shadow 0.2s ease;
    padding: 0.75rem 1rem;
    font-size: 0.9rem;
}

.form-control:focus, .form-select:focus {
    border-color: var(--primary-color);
    box-shadow: 0 0 0 0.2rem rgba(139, 69, 67, 0.25);
    background-color: #fff;
}

.form-label {
    color: var(--primary-color);
    font-weight: 600;
    margin-bottom: 0.5rem;
    font-size: 0.9rem;
    text-transform: uppercase;
    letter-spacing: 0.5px;
}

.btn-primary {
    background: var(--primary-color);
    border: none;
    color: white;
    padding: 0.75rem 1.5rem;
    border-radius: 8px;
    font-weight: 600;
    transition: all 0.2s ease;
}

.btn-primary:hover {
    background: var(--primary-dark);
    transform: translateY(-1px);
    box-shadow: 0 4px 8px rgba(139, 69, 67, 0.3);
}

.requests-table {
    background: white;
    border-radius: 12px;
    overflow: hidden;
    box-shadow: 0 4px 6px rgba(0,0,0,0.05);
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

.info-section {
    background: white;
    border-radius: 12px;
    padding: 1.5rem;
    margin-bottom: 2rem;
    box-shadow: 0 2px 4px rgba(0,0,0,0.05);
    border-left: 4px solid var(--info-color);
}

.info-header {
    color: var(--info-color);
    font-size: 1.1rem;
    margin-bottom: 1rem;
    padding-bottom: 0.5rem;
    border-bottom: 1px solid #eee;
}

.info-content {
    color: #6c757d;
}

.info-item {
    display: flex;
    align-items: center;
    gap: 0.75rem;
    padding: 0.5rem 0;
}



.info-icon {
    width: 32px;
    height: 32px;
    border-radius: 50%;
    display: flex;
    align-items: center;
    justify-content: center;
    font-weight: bold;
    font-size: 1.1rem;
}

.info-icon.pending {
    background: var(--warning-color);
    color: #212529;
}

.info-icon.approved {
    background: var(--success-color);
    color: white;
}

.info-icon.rejected {
    background: var(--danger-color);
    color: white;
}

.info-icon.completed {
    background: var(--info-color);
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

/* Enhanced form styling */
.form-group {
    margin-bottom: 1.5rem;
}

.form-row {
    display: flex;
    gap: 1rem;
    margin-bottom: 1rem;
}

.form-col {
    flex: 1;
}

.quantity-input-group {
    display: flex;
    align-items: center;
    gap: 0.5rem;
}

.quantity-input-group .form-control {
    flex: 1;
}

.quantity-input-group .form-select {
    width: auto;
    min-width: 120px;
}

/* Animation for new requests */
@keyframes slideInUp {
    from {
        opacity: 0;
        transform: translateY(30px);
    }
    to {
        opacity: 1;
        transform: translateY(0);
    }
}

.new-request {
    animation: slideInUp 0.5s ease-out;
}

/* Responsive design */
@media (max-width: 768px) {
    .form-row {
        flex-direction: column;
    }
    
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
}
</style>

<div class="stock-updates-dashboard">
    <div class="container-fluid">
        <h1 class="dashboard-title">
            🔄 Update Stock
        </h1>
        
        <!-- Branch Information -->
        <?php if ($branch_id): ?>
            <?php
            $branch_stmt = $pdo->prepare("SELECT branch_name FROM pos_branch WHERE branch_id = ?");
            $branch_stmt->execute([$branch_id]);
            $branch_name = $branch_stmt->fetchColumn();
            ?>
            <div class="branch-info">
                <div class="branch-badge">
                    <i class="fas fa-building me-2"></i>
                    <strong>Branch:</strong> <?php echo htmlspecialchars($branch_name ?? 'Unknown Branch'); ?>
                    <span class="branch-id">(ID: <?php echo $branch_id; ?>)</span>
                </div>
                <div class="ingredients-count">
                    <i class="fas fa-boxes me-2"></i>
                    <strong>Available Ingredients:</strong> <?php echo count($ingredients); ?> items
                </div>
            </div>
        <?php else: ?>
            <div class="branch-warning">
                <i class="fas fa-exclamation-triangle me-2"></i>
                <strong>Warning:</strong> No branch assigned. Please contact administrator.
            </div>
        <?php endif; ?>
        
        <!-- Statistics Cards -->
        <div class="stats-cards">
            <div class="stat-card">
                <h2 class="stat-number" id="totalRequests">-</h2>
                <p class="stat-label">Total Requests</p>
            </div>
            <div class="stat-card">
                <h2 class="stat-number" id="pendingRequests">-</h2>
                <p class="stat-label">Pending Approval</p>
            </div>
            <div class="stat-card">
                <h2 class="stat-number" id="approvedRequests">-</h2>
                <p class="stat-label">Approved Requests</p>
            </div>
            <div class="stat-card">
                <h2 class="stat-number" id="completedRequests">-</h2>
                <p class="stat-label">Completed Updates</p>
            </div>
        </div>
        
        <!-- Information Section -->
        <div class="info-section">
            <div class="info-header">
                <i class="fas fa-info-circle me-2"></i>
                <strong>Stock Update Guide</strong>
            </div>
            <div class="info-content">
                <div class="row">
                    <div class="col-md-3">
                        <div class="info-item">
                            <span class="info-icon pending">⏳</span>
                            <div>
                                <strong>Pending</strong>
                                <small>Awaiting admin approval</small>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="info-item">
                            <span class="info-icon approved">✓</span>
                            <div>
                                <strong>Approved</strong>
                                <small>Admin has approved the request</small>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="info-item">
                            <span class="info-icon completed">✅</span>
                            <div>
                                <strong>Completed</strong>
                                <small>Stock has been updated</small>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="info-item">
                            <span class="info-icon rejected">✗</span>
                            <div>
                                <strong>Rejected</strong>
                                <small>Request was denied by admin</small>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        

        
        <!-- Request Form Section -->
        <div class="request-form-section">
            <div class="form-header">
                <i class="fas fa-plus-circle"></i>
                Stock Update Request
            </div>
            
            <form id="stockUpdateForm">
                <div class="row">
                    <div class="col-md-6">
                        <div class="form-group">
                            <label class="form-label">Ingredient</label>
                            <small class="form-text text-muted d-block mb-2">
                                <i class="fas fa-info-circle me-1"></i>
                                Only ingredients from your assigned branch are shown
                            </small>
                            <select class="form-select" id="ingredient_id" name="ingredient_id" required>
                                <option value="">Select an ingredient...</option>
                                <?php if (empty($ingredients)): ?>
                                    <option value="" disabled>No ingredients available for this branch</option>
                                <?php else: ?>
                                    <?php foreach ($ingredients as $ingredient): ?>
                                        <option value="<?= $ingredient['ingredient_id'] ?>" 
                                                data-current-stock="<?= $ingredient['ingredient_quantity'] ?>"
                                                data-unit="<?= $ingredient['ingredient_unit'] ?>"
                                                data-category="<?= htmlspecialchars($ingredient['category_name'] ?? 'Uncategorized') ?>"
                                                <?= ($pre_selected_ingredient == $ingredient['ingredient_id']) ? 'selected' : '' ?>>
                                            <?= htmlspecialchars($ingredient['ingredient_name']) ?> 
                                            (<?= htmlspecialchars($ingredient['category_name'] ?? 'Uncategorized') ?> - 
                                            Current: <?= $ingredient['ingredient_quantity'] ?> <?= $ingredient['ingredient_unit'] ?>)
                                        </option>
                                    <?php endforeach; ?>
                                <?php endif; ?>
                            </select>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="form-group">
                            <label class="form-label">Update Type</label>
                            <select class="form-select" id="update_type" name="update_type" required>
                                <option value="">Select update type...</option>
                                <option value="add">Add Stock</option>
                                <option value="adjust">Adjust Stock</option>
                                <option value="correct">Correct Stock Count</option>
                            </select>
                        </div>
                    </div>
                </div>
                
                <div class="row">
                    <div class="col-md-4">
                        <div class="form-group">
                            <label class="form-label">Quantity</label>
                            <div class="quantity-input-group">
                                <input type="number" class="form-control" id="quantity" name="quantity" 
                                       step="0.01" min="0" required placeholder="Enter quantity">
                                <select class="form-select" id="unit" name="unit" required>
                                    <option value="">Unit</option>
                                    <option value="pieces">Pieces</option>
                                    <option value="kg">Kilograms</option>
                                    <option value="grams">Grams</option>
                                    <option value="liters">Liters</option>
                                    <option value="ml">Milliliters</option>
                                    <option value="gallons">Gallons</option>
                                    <option value="tab">Tablets</option>
                                </select>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="form-group">
                            <label class="form-label">Urgency Level</label>
                            <select class="form-select" id="urgency_level" name="urgency_level" required>
                                <option value="">Select urgency...</option>
                                <option value="low">Low - Normal restocking</option>
                                <option value="medium">Medium - Running low</option>
                                <option value="high">High - Critical level</option>
                                <option value="critical">Critical - Out of stock</option>
                            </select>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="form-group">
                            <label class="form-label">Priority</label>
                            <select class="form-select" id="priority" name="priority" required>
                                <option value="">Select priority...</option>
                                <option value="normal">Normal</option>
                                <option value="high">High</option>
                                <option value="urgent">Urgent</option>
                            </select>
                        </div>
                    </div>
                </div>
                
                <div class="form-group">
                    <label class="form-label">Reason for Update</label>
                    <textarea class="form-control" id="reason" name="reason" rows="3" 
                              placeholder="Please provide a detailed reason for this stock update request..." required></textarea>
                </div>
                
                <div class="form-group">
                    <label class="form-label">Additional Notes</label>
                    <textarea class="form-control" id="notes" name="notes" rows="2" 
                              placeholder="Any additional information or special instructions..."></textarea>
                </div>
                
                <div class="text-end">
                    <button type="button" class="btn btn-secondary me-2" onclick="resetForm()">
                        <i class="fas fa-undo"></i> Reset
                    </button>
                    <button type="submit" class="btn btn-primary">
                        <i class="fas fa-paper-plane"></i> Submit Request
                    </button>
                </div>
            </form>
        </div>
        
        <!-- Existing Requests Table -->
        <div class="requests-table">
            <div class="d-flex justify-content-between align-items-center p-3 border-bottom">
                <h5 class="mb-0">
                    <i class="fas fa-history me-2"></i>
                    My Stock Update Requests
                </h5>
                <button class="btn btn-outline-primary btn-sm" onclick="refreshRequests()">
                    <i class="fas fa-sync-alt"></i> Refresh
                </button>
            </div>
            
            <table class="table table-hover">
                <thead>
                    <tr>
                        <th>Ingredient</th>
                        <th>Update Type</th>
                        <th>Quantity</th>
                        <th>Urgency</th>
                        <th>Status</th>
                        <th>Request Date</th>
                        <th>Admin Response</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody id="requestsTableBody">
                    <tr>
                        <td colspan="8" class="loading">
                            <div class="spinner-border text-primary" role="status">
                                <span class="visually-hidden">Loading...</span>
                            </div>
                            <p>Loading your requests...</p>
                        </td>
                    </tr>
                </tbody>
            </table>
        </div>
    </div>
</div>

<!-- Request Details Modal -->
<div class="modal fade" id="requestDetailsModal" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Request Details</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body" id="requestDetailsBody">
                <!-- Content loaded dynamically -->
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
            </div>
        </div>
    </div>
</div>

<script>
let allRequests = [];

// Initialize page
document.addEventListener('DOMContentLoaded', function() {
    loadStats();
    loadRequests();
    setupFormHandlers();
});

function loadStats() {
    fetch('get_stock_update_stats.php')
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                document.getElementById('totalRequests').textContent = data.stats.total_requests;
                document.getElementById('pendingRequests').textContent = data.stats.pending_requests;
                document.getElementById('approvedRequests').textContent = data.stats.approved_requests;
                document.getElementById('completedRequests').textContent = data.stats.completed_requests;
            }
        })
        .catch(error => console.error('Error loading stats:', error));
}

function loadRequests() {
    fetch('get_stock_update_requests.php')
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                allRequests = data.requests;
                displayRequests(data.requests);
            } else {
                document.getElementById('requestsTableBody').innerHTML = 
                    `<tr><td colspan="8" class="no-data">❌ Error: ${data.error}</td></tr>`;
            }
        })
        .catch(error => {
            console.error('Error loading requests:', error);
            document.getElementById('requestsTableBody').innerHTML = 
                `<tr><td colspan="8" class="no-data">❌ Error loading requests</td></tr>`;
        });
}

function displayRequests(requests) {
    const tbody = document.getElementById('requestsTableBody');
    
    if (requests.length === 0) {
        tbody.innerHTML = '<tr><td colspan="8" class="no-data">📝 No requests found</td></tr>';
        return;
    }
    
    tbody.innerHTML = requests.map(request => {
        const urgencyClass = request.urgency_level;
        const statusClass = request.status;
        
        return `
            <tr class="${request.is_new ? 'new-request' : ''}">
                <td>
                    <strong>${request.ingredient_name}</strong>
                    <br>
                    <small class="text-muted">ID: ${request.ingredient_id}</small>
                </td>
                <td>
                    <span class="badge bg-info">${request.update_type.toUpperCase()}</span>
                </td>
                <td>
                    <strong>${request.quantity} ${request.unit}</strong>
                </td>
                <td>
                    <span class="urgency-badge ${urgencyClass}">${urgencyClass.toUpperCase()}</span>
                </td>
                <td>
                    <span class="status-badge ${statusClass}">${statusClass.toUpperCase()}</span>
                </td>
                <td>
                    <small>${new Date(request.request_date).toLocaleDateString()}</small>
                    <br>
                    <small class="text-muted">${new Date(request.request_date).toLocaleTimeString()}</small>
                </td>
                <td>
                    ${request.admin_response ? 
                        `<small>${request.admin_response}</small>` : 
                        '<small class="text-muted">No response yet</small>'
                    }
                </td>
                <td>
                    <button class="btn btn-sm btn-outline-info" onclick="viewRequestDetails(${request.request_id})">
                        <i class="fas fa-eye"></i> View
                    </button>
                    ${request.status === 'pending' ? 
                        `<button class="btn btn-sm btn-outline-warning ms-1" onclick="cancelRequest(${request.request_id})">
                            <i class="fas fa-times"></i> Cancel
                        </button>` : ''
                    }
                </td>
            </tr>
        `;
    }).join('');
}

function setupFormHandlers() {
    // Auto-fill unit when ingredient is selected
    document.getElementById('ingredient_id').addEventListener('change', function() {
        const selectedOption = this.options[this.selectedIndex];
        const unit = selectedOption.getAttribute('data-unit');
        if (unit) {
            document.getElementById('unit').value = unit;
        }
    });
    
    // Form submission
    document.getElementById('stockUpdateForm').addEventListener('submit', function(e) {
        e.preventDefault();
        submitRequest();
    });
}

function submitRequest() {
    const formData = new FormData(document.getElementById('stockUpdateForm'));
    
    fetch('submit_stock_update_request.php', {
        method: 'POST',
        body: formData
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            Swal.fire({
                icon: 'success',
                title: 'Request Submitted!',
                text: 'Your stock update request has been submitted successfully and is pending admin approval.',
                confirmButtonColor: '#8B4543'
            });
            resetForm();
            loadStats();
            loadRequests();
        } else {
            Swal.fire({
                icon: 'error',
                title: 'Submission Failed',
                text: data.error || 'An error occurred while submitting your request.',
                confirmButtonColor: '#8B4543'
            });
        }
    })
    .catch(error => {
        console.error('Error submitting request:', error);
        Swal.fire({
            icon: 'error',
            title: 'Submission Failed',
            text: 'An error occurred while submitting your request.',
            confirmButtonColor: '#8B4543'
        });
    });
}

function resetForm() {
    document.getElementById('stockUpdateForm').reset();
}

function viewRequestDetails(requestId) {
    fetch(`get_stock_update_request_details.php?request_id=${requestId}`)
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                const request = data.request;
                const modalBody = document.getElementById('requestDetailsBody');
                
                modalBody.innerHTML = `
                    <div class="row">
                        <div class="col-md-6">
                            <h6 class="text-primary">Request Information</h6>
                            <table class="table table-borderless">
                                <tr>
                                    <td><strong>Ingredient:</strong></td>
                                    <td>${request.ingredient_name}</td>
                                </tr>
                                <tr>
                                    <td><strong>Update Type:</strong></td>
                                    <td><span class="badge bg-info">${request.update_type.toUpperCase()}</span></td>
                                </tr>
                                <tr>
                                    <td><strong>Quantity:</strong></td>
                                    <td><strong>${request.quantity} ${request.unit}</strong></td>
                                </tr>
                                <tr>
                                    <td><strong>Urgency:</strong></td>
                                    <td><span class="urgency-badge ${request.urgency_level}">${request.urgency_level.toUpperCase()}</span></td>
                                </tr>
                                <tr>
                                    <td><strong>Priority:</strong></td>
                                    <td><span class="badge bg-secondary">${request.priority.toUpperCase()}</span></td>
                                </tr>
                                <tr>
                                    <td><strong>Status:</strong></td>
                                    <td><span class="status-badge ${request.status}">${request.status.toUpperCase()}</span></td>
                                </tr>
                            </table>
                        </div>
                        <div class="col-md-6">
                            <h6 class="text-primary">Request Details</h6>
                            <table class="table table-borderless">
                                <tr>
                                    <td><strong>Request Date:</strong></td>
                                    <td>${new Date(request.request_date).toLocaleString()}</td>
                                </tr>
                                <tr>
                                    <td><strong>Reason:</strong></td>
                                    <td>${request.reason}</td>
                                </tr>
                                <tr>
                                    <td><strong>Notes:</strong></td>
                                    <td>${request.notes || 'No additional notes'}</td>
                                </tr>
                                ${request.admin_response ? `
                                <tr>
                                    <td><strong>Admin Response:</strong></td>
                                    <td>${request.admin_response}</td>
                                </tr>
                                ` : ''}
                                ${request.response_date ? `
                                <tr>
                                    <td><strong>Response Date:</strong></td>
                                    <td>${new Date(request.response_date).toLocaleString()}</td>
                                </tr>
                                ` : ''}
                            </table>
                        </div>
                    </div>
                `;
                
                new bootstrap.Modal(document.getElementById('requestDetailsModal')).show();
            }
        })
        .catch(error => console.error('Error loading request details:', error));
}

function cancelRequest(requestId) {
    Swal.fire({
        title: 'Cancel Request?',
        text: 'Are you sure you want to cancel this request? This action cannot be undone.',
        icon: 'warning',
        showCancelButton: true,
        confirmButtonColor: '#dc3545',
        cancelButtonColor: '#6c757d',
        confirmButtonText: 'Yes, cancel it!'
    }).then((result) => {
        if (result.isConfirmed) {
            fetch('cancel_stock_update_request.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                },
                body: JSON.stringify({ request_id: requestId })
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    Swal.fire({
                        icon: 'success',
                        title: 'Request Cancelled!',
                        text: 'Your request has been cancelled successfully.',
                        confirmButtonColor: '#8B4543'
                    });
                    loadStats();
                    loadRequests();
                } else {
                    Swal.fire({
                        icon: 'error',
                        title: 'Cancellation Failed',
                        text: data.error || 'An error occurred while cancelling the request.',
                        confirmButtonColor: '#8B4543'
                    });
                }
            })
            .catch(error => {
                console.error('Error cancelling request:', error);
                Swal.fire({
                    icon: 'error',
                    title: 'Cancellation Failed',
                    text: 'An error occurred while cancelling the request.',
                    confirmButtonColor: '#8B4543'
                });
            });
        }
    });
}

function refreshRequests() {
    loadStats();
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


</script>



<?php include('footer.php'); ?>
