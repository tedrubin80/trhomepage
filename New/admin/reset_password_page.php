<?php
// TR Portfolio - Reset Password
session_start();
require_once '../Config/config.php';

// Redirect if already logged in
if (isset($_SESSION['admin_id'])) {
    header('Location: dashboard.php');
    exit;
}

$error = '';
$success = '';
$token = $_GET['token'] ?? '';
$validToken = false;
$user = null;

// Validate token
if (empty($token)) {
    $error = 'Invalid or missing reset token';
} else {
    try {
        $stmt = $pdo->prepare("SELECT id, username, email, reset_token_expires FROM admin_users WHERE reset_token = ?");
        $stmt->execute([$token]);
        $user = $stmt->fetch();
        
        if (!$user) {
            $error = 'Invalid reset token';
        } else {
            $now = new DateTime();
            $expires = new DateTime($user['reset_token_expires']);
            
            if ($now > $expires) {
                $error = 'Reset token has expired. Please request a new password reset.';
                // Clear expired token
                $stmt = $pdo->prepare("UPDATE admin_users SET reset_token = NULL, reset_token_expires = NULL WHERE id = ?");
                $stmt->execute([$user['id']]);
            } else {
                $validToken = true;
            }
        }
    } catch (PDOException $e) {
        $error = 'Database error occurred';
    }
}

