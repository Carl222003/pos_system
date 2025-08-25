<?php
require_once 'db_connect.php';
require_once 'auth_function.php';

checkAdminLogin();
include('header.php');
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

.admin-dashboard {
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

.filters-section {
    background: white;
    border-radius: 12px;
    padding: 1.5rem;
    margin-bottom: 2rem;
    box-shadow: 0 2px 4px rgba(0,0,0,0.05);
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

.priority-badge {
    padding: 0.25rem 0.5rem;
    border-radius: 0.25rem;
    font-size: 0.7rem;
    font-weight: 600;
    text-transform: uppercase;
}

.priority-badge.normal {
    background: #e9ecef;
    color: #495057;
}

.priority-badge.high {
    background: #fff3cd;
    color: #856404;
}

.priority-badge.urgent {
    background: #f8d7da;
    color: #721c24;
}

.btn-action {
    padding: 0.375rem 0.75rem;
    border-radius: 6px;
    font-size: 0.875rem;
    transition: all 0.2s ease;
    border: none;
    margin: 0.1rem;
}

.btn-approve {
    background: var(--success-color);
    color: white;
}

.btn-approve:hover {
    background: #218838;
    color: white;
}

.btn-reject {
    background: var(--danger-color);
    color: white;
}

.btn-reject:hover {
    background: #c82333;
    color: white;
}

.btn-complete {
    background: var(--info-color);
    color: white;
}

.btn-complete:hover {
    background: #138496;
    color: white;
}

.btn-view {
    background: var(--primary-color);
    color: white;
}

.btn-view:hover {
    background: var(--primary-dark);
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

/* Filter Button Styles */
.btn-filter {
    background: white;
    border: 2px solid var(--primary-color);
    color: var(--primary-color);
    padding: 0.5rem 1rem;
    border-radius: 25px;
    font-size: 0.875rem;
    font-weight: 500;
    transition: all 0.2s ease;
    display: inline-flex;
    align-items: center;
    gap: 0.5rem;
    box-shadow: 0 2px 4px rgba(139, 69, 67, 0.1);
}

.btn-filter:hover {
    background: var(--primary-color);
    color: white;
    transform: translateY(-1px);
    box-shadow: 0 4px 8px rgba(139, 69, 67, 0.2);
}

.btn-filter i {
    font-size: 0.9rem;
}

/* Enhanced form styling */
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

/* Responsive design */
@media (max-width: 768px) {
    .stats-cards {
        grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
    }
    
    .dashboard-title {
        font-size: 1.8rem;
    }
}
</style>

<div class="admin-dashboard">
    <div class="container-fluid">
        <h1 class="dashboard-title">
            📋 Stock Update Requests Management
        </h1>
        
        <!-- Statistics Cards -->
        <div class="stats-cards" id="statsCards">
            <div class="stat-card">
                <h2 class="stat-number" id="totalRequests">-</h2>
                <p class="stat-label">Total Requests</p>
            </div>
            <div class="stat-card">
                <h2 class="stat-number" id="pendingRequests">-</h2>
                <p class="stat-label">Pending Approval</p>
            </div>
            <div class="stat-card">
                <h2 class="stat-number" id="urgentRequests">-</h2>
                <p class="stat-label">Urgent Requests</p>
            </div>
            <div class="stat-card">
                <h2 class="stat-number" id="approvedRequests">-</h2>
                <p class="stat-label">Approved Today</p>
            </div>
        </div>
        
        <!-- Filters Section -->
        <div class="filters-section">
            <div class="row">
                <div class="col-md-3">
                    <label class="form-label">Search Stockman</label>
                    <input type="text" class="form-control" id="searchStockman" placeholder="Search by stockman name...">
                </div>
                <div class="col-md-3">
                    <label class="form-label">Filter by Status</label>
                    <select class="form-select" id="filterStatus">
                        <option value="">All Status</option>
                        <option value="pending">Pending</option>
                        <option value="approved">Approved</option>
                        <option value="rejected">Rejected</option>
                        <option value="completed">Completed</option>
                    </select>
                </div>
                <div class="col-md-3">
                    <label class="form-label">Filter by Urgency</label>
                    <select class="form-select" id="filterUrgency">
                        <option value="">All Urgency</option>
                        <option value="low">Low</option>
                        <option value="medium">Medium</option>
                        <option value="high">High</option>
                        <option value="critical">Critical</option>
                    </select>
                </div>
                <div class="col-md-3">
                    <label class="form-label">Filter by Branch</label>
                    <select class="form-select" id="filterBranch">
                        <option value="">All Branches</option>
                    </select>
                </div>
            </div>
            <div class="row mt-3">
                <div class="col-12 text-center">
                    <button class="btn btn-filter" onclick="applyFilters()">
                        <i class="fas fa-filter"></i>
                        Apply Filters
                    </button>
                    <button class="btn btn-outline-secondary ms-2" onclick="clearFilters()">
                        <i class="fas fa-times"></i>
                        Clear Filters
                    </button>
                    <button class="btn btn-outline-primary ms-2" onclick="refreshData()">
                        <i class="fas fa-sync-alt"></i>
                        Refresh
                    </button>
                </div>
            </div>
        </div>
        
        <!-- Requests Table -->
        <div class="requests-table">
            <div class="d-flex justify-content-between align-items-center p-3 border-bottom">
                <h5 class="mb-0">
                    <i class="fas fa-list me-2"></i>
                    Stock Update Requests
                </h5>
                <div>
                    <span class="badge bg-primary" id="totalCount">0</span>
                    <span class="badge bg-warning ms-1" id="pendingCount">0</span>
                    <span class="badge bg-danger ms-1" id="urgentCount">0</span>
                </div>
            </div>
            
            <table class="table table-hover">
                <thead>
                    <tr>
                        <th>Request ID</th>
                        <th>Stockman</th>
                        <th>Ingredient</th>
                        <th>Update Type</th>
                        <th>Quantity</th>
                        <th>Urgency</th>
                        <th>Priority</th>
                        <th>Status</th>
                        <th>Request Date</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody id="requestsTableBody">
                    <tr>
                        <td colspan="10" class="loading">
                            <div class="spinner-border text-primary" role="status">
                                <span class="visually-hidden">Loading...</span>
                            </div>
                            <p>Loading requests...</p>
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

<!-- Response Modal -->
<div class="modal fade" id="responseModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="responseModalTitle">Respond to Request</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <form id="responseForm">
                    <input type="hidden" id="responseRequestId" name="request_id">
                    <input type="hidden" id="responseAction" name="action">
                    
                    <div class="form-group">
                        <label class="form-label">Response Message</label>
                        <textarea class="form-control" id="responseMessage" name="response_message" rows="4" 
                                  placeholder="Enter your response message..." required></textarea>
                    </div>
                    
                    <div class="form-group" id="stockUpdateSection" style="display: none;">
                        <label class="form-label">Stock Update</label>
                        <div class="row">
                            <div class="col-md-6">
                                <input type="number" class="form-control" id="stockQuantity" name="stock_quantity" 
                                       step="0.01" min="0" placeholder="Quantity">
                            </div>
                            <div class="col-md-6">
                                <select class="form-select" id="stockUnit" name="stock_unit">
                                    <option value="">Select unit</option>
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
                </form>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                <button type="button" class="btn btn-primary" onclick="submitResponse()">Submit Response</button>
            </div>
        </div>
    </div>
</div>

<script>
let allRequests = [];
let currentRequestId = null;

// Initialize page
document.addEventListener('DOMContentLoaded', function() {
    loadStats();
    loadBranches();
    loadRequests();
    
    // Setup filters
    document.getElementById('searchStockman').addEventListener('input', filterRequests);
    document.getElementById('filterStatus').addEventListener('change', filterRequests);
    document.getElementById('filterUrgency').addEventListener('change', filterRequests);
    document.getElementById('filterBranch').addEventListener('change', filterRequests);
});

function loadStats() {
    fetch('get_admin_stock_update_stats.php')
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                document.getElementById('totalRequests').textContent = data.stats.total_requests;
                document.getElementById('pendingRequests').textContent = data.stats.pending_requests;
                document.getElementById('urgentRequests').textContent = data.stats.urgent_requests;
                document.getElementById('approvedRequests').textContent = data.stats.approved_today;
            }
        })
        .catch(error => console.error('Error loading stats:', error));
}

