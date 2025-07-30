<?php
require_once 'db_connect.php';
require_once 'auth_function.php';

// Check if user is logged in and is a stockman
if (!isset($_SESSION['user_logged_in']) || $_SESSION['user_logged_in'] !== true || $_SESSION['user_type'] !== 'Stockman') {
    header('Location: login.php');
    exit();
}

$branch_id = $_SESSION['branch_id'];
$pre_selected_ingredient = $_GET['ingredient_id'] ?? null;

// Fetch all active categories
$categories = $pdo->query("SELECT category_id, category_name FROM pos_category WHERE status = 'active' ORDER BY category_name")->fetchAll(PDO::FETCH_ASSOC);

// Fetch ingredients for this stockman's branch
if (!$branch_id) {
    $ingredients = [];
} else {
    if ($pre_selected_ingredient) {
        // If specific ingredient is selected, fetch only that ingredient
        $stmt = $pdo->prepare("SELECT i.ingredient_id, i.ingredient_name, i.ingredient_unit, i.ingredient_quantity, i.ingredient_status, i.category_id, c.category_name
            FROM ingredients i
            LEFT JOIN pos_category c ON i.category_id = c.category_id
            WHERE i.branch_id = ? AND i.ingredient_id = ?
            ORDER BY i.ingredient_name");
        $stmt->execute([$branch_id, $pre_selected_ingredient]);
        $ingredients = $stmt->fetchAll(PDO::FETCH_ASSOC);
    } else {
        // Fetch all ingredients
        $stmt = $pdo->prepare("SELECT i.ingredient_id, i.ingredient_name, i.ingredient_unit, i.ingredient_quantity, i.ingredient_status, i.category_id, c.category_name
            FROM ingredients i
            LEFT JOIN pos_category c ON i.category_id = c.category_id
            WHERE i.branch_id = ?
            ORDER BY c.category_name, i.ingredient_name");
        $stmt->execute([$branch_id]);
        $ingredients = $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
}
?>

<div class="modal-header bg-maroon text-white">
    <h5 class="modal-title">
        <i class="fas fa-clipboard-list me-2"></i>
        <?php if ($pre_selected_ingredient): ?>
            Request Stock - <?php echo htmlspecialchars($ingredients[0]['ingredient_name'] ?? 'Ingredient'); ?>
        <?php else: ?>
            Request Stock
        <?php endif; ?>
    </h5>
    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
</div>

<div class="modal-body">
    <form id="requestStockModalForm">
        <div class="mb-3">
            <label for="ingredients" class="form-label fw-medium">
                <?php if ($pre_selected_ingredient): ?>
                    Request Quantity for <?php echo htmlspecialchars($ingredients[0]['ingredient_name'] ?? 'Ingredient'); ?>
                <?php else: ?>
                    Select Ingredients
                <?php endif; ?>
            </label>
            <div id="ingredient-list">
                <?php if ($pre_selected_ingredient): ?>
                    <!-- Single ingredient view -->
                    <?php foreach ($ingredients as $ingredient): ?>
                        <div class="row mb-3 align-items-center ingredient-row<?php if ($ingredient['ingredient_status'] !== 'Available') echo ' unavailable'; ?>">
                            <div class="col-md-8">
                                <div class="ingredient-info">
                                    <h6 class="mb-1">
                                        <strong><?php echo htmlspecialchars($ingredient['ingredient_name']); ?></strong>
                                        <span class="text-muted">(<?php echo htmlspecialchars($ingredient['ingredient_unit']); ?>)</span>
                                    </h6>
                                    <p class="mb-2 text-muted">
                                        Category: <?php echo htmlspecialchars($ingredient['category_name']); ?>
                                    </p>
                                    <p class="mb-0">
                                        <span class="ingredient-status <?php echo ($ingredient['ingredient_status'] === 'Available') ? 'available' : 'unavailable'; ?>">
                                            <?php if ($ingredient['ingredient_status'] === 'Available') {
                                                echo 'Current Stock: ' . htmlspecialchars($ingredient['ingredient_quantity']);
                                            } else {
                                                echo 'Currently Unavailable';
                                            } ?>
                                        </span>
                                    </p>
                                </div>
                            </div>
                            <div class="col-md-4">
                                <label for="quantity_<?php echo $ingredient['ingredient_id']; ?>" class="form-label">Request Quantity</label>
                                <input type="number" class="form-control border-0 shadow-sm" 
                                       name="quantity[<?php echo $ingredient['ingredient_id']; ?>]" 
                                       id="quantity_<?php echo $ingredient['ingredient_id']; ?>"
                                       min="1" placeholder="Enter quantity" 
                                       <?php if ($ingredient['ingredient_status'] !== 'Available') echo 'disabled'; ?>>
                                <input type="hidden" name="ingredients[]" value="<?php echo $ingredient['ingredient_id']; ?>">
                            </div>
                        </div>
                    <?php endforeach; ?>
                <?php else: ?>
                    <!-- Multiple ingredients view -->
                    <?php foreach ($ingredients as $ingredient): ?>
                        <div class="row mb-2 align-items-center ingredient-row<?php if ($ingredient['ingredient_status'] !== 'Available') echo ' unavailable'; ?>">
                            <div class="col-md-6">
                                <input type="checkbox" name="ingredients[]" value="<?php echo $ingredient['ingredient_id']; ?>" id="ingredient_<?php echo $ingredient['ingredient_id']; ?>" <?php if ($ingredient['ingredient_status'] !== 'Available') echo 'disabled'; ?>>
                                <label for="ingredient_<?php echo $ingredient['ingredient_id']; ?>">
                                    <strong><?php echo htmlspecialchars($ingredient['ingredient_name']); ?></strong>
                                    <span class="text-muted">(<?php echo htmlspecialchars($ingredient['ingredient_unit']); ?>)</span>
                                    <span class="ingredient-status <?php echo ($ingredient['ingredient_status'] === 'Available') ? 'available' : 'unavailable'; ?>">
                                        <?php if ($ingredient['ingredient_status'] === 'Available') {
                                            echo 'Available: ' . htmlspecialchars($ingredient['ingredient_quantity']);
                                        } else {
                                            echo 'Unavailable';
                                        } ?>
                                    </span><br>
                                    <small>Category: <?php echo htmlspecialchars($ingredient['category_name']); ?></small>
                                </label>
                            </div>
                            <div class="col-md-4">
                                <input type="number" class="form-control border-0 shadow-sm" name="quantity[<?php echo $ingredient['ingredient_id']; ?>]" min="1" placeholder="Quantity" <?php if ($ingredient['ingredient_status'] !== 'Available') echo 'disabled'; ?>>
                            </div>
                        </div>
                    <?php endforeach; ?>
                <?php endif; ?>
            </div>
        </div>
        
        <div class="mb-3">
            <label for="notes" class="form-label fw-medium">Notes (optional)</label>
            <textarea name="notes" id="notes" class="form-control border-0 shadow-sm" rows="3" placeholder="Enter any additional notes..."></textarea>
        </div>
    </form>
</div>

<div class="modal-footer">
    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">
        <i class="fas fa-times me-1"></i>Cancel
    </button>
    <button type="button" class="btn btn-maroon" id="submitRequestBtn">
        <i class="fas fa-paper-plane me-1"></i>Submit Request
    </button>
</div>

<style>
.ingredient-row {
    border-bottom: 1px solid #f3e9e8;
    padding-bottom: 0.7rem;
    margin-bottom: 0.7rem;
    opacity: 1;
    transition: opacity 0.2s;
}
.ingredient-row.unavailable {
    opacity: 0.5;
    pointer-events: none;
}
.ingredient-status {
    font-size: 0.95em;
    font-weight: 500;
    margin-left: 0.5em;
}
.ingredient-status.available {
    color: #4B7F52;
}
.ingredient-status.unavailable {
    color: #dc3545;
}
</style>

<script>
$(document).ready(function() {
    <?php if ($pre_selected_ingredient): ?>
    // Single ingredient view - focus on quantity input
    $(document).ready(function() {
        const qtyInput = $('#quantity_<?php echo $pre_selected_ingredient; ?>');
        if (qtyInput.length > 0) {
            qtyInput.focus();
        }
    });
    <?php else: ?>
    // Multiple ingredients view - enable quantity input only if ingredient is checked
    $('#ingredient-list input[type="checkbox"]').change(function() {
        const qtyInput = $(this).closest('.row').find('input[type="number"]');
        qtyInput.prop('disabled', !this.checked);
        if (!this.checked) qtyInput.val('');
    });
    <?php endif; ?>

    // Handle form submission
    $('#submitRequestBtn').click(function() {
        const form = $('#requestStockModalForm');
        const formData = form.serialize();
        
        <?php if ($pre_selected_ingredient): ?>
        // Single ingredient validation
        const quantity = $('input[name="quantity[<?php echo $pre_selected_ingredient; ?>]"]').val();
        if (!quantity || quantity <= 0) {
            Swal.fire({
                icon: 'warning',
                title: 'No Quantity Entered',
                text: 'Please enter a quantity for the ingredient.',
                confirmButtonColor: '#8B4543'
            });
            return;
        }
        <?php else: ?>
        // Multiple ingredients validation
        const selectedIngredients = $('input[name="ingredients[]"]:checked');
        if (selectedIngredients.length === 0) {
            Swal.fire({
                icon: 'warning',
                title: 'No Ingredients Selected',
                text: 'Please select at least one ingredient to request.',
                confirmButtonColor: '#8B4543'
            });
            return;
        }

        // Validate quantities
        let hasQuantity = false;
        selectedIngredients.each(function() {
            const ingredientId = $(this).val();
            const quantity = $(`input[name="quantity[${ingredientId}]"]`).val();
            if (quantity && quantity > 0) {
                hasQuantity = true;
            }
        });

        if (!hasQuantity) {
            Swal.fire({
                icon: 'warning',
                title: 'No Quantities Entered',
                text: 'Please enter quantities for the selected ingredients.',
                confirmButtonColor: '#8B4543'
            });
            return;
        }
        <?php endif; ?>

        $.ajax({
            url: 'process_ingredient_request.php',
            type: 'POST',
            data: formData,
            success: function(response) {
                if (response.success) {
                    // Properly hide modal and remove backdrop
                    $('#requestStockModal').modal('hide');
                    $('body').removeClass('modal-open');
                    $('.modal-backdrop').remove();
                    
                    Swal.fire({
                        icon: 'success',
                        title: 'Request Submitted!',
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
                    text: 'An error occurred while submitting your request.',
                    confirmButtonColor: '#8B4543'
                });
            }
        });
    });
    
    // Add modal hidden event handler for proper cleanup
    $('#requestStockModal').on('hidden.bs.modal', function () {
        $('body').removeClass('modal-open');
        $('.modal-backdrop').remove();
    });
});
</script> 