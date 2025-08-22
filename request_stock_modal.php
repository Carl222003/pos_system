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

<div class="modal-header modern-header">
    <div class="header-content">
        <div class="header-icon">
            <i class="fas fa-shopping-cart"></i>
        </div>
        <div class="header-text">
            <h5 class="modal-title mb-0">
        <?php if ($pre_selected_ingredient): ?>
            Request Stock - <?php echo htmlspecialchars($ingredients[0]['ingredient_name'] ?? 'Ingredient'); ?>
        <?php else: ?>
            Request Stock
        <?php endif; ?>
    </h5>
            <p class="header-subtitle mb-0">Submit your ingredient requirements</p>
        </div>
    </div>
    <button type="button" class="btn-close modern-close" data-bs-dismiss="modal" aria-label="Close">
        <i class="fas fa-times"></i>
    </button>
</div>

<style>
.modern-header {
    background: linear-gradient(135deg, #8B4543 0%, #A65D5D 50%, #8B4543 100%);
    border: none;
    padding: 1.5rem 2rem;
    border-radius: 20px 20px 0 0;
    position: relative;
    overflow: hidden;
    margin: 0;
}

.modern-header::before {
    content: '';
    position: absolute;
    top: 0;
    left: 0;
    right: 0;
    bottom: 0;
    background: linear-gradient(45deg, rgba(255,255,255,0.1) 0%, transparent 50%);
    pointer-events: none;
}

.header-content {
    display: flex;
    align-items: center;
    gap: 1rem;
    position: relative;
    z-index: 1;
}

.header-icon {
    width: 50px;
    height: 50px;
    background: rgba(255, 255, 255, 0.2);
    border-radius: 15px;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 1.5rem;
    color: white;
    backdrop-filter: blur(10px);
    box-shadow: 0 4px 15px rgba(0, 0, 0, 0.2);
}

.header-text .modal-title {
    color: white;
    font-weight: 700;
    font-size: 1.4rem;
    text-shadow: 0 2px 4px rgba(0, 0, 0, 0.3);
}

.header-subtitle {
    color: rgba(255, 255, 255, 0.9);
    font-size: 0.9rem;
    font-weight: 400;
    text-shadow: 0 1px 2px rgba(0, 0, 0, 0.2);
}

.modern-close {
    background: rgba(255, 255, 255, 0.2);
    border: none;
    border-radius: 12px;
    width: 40px;
    height: 40px;
    display: flex;
    align-items: center;
    justify-content: center;
    color: white;
    font-size: 1.1rem;
    transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
    backdrop-filter: blur(10px);
    position: relative;
    z-index: 1;
}

.modern-close:hover {
    background: rgba(255, 255, 255, 0.3);
    transform: scale(1.1) rotate(90deg);
    box-shadow: 0 4px 15px rgba(0, 0, 0, 0.3);
}

.modern-body {
    padding: 2rem;
    background: linear-gradient(135deg, #ffffff 0%, #f8f9fa 100%);
}

.form-section {
    background: white;
    border-radius: 16px;
    padding: 1.5rem;
    box-shadow: 0 4px 20px rgba(139, 69, 67, 0.08);
    border: 1px solid rgba(139, 69, 67, 0.1);
    transition: all 0.3s ease;
    margin-bottom: 1.5rem;
}

.form-section:hover {
    transform: translateY(-2px);
    box-shadow: 0 8px 30px rgba(139, 69, 67, 0.12);
}

.section-header {
    display: flex;
    align-items: center;
    gap: 0.75rem;
    margin-bottom: 1rem;
    padding-bottom: 0.75rem;
    border-bottom: 2px solid rgba(139, 69, 67, 0.1);
}

.section-icon {
    color: #8B4543;
    font-size: 1.2rem;
}

.section-title {
    color: #8B4543;
    font-weight: 700;
    margin: 0;
    font-size: 1.1rem;
}

.modern-select {
    border: 2px solid #e9ecef;
    border-radius: 12px;
    padding: 0.75rem 1rem;
    font-size: 1rem;
    transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
    background: white;
}

.modern-select:focus {
    border-color: #8B4543;
    box-shadow: 0 0 0 0.2rem rgba(139, 69, 67, 0.25);
    outline: none;
}

.ingredients-container {
    max-height: 400px;
    overflow-y: auto;
    padding: 0.5rem;
    border-radius: 12px;
    background: #f8f9fa;
}

.ingredient-card {
    background: white;
    border-radius: 12px;
    padding: 1.25rem;
    margin-bottom: 1rem;
    border: 2px solid #e9ecef;
    transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
    position: relative;
    overflow: hidden;
}

.ingredient-card:hover {
    border-color: #8B4543;
    transform: translateY(-2px);
    box-shadow: 0 8px 25px rgba(139, 69, 67, 0.15);
}

.ingredient-card.unavailable {
    background: #f8f9fa;
    border-color: #dee2e6;
    opacity: 0.7;
}

.ingredient-card.selectable {
    cursor: pointer;
}

.ingredient-header {
    display: flex;
    justify-content: between;
    align-items: flex-start;
    margin-bottom: 0.75rem;
}

.ingredient-name {
    color: #2c3e50;
    font-weight: 700;
    margin: 0;
    font-size: 1.1rem;
}

.ingredient-unit {
    color: #6c757d;
    font-weight: 500;
    font-size: 0.9rem;
    margin-left: 0.5rem;
}

.category-badge {
    background: linear-gradient(135deg, #8B4543, #A65D5D);
    color: white;
    padding: 0.25rem 0.75rem;
    border-radius: 20px;
    font-size: 0.8rem;
    font-weight: 600;
    text-shadow: 0 1px 2px rgba(0, 0, 0, 0.2);
}

.status-indicator {
    display: inline-flex;
    align-items: center;
    gap: 0.5rem;
    padding: 0.5rem 1rem;
    border-radius: 25px;
    font-weight: 600;
    font-size: 0.9rem;
}

.status-indicator.available {
    background: linear-gradient(135deg, #d4edda, #c3e6cb);
    color: #155724;
    border: 1px solid #c3e6cb;
}

.status-indicator.unavailable {
    background: linear-gradient(135deg, #f8d7da, #f5c6cb);
    color: #721c24;
    border: 1px solid #f5c6cb;
}

.quantity-input {
    margin-top: 1rem;
}

.quantity-label {
    display: block;
    color: #495057;
    font-weight: 600;
    margin-bottom: 0.5rem;
    font-size: 0.9rem;
}

.modern-input {
    border: 2px solid #e9ecef;
    border-radius: 8px;
    padding: 0.75rem 1rem;
    font-size: 1rem;
    transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
}

.modern-input:focus {
    border-color: #8B4543;
    box-shadow: 0 0 0 0.2rem rgba(139, 69, 67, 0.25);
    outline: none;
}

.unit-display {
    background: #8B4543;
    color: white;
    border: 2px solid #8B4543;
    font-weight: 600;
}

.modern-textarea {
    border: 2px solid #e9ecef;
    border-radius: 12px;
    padding: 1rem;
    font-size: 1rem;
    transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
    resize: vertical;
    min-height: 100px;
}

.modern-textarea:focus {
    border-color: #8B4543;
    box-shadow: 0 0 0 0.2rem rgba(139, 69, 67, 0.25);
    outline: none;
}

.modern-checkbox {
    width: 20px;
    height: 20px;
    accent-color: #8B4543;
    margin-right: 0.75rem;
}

.ingredient-selector {
    display: flex;
    align-items: center;
    margin-bottom: 0.75rem;
}

.checkbox-label {
    cursor: pointer;
    user-select: none;
}

/* Enhanced Request Modal Styling */
.enhanced-request-body {
    padding: 2rem;
    background: linear-gradient(135deg, #ffffff 0%, #f8f9fa 100%);
}

.enhanced-ingredient-section {
    display: grid;
    grid-template-columns: 2fr 1fr;
    gap: 2.5rem;
    align-items: start;
}

.ingredient-overview-card {
    background: white;
    border-radius: 16px;
    padding: 2rem;
    box-shadow: 0 8px 30px rgba(139, 69, 67, 0.1);
    border: 1px solid rgba(139, 69, 67, 0.1);
    transition: all 0.3s ease;
}

.ingredient-overview-card:hover {
    transform: translateY(-2px);
    box-shadow: 0 12px 40px rgba(139, 69, 67, 0.15);
}

.ingredient-header-section {
    display: flex;
    align-items: center;
    gap: 1.5rem;
    flex-wrap: wrap;
}

.ingredient-icon-large {
    width: 80px;
    height: 80px;
    background: linear-gradient(135deg, #8B4543, #A65D5D);
    border-radius: 20px;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 2rem;
    color: white;
    box-shadow: 0 8px 25px rgba(139, 69, 67, 0.3);
}

.ingredient-name-large {
    font-size: 1.8rem;
    font-weight: 700;
    color: #2c3e50;
    margin: 0 0 0.5rem 0;
}

.ingredient-meta {
    display: flex;
    gap: 1rem;
    flex-wrap: wrap;
}

.category-tag {
    background: linear-gradient(135deg, #8B4543, #A65D5D);
    color: white;
    padding: 0.5rem 1rem;
    border-radius: 20px;
    font-weight: 600;
    font-size: 0.9rem;
    display: flex;
    align-items: center;
}

.unit-info {
    background: #e9ecef;
    color: #495057;
    padding: 0.5rem 1rem;
    border-radius: 20px;
    font-weight: 600;
    font-size: 0.9rem;
    display: flex;
    align-items: center;
}

.stock-indicator {
    display: flex;
    flex-direction: column;
    align-items: center;
    gap: 0.5rem;
    padding: 1.5rem;
    border-radius: 16px;
    text-align: center;
    min-width: 120px;
}

.stock-indicator.available {
    background: linear-gradient(135deg, #d4edda, #c3e6cb);
    color: #155724;
    border: 2px solid #c3e6cb;
}

.stock-indicator.unavailable {
    background: linear-gradient(135deg, #f8d7da, #f5c6cb);
    color: #721c24;
    border: 2px solid #f5c6cb;
}

.stock-indicator i {
    font-size: 1.5rem;
}

.stock-number {
    font-size: 2rem;
    font-weight: 800;
    line-height: 1;
}

.stock-label {
    font-size: 0.9rem;
    font-weight: 600;
    text-transform: uppercase;
    letter-spacing: 0.5px;
}

.quantity-request-section {
    margin-top: 1.5rem;
}

.quantity-card {
    background: white;
    border-radius: 16px;
    padding: 1.5rem;
    box-shadow: 0 6px 25px rgba(139, 69, 67, 0.08);
    border: 1px solid rgba(139, 69, 67, 0.1);
}

.quantity-header {
    display: flex;
    align-items: center;
    gap: 0.75rem;
    margin-bottom: 1.5rem;
    padding-bottom: 1rem;
    border-bottom: 2px solid #f8f9fa;
}

.quantity-header i {
    color: #8B4543;
    font-size: 1.2rem;
}

.quantity-header h5 {
    color: #2c3e50;
    font-weight: 700;
    margin: 0;
    font-size: 1.2rem;
}

.quantity-input-group {
    display: flex;
    flex-direction: column;
    gap: 1rem;
}

.input-wrapper {
    position: relative;
    display: flex;
    align-items: center;
}

.enhanced-quantity-input {
    flex: 1;
    border: 3px solid #e9ecef;
    border-radius: 12px;
    padding: 1rem 1.5rem;
    font-size: 1.2rem;
    font-weight: 600;
    text-align: center;
    transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
    background: white;
    color: #2c3e50;
}

.enhanced-quantity-input:focus {
    border-color: #8B4543;
    box-shadow: 0 0 0 0.2rem rgba(139, 69, 67, 0.25);
    outline: none;
    transform: scale(1.02);
}

.input-unit {
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

.quantity-suggestions {
    display: flex;
    gap: 0.75rem;
    justify-content: center;
    flex-wrap: wrap;
}

.suggestion-btn {
    background: linear-gradient(135deg, #f8f9fa, #e9ecef);
    border: 2px solid #dee2e6;
    color: #495057;
    padding: 0.75rem 1.25rem;
    border-radius: 12px;
    font-weight: 600;
    font-size: 1rem;
    transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
    cursor: pointer;
    min-width: 50px;
}

.suggestion-btn:hover {
    background: linear-gradient(135deg, #8B4543, #A65D5D);
    color: white;
    border-color: #8B4543;
    transform: translateY(-2px);
    box-shadow: 0 4px 15px rgba(139, 69, 67, 0.3);
}

.notes-section {
    margin-top: 2rem;
}

.notes-card {
    background: white;
    border-radius: 16px;
    padding: 1.5rem;
    box-shadow: 0 6px 25px rgba(139, 69, 67, 0.08);
    border: 1px solid rgba(139, 69, 67, 0.1);
}

.notes-header {
    display: flex;
    align-items: center;
    gap: 0.75rem;
    margin-bottom: 1rem;
    padding-bottom: 0.75rem;
    border-bottom: 2px solid #f8f9fa;
}

.notes-header i {
    color: #8B4543;
    font-size: 1.1rem;
}

.notes-header h5 {
    color: #2c3e50;
    font-weight: 700;
    margin: 0;
    font-size: 1.1rem;
    flex: 1;
}

.optional-badge {
    background: #6c757d;
    color: white;
    padding: 0.25rem 0.75rem;
    border-radius: 12px;
    font-size: 0.8rem;
    font-weight: 600;
    text-transform: uppercase;
}

.enhanced-notes-input {
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
}

.enhanced-notes-input:focus {
    border-color: #8B4543;
    box-shadow: 0 0 0 0.2rem rgba(139, 69, 67, 0.25);
    outline: none;
}

.enhanced-notes-input::placeholder {
    color: #adb5bd;
    font-style: italic;
}

.landscape-notes-section {
    grid-column: 1 / -1;
    margin-top: 1.5rem;
}

.ingredient-details {
    flex: 1;
    min-width: 200px;
}

.stock-status-large {
    margin-left: auto;
}

/* Responsive Design for Enhanced Modal */
@media (max-width: 768px) {
    .enhanced-ingredient-section {
        grid-template-columns: 1fr;
        gap: 1.5rem;
    }
    
    .ingredient-header-section {
        flex-direction: column;
        text-align: center;
        gap: 1rem;
    }
    
    .stock-status-large {
        margin-left: 0;
    }
    
    .quantity-suggestions {
        justify-content: center;
    }
    
    .enhanced-request-body {
        padding: 1.5rem;
    }
}
</style>

<script>
function setQuantity(ingredientId, quantity) {
    const input = document.getElementById('quantity_' + ingredientId);
    if (input) {
        input.value = quantity;
        input.focus();
        
        // Add visual feedback
        input.style.transform = 'scale(1.05)';
        setTimeout(() => {
            input.style.transform = 'scale(1)';
        }, 200);
    }
}
</script>

<div class="modal-body enhanced-request-body">
    <form id="requestStockModalForm">
                <?php if ($pre_selected_ingredient): ?>
            <!-- Enhanced Single ingredient view -->
            <?php foreach ($ingredients as $ingredient): ?>
                <div class="enhanced-ingredient-section">
                    <!-- Ingredient Overview Card -->
                    <div class="ingredient-overview-card">
                        <div class="ingredient-header-section">
                            <div class="ingredient-icon-large">
                                <i class="fas fa-cube"></i>
                            </div>
                            <div class="ingredient-details">
                                <h4 class="ingredient-name-large">
                                    <?php echo htmlspecialchars($ingredient['ingredient_name']); ?>
                                </h4>
                                <div class="ingredient-meta">
                                    <span class="category-tag">
                                        <i class="fas fa-tag me-1"></i>
                                        <?php echo htmlspecialchars($ingredient['category_name']); ?>
                                    </span>
                                    <span class="unit-info">
                                        <i class="fas fa-balance-scale me-1"></i>
                                        <?php echo htmlspecialchars($ingredient['ingredient_unit']); ?>
                                    </span>
                                </div>
                            </div>
                            <div class="stock-status-large">
                                <div class="stock-indicator <?php echo ($ingredient['ingredient_status'] === 'Available') ? 'available' : 'unavailable'; ?>">
                                    <?php if ($ingredient['ingredient_status'] === 'Available'): ?>
                                        <i class="fas fa-check-circle"></i>
                                        <span class="stock-number"><?php echo htmlspecialchars($ingredient['ingredient_quantity']); ?></span>
                                        <span class="stock-label">Available</span>
                <?php else: ?>
                                        <i class="fas fa-times-circle"></i>
                                        <span class="stock-label">Unavailable</span>
                <?php endif; ?>
                                </div>
                            </div>
                                </div>
                            </div>
                    
                    <!-- Request Quantity Section -->
                    <div class="quantity-request-section">
                        <div class="quantity-card">
                            <div class="quantity-header">
                                <i class="fas fa-shopping-cart"></i>
                                <h5>Request Quantity</h5>
                            </div>
                            <div class="quantity-input-container">
                                <div class="quantity-input-group">
                                    <div class="input-wrapper">
                                        <input type="number" class="enhanced-quantity-input" 
                                       name="quantity[<?php echo $ingredient['ingredient_id']; ?>]" 
                                       id="quantity_<?php echo $ingredient['ingredient_id']; ?>"
                                               min="1" placeholder="0" 
                                       <?php if ($ingredient['ingredient_status'] !== 'Available') echo 'disabled'; ?>>
                                        <span class="input-unit"><?php echo htmlspecialchars($ingredient['ingredient_unit']); ?></span>
                                    </div>
                                    <div class="quantity-suggestions">
                                        <button type="button" class="suggestion-btn" onclick="setQuantity(<?php echo $ingredient['ingredient_id']; ?>, 5)">5</button>
                                        <button type="button" class="suggestion-btn" onclick="setQuantity(<?php echo $ingredient['ingredient_id']; ?>, 10)">10</button>
                                        <button type="button" class="suggestion-btn" onclick="setQuantity(<?php echo $ingredient['ingredient_id']; ?>, 20)">20</button>
                                        <button type="button" class="suggestion-btn" onclick="setQuantity(<?php echo $ingredient['ingredient_id']; ?>, 50)">50</button>
                                    </div>
                                </div>
                                <input type="hidden" name="ingredients[]" value="<?php echo $ingredient['ingredient_id']; ?>">
                            </div>
                        </div>
                            </div>
                        </div>
                    <?php endforeach; ?>
            
            <!-- Full Width Notes Section for Single Ingredient -->
            <div class="landscape-notes-section">
                <div class="notes-card">
                    <div class="notes-header">
                        <i class="fas fa-sticky-note"></i>
                        <h5>Additional Notes</h5>
                        <span class="optional-badge">Optional</span>
                    </div>
                    <div class="notes-input-container">
                        <textarea name="notes" id="notes" class="enhanced-notes-input" rows="3" 
                                  placeholder="Add any special instructions, urgency notes, or specific requirements for your stock request..."></textarea>
                    </div>
                </div>
            </div>
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
        

    </form>
</div>

<div class="modal-footer modern-footer">
    <div class="footer-content">
        <button type="button" class="btn modern-btn-secondary" data-bs-dismiss="modal">
            <i class="fas fa-times me-2"></i>
            Cancel
    </button>
        <button type="button" class="btn modern-btn-primary" id="submitRequestBtn">
            <i class="fas fa-paper-plane me-2"></i>
            Submit Request
    </button>
</div>
</div>

<style>
.modern-footer {
    background: linear-gradient(135deg, #ffffff 0%, #f8f9fa 100%);
    border: none;
    padding: 1.5rem 2rem;
    border-radius: 0 0 20px 20px;
    margin: 0;
}

.footer-content {
    display: flex;
    justify-content: flex-end;
    gap: 1rem;
}

.modern-btn-secondary {
    background: linear-gradient(135deg, #6c757d, #5a6268);
    color: white;
    border: none;
    border-radius: 12px;
    padding: 0.75rem 1.5rem;
    font-weight: 600;
    font-size: 1rem;
    transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
    box-shadow: 0 4px 12px rgba(108, 117, 125, 0.3);
}

.modern-btn-secondary:hover {
    background: linear-gradient(135deg, #5a6268, #4e555b);
    transform: translateY(-2px);
    box-shadow: 0 6px 20px rgba(108, 117, 125, 0.4);
}

.modern-btn-primary {
    background: linear-gradient(135deg, #8B4543, #A65D5D);
    color: white;
    border: none;
    border-radius: 12px;
    padding: 0.75rem 2rem;
    font-weight: 700;
    font-size: 1rem;
    transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
    box-shadow: 0 4px 12px rgba(139, 69, 67, 0.3);
    position: relative;
    overflow: hidden;
}

.modern-btn-primary::before {
    content: '';
    position: absolute;
    top: 0;
    left: -100%;
    width: 100%;
    height: 100%;
    background: linear-gradient(90deg, transparent, rgba(255,255,255,0.2), transparent);
    transition: left 0.6s;
}

.modern-btn-primary:hover::before {
    left: 100%;
}

.modern-btn-primary:hover {
    background: linear-gradient(135deg, #723836, #8B4543);
    transform: translateY(-2px);
    box-shadow: 0 6px 20px rgba(139, 69, 67, 0.4);
}

.modern-btn-primary:active {
    transform: translateY(0);
    box-shadow: 0 2px 8px rgba(139, 69, 67, 0.3);
}
</style>

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