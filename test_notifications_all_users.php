<?php
require_once 'db_connect.php';
require_once 'auth_function.php';

// Check if user is logged in
if (!isset($_SESSION['user_logged_in']) || $_SESSION['user_logged_in'] !== true) {
    header('Location: login.php');
    exit();
}

$user_type = $_SESSION['user_type'] ?? 'Unknown';
$user_name = $_SESSION['user_name'] ?? 'Unknown User';
$branch_id = $_SESSION['branch_id'] ?? null;

include('header.php');
?>

<div class="container-fluid px-4 py-4">
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-header">
                    <h4 class="mb-0">
                        <i class="fas fa-bell me-2"></i>
                        Notification System Test - <?php echo htmlspecialchars($user_type); ?>
                    </h4>
                    <p class="text-muted mb-0">User: <?php echo htmlspecialchars($user_name); ?></p>
                    <?php if ($branch_id): ?>
                        <p class="text-muted mb-0">Branch ID: <?php echo htmlspecialchars($branch_id); ?></p>
                    <?php endif; ?>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-6">
                            <h5>Current User Information</h5>
                            <ul class="list-group">
                                <li class="list-group-item">
                                    <strong>User Type:</strong> <?php echo htmlspecialchars($user_type); ?>
                                </li>
                                <li class="list-group-item">
                                    <strong>User Name:</strong> <?php echo htmlspecialchars($user_name); ?>
                                </li>
                                <li class="list-group-item">
                                    <strong>User ID:</strong> <?php echo htmlspecialchars($_SESSION['user_id'] ?? 'N/A'); ?>
                                </li>
                                <li class="list-group-item">
                                    <strong>Branch ID:</strong> <?php echo htmlspecialchars($branch_id ?? 'N/A'); ?>
                                </li>
                            </ul>
                        </div>
                        <div class="col-md-6">
                            <h5>Notification System Status</h5>
                            <div id="notificationStatus">
                                <div class="alert alert-info">
                                    <i class="fas fa-spinner fa-spin me-2"></i>
                                    Loading notification status...
                                </div>
                            </div>
                        </div>
                    </div>
                    
                    <hr>
                    
                    <div class="row">
                        <div class="col-12">
                            <h5>Test Notifications</h5>
                            <div class="btn-group" role="group">
                                <button type="button" class="btn btn-primary" onclick="testNotifications()">
                                    <i class="fas fa-bell me-2"></i>
                                    Test Notifications
                                </button>
                                <button type="button" class="btn btn-success" onclick="refreshNotifications()">
                                    <i class="fas fa-sync-alt me-2"></i>
                                    Refresh
                                </button>
                                <button type="button" class="btn btn-info" onclick="showNotificationCount()">
                                    <i class="fas fa-info-circle me-2"></i>
                                    Show Count
                                </button>
                            </div>
                        </div>
                    </div>
                    
                    <hr>
                    
                    <div class="row">
                        <div class="col-12">
                            <h5>Notification Results</h5>
                            <div id="notificationResults">
                                <div class="alert alert-secondary">
                                    Click "Test Notifications" to see results
                                </div>
                            </div>
                        </div>
                    </div>
                    
                    <hr>
                    
                    <div class="row">
                        <div class="col-12">
                            <h5>Expected Notifications by User Type</h5>
                            <div class="table-responsive">
                                <table class="table table-bordered">
                                    <thead class="table-dark">
                                        <tr>
                                            <th>User Type</th>
                                            <th>Notification Types</th>
                                            <th>Description</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <tr>
                                            <td><span class="badge bg-primary">Admin</span></td>
                                            <td>
                                                <ul class="list-unstyled mb-0">
                                                    <li><i class="fas fa-exclamation-triangle text-warning"></i> Low Stock Alerts</li>
                                                    <li><i class="fas fa-times-circle text-danger"></i> Out of Stock</li>
                                                    <li><i class="fas fa-shopping-cart text-info"></i> Pending Requests</li>
                                                    <li><i class="fas fa-calendar-times text-warning"></i> Expiring Items</li>
                                                    <li><i class="fas fa-users text-success"></i> Active Cashiers</li>
                                                </ul>
                                            </td>
                                            <td>System-wide inventory and request management</td>
                                        </tr>
                                        <tr>
                                            <td><span class="badge bg-warning">Stockman</span></td>
                                            <td>
                                                <ul class="list-unstyled mb-0">
                                                    <li><i class="fas fa-exclamation-triangle text-warning"></i> Branch Low Stock</li>
                                                    <li><i class="fas fa-check-circle text-success"></i> Request Status Updates</li>
                                                    <li><i class="fas fa-calendar-times text-warning"></i> Branch Expiring Items</li>
                                                </ul>
                                            </td>
                                            <td>Branch-specific inventory management</td>
                                        </tr>
                                        <tr>
                                            <td><span class="badge bg-success">Cashier</span></td>
                                            <td>
                                                <ul class="list-unstyled mb-0">
                                                    <li><i class="fas fa-shopping-cart text-info"></i> Recent Orders</li>
                                                    <li><i class="fas fa-exclamation-triangle text-warning"></i> Low Product Stock</li>
                                                    <li><i class="fas fa-clock text-warning"></i> Long Session Alert</li>
                                                    <li><i class="fas fa-chart-line text-success"></i> Daily Summary</li>
                                                </ul>
                                            </td>
                                            <td>Sales and session management</td>
                                        </tr>
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
function testNotifications() {
    const resultsDiv = document.getElementById('notificationResults');
    resultsDiv.innerHTML = '<div class="alert alert-info"><i class="fas fa-spinner fa-spin me-2"></i>Testing notifications...</div>';
    
    fetch('notifications.php?action=get_notifications')
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                let html = `
                    <div class="alert alert-success">
                        <i class="fas fa-check-circle me-2"></i>
                        <strong>Success!</strong> Found ${data.count} notifications
                    </div>
                    <div class="card">
                        <div class="card-header">
                            <h6 class="mb-0">Notification Details</h6>
                        </div>
                        <div class="card-body">
                `;
                
                if (data.count > 0) {
                    html += '<div class="list-group">';
                    data.notifications.forEach((notification, index) => {
                        html += `
                            <div class="list-group-item">
                                <div class="d-flex w-100 justify-content-between">
                                    <h6 class="mb-1">
                                        <i class="${notification.icon}" style="color: ${notification.icon_color};"></i>
                                        ${notification.title}
                                    </h6>
                                    <small class="text-muted">${notification.timestamp}</small>
                                </div>
                                <p class="mb-1">${notification.message}</p>
                                <small class="text-muted">${notification.details}</small>
                                <span class="badge bg-secondary ms-2">${notification.priority}</span>
                            </div>
                        `;
                    });
                    html += '</div>';
                } else {
                    html += '<div class="alert alert-warning">No notifications found for this user type.</div>';
                }
                
                html += '</div></div>';
                resultsDiv.innerHTML = html;
            } else {
                resultsDiv.innerHTML = '<div class="alert alert-danger"><i class="fas fa-exclamation-triangle me-2"></i>Error loading notifications</div>';
            }
        })
        .catch(error => {
            console.error('Error:', error);
            resultsDiv.innerHTML = '<div class="alert alert-danger"><i class="fas fa-exclamation-triangle me-2"></i>Error: ' + error.message + '</div>';
        });
}

function refreshNotifications() {
    testNotifications();
}

function showNotificationCount() {
    fetch('notifications.php?action=get_notifications')
        .then(response => response.json())
        .then(data => {
            const statusDiv = document.getElementById('notificationStatus');
            if (data.success) {
                statusDiv.innerHTML = `
                    <div class="alert alert-success">
                        <i class="fas fa-check-circle me-2"></i>
                        <strong>Notification System Active</strong><br>
                        Count: ${data.count} notifications<br>
                        Last Updated: ${data.timestamp}
                    </div>
                `;
            } else {
                statusDiv.innerHTML = `
                    <div class="alert alert-danger">
                        <i class="fas fa-exclamation-triangle me-2"></i>
                        <strong>Notification System Error</strong>
                    </div>
                `;
            }
        })
        .catch(error => {
            const statusDiv = document.getElementById('notificationStatus');
            statusDiv.innerHTML = `
                <div class="alert alert-danger">
                    <i class="fas fa-exclamation-triangle me-2"></i>
                    <strong>Connection Error</strong><br>
                    ${error.message}
                </div>
            `;
        });
}

// Load notification status on page load
document.addEventListener('DOMContentLoaded', function() {
    showNotificationCount();
});
</script>

<?php include('footer.php'); ?>
