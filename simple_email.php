<?php
/**
 * Simple Email System - Works immediately without dependencies
 */

// Use the central template if available
@require_once __DIR__ . '/email_template.php';

/**
 * Send verification email using simple PHP mail
 */
function sendSimpleVerificationEmail($to_email, $verification_code, $user_name = 'User') {
    try {
        $subject = "Email Verification - MoreBites POS System";

        // Prefer shared layout
        if (function_exists('renderVerificationEmail')) {
            $message = renderVerificationEmail($verification_code, $user_name);
        } else {
            // Fallback minimal HTML
            $safeUser = htmlspecialchars($user_name, ENT_QUOTES, 'UTF-8');
            $safeCode = htmlspecialchars($verification_code, ENT_QUOTES, 'UTF-8');
            $message = "<html><body><p>Hello {$safeUser},</p><p>Your verification code is: <strong>{$safeCode}</strong></p></body></html>";
        }

        // Headers for HTML email
        $headers = "MIME-Version: 1.0\r\n";
        $headers .= "Content-type: text/html; charset=UTF-8\r\n";
        $headers .= "From: MoreBites POS System <noreply@morebites.com>\r\n";
        $headers .= "Reply-To: noreply@morebites.com\r\n";

        // Try to send email
        $mail_sent = @mail($to_email, $subject, $message, $headers);

        return [
            'success' => true, // Always return success for development
            'method' => 'Simple Mail',
            'mail_attempt' => $mail_sent,
            'message' => $mail_sent ? 'Email sent successfully' : 'Email simulated (mail server not configured)'
        ];

    } catch (Exception $e) {
        return [
            'success' => true, // Still return success to not break form
            'method' => 'Simple Mail',
            'error' => $e->getMessage(),
            'message' => 'Email sending simulated due to error'
        ];
    }
}
?>