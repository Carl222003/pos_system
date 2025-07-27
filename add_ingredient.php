<?php
require_once 'db_connect.php';
require_once 'auth_function.php';

// Only allow AJAX access for logged-in users
if (!isset($_SESSION['user_logged_in']) || $_SESSION['user_logged_in'] !== true) {
    http_response_code(403);
    exit('You are not authorized to access this resource.');
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    header('Content-Type: application/json');
    // Handle form submission (add ingredient)
    $category_id = $_POST['category_id'] ?? '';
    $ingredient_name = trim($_POST['ingredient_name'] ?? '');
    $ingredient_quantity = trim($_POST['ingredient_quantity'] ?? '');
    $ingredient_unit = trim($_POST['ingredient_unit'] ?? '');
    $ingredient_status = $_POST['ingredient_status'] ?? 'Available';
    $date_added = $_POST['date_added'] ?? date('Y-m-d');
    $consume_before = $_POST['consume_before'] ?? '';
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
    if (empty($consume_before)) {
        $errors[] = 'Consume Before Date is required.';
    }

    if (empty($errors)) {
        try {
            $stmt = $pdo->prepare("INSERT INTO ingredients (category_id, ingredient_name, ingredient_quantity, ingredient_unit, ingredient_status, date_added, consume_before) VALUES (?, ?, ?, ?, ?, ?, ?)");
            $stmt->execute([$category_id, $ingredient_name, $ingredient_quantity, $ingredient_unit, $ingredient_status, $date_added, $consume_before]);
            // Log activity
            $admin_id = $_SESSION['user_id'] ?? null;
            $catName = $pdo->query("SELECT category_name FROM pos_category WHERE category_id = " . intval($category_id))->fetchColumn();
            logActivity($pdo, $admin_id, 'Added Ingredient', 'Ingredient: ' . $ingredient_name . ' (Category: ' . $catName . ')');
            echo json_encode(['success' => true]);
        } catch (PDOException $e) {
            echo json_encode(['success' => false, 'message' => 'Database error: ' . $e->getMessage()]);
        }
    } else {
        echo json_encode(['success' => false, 'message' => implode(' ', $errors)]);
    }
    exit;
}

// If GET, output only the Add Ingredient modal form HTML (no header, no dashboard, no layout)
$categories = $pdo->query("SELECT category_id, category_name FROM pos_category WHERE status = 'active' ORDER BY category_name")->fetchAll(PDO::FETCH_ASSOC);
?>
<style>
.add-modal-header {
    background: #8B4543;
    color: #fff;
    border-top-left-radius: 1.25rem;
    border-top-right-radius: 1.25rem;
    border-bottom: none;
    padding: 1.2rem 1.5rem 1rem 1.5rem;
    display: flex;
    align-items: center;
    gap: 0.7rem;
    font-size: 1.3rem;
    font-weight: 700;
}
.add-modal-header .modal-title {
    color: #fff;
    font-weight: 700;
    font-size: 1.25rem;
    margin: 0;
    display: flex;
    align-items: center;
    gap: 0.5rem;
}
.add-modal-header .modal-icon {
    font-size: 1.3em;
    color: #fff;
    margin-right: 0.5rem;
}
.add-modal-header .btn-close {
    margin-left: auto;
    filter: invert(1) grayscale(100%) brightness(200%);
}
.add-modal-content {
    background: #fff;
    border-radius: 1.25rem !important;
    border: none;
    margin: 0;
    padding: 0;
    width: 100%;
    max-width: 420px;
    box-shadow: none !important;
}
.add-modal-body {
    padding: 1.5rem 1.5rem 0.5rem 1.5rem;
    background: #fff;
    border-radius: 0 0 1.25rem 1.25rem !important;
}
.add-modal-body label.form-label {
    color: #8B4543;
    font-weight: 600;
    margin-bottom: 0.25rem;
    letter-spacing: 0.01em;
}
.add-modal-footer {
    border-top: none;
    justify-content: flex-end;
    padding: 1.2rem 1.5rem 1.5rem 1.5rem;
    background: #fff;
    border-radius: 0 0 1.25rem 1.25rem !important;
    display: flex;
    gap: 1rem;
}
.add-modal-footer .btn-primary,
.add-modal-footer .btn-secondary {
    background: #8B4543;
    border: none;
    color: #fff;
    font-weight: 600;
    border-radius: 0.6rem;
    padding: 0.7rem 2rem;
    font-size: 1.1rem;
    box-shadow: 0 2px 8px 0 rgba(139, 69, 67, 0.08);
    transition: background 0.2s, box-shadow 0.2s;
}
.add-modal-footer .btn-primary:hover, .add-modal-footer .btn-primary:focus,
.add-modal-footer .btn-secondary:hover, .add-modal-footer .btn-secondary:focus {
    background: #723937;
    color: #fff;
    box-shadow: 0 4px 16px 0 rgba(139, 69, 67, 0.13);
}
/* Remove any extra background, shadow, or overlay from modal and its parent containers */
.add-modal-content {
    background: #fff;
    border-radius: 1.25rem !important;
    border: none;
    margin: 0;
    padding: 0;
    width: 100%;
    max-width: 420px;
    box-shadow: none !important;
}

