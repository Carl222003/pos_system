<?php
require_once 'db_connect.php';
require_once 'auth_function.php';

requireLogin();
if ($_SESSION['user_type'] !== 'Stockman') {
    echo "Access denied. Only Stockman can view this activity log.";
    exit();
}

// Get the current stockman's branch
$stockmanId = $_SESSION['user_id'];
$branchStmt = $pdo->prepare("SELECT branch_id FROM pos_user WHERE user_id = ?");
$branchStmt->execute([$stockmanId]);
$stockmanBranch = $branchStmt->fetchColumn();

// Check if branch_id column exists in pos_activity_log table
$checkColumn = $pdo->query("SHOW COLUMNS FROM pos_activity_log LIKE 'branch_id'");
$branchIdExists = $checkColumn->rowCount() > 0;

// Fetch activity logs for this stockman and their branch
if ($branchIdExists) {
    $sql = "SELECT l.*, u.user_name, b.branch_name 
            FROM pos_activity_log l 
            JOIN pos_user u ON l.user_id = u.user_id 
            LEFT JOIN pos_branch b ON l.branch_id = b.branch_id 
            WHERE (l.user_id = ? OR l.branch_id = ?) 
            ORDER BY l.created_at DESC 
            LIMIT 200";
    $stmt = $pdo->prepare($sql);
    $stmt->execute([$stockmanId, $stockmanBranch]);
} else {
    // Fallback query if branch_id column doesn't exist
    $sql = "SELECT l.*, u.user_name, NULL as branch_name 
            FROM pos_activity_log l 
            JOIN pos_user u ON l.user_id = u.user_id 
            WHERE l.user_id = ? 
            ORDER BY l.created_at DESC 
            LIMIT 200";
    $stmt = $pdo->prepare($sql);
    $stmt->execute([$stockmanId]);
}
$logs = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Helper to group logs by activity type
function groupStockmanLogs($logs) {
    $groups = [
        'Stock Management' => [],
        'Ingredient Requests' => [],
        'Delivery Updates' => [],
        'Inventory' => [],
        'Others' => []
    ];
    
    foreach ($logs as $log) {
        $action = strtolower($log['action'] . ' ' . $log['details']);
        
        if (strpos($action, 'stock') !== false || 
            strpos($action, 'inventory') !== false ||
            strpos($action, 'adjust') !== false) {
            $groups['Stock Management'][] = $log;
        } elseif (strpos($action, 'request') !== false || 
                  strpos($action, 'ingredient') !== false) {
            $groups['Ingredient Requests'][] = $log;
        } elseif (strpos($action, 'delivery') !== false || 
                  strpos($action, 'received') !== false ||
                  strpos($action, 'returned') !== false) {
            $groups['Delivery Updates'][] = $log;
        } elseif (strpos($action, 'inventory') !== false) {
            $groups['Inventory'][] = $log;
        } else {
            $groups['Others'][] = $log;
        }
    }
    return $groups;
}

