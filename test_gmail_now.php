<?php
/**
 * Test Gmail sending
 */
require_once 'simple_email.php';

echo "<h2>🧪 Testing Gmail Email Sending</h2>";

$test_email = 'carlmadelo22@gmail.com';
$test_code = str_pad(rand(0, 999999), 6, '0', STR_PAD_LEFT);

echo "<div style='background: #e3f2fd; padding: 20px; border-radius: 10px; margin: 20px 0;'>";
echo "<h3>📧 Sending Test Email</h3>";
echo "<p><strong>To:</strong> $test_email</p>";
echo "<p><strong>Test Code:</strong> $test_code</p>";
echo "</div>";

try {
    $result = sendSimpleVerificationEmail($test_email, $test_code, 'Test User');
    
    echo "<div style='padding: 20px; margin: 20px 0; border-radius: 10px; ";
    
    if ($result['mail_attempt']) {
        echo "background: #d4edda; border-left: 5px solid #28a745;'>";
        echo "<h3>🎉 Email Sent Successfully!</h3>";
        echo "<p>✅ Method: {$result['method']}</p>";
        echo "<p>✅ Status: Email sent to Gmail SMTP</p>";
        echo "<p><strong>Check your Gmail inbox now!</strong></p>";
        echo "<p>If you don't see the email, check your spam folder.</p>";
    } else {
        echo "background: #fff3cd; border-left: 5px solid #ffc107;'>";
        echo "<h3>⚠️ Email Sending Simulated</h3>";
        echo "<p>Gmail SMTP may not be fully configured.</p>";
        echo "<p>The verification code would be: <strong>$test_code</strong></p>";
        echo "<p>Try the Gmail setup if you want real emails.</p>";
    }
    
    echo "</div>";
    
} catch (Exception $e) {
    echo "<div style='background: #f8d7da; padding: 20px; border-radius: 10px; margin: 20px 0;'>";
    echo "<h3>❌ Test Failed</h3>";
    echo "<p>Error: " . htmlspecialchars($e->getMessage()) . "</p>";
    echo "</div>";
}

echo "<div style='text-align: center; margin: 30px 0;'>";
echo "<a href='get_verification_code.php' style='background: #17a2b8; color: white; padding: 15px 30px; text-decoration: none; border-radius: 8px; margin: 10px;'>🔑 Get Current Code</a>";
echo "<a href='setup_real_gmail.php' style='background: #28a745; color: white; padding: 15px 30px; text-decoration: none; border-radius: 8px; margin: 10px;'>⚙️ Setup Gmail</a>";
echo "<a href='add_user.php?role=cashier' style='background: #007bff; color: white; padding: 15px 30px; text-decoration: none; border-radius: 8px; margin: 10px;'>👤 Create Cashier</a>";
echo "</div>";
?>