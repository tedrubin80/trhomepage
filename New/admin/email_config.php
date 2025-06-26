<?php
// Email Configuration for Password Reset
// Add this to your config.php file or create a separate email_config.php

// Email Configuration Constants
define('SMTP_HOST', 'smtp.gmail.com'); // Change to your SMTP host
define('SMTP_PORT', 587);
define('SMTP_USERNAME', 'your-email@gmail.com'); // Your email
define('SMTP_PASSWORD', 'your-app-password'); // Your email password or app password
define('SMTP_ENCRYPTION', 'tls'); // tls or ssl

define('FROM_EMAIL', 'noreply@yoursite.com');
define('FROM_NAME', 'TR Portfolio Admin');
define('SITE_URL', 'https://yoursite.com'); // Your site URL

// Enhanced email sending function using PHPMailer (recommended)
function sendPasswordResetEmail($to, $username, $token) {
    // If you want to use PHPMailer (recommended for production)
    // Uncomment the following and install PHPMailer via Composer
    /*
    require_once 'vendor/autoload.php';
    
    $mail = new PHPMailer\PHPMailer\PHPMailer(true);
    
    try {
        // Server settings
        $mail->isSMTP();
        $mail->Host       = SMTP_HOST;
        $mail->SMTPAuth   = true;
        $mail->Username   = SMTP_USERNAME;
        $mail->Password   = SMTP_PASSWORD;
        $mail->SMTPSecure = SMTP_ENCRYPTION;
        $mail->Port       = SMTP_PORT;
        
        // Recipients
        $mail->setFrom(FROM_EMAIL, FROM_NAME);
        $mail->addAddress($to, $username);
        
        // Content
        $mail->isHTML(true);
        $mail->Subject = 'TR Portfolio - Password Reset Request';
        
        $resetLink = SITE_URL . "/admin/reset_password.php?token=" . $token;
        
        $mail->Body = '
        <!DOCTYPE html>
        <html>
        <head>
            <meta charset="UTF-8">
            <meta name="viewport" content="width=device-width, initial-scale=1.0">
            <title>Password Reset</title>
            <style>
                body { font-family: Arial, sans-serif; line-height: 1.6; color: #333; }
                .container { max-width: 600px; margin: 0 auto; padding: 20px; }
                .header { background: linear-gradient(135deg, #667eea, #764ba2); color: white; padding: 20px; text-align: center; border-radius: 8px 8px 0 0; }
                .content { background: #f9f9f9; padding: 30px; border-radius: 0 0 8px 8px; }
                .button { display: inline-block; background: linear-gradient(135deg, #667eea, #764ba2); color: white; padding: 12px 30px; text-decoration: none; border-radius: 5px; margin: 20px 0; }
                .footer { text-align: center; margin-top: 20px; color: #666; font-size: 12px; }
                .warning { background: #fff3cd; border: 1px solid #ffeaa7; padding: 15px; border-radius: 5px; margin: 20px 0; }
            </style>
        </head>
        <body>
            <div class="container">
                <div class="header">
                    <h1>🔐 Password Reset Request</h1>
                </div>
                <div class="content">
                    <h2>Hello ' . htmlspecialchars($username) . ',</h2>
                    <p>You have requested a password reset for your TR Portfolio admin account.</p>
                    <p>Click the button below to reset your password:</p>
                    <a href="' . $resetLink . '" class="button">Reset Password</a>
                    <p>Or copy and paste this link into your browser:</p>
                    <p style="word-break: break-all; background: #f1f1f1; padding: 10px; border-radius: 3px;">' . $resetLink . '</p>
                    
                    <div class="warning">
                        <strong>⚠️ Important Security Information:</strong>
                        <ul>
                            <li>This link will expire in 1 hour for security reasons</li>
                            <li>If you did not request this reset, please ignore this email</li>
                            <li>Never share this link with anyone</li>
                        </ul>
                    </div>
                    
                    <p>If you continue to have problems, please contact the site administrator.</p>
                    <p>Best regards,<br>TR Portfolio System</p>
                </div>
                <div class="footer">
                    <p>This is an automated message. Please do not reply to this email.</p>
                    <p>© ' . date('Y') . ' TR Portfolio. All rights reserved.</p>
                </div>
            </div>
        </body>
        </html>';
        
        $mail->AltBody = 'Hello ' . $username . ',\n\n' .
                        'You have requested a password reset for your TR Portfolio admin account.\n\n' .
                        'Click the following link to reset your password:\n' .
                        $resetLink . '\n\n' .
                        'This link will expire in 1 hour.\n\n' .
                        'If you did not request this reset, please ignore this email.\n\n' .
                        'Best regards,\nTR Portfolio System';
        
        $mail->send();
        return true;
    } catch (Exception $e) {
        error_log("Message could not be sent. Mailer Error: {$mail->ErrorInfo}");
        return false;
    }
    */
    
    // Basic PHP mail() function (for simple setups)
    $subject = "TR Portfolio - Password Reset Request";
    $resetLink = SITE_URL . "/admin/reset_password.php?token=" . $token;
    
    $message = "Hello " . htmlspecialchars($username) . ",\n\n";
    $message .= "You have requested a password reset for your TR Portfolio admin account.\n\n";
    $message .= "Click the following link to reset your password:\n";
    $message .= $resetLink . "\n\n";
    $message .= "This link will expire in 1 hour for security reasons.\n\n";
    $message .= "If you did not request this reset, please ignore this email.\n\n";
    $message .= "SECURITY REMINDER:\n";
    $message .= "- Never share this link with anyone\n";
    $message .= "- This link can only be used once\n";
    $message .= "- Contact support if you didn't request this reset\n\n";
    $message .= "Best regards,\nTR Portfolio System\n\n";
    $message .= "---\n";
    $message .= "This is an automated message. Please do not reply to this email.";
    
    $headers = "From: " . FROM_NAME . " <" . FROM_EMAIL . ">\r\n";
    $headers .= "Reply-To: " . FROM_EMAIL . "\r\n";
    $headers .= "X-Mailer: PHP/" . phpversion() . "\r\n";
    $headers .= "MIME-Version: 1.0\r\n";
    $headers .= "Content-Type: text/plain; charset=UTF-8\r\n";
    
    return mail($to, $subject, $message, $headers);
}

