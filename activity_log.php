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

// Fetch categories for the filter
$categoryStmt = $pdo->query("SELECT category_name FROM pos_category WHERE status = 'active' ORDER BY category_name");
$categories = $categoryStmt->fetchAll(PDO::FETCH_COLUMN);

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
                    <div class="card-header d-flex justify-content-between align-items-center" style="color: #8B4543;">
                        <div><i class="fas fa-clipboard-list me-1"></i> <?= htmlspecialchars($tab) ?> Activity Log</div>
                    </div>
                    <div class="card-body">
                        <?php if ($tab === 'Products'): ?>
                            <?php
                            $actions = [];
                            $details = [];
                            foreach ($tabLogs as $log) {
                                $actions[] = $log['action'];
                                $details[] = $log['details'];
                            }
                            $actions = array_unique($actions);
                            $details = array_unique($details);
                            sort($actions);
                            sort($details);
                            ?>
                            <div class="d-flex align-items-center mb-3 gap-2" style="position: relative;">
                                <button id="productsFilterBtn" class="btn btn-outline-secondary d-flex align-items-center justify-content-center" style="border-radius: 50%; width: 38px; height: 38px; box-shadow: 0 2px 8px rgba(139,69,67,0.08); border: 1.5px solid #8B4543; color: #8B4543; font-size: 1.2em; transition: background 0.18s, color 0.18s;" title="Show Filters">
                                    <i class="fas fa-filter"></i>
                                </button>
                                <span class="ms-2" style="font-weight: 500; color: #8B4543;">Filter</span>
                                <div id="productsFilterPanel" class="card shadow-sm p-3" style="display: none; position: absolute; left: 50px; top: 0; min-width: 320px; z-index: 10; border-radius: 1rem; border: 1px solid #e0e0e0; background: #fff;">
                                    <div class="mb-2">
                                        <label for="filterActionSelect" class="form-label mb-1" style="color: #8B4543; font-weight: 500;">Action</label>
                                        <select id="filterActionSelect" class="form-select">
                                            <option value="">All Actions</option>
                                            <?php foreach ($actions as $action): ?>
                                                <option value="<?= htmlspecialchars($action) ?>"><?= htmlspecialchars($action) ?></option>
                                            <?php endforeach; ?>
                                        </select>
                                    </div>
                                    <div class="mb-2">
                                        <label for="filterDetailsSelect" class="form-label mb-1" style="color: #8B4543; font-weight: 500;">Details</label>
                                        <select id="filterDetailsSelect" class="form-select">
                                            <option value="">All Details</option>
                                            <?php foreach ($details as $detail): ?>
                                                <option value="<?= htmlspecialchars($detail) ?>"><?= htmlspecialchars($detail) ?></option>
                                            <?php endforeach; ?>
                                        </select>
                                    </div>
                                    <div class="mb-2">
                                        <label for="filterCategorySelect" class="form-label mb-1" style="color: #8B4543; font-weight: 500;">Category</label>
                                        <select id="filterCategorySelect" class="form-select">
                                            <option value="">All Categories</option>
                                            <?php foreach ($categories as $category): ?>
                                                <option value="<?= htmlspecialchars($category) ?>"><?= htmlspecialchars($category) ?></option>
                                            <?php endforeach; ?>
                                        </select>
                                    </div>
                                    <button id="applyProductsFilter" class="btn btn-primary w-100" style="background: #8B4543; border: none; border-radius: 0.7rem; font-weight: 600;">Apply Filter</button>
                                </div>
                            </div>
                        <?php endif; ?>
                        <input type="text" class="form-control mb-3 activity-log-search" placeholder="Search <?= htmlspecialchars($tab) ?> logs..." data-table="<?= strtolower($tab) ?>">
                        <table class="table table-bordered table-hover activity-log-table" id="table-<?= strtolower($tab) ?>">
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
<script>
function filterProductsLogTable() {
    var table = document.getElementById('table-products');
    var search = document.querySelector('.activity-log-search[data-table="products"]').value.toLowerCase();
    var action = document.querySelector('.activity-log-action-filter[data-table="products"]').value.toLowerCase();
    var user = document.querySelector('.activity-log-user-filter[data-table="products"]').value.toLowerCase();
    var category = document.getElementById('filterCategorySelect') ? document.getElementById('filterCategorySelect').value.toLowerCase() : '';
    var rows = table.querySelectorAll('tbody tr');
    rows.forEach(function(row) {
        var text = row.textContent.toLowerCase();
        var actionText = row.children[2] ? row.children[2].textContent.toLowerCase() : '';
        var userText = row.children[1] ? row.children[1].textContent.toLowerCase() : '';
        var detailsText = row.children[3] ? row.children[3].textContent.toLowerCase() : '';
        var show = true;
        if (search && text.indexOf(search) === -1) show = false;
        if (action && actionText !== action) show = false;
        if (user && userText !== user) show = false;
        if (category && text.indexOf(category) === -1) show = false;
        if (show || row.classList.contains('no-logs-row')) {
            row.style.display = '';
        } else {
            row.style.display = 'none';
        }
    });
}
document.querySelectorAll('.activity-log-search').forEach(function(input) {
    input.addEventListener('input', function() {
        var table = input.getAttribute('data-table');
        if (table === 'products') {
            filterProductsLogTable();
        } else {
            var tableId = 'table-' + table;
            var tableElem = document.getElementById(tableId);
            var filter = input.value.toLowerCase();
            var rows = tableElem.querySelectorAll('tbody tr');
            rows.forEach(function(row) {
                var text = row.textContent.toLowerCase();
                if (text.indexOf(filter) > -1 || row.classList.contains('no-logs-row')) {
                    row.style.display = '';
                } else {
                    row.style.display = 'none';
                }
            });
        }
    });
});
document.querySelectorAll('.activity-log-action-filter, .activity-log-user-filter').forEach(function(select) {
    select.addEventListener('change', filterProductsLogTable);
});

