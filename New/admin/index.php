<?php
// TR Portfolio Admin Login (Updated with Password Reset)
session_start();
require_once '../Config/config.php';

// Redirect if already logged in
if (isset($_SESSION['admin_id'])) {
    header('Location: dashboard.php');
    exit;
}

$error = '';
$success = '';

// Handle login form submission
if ($_POST) {
    $username = trim($_POST['username'] ?? '');
    $password = $_POST['password'] ?? '';
    
    if (empty($username) || empty($password)) {
        $error = 'Please enter both username and password';
    } else {
        try {
            $stmt = $pdo->prepare("SELECT id, username, password_hash FROM admin_users WHERE username = ?");
            $stmt->execute([$username]);
            $user = $stmt->fetch();
            
            if ($user && password_verify($password, $user['password_hash'])) {
                $_SESSION['admin_id'] = $user['id'];
                $_SESSION['admin_username'] = $user['username'];
                $_SESSION['login_time'] = time();
                
                // Update last login
                $updateStmt = $pdo->prepare("UPDATE admin_users SET last_login = NOW() WHERE id = ?");
                $updateStmt->execute([$user['id']]);
                
                header('Location: dashboard.php');
                exit;
            } else {
                $error = 'Invalid username or password';
            }
        } catch (PDOException $e) {
            $error = 'Database error occurred';
        }
    }
}

// Check for logout message
if (isset($_GET['logout'])) {
    $success = 'You have been logged out successfully';
}

// Check for session timeout
if (isset($_GET['timeout'])) {
    $error = 'Your session has expired. Please login again.';
}

// Check for password reset success
if (isset($_GET['reset_success'])) {
    $success = 'Password reset successfully! You can now login with your new password.';
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>TR Portfolio - Admin Login</title>
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
        
        .forgot-password-link {
            color: #667eea;
            text-decoration: none;
            font-size: 0.9rem;
            transition: color 0.3s ease;
        }
        
        .forgot-password-link:hover {
            color: #5a6fd8;
            text-decoration: underline;
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
                            <h2 class="logo fw-bold mb-2">TR Admin</h2>
                            <p class="text-muted">Portfolio Management System</p>
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
                        <?php endif; ?>
                        
                        <form method="POST" autocomplete="off">
                            <div class="mb-3">
                                <label for="username" class="form-label">
                                    <i class="fas fa-user me-1"></i> Username
                                </label>
                                <input 
                                    type="text" 
                                    class="form-control" 
                                    id="username" 
                                    name="username" 
                                    required 
                                    autocomplete="username"
                                    value="<?= htmlspecialchars($_POST['username'] ?? '') ?>"
                                >
                            </div>
                            
                            <div class="mb-3">
                                <label for="password" class="form-label">
                                    <i class="fas fa-lock me-1"></i> Password
                                </label>
                                <input 
                                    type="password" 
                                    class="form-control" 
                                    id="password" 
                                    name="password" 
                                    required 
                                    autocomplete="current-password"
                                >
                            </div>
                            
                            <div class="text-end mb-3">
                                <a href="forgot_password.php" class="forgot-password-link">
                                    <i class="fas fa-key me-1"></i> Forgot Password?
                                </a>
                            </div>
                            
                            <button type="submit" class="btn btn-gradient text-white w-100 py-2">
                                <i class="fas fa-sign-in-alt me-2"></i> Login to Dashboard
                            </button>
                        </form>
                        
                        <hr class="my-4">
                        
                        <div class="text-center">
                            <a href="../" class="text-decoration-none text-muted">
                                <i class="fas fa-arrow-left me-1"></i> Back to Portfolio
                            </a>
                        </div>
                        
                        <div class="text-center mt-3">
                            <small class="text-muted">
                                Default credentials: <strong>admin</strong> / <strong>password</strong>
                                <br>
                                <span class="text-warning">
                                    <i class="fas fa-exclamation-triangle"></i> 
                                    Please change after first login!
                                </span>
                            </small>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    
    <script>
        // Auto-focus username field
        document.getElementById('username').focus();
        
        // Show/hide password toggle
        const passwordField = document.getElementById('password');
        const toggleButton = document.createElement('button');
        toggleButton.type = 'button';
        toggleButton.className = 'btn btn-outline-secondary position-absolute top-50 end-0 translate-middle-y me-2';
        toggleButton.innerHTML = '<i class="fas fa-eye"></i>';
        toggleButton.style.border = 'none';
        toggleButton.style.background = 'none';
        
        // Make password field container relative
        passwordField.parentElement.style.position = 'relative';
        
        toggleButton.addEventListener('click', function() {
            if (passwordField.type === 'password') {
                passwordField.type = 'text';
                toggleButton.innerHTML = '<i class="fas fa-eye-slash"></i>';
            } else {
                passwordField.type = 'password';
                toggleButton.innerHTML = '<i class="fas fa-eye"></i>';
            }
        });
        
        passwordField.parentElement.appendChild(toggleButton);
        
        // Auto-hide alerts after 5 seconds
        setTimeout(function() {
            const alerts = document.querySelectorAll('.alert');
            alerts.forEach(alert => {
                const bsAlert = new bootstrap.Alert(alert);
                bsAlert.close();
            });
        }, 5000);
    </script>
</body>
</html>