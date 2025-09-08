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

// Fetch all active categories (show even if no ingredients)
$categories = $pdo->query("SELECT category_id, category_name FROM pos_category WHERE status = 'active' ORDER BY category_name")->fetchAll(PDO::FETCH_ASSOC);

// Get stockman's branch information for display purposes
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

// Fetch ingredients that are in stock (quantity > 0) from main ingredients table
// Show all available ingredients from main inventory (not branch-specific)
$stmt = $pdo->prepare("
    SELECT 
        i.ingredient_id, 
        i.ingredient_name, 
        i.ingredient_unit, 
        i.ingredient_quantity, 
        i.ingredient_status, 
        i.category_id, 
        c.category_name,
        'Main Inventory' as availability_status,
        'Main Branch' as branch_name
    FROM ingredients i
    LEFT JOIN pos_category c ON i.category_id = c.category_id
    WHERE c.status = 'active' 
    AND i.ingredient_quantity > 0
    AND i.ingredient_status = 'Available'
    AND (i.consume_before IS NULL OR i.consume_before > CURDATE())
    ORDER BY c.category_name, i.ingredient_name
");

$stmt->execute();
$ingredients = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Group ingredients by category for better organization
$ingredients_by_category = [];
foreach ($ingredients as $ingredient) {
    $category_id = $ingredient['category_id'];
    if (!isset($ingredients_by_category[$category_id])) {
        $ingredients_by_category[$category_id] = [];
    }
    $ingredients_by_category[$category_id][] = $ingredient;
}
?>
<style>
.stockman-card {
    background: #fff;
    border-radius: 1.1rem;
    box-shadow: 0 2px 12px rgba(139, 69, 67, 0.07);
    padding: 2rem 2.5rem 2rem 2.5rem;
    margin-bottom: 2.5rem;
    border: 1.5px solid #e5d6d6;
    max-width: 700px;
    margin-left: auto;
    margin-right: auto;
}
.stockman-header {
    color: #8B4543;
    font-size: 2.2rem;
    font-weight: 700;
    letter-spacing: 0.7px;
    margin-bottom: 1.7rem;
    margin-top: 1.2rem;
    display: flex;
    align-items: center;
    gap: 0.7rem;
    position: relative;
    background: none;
    border: none;
    animation: fadeInDown 0.7s;
}
.stockman-header .log-icon {
    font-size: 1.5em;
    color: #8B4543;
    opacity: 0.92;
}
.stockman-header::after {
    content: '';
    display: block;
    position: absolute;
    left: 0;
    bottom: -7px;
    width: 100%;
    height: 5px;
    border-radius: 3px;
    background: linear-gradient(90deg, #8B4543 0%, #b97a6a 100%);
    opacity: 0.18;
}
.stockman-card label.form-label {
    color: #8B4543;
    font-weight: 600;
}
.stockman-card .form-control, .stockman-card .form-select {
    border-radius: 0.5rem;
    border: 1px solid #C4B1B1;
    font-size: 1rem;
    padding: 0.6rem 1rem;
}
.stockman-card .form-control:focus, .stockman-card .form-select:focus {
    border-color: #8B4543;
    box-shadow: 0 0 0 0.2rem rgba(139, 69, 67, 0.15);
}
.stockman-card .btn-primary {
    background: #8B4543;
    border: none;
    border-radius: 0.7rem;
    font-weight: 600;
    padding: 0.7rem 2.2rem;
    font-size: 1.1rem;
    margin-top: 1.2rem;
    transition: background 0.18s, box-shadow 0.18s;
}
.stockman-card .btn-primary:hover {
    background: #723836;
    box-shadow: 0 2px 8px rgba(139, 69, 67, 0.10);
}
.stockman-card .ingredient-row {
    border-bottom: 1px solid #f3e9e8;
    padding-bottom: 0.7rem;
    margin-bottom: 0.7rem;
    opacity: 1;
    transition: opacity 0.2s;
}

/* SweetAlert Custom Styling */
.swal2-confirm-success {
    background-color: #8B4543 !important;
    border-color: #8B4543 !important;
    color: white !important;
    border-radius: 0.7rem !important;
    font-weight: 600 !important;
    padding: 0.7rem 2.2rem !important;
    font-size: 1.1rem !important;
    transition: background 0.18s, box-shadow 0.18s !important;
}

.swal2-confirm-success:hover {
    background-color: #723836 !important;
    box-shadow: 0 2px 8px rgba(139, 69, 67, 0.10) !important;
}

.swal2-confirm-error {
    background-color: #dc3545 !important;
    border-color: #dc3545 !important;
    color: white !important;
    border-radius: 0.7rem !important;
    font-weight: 600 !important;
    padding: 0.7rem 2.2rem !important;
    font-size: 1.1rem !important;
    transition: background 0.18s, box-shadow 0.18s !important;
}

.swal2-confirm-error:hover {
    background-color: #c82333 !important;
    box-shadow: 0 2px 8px rgba(220, 53, 69, 0.10) !important;
}

.swal2-confirm-warning {
    background-color: #ffc107 !important;
    border-color: #ffc107 !important;
    color: #212529 !important;
    border-radius: 0.7rem !important;
    font-weight: 600 !important;
    padding: 0.7rem 2.2rem !important;
    font-size: 1.1rem !important;
    transition: background 0.18s, box-shadow 0.18s !important;
}

.swal2-confirm-warning:hover {
    background-color: #e0a800 !important;
    box-shadow: 0 2px 8px rgba(255, 193, 7, 0.10) !important;
}

.swal2-popup {
    border-radius: 1.1rem !important;
    font-family: 'Inter', sans-serif !important;
}

.swal2-title {
    color: #8B4543 !important;
    font-weight: 700 !important;
}

.swal2-content {
    color: #566a7f !important;
    font-size: 1rem !important;
}

/* Ensure proper page layout */
.container-fluid {
    width: 100%;
    padding-right: 20px;
    padding-left: 20px;
    margin-right: auto;
    margin-left: auto;
}

/* Fix any potential overflow issues */
.stockman-card {
    overflow: visible;
}

/* Ensure ingredients list is properly displayed */
#ingredient-list {
    max-height: 400px;
    overflow-y: auto;
    border: 1px solid #e5d6d6;
    border-radius: 0.5rem;
    padding: 1rem;
    background: #fafafa;
}

/* Better spacing for ingredient rows */
.ingredient-row {
    margin-bottom: 1rem !important;
    padding: 0.5rem;
    background: white;
    border-radius: 0.3rem;
    border: 1px solid #f0f0f0;
}

.ingredient-row:last-child {
    margin-bottom: 0 !important;
}






/* Ensure form elements are properly sized */
.form-control, .form-select {
    width: 100%;
}

/* Add some breathing room */
.mb-3 {
    margin-bottom: 1.5rem !important;
}

/* Invalid input styling */
.form-control.is-invalid {
    border-color: #dc3545;
    box-shadow: 0 0 0 0.2rem rgba(220, 53, 69, 0.25);
}

.form-control.is-invalid:focus {
    border-color: #dc3545;
    box-shadow: 0 0 0 0.2rem rgba(220, 53, 69, 0.25);
}
</style>
<div class="container-fluid px-4">
    <div class="stockman-header">
        <span class="log-icon"><i class="fas fa-clipboard-list"></i></span>
        Request Stock
    </div>
    <div class="stockman-card">
        <form action="process_ingredient_request.php" method="POST" id="requestStockForm">
            <div class="mb-3">
                <label for="categorySelect" class="form-label">Select Category</label>
                <select id="categorySelect" class="form-select" required>
                    <option value="">-- Select Category --</option>
                    <option value="all">📋 Show All Ingredients</option>
                    <?php foreach ($categories as $cat): ?>
                        <option value="<?php echo $cat['category_id']; ?>"><?php echo htmlspecialchars($cat['category_name']); ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="mb-3">
                <div class="mb-2">
                    <label for="ingredients" class="form-label mb-0">Select Ingredients</label>
                </div>
                <small class="form-text text-muted d-block mb-2">
                    <i class="fas fa-info-circle me-1"></i>
                    Select ingredients and enter desired quantities. The main branch will review and approve your request.
                </small>
                <div id="ingredient-list">
                    <?php if (empty($ingredients)): ?>
                        <div class="alert alert-info text-center">
                            <i class="fas fa-info-circle me-2"></i>
                            No ingredients are currently available in your branch. Please contact the administrator to add ingredients to your branch inventory.
                        </div>
                    <?php else: ?>
                        <?php foreach ($ingredients as $ingredient): ?>
                            <div class="row mb-2 align-items-center ingredient-row ingredient-cat-<?php echo $ingredient['category_id']; ?>" style="display:none;">
                                <div class="col-md-5">
                                    <input type="checkbox" name="ingredients[]" value="<?php echo $ingredient['ingredient_id']; ?>" id="ingredient_<?php echo $ingredient['ingredient_id']; ?>">
                                    <label for="ingredient_<?php echo $ingredient['ingredient_id']; ?>">
                                        <strong class="ingredient-name"><?php echo htmlspecialchars($ingredient['ingredient_name']); ?></strong>
                                        <span class="text-muted">(<?php echo htmlspecialchars($ingredient['ingredient_unit']); ?>)</span>
                                        <br>
                                        <small class="text-muted">Category: <?php echo htmlspecialchars($ingredient['category_name']); ?></small>
                                    </label>
                                </div>
                                <div class="col-md-7">
                                    <input type="number" class="form-control" name="quantity[<?php echo $ingredient['ingredient_id']; ?>]" min="1" placeholder="Qty">
                                </div>
                            </div>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </div>
            </div>
            

            <div class="mb-3">
                <label for="notes" class="form-label">Notes (optional)</label>
                <textarea name="notes" id="notes" class="form-control" rows="2"></textarea>
            </div>
            <button type="submit" class="btn btn-primary">Submit Request</button>
        </form>
    </div>
</div>
<script>
// Enable quantity input only if ingredient is checked
$(document).ready(function() {
    $('#ingredient-list input[type="checkbox"]').change(function() {
        const qtyInput = $(this).closest('.row').find('input[type="number"]');
        // Always keep quantity inputs enabled to ensure they're included in form submission
        // qtyInput.prop('disabled', !this.checked);
        if (!this.checked) {
            qtyInput.val('');
            qtyInput.attr('readonly', true);
        } else {
            qtyInput.attr('readonly', false);
            qtyInput.focus();
        }
    });

    // Basic validation for quantity inputs (just ensure positive numbers)
    $('#ingredient-list input[type="number"]').on('input', function() {
        const currentValue = parseInt($(this).val()) || 0;
        
        if (currentValue < 1) {
            $(this).addClass('is-invalid');
            $(this).attr('title', 'Please enter a quantity of at least 1');
        } else {
            $(this).removeClass('is-invalid');
            $(this).removeAttr('title');
        }
    });

    // Category filter logic
    $('#categorySelect').on('change', function() {
        var catId = $(this).val();
        $('#ingredient-list .ingredient-row').hide();
        if (catId === 'all') {
            // Show all ingredients from all categories
            $('#ingredient-list .ingredient-row').show();
        } else if (catId) {
            // Show ingredients from specific category
            $('#ingredient-list .ingredient-cat-' + catId).show();
        }
    });


    

    // Pre-select ingredient if provided in URL
    <?php if ($pre_selected_ingredient): ?>
    $(document).ready(function() {
        // Find the ingredient and its category
        const ingredientRow = $('.ingredient-row:has(#ingredient_<?php echo $pre_selected_ingredient; ?>)');
        if (ingredientRow.length > 0) {
            const categoryId = ingredientRow.attr('class').match(/ingredient-cat-(\d+)/)[1];
            
            // Select the category
            $('#categorySelect').val(categoryId).trigger('change');
            
            // Check the ingredient and enable quantity input
            setTimeout(function() {
                const checkbox = $('#ingredient_<?php echo $pre_selected_ingredient; ?>');
                checkbox.prop('checked', true).trigger('change');
                
                // Focus on quantity input
                const qtyInput = checkbox.closest('.row').find('input[type="number"]');
                qtyInput.focus();
            }, 100);
        }
    });
    <?php endif; ?>

    // Handle form submission with AJAX
    $('#requestStockForm').on('submit', function(e) {
        e.preventDefault();
        
        // Validate form before submission
        const selectedIngredients = $('input[name="ingredients[]"]:checked');
        
        // Check if there are any ingredients with stock available
        const availableIngredients = $('.ingredient-row:not(.unavailable)');
        if (availableIngredients.length === 0) {
            Swal.fire({
                icon: 'warning',
                title: 'No Stock Available',
                text: 'There are currently no ingredients with available stock. Please contact the administrator or try again later.',
                confirmButtonText: 'OK',
                confirmButtonColor: '#ffc107',
                customClass: {
                    confirmButton: 'swal2-confirm-warning'
                }
            });
            return;
        }
        
        if (selectedIngredients.length === 0) {
            Swal.fire({
                icon: 'warning',
                title: 'No Ingredients Selected',
                text: 'Please select at least one ingredient to request.',
                confirmButtonText: 'OK',
                confirmButtonColor: '#ffc107',
                customClass: {
                    confirmButton: 'swal2-confirm-warning'
                }
            });
            return;
        }
        
        // Check if quantities are entered for selected ingredients
        let hasQuantities = false;
        
        selectedIngredients.each(function() {
            const ingredientId = $(this).val();
            const quantityInput = $(`input[name="quantity[${ingredientId}]"]`);
            const quantityValue = quantityInput.val();
            const quantity = parseInt(quantityValue) || 0;
            
            if (quantity > 0) {
                hasQuantities = true;
            }
        });
        
        if (!hasQuantities) {
            Swal.fire({
                icon: 'warning',
                title: 'No Quantities Entered',
                text: 'Please enter quantities for the selected ingredients.',
                confirmButtonText: 'OK',
                confirmButtonColor: '#ffc107',
                customClass: {
                    confirmButton: 'swal2-confirm-warning'
                }
            });
            return;
        }
        
        // Show loading state
        const submitBtn = $(this).find('button[type="submit"]');
        const originalText = submitBtn.html();
        submitBtn.html('<i class="fas fa-spinner fa-spin"></i> Submitting...').prop('disabled', true);
        
        const formData = $(this).serialize();
        
        $.ajax({
            url: $(this).attr('action'),
            type: 'POST',
            data: formData,
            dataType: 'json',
            success: function(response) {
                if (response.success) {
                    // Show success modal
                    Swal.fire({
                        icon: 'success',
                        title: 'Request Submitted!',
                        text: response.message,
                        confirmButtonText: 'OK',
                        confirmButtonColor: '#8B4543',
                        customClass: {
                            confirmButton: 'swal2-confirm-success'
                        }
                    }).then((result) => {
                        // Reset form after successful submission
                        $('#requestStockForm')[0].reset();
                        $('#ingredient-list .ingredient-row').hide();
                        $('#categorySelect').val('').trigger('change');
                        
                        // Re-enable submit button
                        submitBtn.html(originalText).prop('disabled', false);
                    });
                } else {
                    // Show error modal
                    Swal.fire({
                        icon: 'error',
                        title: 'Error!',
                        text: response.message,
                        confirmButtonText: 'OK',
                        confirmButtonColor: '#dc3545',
                        customClass: {
                            confirmButton: 'swal2-confirm-error'
                        }
                    });
                    
                    // Re-enable submit button
                    submitBtn.html(originalText).prop('disabled', false);
                }
            },
            error: function(xhr, status, error) {
                // Show error modal for network/server errors
                Swal.fire({
                    icon: 'error',
                    title: 'Network Error!',
                    text: 'An error occurred while submitting your request. Please try again.',
                    confirmButtonText: 'OK',
                    confirmButtonColor: '#dc3545',
                    customClass: {
                        confirmButton: 'swal2-confirm-error'
                    }
                });
                
                // Re-enable submit button
                submitBtn.html(originalText).prop('disabled', false);
            }
        });
    });
});
</script>
<?php
if (!$isAjax) {
    include('footer.php');
}
?> 