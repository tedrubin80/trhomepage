# Password Reset Implementation Guide

## 📋 Overview
This guide helps you implement a secure password reset system for your TR Portfolio admin panel.

## 🗄️ Database Setup

### Step 1: Run the Database Migration
Execute the following SQL commands to add password reset functionality:

```sql
-- Add password reset fields to admin_users table
ALTER TABLE admin_users ADD COLUMN email VARCHAR(255) DEFAULT NULL;
ALTER TABLE admin_users ADD COLUMN reset_token VARCHAR(255) DEFAULT NULL;
ALTER TABLE admin_users ADD COLUMN reset_token_expires DATETIME DEFAULT NULL;
ALTER TABLE admin_users ADD COLUMN reset_attempts INT DEFAULT 0;
ALTER TABLE admin_users ADD COLUMN last_reset_attempt DATETIME DEFAULT NULL;

-- Create unique index for email
CREATE UNIQUE INDEX idx_admin_email ON admin_users(email);

-- Update existing admin user with email (replace with actual email)
UPDATE admin_users SET email = 'admin@yoursite.com' WHERE username = 'admin';
```

## 📁 File Structure

Create/update these files in your admin directory:

```
admin/
├── index.php (updated with reset link)
├── forgot_password.php (new)
├── reset_password.php (new)
├── email_config.php (new)
└── admin_dashboard.php (existing)
```

## ⚙️ Configuration

### Step 1: Email Configuration
Add to your `config.php` or create `email_config.php`:

```php
// Email Settings
define('SMTP_HOST', 'smtp.gmail.com');
define('SMTP_PORT', 587);
define('SMTP_USERNAME', 'your-email@gmail.com');
define('SMTP_PASSWORD', 'your-app-password');
define('FROM_EMAIL', 'noreply@yoursite.com');
define('SITE_URL', 'https://yoursite.com');
```

### Step 2: Gmail Setup (if using Gmail)
1. Enable 2-Factor Authentication
2. Generate an "App Password":
   - Go to Google Account settings
   - Security → 2-Step Verification → App passwords
   - Generate password for "Mail"
   - Use this password in `SMTP_PASSWORD`

## 🔐 Security Features

### Built-in Security Measures:
- **Rate Limiting**: Max 3 reset attempts per hour
- **Token Expiration**: Reset links expire after 1 hour
- **Secure Tokens**: 64-character random tokens
- **Password Strength**: Enforced complex passwords
- **No User Enumeration**: Same response for valid/invalid emails
- **Single Use Tokens**: Tokens are cleared after use

### Password Requirements:
- Minimum 8 characters
- At least one uppercase letter
- At least one lowercase letter
- At least one number
- At least one special character (@$!%*?&)

## 📧 Email Options

### Option 1: Basic PHP mail() (Simple)
Uses built-in PHP `mail()` function. Works for basic setups but may have deliverability issues.

### Option 2: PHPMailer (Recommended)
For better email delivery and SMTP authentication:

```bash
composer require phpmailer/phpmailer
```

## 🧪 Testing

### Test Email Configuration:
```php
<?php
require_once 'email_config.php';
$errors = testEmailConfiguration();
if (empty($errors)) {
    echo "✅ Email configuration is valid!";
} else {
    echo "❌ Configuration errors: " . implode(", ", $errors);
}
?>
```

### Test Password Reset Flow:
1. Navigate to `/admin/forgot_password.php`
2. Enter your email address
3. Check email for reset link
4. Click link and set new password
5. Login with new credentials

## 🚀 Deployment Checklist

- [ ] Database schema updated
- [ ] All files uploaded to server
- [ ] Email configuration set
- [ ] SMTP credentials configured
- [ ] Test email delivery
- [ ] Test complete reset flow
- [ ] Update admin user email
- [ ] Remove default credentials warning

## 🛠️ Advanced Configuration

### Custom Email Templates
Modify the email template in `email_config.php` for branded emails with HTML styling.

### Rate Limiting Adjustment
Change rate limiting in `forgot_password.php`:
```php
// Current: 3 attempts per hour
if ($user['reset_attempts'] >= 3) {
    // Increase to 5 attempts per hour
    if ($user['reset_attempts'] >= 5) {
```

### Token Expiry Adjustment
Change token expiry in `forgot_password.php`:
```php
// Current: 1 hour
$expires->add(new DateInterval('PT1H'));
// Change to 30 minutes
$expires->add(new DateInterval('PT30M'));
```

## 🔧 Troubleshooting

### Common Issues:

**Email not sending:**
- Check SMTP credentials
- Verify PHP `mail()` function is enabled
- Check server mail logs
- Test with PHPMailer instead

**Database errors:**
- Ensure all schema changes are applied
- Check database user permissions
- Verify PDO connection

**Reset link not working:**
- Check token in database
- Verify token hasn't expired
- Check URL formatting

**Password validation failing:**
- Review password requirements
- Check JavaScript validation
- Verify regex patterns

## 📞 Support

For additional help:
1. Check server error logs
2. Enable PHP error reporting during testing
3. Test email delivery separately
4. Verify database schema changes

## 🔒 Production Security Notes

1. **Use HTTPS**: Always use SSL/TLS in production
2. **Secure Headers**: Add security headers to prevent XSS
3. **Log Monitoring**: Monitor reset attempts for abuse
4. **Email Rate Limiting**: Implement server-level email rate limiting
5. **Database Backups**: Regular backups before schema changes

---

*This implementation provides enterprise-level security for password resets while maintaining user-friendly functionality.*