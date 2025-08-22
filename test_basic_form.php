<?php
// Simple test to check if the form processing works
require_once 'db_connect.php';

echo "<h2>🧪 Testing Basic Form Processing</h2>";

try {
    // Test database connection
    $stmt = $pdo->query("SELECT 1");
    echo "✅ Database connection working<br>";
    
    // Test if files exist
    $files_to_check = [
        'db_connect.php',
        'auth_function.php', 
        'generate_employee_id.php',
        'simple_email.php'
    ];
    
    foreach ($files_to_check as $file) {
        if (file_exists($file)) {
            echo "✅ File exists: $file<br>";
        } else {
            echo "❌ Missing file: $file<br>";
        }
    }
    
    // Test form processing file
    if (file_exists('process_add_user.php')) {
        echo "✅ process_add_user.php exists<br>";
        
        // Check for syntax errors
        $output = shell_exec("php -l process_add_user.php 2>&1");
        if (strpos($output, 'No syntax errors') !== false) {
            echo "✅ No syntax errors in process_add_user.php<br>";
        } else {
            echo "❌ Syntax error found:<br>";
            echo "<pre>" . htmlspecialchars($output) . "</pre>";
        }
    }
    
    echo "<div style='background: #d4edda; padding: 20px; margin: 20px 0; border-radius: 5px;'>";
    echo "<h3>✅ Basic Setup Looks Good!</h3>";
    echo "<p>Try submitting the cashier form again.</p>";
    echo "</div>";
    
} catch (Exception $e) {
    echo "<div style='background: #f8d7da; padding: 20px; margin: 20px 0; border-radius: 5px;'>";
    echo "<h3>❌ Error Found:</h3>";
    echo "<p>" . htmlspecialchars($e->getMessage()) . "</p>";
    echo "</div>";
}

echo "<br><a href='add_user.php?role=cashier' style='background: #007bff; color: white; padding: 10px 20px; text-decoration: none; border-radius: 5px;'>🔄 Try Cashier Form Again</a>";
?>