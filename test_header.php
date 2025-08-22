<?php
require_once 'db_connect.php';
require_once 'auth_function.php';

// Start session if not already started
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

// Set a test user session if none exists
if (!isset($_SESSION['user_id'])) {
    $_SESSION['user_id'] = 1;
    $_SESSION['user_name'] = 'Test User';
    $_SESSION['user_type'] = 'Admin';
}

include('header.php');
?>

<div class="container mt-4">
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-header">
                    <h4>Header Test Page</h4>
                </div>
                <div class="card-body">
                    <h5>Testing the following elements:</h5>
                    <ul>
                        <li>✅ Clock and date display</li>
                        <li>✅ Notification bell with glassmorphism dropdown</li>
                        <li>✅ User menu dropdown</li>
                        <li>✅ Responsive header layout</li>
                    </ul>
                    
                    <div class="alert alert-info">
                        <i class="fas fa-info-circle me-2"></i>
                        <strong>Instructions:</strong>
                        <ol class="mb-0 mt-2">
                            <li>Check if the clock and date are visible in the header</li>
                            <li>Click the notification bell icon to test the dropdown</li>
                            <li>Click the user menu to test the dropdown</li>
                            <li>Open browser console to see any JavaScript errors</li>
                        </ol>
                    </div>
                    
                    <div class="alert alert-warning">
                        <i class="fas fa-exclamation-triangle me-2"></i>
                        <strong>Debug Info:</strong> Check the browser console for any JavaScript errors or debugging messages from the notification system.
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
// Additional debugging
document.addEventListener('DOMContentLoaded', function() {
    console.log('🔍 Test page loaded');
    
    // Check if elements exist
    const elements = {
        clock: document.getElementById('realtime-clock'),
        time: document.getElementById('current-time'),
        date: document.getElementById('current-date'),
        notificationBtn: document.getElementById('notificationBtn'),
        notificationDropdown: document.getElementById('notificationDropdown'),
        userMenu: document.querySelector('.user-menu')
    };
    
    console.log('📋 Element check:', elements);
    
    // Test clock functionality
    if (elements.time && elements.date) {
        console.log('⏰ Clock elements found');
        console.log('Current time:', elements.time.textContent);
        console.log('Current date:', elements.date.textContent);
    } else {
        console.error('❌ Clock elements missing');
    }
    
    // Test notification elements
    if (elements.notificationBtn && elements.notificationDropdown) {
        console.log('🔔 Notification elements found');
    } else {
        console.error('❌ Notification elements missing');
    }
});
</script>

<?php include('footer.php'); ?>
