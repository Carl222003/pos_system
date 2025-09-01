<?php
require_once 'db_connect.php';
require_once 'auth_function.php';

// Check if user is logged in and is a stockman
if (!isset($_SESSION['user_logged_in']) || $_SESSION['user_logged_in'] !== true || $_SESSION['user_type'] !== 'Stockman') {
    echo '<div class="alert alert-danger">Access denied</div>';
    exit();
}

$category_id = $_GET['category_id'] ?? null;
$branch_id = $_SESSION['branch_id'] ?? null;

// If branch_id is not in session, try to fetch from user record
if (!$branch_id) {
    $stmt = $pdo->prepare('SELECT branch_id FROM pos_user WHERE user_id = ?');
    $stmt->execute([$_SESSION['user_id']]);
    $branch_id = $stmt->fetchColumn();
}

if (!$category_id || !$branch_id) {
    echo '<div class="alert alert-warning">Invalid category or no branch assigned</div>';
    exit();
}

try {
    // Get category name
    $stmt = $pdo->prepare("SELECT category_name FROM pos_category WHERE category_id = ?");
    $stmt->execute([$category_id]);
    $category_name = $stmt->fetchColumn();
    
    if (!$category_name) {
        echo '<div class="alert alert-warning">Category not found</div>';
        exit();
    }
    
    // Get ingredients in this category for this branch
    $stmt = $pdo->prepare("
        SELECT 
            i.ingredient_id,
            i.ingredient_name,
            i.ingredient_quantity,
            i.ingredient_unit,
            i.ingredient_status,
            i.ingredient_cost,
            i.ingredient_max_quantity,
            i.expiry_date,
            i.last_movement_date,
            i.created_date
        FROM ingredients i
        WHERE i.category_id = ? AND i.branch_id = ?
        ORDER BY 
            CASE 
                WHEN i.ingredient_quantity <= 0 THEN 1
                WHEN i.ingredient_quantity < 5 OR i.ingredient_quantity < (COALESCE(i.ingredient_max_quantity, 100) * 0.1) THEN 2
                ELSE 3
            END,
            i.ingredient_name
    ");
    $stmt->execute([$category_id, $branch_id]);
    $ingredients = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    if (empty($ingredients)) {
        echo '<div class="text-center text-muted py-4">
                <i class="fas fa-box-open fa-3x mb-3"></i>
                <h5>No Items Found</h5>
                <p>No ingredients found in this category for your branch.</p>
              </div>';
        exit();
    }
    
    // Calculate category statistics
    $total_items = count($ingredients);
    $out_of_stock = 0;
    $low_stock = 0;
    $healthy_stock = 0;
    $total_value = 0;
    
    foreach ($ingredients as $ingredient) {
        $quantity = $ingredient['ingredient_quantity'];
        $max_qty = $ingredient['ingredient_max_quantity'] ?: 100;
        $cost = $ingredient['ingredient_cost'] ?: 0;
        
        if ($quantity <= 0) {
            $out_of_stock++;
        } elseif ($quantity < 5 || $quantity < ($max_qty * 0.1)) {
            $low_stock++;
        } else {
            $healthy_stock++;
        }
        
        $total_value += $quantity * $cost;
    }
    
    ?>
    
    <div class="category-items-header mb-4">
        <div class="d-flex justify-content-between align-items-center mb-3">
            <h4 class="mb-0">
                <i class="fas fa-tag me-2"></i>
                <?= htmlspecialchars($category_name) ?> Items
            </h4>
            <span class="badge bg-primary fs-6"><?= $total_items ?> items</span>
        </div>
        
        <!-- Category Statistics -->
        <div class="row g-3 mb-4">
            <div class="col-md-3">
                <div class="stat-card bg-success text-white">
                    <div class="stat-value"><?= $healthy_stock ?></div>
                    <div class="stat-label">Healthy Stock</div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="stat-card bg-warning text-dark">
                    <div class="stat-value"><?= $low_stock ?></div>
                    <div class="stat-label">Low Stock</div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="stat-card bg-danger text-white">
                    <div class="stat-value"><?= $out_of_stock ?></div>
                    <div class="stat-label">Out of Stock</div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="stat-card bg-info text-white">
                    <div class="stat-value">₱<?= number_format($total_value, 2) ?></div>
                    <div class="stat-label">Total Value</div>
                </div>
            </div>
        </div>
    </div>
    
    <!-- Ingredients Table -->
    <div class="table-responsive">
        <table class="table table-hover">
            <thead>
                <tr>
                    <th>Item Name</th>
                    <th>Current Stock</th>
                    <th>Unit</th>
                    <th>Status</th>
                    <th>Cost</th>
                    <th>Last Movement</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($ingredients as $ingredient): ?>
                    <?php
                    $quantity = $ingredient['ingredient_quantity'];
                    $max_qty = $ingredient['ingredient_max_quantity'] ?: 100;
                    $cost = $ingredient['ingredient_cost'] ?: 0;
                    
                    // Determine status and styling
                    if ($quantity <= 0) {
                        $status_class = 'danger';
                        $status_text = 'Out of Stock';
                        $row_class = 'table-danger';
                    } elseif ($quantity < 5 || $quantity < ($max_qty * 0.1)) {
                        $status_class = 'warning';
                        $status_text = 'Low Stock';
                        $row_class = 'table-warning';
                    } else {
                        $status_class = 'success';
                        $status_text = 'Healthy';
                        $row_class = '';
                    }
                    
                    // Format last movement date
                    $last_movement = $ingredient['last_movement_date'] ? 
                        date('M j, Y', strtotime($ingredient['last_movement_date'])) : 
                        'Never';
                    ?>
                    
                    <tr class="<?= $row_class ?>">
                        <td>
                            <div class="d-flex align-items-center">
                                <i class="fas fa-box me-2 text-<?= $status_class ?>"></i>
                                <strong><?= htmlspecialchars($ingredient['ingredient_name']) ?></strong>
                            </div>
                        </td>
                        <td>
                            <span class="fw-bold"><?= $quantity ?></span>
                            <?php if ($max_qty > 0): ?>
                                <small class="text-muted d-block">
                                    Max: <?= $max_qty ?>
                                </small>
                            <?php endif; ?>
                        </td>
                        <td>
                            <span class="badge bg-secondary"><?= htmlspecialchars($ingredient['ingredient_unit']) ?></span>
                        </td>
                        <td>
                            <span class="badge bg-<?= $status_class ?>"><?= $status_text ?></span>
                        </td>
                        <td>
                            <?php if ($cost > 0): ?>
                                ₱<?= number_format($cost, 2) ?>
                            <?php else: ?>
                                <span class="text-muted">-</span>
                            <?php endif; ?>
                        </td>
                        <td>
                            <small class="text-muted"><?= $last_movement ?></small>
                        </td>
                        <td>
                            <div class="btn-group btn-group-sm">
                                <button class="btn btn-outline-primary" 
                                        onclick="viewItemDetails(<?= $ingredient['ingredient_id'] ?>)"
                                        title="View Details">
                                    <i class="fas fa-eye"></i>
                                </button>
                                <button class="btn btn-outline-warning" 
                                        onclick="adjustStock(<?= $ingredient['ingredient_id'] ?>)"
                                        title="Adjust Stock">
                                    <i class="fas fa-edit"></i>
                                </button>
                                <?php if ($quantity <= 0 || $quantity < 5): ?>
                                    <button class="btn btn-outline-success" 
                                            onclick="requestStock(<?= $ingredient['ingredient_id'] ?>)"
                                            title="Request Stock">
                                        <i class="fas fa-plus"></i>
                                    </button>
                                <?php endif; ?>
                            </div>
                        </td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
    
    <style>
    .stat-card {
        padding: 1rem;
        border-radius: 8px;
        text-align: center;
        box-shadow: 0 2px 8px rgba(0,0,0,0.1);
    }
    
    .stat-value {
        font-size: 1.5rem;
        font-weight: 700;
        margin-bottom: 0.25rem;
    }
    
    .stat-label {
        font-size: 0.875rem;
        font-weight: 500;
        opacity: 0.9;
    }
    
    .table th {
        background: #f8f9fa;
        border-bottom: 2px solid #dee2e6;
        font-weight: 600;
        color: #495057;
    }
    
    .btn-group .btn {
        border-radius: 6px;
        margin: 0 1px;
    }
    
    .btn-group .btn:hover {
        transform: translateY(-1px);
        box-shadow: 0 2px 4px rgba(0,0,0,0.15);
    }
    </style>
    
    <script>
    function viewItemDetails(ingredientId) {
        // Show item details modal
        Swal.fire({
            title: 'Item Details',
            text: 'Feature coming soon...',
            icon: 'info',
            confirmButtonColor: '#8B4543'
        });
    }
    
    function adjustStock(ingredientId) {
        // Show stock adjustment modal
        Swal.fire({
            title: 'Adjust Stock',
            text: 'Feature coming soon...',
            icon: 'info',
            confirmButtonColor: '#8B4543'
        });
    }
    
    function requestStock(ingredientId) {
        // Show stock request modal
        Swal.fire({
            title: 'Request Stock',
            text: 'Feature coming soon...',
            icon: 'info',
            confirmButtonColor: '#8B4543'
        });
    }
    </script>
    
    <?php
    
} catch (Exception $e) {
    error_log("Error in get_category_items: " . $e->getMessage());
    echo '<div class="alert alert-danger">Error loading category items</div>';
}
?>
