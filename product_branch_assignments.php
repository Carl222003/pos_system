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

.assignment-dashboard {
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

.assignments-table {
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

.product-image {
    width: 50px;
    height: 50px;
    object-fit: cover;
    border-radius: 8px;
}

.assignment-badges {
    display: flex;
    flex-direction: column;
    gap: 0.5rem;
}

.assigned-branches-info {
    display: flex;
    flex-direction: column;
    gap: 0.5rem;
}

.branches-count {
    display: flex;
    align-items: center;
    color: var(--success-color);
    font-size: 0.875rem;
    font-weight: 600;
}

.branches-count i {
    color: var(--primary-color);
}

.branches-list {
    display: flex;
    flex-wrap: wrap;
    gap: 0.25rem;
}

.no-assignments {
    text-align: center;
}

.assignment-badge {
    display: inline-flex;
    align-items: center;
    padding: 0.375rem 0.75rem;
    border-radius: 0.5rem;
    font-size: 0.75rem;
    font-weight: 500;
    text-transform: uppercase;
    letter-spacing: 0.5px;
    transition: all 0.2s ease;
    margin: 0.1rem;
}

.assignment-badge.assigned {
    background: linear-gradient(135deg, var(--success-color), #20c997);
    color: white;
    box-shadow: 0 2px 4px rgba(40, 167, 69, 0.2);
}

.assignment-badge.assigned:hover {
    transform: translateY(-1px);
    box-shadow: 0 4px 8px rgba(40, 167, 69, 0.3);
}

.assignment-badge.unassigned {
    background: linear-gradient(135deg, var(--warning-color), #fd7e14);
    color: #212529;
    box-shadow: 0 2px 4px rgba(255, 193, 7, 0.2);
}

.assignment-badge.unassigned:hover {
    transform: translateY(-1px);
    box-shadow: 0 4px 8px rgba(255, 193, 7, 0.3);
}

.btn-assign {
    background: var(--success-color);
    border: none;
    color: white;
    padding: 0.375rem 0.75rem;
    border-radius: 6px;
    font-size: 0.875rem;
    transition: all 0.2s ease;
}

.btn-assign:hover {
    background: #218838;
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

.bulk-actions {
    margin-bottom: 1rem;
}

.btn-bulk {
    background: var(--info-color);
    border: none;
    color: white;
    padding: 0.5rem 1rem;
    border-radius: 6px;
    font-size: 0.875rem;
    margin-right: 0.5rem;
}

.btn-bulk:hover {
    background: #138496;
    color: white;
}

.assignment-info-section {
    margin-bottom: 1.5rem;
}

.info-card {
    background: white;
    border-radius: 12px;
    padding: 1.5rem;
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

.info-icon.assigned {
    background: var(--success-color);
    color: white;
}

.info-icon.unassigned {
    background: var(--danger-color);
    color: white;
}

.info-icon.partial {
    background: var(--warning-color);
    color: #212529;
}

.info-item strong {
    display: block;
    color: var(--text-dark);
    margin-bottom: 0.25rem;
}

.info-item small {
    color: #6c757d;
    font-size: 0.8rem;
}

/* Modal Enhancements */
.product-summary {
    background: #f8f9fa;
    border-radius: 8px;
    padding: 1rem;
    margin-bottom: 1rem;
}

.current-assignments {
    padding-top: 1rem;
    border-top: 1px solid #dee2e6;
}

.assignment-status .badge {
    font-size: 0.875rem;
    padding: 0.5rem 0.75rem;
}

.branch-selection {
    background: white;
    border-radius: 8px;
    padding: 1rem;
    border: 1px solid #dee2e6;
}

.branch-option {
    padding: 0.75rem;
    border-radius: 6px;
    transition: all 0.2s ease;
}

.branch-option.assigned {
    background: #d4edda;
    border: 1px solid #c3e6cb;
}

.branch-option.unassigned {
    background: #f8f9fa;
    border: 1px solid #dee2e6;
}

.branch-option:hover {
    transform: translateY(-1px);
    box-shadow: 0 2px 4px rgba(0,0,0,0.1);
}

.branch-info strong {
    color: var(--text-dark);
}

.branch-info small {
    color: #6c757d;
    font-size: 0.75rem;
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

.btn-filter:active {
    transform: translateY(0);
    box-shadow: 0 2px 4px rgba(139, 69, 67, 0.1);
}

.btn-filter i {
    font-size: 0.9rem;
}

/* Filter Section Enhancements */
.filters-section .form-control,
.filters-section .form-select {
    border: 1px solid #dee2e6;
    border-radius: 8px;
    transition: border-color 0.2s ease, box-shadow 0.2s ease;
    padding: 0.75rem 1rem;
    font-size: 0.9rem;
}

.filters-section .form-control:focus,
.filters-section .form-select:focus {
    border-color: var(--primary-color);
    box-shadow: 0 0 0 0.2rem rgba(139, 69, 67, 0.25);
    background-color: #fff;
}

.filters-section .form-label {
    color: var(--primary-color);
    font-weight: 600;
    margin-bottom: 0.5rem;
    font-size: 0.9rem;
    text-transform: uppercase;
    letter-spacing: 0.5px;
}

.filters-section {
    background: white;
    border-radius: 12px;
    padding: 1.5rem;
    margin-bottom: 2rem;
    box-shadow: 0 2px 4px rgba(0,0,0,0.05);
    border: 1px solid #f0f0f0;
}

.filters-section .row {
    align-items: end;
}
</style>

<div class="assignment-dashboard">
    <div class="container-fluid">
        <h1 class="dashboard-title">
            🏪 Product Branch Assignments
        </h1>
        
        <!-- Statistics Cards -->
        <div class="stats-cards" id="statsCards">
            <div class="stat-card">
                <h2 class="stat-number" id="totalProducts">-</h2>
                <p class="stat-label">Total Products</p>
            </div>
            <div class="stat-card">
                <h2 class="stat-number" id="totalBranches">-</h2>
                <p class="stat-label">Active Branches</p>
            </div>
            <div class="stat-card">
                <h2 class="stat-number" id="totalAssignments">-</h2>
                <p class="stat-label">Total Assignments</p>
            </div>
            <div class="stat-card">
                <h2 class="stat-number" id="unassignedProducts">-</h2>
                <p class="stat-label">Unassigned Products</p>
            </div>
            <div class="stat-card">
                <h2 class="stat-number" id="fullyAssignedProducts">-</h2>
                <p class="stat-label">Fully Assigned</p>
            </div>
            <div class="stat-card">
                <h2 class="stat-number" id="partiallyAssignedProducts">-</h2>
                <p class="stat-label">Partial Assignments</p>
            </div>
        </div>
        
        <!-- Filters Section -->
        <div class="filters-section">
            <div class="row">
                <div class="col-md-3">
                    <label class="form-label">Search Products</label>
                    <input type="text" class="form-control" id="searchProduct" placeholder="Search by product name...">
                </div>
                <div class="col-md-3">
                    <label class="form-label">Filter by Category</label>
                    <select class="form-select" id="filterCategory">
                        <option value="">All Categories</option>
                    </select>
                </div>
                <div class="col-md-3">
                    <label class="form-label">Filter by Branch</label>
                    <select class="form-select" id="filterBranch">
                        <option value="">All Branches</option>
                    </select>
                </div>
                <div class="col-md-3">
                    <label class="form-label">Assignment Status</label>
                    <select class="form-select" id="filterStatus">
                        <option value="">All Products</option>
                        <option value="assigned">Assigned Products</option>
                        <option value="unassigned">Unassigned Products</option>
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
                </div>
            </div>
        </div>
        
        <!-- Branch Assignment Info -->
        <div class="assignment-info-section">
            <div class="row">
                <div class="col-12">
                    <div class="info-card">
                        <div class="info-header">
                            <i class="fas fa-info-circle me-2"></i>
                            <strong>Branch Assignment Guide</strong>
                        </div>
                        <div class="info-content">
                            <div class="row">
                                <div class="col-md-4">
                                    <div class="info-item">
                                        <span class="info-icon assigned">✓</span>
                                        <div>
                                            <strong>Assigned</strong>
                                            <small>Product available at this branch</small>
                                        </div>
                                    </div>
                                </div>
                                <div class="col-md-4">
                                    <div class="info-item">
                                        <span class="info-icon unassigned">✗</span>
                                        <div>
                                            <strong>Not Assigned</strong>
                                            <small>Product not available at this branch</small>
                                        </div>
                                    </div>
                                </div>
                                <div class="col-md-4">
                                    <div class="info-item">
                                        <span class="info-icon partial">⚠</span>
                                        <div>
                                            <strong>Partial Assignment</strong>
                                            <small>Product available at some branches only</small>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        
        <!-- Bulk Actions -->
        <div class="bulk-actions">
            <button class="btn btn-bulk" onclick="bulkAssignAll()">
                📦 Assign All Products to All Branches
            </button>
            <button class="btn btn-bulk" onclick="refreshData()">
                🔄 Refresh Data
            </button>
            <button class="btn btn-outline-info" onclick="debugAssignments()">
                🐛 Debug Assignments
            </button>
        </div>
        
        <!-- Assignments Table -->
        <div class="assignments-table">
            <table class="table table-hover">
                <thead>
                    <tr>
                        <th>Product</th>
                        <th>Category</th>
                        <th>Price</th>
                        <th>Status</th>
                        <th>
                            <i class="fas fa-building me-1"></i>
                            Branch Availability
                        </th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody id="assignmentsTableBody">
                    <tr>
                        <td colspan="6" class="loading">
                            <div class="spinner-border text-primary" role="status">
                                <span class="visually-hidden">Loading...</span>
                            </div>
                            <p>Loading product assignments...</p>
                        </td>
                    </tr>
                </tbody>
            </table>
        </div>
    </div>
</div>

<!-- Assignment Modal -->
<div class="modal fade" id="assignmentModal" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Manage Product Assignments</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body" id="modalBody">
                <!-- Content loaded dynamically -->
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                <button type="button" class="btn btn-primary" onclick="saveAssignments()">Save Changes</button>
            </div>
        </div>
    </div>
</div>

<script>
let allAssignments = [];
let allBranches = [];
let currentProductId = null;

// Initialize page
document.addEventListener('DOMContentLoaded', function() {
    loadStats();
    loadBranches();
    loadAssignments();
    
    // Also load categories independently to ensure they're available
    loadCategories([]);
    
    // Setup filters
    document.getElementById('searchProduct').addEventListener('input', filterAssignments);
    document.getElementById('filterCategory').addEventListener('change', filterAssignments);
    document.getElementById('filterBranch').addEventListener('change', filterAssignments);
    document.getElementById('filterStatus').addEventListener('change', filterAssignments);
});

function loadStats() {
    fetch('get_assignment_stats.php')
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                document.getElementById('totalProducts').textContent = data.stats.total_products;
                document.getElementById('totalBranches').textContent = data.stats.total_branches;
                document.getElementById('totalAssignments').textContent = data.stats.total_assignments;
                document.getElementById('unassignedProducts').textContent = data.stats.unassigned_products;
                
                // Calculate additional stats from assignments data
                if (allAssignments && allAssignments.length > 0) {
                    const fullyAssigned = allAssignments.filter(a => 
                        a.assigned_branches && a.assigned_branches.length === allBranches.length
                    ).length;
                    const partiallyAssigned = allAssignments.filter(a => 
                        a.assigned_branches && a.assigned_branches.length > 0 && 
                        a.assigned_branches.length < allBranches.length
                    ).length;
                    
                    document.getElementById('fullyAssignedProducts').textContent = fullyAssigned;
                    document.getElementById('partiallyAssignedProducts').textContent = partiallyAssigned;
                }
            }
        })
        .catch(error => console.error('Error loading stats:', error));
}

function loadBranches() {
    fetch('get_branches.php')
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                allBranches = data.branches;
                
                const filterBranch = document.getElementById('filterBranch');
                filterBranch.innerHTML = '<option value="">All Branches</option>';
                
                data.branches.forEach(branch => {
                    filterBranch.innerHTML += `<option value="${branch.branch_id}">${branch.branch_name}</option>`;
                });
            }
        })
        .catch(error => console.error('Error loading branches:', error));
}

function loadAssignments() {
    fetch('get_product_assignments.php')
        .then(response => response.json())
        .then(data => {
            console.log('Raw assignment data:', data); // Debug log
            if (data.success) {
                allAssignments = data.assignments;
                console.log('Processed assignments:', allAssignments); // Debug log
                
                // Log branch assignment details for debugging
                allAssignments.forEach((assignment, index) => {
                    console.log(`Product ${index + 1}: ${assignment.product_name}`);
                    console.log(`  - Assigned branches:`, assignment.assigned_branches);
                    console.log(`  - Branch count: ${assignment.assigned_branches ? assignment.assigned_branches.length : 0}`);
                });
                
                displayAssignments(data.assignments);
                loadCategories(data.assignments);
            } else {
                document.getElementById('assignmentsTableBody').innerHTML = 
                    `<tr><td colspan="6" class="no-data">❌ Error: ${data.error}</td></tr>`;
            }
        })
        .catch(error => {
            console.error('Error loading assignments:', error);
            document.getElementById('assignmentsTableBody').innerHTML = 
                `<tr><td colspan="6" class="no-data">❌ Error loading assignments</td></tr>`;
        });
}

function loadCategories(assignments) {
    // First try to load from assignments if available
    if (assignments && assignments.length > 0) {
        const categories = [...new Set(assignments.map(a => a.category_name).filter(c => c))];
        const filterCategory = document.getElementById('filterCategory');
        filterCategory.innerHTML = '<option value="">All Categories</option>';
        
        categories.forEach(category => {
            filterCategory.innerHTML += `<option value="${category}">${category}</option>`;
        });
    } else {
        // If no assignments, fetch categories directly from database
        fetch('get_categories.php')
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    const filterCategory = document.getElementById('filterCategory');
                    filterCategory.innerHTML = '<option value="">All Categories</option>';
                    
                    data.categories.forEach(category => {
                        filterCategory.innerHTML += `<option value="${category.category_name}">${category.category_name}</option>`;
                    });
                }
            })
            .catch(error => {
                console.error('Error loading categories:', error);
                // Fallback: show at least "All Categories"
                const filterCategory = document.getElementById('filterCategory');
                filterCategory.innerHTML = '<option value="">All Categories</option>';
            });
    }
}

