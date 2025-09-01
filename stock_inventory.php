<?php
require_once 'db_connect.php';
require_once 'auth_function.php';

// Check if user is logged in and is a stockman
if (!isset($_SESSION['user_logged_in']) || $_SESSION['user_logged_in'] !== true || $_SESSION['user_type'] !== 'Stockman') {
    header('Location: login.php');
    exit();
}

$branch_id = $_SESSION['branch_id'];

// Get branch information
$branch_stmt = $pdo->prepare("SELECT branch_name FROM pos_branch WHERE branch_id = ?");
$branch_stmt->execute([$branch_id]);
$branch = $branch_stmt->fetch(PDO::FETCH_ASSOC);
$branch_name = $branch['branch_name'] ?? 'Unknown Branch';

include('header.php');
?>

<div class="stock-inventory-bg">
    <div class="container-fluid px-4">
        <!-- Page Header -->
        <div class="inventory-header-enhanced">
            <div class="header-left-inventory">
                <div class="inventory-icon-container">
                    <div class="inventory-icon">
                        <i class="fas fa-boxes"></i>
                    </div>
                    <div class="icon-pulse-inventory"></div>
                </div>
                <div class="header-text-inventory">
                    <h1 class="inventory-title">Stock Inventory</h1>
                    <p class="inventory-subtitle">
                        <i class="fas fa-building me-1"></i>
                        <?php echo htmlspecialchars($branch_name); ?> - Complete Stock Overview
                    </p>
                </div>
            </div>
            <div class="header-actions-inventory">
                                 <button class="inventory-action-btn refresh-inventory-btn" onclick="refreshInventory()">
                     <i class="fas fa-times me-2"></i>
                     Refresh
                 </button>
                <button class="inventory-action-btn export-btn" onclick="exportInventory()">
                    <i class="fas fa-download me-2"></i>
                    Export
                </button>
            </div>
        </div>

        <!-- Inventory Analytics -->
        <div class="inventory-analytics-section">
            <div class="analytics-grid">
                <!-- Stock Health Card -->
                <div class="analytics-card health-card">
                    <div class="card-header-analytics">
                        <div class="analytics-icon health-icon">
                            <i class="fas fa-heartbeat"></i>
                        </div>
                        <div class="analytics-title">Stock Health</div>
                    </div>
                    <div class="card-body-analytics">
                        <div class="health-score-container">
                            <div class="health-score" id="healthScore">0%</div>
                            <div class="health-label">Overall Health</div>
                        </div>
                        <div class="health-breakdown">
                            <div class="health-item">
                                <span class="health-dot adequate"></span>
                                <span class="health-text">Adequate Stock</span>
                                <span class="health-count" id="adequateCount">0</span>
                            </div>
                            <div class="health-item">
                                <span class="health-dot low"></span>
                                <span class="health-text">Low Stock</span>
                                <span class="health-count" id="lowCount">0</span>
                            </div>
                            <div class="health-item">
                                <span class="health-dot critical"></span>
                                <span class="health-text">Critical</span>
                                <span class="health-count" id="criticalCount">0</span>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Stock Processing Card -->
                <div class="analytics-card processing-card">
                    <div class="card-header-analytics">
                        <div class="analytics-icon processing-icon">
                            <i class="fas fa-cogs"></i>
                        </div>
                        <div class="analytics-title">Stock Processing</div>
                    </div>
                    <div class="card-body-analytics">
                        <div class="processing-stats">
                            <div class="processing-stat-item">
                                <div class="stat-number" id="pendingRequests">0</div>
                                <div class="stat-label">Pending Requests</div>
                            </div>
                            <div class="processing-stat-item">
                                <div class="stat-number" id="processingItems">0</div>
                                <div class="stat-label">Processing Items</div>
                            </div>
                        </div>
                        <div class="processing-actions">
                            <button class="processing-btn primary" onclick="openStockProcessing()">
                                <i class="fas fa-play me-2"></i>
                                Process Stock
                            </button>
                            <button class="processing-btn secondary" onclick="viewProcessingHistory()">
                                <i class="fas fa-history me-2"></i>
                                History
                            </button>
                        </div>
                    </div>
                </div>

                <!-- Recent Activity Card -->
                <div class="analytics-card activity-card">
                    <div class="card-header-analytics">
                        <div class="analytics-icon activity-icon">
                            <i class="fas fa-history"></i>
                        </div>
                        <div class="analytics-title">Recent Activity</div>
                    </div>
                    <div class="card-body-analytics">
                        <div class="activity-list" id="recentActivity">
                            <!-- Recent activities will be loaded here -->
                        </div>
                    </div>
                </div>
            </div>
        </div>



        <!-- Quick Actions Panel -->
        <div class="quick-actions-section">
            <div class="quick-actions-card">
                <div class="quick-actions-header">
                    <h4 class="quick-actions-title">
                        <i class="fas fa-bolt me-2"></i>
                        Quick Actions
                    </h4>
                </div>
                <div class="quick-actions-grid">
                    <button class="quick-action-btn low-stock-action" onclick="showLowStockItems()">
                        <div class="action-icon">
                            <i class="fas fa-exclamation-triangle"></i>
                        </div>
                        <div class="action-text">
                            <div class="action-title">View Low Stock</div>
                            <div class="action-subtitle">Items needing attention</div>
                        </div>
                    </button>
                    
                    <button class="quick-action-btn expiring-action" onclick="showExpiringItems()">
                        <div class="action-icon">
                            <i class="fas fa-calendar-times"></i>
                        </div>
                        <div class="action-text">
                            <div class="action-title">Expiring Items</div>
                            <div class="action-subtitle">Items expiring soon</div>
                        </div>
                    </button>
                    
                    <button class="quick-action-btn request-action" onclick="window.location.href='request_stock.php'">
                        <div class="action-icon">
                            <i class="fas fa-plus-circle"></i>
                        </div>
                        <div class="action-text">
                            <div class="action-title">Request Stock</div>
                            <div class="action-subtitle">Submit new request</div>
                        </div>
                    </button>
                    
                    <button class="quick-action-btn movements-action" onclick="showStockMovements()">
                        <div class="action-icon">
                            <i class="fas fa-exchange-alt"></i>
                        </div>
                        <div class="action-text">
                            <div class="action-title">Stock Movements</div>
                            <div class="action-subtitle">View transaction history</div>
                        </div>
                    </button>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Stock Processing Modal -->
