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

<div class="modal-header bg-maroon text-white">
    <h5 class="modal-title">
        <i class="fas fa-edit me-2"></i>Adjust Stock: <?php echo htmlspecialchars($ingredient['ingredient_name']); ?>
    </h5>
    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
</div>

<div class="modal-body">
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
        
        <hr>
        
        <div class="row">
            <div class="col-md-6">
                <div class="mb-3">
                    <label for="adjustment_type" class="form-label fw-medium">Adjustment Type</label>
                    <select name="adjustment_type" id="adjustment_type" class="form-select border-0 shadow-sm" required>
                        <option value="">Select Type</option>
                        <option value="add">Add Stock</option>
                        <option value="subtract">Subtract Stock</option>
                        <option value="set">Set New Quantity</option>
                    </select>
                </div>
            </div>
            <div class="col-md-6">
                <div class="mb-3">
                    <label for="adjustment_quantity" class="form-label fw-medium">Quantity</label>
                    <input type="number" name="adjustment_quantity" id="adjustment_quantity" class="form-control border-0 shadow-sm" min="0" step="0.01" required>
                </div>
            </div>
        </div>
        
        <div class="mb-3">
            <label for="adjustment_reason" class="form-label fw-medium">Reason for Adjustment</label>
            <textarea name="adjustment_reason" id="adjustment_reason" class="form-control border-0 shadow-sm" rows="3" placeholder="Enter reason for stock adjustment..." required></textarea>
        </div>
        
        <div class="alert alert-info">
            <i class="fas fa-info-circle me-2"></i>
            <strong>Note:</strong> Stock adjustments will be logged and can be reviewed by administrators.
        </div>
    </form>
</div>

<div class="modal-footer">
    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">
        <i class="fas fa-times me-1"></i>Cancel
    </button>
    <button type="button" class="btn btn-maroon" id="saveAdjustmentBtn">
        <i class="fas fa-save me-1"></i>Save Changes
    </button>
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
                    $('#adjustStockModal').modal('hide');
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
});
</script> 