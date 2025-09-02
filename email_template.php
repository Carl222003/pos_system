<?php
/**
 * Centralized HTML email templates
 * Provides a polished, responsive layout for verification emails
 */

/**
 * Render the verification email HTML
 *
 * @param string $verificationCode 6-digit code
 * @param string $userName Recipient name
 * @param array  $brand    Optional overrides: appName, primaryColor, accentColor, footerNote
 * @return string HTML string
 */
function renderVerificationEmail(string $verificationCode, string $userName = 'User', array $brand = []): string
{
    $appName      = $brand['appName'] ?? 'MoreBites';
    $primaryColor = $brand['primaryColor'] ?? '#dc3545';  // Red
    $accentColor  = $brand['accentColor'] ?? '#28a745';  // Green
    $footerNote   = $brand['footerNote'] ?? ("This is an automated security email – please do not reply.");

    // Basic sanitize
    $safeName  = htmlspecialchars($userName, ENT_QUOTES, 'UTF-8');
    $safeCode  = htmlspecialchars($verificationCode, ENT_QUOTES, 'UTF-8');
    $safeApp   = htmlspecialchars($appName, ENT_QUOTES, 'UTF-8');

    return "
    <!DOCTYPE html>
    <html lang='en'>
    <head>
      <meta charset='UTF-8' />
      <meta name='viewport' content='width=device-width,initial-scale=1' />
      <title>Email Verification - {$safeApp}</title>
      <style>
        /* Layout */
        body { margin: 0; padding: 0; background: #f8f9fa; font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, Oxygen, Ubuntu, Cantarell, 'Helvetica Neue', Arial, sans-serif; color: #2d3436; }
        .container { width: 100%; max-width: 640px; margin: 20px auto; background: #ffffff; border-radius: 20px; overflow: hidden; box-shadow: 0 15px 40px rgba(0,0,0,0.12); border: 3px solid #dc3545; }
        .header { background: linear-gradient(135deg, #dc3545, #c82333); color: #fff; padding: 32px 28px; text-align: center; position: relative; }
        .header::before { content: ''; position: absolute; top: 0; left: 0; right: 0; height: 5px; background: linear-gradient(90deg, #28a745, #20c997); }
        .logo-container { margin-bottom: 16px; }
        .logo { width: 100px; height: 100px; border-radius: 0; background: transparent; padding: 0; box-shadow: 0 8px 25px rgba(0,0,0,0.3), 0 0 20px rgba(255,255,255,0.1); display: inline-block; filter: drop-shadow(0 4px 8px rgba(0,0,0,0.2)); transition: transform 0.3s ease, box-shadow 0.3s ease; }
        .logo:hover { transform: scale(1.05); box-shadow: 0 12px 35px rgba(0,0,0,0.4), 0 0 30px rgba(255,255,255,0.2); }

        .header h1 { margin: 0 0 8px 0; font-size: 28px; line-height: 1.2; font-weight: 700; text-shadow: 0 2px 4px rgba(0,0,0,0.3); }
        .header p { margin: 0; opacity: .95; font-size: 16px; font-weight: 300; }
        .content { padding: 36px 28px; background: linear-gradient(180deg, #ffffff 0%, #f8f9fa 100%); }
        .greeting { font-size: 18px; margin: 0 0 16px 0; color: #dc3545; font-weight: 600; text-align: center; }
        .greeting::before { content: '👋 '; margin-right: 8px; }
        .intro { margin: 0 0 20px 0; color: #495057; text-align: center; line-height: 1.6; }
        .intro::before { content: '📧 '; margin-right: 8px; }

        .code-wrap { background: linear-gradient(135deg, #f8f9fa, #e9ecef); border: 4px dashed #dc3545; border-radius: 20px; padding: 28px; text-align: center; margin: 28px 0; position: relative; }
        .code-wrap::before { content: '🔐'; font-size: 20px; position: absolute; top: -12px; left: 50%; transform: translateX(-50%); background: white; padding: 0 12px; }
        .code-label { font-size: 16px; color: #495057; margin-bottom: 16px; font-weight: 500; }
        .code-label::before { content: '🔢 '; margin-right: 8px; }
        .code { font-family: 'SFMono-Regular', Menlo, Consolas, 'Liberation Mono', monospace; font-weight: 700; font-size: 38px; letter-spacing: 10px; color: #dc3545; background: white; padding: 20px; border-radius: 15px; border: 2px solid #dc3545; text-shadow: 0 2px 4px rgba(220,53,69,0.2); }

        .info { background: linear-gradient(135deg, #d4edda, #c3e6cb); border-left: 6px solid #28a745; padding: 18px 20px; border-radius: 12px; margin: 24px 0; color: #155724; border: 1px solid #c3e6cb; }
        .info::before { content: '⚠️ '; margin-right: 8px; font-size: 18px; }
        .info strong { color: #dc3545; }
        .steps { margin: 24px 0; padding-left: 20px; }
        .steps li { margin: 10px 0; color: #155724; font-weight: 500; }
        .steps li::before { content: '✅ '; margin-right: 8px; }

        .footer { background: linear-gradient(135deg, #28a745, #20c997); padding: 24px 28px; text-align: center; color: white; position: relative; }
        .footer::before { content: ''; position: absolute; top: 0; left: 0; right: 0; height: 5px; background: linear-gradient(90deg, #dc3545, #c82333); }
        .footer::after { content: '🔒'; font-size: 20px; margin-top: 12px; display: block; opacity: 0.8; }
        .divider { height: 3px; background: linear-gradient(90deg, #dc3545, #28a745); margin: 30px 0; border: none; border-radius: 2px; }

        /* Mobile */
        @media (max-width: 480px) {
          .code { font-size: 32px; letter-spacing: 8px; }
          .content { padding: 28px 20px; }
          .header { padding: 26px 20px; }
          .header h1 { font-size: 24px; }
          .code-wrap { padding: 24px 20px; }
        }
      </style>
    </head>
    <body>
      <div class='container'>
                <div class='header'>
          <div class='logo-container'>
            <img src='cid:logo' alt='{$safeApp} Logo' class='logo' />
          </div>
          <h1>{$safeApp}</h1>
          <p>Email Verification</p>
        </div>

        <div class='content'>
          <p class='greeting'>Hello <strong>{$safeName}</strong>,</p>
          <p class='intro'>To complete your registration, please verify your email by entering the 6‑digit verification code below.</p>

          <div class='code-wrap'>
            <div class='code-label'>Your verification code</div>
            <div class='code'>{$safeCode}</div>
          </div>

          <div class='info'>
            <strong>Important:</strong> This code expires in 10 minutes. Enter it exactly as shown. If you didn’t request this, you can ignore this email.
          </div>

          <hr class='divider' />
          <ol class='steps'>
            <li>Copy the code above</li>
            <li>Return to the registration form</li>
            <li>Paste the code into the verification field</li>
            <li>Click “Verify Email” to finish</li>
          </ol>
        </div>

        <div class='footer'>
          <div>{$footerNote}</div>
          <div>&copy; " . date('Y') . " {$safeApp}. All rights reserved.</div>
        </div>
      </div>
    </body>
    </html>
    ";
}

?>