function loadBranches() {
    fetch('get_branches.php')
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                const filterBranch = document.getElementById('filterBranch');
                filterBranch.innerHTML = '<option value="">All Branches</option>';
                
                data.branches.forEach(branch => {
                    filterBranch.innerHTML += `<option value="${branch.branch_id}">${branch.branch_name}</option>`;
                });
            }
        })
        .catch(error => console.error('Error loading branches:', error));
}

function loadRequests() {
    fetch('get_all_stock_update_requests.php')
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                allRequests = data.requests;
                displayRequests(data.requests);
                updateCounts(data.requests);
            } else {
                document.getElementById('requestsTableBody').innerHTML = 
                    `<tr><td colspan="10" class="no-data">❌ Error: ${data.error}</td></tr>`;
            }
        })
        .catch(error => {
            console.error('Error loading requests:', error);
            document.getElementById('requestsTableBody').innerHTML = 
                `<tr><td colspan="10" class="no-data">❌ Error loading requests</td></tr>`;
        });
}

function displayRequests(requests) {
    const tbody = document.getElementById('requestsTableBody');
    
    if (requests.length === 0) {
        tbody.innerHTML = '<tr><td colspan="10" class="no-data">📝 No requests found</td></tr>';
        return;
    }
    
    tbody.innerHTML = requests.map(request => {
        const urgencyClass = request.urgency_level;
        const statusClass = request.status;
        const priorityClass = request.priority;
        
        return `
            <tr class="${request.is_urgent ? 'table-warning' : ''}">
                <td>
                    <strong>#${request.request_id}</strong>
                </td>
                <td>
                    <strong>${request.stockman_name}</strong>
                    <br>
                    <small class="text-muted">${request.branch_name}</small>
                </td>
                <td>
                    <strong>${request.ingredient_name}</strong>
                    <br>
                    <small class="text-muted">Current: ${request.current_stock} ${request.current_unit}</small>
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
                    <span class="priority-badge ${priorityClass}">${priorityClass.toUpperCase()}</span>
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
                    <button class="btn btn-action btn-view" onclick="viewRequestDetails(${request.request_id})">
                        <i class="fas fa-eye"></i>
                    </button>
                    ${request.status === 'pending' ? `
                        <button class="btn btn-action btn-approve" onclick="openResponseModal(${request.request_id}, 'approve')">
                            <i class="fas fa-check"></i>
                        </button>
                        <button class="btn btn-action btn-reject" onclick="openResponseModal(${request.request_id}, 'reject')">
                            <i class="fas fa-times"></i>
                        </button>
                    ` : ''}
                    ${request.status === 'approved' ? `
                        <button class="btn btn-action btn-complete" onclick="openResponseModal(${request.request_id}, 'complete')">
                            <i class="fas fa-check-double"></i>
                        </button>
                    ` : ''}
                </td>
            </tr>
        `;
    }).join('');
}

