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
}
</style>

<div class="stock-updates-dashboard">
    <div class="container-fluid">
        <h1 class="dashboard-title">
            🔄 Stock Updates Overview
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
// Pagination variables
let allRequests = [];
let currentPage = 1;
const itemsPerPage = 5;

// Initialize page
document.addEventListener('DOMContentLoaded', function() {
    loadStats();
    loadRequests();
    setupDeliveryHandlers();
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
            actionButton = `<button class="btn btn-info btn-sm update-delivery" data-id="${request.request_id}" onclick="updateDeliveryStatus(${request.request_id})">
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
        
        if (status === 'returned' || status === 'cancelled') {
            notesField.setAttribute('required', 'required');
            notesLabel.innerHTML = 'Delivery Notes <span class="text-danger">*</span>';
            notesField.classList.add('border-warning');
        } else {
            notesField.removeAttribute('required');
            notesLabel.innerHTML = 'Delivery Notes';
            notesField.classList.remove('border-warning');
        }
    });
    
    // Update delivery button handler
    document.getElementById('updateDelivery').addEventListener('click', function() {
        const requestId = document.getElementById('deliveryRequestId').value;
        const deliveryStatus = document.getElementById('deliveryStatus').value;
        const deliveryDate = document.getElementById('deliveryDate').value;
        const deliveryNotes = document.getElementById('deliveryNotes').value.trim();
        
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
                delivery_notes: deliveryNotes
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
                loadStats();
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
        notesLabel.innerHTML = 'Delivery Notes';
    });
}

function updateDeliveryStatus(requestId) {
    // Show delivery status update modal
    console.log('Updating delivery status for request ID:', requestId);
    document.getElementById('deliveryRequestId').value = requestId;
    
    // Set current date/time as default and minimum date
    const now = new Date();
    const currentDateTime = now.toISOString().slice(0, 16); // Format: YYYY-MM-DDTHH:MM
    document.getElementById('deliveryDate').value = currentDateTime;
    document.getElementById('deliveryDate').setAttribute('min', currentDateTime);
    
    const modal = new bootstrap.Modal(document.getElementById('deliveryModal'));
    modal.show();
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
