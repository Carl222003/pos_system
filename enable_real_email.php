<!DOCTYPE html>
<html>
<head>
    <title>Enable Real Email Sending</title>
    <style>
        body { font-family: Arial, sans-serif; max-width: 800px; margin: 50px auto; padding: 20px; background: #f8f9fa; }
        .container { background: white; padding: 30px; border-radius: 10px; box-shadow: 0 2px 10px rgba(0,0,0,0.1); }
        .step { background: #e3f2fd; padding: 20px; margin: 15px 0; border-radius: 8px; border-left: 4px solid #2196f3; }
        .warning { background: #fff3cd; padding: 15px; border-radius: 5px; border-left: 4px solid #ffc107; margin: 20px 0; }
        .success { background: #d4edda; padding: 15px; border-radius: 5px; border-left: 4px solid #28a745; margin: 20px 0; }
        .code { background: #f8f9fa; padding: 10px; border-radius: 5px; font-family: monospace; margin: 10px 0; }
        .btn { background: #007bff; color: white; padding: 12px 24px; text-decoration: none; border-radius: 5px; display: inline-block; margin: 10px 5px; }
        .btn-success { background: #28a745; }
        .btn-warning { background: #ffc107; color: #212529; }
        h1, h2 { color: #333; }
    </style>
</head>
<body>
    <div class="container">
        <h1>📧 Enable Real Email Sending</h1>
        
        <div class="warning">
            <h3>⚠️ Current Status: Test Mode Active</h3>
            <p>Emails are currently simulated and verification codes are shown in the modal popup.</p>
        </div>

        <h2>🚀 To Enable Real Email Sending:</h2>

        <div class="step">
            <h3>Step 1: Set Up Gmail App Password</h3>
            <ol>
                <li>Go to your Google Account settings</li>
                <li>Enable 2-factor authentication if not already enabled</li>
                <li>Go to "Security" → "App passwords"</li>
                <li>Generate an app password for "Mail"</li>
                <li>Save this password (you'll need it in Step 2)</li>
            </ol>
        </div>

        <div class="step">
            <h3>Step 2: Update Email Configuration</h3>
            <p>Edit the file <code>email_config.php</code> and update these settings:</p>
            <div class="code">
define('ENABLE_REAL_EMAIL', true); // Change to true<br>
define('SMTP_USERNAME', 'your-email@gmail.com'); // Your Gmail<br>
define('SMTP_PASSWORD', 'your-app-password'); // The app password from Step 1
            </div>
        </div>

        <div class="step">
            <h3>Step 3: Test Email Sending</h3>
            <p>After updating the configuration:</p>
            <ol>
                <li>Save the changes to <code>email_config.php</code></li>
                <li>Try creating a new cashier/stockman account</li>
                <li>Check your email for the verification code</li>
            </ol>
        </div>

        <h2>📋 Current Configuration:</h2>
        <?php
        require_once 'email_config.php';
        
        if (isRealEmailEnabled()) {
            echo '<div class="success">';
            echo '<h3>✅ Real Email Enabled</h3>';
            echo '<p>SMTP Host: ' . SMTP_HOST . '</p>';
            echo '<p>SMTP Port: ' . SMTP_PORT . '</p>';
            echo '<p>Username: ' . SMTP_USERNAME . '</p>';
            echo '<p>Status: Emails will be sent to actual email addresses</p>';
            echo '</div>';
        } else {
            echo '<div class="warning">';
            echo '<h3>⚠️ Test Mode Active</h3>';
            echo '<p>Real email sending is currently disabled.</p>';
            echo '<p>Verification codes are shown in the modal popup instead.</p>';
            echo '</div>';
        }
        ?>

        <h2>🔄 Quick Actions:</h2>
        <a href="add_user.php?role=cashier" class="btn">👤 Add Cashier (Test Mode)</a>
        <a href="add_user.php?role=stockman" class="btn">📦 Add Stockman (Test Mode)</a>
        
        <?php if (isRealEmailEnabled()): ?>
            <a href="test_real_email.php" class="btn btn-success">✉️ Test Real Email</a>
        <?php endif; ?>

        <div class="warning">
            <h3>💡 For Development/Testing:</h3>
            <p>The current test mode is actually more convenient for development because:</p>
            <ul>
                <li>✅ No need to check email</li>
                <li>✅ Instant verification codes</li>
                <li>✅ No email setup required</li>
                <li>✅ Works offline</li>
            </ul>
            <p><strong>The verification system works perfectly in test mode!</strong></p>
        </div>
    </div>
</body>
</html>