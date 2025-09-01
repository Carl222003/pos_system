<?php
// Email configuration for real email sending
// Set this to true to enable real email sending
define('ENABLE_REAL_EMAIL', true); // Enable real email sending

// Gmail SMTP configuration (if using Gmail)
// IMPORTANT: Use a Gmail App Password (not your regular Gmail password)
define('SMTP_HOST', 'smtp.gmail.com');
define('SMTP_PORT', 587);
define('SMTP_USERNAME', 'carlmadelo22@gmail.com'); // ← CHANGE THIS to your Gmail address
define('SMTP_PASSWORD', 'ykod xjaj mmyx lznu');    // ← Replace with your Gmail App Password (16 characters)
define('SMTP_ENCRYPTION', 'tls');

// Function to send email using SMTP
function sendEmailSMTP($to, $subject, $message, $headers = []) {
    if (!ENABLE_REAL_EMAIL) {
        // Test mode - don't send real emails
        return true;
    }
    
    // In production, you would use PHPMailer or similar library
    // For now, we'll use PHP's mail() function with proper headers
    
    $additional_headers = [
        'MIME-Version: 1.0',
        'Content-type: text/html; charset=UTF-8',
        'From: MoreBites POS System <noreply@morebites.com>',
        'Reply-To: noreply@morebites.com',
        'X-Mailer: PHP/' . phpversion()
    ];
    
    $all_headers = array_merge($additional_headers, $headers);
    
    // Configure PHP mail to use SMTP (requires php.ini configuration)
    ini_set('SMTP', SMTP_HOST);
    ini_set('smtp_port', SMTP_PORT);
    ini_set('sendmail_from', SMTP_USERNAME);
    
    return mail($to, $subject, $message, implode("\r\n", $all_headers));
}

// Function to check if real email is enabled
function isRealEmailEnabled() {
    return ENABLE_REAL_EMAIL;
}
?>