// Function to validate email configuration
function testEmailConfiguration() {
    $errors = [];
    
    if (!defined('SMTP_HOST') || empty(SMTP_HOST)) {
        $errors[] = "SMTP_HOST not configured";
    }
    
    if (!defined('FROM_EMAIL') || empty(FROM_EMAIL) || !filter_var(FROM_EMAIL, FILTER_VALIDATE_EMAIL)) {
        $errors[] = "FROM_EMAIL not properly configured";
    }
    
    if (!defined('SITE_URL') || empty(SITE_URL)) {
        $errors[] = "SITE_URL not configured";
    }
    
    // Test if mail function is available
    if (!function_exists('mail')) {
        $errors[] = "PHP mail() function is not available";
    }
    
    return $errors;
}

// Installation instructions for PHPMailer (recommended)
/*
To install PHPMailer via Composer (recommended for production):

1. Run in your project directory:
   composer require phpmailer/phpmailer

2. Update the forgot_password.php file to use the sendPasswordResetEmail() function:
   
   Replace the mail() call with:
   if (sendPasswordResetEmail($email, $user['username'], $token)) {
       $success = 'Password reset instructions have been sent to your email address.';
   } else {
       $error = 'Failed to send email. Please contact administrator.';
   }

3. Configure your email settings above (SMTP_HOST, SMTP_USERNAME, etc.)

4. For Gmail, you'll need to:
   - Enable 2-factor authentication
   - Generate an "App Password" for this application
   - Use the app password instead of your regular password

5. Test the configuration by running:
   $errors = testEmailConfiguration();
   if (empty($errors)) {
       echo "Email configuration is valid!";
   } else {
       echo "Configuration errors: " . implode(", ", $errors);
   }
*/
?>