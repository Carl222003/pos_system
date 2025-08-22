<?php
require_once 'db_connect.php';
require_once 'auth_function.php';

checkAdminLogin();
include('header.php');
?>

<style>
.simple-dashboard {
    background: #f8f9fa;
    min-height: 100vh;
    padding: 20px 0;
}

.simple-title {
    color: #8B4543;
    font-size: 2rem;
    font-weight: 700;
    margin-bottom: 2rem;
    text-align: center;
}

.assignment-grid {
    background: white;
    border-radius: 12px;
    padding: 2rem;
    box-shadow: 0 4px 6px rgba(0,0,0,0.1);
    margin-bottom: 2rem;
}

.grid-header {
    display: grid;
    grid-template-columns: 300px repeat(auto-fit, minmax(150px, 1fr));
    gap: 1rem;
    padding: 1rem;
    background: #f8f9fa;
    border-radius: 8px;
    margin-bottom: 1rem;
    font-weight: 600;
    color: #8B4543;
}

.grid-row {
    display: grid;
    grid-template-columns: 300px repeat(auto-fit, minmax(150px, 1fr));
    gap: 1rem;
    padding: 1rem;
    border-bottom: 1px solid #eee;
    align-items: center;
}

.grid-row:hover {
    background: #f8f9fa;
}

.grid-row:last-child {
    border-bottom: none;
}

.product-info {
    display: flex;
    align-items: center;
    gap: 1rem;
}

.product-image {
    width: 50px;
    height: 50px;
    object-fit: cover;
    border-radius: 8px;
}

.product-details h6 {
    margin: 0;
    color: #333;
    font-size: 0.9rem;
}

.product-details small {
    color: #6c757d;
    font-size: 0.8rem;
}

.status-badge {
    font-size: 0.7rem;
    padding: 0.2rem 0.5rem;
    border-radius: 12px;
    font-weight: 600;
    text-transform: uppercase;
    margin-top: 0.25rem;
    display: inline-block;
}

.status-available {
    background: #28a745;
    color: white;
}

.status-unavailable {
    background: #dc3545;
    color: white;
}

.status-unknown {
    background: #6c757d;
    color: white;
}

.branch-checkbox {
    display: flex;
    justify-content: center;
    align-items: center;
}

.checkbox-wrapper {
    position: relative;
    display: inline-block;
}

.checkbox-wrapper input[type="checkbox"] {
    width: 20px;
    height: 20px;
    cursor: pointer;
}

.checkbox-wrapper input[type="checkbox"]:checked {
    accent-color: #8B4543;
}

.quantity-input {
    width: 60px;
    padding: 0.25rem;
    border: 1px solid #ddd;
    border-radius: 4px;
    text-align: center;
    font-size: 0.8rem;
}

.quantity-input:disabled {
    background: #f8f9fa;
    color: #6c757d;
}

.actions-section {
    background: white;
    border-radius: 12px;
    padding: 1.5rem;
    box-shadow: 0 4px 6px rgba(0,0,0,0.1);
    margin-bottom: 2rem;
    text-align: center;
}

.btn-save {
    background: #8B4543;
    color: white;
    border: none;
    padding: 1rem 2rem;
    border-radius: 8px;
    font-size: 1.1rem;
    font-weight: 600;
    cursor: pointer;
    transition: all 0.3s ease;
    margin: 0 0.5rem;
}

.btn-save:hover {
    background: #723937;
    transform: translateY(-2px);
}

.btn-clear {
    background: #6c757d;
    color: white;
    border: none;
    padding: 1rem 2rem;
    border-radius: 8px;
    font-size: 1.1rem;
    font-weight: 600;
    cursor: pointer;
    transition: all 0.3s ease;
    margin: 0 0.5rem;
}

.btn-clear:hover {
    background: #5a6268;
    transform: translateY(-2px);
}

.loading {
    text-align: center;
    padding: 2rem;
    color: #6c757d;
}

.alert {
    border-radius: 8px;
    padding: 1rem;
    margin-bottom: 1rem;
}

.alert-success {
    background: #d4edda;
    color: #155724;
    border: 1px solid #c3e6cb;
}

