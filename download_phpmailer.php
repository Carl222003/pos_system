<?php
/**
 * Download and setup PHPMailer library
 */

echo "<h2>📥 Downloading PHPMailer Library...</h2>";

// Create vendor directory if it doesn't exist
if (!file_exists('vendor')) {
    mkdir('vendor', 0755, true);
    echo "✅ Created vendor directory<br>";
}

if (!file_exists('vendor/phpmailer')) {
    mkdir('vendor/phpmailer', 0755, true);
    echo "✅ Created PHPMailer directory<br>";
}

// Download PHPMailer files
$phpmailer_files = [
    'PHPMailer.php' => 'https://raw.githubusercontent.com/PHPMailer/PHPMailer/master/src/PHPMailer.php',
    'SMTP.php' => 'https://raw.githubusercontent.com/PHPMailer/PHPMailer/master/src/SMTP.php',
    'Exception.php' => 'https://raw.githubusercontent.com/PHPMailer/PHPMailer/master/src/Exception.php'
];

$download_success = true;

foreach ($phpmailer_files as $filename => $url) {
    echo "📥 Downloading $filename... ";
    
    $content = @file_get_contents($url);
    if ($content === false) {
        echo "❌ Failed<br>";
        $download_success = false;
        continue;
    }
    
    $local_path = "vendor/phpmailer/$filename";
    if (file_put_contents($local_path, $content)) {
        echo "✅ Success<br>";
    } else {
        echo "❌ Failed to save<br>";
        $download_success = false;
    }
}

if ($download_success) {
    echo "<div style='background: #d4edda; padding: 20px; border-radius: 5px; margin: 20px 0;'>";
    echo "<h3>🎉 PHPMailer Downloaded Successfully!</h3>";
    echo "<p>All required PHPMailer files have been downloaded.</p>";
    echo "</div>";
    
    // Test PHPMailer
    echo "<h3>🧪 Testing PHPMailer...</h3>";
    try {
        require_once 'vendor/phpmailer/Exception.php';
        require_once 'vendor/phpmailer/PHPMailer.php';
        require_once 'vendor/phpmailer/SMTP.php';
        
        echo "✅ PHPMailer classes loaded successfully!<br>";
        echo "✅ Ready to send emails!<br>";
        
    } catch (Exception $e) {
        echo "❌ Error testing PHPMailer: " . $e->getMessage() . "<br>";
    }
} else {
    echo "<div style='background: #f8d7da; padding: 20px; border-radius: 5px; margin: 20px 0;'>";
    echo "<h3>❌ Download Failed</h3>";
    echo "<p>Some files could not be downloaded. Check your internet connection.</p>";
    echo "</div>";
}

echo "<br><a href='setup_phpmailer_email.php' style='background: #007bff; color: white; padding: 10px 20px; text-decoration: none; border-radius: 5px;'>➡️ Continue to Email Setup</a>";
?>