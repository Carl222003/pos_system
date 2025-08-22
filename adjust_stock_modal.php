<?php
require_once 'db_connect.php';
require_once 'auth_function.php';

// Check if user is logged in and is a stockman
if (!isset($_SESSION['user_logged_in']) || $_SESSION['user_logged_in'] !== true || $_SESSION['user_type'] !== 'Stockman') {
    header('Location: login.php');
    exit();
}

$ingredient_id = $_GET['id'] ?? null;
$branch_id = $_SESSION['branch_id'];

if (!$ingredient_id) {
    echo '<div class="alert alert-danger">Invalid ingredient ID</div>';
    exit();
}

// Get ingredient details for this stockman's branch
$stmt = $pdo->prepare("
    SELECT i.ingredient_id, i.ingredient_name, i.ingredient_quantity, i.ingredient_unit, 
           i.ingredient_status, i.category_id, c.category_name, i.date_added, i.consume_before, i.notes
    FROM ingredients i
    LEFT JOIN pos_category c ON i.category_id = c.category_id
    WHERE i.ingredient_id = ? AND i.branch_id = ?
");
$stmt->execute([$ingredient_id, $branch_id]);
$ingredient = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$ingredient) {
    echo '<div class="alert alert-danger">Ingredient not found or not accessible</div>';
    exit();
}
?>

<div class="enhanced-adjust-header">
    <div class="header-background-pattern"></div>
    <div class="header-content-adjust">
        <div class="header-left-adjust">
            <div class="adjust-icon-container">
                <div class="adjust-icon">
                    <i class="fas fa-sliders-h"></i>
                </div>
                <div class="icon-glow"></div>
            </div>
            <div class="header-text-adjust">
                <h4 class="adjust-title">Stock Adjustment</h4>
                <p class="adjust-subtitle">
                    <i class="fas fa-cube me-1"></i>
                    <?php echo htmlspecialchars($ingredient['ingredient_name']); ?>
                </p>
            </div>
        </div>
        <div class="header-right-adjust">
            <button type="button" class="btn-close-enhanced" data-bs-dismiss="modal" aria-label="Close">
                <i class="fas fa-times"></i>
            </button>
        </div>
    </div>
</div>

<div class="enhanced-adjust-body">
    <form id="adjustStockForm">
        <input type="hidden" name="ingredient_id" value="<?php echo $ingredient['ingredient_id']; ?>">
        
        <div class="row">
            <div class="col-md-6">
                <div class="mb-3">
                    <label class="form-label fw-medium">Ingredient Name</label>
                    <input type="text" class="form-control border-0 shadow-sm" value="<?php echo htmlspecialchars($ingredient['ingredient_name']); ?>" readonly>
                </div>
                <div class="mb-3">
                    <label class="form-label fw-medium">Category</label>
                    <input type="text" class="form-control border-0 shadow-sm" value="<?php echo htmlspecialchars($ingredient['category_name']); ?>" readonly>
                </div>
                <div class="mb-3">
                    <label class="form-label fw-medium">Current Stock</label>
                    <input type="text" class="form-control border-0 shadow-sm" value="<?php echo $ingredient['ingredient_quantity'] . ' ' . $ingredient['ingredient_unit']; ?>" readonly>
                </div>
            </div>
            <div class="col-md-6">
                <div class="mb-3">
                    <label class="form-label fw-medium">Unit</label>
                    <input type="text" class="form-control border-0 shadow-sm" value="<?php echo htmlspecialchars($ingredient['ingredient_unit']); ?>" readonly>
                </div>
                <div class="mb-3">
                    <label for="ingredient_status" class="form-label fw-medium">Status</label>
                    <select name="ingredient_status" id="ingredient_status" class="form-select border-0 shadow-sm">
                        <option value="Available" <?php echo ($ingredient['ingredient_status'] === 'Available') ? 'selected' : ''; ?>>Available</option>
                        <option value="Unavailable" <?php echo ($ingredient['ingredient_status'] === 'Unavailable') ? 'selected' : ''; ?>>Unavailable</option>
                    </select>
                </div>
                <div class="mb-3">
                    <label class="form-label fw-medium">Date Added</label>
                    <input type="text" class="form-control border-0 shadow-sm" value="<?php echo $ingredient['date_added']; ?>" readonly>
                </div>
            </div>
        </div>
        
        <!-- Adjustment Controls Card -->
        <div class="adjustment-controls-card">
            <div class="card-header-enhanced">
                <div class="header-icon-info">
                    <i class="fas fa-sliders-h"></i>
                </div>
                <h5 class="card-title-enhanced">Stock Adjustment</h5>
            </div>
            <div class="card-body-enhanced">
                <div class="adjustment-grid">
                    <div class="adjustment-group">
                        <label for="adjustment_type" class="enhanced-label">
                            <i class="fas fa-cog"></i>
                            Adjustment Type
                        </label>
                        <div class="enhanced-select-wrapper">
                            <select name="adjustment_type" id="adjustment_type" class="enhanced-select adjustment-type-select" required>
                                <option value="">🔧 Select Adjustment Type</option>
                                <option value="add">➕ Add Stock</option>
                                <option value="subtract">➖ Subtract Stock</option>
                                <option value="set">🎯 Set New Quantity</option>
                            </select>
                        </div>
                    </div>
                    
                    <div class="adjustment-group">
                        <label for="adjustment_quantity" class="enhanced-label">
                            <i class="fas fa-calculator"></i>
                            Quantity
                        </label>
                        <div class="quantity-input-enhanced">
                            <input type="number" name="adjustment_quantity" id="adjustment_quantity" 
                                   class="enhanced-number-input" min="0" step="0.01" 
                                   placeholder="Enter quantity" required>
                            <span class="quantity-unit-display"><?php echo htmlspecialchars($ingredient['ingredient_unit']); ?></span>
                        </div>
                    </div>
                </div>
                
                <div class="reason-section">
                    <label for="adjustment_reason" class="enhanced-label">
                        <i class="fas fa-comment-alt"></i>
                        Reason for Adjustment
                    </label>
                    <textarea name="adjustment_reason" id="adjustment_reason" 
                              class="enhanced-textarea" rows="3" 
                              placeholder="Provide a detailed reason for this stock adjustment..." required></textarea>
                </div>
                
                <div class="info-notice-enhanced">
                    <div class="notice-icon">
                        <i class="fas fa-info-circle"></i>
                    </div>
                    <div class="notice-content">
                        <strong>Important:</strong> All stock adjustments are logged and tracked for audit purposes. 
                        This action will be reviewed by administrators.
                    </div>
                </div>
            </div>
        </div>
    </form>
