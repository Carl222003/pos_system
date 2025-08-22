<?php
// Simple sender email setup page
// Allows configuring Gmail + App Password into email_config.php and testing a send

// Load current config values if present
$configPath = __DIR__ . '/email_config.php';
$currentEmail = '';
$currentEnabled = false;

if (file_exists($configPath)) {
    // Read constants without fatal if already defined elsewhere
    include_once $configPath;
    if (defined('SMTP_USERNAME')) {
        $currentEmail = SMTP_USERNAME;
    }
    if (defined('ENABLE_REAL_EMAIL')) {
        $currentEnabled = ENABLE_REAL_EMAIL;
    }
}

$message = '';
$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['save_sender'])) {
    $email = trim($_POST['sender_email'] ?? '');
    $appPassword = trim($_POST['app_password'] ?? '');
    $enableReal = isset($_POST['enable_real']) ? 'true' : 'false';

    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $error = 'Please enter a valid Gmail address.';
    } elseif (strlen(str_replace(' ', '', $appPassword)) < 16) {
        $error = 'Please paste the 16-character Gmail App Password (no spaces).';
    } else {
        // Normalize app password (remove spaces)
        $appPassword = str_replace(' ', '', $appPassword);

        // Update email_config.php
        $contents = file_exists($configPath) ? file_get_contents($configPath) : '';
        if ($contents === '' || strpos($contents, 'define(') === false) {
            // Create a basic config if missing
            $contents = "<?php\n" .
                        "define('ENABLE_REAL_EMAIL', {$enableReal});\n" .
                        "define('SMTP_HOST', 'smtp.gmail.com');\n" .
                        "define('SMTP_PORT', 587);\n" .
                        "define('SMTP_USERNAME', '" . addslashes($email) . "');\n" .
                        "define('SMTP_PASSWORD', '" . addslashes($appPassword) . "');\n" .
                        "define('SMTP_ENCRYPTION', 'tls');\n" .
                        "?>\n";
        } else {
            // Replace ENABLE_REAL_EMAIL
            $contents = preg_replace(
                "/define\('ENABLE_REAL_EMAIL',\s*(true|false)\);/i",
                "define('ENABLE_REAL_EMAIL', {$enableReal});",
                $contents
            );
            // Replace SMTP_USERNAME
            $contents = preg_replace(
                "/define\('SMTP_USERNAME',\s*'[^']*'\);/i",
                "define('SMTP_USERNAME', '" . addslashes($email) . "');",
                $contents
            );
            // Replace SMTP_PASSWORD
            $contents = preg_replace(
                "/define\('SMTP_PASSWORD',\s*'[^']*'\);/i",
                "define('SMTP_PASSWORD', '" . addslashes($appPassword) . "');",
                $contents
            );
            // Ensure host/port/encryption exist
            if (strpos($contents, "SMTP_HOST") === false) {
                $contents .= "\ndefine('SMTP_HOST', 'smtp.gmail.com');";
            }
            if (strpos($contents, "SMTP_PORT") === false) {
                $contents .= "\ndefine('SMTP_PORT', 587);";
            }
            if (strpos($contents, "SMTP_ENCRYPTION") === false) {
                $contents .= "\ndefine('SMTP_ENCRYPTION', 'tls');";
            }
            if (substr(trim($contents), -2) !== '?>') {
                $contents .= "\n?>";
            }
        }

        if (file_put_contents($configPath, $contents) !== false) {
            $message = 'Sender account saved successfully.';
            $currentEmail = $email;
            $currentEnabled = ($enableReal === 'true');
        } else {
            $error = 'Failed to write configuration file.';
        }
    }
}

// Optional inline test using PHPMailer helper
$testResult = null;
if (isset($_POST['test_send']) && !empty($_POST['test_email'])) {
    require_once __DIR__ . '/phpmailer_email.php';
    @require_once __DIR__ . '/email_template.php';
    $code = str_pad((string)random_int(0, 999999), 6, '0', STR_PAD_LEFT);
    $testResult = sendVerificationEmailPHPMailer(trim($_POST['test_email']), $code, 'Test User');
}

