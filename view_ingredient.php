<?php
require_once 'db_connect.php';
require_once 'auth_function.php';

checkAdminLogin();

if (!isset($_GET['id']) || !is_numeric($_GET['id'])) {
    echo '<div class="alert alert-danger">Invalid ingredient ID.</div>';
    exit;
}

$ingredient_id = intval($_GET['id']);

try {
    // Get ingredient details with category and branch information
    $stmt = $pdo->prepare("
        SELECT i.*, c.category_name, b.branch_name 
        FROM ingredients i 
        LEFT JOIN pos_category c ON i.category_id = c.category_id 
        LEFT JOIN pos_branch b ON i.branch_id = b.branch_id 
        WHERE i.ingredient_id = ?
    ");
    $stmt->execute([$ingredient_id]);
    $ingredient = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$ingredient) {
        echo '<div class="alert alert-danger">Ingredient not found.</div>';
        exit;
    }

    // Format dates
    $date_added = $ingredient['date_added'] ? date('F j, Y', strtotime($ingredient['date_added'])) : 'Not specified';
    $consume_before = $ingredient['consume_before'] ? date('F j, Y', strtotime($ingredient['consume_before'])) : 'Not specified';
    
    // Calculate days until expiry
    $days_until_expiry = '';
    $expiry_class = '';
    if ($ingredient['consume_before']) {
        $expiry_date = new DateTime($ingredient['consume_before']);
        $today = new DateTime();
        $interval = $today->diff($expiry_date);
        if ($interval->invert) {
            $days_until_expiry = 'Expired ' . $interval->days . ' days ago';
            $expiry_class = 'text-danger';
        } else {
            $days_until_expiry = $interval->days . ' days remaining';
            $expiry_class = $interval->days <= 7 ? 'text-warning' : 'text-success';
        }
    }

    // Status badge class and icon
    $status_class = $ingredient['ingredient_status'] === 'Available' ? 'bg-success' : 'bg-danger';
    $status_icon = $ingredient['ingredient_status'] === 'Available' ? 'fa-check-circle' : 'fa-times-circle';
    
} catch (Exception $e) {
    echo '<div class="alert alert-danger">Error loading ingredient details.</div>';
    exit;
}
?>

