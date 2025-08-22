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
           b.branch_name, i.minimum_stock
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

<!-- Modern Formal Modal Header -->
<div class="formal-modal-header">
    <div class="header-left">
        <div class="ingredient-icon">
            <i class="fas fa-cube"></i>
        </div>
        <div class="header-info">
            <h4 class="ingredient-title"><?php echo htmlspecialchars($ingredient['ingredient_name']); ?></h4>
            <p class="ingredient-subtitle">
                <i class="fas fa-building me-1"></i>
                <?php echo htmlspecialchars($ingredient['branch_name']); ?>
            </p>
        </div>
    </div>
    <div class="header-right">
        <div class="status-indicator-header <?php echo strtolower($ingredient['ingredient_status']); ?>">
            <?php if ($ingredient['ingredient_status'] === 'Available'): ?>
                <i class="fas fa-check-circle"></i>
            <?php else: ?>
                <i class="fas fa-exclamation-triangle"></i>
            <?php endif; ?>
            <?php echo $ingredient['ingredient_status']; ?>
        </div>
        <button type="button" class="btn-close-formal" data-bs-dismiss="modal">
            <i class="fas fa-times"></i>
        </button>
    </div>
</div>

<!-- Formal Modal Body -->
<div class="formal-modal-body">
    <div class="formal-grid">
        <!-- Left Column: Basic Information -->
        <div class="formal-section">
            <div class="section-card">
                <div class="section-header">
                    <i class="fas fa-info-circle"></i>
                    <h5>Basic Information</h5>
                </div>
                <div class="section-content">
                    <div class="formal-info-grid">
                        <div class="formal-info-row">
                            <span class="formal-label">
                                <i class="fas fa-tag"></i>
                                Ingredient Name
                            </span>
                            <span class="formal-value">
                                <?php echo htmlspecialchars($ingredient['ingredient_name']); ?>
                            </span>
                        </div>
                        
                        <div class="formal-info-row">
                            <span class="formal-label">
                                <i class="fas fa-list"></i>
                                Category
                            </span>
                            <span class="formal-value">
                                <span class="formal-badge category">
                                    <?php echo htmlspecialchars($ingredient['category_name']); ?>
                                </span>
                            </span>
                        </div>
                        
                        <div class="formal-info-row highlight-row">
                            <span class="formal-label">
                                <i class="fas fa-cubes"></i>
                                Current Stock
                            </span>
                            <span class="formal-value">
                                <span class="formal-stock-badge">
                                    <?php echo $ingredient['ingredient_quantity']; ?>
                                    <?php echo htmlspecialchars($ingredient['ingredient_unit']); ?>
                                </span>
                            </span>
                        </div>
                        
                        <div class="formal-info-row">
                            <span class="formal-label">
                                <i class="fas fa-calendar-plus"></i>
                                Date Added
                            </span>
                            <span class="formal-value">
                                <?php echo date('M d, Y', strtotime($ingredient['date_added'])); ?>
                            </span>
                        </div>
                        
                        <?php if ($ingredient['consume_before']): ?>
                        <div class="formal-info-row <?php echo (strtotime($ingredient['consume_before']) < strtotime('+7 days')) ? 'warning-row' : ''; ?>">
                            <span class="formal-label">
                                <i class="fas fa-calendar-times"></i>
                                Expiry Date
                            </span>
                            <span class="formal-value">
                                <?php 
                                $expire_date = strtotime($ingredient['consume_before']);
                                $days_left = ceil(($expire_date - time()) / (60 * 60 * 24));
                                echo date('M d, Y', $expire_date);
                                if ($days_left <= 7 && $days_left > 0) {
                                    echo " <span class='formal-warning'>({$days_left} days left)</span>";
                                } elseif ($days_left <= 0) {
                                    echo " <span class='formal-danger'>(Expired)</span>";
                                }
                                ?>
                            </span>
                        </div>
                        <?php endif; ?>
                        
                        <?php if ($ingredient['notes']): ?>
                        <div class="formal-info-row">
                            <span class="formal-label">
                                <i class="fas fa-sticky-note"></i>
                                Notes
                            </span>
                            <span class="formal-value">
                                <div class="formal-notes">
                                    <?php echo htmlspecialchars($ingredient['notes']); ?>
                                </div>
                            </span>
                        </div>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </div>
        
        <!-- Right Column: Recent Activity -->
        <div class="formal-section">
            <div class="section-card">
                <div class="section-header">
                    <i class="fas fa-history"></i>
                    <h5>Recent Stock Adjustments</h5>
                </div>
                <div class="section-content">
                    <?php if (empty($adjustments)): ?>
                        <div class="formal-empty-state">
                            <div class="empty-icon">
                                <i class="fas fa-clock"></i>
                            </div>
                            <p>No recent adjustments</p>
                        </div>
                    <?php else: ?>
                        <div class="formal-timeline">
                            <?php foreach ($adjustments as $adjustment): ?>
                                <div class="formal-timeline-item">
                                    <div class="timeline-dot"></div>
                                    <div class="timeline-content">
                                        <div class="timeline-header">
                                            <span class="timeline-type">
                                                <?php echo ucfirst($adjustment['adjustment_type']); ?>
                                            </span>
                                            <span class="timeline-date">
                                                <?php echo date('M d, Y', strtotime($adjustment['adjustment_date'])); ?>
                                            </span>
                                        </div>
                                        <div class="timeline-details">
                                            <span class="quantity-change">
                                                <?php echo $adjustment['old_quantity']; ?> → <?php echo $adjustment['new_quantity']; ?>
                                            </span>
                                            <?php if ($adjustment['reason']): ?>
                                                <p class="timeline-reason"><?php echo htmlspecialchars($adjustment['reason']); ?></p>
                                            <?php endif; ?>
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
    
    <!-- Full Width: Recent Requests -->
    <?php if (!empty($requests)): ?>
    <div class="formal-section full-width">
        <div class="section-card">
            <div class="section-header">
                <i class="fas fa-clipboard-list"></i>
                <h5>Recent Requests</h5>
            </div>
            <div class="section-content">
                <div class="formal-table-container">
                    <table class="formal-table">
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
                                        <span class="formal-status-badge <?php echo strtolower($request['status']); ?>">
                                            <?php echo ucfirst($request['status']); ?>
                                        </span>
                                    </td>
                                    <td><?php echo htmlspecialchars($request['notes'] ?: 'No notes'); ?></td>
                                    <td><?php echo htmlspecialchars($request['user_name'] ?: 'N/A'); ?></td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
    <?php endif; ?>