function displayAssignments(assignments) {
    const tbody = document.getElementById('assignmentsTableBody');
    
    if (assignments.length === 0) {
        tbody.innerHTML = '<tr><td colspan="6" class="no-data">📦 No products found</td></tr>';
        return;
    }
    
    tbody.innerHTML = assignments.map(assignment => {
        const assignedBranches = assignment.assigned_branches || [];
        const isAssigned = assignedBranches.length > 0;
        
        return `
            <tr>
                <td>
                    <div class="d-flex align-items-center">
                        <img src="${assignment.product_image || 'uploads/products/default.png'}" 
                             alt="${assignment.product_name}" 
                             class="product-image me-3"
                             onerror="this.src='uploads/products/default.png'">
                        <div>
                            <strong>${assignment.product_name}</strong>
                            <br>
                            <small class="text-muted">ID: ${assignment.product_id}</small>
                        </div>
                    </div>
                </td>
                <td>
                    <span class="badge bg-secondary">${assignment.category_name || 'Uncategorized'}</span>
                </td>
                <td>₱${parseFloat(assignment.product_price).toFixed(2)}</td>
                <td>
                    <span class="badge ${assignment.product_status === 'Available' ? 'bg-success' : 'bg-warning'}">
                        ${assignment.product_status}
                    </span>
                </td>
                <td>
                    <div class="assignment-badges">
                        ${isAssigned ? 
                            `<div class="assigned-branches-info">
                                <div class="branches-count">
                                    <i class="fas fa-building me-1"></i>
                                    <strong>${assignedBranches.length} Branch${assignedBranches.length > 1 ? 'es' : ''}</strong>
                                </div>
                                <div class="branches-list">
                                    ${assignedBranches.map(branch => 
                                        `<span class="assignment-badge assigned" title="Branch ID: ${branch.branch_id}">
                                            <i class="fas fa-check-circle me-1"></i>${branch.branch_name}
                                        </span>`
                                    ).join('')}
                                </div>
                            </div>` :
                            '<div class="no-assignments">
                                <span class="assignment-badge unassigned">
                                    <i class="fas fa-times-circle me-1"></i>Not assigned
                                </span>
                                <small class="text-muted d-block mt-1">Click "Quick Assign" to assign to all branches</small>
                            </div>'
                        }
                    </div>
                </td>
                <td>
                    <button class="btn btn-sm btn-assign" onclick="openAssignmentModal(${assignment.product_id})">
                        ⚙️ Manage
                    </button>
                    ${!isAssigned ? 
                        `<button class="btn btn-sm btn-outline-success ms-1" onclick="quickAssignAll(${assignment.product_id})">
                            🚀 Quick Assign
                        </button>` : ''
                    }
                </td>
            </tr>
        `;
    }).join('');
}