$groupedLogs = groupStockmanLogs($logs);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Stockman Activity Log</title>
    <link rel="stylesheet" href="asset/css/styles.css">
    <link rel="stylesheet" href="asset/vendor/bootstrap/bootstrap.min.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <style>
    body {
        background: #f7f6f6;
        font-family: 'Segoe UI', system-ui, -apple-system, sans-serif;
    }
    .nav-tabs {
        border-bottom: none;
    }
    .nav-tabs .nav-link.active {
        background-color: #8B4543;
        color: #fff;
        border: none;
        border-radius: 1.2rem 1.2rem 0 0;
        box-shadow: 0 2px 12px rgba(139, 69, 67, 0.10);
        font-weight: 600;
        letter-spacing: 0.5px;
    }
    .nav-tabs .nav-link {
        color: #8B4543;
        border: none;
        font-weight: 500;
        border-radius: 1.2rem 1.2rem 0 0;
        margin-right: 0.2rem;
        transition: background 0.18s, color 0.18s;
        font-size: 1.08rem;
        display: flex;
        align-items: center;
        gap: 0.5em;
    }
    .nav-tabs .nav-link:not(.active) {
        color: #8B4543 !important;
        opacity: 1 !important;
        background: none !important;
        cursor: pointer !important;
    }
    .nav-tabs .nav-link:not(.active):hover {
        background: #f3e9e8;
        color: #8B4543;
    }
    .card.mb-4 {
        transition: box-shadow 0.25s, transform 0.18s;
        box-shadow: 0 2px 12px rgba(139, 69, 67, 0.07);
        border-radius: 1.1rem;
        border: none;
        margin-top: 0.5rem;
    }
    .card.mb-4:hover {
        box-shadow: 0 8px 32px rgba(139, 69, 67, 0.18);
        transform: translateY(-4px) scale(1.012);
        border: 1.5px solid #8B4543;
        background: #fdf7f6;
    }
    .card-header {
        background: #fff6f4;
        font-size: 1.1rem;
        font-weight: 600;
        color: #8B4543;
        border-bottom: 1px solid #e0e0e0;
        border-radius: 1.1rem 1.1rem 0 0;
        letter-spacing: 0.2px;
        display: flex;
        align-items: center;
        gap: 0.5em;
    }
    .table {
        background: #fff;
        border-radius: 0.75rem;
        overflow: hidden;
        margin-bottom: 0;
    }
    .table thead th {
        background: #f8f9fa;
        color: #8B4543;
        font-weight: 600;
        border-bottom: 2px solid #e0e0e0;
        letter-spacing: 0.5px;
    }
    .table-hover tbody tr:hover {
        background: #f3e9e8;
    }
    .tab-content {
        margin-top: -0.5rem;
    }
    .activity-icon {
        font-size: 1.1em;
        margin-right: 0.2em;
    }
    .no-logs-row td {
        color: #b0a6a6;
        font-style: italic;
        background: #f9f6f6;
        border-bottom: none;
    }
    .activity-log-title {
        color: #8B4543;
        font-size: 2.2rem;
        font-weight: 700;
        letter-spacing: 0.7px;
        margin-bottom: 1.7rem;
        margin-top: 1.2rem;
        display: flex;
        align-items: center;
        gap: 0.7rem;
        position: relative;
        background: none;
        border: none;
        animation: fadeInDown 0.7s;
    }
    .activity-log-title .log-icon {
        font-size: 1.5em;
        color: #8B4543;
        opacity: 0.92;
    }
    .activity-log-title::after {
        content: '';
        display: block;
        position: absolute;
        left: 0;
        bottom: -7px;
        width: 100%;
        height: 5px;
        border-radius: 3px;
        background: linear-gradient(90deg, #8B4543 0%, #b97a6a 100%);
        opacity: 0.18;
    }
    @keyframes fadeInDown {
        from { opacity: 0; transform: translateY(-18px); }
        to { opacity: 1; transform: translateY(0); }
    }
    .badge {
        font-size: 0.75em;
        padding: 0.4em 0.6em;
        border-radius: 0.5rem;
    }
    .badge-stock {
        background-color: #17a2b8;
        color: white;
    }
    .badge-request {
        background-color: #28a745;
        color: white;
    }
    .badge-delivery {
        background-color: #ffc107;
        color: #212529;
    }
    .badge-inventory {
        background-color: #6f42c1;
        color: white;
    }
    .badge-other {
        background-color: #6c757d;
        color: white;
    }
    @media (max-width: 600px) {
        .card.mb-4, .card-header, .table {
            border-radius: 0.7rem !important;
        }
        .nav-tabs .nav-link {
            font-size: 0.98rem;
            padding: 0.5rem 0.7rem;
        }
    }
    </style>
</head>
<body>
    <?php include('header.php'); ?>
    
    <div class="container-fluid px-4">
        <h1 class="activity-log-title">
            <span class="log-icon"><i class="fas fa-clipboard-list"></i></span>
            Stockman Activity Log
        </h1>
        
        <ul class="nav nav-tabs mb-3" id="activityTabs" role="tablist">
            <li class="nav-item" role="presentation">
                <button class="nav-link active" id="stock-tab" data-bs-toggle="tab" data-bs-target="#stock" type="button" role="tab">
                    <i class="fas fa-boxes"></i> Stock Management
                </button>
            </li>
            <li class="nav-item" role="presentation">
                <button class="nav-link" id="request-tab" data-bs-toggle="tab" data-bs-target="#request" type="button" role="tab">
                    <i class="fas fa-clipboard"></i> Ingredient Requests
                </button>
            </li>
            <li class="nav-item" role="presentation">
                <button class="nav-link" id="delivery-tab" data-bs-toggle="tab" data-bs-target="#delivery" type="button" role="tab">
                    <i class="fas fa-truck"></i> Delivery Updates
                </button>
            </li>
            <li class="nav-item" role="presentation">
                <button class="nav-link" id="inventory-tab" data-bs-toggle="tab" data-bs-target="#inventory" type="button" role="tab">
                    <i class="fas fa-warehouse"></i> Inventory
                </button>
            </li>
            <li class="nav-item" role="presentation">
                <button class="nav-link" id="other-tab" data-bs-toggle="tab" data-bs-target="#other" type="button" role="tab">
                    <i class="fas fa-ellipsis-h"></i> Others
                </button>
            </li>
        </ul>
        
        <div class="tab-content" id="activityTabsContent">
            <!-- Stock Management -->
            <div class="tab-pane fade show active" id="stock" role="tabpanel">
                <div class="card mb-4">
                    <div class="card-header">
                        <i class="fas fa-boxes"></i> Stock Management Activities
                    </div>
                    <div class="card-body">
                        <table class="table table-bordered table-hover">
                            <thead>
                                <tr>
                                    <th>Date & Time</th>
                                    <th>User</th>
                                    <th>Action</th>
                                    <th>Details</th>
                                    <th>Branch</th>
                                </tr>
                            </thead>
                            <tbody>
                            <?php if (empty($groupedLogs['Stock Management'])): ?>
                                <tr class="no-logs-row">
                                    <td colspan="5" class="text-center">No stock management activities found.</td>
                                </tr>
                            <?php else: ?>
                                <?php foreach ($groupedLogs['Stock Management'] as $log): ?>
                                    <tr>
                                        <td><?php echo date('M j, Y g:i A', strtotime($log['created_at'])); ?></td>
                                        <td><?php echo htmlspecialchars($log['user_name']); ?></td>
                                        <td>
                                            <span class="badge badge-stock">
                                                <i class="fas fa-boxes activity-icon"></i>
                                                <?php echo htmlspecialchars($log['action']); ?>
                                            </span>
                                        </td>
                                        <td><?php echo htmlspecialchars($log['details']); ?></td>
                                        <td><?php echo htmlspecialchars($log['branch_name'] ?? 'N/A'); ?></td>
                                    </tr>
                                <?php endforeach; ?>
                            <?php endif; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
            
            <!-- Ingredient Requests -->
            <div class="tab-pane fade" id="request" role="tabpanel">
                <div class="card mb-4">
                    <div class="card-header">
                        <i class="fas fa-clipboard"></i> Ingredient Request Activities
                    </div>
                    <div class="card-body">
                        <table class="table table-bordered table-hover">
                            <thead>
                                <tr>
                                    <th>Date & Time</th>
                                    <th>User</th>
                                    <th>Action</th>
                                    <th>Details</th>
                                    <th>Branch</th>
                                </tr>
                            </thead>
                            <tbody>
                            <?php if (empty($groupedLogs['Ingredient Requests'])): ?>
                                <tr class="no-logs-row">
                                    <td colspan="5" class="text-center">No ingredient request activities found.</td>
                                </tr>
                            <?php else: ?>
                                <?php foreach ($groupedLogs['Ingredient Requests'] as $log): ?>
                                    <tr>
                                        <td><?php echo date('M j, Y g:i A', strtotime($log['created_at'])); ?></td>
                                        <td><?php echo htmlspecialchars($log['user_name']); ?></td>
                                        <td>
                                            <span class="badge badge-request">
                                                <i class="fas fa-clipboard activity-icon"></i>
                                                <?php echo htmlspecialchars($log['action']); ?>
                                            </span>
                                        </td>
                                        <td><?php echo htmlspecialchars($log['details']); ?></td>
                                        <td><?php echo htmlspecialchars($log['branch_name'] ?? 'N/A'); ?></td>
                                    </tr>
                                <?php endforeach; ?>
                            <?php endif; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
            
            <!-- Delivery Updates -->
            <div class="tab-pane fade" id="delivery" role="tabpanel">
                <div class="card mb-4">
                    <div class="card-header">
                        <i class="fas fa-truck"></i> Delivery Update Activities
                    </div>
                    <div class="card-body">
                        <table class="table table-bordered table-hover">
                            <thead>
                                <tr>
                                    <th>Date & Time</th>
                                    <th>User</th>
                                    <th>Action</th>
                                    <th>Details</th>
                                    <th>Branch</th>
                                </tr>
                            </thead>
                            <tbody>
                            <?php if (empty($groupedLogs['Delivery Updates'])): ?>
                                <tr class="no-logs-row">
                                    <td colspan="5" class="text-center">No delivery update activities found.</td>
                                </tr>
                            <?php else: ?>
                                <?php foreach ($groupedLogs['Delivery Updates'] as $log): ?>
                                    <tr>
                                        <td><?php echo date('M j, Y g:i A', strtotime($log['created_at'])); ?></td>
                                        <td><?php echo htmlspecialchars($log['user_name']); ?></td>
                                        <td>
                                            <span class="badge badge-delivery">
                                                <i class="fas fa-truck activity-icon"></i>
                                                <?php echo htmlspecialchars($log['action']); ?>
                                            </span>
                                        </td>
                                        <td><?php echo htmlspecialchars($log['details']); ?></td>
                                        <td><?php echo htmlspecialchars($log['branch_name'] ?? 'N/A'); ?></td>
                                    </tr>
                                <?php endforeach; ?>
                            <?php endif; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
            
            <!-- Inventory -->
            <div class="tab-pane fade" id="inventory" role="tabpanel">
                <div class="card mb-4">
                    <div class="card-header">
                        <i class="fas fa-warehouse"></i> Inventory Activities
                    </div>
                    <div class="card-body">
                        <table class="table table-bordered table-hover">
                            <thead>
                                <tr>
                                    <th>Date & Time</th>
                                    <th>User</th>
                                    <th>Action</th>
                                    <th>Details</th>
                                    <th>Branch</th>
                                </tr>
                            </thead>
                            <tbody>
                            <?php if (empty($groupedLogs['Inventory'])): ?>
                                <tr class="no-logs-row">
                                    <td colspan="5" class="text-center">No inventory activities found.</td>
                                </tr>
                            <?php else: ?>
                                <?php foreach ($groupedLogs['Inventory'] as $log): ?>
                                    <tr>
                                        <td><?php echo date('M j, Y g:i A', strtotime($log['created_at'])); ?></td>
                                        <td><?php echo htmlspecialchars($log['user_name']); ?></td>
                                        <td>
                                            <span class="badge badge-inventory">
                                                <i class="fas fa-warehouse activity-icon"></i>
                                                <?php echo htmlspecialchars($log['action']); ?>
                                            </span>
                                        </td>
                                        <td><?php echo htmlspecialchars($log['details']); ?></td>
                                        <td><?php echo htmlspecialchars($log['branch_name'] ?? 'N/A'); ?></td>
                                    </tr>
                                <?php endforeach; ?>
                            <?php endif; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
            
            <!-- Others -->
            <div class="tab-pane fade" id="other" role="tabpanel">
                <div class="card mb-4">
                    <div class="card-header">
                        <i class="fas fa-ellipsis-h"></i> Other Activities
                    </div>
                    <div class="card-body">
                        <table class="table table-bordered table-hover">
                            <thead>
                                <tr>
                                    <th>Date & Time</th>
                                    <th>User</th>
                                    <th>Action</th>
                                    <th>Details</th>
                                    <th>Branch</th>
                                </tr>
                            </thead>
                            <tbody>
                            <?php if (empty($groupedLogs['Others'])): ?>
                                <tr class="no-logs-row">
                                    <td colspan="5" class="text-center">No other activities found.</td>
                                </tr>
                            <?php else: ?>
                                <?php foreach ($groupedLogs['Others'] as $log): ?>
                                    <tr>
                                        <td><?php echo date('M j, Y g:i A', strtotime($log['created_at'])); ?></td>
                                        <td><?php echo htmlspecialchars($log['user_name']); ?></td>
                                        <td>
                                            <span class="badge badge-other">
                                                <i class="fas fa-ellipsis-h activity-icon"></i>
                                                <?php echo htmlspecialchars($log['action']); ?>
                                            </span>
                                        </td>
                                        <td><?php echo htmlspecialchars($log['details']); ?></td>
                                        <td><?php echo htmlspecialchars($log['branch_name'] ?? 'N/A'); ?></td>
                                    </tr>
                                <?php endforeach; ?>
                            <?php endif; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script src="asset/vendor/bootstrap/bootstrap.bundle.min.js"></script>
    <script>
        // Add any additional JavaScript functionality here
        document.addEventListener('DOMContentLoaded', function() {
            // Initialize Bootstrap tabs
            var triggerTabList = [].slice.call(document.querySelectorAll('#activityTabs button'))
            triggerTabList.forEach(function (triggerEl) {
                var tabTrigger = new bootstrap.Tab(triggerEl)
                triggerEl.addEventListener('click', function (event) {
                    event.preventDefault()
                    tabTrigger.show()
                })
            })
        });
    </script>
</body>
</html> 