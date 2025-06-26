<?php
// TR Portfolio - Forgot Password
session_start();
require_once '../Config/config.php';

// Redirect if already logged in
if (isset($_SESSION['admin_id'])) {
    header('Location: dashboard.php');
    exit;
}

$error = '';
$success = '';

// Handle form submission
if ($_POST) {
    $email = trim($_POST['email'] ?? '');
    
    if (empty($email)) {
        $error = 'Please enter your email address';
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $error = 'Please enter a valid email address';
    } else {
        try {
            // Check if email exists and rate limiting
            $stmt = $pdo->prepare("SELECT id, username, email, reset_attempts, last_reset_attempt FROM admin_users WHERE email = ?");
            $stmt->execute([$email]);
            $user = $stmt->fetch();
            
            if ($user) {
                // Check rate limiting (max 3 attempts per hour)
                $now = new DateTime();
                $lastAttempt = $user['last_reset_attempt'] ? new DateTime($user['last_reset_attempt']) : null;
                
                if ($lastAttempt && $user['reset_attempts'] >= 3) {
                    $hourAgo = new DateTime();
                    $hourAgo->sub(new DateInterval('PT1H'));
                    
                    if ($lastAttempt > $hourAgo) {
                        $error = 'Too many reset attempts. Please try again in 1 hour.';
                    } else {
                        // Reset attempts counter after 1 hour
                        $stmt = $pdo->prepare("UPDATE admin_users SET reset_attempts = 0 WHERE id = ?");
                        $stmt->execute([$user['id']]);
                        $user['reset_attempts'] = 0;
                    }
                }
                
                if (empty($error)) {
                    // Generate secure reset token
                    $token = bin2hex(random_bytes(32));
                    $expires = new DateTime();
                    $expires->add(new DateInterval('PT1H')); // 1 hour expiry
                    
                    // Update database with token
                    $stmt = $pdo->prepare("UPDATE admin_users SET reset_token = ?, reset_token_expires = ?, reset_attempts = reset_attempts + 1, last_reset_attempt = NOW() WHERE id = ?");
                    $stmt->execute([$token, $expires->format('Y-m-d H:i:s'), $user['id']]);
                    
                    // Send email (you'll need to configure email settings)
                    $resetLink = "http://" . $_SERVER['HTTP_HOST'] . dirname($_SERVER['REQUEST_URI']) . "/reset_password.php?token=" . $token;
                    
                    $subject = "TR Portfolio - Password Reset Request";
                    $message = "Hi " . htmlspecialchars($user['username']) . ",\n\n";
                    $message .= "You have requested a password reset for your TR Portfolio admin account.\n\n";
                    $message .= "Click the following link to reset your password:\n";
                    $message .= $resetLink . "\n\n";
                    $message .= "This link will expire in 1 hour.\n\n";
                    $message .= "If you did not request this reset, please ignore this email.\n\n";
                    $message .= "Best regards,\nTR Portfolio System";
                    
                    $headers = "From: noreply@yoursite.com\r\n";
                    $headers .= "Reply-To: noreply@yoursite.com\r\n";
                    $headers .= "X-Mailer: PHP/" . phpversion();
                    
                    // Send email (configure your mail settings)
                    if (mail($email, $subject, $message, $headers)) {
                        $success = 'Password reset instructions have been sent to your email address.';
                    } else {
                        $error = 'Failed to send email. Please contact administrator.';
                    }
                }
            } else {
                // Don't reveal if email exists or not for security
                $success = 'If an account with that email exists, password reset instructions have been sent.';
            }
        } catch (PDOException $e) {
            $error = 'Database error occurred. Please try again later.';
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>TR Portfolio - Forgot Password</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">
    <style>
        body { 
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); 
            min-height: 100vh; 
            font-family: 'Inter', -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif;
        }
        
        .login-card { 
            background: rgba(255,255,255,0.95); 
            backdrop-filter: blur(20px); 
            border: 1px solid rgba(255, 255, 255, 0.2);
            box-shadow: 0 20px 40px rgba(0, 0, 0, 0.1);
        }
        
        .logo { 
            background: linear-gradient(135deg, #667eea, #764ba2); 
            -webkit-background-clip: text; 
            -webkit-text-fill-color: transparent; 
            background-clip: text;
        }
        
        .btn-gradient {
            background: linear-gradient(135deg, #667eea, #764ba2);
            border: none;
            transition: transform 0.3s ease;
        }
        
        .btn-gradient:hover {
            transform: translateY(-2px);
            background: linear-gradient(135deg, #5a6fd8, #6a419a);
        }
        
        .form-control:focus {
            border-color: #667eea;
            box-shadow: 0 0 0 0.2rem rgba(102, 126, 234, 0.25);
        }
        
        .alert {
            border: none;
            border-radius: 10px;
        }
    </style>
</head>
<body class="d-flex align-items-center">
    <div class="container">
        <div class="row justify-content-center">
            <div class="col-md-4">
                <div class="card login-card border-0">
                    <div class="card-body p-5">
                        <div class="text-center mb-4">
                            <h2 class="logo fw-bold mb-2">
                                <i class="fas fa-key me-2"></i>Reset Password
                            </h2>
                            <p class="text-muted">Enter your email to receive reset instructions</p>
                        </div>
                        
                        <?php if ($error): ?>
                            <div class="alert alert-danger d-flex align-items-center">
                                <i class="fas fa-exclamation-triangle me-2"></i>
                                <?= htmlspecialchars($error) ?>
                            </div>
                        <?php endif; ?>
                        
                        <?php if ($success): ?>
                            <div class="alert alert-success d-flex align-items-center">
                                <i class="fas fa-check-circle me-2"></i>
                                <?= htmlspecialchars($success) ?>
                            </div>
                            <div class="text-center mt-3">
                                <a href="index.php" class="btn btn-outline-primary">
                                    <i class="fas fa-arrow-left me-2"></i>Back to Login
                                </a>
                            </div>
                        <?php else: ?>
                            <form method="POST" autocomplete="off">
                                <div class="mb-4">
                                    <label for="email" class="form-label">
                                        <i class="fas fa-envelope me-1"></i> Email Address
                                    </label>
                                    <input 
                                        type="email" 
                                        class="form-control" 
                                        id="email" 
                                        name="email" 
                                        required 
                                        autocomplete="email"
                                        placeholder="Enter your registered email"
                                        value="<?= htmlspecialchars($_POST['email'] ?? '') ?>"
                                    >
                                    <small class="text-muted">
                                        We'll send password reset instructions to this email
                                    </small>
                                </div>
                                
                                <button type="submit" class="btn btn-gradient text-white w-100 py-2">
                                    <i class="fas fa-paper-plane me-2"></i>Send Reset Instructions
                                </button>
                            </form>
                        <?php endif; ?>
                        
                        <hr class="my-4">
                        
                        <div class="text-center">
                            <a href="index.php" class="text-decoration-none text-muted">
                                <i class="fas fa-arrow-left me-1"></i> Back to Login
                            </a>
                        </div>
                        
                        <div class="text-center mt-3">
                            <small class="text-muted">
                                <i class="fas fa-info-circle me-1"></i>
                                Reset links expire after 1 hour for security
                            </small>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    
    <script>
        // Auto-focus email field
        document.getElementById('email')?.focus();
        
        // Auto-hide alerts after 5 seconds
        setTimeout(function() {
            const alerts = document.querySelectorAll('.alert');
            alerts.forEach(alert => {
                const bsAlert = new bootstrap.Alert(alert);
                bsAlert.close();
            });
        }, 10000); // Longer timeout for success message
    </script>
</body>
</html>