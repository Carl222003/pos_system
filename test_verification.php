<?php
require_once 'db_connect.php';

// Simple test to generate a verification code
$test_email = "test@example.com";
$verification_code = str_pad(rand(0, 999999), 6, '0', STR_PAD_LEFT);

echo "<h2>📧 Email Verification Test</h2>";
echo "<div style='background: #fff3cd; border: 1px solid #ffeaa7; padding: 20px; border-radius: 5px; margin: 20px 0;'>";
echo "<h3>🔔 Test Mode Active</h3>";
echo "<p><strong>Email:</strong> {$test_email}</p>";
echo "<p><strong>Verification Code:</strong> <span style='font-size: 24px; font-weight: bold; color: #d63384;'>{$verification_code}</span></p>";
echo "<p><small>In production, this would be sent to your email.</small></p>";
echo "</div>";

echo "<h3>📋 Instructions:</h3>";
echo "<ol>";
echo "<li>Go back to the Add User form</li>";
echo "<li>Fill out all required fields</li>";
echo "<li>Click 'Save Cashier' or 'Save Stockman'</li>";
echo "<li>Look for the orange warning box in the modal</li>";
echo "<li>Copy the 6-digit code and paste it in the verification field</li>";
echo "</ol>";

echo "<p><a href='add_user.php?role=cashier' style='background: #007bff; color: white; padding: 10px 20px; text-decoration: none; border-radius: 5px;'>← Go Back to Add User Form</a></p>";
?>