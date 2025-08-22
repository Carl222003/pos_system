<?php
/**
 * FORCE EMAIL SENDING CONFIGURATION
 * This will attempt to send real emails using multiple methods
 */

require_once 'smtp_mailer.php';

// GMAIL CONFIGURATION
// Replace these with your actual Gmail credentials
define('GMAIL_USERNAME', 'morebites.pos.system@gmail.com'); // Your Gmail address
define('GMAIL_APP_PASSWORD', 'your-app-password-here');      // Your Gmail App Password

// FORCE EMAIL SENDING - Always try to send real emails
define('FORCE_REAL_EMAIL', true);

/**
 * Send verification email with multiple fallback methods
 */
function sendVerificationEmail($to_email, $verification_code, $user_name = 'User') {
    $subject = "Email Verification - MoreBites POS System";
    
    $message = "
    <!DOCTYPE html>
    <html>
    <head>
        <meta charset='UTF-8'>
        <title>Email Verification</title>
        <style>
            body { font-family: Arial, sans-serif; line-height: 1.6; color: #333; margin: 0; padding: 20px; }
            .container { max-width: 600px; margin: 0 auto; background: #ffffff; }
            .header { background-color: #8B4513; color: white; padding: 30px; text-align: center; }
            .content { background-color: #f9f9f9; padding: 30px; }
            .code-box { 
                font-size: 32px; 
                font-weight: bold; 
                color: #8B4513; 
                text-align: center; 
                background-color: #fff; 
                padding: 20px; 
                border: 3px dashed #8B4513; 
                border-radius: 10px; 
                margin: 25px 0; 
                letter-spacing: 5px;
                font-family: 'Courier New', monospace;
            }
            .warning { color: #d9534f; font-weight: bold; }
            .footer { text-align: center; color: #666; font-size: 12px; margin-top: 30px; padding: 20px; }
            .important { background: #fff3cd; padding: 15px; border-radius: 5px; margin: 20px 0; }
        </style>
    </head>
    <body>
        <div class='container'>
            <div class='header'>
                <h1>🍽️ MoreBites POS System</h1>
                <h2>📧 Email Verification Required</h2>
            </div>
            <div class='content'>
                <h3>Hello $user_name!</h3>
                <p>Welcome to MoreBites POS System! To complete your account registration, please verify your email address by entering the verification code below:</p>
                
                <div class='code-box'>$verification_code</div>
                
                <div class='important'>
                    <h4>⚠️ Important Instructions:</h4>
                    <ul>
                        <li>This verification code will expire in <span class='warning'>10 minutes</span></li>
                        <li>Enter this code exactly as shown above</li>
                        <li>Your account will be created only after successful verification</li>
                        <li>If you didn't request this account creation, please ignore this email</li>
                    </ul>
                </div>
                
                <p>If you're having trouble with the verification process, please contact your system administrator.</p>
                
                <hr style='margin: 30px 0; border: none; border-top: 1px solid #ddd;'>
                
                <p><strong>Next Steps:</strong></p>
                <ol>
                    <li>Copy the 6-digit code above</li>
                    <li>Return to the registration form</li>
                    <li>Paste the code in the verification field</li>
                    <li>Click 'Verify Email' to complete registration</li>
                </ol>
            </div>
            <div class='footer'>
                <p>📧 This is an automated message from MoreBites POS System</p>
                <p>🚫 Please do not reply to this email</p>
                <p>&copy; " . date('Y') . " MoreBites. All rights reserved.</p>
            </div>
        </div>
    </body>
    </html>
    ";
    
    // Try multiple sending methods
    return forceSendEmail($to_email, $subject, $message);
}

/**
 * Configure XAMPP to send emails
 */
function configureXAMPPMail() {
    // Set PHP mail configuration for XAMPP
    ini_set('SMTP', 'smtp.gmail.com');
    ini_set('smtp_port', '587');
    ini_set('sendmail_from', GMAIL_USERNAME);
    
    // Log configuration attempt
    error_log("📧 Configured XAMPP mail settings for Gmail SMTP");
}

/**
 * Test email configuration
 */
function testEmailConfiguration($test_email = 'test@example.com') {
    echo "<h2>🧪 Testing Email Configuration</h2>";
    
    $test_code = str_pad(rand(0, 999999), 6, '0', STR_PAD_LEFT);
    $result = sendVerificationEmail($test_email, $test_code, 'Test User');
    
    echo "<div style='padding: 20px; margin: 20px 0; border-radius: 5px; ";
    if ($result['success']) {
        echo "background: #d4edda; border-left: 4px solid #28a745;'>";
        echo "<h3>✅ Email Sent Successfully!</h3>";
        echo "<p><strong>Method:</strong> {$result['method']}</p>";
        echo "<p><strong>Test Code:</strong> $test_code</p>";
        echo "<p><strong>Sent to:</strong> $test_email</p>";
    } else {
        echo "background: #f8d7da; border-left: 4px solid #dc3545;'>";
        echo "<h3>❌ Email Sending Failed</h3>";
        echo "<p><strong>Errors:</strong></p>";
        echo "<ul>";
        foreach ($result['errors'] as $error) {
            echo "<li>$error</li>";
        }
        echo "</ul>";
    }
    echo "</div>";
    
    return $result;
}
?>