<!DOCTYPE html>
<html>
<head>
    <title>🚀 PHPMailer Email Setup</title>
    <style>
        body { font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif; max-width: 1000px; margin: 30px auto; padding: 20px; background: #f8f9fa; }
        .container { background: white; padding: 40px; border-radius: 15px; box-shadow: 0 10px 30px rgba(0,0,0,0.1); }
        .header { background: linear-gradient(135deg, #007bff, #0056b3); color: white; padding: 30px; border-radius: 10px; text-align: center; margin-bottom: 30px; }
        .step { background: #e8f5e8; padding: 25px; margin: 20px 0; border-radius: 10px; border-left: 5px solid #28a745; }
        .step h3 { color: #155724; margin-bottom: 15px; }
        .code { background: #f8f9fa; padding: 15px; border-radius: 8px; font-family: 'Courier New', monospace; font-size: 14px; margin: 10px 0; border: 1px solid #dee2e6; overflow-x: auto; }
        .success { background: #d4edda; padding: 20px; border-radius: 8px; border-left: 5px solid #28a745; margin: 20px 0; }
        .warning { background: #fff3cd; padding: 20px; border-radius: 8px; border-left: 5px solid #ffc107; margin: 20px 0; }
        .error { background: #f8d7da; padding: 20px; border-radius: 8px; border-left: 5px solid #dc3545; margin: 20px 0; }
        .btn { background: #007bff; color: white; padding: 15px 30px; text-decoration: none; border-radius: 8px; display: inline-block; margin: 10px 5px; font-weight: bold; cursor: pointer; border: none; }
        .btn-success { background: #28a745; }
        .btn-danger { background: #dc3545; }
        .btn-warning { background: #ffc107; color: #212529; }
        .progress { background: #e9ecef; border-radius: 10px; padding: 3px; margin: 20px 0; }
        .progress-bar { background: #28a745; height: 25px; border-radius: 8px; text-align: center; color: white; line-height: 25px; transition: width 0.3s; }
        .file-edit { background: #fff3cd; padding: 25px; border-radius: 10px; margin: 20px 0; }
        h1 { color: #333; margin-bottom: 20px; }
        h2 { color: #495057; border-bottom: 2px solid #007bff; padding-bottom: 10px; margin: 30px 0 20px 0; }
        .highlight { background: #ffeb3b; padding: 2px 6px; border-radius: 3px; }
        .form-group { margin: 20px 0; }
        .form-control { width: 100%; padding: 12px; border: 1px solid #ddd; border-radius: 5px; font-size: 16px; }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <h1>📧 PHPMailer Professional Email Setup</h1>
            <p>Set up reliable email sending using PHPMailer library</p>
        </div>

        <?php
        // Check if PHPMailer is downloaded
        $phpmailer_exists = file_exists('vendor/phpmailer/PHPMailer.php') && 
                           file_exists('vendor/phpmailer/SMTP.php') && 
                           file_exists('vendor/phpmailer/Exception.php');
        
        if (!$phpmailer_exists) {
            echo "<div class='error'>";
            echo "<h3>❌ PHPMailer Not Found</h3>";
            echo "<p>PHPMailer library needs to be downloaded first.</p>";
            echo "<a href='download_phpmailer.php' class='btn btn-danger'>📥 Download PHPMailer Now</a>";
            echo "</div>";
        } else {
            echo "<div class='success'>";
            echo "<h3>✅ PHPMailer Ready</h3>";
            echo "<p>PHPMailer library is installed and ready to use.</p>";
            echo "</div>";
        }
        ?>

        <h2>📋 Setup Progress</h2>
        <div class="progress">
            <div class="progress-bar" style="width: <?php echo $phpmailer_exists ? '33%' : '0%'; ?>;">
                Step 1: Download PHPMailer <?php echo $phpmailer_exists ? '✅' : '⏳'; ?>
            </div>
        </div>

        <?php if ($phpmailer_exists): ?>
        
        <h2>⚙️ Step 2: Configure Gmail Settings</h2>
        <div class="file-edit">
            <h3>📝 Edit Configuration File</h3>
            <p>Open <strong>phpmailer_email.php</strong> and update these lines (around line 17-20):</p>
            
            <div class="code">
define('GMAIL_HOST', 'smtp.gmail.com');<br>
define('GMAIL_PORT', 587);<br>
define('GMAIL_USERNAME', '<span class="highlight">your-email@gmail.com</span>');     // ← Replace with your Gmail<br>
define('GMAIL_PASSWORD', '<span class="highlight">your-app-password</span>');        // ← Replace with Gmail App Password<br>
define('GMAIL_FROM_NAME', 'MoreBites POS System');
            </div>
            
            <div class="warning">
                <h4>🔑 Gmail App Password Required</h4>
                <p>You need a Gmail App Password (not your regular password):</p>
                <ol>
                    <li>Go to <a href="https://myaccount.google.com/" target="_blank">Google Account Settings</a></li>
                    <li>Enable 2-factor authentication</li>
                    <li>Go to Security → App passwords</li>
                    <li>Generate password for "Mail"</li>
                    <li>Use that 16-character password above</li>
                </ol>
            </div>
        </div>

        <h2>🧪 Step 3: Test Email Configuration</h2>
        <form method="POST" style="background: #e3f2fd; padding: 25px; border-radius: 10px;">
            <div class="form-group">
                <label for="test_email"><strong>Test Email Address:</strong></label>
                <input type="email" id="test_email" name="test_email" class="form-control" 
                       placeholder="Enter your email to test" required>
            </div>
            <button type="submit" name="test_phpmailer" class="btn btn-success">
                🚀 Test PHPMailer Email Sending
            </button>
        </form>

        <?php
        if (isset($_POST['test_phpmailer']) && !empty($_POST['test_email'])) {
            echo "<div style='background: #f8f9fa; padding: 25px; border-radius: 10px; margin: 20px 0;'>";
            echo "<h3>🧪 Testing PHPMailer with: " . htmlspecialchars($_POST['test_email']) . "</h3>";
            
            try {
                require_once 'phpmailer_email.php';
                $result = testPHPMailerSetup($_POST['test_email']);
                
                if ($result && $result['success']) {
                    echo "<div class='success'>";
                    echo "<h3>🎉 PHPMailer SUCCESS!</h3>";
                    echo "<p>✅ Email sent successfully using PHPMailer</p>";
                    echo "<p>✅ Check your email inbox now!</p>";
                    echo "<p><strong>Method:</strong> {$result['method']}</p>";
                    echo "</div>";
                    
                    echo "<div style='text-align: center; margin: 30px 0;'>";
                    echo "<h3>🚀 Ready to Use!</h3>";
                    echo "<a href='add_user.php?role=cashier' class='btn btn-success' style='font-size: 18px; padding: 20px 40px;'>";
                    echo "✅ Create Cashier (Real Email)</a>";
                    echo "<a href='add_user.php?role=stockman' class='btn btn-success' style='font-size: 18px; padding: 20px 40px;'>";
                    echo "✅ Create Stockman (Real Email)</a>";
                    echo "</div>";
                } else {
                    echo "<div class='error'>";
                    echo "<h3>❌ PHPMailer Test Failed</h3>";
                    if ($result) {
                        echo "<p><strong>Error:</strong> " . htmlspecialchars($result['error']) . "</p>";
                        echo "<p><strong>Message:</strong> " . htmlspecialchars($result['message']) . "</p>";
                    }
                    echo "<p>Please check your Gmail configuration in phpmailer_email.php</p>";
                    echo "</div>";
                }
            } catch (Exception $e) {
                echo "<div class='error'>";
                echo "<h3>❌ Configuration Error</h3>";
                echo "<p>" . htmlspecialchars($e->getMessage()) . "</p>";
                echo "<p>Make sure you've updated the Gmail settings in phpmailer_email.php</p>";
                echo "</div>";
            }
            echo "</div>";
        }
        ?>

        <?php endif; ?>

        <div class="warning">
            <h3>📌 Prerequisites Checklist</h3>
            <ul>
                <li>✅ PHP installed locally (XAMPP/WAMP/Laragon)</li>
                <li>✅ MySQL server running</li>
                <li>✅ Visual Studio Code (optional)</li>
                <li><?php echo $phpmailer_exists ? '✅' : '❌'; ?> PHPMailer library</li>
                <li>🔑 Gmail SMTP server (for testing)</li>
            </ul>
        </div>

        <div class="success">
            <h3>🎯 What PHPMailer Gives You:</h3>
            <ul>
                <li>✅ <strong>Reliable Email Delivery</strong> - Professional SMTP sending</li>
                <li>✅ <strong>Beautiful HTML Emails</strong> - Rich formatting and styling</li>
                <li>✅ <strong>Error Handling</strong> - Detailed error messages</li>
                <li>✅ <strong>Security</strong> - Secure SMTP authentication</li>
                <li>✅ <strong>Gmail Integration</strong> - Works perfectly with Gmail</li>
            </ul>
        </div>
    </div>
</body>
</html>