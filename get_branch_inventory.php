<?php
require_once 'db_connect.php';
require_once 'auth_function.php';

checkAdminLogin();

$branch_id = $_GET['id'] ?? null;

if (!$branch_id) {
    echo '<div class="alert alert-danger">Invalid branch ID</div>';
    exit();
}

// Get branch information
$stmt = $pdo->prepare("SELECT branch_name FROM pos_branch WHERE branch_id = ?");
$stmt->execute([$branch_id]);
$branch = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$branch) {
    echo '<div class="alert alert-danger">Branch not found</div>';
    exit();
}

// Get inventory items for this branch
    $stmt = $pdo->prepare("
        SELECT 
            i.ingredient_id,
            i.ingredient_name,
            i.ingredient_quantity,
            i.ingredient_unit,
            i.minimum_stock,
            i.consume_before,
            i.ingredient_status,
            c.category_name,
            CASE 
                WHEN i.ingredient_quantity = 0 THEN 'Out of Stock'
                WHEN i.consume_before IS NOT NULL AND i.consume_before <= CURDATE() THEN 'Out of Stock'
                WHEN i.ingredient_quantity <= i.minimum_stock THEN 'Low Stock'
                ELSE 'Adequate'
            END as stock_status
        FROM ingredients i
        LEFT JOIN pos_category c ON i.category_id = c.category_id
        WHERE i.branch_id = ?
        ORDER BY c.category_name, i.ingredient_name
    ");
$stmt->execute([$branch_id]);
$inventory = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Group by category
$categories = [];
foreach ($inventory as $item) {
    $category = $item['category_name'] ?: 'Uncategorized';
    if (!isset($categories[$category])) {
        $categories[$category] = [];
    }
    $categories[$category][] = $item;
}
?>

<div class="container-fluid">
    <div class="row mb-3">
        <div class="col-12">
            <h4 class="text-primary">
                <i class="fas fa-boxes me-2"></i>
                <?php echo htmlspecialchars($branch['branch_name']); ?> - Inventory
            </h4>
        </div>
    </div>

    <?php if (empty($categories)): ?>
        <div class="alert alert-info">
            <i class="fas fa-info-circle me-2"></i>
            No inventory items found for this branch.
        </div>
    <?php else: ?>
        <?php foreach ($categories as $category_name => $items): ?>
            <div class="card mb-3">
                <div class="card-header">
                    <h6 class="mb-0">
                        <i class="fas fa-tag me-2"></i>
                        <?php echo htmlspecialchars($category_name); ?>
                        <span class="badge bg-secondary ms-2"><?php echo count($items); ?> items</span>
                    </h6>
                </div>
                <div class="card-body p-0">
                    <div class="table-responsive">
                        <table class="table table-hover mb-0">
                            <thead class="table-light">
                                <tr>
                                    <th>Item</th>
                                    <th class="text-center">Current Stock</th>
                                    <th class="text-center">Minimum Stock</th>
                                    <th class="text-center">Status</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($items as $item): ?>
                                    <?php
                                    $status_class = '';
                                    $status_icon = '';
                                    switch ($item['stock_status']) {
                                        case 'Out of Stock':
                                            $status_class = 'danger';
                                            $status_icon = 'fas fa-times-circle';
                                            break;
                                        case 'Low Stock':
                                            $status_class = 'warning';
                                            $status_icon = 'fas fa-exclamation-triangle';
                                            break;
                                        default:
                                            $status_class = 'success';
                                            $status_icon = 'fas fa-check-circle';
                                    }
                                    ?>
                                    <tr>
                                        <td>
                                            <strong><?php echo htmlspecialchars($item['ingredient_name']); ?></strong>
                                            <br>
                                            <small class="text-muted"><?php echo htmlspecialchars($item['ingredient_unit']); ?></small>
                                        </td>
                                        <td class="text-center">
                                            <span class="fw-bold"><?php echo number_format($item['ingredient_quantity'], 2); ?></span>
                                        </td>
                                        <td class="text-center">
                                            <span class="text-muted"><?php echo number_format($item['minimum_stock'], 2); ?></span>
                                        </td>
                                        <td class="text-center">
                                            <span class="badge bg-<?php echo $status_class; ?>">
                                                <i class="<?php echo $status_icon; ?> me-1"></i>
                                                <?php echo $item['stock_status']; ?>
                                            </span>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        <?php endforeach; ?>
    <?php endif; ?>
</div> 