.alert-danger {
    background: #f8d7da;
    color: #721c24;
    border: 1px solid #f5c6cb;
}

.branch-label {
    font-size: 0.8rem;
    font-weight: 500;
    color: #8B4543;
    text-align: center;
    margin-bottom: 0.5rem;
}

.quick-actions {
    text-align: center;
    margin-bottom: 2rem;
}

.btn-quick {
    background: #28a745;
    color: white;
    border: none;
    padding: 0.75rem 1.5rem;
    border-radius: 6px;
    font-weight: 600;
    margin: 0 0.5rem;
    cursor: pointer;
    transition: all 0.3s ease;
}

.btn-quick:hover {
    background: #218838;
    transform: translateY(-1px);
}
</style>

<div class="simple-dashboard">
    <div class="container-fluid">
        <h1 class="simple-title">🔗 Manual Product-Branch Assignment</h1>
        
        <!-- Quick Actions -->
        <div class="quick-actions">
            <button class="btn btn-quick" onclick="loadAssignmentGrid()">
                🔄 Refresh Grid
            </button>
            <button class="btn btn-quick" onclick="clearAllAssignments()">
                🗑️ Clear All Assignments
            </button>
        </div>
        
        <!-- Assignment Grid -->
        <div class="assignment-grid">
            <h4 class="mb-3">📋 Product-Branch Assignment Matrix</h4>
            <div id="assignmentGrid">
                <div class="loading">
                    <div class="spinner-border text-primary" role="status">
                        <span class="visually-hidden">Loading...</span>
                    </div>
                    <p>Loading assignment grid...</p>
                </div>
            </div>
        </div>
        
        <!-- Save Actions -->
        <div class="actions-section">
            <h5 class="mb-3">💾 Save Your Assignments</h5>
            <p class="text-muted mb-3">Check the boxes for products you want in each branch, set quantities, then save</p>
            <button class="btn-save" onclick="saveAllAssignments()">
                💾 Save All Assignments
            </button>
            <button class="btn-clear" onclick="clearAllAssignments()">
                🗑️ Clear All
            </button>
        </div>
    </div>
</div>

<script>
let allProducts = [];
let allBranches = [];
let currentAssignments = {};

// Initialize page
document.addEventListener('DOMContentLoaded', function() {
    loadAssignmentGrid();
});

function loadAssignmentGrid() {
    // Load products and branches first
    Promise.all([
        fetch('get_all_products.php').then(r => r.json()),
        fetch('get_branches.php').then(r => r.json()),
        fetch('simple_get_connections.php').then(r => r.json())
    ]).then(([productsData, branchesData, connectionsData]) => {
        if (productsData.success && branchesData.success) {
            allProducts = productsData.products;
            allBranches = branchesData.branches;
            
            // Process current assignments
            if (connectionsData.success) {
                currentAssignments = {};
                connectionsData.connections.forEach(connection => {
                    const productId = connection.product_id;
                    currentAssignments[productId] = {};
                    connection.branches.forEach(branch => {
                        currentAssignments[productId][branch.branch_id] = true;
                    });
                });
            }
            
            displayAssignmentGrid();
        } else {
            document.getElementById('assignmentGrid').innerHTML = 
                '<div class="alert alert-danger">❌ Error loading data</div>';
        }
    }).catch(error => {
        console.error('Error loading data:', error);
        document.getElementById('assignmentGrid').innerHTML = 
            '<div class="alert alert-danger">❌ Error loading data</div>';
    });
}