?><!DOCTYPE html>
<html>
<head>
  <meta charset="UTF-8" />
  <title>Setup Sender Email</title>
  <style>
    body { font-family: Arial, sans-serif; background: #f5f7fb; margin: 0; }
    .wrap { max-width: 860px; margin: 32px auto; background: #fff; border-radius: 12px; box-shadow: 0 8px 24px rgba(0,0,0,.08); overflow: hidden; }
    .header { background: linear-gradient(135deg, #8B4513, #A0522D); color: #fff; padding: 22px 26px; }
    .container { padding: 24px 26px; }
    .row { margin-bottom: 14px; }
    label { display: block; font-weight: 600; margin-bottom: 6px; color: #34495e; }
    input[type=text], input[type=email], input[type=password] { width: 100%; padding: 10px 12px; border: 1px solid #dcdfe6; border-radius: 8px; font-size: 14px; }
    .hint { color: #768390; font-size: 12px; margin-top: 4px; }
    .actions { margin-top: 18px; display: flex; gap: 10px; flex-wrap: wrap; }
    .btn { background: #8B4513; color: #fff; border: 0; padding: 10px 16px; border-radius: 8px; cursor: pointer; }
    .btn.secondary { background: #64748b; }
    .alert { padding: 12px 14px; border-radius: 8px; margin: 10px 0; }
    .alert.success { background: #e8f5e9; color: #256029; }
    .alert.error { background: #fdecea; color: #b71c1c; }
    .grid { display: grid; grid-template-columns: 1fr 1fr; gap: 16px; }
    @media (max-width: 700px) { .grid { grid-template-columns: 1fr; } }
    .footer { padding: 16px 26px; background: #f7f7f7; color: #6c757d; font-size: 12px; }
  </style>
  <script>
    function togglePwd(el){ el.type = el.type === 'password' ? 'text' : 'password'; }
  </script>
</head>
<body>
  <div class="wrap">
    <div class="header">
      <h2>Configure Sender Email (Gmail)</h2>
      <p>Use a Gmail App Password to send verification codes to new Cashiers and Stockmen</p>
    </div>

    <div class="container">
      <?php if ($message): ?><div class="alert success"><?php echo htmlspecialchars($message); ?></div><?php endif; ?>
      <?php if ($error): ?><div class="alert error"><?php echo htmlspecialchars($error); ?></div><?php endif; ?>

      <form method="post" class="grid">
        <div class="row">
          <label for="sender_email">Gmail Address</label>
          <input type="email" id="sender_email" name="sender_email" placeholder="you@gmail.com" value="<?php echo htmlspecialchars($currentEmail); ?>" required />
          <div class="hint">Example: carlmadelo22@gmail.com</div>
        </div>
        <div class="row">
          <label for="app_password">Gmail App Password</label>
          <input type="password" id="app_password" name="app_password" placeholder="16-character app password" required />
          <div class="hint">Generate in Google Account → Security → 2‑Step Verification → App passwords → Mail</div>
        </div>
        <div class="row">
          <label><input type="checkbox" name="enable_real" <?php echo $currentEnabled ? 'checked' : ''; ?> /> Enable real email for fallbacks</label>
        </div>
        <div class="actions">
          <button class="btn" name="save_sender">Save Sender Account</button>
          <a class="btn secondary" href="setup_phpmailer_email.php">Open PHPMailer Test</a>
        </div>
      </form>

      <hr />

      <h3>Quick Test Send</h3>
      <form method="post" class="grid">
        <div class="row">
          <label for="test_email">Send a test verification to</label>
          <input type="email" id="test_email" name="test_email" placeholder="recipient@example.com" required />
          <div class="hint">We will send a one-time test code to this address using PHPMailer</div>
        </div>
        <div class="actions">
          <button class="btn" name="test_send">Send Test Email</button>
        </div>
      </form>

      <?php if ($testResult !== null): ?>
        <div class="alert <?php echo $testResult['success'] ? 'success' : 'error'; ?>" style="margin-top:12px;">
          <strong><?php echo $testResult['success'] ? 'Success' : 'Failed'; ?>:</strong>
          <?php echo htmlspecialchars($testResult['message'] ?? ''); ?>
          <?php if (!$testResult['success'] && !empty($testResult['error'])): ?>
            <div class="hint">Error: <?php echo htmlspecialchars($testResult['error']); ?></div>
          <?php endif; ?>
        </div>
      <?php endif; ?>

    </div>
    <div class="footer">
      Tip: If authentication fails, double‑check the Gmail App Password (no spaces) and that 2‑Step Verification is enabled.
    </div>
  </div>
</body>
</html>


