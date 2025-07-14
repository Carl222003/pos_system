<?php
// Prevent any accidental output before JSON
ob_start();
ini_set('display_errors', 0);
ini_set('log_errors', 1);
error_reporting(E_ALL);

require_once 'db_connect.php';
require_once 'auth_function.php';

checkCashierLogin();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    header('Content-Type: application/json');
    // Handle form submission (add ingredient)
    $category_id = $_POST['category_id'] ?? '';
    $ingredient_name = trim($_POST['ingredient_name'] ?? '');
    $ingredient_quantity = trim($_POST['ingredient_quantity'] ?? '');
    $ingredient_unit = trim($_POST['ingredient_unit'] ?? '');
    $ingredient_status = $_POST['ingredient_status'] ?? 'Available';
    $errors = [];

    if (empty($category_id)) {
        $errors[] = 'Category is required.';
    }
    if (empty($ingredient_name)) {
        $errors[] = 'Ingredient Name is required.';
    }
    if ($ingredient_quantity === '' || !is_numeric($ingredient_quantity) || $ingredient_quantity < 0) {
        $errors[] = 'Quantity must be a non-negative number.';
    }
    if (empty($ingredient_unit)) {
        $errors[] = 'Unit of Measurement is required.';
    }

    if (empty($errors)) {
        try {
            $stmt = $pdo->prepare("INSERT INTO ingredients (category_id, ingredient_name, ingredient_quantity, ingredient_unit, ingredient_status) VALUES (?, ?, ?, ?, ?)");
            $stmt->execute([$category_id, $ingredient_name, $ingredient_quantity, $ingredient_unit, $ingredient_status]);
            echo json_encode(['success' => true]);
        } catch (PDOException $e) {
            echo json_encode(['success' => false, 'message' => 'Database error: ' . $e->getMessage()]);
        }
    } else {
        echo json_encode(['success' => false, 'message' => implode(' ', $errors)]);
    }
    exit;
}

// If GET, output the Add Ingredient form HTML for the modal
// Fetch categories for the dropdown
$categories = $pdo->query("SELECT category_id, category_name FROM pos_category WHERE status = 'active' ORDER BY category_name")->fetchAll(PDO::FETCH_ASSOC);
?>
<style>
@keyframes modalPopIn {
  0% {
    opacity: 0;
    transform: scale(0.95) translateY(30px);
  }
  100% {
    opacity: 1;
    transform: scale(1) translateY(0);
  }
}
#addIngredientModal .modal-dialog {
    display: flex;
    align-items: center;
    justify-content: center;
    min-height: 100vh;
    margin: 0 auto;
    max-width: 370px;
    width: 100%;
    background: none !important;
    box-shadow: none !important;
    border: none !important;
    border-radius: 0 !important;
    padding: 0 !important;
}
#addIngredientModal .modal-content {
    background: #FCF9F8 !important;
    box-shadow: 0 8px 32px 0 rgba(139, 69, 67, 0.18), 0 1.5px 8px 0 rgba(139, 69, 67, 0.10);
    border: 1.5px solid #e5d3d3;
    border-radius: 1.25rem !important;
    margin: 0 !important;
    padding: 0 !important;
    width: 100%;
    max-width: 370px;
    animation: modalPopIn 0.35s cubic-bezier(.39,1.6,.47,.99);
    transition: box-shadow 0.2s;
}
#addIngredientModal .modal-content:hover {
    box-shadow: 0 12px 40px 0 rgba(139, 69, 67, 0.22), 0 2px 12px 0 rgba(139, 69, 67, 0.13);
    border-color: #d4a59a;
}
#addIngredientModal .modal-header {
    background: #8B4543;
    color: #fff;
    border-top-left-radius: 1.25rem;
    border-top-right-radius: 1.25rem;
    border-bottom: none;
    padding: 1rem 1.5rem 0.8rem 1.5rem;
    box-shadow: 0 2px 12px 0 rgba(139, 69, 67, 0.10);
}
#addIngredientModal .modal-body,
#addIngredientModal .modal-footer {
    background: #FCF9F8 !important;
    border-radius: 0 0 1.25rem 1.25rem !important;
    padding: 0.8rem 1.5rem 0.8rem 1.5rem;
    box-shadow: none !important;
}
#addIngredientModal .modal-body label.form-label {
    color: #8B4543;
    font-weight: 500;
    margin-bottom: 0.25rem;
    letter-spacing: 0.01em;
}
#addIngredientModal .form-control:focus, #addIngredientModal .form-select:focus {
    border-color: #8B4543;
    box-shadow: 0 0 0 2px #d4a59a33;
    transition: border-color 0.2s, box-shadow 0.2s;
}
#addIngredientModal .modal-footer {
    border-top: none;
    justify-content: flex-start;
    padding-top: 0.5rem;
    background: #FCF9F8 !important;
}
#addIngredientModal .btn-primary {
    background: linear-gradient(90deg, #8B4543 0%, #D4A59A 100%);
    border: none;
    color: #fff;
    font-weight: 500;
    transition: background 0.2s, box-shadow 0.2s;
    box-shadow: 0 2px 8px 0 rgba(139, 69, 67, 0.08);
}
#addIngredientModal .btn-primary:hover, #addIngredientModal .btn-primary:focus {
    background: linear-gradient(90deg, #723937 0%, #C4804D 100%);
    color: #fff;
    box-shadow: 0 4px 16px 0 rgba(139, 69, 67, 0.13);
}
#addIngredientModal .btn-secondary {
    background: #bdbdbd;
    border: none;
    color: #fff;
    font-weight: 500;
    transition: background 0.2s, box-shadow 0.2s;
}
#addIngredientModal .btn-secondary:hover, #addIngredientModal .btn-secondary:focus {
    background: #8B4543;
    color: #fff;
}
</style>
<div class="modal-header">
    <h5 class="modal-title">Add Ingredient</h5>
    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
</div>
<div class="modal-body">
    <form id="addIngredientForm">
        <div class="mb-2">
            <label for="category_id" class="form-label">Category</label>
            <select name="category_id" id="category_id" class="form-select" required>
                <option value="">Select Category</option>
                <?php foreach ($categories as $category): ?>
                    <option value="<?php echo htmlspecialchars($category['category_id']); ?>"><?php echo htmlspecialchars($category['category_name']); ?></option>
                <?php endforeach; ?>
            </select>
        </div>
        <div class="mb-2">
            <label for="ingredient_name" class="form-label">Ingredient Name</label>
            <input type="text" name="ingredient_name" id="ingredient_name" class="form-control" required>
        </div>
        <div class="mb-2">
            <label for="ingredient_quantity" class="form-label">Quantity</label>
            <input type="number" name="ingredient_quantity" id="ingredient_quantity" class="form-control" min="0" step="0.01" required>
        </div>
        <div class="mb-2">
            <label for="ingredient_unit" class="form-label">Unit</label>
            <input type="text" name="ingredient_unit" id="ingredient_unit" class="form-control" required>
        </div>
        <div class="mb-2">
            <label for="ingredient_status" class="form-label">Status</label>
            <select name="ingredient_status" id="ingredient_status" class="form-select">
                <option value="Available">Available</option>
                <option value="Out of Stock">Out of Stock</option>
            </select>
        </div>
        <div class="modal-footer">
            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
            <button type="submit" class="btn btn-primary">Add Ingredient</button>
        </div>
    </form>
</div>
