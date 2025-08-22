<!DOCTYPE html>
<html>
<head>
    <title>🚀 FORCE Email Setup - Gmail Configuration</title>
    <style>
        body { font-family: Arial, sans-serif; max-width: 900px; margin: 30px auto; padding: 20px; background: #f0f2f5; }
        .container { background: white; padding: 40px; border-radius: 15px; box-shadow: 0 4px 20px rgba(0,0,0,0.1); }
        .urgent { background: linear-gradient(135deg, #ff6b6b, #ee5a52); color: white; padding: 25px; border-radius: 10px; margin: 20px 0; text-align: center; }
        .step { background: #e8f5e8; padding: 25px; margin: 20px 0; border-radius: 10px; border-left: 5px solid #28a745; }
        .code { background: #f8f9fa; padding: 15px; border-radius: 8px; font-family: monospace; font-size: 14px; margin: 10px 0; border: 1px solid #dee2e6; }
        .success { background: #d4edda; padding: 20px; border-radius: 8px; border-left: 5px solid #28a745; margin: 20px 0; }
        .warning { background: #fff3cd; padding: 20px; border-radius: 8px; border-left: 5px solid #ffc107; margin: 20px 0; }
        .btn { background: #007bff; color: white; padding: 15px 30px; text-decoration: none; border-radius: 8px; display: inline-block; margin: 10px 5px; font-weight: bold; }
        .btn-success { background: #28a745; }
        .btn-danger { background: #dc3545; }
        .btn-warning { background: #ffc107; color: #212529; }
        h1 { color: #333; text-align: center; margin-bottom: 30px; }
        h2 { color: #495057; border-bottom: 2px solid #007bff; padding-bottom: 10px; }
        .edit-file { background: #fff3cd; padding: 20px; border-radius: 8px; margin: 20px 0; }
    </style>
</head>
<body>
    <div class="container">
        <h1>🚀 FORCE Real Email Sending Setup</h1>
        
        <div class="urgent">
            <h2 style="margin: 0; border: none; color: white;">⚡ URGENT: Complete This Setup NOW!</h2>
            <p style="margin: 10px 0; font-size: 18px;">Your system will send REAL emails after this setup!</p>
        </div>

        <h2>📧 Step 1: Get Gmail App Password (2 minutes)</h2>
        <div class="step">
            <h3>1.1 Go to Google Account Settings</h3>
            <p>👉 <a href="https://myaccount.google.com/" target="_blank" style="color: #007bff; font-weight: bold;">Click here: https://myaccount.google.com/</a></p>
            
            <h3>1.2 Enable 2-Factor Authentication</h3>
            <p>• Go to "Security" tab</p>
            <p>• Find "2-Step Verification" and turn it ON</p>
            
            <h3>1.3 Generate App Password</h3>
            <p>• Still in "Security" tab</p>
            <p>• Find "App passwords"</p>
            <p>• Select "Mail" from dropdown</p>
            <p>• Click "Generate"</p>
            <p>• <strong>COPY the 16-character password</strong> (looks like: abcd efgh ijkl mnop)</p>
        </div>

        <h2>⚙️ Step 2: Update Configuration File</h2>
        <div class="edit-file">
            <h3>Edit: email_setup.php</h3>
            <p><strong>Find these lines around line 10-11:</strong></p>
            <div class="code">
define('GMAIL_USERNAME', 'morebites.pos.system@gmail.com'); // Your Gmail address<br>
define('GMAIL_APP_PASSWORD', 'your-app-password-here');      // Your Gmail App Password
            </div>
            
            <p><strong>Replace with YOUR information:</strong></p>
            <div class="code">
define('GMAIL_USERNAME', '<span style="color: red; font-weight: bold;">YOUR_EMAIL@gmail.com</span>'); // Your Gmail address<br>
define('GMAIL_APP_PASSWORD', '<span style="color: red; font-weight: bold;">YOUR_16_CHAR_APP_PASSWORD</span>');      // Your Gmail App Password
            </div>
            
            <div class="warning">
                <h4>⚠️ IMPORTANT:</h4>
                <ul>
                    <li>Use YOUR actual Gmail address</li>
                    <li>Use the 16-character app password from Step 1</li>
                    <li>Remove spaces from the app password</li>
                    <li>Keep the quotes around both values</li>
                </ul>
            </div>
        </div>

        <h2>🧪 Step 3: Test Email Sending</h2>
        <div class="step">
            <form method="POST" style="margin: 20px 0;">
                <h3>Test with Your Email:</h3>
                <input type="email" name="test_email" placeholder="Enter your email address" 
                       style="padding: 12px; width: 300px; margin: 10px; border: 1px solid #ddd; border-radius: 5px;" required>
                <button type="submit" name="test_now" class="btn btn-success">🚀 Test Email Now!</button>
            </form>
            
            <?php
            if (isset($_POST['test_now']) && !empty($_POST['test_email'])) {
                echo "<div style='background: #e3f2fd; padding: 20px; border-radius: 8px; margin: 20px 0;'>";
                echo "<h3>🧪 Testing Email to: " . htmlspecialchars($_POST['test_email']) . "</h3>";
                
                try {
                    require_once 'email_setup.php';
                    $result = testEmailConfiguration($_POST['test_email']);
                    
                    if ($result['success']) {
                        echo "<div class='success'>";
                        echo "<h4>🎉 SUCCESS! Email sent successfully!</h4>";
                        echo "<p>Check your email inbox for the test verification code.</p>";
                        echo "<p><strong>Method used:</strong> {$result['method']}</p>";
                        echo "</div>";
                        
                        echo "<div style='text-align: center; margin: 30px 0;'>";
                        echo "<a href='add_user.php?role=cashier' class='btn btn-success' style='font-size: 18px; padding: 20px 40px;'>";
                        echo "✅ Email Working! Create Cashier Now</a>";
                        echo "</div>";
                    } else {
                        echo "<div style='background: #f8d7da; padding: 20px; border-radius: 8px;'>";
                        echo "<h4>❌ Email sending failed</h4>";
                        echo "<p><strong>Errors:</strong></p><ul>";
                        foreach ($result['errors'] as $error) {
                            echo "<li>" . htmlspecialchars($error) . "</li>";
                        }
                        echo "</ul>";
                        echo "<p><strong>Check your Gmail settings and try again!</strong></p>";
                        echo "</div>";
                    }
                } catch (Exception $e) {
                    echo "<div style='background: #f8d7da; padding: 20px; border-radius: 8px;'>";
                    echo "<h4>❌ Configuration Error</h4>";
                    echo "<p>" . htmlspecialchars($e->getMessage()) . "</p>";
                    echo "<p><strong>Make sure you completed Step 2!</strong></p>";
                    echo "</div>";
                }
                echo "</div>";
            }
            ?>
        </div>

        <h2>🎯 Step 4: Use the System</h2>
        <div class="success">
            <h3>✅ After email is working:</h3>
            <p>1. Go to Add User form</p>
            <p>2. Fill out the form with a real email address</p>
            <p>3. Click "Save Cashier" or "Save Stockman"</p>
            <p>4. <strong>Check your email for the verification code!</strong></p>
            <p>5. Enter the code to complete account creation</p>
        </div>

        <div style="text-align: center; margin: 40px 0;">
            <a href="add_user.php?role=cashier" class="btn btn-warning">👤 Add Cashier (Will Send Real Email)</a>
            <a href="add_user.php?role=stockman" class="btn btn-warning">📦 Add Stockman (Will Send Real Email)</a>
        </div>

        <div class="warning">
            <h3>🔥 SYSTEM IS NOW CONFIGURED TO FORCE SEND REAL EMAILS!</h3>
            <p>After completing the Gmail setup above, the system will:</p>
            <ul>
                <li>✅ Always attempt to send real emails first</li>
                <li>✅ Try multiple sending methods</li>
                <li>✅ Show verification code as backup if email fails</li>
                <li>✅ Log all email attempts for debugging</li>
            </ul>
        </div>
    </div>
</body>
</html>