<div class="enhanced-ingredient-modal">
    <!-- Enhanced Header Section -->
    <div class="modal-hero-section">
        <div class="hero-content">
            <div class="ingredient-avatar">
                <div class="avatar-icon">
                    <i class="fas fa-carrot"></i>
                </div>
                <div class="avatar-glow"></div>
            </div>
            <div class="ingredient-hero-info">
                <h1 class="hero-title"><?php echo htmlspecialchars($ingredient['ingredient_name']); ?></h1>
                <p class="hero-subtitle">Ingredient Details</p>
            </div>
            <div class="status-hero-badge">
                <div class="status-indicator <?php echo $status_class; ?>">
                    <i class="fas <?php echo $status_icon; ?>"></i>
                    <span><?php echo htmlspecialchars($ingredient['ingredient_status']); ?></span>
                </div>
            </div>
        </div>
    </div>

    <!-- Enhanced Content Section -->
    <div class="modal-content-enhanced">
        <div class="content-grid-enhanced">
            <!-- Enhanced Basic Information Card -->
            <div class="enhanced-info-card">
                <div class="card-header-enhanced">
                    <div class="header-icon">
                        <i class="fas fa-info-circle"></i>
                    </div>
                    <h3>Basic Information</h3>
                </div>
                
                <div class="info-list-enhanced">
                    <div class="info-item-enhanced">
                        <div class="item-icon">
                            <i class="fas fa-tags"></i>
                        </div>
                        <div class="item-content">
                            <label>Category</label>
                            <span class="item-value"><?php echo htmlspecialchars($ingredient['category_name'] ?? 'Not assigned'); ?></span>
                        </div>
                    </div>
                    
                    <div class="info-item-enhanced">
                        <div class="item-icon">
                            <i class="fas fa-carrot"></i>
                        </div>
                        <div class="item-content">
                            <label>Ingredient Name</label>
                            <span class="item-value"><?php echo htmlspecialchars($ingredient['ingredient_name']); ?></span>
                        </div>
                    </div>
                    
                    <div class="info-item-enhanced highlight-item">
                        <div class="item-icon">
                            <i class="fas fa-weight-hanging"></i>
                        </div>
                        <div class="item-content">
                            <label>Quantity</label>
                            <div class="quantity-display-enhanced">
                                <span class="quantity-number"><?php echo number_format($ingredient['ingredient_quantity'], 2); ?></span>
                                <span class="quantity-unit"><?php echo htmlspecialchars($ingredient['ingredient_unit']); ?></span>
                            </div>
                        </div>
                    </div>
                    
                    <div class="info-item-enhanced">
                        <div class="item-icon">
                            <i class="fas fa-ruler"></i>
                        </div>
                        <div class="item-content">
                            <label>Unit</label>
                            <span class="item-value"><?php echo htmlspecialchars($ingredient['ingredient_unit']); ?></span>
                        </div>
                    </div>
                </div>
            </div>
            
            <!-- Enhanced Location & Status Card -->
            <div class="enhanced-info-card">
                <div class="card-header-enhanced">
                    <div class="header-icon">
                        <i class="fas fa-map-marker-alt"></i>
                    </div>
                    <h3>Location & Status</h3>
                </div>
                
                <div class="info-list-enhanced">
                    <div class="info-item-enhanced">
                        <div class="item-icon">
                            <i class="fas fa-building"></i>
                        </div>
                        <div class="item-content">
                            <label>Branch</label>
                            <span class="item-value"><?php echo htmlspecialchars($ingredient['branch_name'] ?? 'Not assigned'); ?></span>
                        </div>
                    </div>
                    
                    <div class="info-item-enhanced">
                        <div class="item-icon">
                            <i class="fas fa-calendar-plus"></i>
                        </div>
                        <div class="item-content">
                            <label>Date Added</label>
                            <span class="item-value"><?php echo $date_added; ?></span>
                        </div>
                    </div>
                    
                    <div class="info-item-enhanced">
                        <div class="item-icon">
                            <i class="fas fa-clock"></i>
                        </div>
                        <div class="item-content">
                            <label>Consume Before</label>
                            <div class="consume-info">
                                <span class="consume-date"><?php echo $consume_before; ?></span>
                                <?php if ($days_until_expiry): ?>
                                    <div class="expiry-badge <?php echo $expiry_class; ?>">
                                        <i class="fas fa-exclamation-triangle"></i>
                                        <?php echo $days_until_expiry; ?>
                                    </div>
                                <?php endif; ?>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        
        <!-- Enhanced Notes Section -->
        <?php if ($ingredient['notes']): ?>
        <div class="notes-section-enhanced">
            <div class="enhanced-info-card">
                <div class="card-header-enhanced">
                    <div class="header-icon">
                        <i class="fas fa-sticky-note"></i>
                    </div>
                    <h3>Notes</h3>
                </div>
                <div class="notes-content-enhanced">
                    <?php echo nl2br(htmlspecialchars($ingredient['notes'])); ?>
                </div>
            </div>
        </div>
        <?php endif; ?>
        

    </div>

    <!-- Enhanced Footer Section -->
    <div class="modal-footer-enhanced">
        <button type="button" class="btn-enhanced btn-close-enhanced" data-bs-dismiss="modal">
            <i class="fas fa-times"></i>
            <span>Close</span>
        </button>
    </div>
</div>