</div>

<div class="enhanced-adjust-footer">
    <div class="footer-actions-enhanced">
        <button type="button" class="enhanced-btn enhanced-btn-cancel" data-bs-dismiss="modal">
            <i class="fas fa-times me-2"></i>
            Cancel
        </button>
        <button type="button" class="enhanced-btn enhanced-btn-save" id="saveAdjustmentBtn">
            <i class="fas fa-save me-2"></i>
            Save Changes
        </button>
    </div>
</div>

<script>
$(document).ready(function() {
    // Handle adjustment type change
    $('#adjustment_type').change(function() {
        const type = $(this).val();
        const currentStock = <?php echo $ingredient['ingredient_quantity']; ?>;
        
        if (type === 'subtract') {
            $('#adjustment_quantity').attr('max', currentStock);
            $('#adjustment_quantity').attr('placeholder', `Max: ${currentStock} ${<?php echo json_encode($ingredient['ingredient_unit']); ?>}`);
        } else if (type === 'set') {
            $('#adjustment_quantity').removeAttr('max');
            $('#adjustment_quantity').attr('placeholder', 'Enter new total quantity');
        } else {
            $('#adjustment_quantity').removeAttr('max');
            $('#adjustment_quantity').attr('placeholder', 'Enter quantity to add');
        }
    });
    
    // Handle form submission
    $('#saveAdjustmentBtn').click(function() {
        const form = $('#adjustStockForm');
        const formData = form.serialize();
        
        $.ajax({
            url: 'process_stock_adjustment.php',
            type: 'POST',
            data: formData,
            success: function(response) {
                if (response.success) {
                    // Properly hide modal and remove backdrop
                    $('#adjustStockModal').modal('hide');
                    $('body').removeClass('modal-open');
                    $('.modal-backdrop').remove();
                    
                    Swal.fire({
                        icon: 'success',
                        title: 'Stock Adjusted!',
                        text: response.message,
                        confirmButtonColor: '#8B4543'
                    });
                    // Refresh dashboard
                    updateDashboard();
                } else {
                    Swal.fire({
                        icon: 'error',
                        title: 'Error!',
                        text: response.message,
                        confirmButtonColor: '#8B4543'
                    });
                }
            },
            error: function() {
                Swal.fire({
                    icon: 'error',
                    title: 'Error!',
                    text: 'An error occurred while processing your request.',
                    confirmButtonColor: '#8B4543'
                });
            }
        });
    });
    
    // Add modal hidden event handler for proper cleanup
    $('#adjustStockModal').on('hidden.bs.modal', function () {
        $('body').removeClass('modal-open');
        $('.modal-backdrop').remove();
    });
});
</script>

