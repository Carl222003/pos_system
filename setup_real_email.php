<?php
echo "<h2>📧 Configure Real Email Sending</h2>";

echo "<div style='background: #d1ecf1; border: 1px solid #bee5eb; padding: 20px; border-radius: 5px; margin: 20px 0;'>";
echo "<h3>Option 1: Gmail SMTP Configuration</h3>";
echo "<p>To send real emails through Gmail:</p>";
echo "<ol>";
echo "<li>Enable 2-factor authentication on your Gmail account</li>";
echo "<li>Generate an App Password for PHP mail</li>";
echo "<li>Update the mail configuration in PHP</li>";
echo "</ol>";
echo "</div>";

echo "<div style='background: #f8d7da; border: 1px solid #f5c6cb; padding: 20px; border-radius: 5px; margin: 20px 0;'>";
echo "<h3>Current Status: Test Mode Active</h3>";
echo "<p>The system is currently set to test mode because:</p>";
echo "<ul>";
echo "<li>No mail server is configured in XAMPP</li>";
echo "<li>Verification codes are shown directly in the modal</li>";
echo "<li>This prevents email sending errors</li>";
echo "</ul>";
echo "</div>";

echo "<h3>📱 How to Use Test Mode:</h3>";
echo "<div style='background: #fff3cd; border: 1px solid #ffeaa7; padding: 15px; border-radius: 5px;'>";
echo "<p><strong>1.</strong> Fill the user form completely</p>";
echo "<p><strong>2.</strong> Click 'Save Cashier' or 'Save Stockman'</p>";
echo "<p><strong>3.</strong> Modal appears with verification code in orange box</p>";
echo "<p><strong>4.</strong> Copy the code and enter it in the verification field</p>";
echo "<p><strong>5.</strong> Click 'Verify Email' to complete account creation</p>";
echo "</div>";

echo "<p><a href='add_user.php?role=cashier' style='background: #28a745; color: white; padding: 10px 20px; text-decoration: none; border-radius: 5px;'>🔄 Try Again with Test Mode</a></p>";
?>

<style>
body { font-family: Arial, sans-serif; max-width: 800px; margin: 50px auto; padding: 20px; }
h2, h3 { color: #333; }
</style>