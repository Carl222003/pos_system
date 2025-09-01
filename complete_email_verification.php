<?php
require_once 'db_connect.php';
require_once 'auth_function.php';

header('Content-Type: application/json');

try {
    // Check if required parameters are provided
    if (empty($_POST['email']) || empty($_POST['user_id']) || empty($_POST['verification_code'])) {
        throw new Exception("Email, user ID, and verification code are required");
    }

    $email = filter_var($_POST['email'], FILTER_VALIDATE_EMAIL);
    if (!$email) {
        throw new Exception("Invalid email format");
    }

    $user_id = intval($_POST['user_id']);
    $verification_code = trim($_POST['verification_code']);

    if (strlen($verification_code) !== 6 || !ctype_digit($verification_code)) {
        throw new Exception("Verification code must be a 6-digit number");
    }

    // Set timezone to Asia/Manila for accurate time comparison
    date_default_timezone_set('Asia/Manila');
    
    // Check if verification code exists and is valid
    $stmt = $pdo->prepare("
        SELECT id, attempt_count, is_verified 
        FROM pos_email_verification 
        WHERE email = ? AND user_id = ? AND verification_code = ? AND expires_at > CONVERT_TZ(NOW(), @@session.time_zone, '+08:00')
    ");
    $stmt->execute([$email, $user_id, $verification_code]);
    $verification = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$verification) {
        // Increment attempt count for this email/user
        $stmt = $pdo->prepare("
            UPDATE pos_email_verification 
            SET attempt_count = attempt_count + 1 
            WHERE email = ? AND user_id = ? AND expires_at > CONVERT_TZ(NOW(), @@session.time_zone, '+08:00')
        ");
        $stmt->execute([$email, $user_id]);
        
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

    // Start transaction
    $pdo->beginTransaction();

    // Mark verification as complete
    $stmt = $pdo->prepare("
        UPDATE pos_email_verification 
        SET is_verified = TRUE 
        WHERE id = ?
    ");
    $stmt->execute([$verification['id']]);

    // Activate the user account
    $stmt = $pdo->prepare("
        UPDATE pos_user 
        SET user_status = 'Active' 
        WHERE user_id = ? AND user_email = ?
    ");
    $stmt->execute([$user_id, $email]);

    // Log the activation
    $stmt = $pdo->prepare("SELECT user_name, user_type FROM pos_user WHERE user_id = ?");
    $stmt->execute([$user_id]);
    $user_info = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($user_info) {
        // Get admin ID from session (who created the account)
        $admin_id = $_SESSION['user_id'] ?? null;
        if ($admin_id) {
            logActivity($pdo, $admin_id, 'Activated ' . $user_info['user_type'], 
                       $user_info['user_type'] . ': ' . $user_info['user_name'] . ' (ID: ' . $user_id . ') - Email verified');
        }
    }

    // Commit transaction
    $pdo->commit();

    // Clean up verification codes for this user
    $stmt = $pdo->prepare("DELETE FROM pos_email_verification WHERE email = ? OR user_id = ?");
    $stmt->execute([$email, $user_id]);

    echo json_encode([
        'success' => true,
        'message' => 'Email verified successfully! Account has been activated.'
    ]);

} catch (Exception $e) {
    // Rollback transaction on error
    if ($pdo->inTransaction()) {
        $pdo->rollBack();
    }
    
    echo json_encode([
        'success' => false,
        'message' => $e->getMessage()
    ]);
}
?>