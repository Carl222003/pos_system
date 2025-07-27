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

// Fetch all ingredients with category info (any status)
$ingredients = $pdo->query("SELECT i.ingredient_id, i.ingredient_name, i.ingredient_unit, i.ingredient_quantity, i.ingredient_status, i.category_id, c.category_name
    FROM ingredients i
    LEFT JOIN pos_category c ON i.category_id = c.category_id
    ORDER BY c.category_name, i.ingredient_name")->fetchAll(PDO::FETCH_ASSOC);
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
.stockman-card .ingredient-row.unavailable {
    opacity: 0.5;
    pointer-events: none;
}
.stockman-card .ingredient-status {
    font-size: 0.95em;
    font-weight: 500;
    margin-left: 0.5em;
}
.stockman-card .ingredient-status.available {
    color: #4B7F52;
}
.stockman-card .ingredient-status.unavailable {
    color: #dc3545;
}
</style>
<div class="container mt-4">
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
                    <?php foreach ($categories as $cat): ?>
                        <option value="<?php echo $cat['category_id']; ?>"><?php echo htmlspecialchars($cat['category_name']); ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="mb-3">
                <label for="ingredients" class="form-label">Select Ingredients</label>
                <div id="ingredient-list">
                    <?php foreach ($ingredients as $ingredient): ?>
                        <div class="row mb-2 align-items-center ingredient-row ingredient-cat-<?php echo $ingredient['category_id']; ?><?php if ($ingredient['ingredient_status'] !== 'Active') echo ' unavailable'; ?>" style="display:none;">
                            <div class="col-md-6">
                                <input type="checkbox" name="ingredients[]" value="<?php echo $ingredient['ingredient_id']; ?>" id="ingredient_<?php echo $ingredient['ingredient_id']; ?>" <?php if ($ingredient['ingredient_status'] !== 'Active') echo 'disabled'; ?>>
                                <label for="ingredient_<?php echo $ingredient['ingredient_id']; ?>">
                                    <strong><?php echo htmlspecialchars($ingredient['ingredient_name']); ?></strong>
                                    <span class="text-muted">(<?php echo htmlspecialchars($ingredient['ingredient_unit']); ?>)</span>
                                    <span class="ingredient-status <?php echo ($ingredient['ingredient_status'] === 'Active') ? 'available' : 'unavailable'; ?>">
                                        <?php if ($ingredient['ingredient_status'] === 'Active') {
                                            echo 'Available: ' . htmlspecialchars($ingredient['ingredient_quantity']);
                                        } else {
                                            echo 'Unavailable';
                                        } ?>
                                    </span><br>
                                    <small>Category: <?php echo htmlspecialchars($ingredient['category_name']); ?></small>
                                </label>
                            </div>
                            <div class="col-md-4">
                                <input type="number" class="form-control" name="quantity[<?php echo $ingredient['ingredient_id']; ?>]" min="1" placeholder="Quantity" <?php if ($ingredient['ingredient_status'] !== 'Active') echo 'disabled'; ?>>
                            </div>
                        </div>
                    <?php endforeach; ?>
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
        qtyInput.prop('disabled', !this.checked);
        if (!this.checked) qtyInput.val('');
    });

    // Category filter logic
    $('#categorySelect').on('change', function() {
        var catId = $(this).val();
        $('#ingredient-list .ingredient-row').hide();
        if (catId) {
            $('#ingredient-list .ingredient-cat-' + catId).show();
        }
    });
});
</script>
<?php
if (!$isAjax) {
    include('footer.php');
}
?> 