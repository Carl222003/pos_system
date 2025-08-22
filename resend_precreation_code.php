<?php
require_once 'db_connect.php';
@require_once 'phpmailer_email.php';
require_once 'simple_email.php';

header('Content-Type: application/json');

try {
    // Check if required parameters are provided
    if (empty($_POST['email']) || empty($_POST['verification_id'])) {
        throw new Exception("Email and verification ID are required");
    }

    $email = filter_var($_POST['email'], FILTER_VALIDATE_EMAIL);
    if (!$email) {
        throw new Exception("Invalid email format");
    }

    $verification_id = intval($_POST['verification_id']);

    // Check if verification exists and get form data
    $stmt = $pdo->prepare("
        SELECT form_data, is_verified 
        FROM pos_email_verification 
        WHERE id = ? AND email = ?
    ");
    $stmt->execute([$verification_id, $email]);
    $verification = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$verification) {
        throw new Exception("Verification request not found");
    }

    if ($verification['is_verified']) {
        throw new Exception("This verification has already been completed");
    }

    // Decode form data to get user name
    $form_data = json_decode($verification['form_data'], true);
    if (!$form_data) {
        throw new Exception("Invalid verification data");
    }

    // Generate new 6-digit verification code
    $verification_code = str_pad(rand(0, 999999), 6, '0', STR_PAD_LEFT);
    
    // Set expiration time (10 minutes from now)
    $expires_at = date('Y-m-d H:i:s', strtotime('+10 minutes'));

    // Update the verification record with new code and reset attempt count
    $stmt = $pdo->prepare("
        UPDATE pos_email_verification 
        SET verification_code = ?, expires_at = ?, attempt_count = 0 
        WHERE id = ?
    ");
    $stmt->execute([$verification_code, $expires_at, $verification_id]);

    // Attempt to send via PHPMailer first, then fallback to simple mail
    $email_result = null;
    if (function_exists('sendVerificationEmailPHPMailer')) {
        $pmResult = sendVerificationEmailPHPMailer($email, $verification_code, $form_data['user_name']);
        if (is_array($pmResult) && !empty($pmResult['success'])) {
            $email_result = [
                'success' => true,
                'method' => 'PHPMailer SMTP',
                'mail_attempt' => true,
                'message' => 'Email sent successfully via PHPMailer'
            ];
        } else {
            $email_result = sendSimpleVerificationEmail($email, $verification_code, $form_data['user_name']);
        }
    } else {
        $email_result = sendSimpleVerificationEmail($email, $verification_code, $form_data['user_name']);
    }

    // Log the result
    error_log("RESEND VERIFICATION CODE for {$email}: {$verification_code}; method=" . ($email_result['method'] ?? 'unknown'));

    echo json_encode([
        'success' => true,
        'message' => 'New verification code sent successfully to your email address.',
        'verification_code' => $verification_code,
        'test_mode' => empty($email_result['mail_attempt']),
        'email_status' => !empty($email_result['mail_attempt']) ? ("Email attempted via " . $email_result['method']) : 'Email simulated - using test mode'
    ]);

} catch (Exception $e) {
    echo json_encode([
        'success' => false,
        'message' => $e->getMessage()
    ]);
}
?>