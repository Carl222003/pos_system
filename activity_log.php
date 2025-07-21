<?php
require_once 'db_connect.php';
require_once 'auth_function.php';

requireLogin();
if ($_SESSION['user_type'] !== 'Admin') {
    echo "Access denied. Only Admin can view the activity log.";
    exit();
}

// Fetch activity logs
$sql = "SELECT l.*, u.user_name FROM pos_activity_log l JOIN pos_user u ON l.user_id = u.user_id ORDER BY l.created_at DESC LIMIT 200";
$stmt = $pdo->prepare($sql);
$stmt->execute();
$logs = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Helper to group logs by entity
function groupLogsByTable($logs) {
    $groups = [
        'Users' => [],
        'Products' => [],
        'Categories' => [],
        'Branches' => [],
        'Ingredients' => [],
        'Others' => []
    ];
    foreach ($logs as $log) {
        $action = strtolower($log['action'] . ' ' . $log['details']);
        if (
            strpos($action, 'user') !== false ||
            strpos($action, 'added cashier') !== false ||
            strpos($action, 'added stockman') !== false
        ) {
            $groups['Users'][] = $log;
        } elseif (strpos($action, 'product') !== false) {
            $groups['Products'][] = $log;
        } elseif (strpos($action, 'category') !== false) {
            $groups['Categories'][] = $log;
        } elseif (strpos($action, 'branch') !== false) {
            $groups['Branches'][] = $log;
        } elseif (strpos($action, 'ingredient') !== false) {
            $groups['Ingredients'][] = $log;
        } else {
            $groups['Others'][] = $log;
        }
    }
    return $groups;
}
$groupedLogs = groupLogsByTable($logs);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Activity Log</title>
    <link rel="stylesheet" href="asset/css/styles.css">
    <link rel="stylesheet" href="asset/vendor/bootstrap/bootstrap.min.css">
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
    @media (max-width: 600px) {
        .card.mb-4, .card-header, .table {
            border-radius: 0.7rem !important;
        }
        .nav-tabs .nav-link {
            font-size: 0.98rem;
            padding: 0.5rem 0.7rem;
        }
    }
    .activity-log-container {
        max-width: 1460px;
        margin: 0 auto;
        padding: 0 18px;
    }
    .activity-log-card {
        background: #fff;
        border-radius: 1.1rem;
        box-shadow: 0 2px 12px rgba(139, 69, 67, 0.07);
        padding: 0 0 1.5rem 0;
        margin-bottom: 2.5rem;
        border: none;
    }
    @media (max-width: 900px) {
        .activity-log-container {
            max-width: 100%;
            padding: 0 4px;
        }
    }
    </style>
</head>
<body>
<?php include 'header.php'; ?>
<div class="activity-log-container">
    <h2 class="activity-log-title"><span class="log-icon"><i class="fas fa-clipboard-list"></i></span>Activity Log</h2>
    <div class="activity-log-card">
        <ul class="nav nav-tabs mb-3" id="logTabs" role="tablist">
            <li class="nav-item" role="presentation">
                <button class="nav-link active" id="users-tab" data-bs-toggle="tab" data-bs-target="#users" type="button" role="tab"><i class="fas fa-user activity-icon"></i>Users</button>
            </li>
            <li class="nav-item" role="presentation">
                <button class="nav-link" id="products-tab" data-bs-toggle="tab" data-bs-target="#products" type="button" role="tab"><i class="fas fa-box-open activity-icon"></i>Products</button>
            </li>
            <li class="nav-item" role="presentation">
                <button class="nav-link" id="categories-tab" data-bs-toggle="tab" data-bs-target="#categories" type="button" role="tab"><i class="fas fa-list-alt activity-icon"></i>Categories</button>
            </li>
            <li class="nav-item" role="presentation">
                <button class="nav-link" id="branches-tab" data-bs-toggle="tab" data-bs-target="#branches" type="button" role="tab"><i class="fas fa-store-alt activity-icon"></i>Branches</button>
            </li>
            <li class="nav-item" role="presentation">
                <button class="nav-link" id="ingredients-tab" data-bs-toggle="tab" data-bs-target="#ingredients" type="button" role="tab"><i class="fas fa-carrot activity-icon"></i>Ingredients</button>
            </li>
            <li class="nav-item" role="presentation">
                <button class="nav-link" id="others-tab" data-bs-toggle="tab" data-bs-target="#others" type="button" role="tab"><i class="fas fa-ellipsis-h activity-icon"></i>Others</button>
            </li>
        </ul>
        <div class="tab-content" id="logTabsContent">
            <?php foreach ($groupedLogs as $tab => $tabLogs): ?>
            <div class="tab-pane fade<?= $tab === 'Users' ? ' show active' : '' ?>" id="<?= strtolower($tab) ?>" role="tabpanel">
                <div class="card mb-4">
                    <div class="card-header d-flex justify-content-between align-items-center">
                        <div><i class="fas fa-clipboard-list me-1"></i> <?= htmlspecialchars($tab) ?> Activity Log</div>
                    </div>
                    <div class="card-body">
                        <table class="table table-bordered table-hover">
                            <thead>
                                <tr>
                                    <th>Date/Time</th>
                                    <th>User</th>
                                    <th>Action</th>
                                    <th>Details</th>
                                    <th>IP Address</th>
                                </tr>
                            </thead>
                            <tbody>
                            <?php if (count($tabLogs) === 0): ?>
                                <tr class="no-logs-row"><td colspan="5" class="text-center">No logs found.</td></tr>
                            <?php else: ?>
                                <?php foreach ($tabLogs as $log): ?>
                                    <tr>
                                        <td><?= htmlspecialchars($log['created_at']) ?></td>
                                        <td><?= htmlspecialchars($log['user_name']) ?></td>
                                        <td><?= htmlspecialchars($log['action']) ?></td>
                                        <td><?= nl2br(htmlspecialchars($log['details'])) ?></td>
                                        <td><?= htmlspecialchars($log['ip_address']) ?></td>
                                    </tr>
                                <?php endforeach; ?>
                            <?php endif; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
            <?php endforeach; ?>
        </div>
    </div>
</div>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.2.3/dist/js/bootstrap.bundle.min.js"></script>
<script src="https://kit.fontawesome.com/2c36e9b7b1.js" crossorigin="anonymous"></script>
</body>
</html> 