function filterAssignments() {
    const search = document.getElementById('searchProduct').value.toLowerCase();
    const categoryFilter = document.getElementById('filterCategory').value;
    const branchFilter = document.getElementById('filterBranch').value;
    const statusFilter = document.getElementById('filterStatus').value;
    
    let filtered = allAssignments.filter(assignment => {
        const matchesSearch = assignment.product_name.toLowerCase().includes(search);
        const matchesCategory = !categoryFilter || assignment.category_name === categoryFilter;
        const matchesBranch = !branchFilter || (assignment.assigned_branches && 
            assignment.assigned_branches.some(b => b.branch_id == branchFilter));
        
        let matchesStatus = true;
        if (statusFilter === 'assigned') {
            matchesStatus = assignment.assigned_branches && assignment.assigned_branches.length > 0;
        } else if (statusFilter === 'unassigned') {
            matchesStatus = !assignment.assigned_branches || assignment.assigned_branches.length === 0;
        }
        
        return matchesSearch && matchesCategory && matchesBranch && matchesStatus;
    });
    
    displayAssignments(filtered);
}

function openAssignmentModal(productId) {
    currentProductId = productId;
    
    fetch(`get_product_assignment_details.php?product_id=${productId}`)
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                const modalBody = document.getElementById('modalBody');
                modalBody.innerHTML = `
                    <div class="mb-4">
                        <div class="product-summary">
                            <div class="d-flex align-items-center mb-3">
                                <img src="${data.product.product_image || 'uploads/products/default.png'}" 
                                     alt="${data.product.product_name}" 
                                     class="product-image me-3"
                                     onerror="this.src='uploads/products/default.png'"
                                     style="width: 60px; height: 60px; object-fit: cover; border-radius: 8px;">
                                <div>
                                    <h6 class="mb-1"><strong>${data.product.product_name}</strong></h6>
                                    <p class="text-muted mb-0">ID: ${data.product.product_id}</p>
                                    <p class="text-muted mb-0">Price: ₱${parseFloat(data.product.product_price).toFixed(2)}</p>
                                </div>
                            </div>
                            
                            <div class="current-assignments mb-3">
                                <h6 class="text-primary">
                                    <i class="fas fa-building me-1"></i>
                                    Current Branch Assignments
                                </h6>
                                <div class="assignment-status">
                                    ${data.assigned_branches.length > 0 ? 
                                        `<span class="badge bg-success me-2">
                                            <i class="fas fa-check-circle me-1"></i>
                                            ${data.assigned_branches.length} Branch${data.assigned_branches.length > 1 ? 'es' : ''} Assigned
                                        </span>` :
                                        `<span class="badge bg-warning">
                                            <i class="fas fa-exclamation-triangle me-1"></i>
                                            No Branches Assigned
                                        </span>`
                                    }
                                </div>
                            </div>
                        </div>
                        
                        <div class="branch-selection">
                            <h6 class="text-primary mb-3">
                                <i class="fas fa-edit me-1"></i>
                                Select Branches for This Product
                            </h6>
                            <div class="row">
                                ${data.all_branches.map(branch => {
                                    const isAssigned = data.assigned_branches.includes(branch.branch_id);
                                    return `
                                        <div class="col-md-6 mb-3">
                                            <div class="branch-option ${isAssigned ? 'assigned' : 'unassigned'}">
                                                <div class="form-check">
                                                    <input class="form-check-input" type="checkbox" 
                                                           id="branch_${branch.branch_id}" 
                                                           value="${branch.branch_id}"
                                                           ${isAssigned ? 'checked' : ''}>
                                                    <label class="form-check-label" for="branch_${branch.branch_id}">
                                                        <div class="branch-info">
                                                            <strong>${branch.branch_name}</strong>
                                                            <small class="d-block text-muted">Branch ID: ${branch.branch_id}</small>
                                                        </div>
                                                    </label>
                                                </div>
                                            </div>
                                        </div>
                                    `;
                                }).join('')}
                            </div>
                        </div>
                    </div>
                `;
                
                new bootstrap.Modal(document.getElementById('assignmentModal')).show();
            }
        })
        .catch(error => console.error('Error loading assignment details:', error));
}