</div>

<!-- Formal Modal Footer -->
<div class="formal-modal-footer">
    <button type="button" class="formal-btn formal-btn-secondary" data-bs-dismiss="modal">
        <i class="fas fa-times me-2"></i>
        Close
    </button>
</div>

<style>
/* Formal Modal Styling */
.formal-modal-header {
    background: linear-gradient(135deg, #8B4543 0%, #A65D5D 50%, #8B4543 100%);
    color: white;
    padding: 1.5rem 2rem;
    display: flex;
    justify-content: space-between;
    align-items: center;
    border-radius: 20px 20px 0 0;
    position: relative;
    overflow: hidden;
    margin: 0;
    border: none;
}

.formal-modal-header::before {
    content: '';
    position: absolute;
    top: 0;
    left: 0;
    right: 0;
    bottom: 0;
    background: linear-gradient(45deg, rgba(255,255,255,0.1) 0%, transparent 50%);
    pointer-events: none;
}

.header-left {
    display: flex;
    align-items: center;
    gap: 1rem;
}

.ingredient-icon {
    width: 50px;
    height: 50px;
    background: rgba(255, 255, 255, 0.25);
    border-radius: 12px;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 1.5rem;
    color: white;
    backdrop-filter: blur(10px);
    box-shadow: 0 4px 15px rgba(0, 0, 0, 0.2);
    border: 1px solid rgba(255, 255, 255, 0.3);
}

.ingredient-title {
    font-size: 1.5rem;
    font-weight: 700;
    margin: 0;
    color: white;
    text-shadow: 0 2px 4px rgba(0, 0, 0, 0.3);
}

.ingredient-subtitle {
    font-size: 0.9rem;
    margin: 0;
    color: rgba(255, 255, 255, 0.9);
    text-shadow: 0 1px 2px rgba(0, 0, 0, 0.2);
}

.header-right {
    display: flex;
    align-items: center;
    gap: 1rem;
}

.status-indicator-header {
    padding: 0.5rem 1rem;
    border-radius: 20px;
    font-weight: 600;
    font-size: 0.9rem;
    display: flex;
    align-items: center;
    gap: 0.5rem;
}

.status-indicator-header.available {
    background: #28a745;
    color: white;
}

.status-indicator-header.unavailable {
    background: #dc3545;
    color: white;
}

.btn-close-formal {
    background: rgba(255, 255, 255, 0.2);
    border: none;
    color: white;
    width: 40px;
    height: 40px;
    border-radius: 10px;
    display: flex;
    align-items: center;
    justify-content: center;
    transition: all 0.3s ease;
    backdrop-filter: blur(10px);
    border: 1px solid rgba(255, 255, 255, 0.3);
}

.btn-close-formal:hover {
    background: rgba(255, 255, 255, 0.35);
    transform: scale(1.1);
    box-shadow: 0 4px 15px rgba(0, 0, 0, 0.3);
}

.formal-modal-body {
    padding: 2rem;
    background: #f8f9fa;
}

.formal-grid {
    display: grid;
    grid-template-columns: 1fr 1fr;
    gap: 2rem;
    margin-bottom: 2rem;
}

.formal-section {
    display: flex;
    flex-direction: column;
}

.formal-section.full-width {
    grid-column: 1 / -1;
}

.section-card {
    background: white;
    border-radius: 12px;
    box-shadow: 0 4px 20px rgba(0, 0, 0, 0.08);
    border: 1px solid #e9ecef;
    overflow: hidden;
    height: 100%;
}

.section-header {
    background: linear-gradient(135deg, #8B4543, #A65D5D);
    color: white;
    padding: 1rem 1.5rem;
    display: flex;
    align-items: center;
    gap: 0.75rem;
    border-bottom: 1px solid rgba(255, 255, 255, 0.1);
}

.section-header h5 {
    margin: 0;
    font-weight: 600;
    font-size: 1.1rem;
}

.section-content {
    padding: 1.5rem;
    flex: 1;
}

.formal-info-grid {
    display: flex;
    flex-direction: column;
    gap: 1rem;
}

.formal-info-row {
    display: flex;
    justify-content: space-between;
    align-items: center;
    padding: 0.75rem 1rem;
    border-radius: 8px;
    border: 1px solid #f0f0f0;
    transition: all 0.3s ease;
}

.formal-info-row:hover {
    background: #f8f9fa;
    border-color: #8B4543;
}

.formal-info-row.highlight-row {
    background: linear-gradient(135deg, rgba(139, 69, 67, 0.05), rgba(139, 69, 67, 0.02));
    border: 2px solid #8B4543;
}

.formal-info-row.warning-row {
    background: linear-gradient(135deg, rgba(255, 193, 7, 0.1), rgba(255, 193, 7, 0.05));
    border: 1px solid rgba(255, 193, 7, 0.3);
}

.formal-label {
    color: #495057;
    font-weight: 600;
    font-size: 0.9rem;
    display: flex;
    align-items: center;
    gap: 0.5rem;
}

.formal-value {
    color: #2c3e50;
    font-weight: 600;
    text-align: right;
}

.formal-badge.category {
    background: linear-gradient(135deg, #8B4543, #A65D5D);
    color: white;
    padding: 0.3rem 0.8rem;
    border-radius: 15px;
    font-size: 0.8rem;
    font-weight: 600;
}

.formal-stock-badge {
    background: linear-gradient(135deg, #007bff, #0056b3);
    color: white;
    padding: 0.5rem 1rem;
    border-radius: 20px;
    font-weight: 700;
    font-size: 1rem;
}

.formal-notes {
    background: #f8f9fa;
    padding: 0.75rem;
    border-radius: 6px;
    border-left: 3px solid #8B4543;
    font-style: italic;
    color: #495057;
    margin-top: 0.5rem;
}

.formal-warning {
    color: #856404;
    font-weight: 600;
}

.formal-danger {
    color: #721c24;
    font-weight: 600;
}

.formal-empty-state {
    text-align: center;
    padding: 2rem 1rem;
    color: #6c757d;
}

.empty-icon {
    width: 50px;
    height: 50px;
    background: #f8f9fa;
    border-radius: 50%;
    display: flex;
    align-items: center;
    justify-content: center;
    margin: 0 auto 1rem;
    font-size: 1.5rem;
    color: #adb5bd;
}

.formal-timeline {
    position: relative;
}

.formal-timeline::before {
    content: '';
    position: absolute;
    left: 15px;
    top: 0;
    bottom: 0;
    width: 2px;
    background: #dee2e6;
}

.formal-timeline-item {
    position: relative;
    padding-left: 2.5rem;
    margin-bottom: 1.5rem;
}

.timeline-dot {
    position: absolute;
    left: 9px;
    top: 8px;
    width: 12px;
    height: 12px;
    background: #8B4543;
    border-radius: 50%;
    border: 3px solid white;
    box-shadow: 0 0 0 2px #dee2e6;
}

.timeline-content {
    background: #f8f9fa;
    padding: 1rem;
    border-radius: 8px;
    border: 1px solid #e9ecef;
}

.timeline-header {
    display: flex;
    justify-content: space-between;
    align-items: center;
    margin-bottom: 0.5rem;
}

.timeline-type {
    font-weight: 600;
    color: #8B4543;
    text-transform: capitalize;
}

.timeline-date {
    font-size: 0.85rem;
    color: #6c757d;
}

.quantity-change {
    background: #e3f2fd;
    color: #1976d2;
    padding: 0.25rem 0.5rem;
    border-radius: 12px;
    font-weight: 600;
    font-size: 0.9rem;
}

.timeline-reason {
    margin-top: 0.5rem;
    margin-bottom: 0;
    font-size: 0.9rem;
    color: #495057;
    font-style: italic;
}

.formal-table-container {
    border-radius: 8px;
    overflow: hidden;
    border: 1px solid #e9ecef;
}

.formal-table {
    width: 100%;
    margin: 0;
    border-collapse: collapse;
}

.formal-table th {
    background: linear-gradient(135deg, #8B4543, #A65D5D);
    color: white;
    padding: 1rem;
    font-weight: 600;
    text-align: left;
    border: none;
    font-size: 0.9rem;
}

.formal-table td {
    padding: 1rem;
    border-bottom: 1px solid #f0f0f0;
    vertical-align: middle;
}

.formal-table tr:hover {
    background: #f8f9fa;
}

.formal-status-badge {
    padding: 0.4rem 0.8rem;
    border-radius: 15px;
    font-weight: 600;
    font-size: 0.8rem;
    text-transform: uppercase;
}

.formal-status-badge.pending {
    background: #ffc107;
    color: #212529;
}

.formal-status-badge.approved {
    background: #28a745;
    color: white;
}

.formal-status-badge.rejected {
    background: #dc3545;
    color: white;
}

.formal-modal-footer {
    background: white;
    padding: 1.5rem 2rem;
    border-top: 1px solid #e9ecef;
    display: flex;
    justify-content: flex-end;
    gap: 1rem;
    border-radius: 0 0 20px 20px;
    margin: 0;
}

.formal-btn {
    padding: 0.75rem 1.5rem;
    border-radius: 8px;
    font-weight: 600;
    font-size: 0.95rem;
    transition: all 0.3s ease;
    border: none;
    display: flex;
    align-items: center;
    gap: 0.5rem;
}

.formal-btn-secondary {
    background: #6c757d;
    color: white;
}

.formal-btn-secondary:hover {
    background: #5a6268;
    transform: translateY(-1px);
}

.formal-btn-primary {
    background: linear-gradient(135deg, #8B4543, #A65D5D);
    color: white;
}

.formal-btn-primary:hover {
    background: linear-gradient(135deg, #723836, #8B4543);
    transform: translateY(-1px);
    box-shadow: 0 4px 12px rgba(139, 69, 67, 0.3);
}

/* Responsive Design */
@media (max-width: 768px) {
    .formal-grid {
        grid-template-columns: 1fr;
        gap: 1.5rem;
    }
    
    .formal-modal-body {
        padding: 1.5rem;
    }
    
    .formal-info-row {
        flex-direction: column;
        align-items: flex-start;
        gap: 0.5rem;
    }
    
    .formal-value {
        text-align: left;
    }
}
</style>