// Handle password reset form submission
if ($_POST && $validToken) {
    $password = $_POST['password'] ?? '';
    $confirmPassword = $_POST['confirm_password'] ?? '';
    
    if (empty($password) || empty($confirmPassword)) {
        $error = 'Please fill in all fields';
    } elseif (strlen($password) < 8) {
        $error = 'Password must be at least 8 characters long';
    } elseif ($password !== $confirmPassword) {
        $error = 'Passwords do not match';
    } else {
        // Additional password strength validation
        if (!preg_match('/^(?=.*[a-z])(?=.*[A-Z])(?=.*\d)(?=.*[@$!%*?&])[A-Za-z\d@$!%*?&]/', $password)) {
            $error = 'Password must contain at least one uppercase letter, one lowercase letter, one number, and one special character';
        } else {
            try {
                // Hash the new password
                $hashedPassword = password_hash($password, PASSWORD_DEFAULT);
                
                // Update password and clear reset token
                $stmt = $pdo->prepare("UPDATE admin_users SET password_hash = ?, reset_token = NULL, reset_token_expires = NULL, reset_attempts = 0 WHERE id = ?");
                $stmt->execute([$hashedPassword, $user['id']]);
                
                // Log the password reset (optional)
                error_log("Password reset successful for user: " . $user['username'] . " (" . $user['email'] . ")");
                
                // Redirect to login with success message
                header('Location: index.php?reset_success=1');
                exit;
            } catch (PDOException $e) {
                $error = 'Failed to update password. Please try again.';
            }
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>TR Portfolio - Reset Password</title>
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
        
        .password-strength {
            font-size: 0.8rem;
            margin-top: 0.5rem;
        }
        
        .strength-weak { color: #dc3545; }
        .strength-medium { color: #ffc107; }
        .strength-strong { color: #28a745; }
        
        .password-toggle {
            cursor: pointer;
            color: #6c757d;
        }
        
        .password-toggle:hover {
            color: #495057;
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
                                <i class="fas fa-shield-alt me-2"></i>Reset Password
                            </h2>
                            <?php if ($validToken): ?>
                                <p class="text-muted">Enter your new password for <strong><?= htmlspecialchars($user['username']) ?></strong></p>
                            <?php else: ?>
                                <p class="text-muted">Password Reset</p>
                            <?php endif; ?>
                        </div>
                        
                        <?php if ($error): ?>
                            <div class="alert alert-danger d-flex align-items-center">
                                <i class="fas fa-exclamation-triangle me-2"></i>
                                <?= htmlspecialchars($error) ?>
                            </div>
                            
                            <?php if (!$validToken): ?>
                                <div class="text-center">
                                    <a href="forgot_password.php" class="btn btn-outline-primary">
                                        <i class="fas fa-redo me-2"></i>Request New Reset Link
                                    </a>
                                </div>
                            <?php endif; ?>
                        <?php endif; ?>
                        
                        <?php if ($validToken): ?>
                            <form method="POST" autocomplete="off" id="resetForm">
                                <div class="mb-3">
                                    <label for="password" class="form-label">
                                        <i class="fas fa-lock me-1"></i> New Password
                                    </label>
                                    <div class="position-relative">
                                        <input 
                                            type="password" 
                                            class="form-control" 
                                            id="password" 
                                            name="password" 
                                            required 
                                            autocomplete="new-password"
                                            minlength="8"
                                        >
                                        <i class="fas fa-eye password-toggle position-absolute top-50 end-0 translate-middle-y me-3" id="togglePassword"></i>
                                    </div>
                                    <div id="passwordStrength" class="password-strength"></div>
                                    <small class="text-muted">
                                        Must be at least 8 characters with uppercase, lowercase, number and special character
                                    </small>
                                </div>
                                
                                <div class="mb-4">
                                    <label for="confirm_password" class="form-label">
                                        <i class="fas fa-lock me-1"></i> Confirm New Password
                                    </label>
                                    <div class="position-relative">
                                        <input 
                                            type="password" 
                                            class="form-control" 
                                            id="confirm_password" 
                                            name="confirm_password" 
                                            required 
                                            autocomplete="new-password"
                                            minlength="8"
                                        >
                                        <i class="fas fa-eye password-toggle position-absolute top-50 end-0 translate-middle-y me-3" id="toggleConfirmPassword"></i>
                                    </div>
                                    <div id="passwordMatch" class="password-strength"></div>
                                </div>
                                
                                <button type="submit" class="btn btn-gradient text-white w-100 py-2" id="submitBtn">
                                    <i class="fas fa-shield-alt me-2"></i>Reset Password
                                </button>
                            </form>
                        <?php endif; ?>
                        
                        <hr class="my-4">
                        
                        <div class="text-center">
                            <a href="index.php" class="text-decoration-none text-muted">
                                <i class="fas fa-arrow-left me-1"></i> Back to Login
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const passwordField = document.getElementById('password');
            const confirmPasswordField = document.getElementById('confirm_password');
            const strengthDiv = document.getElementById('passwordStrength');
            const matchDiv = document.getElementById('passwordMatch');
            const submitBtn = document.getElementById('submitBtn');
            
            // Password toggle functionality
            function setupPasswordToggle(fieldId, toggleId) {
                const field = document.getElementById(fieldId);
                const toggle = document.getElementById(toggleId);
                
                if (field && toggle) {
                    toggle.addEventListener('click', function() {
                        if (field.type === 'password') {
                            field.type = 'text';
                            toggle.classList.remove('fa-eye');
                            toggle.classList.add('fa-eye-slash');
                        } else {
                            field.type = 'password';
                            toggle.classList.remove('fa-eye-slash');
                            toggle.classList.add('fa-eye');
                        }
                    });
                }
            }
            
            setupPasswordToggle('password', 'togglePassword');
            setupPasswordToggle('confirm_password', 'toggleConfirmPassword');
            
            // Password strength checker
            function checkPasswordStrength(password) {
                let strength = 0;
                let feedback = [];
                
                if (password.length >= 8) strength++;
                else feedback.push('at least 8 characters');
                
                if (/[a-z]/.test(password)) strength++;
                else feedback.push('lowercase letter');
                
                if (/[A-Z]/.test(password)) strength++;
                else feedback.push('uppercase letter');
                
                if (/\d/.test(password)) strength++;
                else feedback.push('number');
                
                if (/[@$!%*?&]/.test(password)) strength++;
                else feedback.push('special character');
                
                return { strength, feedback };
            }
            
            // Real-time password validation
            if (passwordField) {
                passwordField.addEventListener('input', function() {
                    const password = this.value;
                    const result = checkPasswordStrength(password);
                    
                    if (password.length === 0) {
                        strengthDiv.innerHTML = '';
                        return;
                    }
                    
                    let strengthText = '';
                    let strengthClass = '';
                    
                    if (result.strength < 3) {
                        strengthText = 'Weak';
                        strengthClass = 'strength-weak';
                    } else if (result.strength < 5) {
                        strengthText = 'Medium';
                        strengthClass = 'strength-medium';
                    } else {
                        strengthText = 'Strong';
                        strengthClass = 'strength-strong';
                    }
                    
                    strengthDiv.innerHTML = `<span class="${strengthClass}">Strength: ${strengthText}</span>`;
                    
                    if (result.feedback.length > 0) {
                        strengthDiv.innerHTML += `<br><small>Missing: ${result.feedback.join(', ')}</small>`;
                    }
                    
                    checkPasswordMatch();
                });
            }
            
            // Password match checker
            function checkPasswordMatch() {
                if (!passwordField || !confirmPasswordField || !matchDiv) return;
                
                const password = passwordField.value;
                const confirmPassword = confirmPasswordField.value;
                
                if (confirmPassword.length === 0) {
                    matchDiv.innerHTML = '';
                    return;
                }
                
                if (password === confirmPassword) {
                    matchDiv.innerHTML = '<span class="strength-strong"><i class="fas fa-check me-1"></i>Passwords match</span>';
                } else {
                    matchDiv.innerHTML = '<span class="strength-weak"><i class="fas fa-times me-1"></i>Passwords do not match</span>';
                }
            }
            
            if (confirmPasswordField) {
                confirmPasswordField.addEventListener('input', checkPasswordMatch);
            }
            
            // Auto-focus password field
            if (passwordField) {
                passwordField.focus();
            }
            
            // Form validation before submit
            const form = document.getElementById('resetForm');
            if (form) {
                form.addEventListener('submit', function(e) {
                    const password = passwordField.value;
                    const confirmPassword = confirmPasswordField.value;
                    const result = checkPasswordStrength(password);
                    
                    if (result.strength < 5) {
                        e.preventDefault();
                        alert('Please ensure your password meets all requirements.');
                        return false;
                    }
                    
                    if (password !== confirmPassword) {
                        e.preventDefault();
                        alert('Passwords do not match.');
                        return false;
                    }
                    
                    // Show loading state
                    submitBtn.innerHTML = '<i class="fas fa-spinner fa-spin me-2"></i>Resetting Password...';
                    submitBtn.disabled = true;
                });
            }
        });
    </script>
</body>
</html>