<?php
require_once 'db_connect.php';
require_once 'auth_function.php';

// Check if user is logged in and is a stockman
if (!isset($_SESSION['user_logged_in']) || $_SESSION['user_logged_in'] !== true || $_SESSION['user_type'] !== 'Stockman') {
    header('Location: login.php');
    exit();
}

$branch_id = $_SESSION['branch_id'];

// Get branch information
$branch_stmt = $pdo->prepare("SELECT branch_name FROM pos_branch WHERE branch_id = ?");
$branch_stmt->execute([$branch_id]);
$branch = $branch_stmt->fetch(PDO::FETCH_ASSOC);
$branch_name = $branch['branch_name'] ?? 'Unknown Branch';

include('header.php');
?>

<div class="available-ingredients-bg">
    <div class="container-fluid px-4">


        <!-- Ingredients Table -->
        <div class="ingredients-table-section">
            <div class="table-card-enhanced">
                <div class="table-header-enhanced">
                    <div class="table-title-section">
                        <div class="table-icon">
                            <i class="fas fa-list"></i>
                        </div>
                        <h4 class="table-title">Ingredient Inventory</h4>
                    </div>
                    <div class="table-actions">
                        <div class="view-toggle">
                            <button class="toggle-btn active" data-view="card" onclick="switchView('card')">
                                <i class="fas fa-th-large"></i>
                            </button>
                            <button class="toggle-btn" data-view="table" onclick="switchView('table')">
                                <i class="fas fa-list"></i>
                            </button>
                        </div>
                    </div>
                </div>
                
                <!-- Card View -->
                <div id="cardView" class="ingredients-card-view">
                    <div class="ingredients-grid" id="ingredientsGrid">
                        <!-- Cards will be loaded here -->
                    </div>
                </div>
                
                <!-- Table View -->
                <div id="tableView" class="ingredients-table-view" style="display: none;">
                    <div class="table-responsive">
                        <table class="table enhanced-table" id="ingredientsTable">
                            <thead>
                                <tr>
                                    <th>Ingredient</th>
                                    <th>Category</th>
                                    <th>Current Stock</th>
                                    <th>Status</th>
                                    <th>Expiry Date</th>
                                    <th>Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                <!-- Table rows will be loaded here -->
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<style>
/* Available Ingredients Page Styling */
.available-ingredients-bg {
    background: linear-gradient(135deg, #f8f9fa 0%, #e9ecef 100%);
    min-height: 100vh;
    padding: 2rem 0;
}




.ingredients-table-section {
    margin-bottom: 2rem;
}

.table-card-enhanced {
    background: white;
    border-radius: 20px;
    box-shadow: 0 8px 30px rgba(139, 69, 67, 0.1);
    border: 1px solid rgba(139, 69, 67, 0.1);
    overflow: hidden;
    transition: all 0.3s ease;
}

.table-card-enhanced:hover {
    transform: translateY(-2px);
    box-shadow: 0 12px 40px rgba(139, 69, 67, 0.15);
}

.table-header-enhanced {
    background: linear-gradient(135deg, #8B4543, #A65D5D);
    color: white;
    padding: 1.5rem 2rem;
    display: flex;
    justify-content: space-between;
    align-items: center;
}

.table-title-section {
    display: flex;
    align-items: center;
    gap: 1rem;
}

.table-icon {
    width: 45px;
    height: 45px;
    background: rgba(255, 255, 255, 0.2);
    border-radius: 12px;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 1.3rem;
    color: white;
}

.table-title {
    font-size: 1.4rem;
    font-weight: 700;
    margin: 0;
    color: white;
}

.view-toggle {
    display: flex;
    background: rgba(255, 255, 255, 0.1);
    border-radius: 10px;
    padding: 0.25rem;
    gap: 0.25rem;
}

.toggle-btn {
    background: transparent;
    color: rgba(255, 255, 255, 0.7);
    border: none;
    border-radius: 8px;
    padding: 0.5rem 0.75rem;
    font-size: 1rem;
    transition: all 0.3s ease;
    cursor: pointer;
}

.toggle-btn.active,
.toggle-btn:hover {
    background: rgba(255, 255, 255, 0.2);
    color: white;
    transform: scale(1.05);
}

.ingredients-card-view {
    padding: 2rem;
}

.ingredients-grid {
    display: grid;
    grid-template-columns: repeat(auto-fill, minmax(300px, 1fr));
    gap: 1.5rem;
}

.ingredient-card-enhanced {
    background: white;
    border-radius: 16px;
    padding: 1.5rem;
    box-shadow: 0 6px 25px rgba(139, 69, 67, 0.08);
    border: 1px solid rgba(139, 69, 67, 0.1);
    transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
    position: relative;
    overflow: hidden;
}

.ingredient-card-enhanced:hover {
    transform: translateY(-5px);
    box-shadow: 0 15px 45px rgba(139, 69, 67, 0.2);
}

.ingredient-card-enhanced::before {
    content: '';
    position: absolute;
    top: 0;
    left: 0;
    right: 0;
    height: 4px;
    background: linear-gradient(135deg, #8B4543, #A65D5D);
}

.ingredients-table-view {
    padding: 2rem;
}

.enhanced-table {
    border-radius: 12px;
    overflow: hidden;
    box-shadow: 0 4px 15px rgba(139, 69, 67, 0.08);
}

.enhanced-table th {
    background: linear-gradient(135deg, #8B4543, #A65D5D);
    color: white;
    font-weight: 600;
    border: none;
    padding: 1rem;
    text-transform: uppercase;
    letter-spacing: 0.5px;
    font-size: 0.85rem;
}

.enhanced-table td {
    padding: 1rem;
    border-bottom: 1px solid rgba(139, 69, 67, 0.1);
    vertical-align: middle;
}

.enhanced-table tr:hover {
    background: rgba(139, 69, 67, 0.02);
}

.card-header-info {
    display: flex;
    align-items: center;
    gap: 1rem;
    margin-bottom: 1rem;
}

.ingredient-icon-card {
    width: 45px;
    height: 45px;
    background: linear-gradient(135deg, #8B4543, #A65D5D);
    border-radius: 12px;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 1.2rem;
    color: white;
    flex-shrink: 0;
}

.ingredient-info-card {
    flex: 1;
}

.ingredient-name-card {
    font-size: 1.2rem;
    font-weight: 700;
    color: #2c3e50;
    margin: 0 0 0.25rem 0;
}

.category-badge-card {
    background: linear-gradient(135deg, #6c757d, #5a6268);
    color: white;
    padding: 0.25rem 0.75rem;
    border-radius: 12px;
    font-size: 0.8rem;
    font-weight: 600;
}

.card-stock-info {
    display: flex;
    justify-content: space-between;
    align-items: center;
    margin-bottom: 1rem;
    padding: 1rem;
    background: #f8f9fa;
    border-radius: 12px;
}

.stock-display-card {
    display: flex;
    flex-direction: column;
    align-items: center;
    gap: 0.25rem;
}

.stock-display-card.adequate {
    color: #155724;
}

.stock-display-card.low {
    color: #856404;
}

.stock-number-card {
    font-size: 1.5rem;
    font-weight: 800;
    line-height: 1;
}

.stock-unit-card {
    font-size: 0.8rem;
    font-weight: 600;
    text-transform: uppercase;
    opacity: 0.8;
}

.status-indicator-card {
    display: flex;
    align-items: center;
    gap: 0.5rem;
    padding: 0.5rem 1rem;
    border-radius: 20px;
    font-weight: 600;
    font-size: 0.85rem;
}

.status-indicator-card.available {
    background: #d4edda;
    color: #155724;
}

.status-indicator-card.unavailable {
    background: #f8d7da;
    color: #721c24;
}

.card-actions {
    display: flex;
    gap: 0.75rem;
}

.card-action-btn {
    flex: 1;
    background: #f8f9fa;
    border: 1px solid #dee2e6;
    color: #495057;
    border-radius: 8px;
    padding: 0.75rem;
    font-weight: 600;
    font-size: 0.9rem;
    transition: all 0.3s ease;
    cursor: pointer;
    display: flex;
    align-items: center;
    justify-content: center;
    gap: 0.5rem;
}

.card-action-btn:hover {
    transform: translateY(-2px);
    box-shadow: 0 4px 15px rgba(139, 69, 67, 0.2);
}



.request-btn:hover {
    background: #007bff;
    color: white;
    border-color: #007bff;
}

.adjust-btn:hover {
    background: #28a745;
    color: white;
    border-color: #28a745;
}

.action-buttons {
    display: flex;
    gap: 0.5rem;
}

.table-action-btn {
    background: #f8f9fa;
    border: 1px solid #dee2e6;
    color: #495057;
    border-radius: 6px;
    padding: 0.5rem;
    font-size: 0.9rem;
    transition: all 0.3s ease;
    cursor: pointer;
    display: flex;
    align-items: center;
    justify-content: center;
    width: 35px;
    height: 35px;
}

.table-action-btn:hover {
    transform: translateY(-1px);
    box-shadow: 0 2px 8px rgba(139, 69, 67, 0.2);
}



.request-table-btn:hover {
    background: #007bff;
    color: white;
    border-color: #007bff;
}

.adjust-table-btn:hover {
    background: #28a745;
    color: white;
    border-color: #28a745;
}

.stock-badge {
    padding: 0.4rem 0.8rem;
    border-radius: 12px;
    font-weight: 600;
    font-size: 0.85rem;
}

.stock-badge.adequate {
    background: #d4edda;
    color: #155724;
}

.stock-badge.low {
    background: #fff3cd;
    color: #856404;
}

.ingredient-icon-small {
    width: 30px;
    height: 30px;
    background: linear-gradient(135deg, #8B4543, #A65D5D);
    border-radius: 8px;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 0.9rem;
    color: white;
}

.expiry-warning {
    background-color: #fff3cd;
    color: #856404;
    padding: 0.75rem 1rem;
    border-radius: 12px;
    margin-top: 1rem;
    display: flex;
    align-items: center;
    gap: 0.5rem;
    font-size: 0.9rem;
    font-weight: 600;
    border: 1px solid #ffeaa7;
}

.branch-status-info {
    margin-top: 0.5rem;
}

.branch-status-badge {
    background: rgba(255, 255, 255, 0.2);
    color: white;
    padding: 0.4rem 0.8rem;
    border-radius: 15px;
    font-size: 0.85rem;
    font-weight: 600;
    display: inline-flex;
    align-items: center;
    backdrop-filter: blur(10px);
    border: 1px solid rgba(255, 255, 255, 0.3);
}

.card-action-btn:disabled {
    opacity: 0.6;
    cursor: not-allowed;
    background: #e9ecef;
    color: #6c757d;
    border-color: #dee2e6;
}

.card-action-btn:disabled:hover {
    transform: none;
    box-shadow: none;
}

/* Responsive Design */
@media (max-width: 768px) {
    .ingredients-grid {
        grid-template-columns: 1fr;
    }
    
    .card-actions {
        flex-direction: column;

    }
}

/* Hide spinner arrows for number inputs */
.no-spinner::-webkit-outer-spin-button,
.no-spinner::-webkit-inner-spin-button {
    -webkit-appearance: none;
    margin: 0;
}

.no-spinner[type=number] {
    -moz-appearance: textfield;
}
</style>

<script>
$(document).ready(function() {
    loadAvailableIngredients();
    
    // Auto-refresh every 60 seconds
    setInterval(loadAvailableIngredients, 60000);
});

function loadAvailableIngredients() {
    const gridContainer = document.getElementById('ingredientsGrid');
    const tableBody = document.querySelector('#ingredientsTable tbody');
    
    // Show loading state
            gridContainer.innerHTML = '<div class="text-center p-4"><i class="fas fa-spinner fa-spin fa-2x text-muted"></i><p class="mt-2">Loading branch-specific ingredients...</p></div>';
    tableBody.innerHTML = '<tr><td colspan="6" class="text-center"><i class="fas fa-spinner fa-spin"></i> Loading...</td></tr>';
    
    fetch('get_available_ingredients.php')
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                displayIngredients(data.ingredients);
                
                // Update page title with branch info
                const pageTitle = document.querySelector('.page-title');
                if (pageTitle && data.branch_name) {
                    pageTitle.textContent = `Branch Ingredients - ${data.branch_name}`;
                }
                
                // Show branch info message
                if (data.message) {
                    console.log(data.message);
                }
            } else {
                console.error('Error loading ingredients:', data.error);
                gridContainer.innerHTML = '<div class="alert alert-danger">Error loading ingredients: ' + data.error + '</div>';
                tableBody.innerHTML = '<tr><td colspan="6" class="text-center text-danger">Error loading data</td></tr>';
            }
        })
        .catch(error => {
            console.error('Error:', error);
            gridContainer.innerHTML = '<div class="alert alert-danger">Network error loading ingredients</div>';
            tableBody.innerHTML = '<tr><td colspan="6" class="text-center text-danger">Network error</td></tr>';
        });
}


function displayIngredients(ingredients) {
    const cardView = document.getElementById('ingredientsGrid');
    const tableView = document.querySelector('#ingredientsTable tbody');
    
    // Clear existing content
    cardView.innerHTML = '';
    tableView.innerHTML = '';
    
    ingredients.forEach(ingredient => {
        // Create card
        const card = createIngredientCard(ingredient);
        cardView.appendChild(card);
        
        // Create table row
        const row = createIngredientRow(ingredient);
        tableView.appendChild(row);
    });
}

function createIngredientCard(ingredient) {
    const card = document.createElement('div');
    card.className = 'ingredient-card-enhanced';
    
    const statusClass = ingredient.availability_status || 'unknown';
    const stockLevel = ingredient.stock_level || 'adequate';
    
    card.innerHTML = `
        <div class="card-header-info">
            <div class="ingredient-icon-card">
                <i class="fas fa-cube"></i>
            </div>
            <div class="ingredient-info-card">
                <h5 class="ingredient-name-card">${ingredient.ingredient_name}</h5>
                <span class="category-badge-card">${ingredient.category_name}</span>
            </div>
        </div>
        
        <div class="card-stock-info">
            <div class="stock-display-card ${stockLevel}">
                <span class="stock-number-card">${ingredient.ingredient_quantity}</span>
                <span class="stock-unit-card">${ingredient.ingredient_unit}</span>
            </div>
            <div class="status-indicator-card ${statusClass}" style="background-color: ${ingredient.status_bg}; color: ${ingredient.status_text_color};">
                <i class="fas ${ingredient.status_icon}"></i>
                ${ingredient.status_display || ingredient.ingredient_status}
            </div>
        </div>
        
        ${ingredient.is_expiring ? `
        <div class="expiry-warning">
            <i class="fas fa-exclamation-triangle"></i>
            Expires in ${ingredient.days_until_expiry} days
        </div>
        ` : ''}
        
        <div class="card-actions">
            <button class="card-action-btn adjust-btn" onclick="adjustQuantity(${ingredient.ingredient_id}, '${ingredient.ingredient_name}', ${ingredient.ingredient_quantity}, '${ingredient.ingredient_unit}')">
                <i class="fas fa-edit"></i>
                Adjust Quantity
            </button>
            <button class="card-action-btn request-btn" onclick="requestStock(${ingredient.ingredient_id})" ${ingredient.availability_status === 'available' ? 'disabled' : ''}>
                <i class="fas fa-shopping-cart"></i>
                ${ingredient.availability_status === 'available' ? 'In Stock' : 'Request'}
            </button>
        </div>
    `;
    
    return card;
}

function createIngredientRow(ingredient) {
    const row = document.createElement('tr');
    
    const statusClass = ingredient.availability_status || 'unknown';
    const expiryDate = ingredient.consume_before ? new Date(ingredient.consume_before).toLocaleDateString() : 'No expiry';
    
    row.innerHTML = `
        <td>
            <div class="d-flex align-items-center">
                <div class="ingredient-icon-small me-2">
                    <i class="fas fa-cube"></i>
                </div>
                <div>
                    <div class="fw-bold">${ingredient.ingredient_name}</div>
                    <small class="text-muted">${ingredient.ingredient_unit}</small>
                </div>
            </div>
        </td>
        <td><span class="badge bg-secondary">${ingredient.category_name}</span></td>
        <td>
            <span class="stock-badge ${ingredient.stock_level || 'adequate'}">
                ${ingredient.ingredient_quantity} ${ingredient.ingredient_unit}
            </span>
        </td>
        <td>
            <span class="badge" style="background-color: ${ingredient.status_bg}; color: ${ingredient.status_text_color};">
                <i class="fas ${ingredient.status_icon} me-1"></i>
                ${ingredient.status_display || ingredient.ingredient_status}
            </span>
        </td>
        <td>
            ${expiryDate}
            ${ingredient.is_expiring ? `<br><small class="text-warning">Expires in ${ingredient.days_until_expiry} days</small>` : ''}
        </td>
        <td>
            <div class="action-buttons">
                <button class="table-action-btn adjust-table-btn" onclick="adjustQuantity(${ingredient.ingredient_id}, '${ingredient.ingredient_name}', ${ingredient.ingredient_quantity}, '${ingredient.ingredient_unit}')" title="Adjust Quantity">
                    <i class="fas fa-edit"></i>
                </button>
                <button class="table-action-btn request-table-btn" onclick="requestStock(${ingredient.ingredient_id})" title="Request Stock" ${ingredient.availability_status === 'available' ? 'disabled' : ''}>
                    <i class="fas fa-shopping-cart"></i>
                </button>
            </div>
        </td>
    `;
    
    return row;
}

function switchView(viewType) {
    const cardView = document.getElementById('cardView');
    const tableView = document.getElementById('tableView');
    const toggleBtns = document.querySelectorAll('.toggle-btn');
    
    toggleBtns.forEach(btn => btn.classList.remove('active'));
    document.querySelector(`[data-view="${viewType}"]`).classList.add('active');
    
    if (viewType === 'card') {
        cardView.style.display = 'block';
        tableView.style.display = 'none';
    } else {
        cardView.style.display = 'none';
        tableView.style.display = 'block';
    }
}


// Adjust Quantity Function
function adjustQuantity(ingredientId, ingredientName, currentQuantity, unit) {
    // Populate the modal with ingredient information
    document.getElementById('adjustIngredientName').textContent = ingredientName;
    document.getElementById('adjustCurrentQuantity').textContent = `${currentQuantity} ${unit}`;
    document.getElementById('adjustIngredientId').value = ingredientId;
    document.getElementById('adjustNewQuantity').value = currentQuantity;
    document.getElementById('adjustUnit').textContent = unit;
    
    // Show the modal
    const modal = new bootstrap.Modal(document.getElementById('adjustQuantityModal'));
    modal.show();
}

// Submit quantity adjustment
function submitQuantityAdjustment() {
    const ingredientId = document.getElementById('adjustIngredientId').value;
    const newQuantity = document.getElementById('adjustNewQuantity').value;
    const reason = document.getElementById('adjustReason').value;
    const notes = document.getElementById('adjustNotes').value;
    
    // Validate inputs
    if (newQuantity === '' || newQuantity === null || newQuantity < 0) {
        Swal.fire({
            icon: 'error',
            title: 'Invalid Quantity',
            text: 'Please enter a valid quantity (0 or greater).',
            confirmButtonColor: '#8B4543'
        });
        return;
    }
    
    if (!reason.trim()) {
        Swal.fire({
            icon: 'error',
            title: 'Reason Required',
            text: 'Please provide a reason for this quantity adjustment.',
            confirmButtonColor: '#8B4543'
        });
        return;
    }
    
    // Show loading state
    const submitBtn = document.getElementById('submitAdjustment');
    const originalText = submitBtn.innerHTML;
    submitBtn.innerHTML = '<i class="fas fa-spinner fa-spin me-2"></i>Updating...';
    submitBtn.disabled = true;
    
    // Submit the adjustment
    fetch('adjust_ingredient_quantity.php', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
        },
        body: JSON.stringify({
            ingredient_id: ingredientId,
            new_quantity: parseFloat(newQuantity),
            reason: reason,
            notes: notes
        })
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            Swal.fire({
                icon: 'success',
                title: 'Quantity Updated!',
                text: data.message,
                confirmButtonColor: '#8B4543'
            }).then(() => {
                // Close modal and refresh ingredients
                const modal = bootstrap.Modal.getInstance(document.getElementById('adjustQuantityModal'));
                modal.hide();
                loadAvailableIngredients();
            });
        } else {
            Swal.fire({
                icon: 'error',
                title: 'Update Failed',
                text: data.message || 'An error occurred while updating the quantity.',
                confirmButtonColor: '#8B4543'
            });
        }
    })
    .catch(error => {
        console.error('Error:', error);
        Swal.fire({
            icon: 'error',
            title: 'Network Error',
            text: 'An error occurred while updating the quantity.',
            confirmButtonColor: '#8B4543'
        });
    })
    .finally(() => {
        // Reset button
        submitBtn.innerHTML = originalText;
        submitBtn.disabled = false;
    });
}
</script>

<!-- Adjust Quantity Modal -->
<div class="modal fade" id="adjustQuantityModal" tabindex="-1" aria-labelledby="adjustQuantityModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header bg-maroon text-white">
                <h5 class="modal-title" id="adjustQuantityModalLabel">
                    <i class="fas fa-edit me-2"></i>Adjust Ingredient Quantity
                </h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <div class="row">
                    <div class="col-md-6">
                        <div class="mb-3">
                            <label class="form-label fw-bold">Ingredient Name</label>
                            <div class="form-control-plaintext" id="adjustIngredientName"></div>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="mb-3">
                            <label class="form-label fw-bold">Current Quantity</label>
                            <div class="form-control-plaintext" id="adjustCurrentQuantity"></div>
                        </div>
                    </div>
                </div>
                
                <div class="row">
                    <div class="col-md-6">
                        <div class="mb-3">
                            <label for="adjustNewQuantity" class="form-label fw-bold">New Quantity <span class="text-danger">*</span></label>
                            <div class="input-group">
                                <input type="number" class="form-control no-spinner" id="adjustNewQuantity" min="0" step="1" required
                                       oninput="this.value = Math.round(this.value)"
                                       onpaste="setTimeout(() => this.value = Math.round(parseFloat(this.value) || 0), 0)">
                                <span class="input-group-text" id="adjustUnit"></span>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="mb-3">
                            <label for="adjustReason" class="form-label fw-bold">Reason for Adjustment <span class="text-danger">*</span></label>
                            <select class="form-select" id="adjustReason" required>
                                <option value="">Select a reason...</option>
                                <option value="Stock Count Correction">Stock Count Correction</option>
                                <option value="Damaged/Lost Items">Damaged/Lost Items</option>
                                <option value="Expired Items">Expired Items</option>
                                <option value="Received New Stock">Received New Stock</option>
                                <option value="Used in Production">Used in Production</option>
                                <option value="Transfer to Other Branch">Transfer to Other Branch</option>
                                <option value="Theft/Loss">Theft/Loss</option>
                                <option value="Quality Control Rejection">Quality Control Rejection</option>
                                <option value="Other">Other</option>
                            </select>
                        </div>
                    </div>
                </div>
                
                <div class="mb-3">
                    <label for="adjustNotes" class="form-label fw-bold">Additional Notes</label>
                    <textarea class="form-control" id="adjustNotes" rows="3" placeholder="Provide additional details about this quantity adjustment..."></textarea>
                </div>
                
                <input type="hidden" id="adjustIngredientId">
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">
                    <i class="fas fa-times me-2"></i>Cancel
                </button>
                <button type="button" class="btn btn-maroon" id="submitAdjustment" onclick="submitQuantityAdjustment()">
                    <i class="fas fa-save me-2"></i>Update Quantity
                </button>
            </div>
        </div>
    </div>
</div>

<?php include('footer.php'); ?>

