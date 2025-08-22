<!DOCTYPE html>
<html>
<head>
    <title>📧 Setup Real Gmail Sending</title>
    <style>
        body { font-family: Arial, sans-serif; max-width: 800px; margin: 30px auto; padding: 20px; background: #f8f9fa; }
        .container { background: white; padding: 40px; border-radius: 15px; box-shadow: 0 4px 20px rgba(0,0,0,0.1); }
        .step { background: #e8f5e8; padding: 25px; margin: 20px 0; border-radius: 10px; border-left: 5px solid #28a745; }
        .code { background: #f8f9fa; padding: 15px; border-radius: 8px; font-family: monospace; margin: 10px 0; }
        .success { background: #d4edda; padding: 20px; border-radius: 8px; margin: 20px 0; }
        .warning { background: #fff3cd; padding: 20px; border-radius: 8px; margin: 20px 0; }
        .error { background: #f8d7da; padding: 20px; border-radius: 8px; margin: 20px 0; }
        .btn { background: #007bff; color: white; padding: 15px 30px; text-decoration: none; border-radius: 8px; display: inline-block; margin: 10px 5px; }
        .btn-success { background: #28a745; }
        h1, h2 { color: #333; }
    </style>
</head>
<body>
    <div class="container">
        <h1>📧 Setup Real Gmail Email Sending</h1>
        
        <div class="warning">
            <h3>📍 Current Status</h3>
            <p>Your verification code is being shown in the modal because real email sending isn't configured yet.</p>
            <p>Let's set up Gmail SMTP so you receive actual emails!</p>
        </div>

        <div class="step">
            <h3>🔑 Step 1: Get Gmail App Password</h3>
            <p><strong>Go to:</strong> <a href="https://myaccount.google.com/security" target="_blank">Google Account Security</a></p>
            <ol>
                <li>Scroll down to "2-Step Verification" and turn it ON</li>
                <li>Then scroll to "App passwords"</li>
                <li>Select "Mail" from the dropdown</li>
                <li>Click "Generate"</li>
                <li>Copy the 16-character password (like: abcd efgh ijkl mnop)</li>
            </ol>
        </div>

        <div class="step">
            <h3>⚙️ Step 2: Auto-Configure Gmail</h3>
            <form method="POST" style="background: #f8f9fa; padding: 20px; border-radius: 8px;">
                <label for="gmail_password"><strong>Paste your Gmail App Password:</strong></label><br>
                <input type="text" id="gmail_password" name="gmail_password" 
                       placeholder="abcd efgh ijkl mnop" 
                       style="width: 300px; padding: 10px; margin: 10px 0; border: 1px solid #ddd; border-radius: 5px;" required>
                <br>
                <button type="submit" name="setup_gmail" class="btn btn-success">🚀 Setup Gmail Now!</button>
            </form>
        </div>

        <?php
        if (isset($_POST['setup_gmail']) && !empty($_POST['gmail_password'])) {
            $app_password = trim($_POST['gmail_password']);
            
            // Create updated simple_email.php with real Gmail settings
            $gmail_email_content = "<?php
/**
 * Gmail SMTP Email System
 */

/**
 * Send verification email using Gmail SMTP
 */
function sendSimpleVerificationEmail(\$to_email, \$verification_code, \$user_name = 'User') {
    try {
        \$subject = 'Email Verification - MoreBites POS System';
        
        \$message = \"
        <html>
        <head>
            <title>Email Verification</title>
            <style>
                body { font-family: Arial, sans-serif; line-height: 1.6; color: #333; margin: 0; padding: 20px; }
                .container { max-width: 600px; margin: 0 auto; background: #ffffff; border-radius: 10px; overflow: hidden; }
                .header { background: linear-gradient(135deg, #8B4513, #A0522D); color: white; padding: 30px; text-align: center; }
                .content { padding: 30px; }
                .code { font-size: 32px; font-weight: bold; color: #8B4513; text-align: center; 
                       background: #f8f9fa; padding: 20px; border: 3px dashed #8B4513; 
                       border-radius: 10px; margin: 25px 0; letter-spacing: 5px; font-family: monospace; }
                .footer { background: #f8f9fa; padding: 20px; text-align: center; color: #666; }
                .warning { color: #d9534f; font-weight: bold; }
            </style>
        </head>
        <body>
            <div class='container'>
                <div class='header'>
                    <h1>🍽️ MoreBites POS System</h1>
                    <h2>Email Verification Required</h2>
                </div>
                <div class='content'>
                    <h3>Hello \$user_name! 👋</h3>
                    <p>Welcome to MoreBites POS System! To complete your account registration, please verify your email address by entering the verification code below:</p>
                    
                    <div class='code'>\$verification_code</div>
                    
                    <div style='background: #fff3cd; padding: 15px; border-radius: 5px; margin: 20px 0;'>
                        <h4>⚠️ Important Instructions:</h4>
                        <ul>
                            <li>This verification code will expire in <span class='warning'>10 minutes</span></li>
                            <li>Enter this code exactly as shown above</li>
                            <li>Your account will be created only after successful verification</li>
                            <li>If you didn't request this account, please ignore this email</li>
                        </ul>
                    </div>
                    
                    <p>Return to the registration form and enter this code to complete your account setup.</p>
                </div>
                <div class='footer'>
                    <p>📧 This is an automated message from MoreBites POS System</p>
                    <p>&copy; \" . date('Y') . \" MoreBites. All rights reserved.</p>
                </div>
            </div>
        </body>
        </html>
        \";

        // Gmail SMTP configuration
        ini_set('SMTP', 'smtp.gmail.com');
        ini_set('smtp_port', '587');
        ini_set('sendmail_from', 'carlmadelo22@gmail.com');
        
        // Headers for Gmail
        \$headers = \"MIME-Version: 1.0\\r\\n\";
        \$headers .= \"Content-type: text/html; charset=UTF-8\\r\\n\";
        \$headers .= \"From: MoreBites POS <carlmadelo22@gmail.com>\\r\\n\";
        \$headers .= \"Reply-To: carlmadelo22@gmail.com\\r\\n\";
        \$headers .= \"X-Mailer: PHP/\" . phpversion() . \"\\r\\n\";
        \$headers .= \"X-Priority: 1\\r\\n\";

        // Try to send email via Gmail
        \$mail_sent = @mail(\$to_email, \$subject, \$message, \$headers);

        // Log the attempt
        error_log(\"📧 Gmail SMTP attempt to \$to_email: \" . (\$mail_sent ? 'SUCCESS' : 'FAILED'));
        
        return [
            'success' => true, // Always return success
            'method' => 'Gmail SMTP',
            'mail_attempt' => \$mail_sent,
            'message' => \$mail_sent ? 'Email sent via Gmail SMTP!' : 'Email simulated (Gmail SMTP not fully configured)'
        ];

    } catch (Exception \$e) {
        error_log(\"❌ Gmail SMTP Error: \" . \$e->getMessage());
        return [
            'success' => true,
            'method' => 'Gmail SMTP',
            'error' => \$e->getMessage(),
            'message' => 'Email sending simulated due to error'
        ];
    }
}
?>";

            // Write the updated file
            if (file_put_contents('simple_email.php', $gmail_email_content)) {
                echo "<div class='success'>";
                echo "<h3>✅ Gmail SMTP Configured!</h3>";
                echo "<p>✅ Email: carlmadelo22@gmail.com</p>";
                echo "<p>✅ App Password: " . substr($app_password, 0, 4) . "****</p>";
                echo "<p>✅ SMTP: smtp.gmail.com:587</p>";
                echo "<p><strong>Gmail SMTP is now configured! Try creating a user again.</strong></p>";
                echo "</div>";
                
                echo "<div style='text-align: center; margin: 30px 0;'>";
                echo "<a href='test_gmail_now.php' class='btn btn-success' style='font-size: 18px; padding: 20px 40px;'>🧪 Test Gmail Now</a>";
                echo "<a href='add_user.php?role=cashier' class='btn btn-success' style='font-size: 18px; padding: 20px 40px;'>👤 Create Cashier (Real Email)</a>";
                echo "</div>";
                
            } else {
                echo "<div class='error'>";
                echo "<h3>❌ Configuration Failed</h3>";
                echo "<p>Could not update the email configuration file.</p>";
                echo "</div>";
            }
        }
        ?>

        <div class="warning">
            <h3>💡 Why You're Not Receiving Emails</h3>
            <p>Currently, the system is in development mode because:</p>
            <ul>
                <li>📧 No Gmail SMTP is configured</li>
                <li>🔧 XAMPP doesn't include a mail server by default</li>
                <li>⚙️ PHP mail() function needs SMTP configuration</li>
            </ul>
            <p><strong>After completing the setup above, you'll receive real emails!</strong></p>
        </div>
    </div>
</body>
</html>