<style>
/* Enhanced Adjust Stock Modal Styling */
.enhanced-adjust-header {
    background: linear-gradient(135deg, #8B4543 0%, #A65D5D 50%, #8B4543 100%);
    color: white;
    padding: 2rem;
    position: relative;
    overflow: hidden;
    border-radius: 20px 20px 0 0;
    margin: 0;
    border: none;
}

.header-background-pattern {
    position: absolute;
    top: 0;
    left: 0;
    right: 0;
    bottom: 0;
    background: 
        radial-gradient(circle at 20% 50%, rgba(255,255,255,0.1) 0%, transparent 50%),
        radial-gradient(circle at 80% 20%, rgba(255,255,255,0.08) 0%, transparent 50%),
        linear-gradient(45deg, rgba(255,255,255,0.05) 0%, transparent 50%);
    animation: patternMove 6s ease-in-out infinite;
}

@keyframes patternMove {
    0%, 100% { transform: translateX(0) translateY(0); }
    50% { transform: translateX(10px) translateY(-10px); }
}

.header-content-adjust {
    display: flex;
    justify-content: space-between;
    align-items: center;
    position: relative;
    z-index: 2;
}

.header-left-adjust {
    display: flex;
    align-items: center;
    gap: 1.25rem;
}

.adjust-icon-container {
    position: relative;
    display: flex;
    align-items: center;
    justify-content: center;
}

.adjust-icon {
    width: 60px;
    height: 60px;
    background: rgba(255, 255, 255, 0.25);
    border-radius: 16px;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 1.8rem;
    color: white;
    backdrop-filter: blur(15px);
    box-shadow: 0 8px 25px rgba(0, 0, 0, 0.2);
    border: 1px solid rgba(255, 255, 255, 0.3);
    transition: all 0.4s cubic-bezier(0.4, 0, 0.2, 1);
    position: relative;
    z-index: 2;
}

.icon-glow {
    position: absolute;
    width: 60px;
    height: 60px;
    border-radius: 16px;
    background: rgba(255, 255, 255, 0.1);
    animation: iconGlow 3s ease-in-out infinite;
}

@keyframes iconGlow {
    0%, 100% { transform: scale(1); opacity: 0.5; }
    50% { transform: scale(1.2); opacity: 0.2; }
}

.adjust-title {
    font-size: 1.6rem;
    font-weight: 800;
    margin: 0;
    color: white;
    text-shadow: 0 2px 4px rgba(0, 0, 0, 0.3);
}

.adjust-subtitle {
    font-size: 1rem;
    margin: 0;
    color: rgba(255, 255, 255, 0.9);
    text-shadow: 0 1px 2px rgba(0, 0, 0, 0.2);
    display: flex;
    align-items: center;
}

.btn-close-enhanced {
    background: rgba(255, 255, 255, 0.2);
    border: none;
    color: white;
    width: 45px;
    height: 45px;
    border-radius: 12px;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 1.2rem;
    transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
    backdrop-filter: blur(10px);
    border: 1px solid rgba(255, 255, 255, 0.3);
}

.btn-close-enhanced:hover {
    background: rgba(255, 255, 255, 0.35);
    transform: scale(1.1) rotate(90deg);
    box-shadow: 0 4px 15px rgba(0, 0, 0, 0.3);
}

.enhanced-adjust-body {
    padding: 2rem;
    background: linear-gradient(135deg, #ffffff 0%, #f8f9fa 100%);
}

.info-overview-card {
    background: white;
    border-radius: 16px;
    box-shadow: 0 8px 30px rgba(139, 69, 67, 0.1);
    border: 1px solid rgba(139, 69, 67, 0.1);
    transition: all 0.3s ease;
    margin-bottom: 2rem;
    overflow: hidden;
}

.info-overview-card:hover {
    transform: translateY(-2px);
    box-shadow: 0 12px 40px rgba(139, 69, 67, 0.15);
}

.card-header-enhanced {
    background: linear-gradient(135deg, #8B4543, #A65D5D);
    color: white;
    padding: 1rem 1.5rem;
    display: flex;
    align-items: center;
    gap: 0.75rem;
    border-bottom: 1px solid rgba(255, 255, 255, 0.1);
}

.header-icon-info {
    width: 35px;
    height: 35px;
    background: rgba(255, 255, 255, 0.2);
    border-radius: 10px;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 1rem;
    color: white;
}

.card-title-enhanced {
    margin: 0;
    font-weight: 700;
    font-size: 1.1rem;
}

.card-body-enhanced {
    padding: 1.5rem;
}

.info-grid-enhanced {
    display: flex;
    flex-direction: column;
    gap: 1.25rem;
}

.info-row-enhanced {
    display: grid;
    grid-template-columns: 1fr 1fr;
    gap: 1.5rem;
}

.info-row-enhanced.highlight-row-enhanced {
    background: linear-gradient(135deg, rgba(139, 69, 67, 0.05), rgba(139, 69, 67, 0.02));
    border-radius: 12px;
    padding: 1rem;
    border: 2px solid rgba(139, 69, 67, 0.1);
    margin: 0.5rem 0;
}

.info-group {
    display: flex;
    flex-direction: column;
    gap: 0.5rem;
}

.info-group.full-width {
    grid-column: 1 / -1;
}

.enhanced-label {
    color: #495057;
    font-weight: 600;
    font-size: 0.9rem;
    display: flex;
    align-items: center;
    gap: 0.5rem;
    margin-bottom: 0.5rem;
}

.enhanced-label i {
    color: #8B4543;
    width: 16px;
}

.enhanced-display-field {
    background: #f8f9fa;
    border: 1px solid #e9ecef;
    border-radius: 8px;
    padding: 0.75rem 1rem;
    color: #2c3e50;
    font-weight: 600;
    transition: all 0.3s ease;
}

.enhanced-display-field:hover {
    background: #e9ecef;
    border-color: #8B4543;
}

.unit-badge {
    background: linear-gradient(135deg, #6c757d, #5a6268) !important;
    color: white !important;
    text-align: center;
    font-weight: 700;
}

.category-badge-enhanced {
    background: linear-gradient(135deg, #8B4543, #A65D5D);
    color: white;
    padding: 0.5rem 1rem;
    border-radius: 20px;
    font-weight: 600;
    font-size: 0.9rem;
    display: inline-block;
}

.current-stock-display {
    background: linear-gradient(135deg, #007bff, #0056b3);
    color: white;
    padding: 0.75rem 1.5rem;
    border-radius: 25px;
    font-weight: 700;
    font-size: 1.2rem;
    display: inline-block;
    box-shadow: 0 4px 15px rgba(0, 123, 255, 0.3);
}

.stock-unit {
    opacity: 0.9;
    font-weight: 500;
    margin-left: 0.5rem;
}

.enhanced-select-wrapper {
    position: relative;
}

.enhanced-select {
    background: white;
    border: 2px solid #e9ecef;
    border-radius: 8px;
    padding: 0.75rem 1rem;
    font-weight: 600;
    color: #2c3e50;
    transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
    width: 100%;
    cursor: pointer;
}

.enhanced-select:hover {
    border-color: #8B4543;
    box-shadow: 0 0 0 0.1rem rgba(139, 69, 67, 0.15);
}

.enhanced-select:focus {
    border-color: #8B4543;
    box-shadow: 0 0 0 0.2rem rgba(139, 69, 67, 0.25);
    outline: none;
}

.status-select option[value="Available"] {
    background: #d4edda;
    color: #155724;
}

.status-select option[value="Low Stock"] {
    background: #fff3cd;
    color: #856404;
}

.status-select option[value="Out of Stock"] {
    background: #f8d7da;
    color: #721c24;
}

.adjustment-controls-card {
    background: white;
    border-radius: 16px;
    box-shadow: 0 8px 30px rgba(139, 69, 67, 0.1);
    border: 1px solid rgba(139, 69, 67, 0.1);
    transition: all 0.3s ease;
    overflow: hidden;
    margin-top: 1.5rem;
}

.adjustment-controls-card:hover {
    transform: translateY(-2px);
    box-shadow: 0 12px 40px rgba(139, 69, 67, 0.15);
}

.adjustment-grid {
    display: grid;
    grid-template-columns: 1fr 1fr;
    gap: 2rem;
    margin-bottom: 1.5rem;
}

.adjustment-group {
    display: flex;
    flex-direction: column;
    gap: 0.75rem;
}

.adjustment-type-select {
    background: white;
    border: 2px solid #e9ecef;
    border-radius: 12px;
    padding: 1rem;
    font-weight: 600;
    color: #2c3e50;
    transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
    cursor: pointer;
}

.adjustment-type-select:hover {
    border-color: #8B4543;
    box-shadow: 0 0 0 0.1rem rgba(139, 69, 67, 0.15);
    transform: translateY(-1px);
}

.adjustment-type-select:focus {
    border-color: #8B4543;
    box-shadow: 0 0 0 0.2rem rgba(139, 69, 67, 0.25);
    outline: none;
    transform: translateY(-1px);
}

.quantity-input-enhanced {
    position: relative;
    display: flex;
    align-items: center;
}

.enhanced-number-input {
    flex: 1;
    border: 2px solid #e9ecef;
    border-radius: 12px;
    padding: 1rem 5rem 1rem 1rem;
    font-size: 1.1rem;
    font-weight: 600;
    text-align: center;
    transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
    background: white;
    color: #2c3e50;
}

.enhanced-number-input:focus {
    border-color: #8B4543;
    box-shadow: 0 0 0 0.2rem rgba(139, 69, 67, 0.25);
    outline: none;
    transform: scale(1.02);
}

.enhanced-number-input:hover {
    border-color: #8B4543;
    transform: translateY(-1px);
}

.quantity-unit-display {
    position: absolute;
    right: 1rem;
    background: #8B4543;
    color: white;
    padding: 0.5rem 1rem;
    border-radius: 8px;
    font-weight: 600;
    font-size: 0.9rem;
    pointer-events: none;
}

.reason-section {
    margin-top: 1.5rem;
}

.enhanced-textarea {
    width: 100%;
    border: 2px solid #e9ecef;
    border-radius: 12px;
    padding: 1rem;
    font-size: 1rem;
    transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
    resize: vertical;
    background: white;
    color: #495057;
    line-height: 1.5;
    min-height: 100px;
}

.enhanced-textarea:focus {
    border-color: #8B4543;
    box-shadow: 0 0 0 0.2rem rgba(139, 69, 67, 0.25);
    outline: none;
    transform: translateY(-1px);
}

.enhanced-textarea:hover {
    border-color: #8B4543;
}

.info-notice-enhanced {
    background: linear-gradient(135deg, #d1ecf1, #bee5eb);
    border: 1px solid #b6e0e8;
    border-radius: 12px;
    padding: 1rem;
    margin-top: 1.5rem;
    display: flex;
    align-items: flex-start;
    gap: 1rem;
    transition: all 0.3s ease;
}

.info-notice-enhanced:hover {
    background: linear-gradient(135deg, #bee5eb, #abdde5);
    transform: translateY(-1px);
}

.notice-icon {
    width: 40px;
    height: 40px;
    background: #17a2b8;
    color: white;
    border-radius: 50%;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 1.1rem;
    flex-shrink: 0;
}

.notice-content {
    color: #0c5460;
    font-size: 0.95rem;
    line-height: 1.4;
}

.enhanced-adjust-footer {
    background: linear-gradient(135deg, #ffffff 0%, #f8f9fa 100%);
    border: none;
    padding: 1.5rem 2rem;
    border-radius: 0 0 20px 20px;
    margin: 0;
}

.footer-actions-enhanced {
    display: flex;
    justify-content: flex-end;
    gap: 1rem;
}

.enhanced-btn {
    padding: 0.75rem 2rem;
    border-radius: 12px;
    font-weight: 700;
    font-size: 1rem;
    transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
    border: none;
    display: flex;
    align-items: center;
    gap: 0.5rem;
    position: relative;
    overflow: hidden;
}

.enhanced-btn::before {
    content: '';
    position: absolute;
    top: 0;
    left: -100%;
    width: 100%;
    height: 100%;
    background: linear-gradient(90deg, transparent, rgba(255,255,255,0.2), transparent);
    transition: left 0.6s;
}

.enhanced-btn:hover::before {
    left: 100%;
}

.enhanced-btn-cancel {
    background: linear-gradient(135deg, #6c757d, #5a6268);
    color: white;
    box-shadow: 0 4px 12px rgba(108, 117, 125, 0.3);
}

.enhanced-btn-cancel:hover {
    background: linear-gradient(135deg, #5a6268, #4e555b);
    transform: translateY(-2px);
    box-shadow: 0 6px 20px rgba(108, 117, 125, 0.4);
}

.enhanced-btn-save {
    background: linear-gradient(135deg, #8B4543, #A65D5D);
    color: white;
    box-shadow: 0 4px 12px rgba(139, 69, 67, 0.3);
}

.enhanced-btn-save:hover {
    background: linear-gradient(135deg, #723836, #8B4543);
    transform: translateY(-2px);
    box-shadow: 0 6px 20px rgba(139, 69, 67, 0.4);
}

/* Responsive Design */
@media (max-width: 768px) {
    .adjustment-grid {
        grid-template-columns: 1fr;
        gap: 1.5rem;
    }
    
    .enhanced-adjust-body {
        padding: 1.5rem;
    }
    
    .footer-actions-enhanced {
        flex-direction: column;
        gap: 0.75rem;
    }
    
    .enhanced-btn {
        width: 100%;
        justify-content: center;
    }
}
</style> 