function saveAssignments() {
    if (!currentProductId) return;
    
    const checkedBoxes = document.querySelectorAll('#modalBody input[type="checkbox"]:checked');
    const assignments = Array.from(checkedBoxes).map(cb => cb.value);
    
    const formData = new FormData();
    formData.append('product_id', currentProductId);
    formData.append('assignments', JSON.stringify(assignments));
    
    fetch('save_product_assignments.php', {
        method: 'POST',
        body: formData
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            bootstrap.Modal.getInstance(document.getElementById('assignmentModal')).hide();
            refreshData();
            alert('✅ Assignments saved successfully!');
        } else {
            alert('❌ Error: ' + data.error);
        }
    })
    .catch(error => {
        console.error('Error saving assignments:', error);
        alert('❌ Error saving assignments');
    });
}

function quickAssignAll(productId) {
    if (!confirm('Assign this product to all active branches?')) return;
    
    const formData = new FormData();
    formData.append('product_id', productId);
    
    fetch('quick_assign_product.php', {
        method: 'POST',
        body: formData
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            refreshData();
            alert('✅ Product assigned to all branches!');
        } else {
            alert('❌ Error: ' + data.error);
        }
    })
    .catch(error => {
        console.error('Error in quick assign:', error);
        alert('❌ Error assigning product');
    });
}