function displayAssignmentGrid() {
    const grid = document.getElementById('assignmentGrid');
    
    if (allProducts.length === 0 || allBranches.length === 0) {
        grid.innerHTML = '<div class="alert alert-info">📦 No products or branches found</div>';
        return;
    }
    
    // Create header row
    let html = '<div class="grid-header">';
    html += '<div>Product</div>';
    allBranches.forEach(branch => {
        html += `<div class="branch-label">${branch.branch_name}</div>`;
    });
    html += '</div>';
    
    // Create product rows
    allProducts.forEach(product => {
        html += '<div class="grid-row">';
        
        // Product info column
        html += `
            <div class="product-info">
                <img src="${product.product_image || 'uploads/products/default.png'}" 
                     alt="${product.product_name}" 
                     class="product-image"
                     onerror="this.src='uploads/products/default.png'">
                <div class="product-details">
                    <h6>${product.product_name}</h6>
                    <small>₱${parseFloat(product.product_price).toFixed(2)}</small>
                    <div class="status-badge ${product.product_status === 'Available' ? 'status-available' : (product.product_status === 'Unavailable' ? 'status-unavailable' : 'status-unknown')}">
                        ${product.product_status || 'Unknown'}
                    </div>
                </div>
            </div>
        `;
        
        // Branch assignment columns
        allBranches.forEach(branch => {
            const isAssigned = currentAssignments[product.product_id] && 
                              currentAssignments[product.product_id][branch.branch_id];
            
            html += `
                <div class="branch-checkbox">
                    <div class="checkbox-wrapper">
                        <input type="checkbox" 
                               id="product_${product.product_id}_branch_${branch.branch_id}"
                               data-product="${product.product_id}"
                               data-branch="${branch.branch_id}"
                               ${isAssigned ? 'checked' : ''}
                               onchange="toggleAssignment(this)">
                        <label for="product_${product.product_id}_branch_${branch.branch_id}"></label>
                    </div>
                    <input type="number" 
                           class="quantity-input" 
                           id="qty_${product.product_id}_${branch.branch_id}"
                           value="10" 
                           min="1" 
                           ${!isAssigned ? 'disabled' : ''}
                           placeholder="Qty">
                </div>
            `;
        });
        
        html += '</div>';
    });
    
    grid.innerHTML = html;
}

function toggleAssignment(checkbox) {
    const productId = checkbox.dataset.product;
    const branchId = checkbox.dataset.branch;
    const quantityInput = document.getElementById(`qty_${productId}_${branchId}`);
    
    if (checkbox.checked) {
        quantityInput.disabled = false;
        quantityInput.value = '10'; // Default quantity
    } else {
        quantityInput.disabled = true;
        quantityInput.value = '';
    }
}

function saveAllAssignments() {
    const assignments = [];
    
    // Collect all checked assignments
    allProducts.forEach(product => {
        allBranches.forEach(branch => {
            const checkbox = document.getElementById(`product_${product.product_id}_branch_${branch.branch_id}`);
            if (checkbox.checked) {
                const quantityInput = document.getElementById(`qty_${product.product_id}_${branch.branch_id}`);
                assignments.push({
                    product_id: product.product_id,
                    branch_id: branch.branch_id,
                    quantity: parseInt(quantityInput.value) || 10
                });
            }
        });
    });
    
    if (assignments.length === 0) {
        alert('Please select at least one product-branch assignment');
        return;
    }
    
    // Save assignments
    fetch('save_all_assignments.php', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
        },
        body: JSON.stringify({ assignments: assignments })
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            alert(`✅ Success! Saved ${assignments.length} assignments.`);
            // Update current assignments
            currentAssignments = {};
            assignments.forEach(assignment => {
                if (!currentAssignments[assignment.product_id]) {
                    currentAssignments[assignment.product_id] = {};
                }
                currentAssignments[assignment.product_id][assignment.branch_id] = true;
            });
        } else {
            alert('❌ Error: ' + data.error);
        }
    })
    .catch(error => {
        console.error('Error saving assignments:', error);
        alert('❌ Error saving assignments');
    });
}

function clearAllAssignments() {
    if (!confirm('This will clear ALL product-branch assignments. Continue?')) {
        return;
    }
    
    // Uncheck all checkboxes and disable quantity inputs
    allProducts.forEach(product => {
        allBranches.forEach(branch => {
            const checkbox = document.getElementById(`product_${product.product_id}_branch_${branch.branch_id}`);
            const quantityInput = document.getElementById(`qty_${product.product_id}_${branch.branch_id}`);
            
            if (checkbox) {
                checkbox.checked = false;
            }
            if (quantityInput) {
                quantityInput.disabled = true;
                quantityInput.value = '';
            }
        });
    });
    
    // Clear current assignments
    currentAssignments = {};
    
    alert('🗑️ All assignments cleared. Remember to save if you want to keep the changes.');
}
</script>

<?php include('footer.php'); ?>