// Filter panel logic for Products tab
(function() {
    var filterBtn = document.getElementById('productsFilterBtn');
    var filterPanel = document.getElementById('productsFilterPanel');
    var applyBtn = document.getElementById('applyProductsFilter');
    var actionSelect = document.getElementById('filterActionSelect');
    var detailsSelect = document.getElementById('filterDetailsSelect');
    var categorySelect = document.getElementById('filterCategorySelect');
    var searchInput = document.querySelector('.activity-log-search[data-table="products"]');
    var table = document.getElementById('table-products');

    if (filterBtn && filterPanel && applyBtn && actionSelect && detailsSelect && categorySelect && table) {
        filterBtn.addEventListener('click', function(e) {
            filterPanel.style.display = filterPanel.style.display === 'none' ? 'block' : 'none';
            if (filterPanel.style.display === 'block') {
                actionSelect.focus();
            }
            e.stopPropagation();
        });
        applyBtn.addEventListener('click', function() {
            filterProductsLogTable();
            filterPanel.style.display = 'none';
        });
        // Hide panel when clicking outside
        document.addEventListener('click', function(e) {
            if (!filterPanel.contains(e.target) && e.target !== filterBtn) {
                filterPanel.style.display = 'none';
            }
        });
        // Prevent click inside panel from closing it
        filterPanel.addEventListener('click', function(e) {
            e.stopPropagation();
        });
    }
    function filterProductsLogTable() {
        var search = searchInput ? searchInput.value.toLowerCase() : '';
        var action = actionSelect.value;
        var details = detailsSelect.value;
        var category = categorySelect ? categorySelect.value : '';
        var rows = table.querySelectorAll('tbody tr');
        rows.forEach(function(row) {
            var text = row.textContent.toLowerCase();
            var actionText = row.children[2] ? row.children[2].textContent : '';
            var detailsText = row.children[3] ? row.children[3].textContent : '';
            var show = true;
            if (search && text.indexOf(search) === -1) show = false;
            if (action && actionText !== action) show = false;
            if (details && detailsText !== details) show = false;
            if (category && text.indexOf(category) === -1) show = false;
            if (show || row.classList.contains('no-logs-row')) {
                row.style.display = '';
            } else {
                row.style.display = 'none';
            }
        });
    }
    // Also re-apply filter when using the search bar
    if (searchInput) {
        searchInput.addEventListener('input', function() {
            filterProductsLogTable();
        });
    }
})();
</script>
</body>
</html> 