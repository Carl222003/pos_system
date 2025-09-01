<?php
require_once 'db_connect.php';
require_once 'auth_function.php';
require_once 'generate_employee_id.php';

header('Content-Type: application/json');

try {
    // Check if required parameters are provided
    if (empty($_POST['email']) || empty($_POST['verification_id']) || empty($_POST['verification_code'])) {
        throw new Exception("Email, verification ID, and verification code are required");
    }

    $email = filter_var($_POST['email'], FILTER_VALIDATE_EMAIL);
    if (!$email) {
        throw new Exception("Invalid email format");
    }

    $verification_id = intval($_POST['verification_id']);
    $verification_code = trim($_POST['verification_code']);

    if (strlen($verification_code) !== 6 || !ctype_digit($verification_code)) {
        throw new Exception("Verification code must be a 6-digit number");
    }

    // Set timezone to Asia/Manila for accurate time comparison
    date_default_timezone_set('Asia/Manila');
    
    // Check if verification code exists and is valid
    $stmt = $pdo->prepare("
        SELECT id, form_data, attempt_count, is_verified 
        FROM pos_email_verification 
        WHERE id = ? AND email = ? AND verification_code = ? AND expires_at > CONVERT_TZ(NOW(), @@session.time_zone, '+08:00')
    ");
    $stmt->execute([$verification_id, $email, $verification_code]);
    $verification = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$verification) {
        // Increment attempt count for this verification
        $stmt = $pdo->prepare("
            UPDATE pos_email_verification 
            SET attempt_count = attempt_count + 1 
            WHERE id = ? AND expires_at > CONVERT_TZ(NOW(), @@session.time_zone, '+08:00')
        ");
        $stmt->execute([$verification_id]);
        
        throw new Exception("Invalid or expired verification code");
    }

    // Check attempt limit (max 5 attempts)
    if ($verification['attempt_count'] >= 5) {
        throw new Exception("Too many verification attempts. Please request a new code.");
    }

    // Check if already verified
    if ($verification['is_verified']) {
        throw new Exception("This verification code has already been used");
    }

    // Decode the stored form data
    $form_data = json_decode($verification['form_data'], true);
    if (!$form_data) {
        throw new Exception("Invalid form data stored");
    }

    // Start transaction
    $pdo->beginTransaction();

    // Mark verification as complete
    $stmt = $pdo->prepare("
        UPDATE pos_email_verification 
        SET is_verified = TRUE 
        WHERE id = ?
    ");
    $stmt->execute([$verification_id]);

    // Check if email already exists (security check)
    $stmt = $pdo->prepare("SELECT COUNT(*) FROM pos_user WHERE user_email = ?");
    $stmt->execute([$email]);
    if ($stmt->fetchColumn() > 0) {
        throw new Exception("Email already exists in the system");
    }

    // Handle profile image upload if provided
    $profile_image = '';
    if (isset($_FILES['profile_image']) && $_FILES['profile_image']['error'] === 0) {
        $allowed = ['jpg', 'jpeg', 'png', 'gif'];
        $filename = $_FILES['profile_image']['name'];
        $ext = strtolower(pathinfo($filename, PATHINFO_EXTENSION));
        
        if (!in_array($ext, $allowed)) {
            throw new Exception("Invalid image format. Allowed formats: " . implode(', ', $allowed));
        }

        $upload_name = 'profile_' . time() . '.' . $ext;
        $upload_path = 'uploads/profiles/' . $upload_name;
        
        if (!move_uploaded_file($_FILES['profile_image']['tmp_name'], $upload_path)) {
            throw new Exception("Failed to upload profile image");
        }
        
        $profile_image = $upload_path;
    }

    // Generate Employee ID for cashier/stockman
    $employee_id = null;
    if ($form_data['user_type'] === 'Cashier' || $form_data['user_type'] === 'Stockman') {
        $employee_id = generateEmployeeID($pdo, $form_data['user_type']);
    }

    // Insert into pos_user table
    $stmt = $pdo->prepare("
        INSERT INTO pos_user (
            user_name,
            user_email,
            user_password,
            user_type,
            contact_number,
            profile_image,
            user_status,
            branch_id,
            employee_id,
            created_at
        ) VALUES (
            :user_name,
            :user_email,
            :user_password,
            :user_type,
            :contact_number,
            :profile_image,
            'Active',
            :branch_id,
            :employee_id,
            NOW()
        )
    ");

    $stmt->execute([
        'user_name' => $form_data['user_name'],
        'user_email' => $form_data['user_email'],
        'user_password' => $form_data['user_password'], // Already hashed when stored
        'user_type' => $form_data['user_type'],
        'contact_number' => $form_data['contact_number'],
        'profile_image' => $profile_image,
        'branch_id' => ($form_data['user_type'] === 'Cashier' || $form_data['user_type'] === 'Stockman') ? $form_data['branch_id'] : null,
        'employee_id' => $employee_id
    ]);

    $user_id = $pdo->lastInsertId();

    // If user is a cashier, insert additional information
    if ($form_data['user_type'] === 'Cashier') {
        $stmt = $pdo->prepare("
            INSERT INTO pos_cashier_details (
                user_id,
                branch_id,
                employee_id,
                date_hired,
                emergency_contact,
                emergency_number,
                address,
                notes,
                created_at
            ) VALUES (
                :user_id,
                :branch_id,
                :employee_id,
                :date_hired,
                :emergency_contact,
                :emergency_number,
                :address,
                :notes,
                NOW()
            )
        ");

        $stmt->execute([
            'user_id' => $user_id,
            'branch_id' => $form_data['branch_id'],
            'employee_id' => $employee_id,
            'date_hired' => $form_data['date_hired'],
            'emergency_contact' => $form_data['emergency_contact'],
            'emergency_number' => $form_data['emergency_number'],
            'address' => $form_data['address'],
            'notes' => isset($form_data['notes']) ? $form_data['notes'] : null
        ]);
    }

    // If user is a stockman, insert additional information
    if ($form_data['user_type'] === 'Stockman') {
        $stmt = $pdo->prepare("
            INSERT INTO pos_stockman_details (
                user_id,
                branch_id,
                employee_id,
                date_hired,
                emergency_contact,
                emergency_number,
                address,
                notes,
                created_at
            ) VALUES (
                :user_id,
                :branch_id,
                :employee_id,
                :date_hired,
                :emergency_contact,
                :emergency_number,
                :address,
                :notes,
                NOW()
            )
        ");

        $stmt->execute([
            'user_id' => $user_id,
            'branch_id' => $form_data['branch_id'],
            'employee_id' => $employee_id,
            'date_hired' => $form_data['date_hired'],
            'emergency_contact' => $form_data['emergency_contact'],
            'emergency_number' => $form_data['emergency_number'],
            'address' => $form_data['address'],
            'notes' => isset($form_data['notes']) ? $form_data['notes'] : null
        ]);
    }

    // Commit transaction
    $pdo->commit();

    // Log activity for adding cashier or stockman
    $admin_id = $_SESSION['user_id'] ?? null;
    if ($admin_id) {
        logActivity($pdo, $admin_id, 'Added ' . $form_data['user_type'], 
                   $form_data['user_type'] . ': ' . $form_data['user_name'] . ' (ID: ' . $user_id . ') - Email verified');
    }

    // Clean up verification codes for this email
    $stmt = $pdo->prepare("DELETE FROM pos_email_verification WHERE email = ?");
    $stmt->execute([$email]);

    echo json_encode([
        'success' => true,
        'message' => 'Email verified successfully! Account has been created and activated.' . 
                    ($employee_id ? ' (Employee ID: ' . $employee_id . ')' : ''),
        'employee_id' => $employee_id,
        'user_id' => $user_id
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