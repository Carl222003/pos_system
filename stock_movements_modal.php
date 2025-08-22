<?php
require_once 'db_connect.php';
require_once 'auth_function.php';

if (!isset($_SESSION['user_logged_in']) || $_SESSION['user_type'] !== 'Stockman') {
    header('Location: login.php');
    exit();
}

$ingredient_id = $_GET['id'] ?? null;
$branch_id = $_SESSION['branch_id'];

if (!$ingredient_id) {
    echo '<div class="alert alert-danger">Invalid ingredient ID</div>';
    exit();
}

// Get ingredient details
$stmt = $pdo->prepare("SELECT ingredient_name, ingredient_quantity, ingredient_unit FROM ingredients WHERE ingredient_id = ? AND branch_id = ?");
$stmt->execute([$ingredient_id, $branch_id]);
$ingredient = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$ingredient) {
    echo '<div class="alert alert-danger">Ingredient not found</div>';
    exit();
}

// Get stock movements
$stmt = $pdo->prepare("SELECT movement_type, quantity, previous_stock, new_stock, reason, created_at FROM pos_stock_movement WHERE ingredient_id = ? ORDER BY created_at DESC LIMIT 50");
$stmt->execute([$ingredient_id]);
$movements = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>

<div class="modal-header bg-maroon text-white">
    <h5 class="modal-title">
        <i class="fas fa-history me-2"></i>Stock History: <?php echo htmlspecialchars($ingredient['ingredient_name']); ?>
    </h5>
    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
</div>

<div class="modal-body">
    <div class="row mb-3">
        <div class="col-md-6">
            <div class="card bg-light">
                <div class="card-body">
                    <h6>Current Stock: <strong><?php echo $ingredient['ingredient_quantity'] . ' ' . $ingredient['ingredient_unit']; ?></strong></h6>
                </div>
            </div>
        </div>
    </div>
    
    <h6>Movement History</h6>
    <?php if (empty($movements)): ?>
        <p class="text-muted">No movements recorded yet.</p>
    <?php else: ?>
        <div class="table-responsive">
            <table class="table table-sm">
                <thead>
                    <tr>
                        <th>Date</th>
                        <th>Type</th>
                        <th>Quantity</th>
                        <th>Previous</th>
                        <th>New</th>
                        <th>Reason</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($movements as $movement): ?>
                        <tr>
                            <td><?php echo date('M d, Y H:i', strtotime($movement['created_at'])); ?></td>
                            <td>
                                <span class="badge bg-<?php echo $movement['movement_type'] === 'add' ? 'success' : ($movement['movement_type'] === 'subtract' ? 'warning' : 'info'); ?>">
                                    <?php echo ucfirst($movement['movement_type']); ?>
                                </span>
                            </td>
                            <td><?php echo $movement['quantity']; ?></td>
                            <td><?php echo $movement['previous_stock']; ?></td>
                            <td><?php echo $movement['new_stock']; ?></td>
                            <td><?php echo htmlspecialchars($movement['reason'] ?? '-'); ?></td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    <?php endif; ?>
</div>

<div class="modal-footer">
    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
    <button type="button" class="btn btn-maroon" onclick="adjustStock(<?php echo $ingredient_id; ?>)">Adjust Stock</button>
</div>
