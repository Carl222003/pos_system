<?php
require_once 'db_connect.php';
require_once 'auth_function.php';

// Check if user is logged in and is a stockman
if (!isset($_SESSION['user_logged_in']) || $_SESSION['user_logged_in'] !== true || $_SESSION['user_type'] !== 'Stockman') {
    http_response_code(401);
    echo json_encode(['error' => 'Unauthorized access']);
    exit();
}

if (!isset($_GET['category_id'])) {
    echo '<div class="alert alert-danger">Category ID is required.</div>';
    exit();
}

$category_id = $_GET['category_id'];
$stockman_id = $_SESSION['user_id'];

try {
    // Get category information
    $stmt = $pdo->prepare("
        SELECT category_name 
        FROM pos_category 
        WHERE category_id = ?
    ");
    $stmt->execute([$category_id]);
    $category = $stmt->fetch();
    
    if (!$category) {
        echo '<div class="alert alert-danger">Category not found.</div>';
        exit();
    }
    
    // Get items in this category
    $stmt = $pdo->prepare("
        SELECT 
            i.ingredient_id,
            i.ingredient_name,
            i.current_stock,
            i.minimum_stock,
            i.maximum_stock,
            i.unit,
            i.unit_price,
            CASE 
                WHEN i.current_stock = 0 THEN 'Out of Stock'
                WHEN i.current_stock <= i.minimum_stock THEN 'Low Stock'
                ELSE 'Available'
            END as stock_status
        FROM ingredients i
        WHERE i.category_id = ? 
        AND i.branch_id = (SELECT branch_id FROM pos_user WHERE user_id = ?)
        ORDER BY i.ingredient_name
    ");
    $stmt->execute([$category_id, $stockman_id]);
    $items = $stmt->fetchAll();
    
    if (empty($items)) {
        echo '<div class="alert alert-info">No items found in this category.</div>';
        exit();
    }
    
    // Display category items
    ?>
    <div class="category-items-container">
        <div class="category-header mb-3">
            <h4 class="text-maroon">
                <i class="fas fa-tag me-2"></i>
                <?php echo htmlspecialchars($category['category_name']); ?>
            </h4>
            <p class="text-muted mb-0"><?php echo count($items); ?> items in this category</p>
        </div>
        
        <div class="table-responsive">
            <table class="table table-hover">
                <thead class="table-dark">
                    <tr>
                        <th>Item Name</th>
                        <th>Current Stock</th>
                        <th>Min/Max</th>
                        <th>Unit</th>
                        <th>Status</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($items as $item): ?>
                        <tr>
                            <td>
                                <strong><?php echo htmlspecialchars($item['ingredient_name']); ?></strong>
                            </td>
                            <td>
                                <span class="fw-bold"><?php echo $item['current_stock']; ?></span>
                            </td>
                            <td>
                                <small class="text-muted">
                                    <?php echo $item['minimum_stock']; ?> / <?php echo $item['maximum_stock']; ?>
                                </small>
                            </td>
                            <td>
                                <span class="badge bg-secondary"><?php echo htmlspecialchars($item['unit']); ?></span>
                            </td>
                            <td>
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
                                    case 'Available':
                                        $status_class = 'success';
                                        $status_icon = 'fas fa-check-circle';
                                        break;
                                }
                                ?>
                                <span class="badge bg-<?php echo $status_class; ?>">
                                    <i class="<?php echo $status_icon; ?> me-1"></i>
                                    <?php echo $item['stock_status']; ?>
                                </span>
                            </td>
                            <td>
                                <div class="btn-group btn-group-sm" role="group">
                                    <button class="btn btn-outline-primary" onclick="adjustStock(<?php echo $item['ingredient_id']; ?>)" title="Adjust Stock">
                                        <i class="fas fa-edit"></i>
                                    </button>
                                    <button class="btn btn-outline-info" onclick="viewDetails(<?php echo $item['ingredient_id']; ?>)" title="View Details">
                                        <i class="fas fa-eye"></i>
                                    </button>
                                    <button class="btn btn-outline-warning" onclick="requestStock(<?php echo $item['ingredient_id']; ?>)" title="Request Stock">
                                        <i class="fas fa-plus"></i>
                                    </button>
                                </div>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
        
        <div class="category-summary mt-3">
            <div class="row">
                <div class="col-md-3">
                    <div class="summary-card bg-success text-white">
                        <div class="summary-icon">
                            <i class="fas fa-check-circle"></i>
                        </div>
                        <div class="summary-content">
                            <div class="summary-value">
                                <?php echo count(array_filter($items, function($item) { return $item['stock_status'] === 'Available'; })); ?>
                            </div>
                            <div class="summary-label">Available</div>
                        </div>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="summary-card bg-warning text-dark">
                        <div class="summary-icon">
                            <i class="fas fa-exclamation-triangle"></i>
                        </div>
                        <div class="summary-content">
                            <div class="summary-value">
                                <?php echo count(array_filter($items, function($item) { return $item['stock_status'] === 'Low Stock'; })); ?>
                            </div>
                            <div class="summary-label">Low Stock</div>
                        </div>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="summary-card bg-danger text-white">
                        <div class="summary-icon">
                            <i class="fas fa-times-circle"></i>
                        </div>
                        <div class="summary-content">
                            <div class="summary-value">
                                <?php echo count(array_filter($items, function($item) { return $item['stock_status'] === 'Out of Stock'; })); ?>
                            </div>
                            <div class="summary-label">Out of Stock</div>
                        </div>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="summary-card bg-info text-white">
                        <div class="summary-icon">
                            <i class="fas fa-boxes"></i>
                        </div>
                        <div class="summary-content">
                            <div class="summary-value"><?php echo count($items); ?></div>
                            <div class="summary-label">Total Items</div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <style>
    .text-maroon {
        color: #8B4543 !important;
    }
    
    .category-header {
        border-bottom: 2px solid #e9ecef;
        padding-bottom: 1rem;
    }
    
    .summary-card {
        border-radius: 12px;
        padding: 1rem;
        display: flex;
        align-items: center;
        gap: 1rem;
        box-shadow: 0 4px 12px rgba(0,0,0,0.1);
        transition: transform 0.3s ease;
    }
    
    .summary-card:hover {
        transform: translateY(-2px);
    }
    
    .summary-icon {
        font-size: 2rem;
        opacity: 0.8;
    }
    
    .summary-value {
        font-size: 1.5rem;
        font-weight: 800;
        line-height: 1;
    }
    
    .summary-label {
        font-size: 0.9rem;
        opacity: 0.9;
        margin-top: 0.2rem;
    }
    
    .table-dark {
        background: linear-gradient(135deg, #8B4543 0%, #b97a6a 100%);
        color: white;
    }
    
    .table-dark th {
        border: none;
        font-weight: 600;
        text-transform: uppercase;
        font-size: 0.8rem;
        letter-spacing: 0.5px;
    }
    
    .btn-group-sm .btn {
        padding: 0.25rem 0.5rem;
        font-size: 0.8rem;
    }
    </style>
    <?php
    
} catch (PDOException $e) {
    echo '<div class="alert alert-danger">Database error: ' . htmlspecialchars($e->getMessage()) . '</div>';
} catch (Exception $e) {
    echo '<div class="alert alert-danger">Server error: ' . htmlspecialchars($e->getMessage()) . '</div>';
}
?>
