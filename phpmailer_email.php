<?php
/**
 * PHPMailer Email Configuration
 * Professional email sending using PHPMailer library
 */

// Include PHPMailer classes
require_once 'vendor/phpmailer/Exception.php';
require_once 'vendor/phpmailer/PHPMailer.php';
require_once 'vendor/phpmailer/SMTP.php';
@require_once __DIR__ . '/email_template.php';

// Optional: central email config (so you don't hardcode here)
@require_once __DIR__ . '/email_config.php';

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\SMTP;
use PHPMailer\PHPMailer\Exception;

/**
 * Gmail SMTP Configuration
 * Update these with your Gmail credentials
 */
if (!defined('GMAIL_HOST')) {
    define('GMAIL_HOST', defined('SMTP_HOST') ? SMTP_HOST : 'smtp.gmail.com');
}
if (!defined('GMAIL_PORT')) {
    define('GMAIL_PORT', defined('SMTP_PORT') ? SMTP_PORT : 587);
}
if (!defined('GMAIL_USERNAME')) {
    // Prefer values from email_config.php if present
    define('GMAIL_USERNAME', defined('SMTP_USERNAME') ? SMTP_USERNAME : 'carlmadelo22@gmail.com');
}
if (!defined('GMAIL_PASSWORD')) {
    define('GMAIL_PASSWORD', defined('SMTP_PASSWORD') ? SMTP_PASSWORD : 'your-app-password');
}
if (!defined('GMAIL_FROM_NAME')) {
    define('GMAIL_FROM_NAME', 'MoreBites POS System');
}

/**
 * Send verification email using PHPMailer
 */
function sendVerificationEmailPHPMailer($to_email, $verification_code, $user_name = 'User') {
    // Allow env or config overrides
    $username = getenv('GMAIL_USERNAME') ?: (defined('SMTP_USERNAME') ? SMTP_USERNAME : (defined('GMAIL_USERNAME') ? GMAIL_USERNAME : ''));
    $password = getenv('GMAIL_PASSWORD') ?: (defined('SMTP_PASSWORD') ? SMTP_PASSWORD : (defined('GMAIL_PASSWORD') ? GMAIL_PASSWORD : ''));
    $fromName = defined('GMAIL_FROM_NAME') ? GMAIL_FROM_NAME : 'MoreBites POS System';

    $attempts = [
        // Try TLS on 587 first
        [GMAIL_HOST, 587, PHPMailer::ENCRYPTION_STARTTLS],
        // Fallback to SMTPS on 465
        [GMAIL_HOST, 465, PHPMailer::ENCRYPTION_SMTPS],
    ];

    $lastError = '';
    foreach ($attempts as [$host, $port, $encryption]) {
        $mail = new PHPMailer(true);
        try {
            $mail->isSMTP();
            $mail->Host       = $host;
            $mail->SMTPAuth   = true;
            $mail->AuthType   = 'LOGIN';
            $mail->Username   = $username;
            $mail->Password   = $password;
            $mail->SMTPSecure = $encryption;
            $mail->Port       = $port;
            $mail->CharSet    = 'UTF-8';
            $mail->Encoding   = 'base64';

            // Debug to error_log when MAIL_DEBUG is set (0/1/2)
            $mail->SMTPDebug = (int) (getenv('MAIL_DEBUG') !== false ? getenv('MAIL_DEBUG') : 0);
            $mail->Debugoutput = function ($str, $level) use ($port) {
                error_log('PHPMailer[' . $level . "](:$port): " . $str);
            };

            // Relax SSL for local dev
            $mail->SMTPOptions = [
                'ssl' => [
                    'verify_peer' => false,
                    'verify_peer_name' => false,
                    'allow_self_signed' => true,
                ],
            ];

            // Recipients
            $mail->setFrom($username, $fromName);
            $mail->addAddress($to_email, $user_name);
            $mail->addReplyTo($username, $fromName);

            // Content
            $mail->isHTML(true);
            $mail->Subject = 'Email Verification - MoreBites POS System';
            if (function_exists('renderVerificationEmail')) {
                $mail->Body = renderVerificationEmail($verification_code, $user_name);
            } else {
                $mail->Body = getVerificationEmailTemplate($verification_code, $user_name);
            }
            $mail->AltBody = "Hello $user_name,\n\nYour verification code is: $verification_code\n\nThis code will expire in 10 minutes.\n\nMoreBites POS System";

            if ($mail->send()) {
                error_log("✅ PHPMailer: Email sent (port $port) to $to_email");
                return [
                    'success' => true,
                    'method' => 'PHPMailer SMTP',
                    'message' => "Email sent successfully via PHPMailer (port $port)",
                ];
            }

            $lastError = $mail->ErrorInfo ?: 'Unknown error';
        } catch (Exception $e) {
            $lastError = $mail->ErrorInfo ?: $e->getMessage();
            error_log("❌ PHPMailer attempt failed on port $port: $lastError");
            // Try next attempt
        }
    }

    return [
        'success' => false,
        'method' => 'PHPMailer SMTP',
        'error' => $lastError ?: 'Could not authenticate',
        'message' => 'PHPMailer Error: ' . ($lastError ?: 'Could not authenticate'),
    ];
}