.modal,
.modal-backdrop,
.modal-dialog {
    background: transparent !important;
    box-shadow: none !important;
}

/* Remove padding from modal-dialog if present */
.modal-dialog {
    padding: 0 !important;
    margin: 0 auto;
}

/* Remove any extra border or background from modal-fade */
.modal.fade {
    background: transparent !important;
}
/* Remove any extra border, background, or border-radius from modal parent containers */
.modal,
.modal-backdrop,
.modal-dialog {
    background: transparent !important;
    box-shadow: none !important;
    border: none !important;
    border-radius: 0 !important;
}

/* Only the modal itself should have border-radius and background */
.add-modal-content {
    background: #fff;
    border-radius: 1.25rem !important;
    border: none;
    margin: 0;
    padding: 0;
    width: 100%;
    max-width: 420px;
    box-shadow: none !important;
}
.modal,
.modal-dialog,
.modal-content {
    box-shadow: none !important;
    border: none !important;
    outline: none !important;
    background: none !important;
    border-radius: 0 !important;
}

.add-modal-content {
    background: #fff;
    border-radius: 1.25rem !important;
    border: none;
    margin: 0;
    padding: 0;
    width: 100%;
    max-width: 420px;
    box-shadow: none !important;
}
/* Center the modal horizontally and vertically */
.modal {
    display: flex !important;
    align-items: center;
    justify-content: center;
}

.modal-dialog {
    margin: 0 auto;
    display: flex;
    align-items: center;
    min-height: 100vh;
}
</style>
<div class="modal-content add-modal-content">
    <div class="add-modal-header">
        <span class="modal-icon"><i class="fas fa-carrot"></i></span>
        <span class="modal-title" id="addIngredientModalLabel">Add Ingredient</span>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
    </div>
    <div class="add-modal-body">
        <form id="addIngredientForm">
            <div class="mb-3">
                <label for="category_id" class="form-label">Category Name</label>
                <select name="category_id" id="category_id" class="form-select" required>
                    <option value="">Select Category</option>
                    <?php foreach ($categories as $category): ?>
                        <option value="<?php echo htmlspecialchars($category['category_id']); ?>"><?php echo htmlspecialchars($category['category_name']); ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="mb-3">
                <label for="ingredient_name" class="form-label">Ingredient Name</label>
                <input type="text" name="ingredient_name" id="ingredient_name" class="form-control" required>
            </div>
            <div class="mb-3">
                <label for="ingredient_quantity" class="form-label">Quantity</label>
                <input type="number" name="ingredient_quantity" id="ingredient_quantity" class="form-control" min="0" step="0.01" required>
            </div>
            <div class="mb-3">
                <label for="ingredient_unit" class="form-label">Unit</label>
                <input type="text" name="ingredient_unit" id="ingredient_unit" class="form-control" required>
            </div>
            <div class="mb-3">
                <label for="date_added" class="form-label">Date Added</label>
                <input type="date" name="date_added" id="date_added" class="form-control" value="<?php echo date('Y-m-d'); ?>" readonly>
            </div>
            <div class="mb-3">
                <label for="consume_before" class="form-label">Consume Before Date</label>
                <input type="date" name="consume_before" id="consume_before" class="form-control" required>
            </div>
            <div class="mb-3">
                <label for="ingredient_status" class="form-label">Status</label>
                <select name="ingredient_status" id="ingredient_status" class="form-select">
                    <option value="Available">Available</option>
                    <option value="Out of Stock">Out of Stock</option>
                </select>
            </div>
            <div class="add-modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                <button type="submit" class="btn btn-primary">Add Ingredient</button>
            </div>
        </form>
    </div>
</div>
