<?php
require_once 'db_connect.php';

header('Content-Type: application/json');

try {
    // Check if required parameters are provided
    if (empty($_POST['email']) || empty($_POST['user_id'])) {
        throw new Exception("Email and user ID are required");
    }

    $email = filter_var($_POST['email'], FILTER_VALIDATE_EMAIL);
    if (!$email) {
        throw new Exception("Invalid email format");
    }

    $user_id = intval($_POST['user_id']);

    // Check if user exists and is pending verification
    $stmt = $pdo->prepare("SELECT user_name, user_type FROM pos_user WHERE user_id = ? AND user_email = ? AND user_status = 'Pending'");
    $stmt->execute([$user_id, $email]);
    $user = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$user) {
        throw new Exception("User not found or already activated");
    }

    // Generate new 6-digit verification code
    $verification_code = str_pad(rand(0, 999999), 6, '0', STR_PAD_LEFT);
    
    // Set expiration time (10 minutes from now)
    $expires_at = date('Y-m-d H:i:s', strtotime('+10 minutes'));

    // Delete any existing verification codes for this email/user
    $stmt = $pdo->prepare("DELETE FROM pos_email_verification WHERE email = ? OR user_id = ?");
    $stmt->execute([$email, $user_id]);

    // Insert new verification code
    $stmt = $pdo->prepare("
        INSERT INTO pos_email_verification (email, user_id, verification_code, expires_at) 
        VALUES (?, ?, ?, ?)
    ");
    $stmt->execute([$email, $user_id, $verification_code, $expires_at]);

    // Send email
    $subject = "Email Verification - MoreBites POS System (Resent)";
    $message = "
    <html>
    <head>
        <title>Email Verification</title>
        <style>
            body { font-family: Arial, sans-serif; line-height: 1.6; color: #333; }
            .container { max-width: 600px; margin: 0 auto; padding: 20px; }
            .header { background-color: #8B4513; color: white; padding: 20px; text-align: center; }
            .content { background-color: #f9f9f9; padding: 30px; border-radius: 5px; margin: 20px 0; }
            .code { font-size: 24px; font-weight: bold; color: #8B4513; text-align: center; 
                   background-color: #fff; padding: 15px; border: 2px dashed #8B4513; 
                   border-radius: 5px; margin: 20px 0; letter-spacing: 3px; }
            .footer { text-align: center; color: #666; font-size: 12px; margin-top: 20px; }
            .warning { color: #d9534f; font-weight: bold; }
        </style>
    </head>
    <body>
        <div class='container'>
            <div class='header'>
                <h1>MoreBites POS System</h1>
                <h2>Email Verification Code (Resent)</h2>
            </div>
            <div class='content'>
                <h3>Hello {$user['user_name']}!</h3>
                <p>You requested a new verification code. Please use the code below to verify your email address and activate your account:</p>
                
                <div class='code'>{$verification_code}</div>
                
                <p><strong>Important:</strong></p>
                <ul>
                    <li>This code will expire in <span class='warning'>10 minutes</span></li>
                    <li>Enter this code exactly as shown</li>
                    <li>Your account will remain inactive until verification is complete</li>
                    <li>This code replaces any previous verification codes</li>
                </ul>
                
                <p>If you're having trouble with the verification process, please contact your system administrator.</p>
            </div>
            <div class='footer'>
                <p>This is an automated message from MoreBites POS System. Please do not reply to this email.</p>
                <p>&copy; " . date('Y') . " MoreBites. All rights reserved.</p>
            </div>
        </div>
    </body>
    </html>
    ";

    // Headers for HTML email
    $headers = array(
        'MIME-Version: 1.0',
        'Content-type: text/html; charset=UTF-8',
        'From: MoreBites POS System <noreply@morebites.com>',
        'Reply-To: noreply@morebites.com',
        'X-Mailer: PHP/' . phpversion()
    );

    $mail_sent = mail($email, $subject, $message, implode("\r\n", $headers));

    if (!$mail_sent) {
        throw new Exception("Failed to send verification email. Please try again.");
    }

    echo json_encode([
        'success' => true,
        'message' => 'New verification code sent successfully to your email address.'
    ]);

} catch (Exception $e) {
    echo json_encode([
        'success' => false,
        'message' => $e->getMessage()
    ]);
}
?>