/**
 * Professional HTML email template
 */
function getVerificationEmailTemplate($verification_code, $user_name) {
    return "
    <!DOCTYPE html>
    <html lang='en'>
    <head>
        <meta charset='UTF-8'>
        <meta name='viewport' content='width=device-width, initial-scale=1.0'>
        <title>Email Verification - MoreBites POS</title>
        <style>
            * { margin: 0; padding: 0; box-sizing: border-box; }
            body { font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif; line-height: 1.6; color: #333; background: #f4f4f4; }
            .container { max-width: 600px; margin: 20px auto; background: #ffffff; border-radius: 15px; overflow: hidden; box-shadow: 0 10px 30px rgba(0,0,0,0.1); }
            .header { background: linear-gradient(135deg, #8B4513, #A0522D); color: white; padding: 40px 30px; text-align: center; }
            .header h1 { font-size: 28px; margin-bottom: 10px; }
            .header p { font-size: 16px; opacity: 0.9; }
            .content { padding: 40px 30px; }
            .greeting { font-size: 18px; margin-bottom: 20px; color: #2c3e50; }
            .code-section { background: #f8f9fa; border: 3px dashed #8B4513; border-radius: 15px; padding: 30px; margin: 30px 0; text-align: center; }
            .code-label { font-size: 16px; color: #666; margin-bottom: 15px; }
            .verification-code { font-family: 'Courier New', monospace; font-size: 36px; font-weight: bold; color: #8B4513; letter-spacing: 8px; margin: 10px 0; }
            .instructions { background: #e8f4fd; border-left: 5px solid #2196F3; padding: 20px; margin: 25px 0; border-radius: 5px; }
            .instructions h3 { color: #1976D2; margin-bottom: 15px; }
            .instructions ul { margin-left: 20px; }
            .instructions li { margin: 8px 0; }
            .warning { color: #e74c3c; font-weight: bold; }
            .footer { background: #2c3e50; color: white; padding: 30px; text-align: center; }
            .footer p { margin: 5px 0; opacity: 0.8; }
            .divider { height: 2px; background: linear-gradient(90deg, #8B4513, #A0522D); margin: 30px 0; }
            .steps { background: #fff3cd; border-radius: 10px; padding: 20px; margin: 20px 0; }
            .steps h3 { color: #856404; margin-bottom: 15px; }
            .step { margin: 10px 0; padding: 5px 0; }
        </style>
    </head>
    <body>
        <div class='container'>
            <div class='header'>
                <h1>🍽️ MoreBites POS System</h1>
                <p>Email Verification Required</p>
            </div>
            
            <div class='content'>
                <div class='greeting'>Hello <strong>$user_name</strong>! 👋</div>
                
                <p>Welcome to MoreBites POS System! We're excited to have you on board. To complete your account registration and ensure the security of your account, please verify your email address.</p>
                
                <div class='code-section'>
                    <div class='code-label'>Your Verification Code:</div>
                    <div class='verification-code'>$verification_code</div>
                    <p style='font-size: 14px; color: #666; margin-top: 10px;'>Copy this code exactly as shown</p>
                </div>
                
                <div class='instructions'>
                    <h3>📋 Verification Instructions:</h3>
                    <ul>
                        <li>This verification code will expire in <span class='warning'>10 minutes</span></li>
                        <li>Enter this code exactly as shown above</li>
                        <li>Your account will be created only after successful verification</li>
                        <li>If you didn't request this account creation, please ignore this email</li>
                    </ul>
                </div>
                
                <div class='divider'></div>
                
                <div class='steps'>
                    <h3>🎯 Next Steps:</h3>
                    <div class='step'>1. Copy the 6-digit verification code above</div>
                    <div class='step'>2. Return to the MoreBites registration form</div>
                    <div class='step'>3. Paste the code in the verification field</div>
                    <div class='step'>4. Click 'Verify Email' to complete your registration</div>
                </div>
                
                <p style='margin-top: 30px; color: #666; font-size: 14px;'>
                    If you're having trouble with the verification process, please contact your system administrator or IT support team.
                </p>
            </div>
            
            <div class='footer'>
                <p><strong>📧 MoreBites POS System</strong></p>
                <p>This is an automated security email - Please do not reply</p>
                <p>&copy; " . date('Y') . " MoreBites. All rights reserved.</p>
            </div>
        </div>
    </body>
    </html>
    ";
}

/**
 * Test PHPMailer configuration
 */
function testPHPMailerSetup($test_email = 'test@example.com') {
    echo "<h2>🧪 Testing PHPMailer Configuration</h2>";
    
    // Check if PHPMailer files exist
    $required_files = [
        'vendor/phpmailer/PHPMailer.php',
        'vendor/phpmailer/SMTP.php',
        'vendor/phpmailer/Exception.php'
    ];
    
    $files_exist = true;
    foreach ($required_files as $file) {
        if (!file_exists($file)) {
            echo "❌ Missing file: $file<br>";
            $files_exist = false;
        } else {
            echo "✅ Found: $file<br>";
        }
    }
    
    if (!$files_exist) {
        echo "<p style='color: red;'><strong>❌ PHPMailer files missing. Please download them first.</strong></p>";
        return false;
    }
    
    // Test sending email
    echo "<h3>📧 Sending test email to: $test_email</h3>";
    
    $test_code = str_pad(rand(0, 999999), 6, '0', STR_PAD_LEFT);
    $result = sendVerificationEmailPHPMailer($test_email, $test_code, 'Test User');
    
    echo "<div style='padding: 20px; margin: 20px 0; border-radius: 10px; ";
    if ($result['success']) {
        echo "background: #d4edda; border-left: 5px solid #28a745;'>";
        echo "<h3>🎉 SUCCESS! Email sent via PHPMailer!</h3>";
        echo "<p><strong>Method:</strong> {$result['method']}</p>";
        echo "<p><strong>Test Code:</strong> $test_code</p>";
        echo "<p><strong>Sent to:</strong> $test_email</p>";
        echo "<p style='color: green; font-weight: bold;'>✅ PHPMailer is working perfectly!</p>";
    } else {
        echo "background: #f8d7da; border-left: 5px solid #dc3545;'>";
        echo "<h3>❌ PHPMailer Test Failed</h3>";
        echo "<p><strong>Error:</strong> {$result['error']}</p>";
        echo "<p><strong>Message:</strong> {$result['message']}</p>";
        echo "<p style='color: red;'>Check your Gmail configuration!</p>";
    }
    echo "</div>";
    
    return $result;
}
?>