<div class="modal fade" id="stockProcessingModal" tabindex="-1" aria-labelledby="stockProcessingModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-xl">
        <div class="modal-content processing-modal-content">
            <div class="processing-modal-header">
                <div class="processing-header-content">
                    <div class="processing-icon-large">
                        <i class="fas fa-cogs"></i>
                    </div>
                    <div class="processing-text">
                        <h4 class="processing-title">Stock Processing Center</h4>
                        <p class="processing-subtitle">Manage and process inventory items</p>
                    </div>
                </div>
                <button type="button" class="btn-close-processing" data-bs-dismiss="modal">
                    <i class="fas fa-times"></i>
                </button>
            </div>
            
            <div class="processing-modal-body">
                <div class="processing-tabs">
                    <button class="processing-tab active" data-tab="incoming">
                        <i class="fas fa-download me-2"></i>
                        Incoming Stock
                    </button>
                    <button class="processing-tab" data-tab="outgoing">
                        <i class="fas fa-upload me-2"></i>
                        Outgoing Stock
                    </button>
                    <button class="processing-tab" data-tab="adjustments">
                        <i class="fas fa-edit me-2"></i>
                        Adjustments
                    </button>
                    <button class="processing-tab" data-tab="transfers">
                        <i class="fas fa-exchange-alt me-2"></i>
                        Transfers
                    </button>
                </div>
                
                <div class="processing-content">
                    <!-- Incoming Stock Tab -->
                    <div class="tab-content active" id="incoming-tab">
                        <div class="processing-section">
                            <div class="section-header">
                                <h5><i class="fas fa-download me-2"></i>Process Incoming Stock</h5>
                                <p>Receive and process new inventory items</p>
                            </div>
                            <div class="processing-form">
                                <div class="form-row">
                                    <div class="form-group">
                                        <label>Select Ingredient</label>
                                        <select class="form-control" id="incomingIngredient">
                                            <option value="">Choose ingredient...</option>
                                        </select>
                                    </div>
                                    <div class="form-group">
                                        <label>Quantity Received</label>
                                        <input type="number" class="form-control" id="incomingQuantity" placeholder="Enter quantity">
                                    </div>
                                    <div class="form-group">
                                        <label>Supplier/Notes</label>
                                        <input type="text" class="form-control" id="incomingNotes" placeholder="Supplier or notes">
                                    </div>
                                </div>
                                <div class="form-actions">
                                    <button class="process-action-btn primary" onclick="processIncomingStock()">
                                        <i class="fas fa-check me-2"></i>
                                        Process Incoming
                                    </button>
                                    <button class="process-action-btn secondary" onclick="clearIncomingForm()">
                                        <i class="fas fa-undo me-2"></i>
                                        Clear
                                    </button>
                                </div>
                            </div>
                        </div>
                    </div>
                    
                    <!-- Outgoing Stock Tab -->
                    <div class="tab-content" id="outgoing-tab">
                        <div class="processing-section">
                            <div class="section-header">
                                <h5><i class="fas fa-upload me-2"></i>Process Outgoing Stock</h5>
                                <p>Record stock usage and consumption</p>
                            </div>
                            <div class="processing-form">
                                <div class="form-row">
                                    <div class="form-group">
                                        <label>Select Ingredient</label>
                                        <select class="form-control" id="outgoingIngredient">
                                            <option value="">Choose ingredient...</option>
                                        </select>
                                    </div>
                                    <div class="form-group">
                                        <label>Quantity Used</label>
                                        <input type="number" class="form-control" id="outgoingQuantity" placeholder="Enter quantity">
                                    </div>
                                    <div class="form-group">
                                        <label>Reason/Notes</label>
                                        <input type="text" class="form-control" id="outgoingNotes" placeholder="Reason for usage">
                                    </div>
                                </div>
                                <div class="form-actions">
                                    <button class="process-action-btn primary" onclick="processOutgoingStock()">
                                        <i class="fas fa-check me-2"></i>
                                        Process Outgoing
                                    </button>
                                    <button class="process-action-btn secondary" onclick="clearOutgoingForm()">
                                        <i class="fas fa-undo me-2"></i>
                                        Clear
                                    </button>
                                </div>
                            </div>
                        </div>
                    </div>
                    
                    <!-- Adjustments Tab -->
                    <div class="tab-content" id="adjustments-tab">
                        <div class="processing-section">
                            <div class="section-header">
                                <h5><i class="fas fa-edit me-2"></i>Stock Adjustments</h5>
                                <p>Adjust stock levels for corrections or damages</p>
                            </div>
                            <div class="processing-form">
                                <div class="form-row">
                                    <div class="form-group">
                                        <label>Select Ingredient</label>
                                        <select class="form-control" id="adjustmentIngredient">
                                            <option value="">Choose ingredient...</option>
                                        </select>
                                    </div>
                                    <div class="form-group">
                                        <label>Adjustment Type</label>
                                        <select class="form-control" id="adjustmentType">
                                            <option value="add">Add Stock</option>
                                            <option value="subtract">Subtract Stock</option>
                                            <option value="set">Set to Specific Amount</option>
                                        </select>
                                    </div>
                                    <div class="form-group">
                                        <label>Quantity</label>
                                        <input type="number" class="form-control" id="adjustmentQuantity" placeholder="Enter quantity">
                                    </div>
                                    <div class="form-group">
                                        <label>Reason</label>
                                        <select class="form-control" id="adjustmentReason">
                                            <option value="damage">Damage/Loss</option>
                                            <option value="correction">Correction</option>
                                            <option value="expiry">Expiry</option>
                                            <option value="other">Other</option>
                                        </select>
                                    </div>
                                </div>
                                <div class="form-actions">
                                    <button class="process-action-btn primary" onclick="processAdjustment()">
                                        <i class="fas fa-check me-2"></i>
                                        Process Adjustment
                                    </button>
                                    <button class="process-action-btn secondary" onclick="clearAdjustmentForm()">
                                        <i class="fas fa-undo me-2"></i>
                                        Clear
                                    </button>
                                </div>
                            </div>
                        </div>
                    </div>
                    
                    <!-- Transfers Tab -->
                    <div class="tab-content" id="transfers-tab">
                        <div class="processing-section">
                            <div class="section-header">
                                <h5><i class="fas fa-exchange-alt me-2"></i>Stock Transfers</h5>
                                <p>Transfer stock between locations or branches</p>
                            </div>
                            <div class="processing-form">
                                <div class="form-row">
                                    <div class="form-group">
                                        <label>Select Ingredient</label>
                                        <select class="form-control" id="transferIngredient">
                                            <option value="">Choose ingredient...</option>
                                        </select>
                                    </div>
                                    <div class="form-group">
                                        <label>From Location</label>
                                        <select class="form-control" id="transferFrom">
                                            <option value="current">Current Branch</option>
                                        </select>
                                    </div>
                                    <div class="form-group">
                                        <label>To Location</label>
                                        <select class="form-control" id="transferTo">
                                            <option value="">Select destination...</option>
                                        </select>
                                    </div>
                                    <div class="form-group">
                                        <label>Transfer Quantity</label>
                                        <input type="number" class="form-control" id="transferQuantity" placeholder="Enter quantity">
                                    </div>
                                </div>
                                <div class="form-actions">
                                    <button class="process-action-btn primary" onclick="processTransfer()">
                                        <i class="fas fa-check me-2"></i>
                                        Process Transfer
                                    </button>
                                    <button class="process-action-btn secondary" onclick="clearTransferForm()">
                                        <i class="fas fa-undo me-2"></i>
                                        Clear
                                    </button>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Processing History Modal -->