function bulkAssignAll() {
    if (!confirm('This will assign ALL products to ALL branches. Continue?')) return;
    
    fetch('bulk_assign_products.php', {
        method: 'POST'
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            refreshData();
            alert(`✅ Success! Assigned ${data.products_assigned} products to ${data.branches_count} branches.`);
        } else {
            alert('❌ Error: ' + data.error);
        }
    })
    .catch(error => {
        console.error('Error in bulk assign:', error);
        alert('❌ Error in bulk assignment');
    });
}

function refreshData() {
    loadStats();
    loadAssignments();
}

function debugAssignments() {
    console.log('=== DEBUG MODE ACTIVATED ===');
    fetch('get_product_assignments.php?debug=1')
        .then(response => response.json())
        .then(data => {
            console.log('🔍 DEBUG DATA:', data);
            if (data.debug) {
                console.log('📊 Table used:', data.debug.table_used);
                console.log('📦 Total products:', data.debug.total_products);
                console.log('🏪 Products with branches:', data.debug.products_with_branches);
                console.log('📋 Raw results sample:', data.debug.raw_results_sample);
                console.log('✅ Processed assignments sample:', data.debug.processed_assignments_sample);
            }
            
            // Show debug info in alert
            let debugMessage = `Debug Information:\n`;
            debugMessage += `Table used: ${data.table_used}\n`;
            debugMessage += `Total products: ${data.assignments.length}\n`;
            debugMessage += `Products with branches: ${data.assignments.filter(a => !empty(a.assigned_branches)).length}\n`;
            
            if (data.debug) {
                debugMessage += `\nCheck browser console for detailed debug info.`;
            }
            
            alert(debugMessage);
        })
        .catch(error => {
            console.error('Debug fetch error:', error);
            alert('❌ Error fetching debug data. Check console for details.');
        });
}

// Filter functions for the new filter buttons
function applyFilters() {
    filterAssignments();
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
    document.getElementById('searchProduct').value = '';
    document.getElementById('filterCategory').value = '';
    document.getElementById('filterBranch').value = '';
    document.getElementById('filterStatus').value = '';
    
    // Re-apply filters to show all data
    filterAssignments();
    
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
</script>

<?php include('footer.php'); ?>