function updateCounts(requests) {
    const totalCount = requests.length;
    const pendingCount = requests.filter(r => r.status === 'pending').length;
    const urgentCount = requests.filter(r => r.urgency_level === 'high' || r.urgency_level === 'critical').length;
    
    document.getElementById('totalCount').textContent = totalCount;
    document.getElementById('pendingCount').textContent = pendingCount;
    document.getElementById('urgentCount').textContent = urgentCount;
}

function filterRequests() {
    const search = document.getElementById('searchStockman').value.toLowerCase();
    const statusFilter = document.getElementById('filterStatus').value;
    const urgencyFilter = document.getElementById('filterUrgency').value;
    const branchFilter = document.getElementById('filterBranch').value;
    
    let filtered = allRequests.filter(request => {
        const matchesSearch = request.stockman_name.toLowerCase().includes(search);
        const matchesStatus = !statusFilter || request.status === statusFilter;
        const matchesUrgency = !urgencyFilter || request.urgency_level === urgencyFilter;
        const matchesBranch = !branchFilter || request.branch_id == branchFilter;
        
        return matchesSearch && matchesStatus && matchesUrgency && matchesBranch;
    });
    
    displayRequests(filtered);
    updateCounts(filtered);
}

function viewRequestDetails(requestId) {
    fetch(`get_admin_stock_update_request_details.php?request_id=${requestId}`)
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
                                    <td><strong>Request ID:</strong></td>
                                    <td>#${request.request_id}</td>
                                </tr>
                                <tr>
                                    <td><strong>Stockman:</strong></td>
                                    <td>${request.stockman_name} (${request.branch_name})</td>
                                </tr>
                                <tr>
                                    <td><strong>Ingredient:</strong></td>
                                    <td>${request.ingredient_name}</td>
                                </tr>
                                <tr>
                                    <td><strong>Current Stock:</strong></td>
                                    <td>${request.current_stock} ${request.current_unit}</td>
                                </tr>
                                <tr>
                                    <td><strong>Update Type:</strong></td>
                                    <td><span class="badge bg-info">${request.update_type.toUpperCase()}</span></td>
                                </tr>
                                <tr>
                                    <td><strong>Quantity:</strong></td>
                                    <td><strong>${request.quantity} ${request.unit}</strong></td>
                                </tr>
                            </table>
                        </div>
                        <div class="col-md-6">
                            <h6 class="text-primary">Request Details</h6>
                            <table class="table table-borderless">
                                <tr>
                                    <td><strong>Urgency:</strong></td>
                                    <td><span class="urgency-badge ${request.urgency_level}">${request.urgency_level.toUpperCase()}</span></td>
                                </tr>
                                <tr>
                                    <td><strong>Priority:</strong></td>
                                    <td><span class="priority-badge ${request.priority}">${request.priority.toUpperCase()}</span></td>
                                </tr>
                                <tr>
                                    <td><strong>Status:</strong></td>
                                    <td><span class="status-badge ${request.status}">${request.status.toUpperCase()}</span></td>
                                </tr>
                                <tr>
                                    <td><strong>Request Date:</strong></td>
                                    <td>${request.formatted_request_date}</td>
                                </tr>
                                <tr>
                                    <td><strong>Reason:</strong></td>
                                    <td>${request.reason}</td>
                                </tr>
                                <tr>
                                    <td><strong>Notes:</strong></td>
                                    <td>${request.notes || 'No additional notes'}</td>
                                </tr>
                            </table>
                        </div>
                    </div>
                    ${request.admin_response ? `
                    <div class="row mt-3">
                        <div class="col-12">
                            <h6 class="text-primary">Admin Response</h6>
                            <div class="alert alert-info">
                                <strong>Response:</strong> ${request.admin_response}
                                <br>
                                <small class="text-muted">Response Date: ${request.formatted_response_date}</small>
                            </div>
                        </div>
                    </div>
                    ` : ''}
                `;
                
                new bootstrap.Modal(document.getElementById('requestDetailsModal')).show();
            }
        })
        .catch(error => console.error('Error loading request details:', error));
}

function openResponseModal(requestId, action) {
    currentRequestId = requestId;
    const modal = document.getElementById('responseModal');
    const title = document.getElementById('responseModalTitle');
    const actionField = document.getElementById('responseAction');
    const stockSection = document.getElementById('stockUpdateSection');
    
    actionField.value = action;
    
    switch(action) {
        case 'approve':
            title.textContent = 'Approve Request';
            stockSection.style.display = 'block';
            break;
        case 'reject':
            title.textContent = 'Reject Request';
            stockSection.style.display = 'none';
            break;
        case 'complete':
            title.textContent = 'Complete Request';
            stockSection.style.display = 'block';
            break;
    }
    
    document.getElementById('responseRequestId').value = requestId;
    document.getElementById('responseMessage').value = '';
    document.getElementById('stockQuantity').value = '';
    document.getElementById('stockUnit').value = '';
    
    new bootstrap.Modal(modal).show();
}

function submitResponse() {
    const formData = new FormData(document.getElementById('responseForm'));
    
    fetch('admin_respond_to_stock_request.php', {
        method: 'POST',
        body: formData
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            bootstrap.Modal.getInstance(document.getElementById('responseModal')).hide();
            Swal.fire({
                icon: 'success',
                title: 'Response Submitted!',
                text: data.message,
                confirmButtonColor: '#8B4543'
            });
            loadStats();
            loadRequests();
        } else {
            Swal.fire({
                icon: 'error',
                title: 'Submission Failed',
                text: data.error || 'An error occurred while submitting your response.',
                confirmButtonColor: '#8B4543'
            });
        }
    })
    .catch(error => {
        console.error('Error submitting response:', error);
        Swal.fire({
            icon: 'error',
            title: 'Submission Failed',
            text: 'An error occurred while submitting your response.',
            confirmButtonColor: '#8B4543'
        });
    });
}

function applyFilters() {
    filterRequests();
    // Show a brief success message
    const filterBtn = document.querySelector('.btn-filter');
    const originalText = filterBtn.innerHTML;
    filterBtn.innerHTML = '<i class="fas fa-check"></i> Filters Applied';
    filterBtn.style.background = 'var(--success-color)';
    filterBtn.style.borderColor = 'var(--success-color)';
    filterBtn.style.color = 'white';
    
    setTimeout(() => {
        filterBtn.innerHTML = originalText;
        filterBtn.style.background = '';
        filterBtn.style.borderColor = '';
        filterBtn.style.color = '';
    }, 1500);
}

function clearFilters() {
    // Reset all filter inputs
    document.getElementById('searchStockman').value = '';
    document.getElementById('filterStatus').value = '';
    document.getElementById('filterUrgency').value = '';
    document.getElementById('filterBranch').value = '';
    
    // Re-apply filters to show all data
    filterRequests();
    
    // Show a brief success message
    const clearBtn = document.querySelector('.btn-outline-secondary');
    const originalText = clearBtn.innerHTML;
    clearBtn.innerHTML = '<i class="fas fa-check"></i> Cleared';
    clearBtn.style.background = 'var(--success-color)';
    clearBtn.style.borderColor = 'var(--success-color)';
    clearBtn.style.color = 'white';
    
    setTimeout(() => {
        clearBtn.innerHTML = originalText;
        clearBtn.style.background = '';
        clearBtn.style.borderColor = '';
        clearBtn.style.color = '';
    }, 1500);
}

function refreshData() {
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