<div class="modal fade" id="processingHistoryModal" tabindex="-1" aria-labelledby="processingHistoryModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content history-modal-content">
            <div class="history-modal-header">
                <div class="history-header-content">
                    <div class="history-icon-large">
                        <i class="fas fa-history"></i>
                    </div>
                    <div class="history-text">
                        <h4 class="history-title">Processing History</h4>
                        <p class="history-subtitle">Recent stock processing activities</p>
                    </div>
                </div>
                <button type="button" class="btn-close-history" data-bs-dismiss="modal">
                    <i class="fas fa-times"></i>
                </button>
            </div>
            
            <div class="history-modal-body">
                <div class="history-filters">
                    <select class="history-filter" id="historyTypeFilter">
                        <option value="all">All Activities</option>
                        <option value="incoming">Incoming Stock</option>
                        <option value="outgoing">Outgoing Stock</option>
                        <option value="adjustment">Adjustments</option>
                        <option value="transfer">Transfers</option>
                    </select>
                    <select class="history-filter" id="historyDateFilter">
                        <option value="today">Today</option>
                        <option value="week">This Week</option>
                        <option value="month">This Month</option>
                        <option value="all">All Time</option>
                    </select>
                </div>
                
                <div class="history-list" id="processingHistoryList">
                    <!-- History items will be loaded here -->
                </div>
            </div>
        </div>
    </div>
</div>

