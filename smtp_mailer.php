<?php
/**
 * Simple SMTP Mailer Class
 * A lightweight SMTP email sender without external dependencies
 */
class SMTPMailer {
    private $smtp_host;
    private $smtp_port;
    private $smtp_username;
    private $smtp_password;
    private $smtp_secure;
    
    public function __construct($host, $port, $username, $password, $secure = 'tls') {
        $this->smtp_host = $host;
        $this->smtp_port = $port;
        $this->smtp_username = $username;
        $this->smtp_password = $password;
        $this->smtp_secure = $secure;
    }
    
    public function sendMail($to, $subject, $body, $from_name = 'MoreBites POS System') {
        try {
            // Create connection
            $socket = fsockopen($this->smtp_host, $this->smtp_port, $errno, $errstr, 30);
            if (!$socket) {
                throw new Exception("Could not connect to SMTP server: $errstr ($errno)");
            }
            
            // Read initial response
            $this->getResponse($socket);
            
            // Send EHLO
            fputs($socket, "EHLO " . $_SERVER['HTTP_HOST'] . "\r\n");
            $this->getResponse($socket);
            
            // Start TLS
            if ($this->smtp_secure == 'tls') {
                fputs($socket, "STARTTLS\r\n");
                $this->getResponse($socket);
                stream_socket_enable_crypto($socket, true, STREAM_CRYPTO_METHOD_TLS_CLIENT);
                
                // Send EHLO again after TLS
                fputs($socket, "EHLO " . $_SERVER['HTTP_HOST'] . "\r\n");
                $this->getResponse($socket);
            }
            
            // Authenticate
            fputs($socket, "AUTH LOGIN\r\n");
            $this->getResponse($socket);
            
            fputs($socket, base64_encode($this->smtp_username) . "\r\n");
            $this->getResponse($socket);
            
            fputs($socket, base64_encode($this->smtp_password) . "\r\n");
            $this->getResponse($socket);
            
            // Send FROM
            fputs($socket, "MAIL FROM: <{$this->smtp_username}>\r\n");
            $this->getResponse($socket);
            
            // Send TO
            fputs($socket, "RCPT TO: <$to>\r\n");
            $this->getResponse($socket);
            
            // Send DATA
            fputs($socket, "DATA\r\n");
            $this->getResponse($socket);
            
            // Send email headers and body
            $headers = "From: $from_name <{$this->smtp_username}>\r\n";
            $headers .= "To: $to\r\n";
            $headers .= "Subject: $subject\r\n";
            $headers .= "MIME-Version: 1.0\r\n";
            $headers .= "Content-Type: text/html; charset=UTF-8\r\n";
            $headers .= "Date: " . date('r') . "\r\n";
            $headers .= "\r\n";
            
            fputs($socket, $headers . $body . "\r\n.\r\n");
            $this->getResponse($socket);
            
            // Send QUIT
            fputs($socket, "QUIT\r\n");
            $this->getResponse($socket);
            
            fclose($socket);
            return true;
            
        } catch (Exception $e) {
            error_log("SMTP Error: " . $e->getMessage());
            return false;
        }
    }
    
    private function getResponse($socket) {
        $response = '';
        while (($line = fgets($socket, 515)) !== false) {
            $response .= $line;
            if (substr($line, 3, 1) == ' ') {
                break;
            }
        }
        return $response;
    }
}

/**
 * Gmail SMTP Configuration
 * Update these with your Gmail credentials
 */
function sendGmailSMTP($to, $subject, $body) {
    // Gmail SMTP settings
    $gmail_username = 'your-email@gmail.com'; // Replace with your Gmail
    $gmail_password = 'your-app-password';    // Replace with your Gmail App Password
    
    $mailer = new SMTPMailer('smtp.gmail.com', 587, $gmail_username, $gmail_password, 'tls');
    return $mailer->sendMail($to, $subject, $body);
}

/**
 * Alternative: Use local mail server if available
 */
function sendLocalMail($to, $subject, $body) {
    $headers = "MIME-Version: 1.0\r\n";
    $headers .= "Content-type: text/html; charset=UTF-8\r\n";
    $headers .= "From: MoreBites POS System <noreply@morebites.com>\r\n";
    $headers .= "Reply-To: noreply@morebites.com\r\n";
    
    return mail($to, $subject, $body, $headers);
}

/**
 * Try multiple email sending methods
 */
function forceSendEmail($to, $subject, $body) {
    $methods = [
        'Gmail SMTP' => 'sendGmailSMTP',
        'Local Mail' => 'sendLocalMail'
    ];
    
    $errors = [];
    
    foreach ($methods as $method_name => $function) {
        try {
            error_log("Attempting to send email via: $method_name");
            $result = $function($to, $subject, $body);
            
            if ($result) {
                error_log("✅ Email sent successfully via: $method_name");
                return [
                    'success' => true,
                    'method' => $method_name,
                    'message' => "Email sent successfully via $method_name"
                ];
            } else {
                $errors[] = "$method_name: Failed to send";
                error_log("❌ Failed to send via: $method_name");
            }
        } catch (Exception $e) {
            $errors[] = "$method_name: " . $e->getMessage();
            error_log("❌ Error with $method_name: " . $e->getMessage());
        }
    }
    
    // If all methods fail, return error
    return [
        'success' => false,
        'errors' => $errors,
        'message' => 'All email sending methods failed: ' . implode(', ', $errors)
    ];
}
?>