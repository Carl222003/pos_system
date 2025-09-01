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
    $primaryColor = $brand['primaryColor'] ?? '#8B4513';
    $accentColor  = $brand['accentColor'] ?? '#A0522D';
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
        body { margin: 0; padding: 0; background: #f4f4f4; font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, Oxygen, Ubuntu, Cantarell, 'Helvetica Neue', Arial, sans-serif; color: #2d3436; }
        .container { width: 100%; max-width: 640px; margin: 20px auto; background: #ffffff; border-radius: 14px; overflow: hidden; box-shadow: 0 8px 28px rgba(0,0,0,0.08); }
        .header { background: linear-gradient(135deg, {$primaryColor}, {$accentColor}); color: #fff; padding: 28px 28px; text-align: left; }

        .header h1 { margin: 0 0 6px 0; font-size: 24px; line-height: 1.2; }
        .header p { margin: 0; opacity: .9; font-size: 14px; }
        .content { padding: 32px 28px; }
        .greeting { font-size: 16px; margin: 0 0 14px 0; color: #34495e; }
        .intro { margin: 0 0 16px 0; color: #4d4d4d; }

        .code-wrap { background: #f8f9fa; border: 2px dashed {$primaryColor}; border-radius: 12px; padding: 20px; text-align: center; margin: 22px 0; }
        .code-label { font-size: 13px; color: #636e72; margin-bottom: 8px; }
        .code { font-family: 'SFMono-Regular', Menlo, Consolas, 'Liberation Mono', monospace; font-weight: 700; font-size: 34px; letter-spacing: 8px; color: {$primaryColor}; }

        .info { background: #fff7e6; border-left: 4px solid #f0a500; padding: 14px 16px; border-radius: 8px; margin: 18px 0; color: #6b5e00; }
        .steps { margin: 20px 0; padding-left: 18px; }
        .steps li { margin: 8px 0; }

        .footer { background: #f7f7f7; padding: 18px 22px; text-align: center; color: #6c757d; font-size: 12px; }
        .divider { height: 1px; background: #ececec; margin: 26px 0; border: none; }

        /* Mobile */
        @media (max-width: 480px) {
          .code { font-size: 28px; letter-spacing: 6px; }
          .content { padding: 24px 18px; }
          .header { padding: 22px 18px; }
        }
      </style>
    </head>
    <body>
      <div class='container'>
                <div class='header'>
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