<style>
/* Stock Inventory Page Styling */
.stock-inventory-bg {
    background: linear-gradient(135deg, #f8f9fa 0%, #e9ecef 100%);
    min-height: 100vh;
    padding: 2rem 0;
}

.inventory-header-enhanced {
    background: linear-gradient(135deg, #8B4543 0%, #A65D5D 50%, #8B4543 100%);
    color: white;
    padding: 2.5rem;
    border-radius: 20px;
    margin-bottom: 2rem;
    display: flex;
    justify-content: space-between;
    align-items: center;
    position: relative;
    overflow: hidden;
    box-shadow: 0 10px 35px rgba(139, 69, 67, 0.25);
}

.inventory-header-enhanced::before {
    content: '';
    position: absolute;
    top: 0;
    left: 0;
    right: 0;
    bottom: 0;
    background: 
        radial-gradient(circle at 20% 50%, rgba(255,255,255,0.1) 0%, transparent 50%),
        radial-gradient(circle at 80% 20%, rgba(255,255,255,0.08) 0%, transparent 50%),
        linear-gradient(45deg, rgba(255,255,255,0.05) 0%, transparent 50%);
    animation: headerPattern 8s ease-in-out infinite;
    pointer-events: none;
}

@keyframes headerPattern {
    0%, 100% { transform: translateX(0) translateY(0) rotate(0deg); }
    50% { transform: translateX(15px) translateY(-15px) rotate(2deg); }
}

.header-left-inventory {
    display: flex;
    align-items: center;
    gap: 2rem;
    position: relative;
    z-index: 2;
}

.inventory-icon-container {
    position: relative;
}

.inventory-icon {
    width: 80px;
    height: 80px;
    background: rgba(255, 255, 255, 0.25);
    border-radius: 20px;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 2.2rem;
    color: white;
    backdrop-filter: blur(15px);
    box-shadow: 0 10px 30px rgba(0, 0, 0, 0.25);
    border: 2px solid rgba(255, 255, 255, 0.3);
}

.icon-pulse-inventory {
    position: absolute;
    width: 80px;
    height: 80px;
    border-radius: 20px;
    background: rgba(255, 255, 255, 0.1);
    animation: inventoryPulse 4s ease-in-out infinite;
}

@keyframes inventoryPulse {
    0%, 100% { transform: scale(1); opacity: 0.4; }
    50% { transform: scale(1.2); opacity: 0.1; }
}

.inventory-title {
    font-size: 2.5rem;
    font-weight: 900;
    margin: 0;
    color: white;
    text-shadow: 0 3px 6px rgba(0, 0, 0, 0.3);
    letter-spacing: -0.5px;
}

.inventory-subtitle {
    font-size: 1.2rem;
    margin: 0;
    color: rgba(255, 255, 255, 0.9);
    text-shadow: 0 2px 4px rgba(0, 0, 0, 0.2);
    display: flex;
    align-items: center;
}

.header-actions-inventory {
    display: flex;
    gap: 1rem;
    position: relative;
    z-index: 2;
}

.inventory-action-btn {
    background: rgba(255, 255, 255, 0.2);
    color: white;
    border: none;
    border-radius: 12px;
    padding: 1rem 2rem;
    font-weight: 700;
    font-size: 1rem;
    transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
    backdrop-filter: blur(10px);
    border: 1px solid rgba(255, 255, 255, 0.3);
    cursor: pointer;
    display: flex;
    align-items: center;
    position: relative;
    overflow: hidden;
}

.inventory-action-btn::before {
    content: '';
    position: absolute;
    top: 0;
    left: -100%;
    width: 100%;
    height: 100%;
    background: linear-gradient(90deg, transparent, rgba(255,255,255,0.2), transparent);
    transition: left 0.6s;
}

.inventory-action-btn:hover::before {
    left: 100%;
}

.inventory-action-btn:hover {
    background: rgba(255, 255, 255, 0.35);
    transform: translateY(-3px);
    box-shadow: 0 6px 20px rgba(0, 0, 0, 0.3);
}

.inventory-analytics-section {
    margin-bottom: 2rem;
}

.analytics-grid {
    display: grid;
    grid-template-columns: 1fr 1fr 1fr;
    gap: 2rem;
}

.analytics-card {
    background: white;
    border-radius: 18px;
    padding: 2rem;
    box-shadow: 0 8px 30px rgba(139, 69, 67, 0.1);
    border: 1px solid rgba(139, 69, 67, 0.1);
    transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
    position: relative;
    overflow: hidden;
}

.analytics-card:hover {
    transform: translateY(-5px);
    box-shadow: 0 15px 50px rgba(139, 69, 67, 0.2);
}

.analytics-card::before {
    content: '';
    position: absolute;
    top: 0;
    left: 0;
    right: 0;
    height: 4px;
    background: linear-gradient(135deg, #8B4543, #A65D5D);
}

.card-header-analytics {
    display: flex;
    align-items: center;
    gap: 1rem;
    margin-bottom: 1.5rem;
    padding-bottom: 1rem;
    border-bottom: 2px solid #f8f9fa;
}

.analytics-icon {
    width: 50px;
    height: 50px;
    border-radius: 14px;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 1.4rem;
    color: white;
    flex-shrink: 0;
}

.health-icon {
    background: linear-gradient(135deg, #28a745, #20c997);
}

 .processing-icon {
     background: linear-gradient(135deg, #8B4543, #A65D5D);
 }

.activity-icon {
    background: linear-gradient(135deg, #8B4543, #A65D5D);
}

.analytics-title {
    font-size: 1.3rem;
    font-weight: 700;
    color: #2c3e50;
    margin: 0;
}

.health-score-container {
    text-align: center;
    margin-bottom: 1.5rem;
}

.health-score {
    font-size: 3rem;
    font-weight: 900;
    color: #28a745;
    line-height: 1;
    margin-bottom: 0.5rem;
}

.health-label {
    color: #6c757d;
    font-weight: 600;
    font-size: 0.9rem;
    text-transform: uppercase;
    letter-spacing: 0.5px;
}

.health-breakdown {
    display: flex;
    flex-direction: column;
    gap: 0.75rem;
}

.health-item {
    display: flex;
    align-items: center;
    gap: 0.75rem;
    padding: 0.5rem;
    border-radius: 8px;
    transition: all 0.3s ease;
}

.health-item:hover {
    background: #f8f9fa;
    transform: translateX(5px);
}

.health-dot {
    width: 12px;
    height: 12px;
    border-radius: 50%;
    flex-shrink: 0;
}

.health-dot.adequate {
    background: #28a745;
}

.health-dot.low {
    background: #ffc107;
}

.health-dot.critical {
    background: #dc3545;
}

.health-text {
    flex: 1;
    font-weight: 600;
    color: #495057;
    font-size: 0.9rem;
}

.health-count {
    background: #f8f9fa;
    color: #2c3e50;
    padding: 0.25rem 0.75rem;
    border-radius: 12px;
    font-weight: 700;
    font-size: 0.85rem;
}

 .processing-stats {
     display: flex;
     justify-content: space-around;
     margin-bottom: 1.5rem;
 }

 .processing-stat-item {
     text-align: center;
     padding: 1rem;
     background: #f8f9fa;
     border-radius: 12px;
     transition: all 0.3s ease;
 }

 .processing-stat-item:hover {
     background: #e9ecef;
     transform: scale(1.05);
 }

 .processing-stat-item .stat-number {
     font-size: 2rem;
     font-weight: 900;
     color: #8B4543;
     line-height: 1;
     margin-bottom: 0.5rem;
 }

 .processing-stat-item .stat-label {
     color: #6c757d;
     font-weight: 600;
     font-size: 0.85rem;
     text-transform: uppercase;
     letter-spacing: 0.5px;
 }

 .processing-actions {
     display: flex;
     gap: 1rem;
     justify-content: center;
 }

 .processing-btn {
     padding: 0.75rem 1.5rem;
     border-radius: 10px;
     font-weight: 600;
     font-size: 0.9rem;
     transition: all 0.3s ease;
     border: none;
     cursor: pointer;
     display: flex;
     align-items: center;
     gap: 0.5rem;
 }

 .processing-btn.primary {
     background: linear-gradient(135deg, #8B4543, #A65D5D);
     color: white;
 }

 .processing-btn.primary:hover {
     background: linear-gradient(135deg, #A65D5D, #8B4543);
     transform: translateY(-2px);
     box-shadow: 0 4px 15px rgba(139, 69, 67, 0.3);
 }

 .processing-btn.secondary {
     background: #f8f9fa;
     color: #495057;
     border: 2px solid #dee2e6;
 }

 .processing-btn.secondary:hover {
     background: #e9ecef;
     border-color: #8B4543;
     color: #8B4543;
     transform: translateY(-2px);
 }

.activity-list {
    display: flex;
    flex-direction: column;
    gap: 0.75rem;
    max-height: 200px;
    overflow-y: auto;
}

.activity-item {
    display: flex;
    align-items: center;
    gap: 0.75rem;
    padding: 0.75rem;
    background: #f8f9fa;
    border-radius: 8px;
    transition: all 0.3s ease;
}

.activity-item:hover {
    background: #e9ecef;
    transform: translateX(3px);
}

.activity-icon-small {
    width: 30px;
    height: 30px;
    border-radius: 8px;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 0.9rem;
    color: white;
    flex-shrink: 0;
}

.activity-text {
    flex: 1;
}

.activity-title {
    font-weight: 600;
    color: #2c3e50;
    font-size: 0.9rem;
    margin-bottom: 0.25rem;
}

.activity-time {
    color: #6c757d;
    font-size: 0.8rem;
}

.inventory-categories-section {
    margin-bottom: 2rem;
}

.categories-header {
    margin-bottom: 1.5rem;
}

.categories-title {
    color: #2c3e50;
    font-weight: 700;
    font-size: 1.5rem;
    margin: 0;
}

.categories-grid {
    display: grid;
    grid-template-columns: repeat(auto-fill, minmax(280px, 1fr));
    gap: 1.5rem;
}

.category-card {
    background: white;
    border-radius: 16px;
    padding: 1.5rem;
    box-shadow: 0 6px 25px rgba(139, 69, 67, 0.08);
    border: 1px solid rgba(139, 69, 67, 0.1);
    transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
    position: relative;
    overflow: hidden;
}

.category-card:hover {
    transform: translateY(-4px);
    box-shadow: 0 12px 40px rgba(139, 69, 67, 0.15);
}

.category-card::before {
    content: '';
    position: absolute;
    top: 0;
    left: 0;
    right: 0;
    height: 3px;
    background: linear-gradient(135deg, #8B4543, #A65D5D);
}

.quick-actions-section {
    margin-bottom: 2rem;
}

.quick-actions-card {
    background: white;
    border-radius: 18px;
    padding: 2rem;
    box-shadow: 0 8px 30px rgba(139, 69, 67, 0.1);
    border: 1px solid rgba(139, 69, 67, 0.1);
    transition: all 0.3s ease;
}

.quick-actions-card:hover {
    transform: translateY(-2px);
    box-shadow: 0 12px 40px rgba(139, 69, 67, 0.15);
}

.quick-actions-header {
    margin-bottom: 1.5rem;
    padding-bottom: 1rem;
    border-bottom: 2px solid #f8f9fa;
}

.quick-actions-title {
    color: #2c3e50;
    font-weight: 700;
    font-size: 1.4rem;
    margin: 0;
    display: flex;
    align-items: center;
}

.quick-actions-grid {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
    gap: 1.5rem;
}

.quick-action-btn {
    background: linear-gradient(135deg, #f8f9fa, #e9ecef);
    border: 2px solid #dee2e6;
    border-radius: 12px;
    padding: 1.5rem;
    transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
    cursor: pointer;
    display: flex;
    align-items: center;
    gap: 1rem;
    text-align: left;
}

.quick-action-btn:hover {
    transform: translateY(-3px);
    box-shadow: 0 8px 25px rgba(139, 69, 67, 0.2);
    border-color: #8B4543;
}

.action-icon {
    width: 50px;
    height: 50px;
    border-radius: 12px;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 1.3rem;
    color: white;
    flex-shrink: 0;
    transition: all 0.3s ease;
}

.low-stock-action .action-icon {
    background: linear-gradient(135deg, #ffc107, #ffb300);
}

.expiring-action .action-icon {
    background: linear-gradient(135deg, #fd7e14, #e55353);
}

.request-action .action-icon {
    background: linear-gradient(135deg, #007bff, #0056b3);
}

.movements-action .action-icon {
    background: linear-gradient(135deg, #8B4543, #A65D5D);
}

.quick-action-btn:hover .action-icon {
    transform: scale(1.1) rotate(5deg);
}

.action-text {
    flex: 1;
}

.action-title {
    font-size: 1.1rem;
    font-weight: 700;
    color: #2c3e50;
    margin-bottom: 0.25rem;
}

.action-subtitle {
    font-size: 0.9rem;
    color: #6c757d;
    font-weight: 500;
}

/* Responsive Design */
@media (max-width: 768px) {
    .inventory-header-enhanced {
        flex-direction: column;
        gap: 2rem;
        text-align: center;
        padding: 2rem;
    }
    
    .analytics-grid {
        grid-template-columns: 1fr;
        gap: 1.5rem;
    }
    
    .categories-grid {
        grid-template-columns: 1fr;
    }
    
    .quick-actions-grid {
        grid-template-columns: 1fr;
    }
    
         .header-actions-inventory {
         width: 100%;
         justify-content: center;
     }
 }

 /* Processing Modal Styles */
 .processing-modal-content {
     border-radius: 20px;
     overflow: hidden;
     border: none;
     box-shadow: 0 25px 80px rgba(0, 0, 0, 0.2);
 }

 .processing-modal-header {
     background: linear-gradient(135deg, #8B4543 0%, #A65D5D 50%, #8B4543 100%);
     color: white;
     padding: 2rem;
     display: flex;
     justify-content: space-between;
     align-items: center;
     position: relative;
     overflow: hidden;
 }

 .processing-modal-header::before {
     content: '';
     position: absolute;
     top: 0;
     left: 0;
     right: 0;
     bottom: 0;
     background: linear-gradient(45deg, rgba(255,255,255,0.1) 0%, transparent 50%);
     pointer-events: none;
 }

 .processing-header-content {
     display: flex;
     align-items: center;
     gap: 1.5rem;
     position: relative;
     z-index: 2;
 }

 .processing-icon-large {
     width: 60px;
     height: 60px;
     background: rgba(255, 255, 255, 0.25);
     border-radius: 15px;
     display: flex;
     align-items: center;
     justify-content: center;
     font-size: 1.8rem;
     color: white;
     backdrop-filter: blur(10px);
 }

 .processing-title {
     font-size: 1.6rem;
     font-weight: 700;
     margin: 0;
     color: white;
     text-shadow: 0 2px 4px rgba(0, 0, 0, 0.3);
 }

 .processing-subtitle {
     font-size: 1rem;
     margin: 0;
     color: rgba(255, 255, 255, 0.9);
 }

 .btn-close-processing {
     background: rgba(255, 255, 255, 0.2);
     border: none;
     color: white;
     width: 45px;
     height: 45px;
     border-radius: 12px;
     display: flex;
     align-items: center;
     justify-content: center;
     transition: all 0.3s ease;
     position: relative;
     z-index: 2;
 }

 .btn-close-processing:hover {
     background: rgba(255, 255, 255, 0.35);
     transform: scale(1.1) rotate(90deg);
 }

 .processing-modal-body {
     padding: 2rem;
     background: #f8f9fa;
 }

 .processing-tabs {
     display: flex;
     background: white;
     border-radius: 15px;
     padding: 0.5rem;
     margin-bottom: 2rem;
     box-shadow: 0 4px 15px rgba(0, 0, 0, 0.1);
 }

 .processing-tab {
     flex: 1;
     background: transparent;
     border: none;
     padding: 1rem 1.5rem;
     border-radius: 10px;
     font-weight: 600;
     font-size: 0.95rem;
     color: #6c757d;
     transition: all 0.3s ease;
     cursor: pointer;
     display: flex;
     align-items: center;
     justify-content: center;
     gap: 0.5rem;
 }

 .processing-tab.active,
 .processing-tab:hover {
     background: linear-gradient(135deg, #8B4543, #A65D5D);
     color: white;
     transform: translateY(-2px);
     box-shadow: 0 4px 15px rgba(139, 69, 67, 0.3);
 }

 .tab-content {
     display: none;
 }

 .tab-content.active {
     display: block;
     animation: fadeInUp 0.3s ease;
 }

 @keyframes fadeInUp {
     from {
         opacity: 0;
         transform: translateY(20px);
     }
     to {
         opacity: 1;
         transform: translateY(0);
     }
 }

 .processing-section {
     background: white;
     border-radius: 15px;
     padding: 2rem;
     box-shadow: 0 6px 25px rgba(0, 0, 0, 0.08);
     border: 1px solid rgba(139, 69, 67, 0.1);
 }

 .section-header {
     margin-bottom: 2rem;
     padding-bottom: 1rem;
     border-bottom: 2px solid #f8f9fa;
 }

 .section-header h5 {
     color: #2c3e50;
     font-weight: 700;
     font-size: 1.3rem;
     margin: 0 0 0.5rem 0;
     display: flex;
     align-items: center;
 }

 .section-header p {
     color: #6c757d;
     margin: 0;
     font-size: 0.95rem;
 }

 .processing-form {
     background: #f8f9fa;
     border-radius: 12px;
     padding: 1.5rem;
 }

 .form-row {
     display: grid;
     grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
     gap: 1.5rem;
     margin-bottom: 2rem;
 }

 .form-group {
     display: flex;
     flex-direction: column;
     gap: 0.5rem;
 }

 .form-group label {
     font-weight: 600;
     color: #495057;
     font-size: 0.9rem;
     text-transform: uppercase;
     letter-spacing: 0.5px;
 }

 .form-control {
     background: white;
     border: 2px solid #e9ecef;
     border-radius: 8px;
     padding: 0.75rem 1rem;
     font-weight: 500;
     color: #495057;
     transition: all 0.3s ease;
 }

 .form-control:focus {
     border-color: #8B4543;
     box-shadow: 0 0 0 0.2rem rgba(139, 69, 67, 0.25);
     outline: none;
 }

 .form-control:hover {
     border-color: #8B4543;
 }

 .form-actions {
     display: flex;
     gap: 1rem;
     justify-content: center;
 }

 .process-action-btn {
     padding: 1rem 2rem;
     border-radius: 10px;
     font-weight: 600;
     font-size: 1rem;
     transition: all 0.3s ease;
     border: none;
     cursor: pointer;
     display: flex;
     align-items: center;
     gap: 0.5rem;
     min-width: 150px;
     justify-content: center;
 }

 .process-action-btn.primary {
     background: linear-gradient(135deg, #8B4543, #A65D5D);
     color: white;
 }

 .process-action-btn.primary:hover {
     background: linear-gradient(135deg, #A65D5D, #8B4543);
     transform: translateY(-2px);
     box-shadow: 0 6px 20px rgba(139, 69, 67, 0.4);
 }

 .process-action-btn.secondary {
     background: #f8f9fa;
     color: #495057;
     border: 2px solid #dee2e6;
 }

 .process-action-btn.secondary:hover {
     background: #e9ecef;
     border-color: #8B4543;
     color: #8B4543;
     transform: translateY(-2px);
 }

 /* History Modal Styles */
 .history-modal-content {
     border-radius: 20px;
     overflow: hidden;
     border: none;
     box-shadow: 0 25px 80px rgba(0, 0, 0, 0.2);
 }

 .history-modal-header {
     background: linear-gradient(135deg, #8B4543 0%, #A65D5D 50%, #8B4543 100%);
     color: white;
     padding: 2rem;
     display: flex;
     justify-content: space-between;
     align-items: center;
     position: relative;
     overflow: hidden;
 }

 .history-modal-header::before {
     content: '';
     position: absolute;
     top: 0;
     left: 0;
     right: 0;
     bottom: 0;
     background: linear-gradient(45deg, rgba(255,255,255,0.1) 0%, transparent 50%);
     pointer-events: none;
 }

 .history-header-content {
     display: flex;
     align-items: center;
     gap: 1.5rem;
     position: relative;
     z-index: 2;
 }

 .history-icon-large {
     width: 60px;
     height: 60px;
     background: rgba(255, 255, 255, 0.25);
     border-radius: 15px;
     display: flex;
     align-items: center;
     justify-content: center;
     font-size: 1.8rem;
     color: white;
     backdrop-filter: blur(10px);
 }

 .history-title {
     font-size: 1.6rem;
     font-weight: 700;
     margin: 0;
     color: white;
     text-shadow: 0 2px 4px rgba(0, 0, 0, 0.3);
 }

 .history-subtitle {
     font-size: 1rem;
     margin: 0;
     color: rgba(255, 255, 255, 0.9);
 }

 .btn-close-history {
     background: rgba(255, 255, 255, 0.2);
     border: none;
     color: white;
     width: 45px;
     height: 45px;
     border-radius: 12px;
     display: flex;
     align-items: center;
     justify-content: center;
     transition: all 0.3s ease;
     position: relative;
     z-index: 2;
 }

 .btn-close-history:hover {
     background: rgba(255, 255, 255, 0.35);
     transform: scale(1.1) rotate(90deg);
 }

 .history-modal-body {
     padding: 2rem;
     background: #f8f9fa;
 }

 .history-filters {
     display: flex;
     gap: 1rem;
     margin-bottom: 2rem;
 }

 .history-filter {
     background: white;
     border: 2px solid #e9ecef;
     border-radius: 8px;
     padding: 0.75rem 1rem;
     font-weight: 500;
     color: #495057;
     transition: all 0.3s ease;
     cursor: pointer;
 }

 .history-filter:focus {
     border-color: #8B4543;
     box-shadow: 0 0 0 0.2rem rgba(139, 69, 67, 0.25);
     outline: none;
 }

 .history-filter:hover {
     border-color: #8B4543;
 }

 .history-list {
     max-height: 400px;
     overflow-y: auto;
     background: white;
     border-radius: 12px;
     padding: 1rem;
     box-shadow: 0 4px 15px rgba(0, 0, 0, 0.1);
 }

 /* Enhanced Category Card Styles */
 .category-header {
     display: flex;
     align-items: center;
     gap: 1rem;
     margin-bottom: 1.5rem;
 }

 .category-icon {
     width: 40px;
     height: 40px;
     background: linear-gradient(135deg, #8B4543, #A65D5D);
     border-radius: 10px;
     display: flex;
     align-items: center;
     justify-content: center;
     font-size: 1.1rem;
     color: white;
 }

 .category-info {
     flex: 1;
 }

 .category-name {
     font-size: 1.2rem;
     font-weight: 700;
     color: #2c3e50;
     margin: 0 0 0.25rem 0;
 }

 .category-stats {
     display: flex;
     align-items: center;
     gap: 0.5rem;
 }

 .category-stat {
     background: #f8f9fa;
     color: #6c757d;
     padding: 0.25rem 0.75rem;
     border-radius: 8px;
     font-size: 0.85rem;
     font-weight: 600;
 }

 .category-metrics {
     display: grid;
     grid-template-columns: repeat(3, 1fr);
     gap: 1rem;
     margin-bottom: 1.5rem;
 }

 .metric-item {
     text-align: center;
     padding: 1rem;
     background: #f8f9fa;
     border-radius: 10px;
     transition: all 0.3s ease;
 }

 .metric-item:hover {
     background: #e9ecef;
     transform: scale(1.05);
 }

 .metric-label {
     font-size: 0.8rem;
     font-weight: 600;
     color: #6c757d;
     text-transform: uppercase;
     letter-spacing: 0.5px;
     margin-bottom: 0.5rem;
 }

 .metric-value {
     font-size: 1.3rem;
     font-weight: 800;
     line-height: 1;
 }

 .category-actions {
     text-align: center;
 }

 .category-action-btn {
     background: linear-gradient(135deg, #8B4543, #A65D5D);
     color: white;
     border: none;
     border-radius: 8px;
     padding: 0.75rem 1.5rem;
     font-weight: 600;
     font-size: 0.9rem;
     transition: all 0.3s ease;
     cursor: pointer;
     display: inline-flex;
     align-items: center;
     gap: 0.5rem;
 }

 .category-action-btn:hover {
     background: linear-gradient(135deg, #723836, #8B4543);
     transform: translateY(-2px);
     box-shadow: 0 4px 15px rgba(139, 69, 67, 0.3);
     color: white;
     text-decoration: none;
 }
</style>

<script>
$(document).ready(function() {
    loadInventoryData();
    
    // Auto-refresh every 2 minutes
    setInterval(loadInventoryData, 120000);
});

function loadInventoryData() {
    fetch('get_available_ingredients.php')
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                updateInventoryAnalytics(data);
                loadCategoriesData(data.ingredients);
                loadRecentActivity();
            } else {
                console.error('Error loading inventory data:', data.error);
            }
        })
        .catch(error => {
            console.error('Error:', error);
        });
}

function updateInventoryAnalytics(data) {
    const stats = data.stats;
    const ingredients = data.ingredients;
    
    // Update health score
    const totalItems = stats.total;
    const healthyItems = stats.available;
    const healthScore = totalItems > 0 ? Math.round((healthyItems / totalItems) * 100) : 100;
    
    document.getElementById('healthScore').textContent = healthScore + '%';
    document.getElementById('healthScore').style.color = healthScore >= 80 ? '#28a745' : healthScore >= 60 ? '#ffc107' : '#dc3545';
    
    // Update health breakdown
    document.getElementById('adequateCount').textContent = stats.available;
    document.getElementById('lowCount').textContent = stats.low_stock;
    document.getElementById('criticalCount').textContent = stats.out_of_stock;
    
    // Update processing stats (mock data for now)
    const pendingRequests = Math.floor(Math.random() * 5) + 1; // Random 1-5
    const processingItems = Math.floor(Math.random() * 3); // Random 0-2
    
    document.getElementById('pendingRequests').textContent = pendingRequests;
    document.getElementById('processingItems').textContent = processingItems;
}

function loadCategoriesData(ingredients) {
    const categoriesGrid = document.getElementById('categoriesGrid');
    categoriesGrid.innerHTML = '';
    
    // Group ingredients by category
    const categories = {};
    ingredients.forEach(ingredient => {
        const categoryName = ingredient.category_name || 'Uncategorized';
        if (!categories[categoryName]) {
            categories[categoryName] = {
                name: categoryName,
                items: [],
                total: 0,
                available: 0,
                low_stock: 0
            };
        }
        
        categories[categoryName].items.push(ingredient);
        categories[categoryName].total++;
        
        if (ingredient.ingredient_status === 'Available') {
            categories[categoryName].available++;
        }
        
        if (ingredient.ingredient_quantity <= ingredient.minimum_stock) {
            categories[categoryName].low_stock++;
        }
    });
    
    // Create category cards
    Object.values(categories).forEach(category => {
        const card = createCategoryCard(category);
        categoriesGrid.appendChild(card);
    });
}

function createCategoryCard(category) {
    const card = document.createElement('div');
    card.className = 'category-card';
    
    const healthPercentage = Math.round((category.available / category.total) * 100);
    const healthColor = healthPercentage >= 80 ? '#28a745' : healthPercentage >= 60 ? '#ffc107' : '#dc3545';
    
    card.innerHTML = `
        <div class="category-header">
            <div class="category-icon">
                <i class="fas fa-tag"></i>
            </div>
            <div class="category-info">
                <h5 class="category-name">${category.name}</h5>
                <div class="category-stats">
                    <span class="category-stat">
                        <i class="fas fa-cubes me-1"></i>
                        ${category.total} items
                    </span>
                </div>
            </div>
        </div>
        
        <div class="category-metrics">
            <div class="metric-item">
                <span class="metric-label">Available</span>
                <span class="metric-value" style="color: #28a745;">${category.available}</span>
            </div>
            <div class="metric-item">
                <span class="metric-label">Low Stock</span>
                <span class="metric-value" style="color: #ffc107;">${category.low_stock}</span>
            </div>
            <div class="metric-item">
                <span class="metric-label">Health</span>
                <span class="metric-value" style="color: ${healthColor};">${healthPercentage}%</span>
            </div>
        </div>
        
        <div class="category-actions">
            <button class="category-action-btn" onclick="filterByCategory('${category.name}')">
                <i class="fas fa-filter me-1"></i>
                View Items
            </button>
        </div>
    `;
    
    return card;
}

function loadRecentActivity() {
    // Mock recent activity data
    const activities = [
        { type: 'request', title: 'Stock request submitted', time: '5 minutes ago', icon: 'fas fa-plus-circle', color: '#007bff' },
        { type: 'adjustment', title: 'Stock adjusted: Coke Mismo', time: '15 minutes ago', icon: 'fas fa-edit', color: '#28a745' },
        { type: 'alert', title: 'Low stock alert: Halo-Halo', time: '1 hour ago', icon: 'fas fa-exclamation-triangle', color: '#ffc107' },
        { type: 'expiry', title: 'Item expiring: Ice Cream', time: '2 hours ago', icon: 'fas fa-calendar-times', color: '#fd7e14' }
    ];
    
    const activityList = document.getElementById('recentActivity');
    activityList.innerHTML = '';
    
    activities.forEach(activity => {
        const item = document.createElement('div');
        item.className = 'activity-item';
        item.innerHTML = `
            <div class="activity-icon-small" style="background: ${activity.color};">
                <i class="${activity.icon}"></i>
            </div>
            <div class="activity-text">
                <div class="activity-title">${activity.title}</div>
                <div class="activity-time">${activity.time}</div>
            </div>
        `;
        activityList.appendChild(item);
    });
}

function refreshInventory() {
    const btn = document.querySelector('.refresh-inventory-btn');
    const originalText = btn.innerHTML;
    
    btn.innerHTML = '<i class="fas fa-spinner fa-spin me-2"></i>Refreshing...';
    btn.disabled = true;
    
    loadInventoryData();
    
    setTimeout(() => {
        btn.innerHTML = originalText;
        btn.disabled = false;
    }, 2000);
}

function exportInventory() {
    // Mock export functionality
    const btn = document.querySelector('.export-btn');
    const originalText = btn.innerHTML;
    
    btn.innerHTML = '<i class="fas fa-spinner fa-spin me-2"></i>Exporting...';
    btn.disabled = true;
    
    setTimeout(() => {
        btn.innerHTML = '<i class="fas fa-check me-2"></i>Exported!';
        
        setTimeout(() => {
            btn.innerHTML = originalText;
            btn.disabled = false;
        }, 1500);
    }, 2000);
}

function showLowStockItems() {
    window.location.href = 'available_ingredients.php?filter=low_stock';
}

function showExpiringItems() {
    window.location.href = 'available_ingredients.php?filter=expiring';
}

function showStockMovements() {
    // This could open a modal or redirect to a stock movements page
    console.log('Show stock movements');
}

function filterByCategory(categoryName) {
    window.location.href = `available_ingredients.php?category=${encodeURIComponent(categoryName)}`;
}

// Stock Processing Functions
function openStockProcessing() {
    $('#stockProcessingModal').modal('show');
    loadIngredientsForProcessing();
    setupProcessingTabs();
}

function viewProcessingHistory() {
    $('#processingHistoryModal').modal('show');
    loadProcessingHistory();
}

function setupProcessingTabs() {
    const tabs = document.querySelectorAll('.processing-tab');
    const tabContents = document.querySelectorAll('.tab-content');
    
    tabs.forEach(tab => {
        tab.addEventListener('click', () => {
            const targetTab = tab.getAttribute('data-tab');
            
            // Remove active class from all tabs and contents
            tabs.forEach(t => t.classList.remove('active'));
            tabContents.forEach(content => content.classList.remove('active'));
            
            // Add active class to clicked tab and corresponding content
            tab.classList.add('active');
            document.getElementById(`${targetTab}-tab`).classList.add('active');
        });
    });
}

function loadIngredientsForProcessing() {
    fetch('get_available_ingredients.php')
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                populateIngredientSelects(data.ingredients);
            }
        })
        .catch(error => console.error('Error loading ingredients:', error));
}

function populateIngredientSelects(ingredients) {
    const selects = ['incomingIngredient', 'outgoingIngredient', 'adjustmentIngredient', 'transferIngredient'];
    
    selects.forEach(selectId => {
        const select = document.getElementById(selectId);
        select.innerHTML = '<option value="">Choose ingredient...</option>';
        
        ingredients.forEach(ingredient => {
            const option = document.createElement('option');
            option.value = ingredient.ingredient_id;
            option.textContent = `${ingredient.ingredient_name} (${ingredient.ingredient_quantity} ${ingredient.ingredient_unit})`;
            select.appendChild(option);
        });
    });
}

function processIncomingStock() {
    const ingredientId = document.getElementById('incomingIngredient').value;
    const quantity = document.getElementById('incomingQuantity').value;
    const notes = document.getElementById('incomingNotes').value;
    
    if (!ingredientId || !quantity) {
        showProcessingAlert('Please fill in all required fields', 'warning');
        return;
    }
    
    // Simulate processing
    showProcessingAlert('Processing incoming stock...', 'info');
    
    setTimeout(() => {
        showProcessingAlert('Incoming stock processed successfully!', 'success');
        clearIncomingForm();
        loadInventoryData(); // Refresh data
    }, 2000);
}

function processOutgoingStock() {
    const ingredientId = document.getElementById('outgoingIngredient').value;
    const quantity = document.getElementById('outgoingQuantity').value;
    const notes = document.getElementById('outgoingNotes').value;
    
    if (!ingredientId || !quantity) {
        showProcessingAlert('Please fill in all required fields', 'warning');
        return;
    }
    
    // Simulate processing
    showProcessingAlert('Processing outgoing stock...', 'info');
    
    setTimeout(() => {
        showProcessingAlert('Outgoing stock processed successfully!', 'success');
        clearOutgoingForm();
        loadInventoryData(); // Refresh data
    }, 2000);
}

function processAdjustment() {
    const ingredientId = document.getElementById('adjustmentIngredient').value;
    const type = document.getElementById('adjustmentType').value;
    const quantity = document.getElementById('adjustmentQuantity').value;
    const reason = document.getElementById('adjustmentReason').value;
    
    if (!ingredientId || !quantity) {
        showProcessingAlert('Please fill in all required fields', 'warning');
        return;
    }
    
    // Simulate processing
    showProcessingAlert('Processing stock adjustment...', 'info');
    
    setTimeout(() => {
        showProcessingAlert('Stock adjustment processed successfully!', 'success');
        clearAdjustmentForm();
        loadInventoryData(); // Refresh data
    }, 2000);
}

function processTransfer() {
    const ingredientId = document.getElementById('transferIngredient').value;
    const fromLocation = document.getElementById('transferFrom').value;
    const toLocation = document.getElementById('transferTo').value;
    const quantity = document.getElementById('transferQuantity').value;
    
    if (!ingredientId || !toLocation || !quantity) {
        showProcessingAlert('Please fill in all required fields', 'warning');
        return;
    }
    
    // Simulate processing
    showProcessingAlert('Processing stock transfer...', 'info');
    
    setTimeout(() => {
        showProcessingAlert('Stock transfer processed successfully!', 'success');
        clearTransferForm();
        loadInventoryData(); // Refresh data
    }, 2000);
}

function clearIncomingForm() {
    document.getElementById('incomingIngredient').value = '';
    document.getElementById('incomingQuantity').value = '';
    document.getElementById('incomingNotes').value = '';
}

function clearOutgoingForm() {
    document.getElementById('outgoingIngredient').value = '';
    document.getElementById('outgoingQuantity').value = '';
    document.getElementById('outgoingNotes').value = '';
}

function clearAdjustmentForm() {
    document.getElementById('adjustmentIngredient').value = '';
    document.getElementById('adjustmentType').value = 'add';
    document.getElementById('adjustmentQuantity').value = '';
    document.getElementById('adjustmentReason').value = 'damage';
}

function clearTransferForm() {
    document.getElementById('transferIngredient').value = '';
    document.getElementById('transferFrom').value = 'current';
    document.getElementById('transferTo').value = '';
    document.getElementById('transferQuantity').value = '';
}

function loadProcessingHistory() {
    // Mock processing history data
    const historyData = [
        {
            type: 'incoming',
            title: 'Incoming Stock: Coke Mismo',
            description: 'Received 50 units from Supplier A',
            time: '2 hours ago',
            icon: 'fas fa-download',
            color: '#28a745'
        },
        {
            type: 'outgoing',
            title: 'Outgoing Stock: Halo-Halo Mix',
            description: 'Used 10 units for production',
            time: '4 hours ago',
            icon: 'fas fa-upload',
            color: '#dc3545'
        },
        {
            type: 'adjustment',
            title: 'Stock Adjustment: Ice Cream',
            description: 'Damaged 5 units - removed from inventory',
            time: '1 day ago',
            icon: 'fas fa-edit',
            color: '#ffc107'
        },
        {
            type: 'transfer',
            title: 'Stock Transfer: Pizza Dough',
            description: 'Transferred 20 units to Branch B',
            time: '2 days ago',
            icon: 'fas fa-exchange-alt',
            color: '#8B4543'
        }
    ];
    
    const historyList = document.getElementById('processingHistoryList');
    historyList.innerHTML = '';
    
    historyData.forEach(item => {
        const historyItem = document.createElement('div');
        historyItem.className = 'activity-item';
        historyItem.innerHTML = `
            <div class="activity-icon-small" style="background: ${item.color};">
                <i class="${item.icon}"></i>
            </div>
            <div class="activity-text">
                <div class="activity-title">${item.title}</div>
                <div class="activity-time">${item.description}</div>
                <small class="text-muted">${item.time}</small>
            </div>
        `;
        historyList.appendChild(historyItem);
    });
}

function showProcessingAlert(message, type) {
    // Create alert element
    const alertDiv = document.createElement('div');
    alertDiv.className = `alert alert-${type} alert-dismissible fade show processing-alert`;
    alertDiv.style.cssText = `
        position: fixed;
        top: 20px;
        right: 20px;
        z-index: 9999;
        min-width: 300px;
        border-radius: 10px;
        box-shadow: 0 4px 15px rgba(0,0,0,0.2);
        animation: slideInRight 0.3s ease;
    `;
    
    alertDiv.innerHTML = `
        <i class="fas fa-${type === 'success' ? 'check-circle' : type === 'warning' ? 'exclamation-triangle' : 'info-circle'} me-2"></i>
        ${message}
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    `;
    
    document.body.appendChild(alertDiv);
    
    // Auto-remove after 3 seconds
    setTimeout(() => {
        if (alertDiv.parentNode) {
            alertDiv.remove();
        }
    }, 3000);
}

// Add CSS animation for alerts
const style = document.createElement('style');
style.textContent = `
    @keyframes slideInRight {
        from {
            transform: translateX(100%);
            opacity: 0;
        }
        to {
            transform: translateX(0);
            opacity: 1;
        }
    }
`;
document.head.appendChild(style);
</script>

<?php include('footer.php'); ?>