<style>
.enhanced-ingredient-modal {
    background: linear-gradient(135deg, #ffffff 0%, #f8f9fa 100%);
    border-radius: 20px;
    overflow: hidden;
    box-shadow: 0 30px 60px rgba(0, 0, 0, 0.12);
    max-width: 900px;
    margin: 0 auto;
    position: relative;
}

/* Enhanced Header Styles */
.modal-hero-section {
    background: linear-gradient(135deg, #8B4543 0%, #A65D5D 50%, #C47A7A 100%);
    color: white;
    padding: 3rem 2.5rem;
    position: relative;
    overflow: hidden;
}

.modal-hero-section::before {
    content: '';
    position: absolute;
    top: 0;
    left: 0;
    right: 0;
    bottom: 0;
    background: url('data:image/svg+xml,<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 100 100"><defs><pattern id="grain" width="100" height="100" patternUnits="userSpaceOnUse"><circle cx="25" cy="25" r="1" fill="rgba(255,255,255,0.1)"/><circle cx="75" cy="75" r="1" fill="rgba(255,255,255,0.1)"/><circle cx="50" cy="10" r="0.5" fill="rgba(255,255,255,0.05)"/><circle cx="10" cy="60" r="0.5" fill="rgba(255,255,255,0.05)"/><circle cx="90" cy="40" r="0.5" fill="rgba(255,255,255,0.05)"/></pattern></defs><rect width="100" height="100" fill="url(%23grain)"/></svg>');
    opacity: 0.3;
}

.hero-content {
    display: flex;
    align-items: center;
    gap: 2rem;
    position: relative;
    z-index: 1;
}

.ingredient-avatar {
    position: relative;
    flex-shrink: 0;
}

.avatar-icon {
    background: rgba(255, 255, 255, 0.25);
    width: 80px;
    height: 80px;
    border-radius: 50%;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 2rem;
    backdrop-filter: blur(20px);
    border: 3px solid rgba(255, 255, 255, 0.3);
    position: relative;
    z-index: 2;
}

.avatar-glow {
    position: absolute;
    top: -10px;
    left: -10px;
    right: -10px;
    bottom: -10px;
    background: radial-gradient(circle, rgba(255,255,255,0.3) 0%, transparent 70%);
    border-radius: 50%;
    animation: pulse 2s ease-in-out infinite;
}

@keyframes pulse {
    0%, 100% { transform: scale(1); opacity: 0.5; }
    50% { transform: scale(1.1); opacity: 0.8; }
}

.ingredient-hero-info {
    flex: 1;
}

.hero-title {
    margin: 0;
    font-size: 2.5rem;
    font-weight: 800;
    text-shadow: 0 4px 8px rgba(0, 0, 0, 0.2);
    line-height: 1.1;
    letter-spacing: -0.5px;
}

.hero-subtitle {
    margin: 0.5rem 0 1rem 0;
    opacity: 0.9;
    font-size: 1.1rem;
    font-weight: 400;
}

.ingredient-meta {
    display: flex;
    gap: 1.5rem;
    flex-wrap: wrap;
}

.meta-item {
    display: flex;
    align-items: center;
    gap: 0.5rem;
    background: rgba(255, 255, 255, 0.2);
    padding: 0.5rem 1rem;
    border-radius: 25px;
    font-size: 0.9rem;
    font-weight: 500;
    backdrop-filter: blur(10px);
    border: 1px solid rgba(255, 255, 255, 0.2);
}

.status-hero-badge {
    margin-left: auto;
}

.status-indicator {
    display: inline-flex;
    align-items: center;
    gap: 0.75rem;
    padding: 1rem 2rem;
    border-radius: 50px;
    font-size: 1rem;
    font-weight: 700;
    box-shadow: 0 8px 20px rgba(0, 0, 0, 0.2);
    text-transform: uppercase;
    letter-spacing: 1px;
    backdrop-filter: blur(10px);
    border: 2px solid rgba(255, 255, 255, 0.3);
    position: relative;
    overflow: hidden;
}

.status-indicator::before {
    content: '';
    position: absolute;
    top: 0;
    left: -100%;
    width: 100%;
    height: 100%;
    background: linear-gradient(90deg, transparent, rgba(255,255,255,0.2), transparent);
    transition: left 0.5s;
}

.status-indicator:hover::before {
    left: 100%;
}

/* Enhanced Content Styles */
.modal-content-enhanced {
    padding: 2.5rem;
    background: #ffffff;
}

.content-grid-enhanced {
    display: grid;
    grid-template-columns: 1fr 1fr;
    gap: 2rem;
    margin-bottom: 2rem;
}

.enhanced-info-card {
    background: linear-gradient(135deg, #ffffff 0%, #f8f9fa 100%);
    border-radius: 16px;
    padding: 2rem;
    box-shadow: 0 8px 25px rgba(0, 0, 0, 0.08);
    border: 1px solid #e9ecef;
    position: relative;
    overflow: hidden;
}

.enhanced-info-card::before {
    content: '';
    position: absolute;
    top: 0;
    left: 0;
    right: 0;
    height: 4px;
    background: linear-gradient(90deg, #8B4543, #A65D5D);
}

.card-header-enhanced {
    display: flex;
    align-items: center;
    gap: 1rem;
    margin-bottom: 2rem;
    padding-bottom: 1rem;
    border-bottom: 2px solid #f1f3f4;
}

.header-icon {
    background: linear-gradient(135deg, #8B4543, #A65D5D);
    width: 50px;
    height: 50px;
    border-radius: 12px;
    display: flex;
    align-items: center;
    justify-content: center;
    color: white;
    font-size: 1.25rem;
    box-shadow: 0 4px 12px rgba(139, 69, 67, 0.3);
}

.card-header-enhanced h3 {
    margin: 0;
    font-weight: 700;
    color: #2c3e50;
    font-size: 1.3rem;
}

.info-list-enhanced {
    display: flex;
    flex-direction: column;
    gap: 1.25rem;
}

.info-item-enhanced {
    display: flex;
    align-items: flex-start;
    gap: 1rem;
    padding: 1.25rem;
    background: #f8f9fa;
    border-radius: 12px;
    transition: all 0.3s ease;
    border: 1px solid #e9ecef;
    position: relative;
    overflow: hidden;
}

.info-item-enhanced::before {
    content: '';
    position: absolute;
    top: 0;
    left: 0;
    width: 4px;
    height: 100%;
    background: linear-gradient(180deg, #8B4543, #A65D5D);
    transform: scaleY(0);
    transition: transform 0.3s ease;
}

.info-item-enhanced:hover {
    background: #ffffff;
    transform: translateY(-2px);
    box-shadow: 0 8px 20px rgba(0, 0, 0, 0.1);
}

.info-item-enhanced:hover::before {
    transform: scaleY(1);
}

.highlight-item {
    background: linear-gradient(135deg, #fff5f5 0%, #fef2f2 100%);
    border: 1px solid #fecaca;
}

.highlight-item:hover {
    background: linear-gradient(135deg, #ffffff 0%, #fef2f2 100%);
}

.item-icon {
    background: linear-gradient(135deg, #8B4543, #A65D5D);
    width: 40px;
    height: 40px;
    border-radius: 10px;
    display: flex;
    align-items: center;
    justify-content: center;
    color: white;
    font-size: 1rem;
    flex-shrink: 0;
    box-shadow: 0 4px 8px rgba(139, 69, 67, 0.2);
}

.item-content {
    flex: 1;
    display: flex;
    flex-direction: column;
    gap: 0.5rem;
}

.item-content label {
    font-weight: 600;
    color: #6c757d;
    font-size: 0.9rem;
    text-transform: uppercase;
    letter-spacing: 0.5px;
}

.item-value {
    color: #2c3e50;
    font-weight: 600;
    font-size: 1rem;
}

.quantity-display-enhanced {
    display: flex;
    align-items: baseline;
    gap: 0.75rem;
}

.quantity-number {
    font-size: 1.4rem;
    font-weight: 800;
    color: #8B4543;
    text-shadow: 0 1px 2px rgba(139, 69, 67, 0.1);
}

.quantity-unit {
    font-size: 0.9rem;
    color: #6c757d;
    background: #e9ecef;
    padding: 0.25rem 0.75rem;
    border-radius: 20px;
    font-weight: 600;
    border: 1px solid #dee2e6;
}

.consume-info {
    display: flex;
    flex-direction: column;
    gap: 0.5rem;
}

.consume-date {
    font-weight: 600;
    color: #2c3e50;
}

.expiry-badge {
    font-size: 0.85rem;
    font-weight: 600;
    padding: 0.5rem 1rem;
    border-radius: 20px;
    display: inline-flex;
    align-items: center;
    gap: 0.5rem;
    width: fit-content;
}

.expiry-badge.text-danger {
    background: linear-gradient(135deg, #fee2e2, #fecaca);
    color: #dc2626;
    border: 1px solid #fca5a5;
}

.expiry-badge.text-warning {
    background: linear-gradient(135deg, #fef3c7, #fde68a);
    color: #d97706;
    border: 1px solid #fbbf24;
}

.expiry-badge.text-success {
    background: linear-gradient(135deg, #dcfce7, #bbf7d0);
    color: #16a34a;
    border: 1px solid #86efac;
}

/* Status Display */
.status-display {
    display: inline-flex;
    align-items: center;
    gap: 0.5rem;
    padding: 0.25rem 0.75rem;
    border-radius: 15px;
    font-size: 0.9rem;
    font-weight: 600;
    text-transform: uppercase;
    letter-spacing: 0.5px;
}

.status-display.bg-success {
    background: linear-gradient(135deg, #dcfce7, #bbf7d0);
    color: #16a34a;
    border: 1px solid #86efac;
}

.status-display.bg-danger {
    background: linear-gradient(135deg, #fee2e2, #fecaca);
    color: #dc2626;
    border: 1px solid #fca5a5;
}

/* Enhanced Notes Section */
.notes-section-enhanced {
    margin-bottom: 2rem;
}

.notes-content-enhanced {
    background: linear-gradient(135deg, #f8f9fa 0%, #ffffff 100%);
    padding: 1.5rem;
    border-radius: 12px;
    color: #2c3e50;
    line-height: 1.7;
    border-left: 4px solid #8B4543;
    font-size: 1rem;
    box-shadow: inset 0 2px 4px rgba(0, 0, 0, 0.05);
}

/* Enhanced Footer Styles */
.modal-footer-enhanced {
    background: linear-gradient(135deg, #f8f9fa 0%, #ffffff 100%);
    padding: 2rem 2.5rem;
    display: flex;
    justify-content: center;
    border-top: 1px solid #e9ecef;
    position: relative;
}

.modal-footer-enhanced::before {
    content: '';
    position: absolute;
    top: 0;
    left: 0;
    right: 0;
    height: 1px;
    background: linear-gradient(90deg, transparent, #8B4543, transparent);
}

.btn-enhanced {
    display: inline-flex;
    align-items: center;
    gap: 0.75rem;
    padding: 1rem 2.5rem;
    border-radius: 12px;
    font-weight: 600;
    font-size: 1rem;
    transition: all 0.3s ease;
    border: none;
    cursor: pointer;
    text-decoration: none;
    position: relative;
    overflow: hidden;
}

.btn-close-enhanced {
    background: linear-gradient(135deg, #6c757d, #495057);
    color: white;
    box-shadow: 0 4px 12px rgba(108, 117, 125, 0.3);
}

.btn-close-enhanced:hover {
    background: linear-gradient(135deg, #495057, #343a40);
    transform: translateY(-2px);
    box-shadow: 0 8px 20px rgba(108, 117, 125, 0.4);
}

.btn-close-enhanced::before {
    content: '';
    position: absolute;
    top: 0;
    left: -100%;
    width: 100%;
    height: 100%;
    background: linear-gradient(90deg, transparent, rgba(255,255,255,0.2), transparent);
    transition: left 0.5s;
}

.btn-close-enhanced:hover::before {
    left: 100%;
}

/* Responsive Design */
@media (max-width: 768px) {
    .hero-content {
        flex-direction: column;
        text-align: center;
        gap: 1.5rem;
    }
    
    .status-hero-badge {
        margin-left: 0;
    }
    
    .content-grid-enhanced {
        grid-template-columns: 1fr;
        gap: 1.5rem;
    }
    
    .info-item-enhanced {
        flex-direction: column;
        align-items: flex-start;
        gap: 1rem;
    }
    
    .quantity-display-enhanced {
        justify-content: flex-start;
    }
    
    .hero-title {
        font-size: 2rem;
    }
    
    .modal-content-enhanced {
        padding: 1.5rem;
    }
    
    .modal-hero-section {
        padding: 2rem 1.5rem;
    }
}

/* Enhanced Animations */
@keyframes slideInUp {
    from {
        opacity: 0;
        transform: translateY(40px);
    }
    to {
        opacity: 1;
        transform: translateY(0);
    }
}

@keyframes fadeInScale {
    from {
        opacity: 0;
        transform: scale(0.9);
    }
    to {
        opacity: 1;
        transform: scale(1);
    }
}

.enhanced-ingredient-modal {
    animation: fadeInScale 0.6s ease-out;
}

.modal-hero-section {
    animation: slideInUp 0.6s ease-out 0.1s both;
}

.enhanced-info-card {
    animation: slideInUp 0.6s ease-out 0.2s both;
}

.enhanced-info-card:nth-child(2) {
    animation-delay: 0.3s;
}

.info-item-enhanced {
    animation: slideInUp 0.4s ease-out 0.4s both;
}

.info-item-enhanced:nth-child(1) { animation-delay: 0.5s; }
.info-item-enhanced:nth-child(2) { animation-delay: 0.6s; }
.info-item-enhanced:nth-child(3) { animation-delay: 0.7s; }
.info-item-enhanced:nth-child(4) { animation-delay: 0.8s; }

.modal-footer-enhanced {
    animation: slideInUp 0.6s ease-out 0.9s both;
}
</style> 