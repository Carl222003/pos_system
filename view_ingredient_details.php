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
           i.ingredient_status, i.category_id, c.category_name, i.date_added, i.consume_before, i.notes,
           b.branch_name
    FROM ingredients i
    LEFT JOIN pos_category c ON i.category_id = c.category_id
    LEFT JOIN pos_branch b ON i.branch_id = b.branch_id
    WHERE i.ingredient_id = ? AND i.branch_id = ?
");
$stmt->execute([$ingredient_id, $branch_id]);
$ingredient = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$ingredient) {
    echo '<div class="alert alert-danger">Ingredient not found or not accessible</div>';
    exit();
}

// Get recent stock adjustments
$stmt = $pdo->prepare("
    SELECT sa.adjustment_type, sa.old_quantity, sa.new_quantity, sa.adjustment_quantity, 
           sa.old_status, sa.new_status, sa.reason, sa.adjustment_date, u.user_name
    FROM stock_adjustments sa
    LEFT JOIN pos_user u ON sa.stockman_id = u.user_id
    WHERE sa.ingredient_id = ? AND sa.branch_id = ?
    ORDER BY sa.adjustment_date DESC
    LIMIT 5
");
$stmt->execute([$ingredient_id, $branch_id]);
$adjustments = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Get recent ingredient requests
$stmt = $pdo->prepare("
    SELECT ir.request_date, ir.status, ir.notes, u.user_name
    FROM ingredient_requests ir
    LEFT JOIN pos_user u ON ir.updated_by = u.user_id
    WHERE ir.branch_id = ? AND ir.ingredients LIKE ?
    ORDER BY ir.request_date DESC
    LIMIT 3
");
$stmt->execute([$branch_id, '%' . $ingredient_id . '%']);
$requests = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>

<div class="modal-header bg-maroon text-white">
    <h5 class="modal-title">
        <i class="fas fa-eye me-2"></i>Ingredient Details: <?php echo htmlspecialchars($ingredient['ingredient_name']); ?>
    </h5>
    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
</div>

<div class="modal-body">
    <div class="row">
        <!-- Basic Information -->
        <div class="col-md-6">
            <div class="card border-0 shadow-sm">
                <div class="card-header bg-light">
                    <h6 class="mb-0"><i class="fas fa-info-circle me-2"></i>Basic Information</h6>
                </div>
                <div class="card-body">
                    <table class="table table-borderless">
                        <tr>
                            <td class="fw-medium">Name:</td>
                            <td><?php echo htmlspecialchars($ingredient['ingredient_name']); ?></td>
                        </tr>
                        <tr>
                            <td class="fw-medium">Category:</td>
                            <td><?php echo htmlspecialchars($ingredient['category_name']); ?></td>
                        </tr>
                        <tr>
                            <td class="fw-medium">Branch:</td>
                            <td><?php echo htmlspecialchars($ingredient['branch_name']); ?></td>
                        </tr>
                        <tr>
                            <td class="fw-medium">Current Stock:</td>
                            <td>
                                <span class="fw-bold text-primary"><?php echo $ingredient['ingredient_quantity']; ?> <?php echo htmlspecialchars($ingredient['ingredient_unit']); ?></span>
                            </td>
                        </tr>
                        <tr>
                            <td class="fw-medium">Status:</td>
                            <td>
                                <?php 
                                $statusClass = '';
                                switch($ingredient['ingredient_status']) {
                                    case 'Available':
                                        $statusClass = 'bg-success';
                                        break;
                                    case 'Low Stock':
                                        $statusClass = 'bg-warning';
                                        break;
                                    case 'Out of Stock':
                                        $statusClass = 'bg-danger';
                                        break;
                                    default:
                                        $statusClass = 'bg-secondary';
                                }
                                ?>
                                <span class="badge <?php echo $statusClass; ?>"><?php echo htmlspecialchars($ingredient['ingredient_status']); ?></span>
                            </td>
                        </tr>
                        <tr>
                            <td class="fw-medium">Date Added:</td>
                            <td><?php echo date('M d, Y', strtotime($ingredient['date_added'])); ?></td>
                        </tr>
                        <?php if ($ingredient['consume_before']): ?>
                        <tr>
                            <td class="fw-medium">Consume Before:</td>
                            <td>
                                <?php 
                                $consumeDate = strtotime($ingredient['consume_before']);
                                $daysLeft = ceil(($consumeDate - time()) / (60 * 60 * 24));
                                $dateClass = $daysLeft <= 7 ? 'text-danger' : ($daysLeft <= 14 ? 'text-warning' : 'text-success');
                                ?>
                                <span class="<?php echo $dateClass; ?>">
                                    <?php echo date('M d, Y', $consumeDate); ?>
                                    (<?php echo $daysLeft; ?> days left)
                                </span>
                            </td>
                        </tr>
                        <?php endif; ?>
                        <?php if ($ingredient['notes']): ?>
                        <tr>
                            <td class="fw-medium">Notes:</td>
                            <td><?php echo htmlspecialchars($ingredient['notes']); ?></td>
                        </tr>
                        <?php endif; ?>
                    </table>
                </div>
            </div>
        </div>

        <!-- Recent Activity -->
        <div class="col-md-6">
            <div class="card border-0 shadow-sm">
                <div class="card-header bg-light">
                    <h6 class="mb-0"><i class="fas fa-history me-2"></i>Recent Stock Adjustments</h6>
                </div>
                <div class="card-body">
                    <?php if (empty($adjustments)): ?>
                        <p class="text-muted text-center">No recent adjustments</p>
                    <?php else: ?>
                        <div class="timeline">
                            <?php foreach ($adjustments as $adjustment): ?>
                                <div class="timeline-item mb-3">
                                    <div class="d-flex align-items-start">
                                        <div class="timeline-icon me-3">
                                            <?php 
                                            $icon = '';
                                            $color = '';
                                            switch($adjustment['adjustment_type']) {
                                                case 'add':
                                                    $icon = 'fa-plus';
                                                    $color = 'text-success';
                                                    break;
                                                case 'subtract':
                                                    $icon = 'fa-minus';
                                                    $color = 'text-danger';
                                                    break;
                                                case 'set':
                                                    $icon = 'fa-edit';
                                                    $color = 'text-primary';
                                                    break;
                                            }
                                            ?>
                                            <i class="fas <?php echo $icon; ?> <?php echo $color; ?>"></i>
                                        </div>
                                        <div class="timeline-content flex-grow-1">
                                            <div class="fw-bold">
                                                <?php echo ucfirst($adjustment['adjustment_type']); ?>: 
                                                <?php echo $adjustment['adjustment_quantity']; ?> <?php echo htmlspecialchars($ingredient['ingredient_unit']); ?>
                                                <?php if ($adjustment['old_status'] && $adjustment['new_status'] && $adjustment['old_status'] !== $adjustment['new_status']): ?>
                                                    <br><small class="text-info">Status: <?php echo $adjustment['old_status']; ?> → <?php echo $adjustment['new_status']; ?></small>
                                                <?php endif; ?>
                                            </div>
                                            <div class="text-muted small">
                                                <?php echo $adjustment['reason']; ?>
                                            </div>
                                            <div class="text-muted small">
                                                <?php echo date('M d, Y H:i', strtotime($adjustment['adjustment_date'])); ?> 
                                                by <?php echo htmlspecialchars($adjustment['user_name']); ?>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            <?php endforeach; ?>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>

    <!-- Recent Requests -->
    <?php if (!empty($requests)): ?>
    <div class="row mt-3">
        <div class="col-12">
            <div class="card border-0 shadow-sm">
                <div class="card-header bg-light">
                    <h6 class="mb-0"><i class="fas fa-clipboard-list me-2"></i>Recent Requests</h6>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-sm">
                            <thead>
                                <tr>
                                    <th>Date</th>
                                    <th>Status</th>
                                    <th>Notes</th>
                                    <th>Updated By</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($requests as $request): ?>
                                    <tr>
                                        <td><?php echo date('M d, Y', strtotime($request['request_date'])); ?></td>
                                        <td>
                                            <?php 
                                            $statusClass = '';
                                            switch($request['status']) {
                                                case 'pending':
                                                    $statusClass = 'bg-warning';
                                                    break;
                                                case 'approved':
                                                    $statusClass = 'bg-success';
                                                    break;
                                                case 'declined':
                                                    $statusClass = 'bg-danger';
                                                    break;
                                                default:
                                                    $statusClass = 'bg-secondary';
                                            }
                                            ?>
                                            <span class="badge <?php echo $statusClass; ?>"><?php echo ucfirst($request['status']); ?></span>
                                        </td>
                                        <td><?php echo htmlspecialchars($request['notes'] ?? ''); ?></td>
                                        <td><?php echo htmlspecialchars($request['user_name']); ?></td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <?php endif; ?>
</div>

<div class="modal-footer">
    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">
        <i class="fas fa-times me-1"></i>Cancel
    </button>
    <button type="button" class="btn btn-maroon" onclick="adjustStock(<?php echo $ingredient['ingredient_id']; ?>)">
        <i class="fas fa-edit me-1"></i>Adjust Stock
    </button>
</div>

<style>
.timeline-icon {
    width: 30px;
    height: 30px;
    border-radius: 50%;
    background: #f8f9fa;
    display: flex;
    align-items: center;
    justify-content: center;
    flex-shrink: 0;
}
.timeline-item {
    border-left: 2px solid #e9ecef;
    padding-left: 15px;
    margin-left: 15px;
}
</style> 