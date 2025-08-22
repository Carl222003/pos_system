<?php
require_once 'db_connect.php';
require_once 'auth_function.php';
require_once 'generate_employee_id.php';

// Prefer PHPMailer (Gmail SMTP) with fallback to simple mail
@require_once 'phpmailer_email.php';
require_once 'simple_email.php';

header('Content-Type: application/json');

// Function to send verification email before account creation
function sendPreCreationVerification($form_data, $pdo) {
    try {
        // Create verification table if it doesn't exist
        $pdo->exec("SET sql_mode = ''"); // Temporarily disable strict mode
        $pdo->exec("
            CREATE TABLE IF NOT EXISTS pos_email_verification (
                id INT AUTO_INCREMENT PRIMARY KEY,
                email VARCHAR(255) NOT NULL,
                verification_code VARCHAR(6) NOT NULL,
                form_data TEXT,
                created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
                expires_at DATETIME,
                is_verified BOOLEAN DEFAULT FALSE,
                attempt_count INT DEFAULT 0,
                INDEX idx_email (email),
                INDEX idx_code (verification_code),
                INDEX idx_expires (expires_at)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
        ");

        // Generate 6-digit verification code
        $verification_code = str_pad(rand(0, 999999), 6, '0', STR_PAD_LEFT);
        
        // Set expiration time (10 minutes from now)
        $expires_at = date('Y-m-d H:i:s', time() + (10 * 60));

        // Store form data as JSON (excluding sensitive password)
        $safe_form_data = $form_data;
        $safe_form_data['user_password'] = password_hash($form_data['user_password'], PASSWORD_DEFAULT); // Hash password before storing
        $form_json = json_encode($safe_form_data);

        // Delete any existing verification codes for this email
        $stmt = $pdo->prepare("DELETE FROM pos_email_verification WHERE email = ?");
        $stmt->execute([$form_data['user_email']]);

        // Insert new verification code with form data
        $stmt = $pdo->prepare("
            INSERT INTO pos_email_verification (email, verification_code, form_data, expires_at) 
            VALUES (?, ?, ?, ?)
        ");
        $stmt->execute([$form_data['user_email'], $verification_code, $form_json, $expires_at]);
        
        $verification_id = $pdo->lastInsertId();

        // Send email (try PHPMailer first, then fallback to simple mail)
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
                    <h3>Hello {$form_data['user_name']}!</h3>
                    <p>We received a request to create a {$form_data['user_type']} account for you. To complete the account creation, please verify your email address by entering the following verification code:</p>
                    
                    <div class='code'>{$verification_code}</div>
                    
                    <p><strong>Important:</strong></p>
                    <ul>
                        <li>This code will expire in <span class='warning'>10 minutes</span></li>
                        <li>Enter this code exactly as shown</li>
                        <li>Your account will be created only after successful verification</li>
                        <li>If you didn't request this account, please ignore this email</li>
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

        // Attempt PHPMailer (Gmail SMTP) first
        $email_result = null;
        if (function_exists('sendVerificationEmailPHPMailer')) {
            $pmResult = sendVerificationEmailPHPMailer($form_data['user_email'], $verification_code, $form_data['user_name']);
            if (is_array($pmResult) && !empty($pmResult['success'])) {
                $email_result = [
                    'success' => true,
                    'method' => 'PHPMailer SMTP',
                    'mail_attempt' => true,
                    'message' => 'Email sent successfully via PHPMailer'
                ];
            } else {
                // Fallback to simple mail
                $email_result = sendSimpleVerificationEmail($form_data['user_email'], $verification_code, $form_data['user_name']);
            }
        } else {
            // PHPMailer not available - use simple mail
            $email_result = sendSimpleVerificationEmail($form_data['user_email'], $verification_code, $form_data['user_name']);
        }
        
        // Log verification attempt
        error_log("🚀 EMAIL ATTEMPT for {$form_data['user_email']}: {$verification_code}");
        error_log("📧 Email result: " . json_encode($email_result));

        $response = [
            'success' => true, // Always return success for form processing
            'verification_id' => $verification_id,
            'verification_code' => $verification_code, // Always include for backup
            'email_attempt' => $email_result
        ];

        // Always show verification code for development
        $response['test_mode'] = true;
        $response['email_method'] = $email_result['method'];
        $response['message'] = "Verification code sent! Check your email or use the code shown in the modal.";
        
        if (!empty($email_result['mail_attempt'])) {
            $response['email_status'] = "Email attempted via {$email_result['method']}";
        } else {
            $response['email_status'] = "Email simulated - using test mode";
        }

        return $response;

    } catch (Exception $e) {
        // Log the full error for debugging
        error_log("Email verification error: " . $e->getMessage());
        return [
            'success' => false,
            'error' => $e->getMessage(),
            'debug_info' => [
                'file' => $e->getFile(),
                'line' => $e->getLine(),
                'trace' => $e->getTraceAsString()
            ]
        ];
    }
}

try {
    // Validate required fields
    $required_fields = ['user_name', 'user_email', 'user_password', 'user_type', 'contact_number'];
    
    // Add cashier and stockman-specific required fields
    if ($_POST['user_type'] === 'Cashier' || $_POST['user_type'] === 'Stockman') {
        $work_fields = [
            'branch_id',
            'date_hired',
            'emergency_contact',
            'emergency_number',
            'address'
        ];
        $required_fields = array_merge($required_fields, $work_fields);
    }

    foreach ($required_fields as $field) {
        if (empty($_POST[$field])) {
            throw new Exception("$field is required");
        }
    }

    // Validate email format
    if (!filter_var($_POST['user_email'], FILTER_VALIDATE_EMAIL)) {
        throw new Exception("Invalid email format");
    }

    // Check if email already exists
    $stmt = $pdo->prepare("SELECT COUNT(*) FROM pos_user WHERE user_email = ?");
    $stmt->execute([$_POST['user_email']]);
    if ($stmt->fetchColumn() > 0) {
        throw new Exception("Email already exists");
    }

    // For cashiers and stockmen, send verification code first before creating account
    if ($_POST['user_type'] === 'Cashier' || $_POST['user_type'] === 'Stockman') {
        // Store form data temporarily and send verification code
        $verification_result = sendPreCreationVerification($_POST, $pdo);
        
        if ($verification_result['success']) {
            echo json_encode([
                'success' => true,
                'requires_verification' => true,
                'verification_id' => $verification_result['verification_id'],
                'email' => $_POST['user_email'],
                'message' => 'Verification code sent to your email. Please enter the code to complete account creation.'
            ]);
        } else {
            echo json_encode([
                'success' => false,
                'message' => 'Failed to send verification email: ' . $verification_result['error']
            ]);
        }
        exit;
    }

    // For admins, create account directly without verification
    // Start transaction
    $pdo->beginTransaction();

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
            :user_status,
            :branch_id,
            :employee_id,
            NOW()
        )
    ");

    // Handle profile image upload
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

    // Auto-generate Employee ID for cashier/stockman
    $employee_id = null;
    if ($_POST['user_type'] === 'Cashier' || $_POST['user_type'] === 'Stockman') {
        $employee_id = generateEmployeeID($pdo, $_POST['user_type']);
    }
    
    // Set user status - 'Pending' for cashiers/stockmen (requires email verification), 'Active' for admins
    $user_status = ($_POST['user_type'] === 'Cashier' || $_POST['user_type'] === 'Stockman') ? 'Pending' : 'Active';
    
    $stmt->execute([
        'user_name' => $_POST['user_name'],
        'user_email' => $_POST['user_email'],
        'user_password' => password_hash($_POST['user_password'], PASSWORD_DEFAULT),
        'user_type' => $_POST['user_type'],
        'contact_number' => $_POST['contact_number'],
        'profile_image' => $profile_image,
        'user_status' => $user_status,
        'branch_id' => ($_POST['user_type'] === 'Cashier' || $_POST['user_type'] === 'Stockman') ? $_POST['branch_id'] : null,
        'employee_id' => $employee_id
    ]);

    $user_id = $pdo->lastInsertId();

    // If user is a cashier, insert additional information
    if ($_POST['user_type'] === 'Cashier') {
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
            'branch_id' => $_POST['branch_id'],
            'employee_id' => $employee_id,
            'date_hired' => $_POST['date_hired'],
            'emergency_contact' => $_POST['emergency_contact'],
            'emergency_number' => $_POST['emergency_number'],
            'address' => $_POST['address'],
            'notes' => isset($_POST['notes']) ? $_POST['notes'] : null
        ]);
    }

    // If user is a stockman, insert additional information
    if ($_POST['user_type'] === 'Stockman') {
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
            'branch_id' => $_POST['branch_id'],
            'employee_id' => $employee_id,
            'date_hired' => $_POST['date_hired'],
            'emergency_contact' => $_POST['emergency_contact'],
            'emergency_number' => $_POST['emergency_number'],
            'address' => $_POST['address'],
            'notes' => isset($_POST['notes']) ? $_POST['notes'] : null
        ]);
    }

    // Commit transaction
    $pdo->commit();

    // Log activity for adding cashier or stockman
    $admin_id = $_SESSION['user_id'] ?? null;
    $user_type = $_POST['user_type'];
    $user_name = $_POST['user_name'];
    if (($user_type === 'Cashier' || $user_type === 'Stockman') && $admin_id) {
        logActivity($pdo, $admin_id, 'Added ' . $user_type, $user_type . ': ' . $user_name . ' (ID: ' . $user_id . ')');
    }

    // For admins, no verification required
    echo json_encode([
        'success' => true,
        'message' => 'User added successfully' . ($employee_id ? ' (Employee ID: ' . $employee_id . ')' : ''),
        'employee_id' => $employee_id
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