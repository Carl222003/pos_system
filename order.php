<?php

require_once 'db_connect.php';
require_once 'auth_function.php';

checkOrderAccess();

$confData = getConfigData($pdo);

// Get the current logged-in user's information
$currentUser = null;
if (isset($_SESSION['user_id'])) {
    try {
        $userQuery = "SELECT user_name, first_name, last_name FROM pos_users WHERE user_id = ?";
        $userStmt = $pdo->prepare($userQuery);
        $userStmt->execute([$_SESSION['user_id']]);
        $currentUser = $userStmt->fetch(PDO::FETCH_ASSOC);
    } catch (Exception $e) {
        error_log('Error fetching current user: ' . $e->getMessage());
    }
}

// Get the display name for the current user
$currentCashierName = 'Unknown Cashier';
if ($currentUser) {
    if (!empty($currentUser['user_name'])) {
        $currentCashierName = $user['user_name'];
    } elseif (!empty($currentUser['first_name']) || !empty($currentUser['last_name'])) {
        $currentCashierName = trim($currentUser['first_name'] . ' ' . $currentUser['last_name']);
    }
}

include('header.php');
?>

<style>
    .kds-dashboard {
        background: #ffffff;
        min-height: 100vh;
        color: #333;
        font-family: 'Inter', sans-serif;
        padding: 20px;
    }
    
    .kds-header {
        background: #f8f9fa;
        padding: 20px 30px;
        border-bottom: 2px solid #e9ecef;
        display: flex;
        justify-content: space-between;
        align-items: center;
        margin-bottom: 30px;
        border-radius: 8px;
    }
    
    .header-stats {
        display: flex;
        align-items: center;
        gap: 30px;
    }
    
    .stat-item {
        display: flex;
        align-items: center;
        gap: 10px;
        font-size: 0.9rem;
        color: #666;
    }
    
    .stat-value {
        font-weight: 600;
        color: #333;
    }
    
    .order-summary {
        display: flex;
        align-items: center;
        gap: 20px;
        font-size: 0.9rem;
    }
    
    .summary-item {
        display: flex;
        align-items: center;
        gap: 8px;
    }
    
    .summary-count {
        background: #8B4543;
        color: white;
        padding: 4px 8px;
        border-radius: 4px;
    font-weight: 600;
        font-size: 0.8rem;
    }
    
    .nav-arrows {
        display: flex;
        gap: 10px;
        margin-left: 20px;
    }
    
    .nav-arrow {
        background: #8B4543;
        border: none;
        color: white;
        width: 30px;
        height: 30px;
        border-radius: 4px;
        cursor: pointer;
    display: flex;
    align-items: center;
    justify-content: center;
        transition: all 0.2s ease;
    }
    
    .nav-arrow:hover {
        background: #723937;
        transform: translateY(-1px);
    }
    
    .kds-main {
        padding: 20px 0;
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(350px, 1fr));
        gap: 20px;
        max-width: 1400px;
        margin: 0 auto;
    }
    
    .order-column {
        background: #ffffff;
        border: 1px solid #e9ecef;
        border-radius: 8px;
        padding: 20px;
        display: flex;
        flex-direction: column;
        gap: 15px;
        box-shadow: 0 2px 8px rgba(0, 0, 0, 0.08);
    }
    
    .column-header {
        text-align: center;
        padding-bottom: 15px;
        border-bottom: 2px solid #e9ecef;
        margin-bottom: 20px;
    }
    
    .column-title {
        font-size: 1.2rem;
        font-weight: 700;
        margin-bottom: 8px;
        text-transform: uppercase;
        letter-spacing: 1px;
    }
    
    .column-title.dine-in { color: #28a745; }
    .column-title.take-out { color: #6f42c1; }
    .column-title.delivery { color: #fd7e14; }
    .column-title.drive-thru { color: #17a2b8; }
    
    .column-staff {
        font-size: 0.85rem;
        color: #666;
        margin-bottom: 5px;
    }
    
    .order-card {
        background: #ffffff;
    border-radius: 8px;
        padding: 16px;
        border: 1px solid #e9ecef;
        transition: all 0.2s ease;
        cursor: pointer;
        position: relative;
        box-shadow: 0 1px 3px rgba(0, 0, 0, 0.1);
    }
    
    .order-card:hover {
        transform: translateY(-2px);
        box-shadow: 0 4px 12px rgba(0, 0, 0, 0.15);
    border-color: #8B4543;
}

    /* Status-based styling */
    .order-card.pending {
        border-left: 4px solid #ffc107;
    }
    
    .order-card.preparing {
        border-left: 4px solid #17a2b8;
    }
    
    .order-card.ready {
        border-left: 4px solid #28a745;
    }
    
    .order-card.completed {
        border-left: 4px solid #6c757d;
        opacity: 0.7;
    }
    
    .order-header {
        display: flex;
        justify-content: space-between;
        align-items: center;
        margin-bottom: 12px;
        padding-bottom: 8px;
        border-bottom: 1px solid #e9ecef;
    }
    
    .order-number {
        font-size: 1.1rem;
        font-weight: 700;
        color: #333;
    }
    
    .order-time {
        font-size: 0.85rem;
        color: #666;
        text-align: right;
    }
    
    .order-items {
        display: flex;
        flex-direction: column;
        gap: 8px;
    }
    
    .order-item {
        display: flex;
        justify-content: space-between;
        align-items: flex-start;
        padding: 6px 0;
        border-bottom: 1px solid #f8f9fa;
    }
    
    .order-item:last-child {
        border-bottom: none;
    }
    
    .item-details {
        flex: 1;
    }
    
    .item-name {
        font-weight: 600;
        color: #333;
        margin-bottom: 3px;
        font-size: 0.9rem;
    }
    
    .item-modifications {
        font-size: 0.8rem;
        color: #666;
        line-height: 1.3;
        margin-left: 15px;
    }
    
    .modification {
        display: inline-block;
        margin-right: 8px;
        margin-bottom: 2px;
    }
    
    .modification.add {
        color: #28a745;
    }
    
    .modification.remove {
        color: #dc3545;
    }
    
    .item-quantity {
        background: #8B4543;
        color: white;
        padding: 3px 6px;
        border-radius: 3px;
        font-weight: 600;
        font-size: 0.8rem;
        min-width: 25px;
        text-align: center;
    }
    
    .item-label {
        position: absolute;
        top: 8px;
        right: 8px;
        background: #6c757d;
        color: white;
        padding: 2px 6px;
        border-radius: 3px;
        font-size: 0.7rem;
        font-weight: 600;
    }
    
    .order-actions {
        display: flex;
        gap: 8px;
        margin-top: 12px;
        padding-top: 12px;
        border-top: 1px solid #e9ecef;
    }
    
    .status-btn {
        background: #f8f9fa;
        border: 1px solid #dee2e6;
        color: #495057;
        padding: 6px 10px;
        border-radius: 4px;
        font-size: 0.75rem;
        cursor: pointer;
        transition: all 0.2s ease;
        display: flex;
        align-items: center;
        gap: 4px;
    }
    
    .status-btn:hover:not(:disabled) {
        background: #8B4543;
        color: white;
        border-color: #8B4543;
        transform: translateY(-1px);
    }
    
    .status-btn:disabled {
        opacity: 0.5;
        cursor: not-allowed;
    }
    
    .order-status-badge {
        position: absolute;
        top: 8px;
        left: 8px;
        padding: 4px 8px;
        border-radius: 4px;
        font-size: 0.7rem;
    font-weight: 600;
        text-transform: uppercase;
    }
    
    .order-status-badge.pending {
        background: #fff3cd;
        color: #856404;
        border: 1px solid #ffeaa7;
    }
    
    .order-status-badge.preparing {
        background: #d1ecf1;
        color: #0c5460;
        border: 1px solid #bee5eb;
    }
    
    .order-status-badge.ready {
        background: #d4edda;
        color: #155724;
        border: 1px solid #c3e6cb;
    }
    
    .order-status-badge.completed {
        background: #e2e3e5;
        color: #383d41;
        border: 1px solid #d6d8db;
    }
    
    .empty-state {
    display: flex;
    align-items: center;
    justify-content: center;
        height: 300px;
        color: #6c757d;
        font-style: italic;
        font-size: 1.1rem;
        background: #f8f9fa;
        border-radius: 12px;
        border: 2px dashed #dee2e6;
        text-align: center;
        padding: 40px;
    }
    
    .footer-bar {
        background: rgba(52, 58, 64, 0.85);
        backdrop-filter: blur(10px);
        -webkit-backdrop-filter: blur(10px);
        color: white;
        padding: 15px 30px;
        margin-top: 30px;
        border-radius: 12px;
        display: flex;
        justify-content: space-between;
        align-items: center;
        border: 1px solid rgba(255, 255, 255, 0.1);
        box-shadow: 0 8px 32px rgba(0, 0, 0, 0.1);
    }
    
    .footer-nav {
        display: flex;
        gap: 15px;
    }
    
    .footer-btn {
        background: rgba(255, 255, 255, 0.1);
        border: 1px solid rgba(255, 255, 255, 0.2);
        color: white;
        padding: 8px 16px;
        border-radius: 6px;
        cursor: pointer;
    transition: all 0.3s ease;
        font-size: 0.9rem;
        backdrop-filter: blur(5px);
        -webkit-backdrop-filter: blur(5px);
    }
    
    .footer-btn:hover {
        background: rgba(255, 255, 255, 0.2);
        border-color: rgba(255, 255, 255, 0.3);
        transform: translateY(-1px);
        box-shadow: 0 4px 12px rgba(0, 0, 0, 0.15);
    }
    
    .footer-info {
        display: flex;
        align-items: center;
        gap: 20px;
        font-size: 0.9rem;
    }
    
    .system-status {
        display: flex;
        align-items: center;
        gap: 8px;
    }
    
    .status-indicator {
        width: 12px;
        height: 12px;
        background: rgba(40, 167, 69, 0.8);
        border-radius: 50%;
        animation: pulse 2s infinite;
        box-shadow: 0 0 10px rgba(40, 167, 69, 0.5);
        border: 1px solid rgba(255, 255, 255, 0.2);
    }
    
    @keyframes pulse {
        0% { 
            opacity: 1; 
            transform: scale(1);
            box-shadow: 0 0 10px rgba(40, 167, 69, 0.5);
        }
        50% { 
            opacity: 0.7; 
            transform: scale(1.1);
            box-shadow: 0 0 15px rgba(40, 167, 69, 0.7);
        }
        100% { 
            opacity: 1; 
            transform: scale(1);
            box-shadow: 0 0 10px rgba(40, 167, 69, 0.5);
        }
    }
    
    @media (max-width: 1400px) {
        .kds-main {
            grid-template-columns: repeat(auto-fit, minmax(300px, 1fr));
        }
    }
    
    @media (max-width: 1200px) {
        .kds-main {
            grid-template-columns: repeat(auto-fit, minmax(280px, 1fr));
        }
    }
    
    @media (max-width: 768px) {
        .kds-main {
            grid-template-columns: 1fr;
        }
        
        .kds-header {
            flex-direction: column;
            gap: 20px;
            text-align: center;
        }
        
        .order-summary {
            flex-wrap: wrap;
            justify-content: center;
        }
        
        .footer-bar {
            flex-direction: column;
            gap: 15px;
            text-align: center;
        }
}
</style>

<div class="kds-dashboard">
    <div class="kds-header">
        <div class="header-stats">
            <div class="stat-item">
                <i class="fas fa-clock"></i>
                <span>Avg: <span class="stat-value">00:00</span></span>
            </div>
            <div class="stat-item">
                <i class="fas fa-clock"></i>
                <span>Highest: <span class="stat-value">00:00</span></span>
            </div>
            <div class="stat-item">
                <i class="fas fa-users"></i>
                <span class="stat-value">150</span>
            </div>
        </div>
        
        <div class="order-summary">
            <div class="summary-item">
                <span>All:</span>
                <span class="summary-count" id="totalOrders">0</span>
            </div>
            <div class="summary-item">
                <span>Dine In:</span>
                <span class="summary-count" id="dineInCount">0</span>
            </div>
            <div class="summary-item">
                <span>Take Out:</span>
                <span class="summary-count" id="takeOutCount">0</span>
            </div>
            <div class="summary-item">
                <span>Delivery:</span>
                <span class="summary-count" id="deliveryCount">0</span>
            </div>
            <div class="summary-item">
                <span>Drive Thru:</span>
                <span class="summary-count" id="driveThruCount">0</span>
            </div>
            
            <div class="nav-arrows">
                <button class="nav-arrow" onclick="navigateColumns(-1)">
                    <i class="fas fa-chevron-left"></i>
                </button>
                <button class="nav-arrow" onclick="navigateColumns(1)">
                    <i class="fas fa-chevron-right"></i>
                </button>
            </div>
        </div>
    </div>
    
    <div class="kds-main" id="kdsMain">
        <!-- Columns will be created dynamically based on orders -->
    </div>
    
    <div class="footer-bar">
        <div class="footer-nav">
            <button class="footer-btn" onclick="previousPage()">
                <i class="fas fa-chevron-left"></i> PREVIOUS
            </button>
            <button class="footer-btn" onclick="nextPage()">
                NEXT <i class="fas fa-chevron-right"></i>
            </button>
            <button class="footer-btn" onclick="searchOrders()">
                <i class="fas fa-search"></i> SEARCH
            </button>
            <button class="footer-btn" onclick="recallOrder()">
                <i class="fas fa-history"></i> RECALL
            </button>
            <button class="footer-btn" onclick="printOrder()">
                <i class="fas fa-print"></i> PRINT
            </button>
        </div>
        
        <div class="footer-info">
            <div class="page-info">Page: 1 of 1</div>
            <div class="system-status">
                <div class="status-indicator"></div>
                <span id="currentTime">11:44:00 PM</span>
            </div>
            <div class="system-version">[KIT ASSEMBLY] v1.303</div>
        </div>
    </div>
</div>

<script>
let allOrders = [];
let currentPage = 1;
let currentCashierName = '<?php echo htmlspecialchars($currentCashierName); ?>';

$(document).ready(function() {
    loadOrders();
    updateCurrentTime();
    setInterval(updateCurrentTime, 1000);
});

function loadOrders() {
    $.ajax({
        url: 'get_order_data.php',
        method: 'GET',
        dataType: 'json',
        success: function(response) {
            if (response.success) {
                allOrders = response.orders || [];
                createDynamicColumns();
                updateOrderCounts();
            } else {
                console.error('Error loading orders:', response.message);
                showEmptyState();
            }
        },
        error: function(xhr, status, error) {
            console.error('AJAX Error:', error);
            showEmptyState();
        }
    });
}

function createDynamicColumns() {
    const mainContainer = $('#kdsMain');
    mainContainer.empty();
    
    if (allOrders.length === 0) {
        showEmptyState();
        return;
    }
    
    // Group orders by type
    const orderTypes = {};
    allOrders.forEach(order => {
        if (!orderTypes[order.order_type]) {
            orderTypes[order.order_type] = [];
        }
        orderTypes[order.order_type].push(order);
    });
    
    // Create columns for each order type
    Object.keys(orderTypes).forEach(orderType => {
        const orders = orderTypes[orderType];
        const columnCount = Math.ceil(orders.length / 8); // Max 8 orders per column
        
        for (let i = 0; i < columnCount; i++) {
            const startIndex = i * 8;
            const endIndex = startIndex + 8;
            const columnOrders = orders.slice(startIndex, endIndex);
            
            const column = createOrderColumn(orderType, columnOrders, i + 1);
            mainContainer.append(column);
                }
            });
        }

function createOrderColumn(orderType, orders, columnNumber) {
    const orderTypeClass = orderType.toLowerCase().replace(' ', '-');
    const orderTypeColor = getOrderTypeColor(orderType);
    
    const column = $(`
        <div class="order-column">
            <div class="column-header">
                <div class="column-title ${orderTypeClass}">${orderType}</div>
                <div class="column-staff" id="cashier_${orderTypeClass}_${columnNumber}">
                    Cashier: ${getColumnCashierName(orders)}
                </div>
            </div>
            <div class="orders-container" id="orders_${orderTypeClass}_${columnNumber}">
                <!-- Orders will be populated here -->
            </div>
        </div>
    `);
    
    // Populate orders in this column
    const ordersContainer = column.find('.orders-container');
    orders.forEach(order => {
        const orderCard = createOrderCard(order);
        ordersContainer.append(orderCard);
    });
    
    return column;
}

function getOrderTypeColor(orderType) {
    switch (orderType) {
        case 'DINE IN': return '#28a745';
        case 'TAKE OUT': return '#6f42c1';
        case 'DELIVERY': return '#fd7e14';
        case 'DRIVE THRU': return '#17a2b8';
        default: return '#6c757d';
    }
}

function getColumnCashierName(orders) {
    if (orders.length === 0) return currentCashierName;
    
    // Get the cashier name from the first order
    const firstOrder = orders[0];
    return firstOrder.cashier_name || currentCashierName;
}

function showEmptyState() {
    const mainContainer = $('#kdsMain');
    mainContainer.html(`
        <div class="empty-state">
            <div>
                <i class="fas fa-clipboard-list" style="font-size: 3rem; margin-bottom: 20px; color: #8B4543;"></i>
                <h3>No Orders Yet</h3>
                <p>Orders will appear here when cashiers start taking them.</p>
                <p style="margin-top: 20px; font-size: 0.9rem; color: #999;">
                    <strong>Current Cashier:</strong> ${currentCashierName}
                </p>
            </div>
        </div>
    `);
}

function createOrderCard(order) {
    const orderTime = formatTime(order.created_at);
    const readyTime = calculateReadyTime(order.created_at);
    const statusClass = getStatusClass(order.status);
    const statusLabel = getStatusLabel(order.status);
    
    return `
        <div class="order-card ${statusClass}" data-order-id="${order.id}">
            <div class="item-label">S1</div>
            <div class="order-status-badge ${statusClass}">${statusLabel}</div>
            <div class="order-header">
                <div class="order-number">#${order.order_number}</div>
                <div class="order-time">${orderTime}<br>${readyTime}</div>
            </div>
            <div class="order-items">
                ${generateOrderItemsHTML(order.items)}
            </div>
            <div class="order-actions">
                <button class="status-btn" onclick="updateOrderStatus(${order.id}, 'PREPARING')" ${order.status === 'PREPARING' ? 'disabled' : ''}>
                    <i class="fas fa-fire"></i> Start
                </button>
                <button class="status-btn" onclick="updateOrderStatus(${order.id}, 'READY')" ${order.status === 'READY' ? 'disabled' : ''}>
                    <i class="fas fa-check"></i> Ready
                </button>
                <button class="status-btn" onclick="updateOrderStatus(${order.id}, 'COMPLETED')" ${order.status === 'COMPLETED' ? 'disabled' : ''}>
                    <i class="fas fa-flag-checkered"></i> Complete
                </button>
            </div>
        </div>
    `;
}

function generateOrderItemsHTML(items) {
    if (!items || !Array.isArray(items)) return '<div class="text-center text-muted">No items</div>';
    
    return items.map(item => {
        const modifications = item.modifications || [];
        const addMods = modifications.filter(mod => mod.type === 'add').map(mod => `<span class="modification add">*${mod.description}</span>`);
        const removeMods = modifications.filter(mod => mod.type === 'remove').map(mod => `<span class="modification remove">NO ${mod.description}</span>`);
        
        return `
            <div class="order-item">
                <div class="item-details">
                    <div class="item-name">${item.quantity} ${item.name}</div>
                    <div class="item-modifications">
                        ${addMods.join('')}
                        ${removeMods.join('')}
                    </div>
                </div>
                <div class="item-quantity">${item.quantity}</div>
                </div>
            `;
    }).join('');
}

function updateOrderCounts() {
    const total = allOrders.length;
    const dineIn = allOrders.filter(order => order.order_type === 'DINE IN').length;
    const takeOut = allOrders.filter(order => order.order_type === 'TAKE OUT').length;
    const delivery = allOrders.filter(order => order.order_type === 'DELIVERY').length;
    const driveThru = allOrders.filter(order => order.order_type === 'DRIVE THRU').length;
    
    $('#totalOrders').text(total);
    $('#dineInCount').text(dineIn);
    $('#takeOutCount').text(takeOut);
    $('#deliveryCount').text(delivery);
    $('#driveThruCount').text(driveThru);
}

function formatTime(timestamp) {
    const date = new Date(timestamp);
    return date.toLocaleTimeString('en-US', { 
        hour: '2-digit', 
        minute: '2-digit',
        hour12: true 
    });
}

function calculateReadyTime(timestamp) {
    const date = new Date(timestamp);
    date.setMinutes(date.getMinutes() + 30); // Add 30 minutes for preparation
    return date.toLocaleTimeString('en-US', { 
        hour: '2-digit', 
        minute: '2-digit',
        hour12: true 
    });
}

function navigateColumns(direction) {
    const mainContainer = $('#kdsMain');
    const currentScroll = mainContainer.scrollLeft();
    const scrollAmount = 370; // Column width + gap
    
    mainContainer.animate({
        scrollLeft: currentScroll + (direction * scrollAmount)
    }, 300);
}

function updateOrderStatus(orderId, newStatus) {
    $.ajax({
        url: 'update_order_status.php',
        method: 'POST',
        contentType: 'application/json',
        data: JSON.stringify({
            order_id: orderId,
            status: newStatus
        }),
        success: function(response) {
            if (response.success) {
                // Update the order card visually
                updateOrderCardStatus(orderId, newStatus);
                // Refresh the dashboard after a short delay
                setTimeout(() => {
                    loadOrders();
                }, 1000);
            } else {
                alert('Error updating order status: ' + response.message);
            }
        },
        error: function(xhr, status, error) {
            alert('Error updating order status: ' + error);
        }
    });
}

function updateOrderCardStatus(orderId, newStatus) {
    const orderCard = $(`.order-card[data-order-id="${orderId}"]`);
    if (orderCard.length) {
        // Remove old status classes
        orderCard.removeClass('pending preparing ready completed');
        // Add new status class
        orderCard.addClass(newStatus.toLowerCase());
        
        // Update status badge
        const statusBadge = orderCard.find('.order-status-badge');
        statusBadge.removeClass('pending preparing ready completed');
        statusBadge.addClass(newStatus.toLowerCase());
        statusBadge.text(getStatusLabel(newStatus));
        
        // Update button states
        updateButtonStates(orderCard, newStatus);
    }
}

function updateButtonStates(orderCard, status) {
    const startBtn = orderCard.find('button[onclick*="PREPARING"]');
    const readyBtn = orderCard.find('button[onclick*="READY"]');
    const completeBtn = orderCard.find('button[onclick*="COMPLETED"]');
    
    // Reset all buttons
    startBtn.prop('disabled', false);
    readyBtn.prop('disabled', false);
    completeBtn.prop('disabled', false);
    
    // Disable appropriate buttons based on status
    switch (status) {
        case 'PREPARING':
            startBtn.prop('disabled', true);
            break;
        case 'READY':
            startBtn.prop('disabled', true);
            readyBtn.prop('disabled', true);
            break;
        case 'COMPLETED':
            startBtn.prop('disabled', true);
            readyBtn.prop('disabled', true);
            completeBtn.prop('disabled', true);
            break;
    }
}

function getStatusClass(status) {
    switch (status) {
        case 'PENDING': return 'pending';
        case 'PREPARING': return 'preparing';
        case 'READY': return 'ready';
        case 'COMPLETED': return 'completed';
        default: return 'pending';
    }
}

function getStatusLabel(status) {
    switch (status) {
        case 'PENDING': return 'Pending';
        case 'PREPARING': return 'Preparing';
        case 'READY': return 'Ready';
        case 'COMPLETED': return 'Completed';
        default: return 'Pending';
    }
}

function updateCurrentTime() {
    const now = new Date();
    const timeString = now.toLocaleTimeString('en-US', {
        hour: '2-digit',
        minute: '2-digit',
        second: '2-digit',
        hour12: true
    });
    $('#currentTime').text(timeString);
}

// Footer navigation functions
function previousPage() {
    if (currentPage > 1) {
        currentPage--;
        loadOrders();
    }
}

function nextPage() {
    currentPage++;
    loadOrders();
}

function searchOrders() {
    // Implement search functionality
    alert('Search functionality coming soon!');
}

function recallOrder() {
    // Implement order recall functionality
    alert('Order recall functionality coming soon!');
}

function printOrder() {
    // Implement print functionality
    window.print();
}

// Auto-refresh functionality
function startAutoRefresh() {
    setInterval(() => {
        loadOrders();
    }, 30000); // Refresh every 30 seconds
}

// Initialize auto-refresh when page loads
$(document).ready(function() {
    loadOrders();
    startAutoRefresh();
});
</script>

<?php include 'footer.php'; ?>