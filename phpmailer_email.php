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
            
            // Add logo as embedded image
            $logoPath = __DIR__ . '/asset/images/logo.png';
            if (file_exists($logoPath)) {
                $mail->addEmbeddedImage($logoPath, 'logo', 'logo.png');
            }

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
            body { font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif; line-height: 1.6; color: #333; background: #f8f9fa; }
            .container { max-width: 600px; margin: 20px auto; background: #ffffff; border-radius: 20px; overflow: hidden; box-shadow: 0 15px 40px rgba(0,0,0,0.15); border: 3px solid #dc3545; }
            .header { background: linear-gradient(135deg, #dc3545, #c82333); color: white; padding: 40px 30px; text-align: center; position: relative; }
            .header::before { content: ''; position: absolute; top: 0; left: 0; right: 0; height: 5px; background: linear-gradient(90deg, #28a745, #20c997); }
            .header h1 { font-size: 32px; margin-bottom: 10px; text-shadow: 2px 2px 4px rgba(0,0,0,0.3); }
            .header p { font-size: 18px; opacity: 0.95; font-weight: 300; }
            .content { padding: 40px 30px; background: linear-gradient(180deg, #ffffff 0%, #f8f9fa 100%); }
            .greeting { font-size: 20px; margin-bottom: 25px; color: #dc3545; font-weight: 600; text-align: center; }
            .code-section { background: linear-gradient(135deg, #f8f9fa, #e9ecef); border: 4px dashed #dc3545; border-radius: 20px; padding: 35px; margin: 35px 0; text-align: center; position: relative; }
            .code-section::before { content: '🔐'; font-size: 24px; position: absolute; top: -15px; left: 50%; transform: translateX(-50%); background: white; padding: 0 15px; }
            .code-label { font-size: 18px; color: #495057; margin-bottom: 20px; font-weight: 500; }
            .verification-code { font-family: 'Courier New', monospace; font-size: 42px; font-weight: bold; color: #dc3545; letter-spacing: 10px; margin: 15px 0; text-shadow: 2px 2px 4px rgba(220,53,69,0.2); background: white; padding: 20px; border-radius: 15px; border: 2px solid #dc3545; }
            .instructions { background: linear-gradient(135deg, #d4edda, #c3e6cb); border-left: 6px solid #28a745; padding: 25px; margin: 30px 0; border-radius: 10px; border: 1px solid #c3e6cb; }
            .instructions h3 { color: #155724; margin-bottom: 18px; font-size: 20px; }
            .instructions ul { margin-left: 25px; }
            .instructions li { margin: 12px 0; color: #155724; font-weight: 500; }
            .warning { color: #dc3545; font-weight: bold; background: #f8d7da; padding: 3px 8px; border-radius: 5px; }
            .footer { background: linear-gradient(135deg, #28a745, #20c997); color: white; padding: 35px; text-align: center; position: relative; }
            .footer::before { content: ''; position: absolute; top: 0; left: 0; right: 0; height: 5px; background: linear-gradient(90deg, #dc3545, #c82333); }
            .footer p { margin: 8px 0; opacity: 0.9; font-weight: 300; }
            .divider { height: 3px; background: linear-gradient(90deg, #dc3545, #28a745); margin: 35px 0; border-radius: 2px; }
            .steps { background: linear-gradient(135deg, #fff3cd, #ffeaa7); border-radius: 15px; padding: 25px; margin: 25px 0; border: 2px solid #ffc107; }
            .steps h3 { color: #856404; margin-bottom: 18px; font-size: 20px; }
            .step { margin: 12px 0; padding: 8px 0; color: #856404; font-weight: 500; }
            .success-badge { background: #28a745; color: white; padding: 8px 16px; border-radius: 20px; font-size: 14px; font-weight: 600; display: inline-block; margin-bottom: 20px; }
            .security-note { background: #f8d7da; border: 1px solid #f5c6cb; border-radius: 10px; padding: 20px; margin: 25px 0; text-align: center; }
            .security-note h4 { color: #721c24; margin-bottom: 10px; }
            .security-note p { color: #721c24; font-size: 14px; }
            .important-box { background: linear-gradient(135deg, #fff3cd, #ffeaa7); border-left: 5px solid #ffc107; padding: 20px; margin: 25px 0; border-radius: 10px; }
            .important-box h4 { color: #856404; margin-bottom: 15px; }
            .important-box p { color: #856404; font-size: 14px; }
        </style>
    </head>
    <body>
        <div class='container'>
            <div class='header'>
                <h1>🍽️ MoreBites</h1>
                <p>Email Verification</p>
            </div>
            
            <div class='content'>
                <div class='greeting'>Hello <strong>$user_name</strong>! 👋</div>
                
                <p style='text-align: center; color: #495057; font-size: 16px; margin-bottom: 30px; line-height: 1.6;'>
                    To complete your registration, please verify your email by entering the 6-digit verification code below.
                </p>
                
                <div class='code-section'>
                    <div class='code-label'>Your verification code</div>
                    <div class='verification-code'>$verification_code</div>
                    <p style='font-size: 14px; color: #6c757d; margin-top: 15px; font-weight: 500;'>Copy this code exactly as shown</p>
                </div>
                
                <div class='important-box'>
                    <h4>⚠️ Important:</h4>
                    <p>This code expires in <strong style='color: #dc3545;'>10 minutes</strong>. Enter it exactly as shown. If you didn't request this, you can ignore this email.</p>
                </div>
                
                <div class='instructions'>
                    <h3>📋 Instructions:</h3>
                    <ol style='margin-left: 20px;'>
                        <li>Copy the code above</li>
                        <li>Return to the registration form</li>
                        <li>Paste the code into the verification field</li>
                        <li>Click \"Verify Email\" to finish</li>
                    </ol>
                </div>
                
                <div class='security-note'>
                    <h4>🔒 Security Notice</h4>
                    <p>This verification code is for your account security. Never share it with anyone, including our support team.</p>
                </div>
                
                <p style='margin-top: 30px; color: #6c757d; font-size: 14px; text-align: center;'>
                    If you're having trouble with the verification process, please contact your system administrator.
                </p>
            </div>
            
            <div class='footer'>
                <p><strong>📧 MoreBites System</strong></p>
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