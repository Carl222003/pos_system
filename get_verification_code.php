<?php
/**
 * Get the current verification code for testing
 */
require_once 'db_connect.php';

echo "<h2>📧 Current Verification Code</h2>";

try {
    // Get the most recent verification code for the email
    $email = 'carlmadelo22@gmail.com'; // Your email from the form
    
    $stmt = $pdo->prepare("
        SELECT verification_code, created_at, expires_at, is_verified 
        FROM pos_email_verification 
        WHERE email = ? 
        ORDER BY created_at DESC 
        LIMIT 1
    ");
    $stmt->execute([$email]);
    $verification = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if ($verification) {
        echo "<div style='background: #fff3cd; padding: 25px; border-radius: 10px; margin: 20px 0; border-left: 5px solid #ffc107;'>";
        echo "<h3>🔑 Your Verification Code:</h3>";
        echo "<div style='font-size: 36px; font-weight: bold; color: #8B4513; text-align: center; background: white; padding: 20px; border: 3px dashed #8B4513; border-radius: 10px; letter-spacing: 5px; font-family: monospace;'>";
        echo $verification['verification_code'];
        echo "</div>";
        echo "<p><strong>Email:</strong> $email</p>";
        echo "<p><strong>Created:</strong> {$verification['created_at']}</p>";
        echo "<p><strong>Expires:</strong> {$verification['expires_at']}</p>";
        echo "<p><strong>Status:</strong> " . ($verification['is_verified'] ? 'Verified ✅' : 'Pending ⏳') . "</p>";
        echo "</div>";
        
        // Check if expired
        $now = new DateTime();
        $expires = new DateTime($verification['expires_at']);
        if ($now > $expires) {
            echo "<div style='background: #f8d7da; padding: 20px; border-radius: 10px; margin: 20px 0;'>";
            echo "<h4>⚠️ Code Expired</h4>";
            echo "<p>This verification code has expired. You'll need to request a new one.</p>";
            echo "</div>";
        } else {
            $diff = $expires->diff($now);
            $minutes = $diff->i;
            $seconds = $diff->s;
            echo "<div style='background: #d4edda; padding: 20px; border-radius: 10px; margin: 20px 0;'>";
            echo "<h4>✅ Code Still Valid</h4>";
            echo "<p>Expires in: {$minutes} minutes and {$seconds} seconds</p>";
            echo "</div>";
        }
    } else {
        echo "<div style='background: #f8d7da; padding: 20px; border-radius: 10px; margin: 20px 0;'>";
        echo "<h3>❌ No Verification Code Found</h3>";
        echo "<p>No verification code found for email: $email</p>";
        echo "<p>Try submitting the form again.</p>";
        echo "</div>";
    }
    
} catch (Exception $e) {
    echo "<div style='background: #f8d7da; padding: 20px; border-radius: 10px; margin: 20px 0;'>";
    echo "<h3>❌ Database Error</h3>";
    echo "<p>" . htmlspecialchars($e->getMessage()) . "</p>";
    echo "</div>";
}

echo "<h2>🚀 Set Up Real Gmail Sending</h2>";
echo "<div style='background: #e3f2fd; padding: 25px; border-radius: 10px; margin: 20px 0;'>";
echo "<h3>Quick Gmail Setup (2 minutes):</h3>";
echo "<ol>";
echo "<li><strong>Get Gmail App Password:</strong>";
echo "<ul>";
echo "<li>Go to <a href='https://myaccount.google.com/security' target='_blank'>Google Account Security</a></li>";
echo "<li>Enable 2-factor authentication</li>";
echo "<li>Generate App Password for 'Mail'</li>";
echo "<li>Copy the 16-character password</li>";
echo "</ul></li>";
echo "<li><strong>Update Configuration:</strong>";
echo "<ul>";
echo "<li>Edit <code>simple_email.php</code></li>";
echo "<li>Replace 'your-email@gmail.com' with 'carlmadelo22@gmail.com'</li>";
echo "<li>Replace 'your-app-password' with your Gmail app password</li>";
echo "</ul></li>";
echo "</ol>";
echo "</div>";

echo "<div style='text-align: center; margin: 30px 0;'>";
echo "<a href='add_user.php?role=cashier' style='background: #007bff; color: white; padding: 15px 30px; text-decoration: none; border-radius: 8px; margin: 10px;'>🔄 Try Form Again</a>";
echo "<a href='setup_real_gmail.php' style='background: #28a745; color: white; padding: 15px 30px; text-decoration: none; border-radius: 8px; margin: 10px;'>📧 Setup Real Gmail</a>";
echo "</div>";
?>