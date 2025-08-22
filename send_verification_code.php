<?php
require_once 'db_connect.php';

header('Content-Type: application/json');

try {
    // Check if email is provided
    if (empty($_POST['email'])) {
        throw new Exception("Email is required");
    }

    $email = filter_var($_POST['email'], FILTER_VALIDATE_EMAIL);
    if (!$email) {
        throw new Exception("Invalid email format");
    }

    // Check if email already exists in users table
    $stmt = $pdo->prepare("SELECT COUNT(*) FROM pos_user WHERE user_email = ?");
    $stmt->execute([$email]);
    if ($stmt->fetchColumn() > 0) {
        throw new Exception("Email already exists in the system");
    }

    // Create verification table if it doesn't exist
    $pdo->exec("
        CREATE TABLE IF NOT EXISTS pos_email_verification (
            id INT AUTO_INCREMENT PRIMARY KEY,
            email VARCHAR(255) NOT NULL,
            verification_code VARCHAR(6) NOT NULL,
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            expires_at TIMESTAMP NOT NULL,
            is_verified BOOLEAN DEFAULT FALSE,
            attempt_count INT DEFAULT 0,
            INDEX idx_email (email),
            INDEX idx_code (verification_code),
            INDEX idx_expires (expires_at)
        )
    ");

    // Generate 6-digit verification code
    $verification_code = str_pad(rand(0, 999999), 6, '0', STR_PAD_LEFT);
    
    // Set expiration time (10 minutes from now)
    $expires_at = date('Y-m-d H:i:s', strtotime('+10 minutes'));

    // Delete any existing verification codes for this email
    $stmt = $pdo->prepare("DELETE FROM pos_email_verification WHERE email = ?");
    $stmt->execute([$email]);

    // Insert new verification code
    $stmt = $pdo->prepare("
        INSERT INTO pos_email_verification (email, verification_code, expires_at) 
        VALUES (?, ?, ?)
    ");
    $stmt->execute([$email, $verification_code, $expires_at]);

    // Send email (using PHP's mail function for simplicity)
    $subject = "Email Verification - MoreBites POS System";
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
                <h2>Email Verification Required</h2>
            </div>
            <div class='content'>
                <h3>Hello!</h3>
                <p>Thank you for registering with MoreBites POS System. To complete your registration, please verify your email address by entering the following verification code:</p>
                
                <div class='code'>{$verification_code}</div>
                
                <p><strong>Important:</strong></p>
                <ul>
                    <li>This code will expire in <span class='warning'>10 minutes</span></li>
                    <li>Enter this code exactly as shown</li>
                    <li>If you didn't request this verification, please ignore this email</li>
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
        throw new Exception("Failed to send verification email. Please check your email configuration.");
    }

    echo json_encode([
        'success' => true,
        'message' => 'Verification code sent successfully to your email address. Please check your inbox and enter the 6-digit code below.',
        'expires_in' => 10 // minutes
    ]);

} catch (Exception $e) {
    echo json_encode([
        'success' => false,
        'message' => $e->getMessage()
    ]);
}
?>