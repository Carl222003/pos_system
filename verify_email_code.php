<?php
require_once 'db_connect.php';

header('Content-Type: application/json');

try {
    // Check if email and code are provided
    if (empty($_POST['email']) || empty($_POST['verification_code'])) {
        throw new Exception("Email and verification code are required");
    }

    $email = filter_var($_POST['email'], FILTER_VALIDATE_EMAIL);
    if (!$email) {
        throw new Exception("Invalid email format");
    }

    $verification_code = trim($_POST['verification_code']);
    if (strlen($verification_code) !== 6 || !ctype_digit($verification_code)) {
        throw new Exception("Verification code must be a 6-digit number");
    }

    // Check if verification code exists and is not expired
    $stmt = $pdo->prepare("
        SELECT id, attempt_count, is_verified 
        FROM pos_email_verification 
        WHERE email = ? AND verification_code = ? AND expires_at > NOW()
    ");
    $stmt->execute([$email, $verification_code]);
    $verification = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$verification) {
        // Increment attempt count for this email
        $stmt = $pdo->prepare("
            UPDATE pos_email_verification 
            SET attempt_count = attempt_count + 1 
            WHERE email = ? AND expires_at > NOW()
        ");
        $stmt->execute([$email]);
        
        throw new Exception("Invalid or expired verification code");
    }

    // Check attempt limit (max 5 attempts)
    if ($verification['attempt_count'] >= 5) {
        throw new Exception("Too many verification attempts. Please request a new code.");
    }

    // Check if already verified
    if ($verification['is_verified']) {
        echo json_encode([
            'success' => true,
            'message' => 'Email already verified successfully'
        ]);
        exit;
    }

    // Mark as verified
    $stmt = $pdo->prepare("
        UPDATE pos_email_verification 
        SET is_verified = TRUE 
        WHERE id = ?
    ");
    $stmt->execute([$verification['id']]);

    echo json_encode([
        'success' => true,
        'message' => 'Email verified successfully! You can now complete your registration.'
    ]);

} catch (Exception $e) {
    echo json_encode([
        'success' => false,
        'message' => $e->getMessage()
    ]);
}
?>