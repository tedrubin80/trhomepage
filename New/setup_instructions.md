# TR Portfolio System - Setup Instructions

## 📁 File Structure
Create this directory structure on your web server:

```
tr-portfolio/
├── index.php                 (Main portfolio site)
├── Config/
│   └── config.php           (Database configuration)
├── admin/
│   ├── index.php            (Admin login)
│   ├── dashboard.php        (Admin dashboard)
│   └── logout.php           (Logout script)
└── setup.sql                (Database schema)
```

## 🚀 Quick Setup (5 minutes)

### Step 1: Database Setup
1. Create a MySQL database named `tr_portfolio`
2. Import the `setup.sql` file into your database
3. Note your database credentials (host, username, password)

### Step 2: Configure Your Site
1. Edit `Config/config.php` and update:
   - Database credentials (DB_HOST, DB_USER, DB_PASS)
   - Your external links (LinkedIn, Linktree, Resume URLs)

### Step 3: Upload Files
1. Upload all files to your web server
2. Ensure proper permissions (755 for directories, 644 for files)

### Step 4: Test Your Site
1. Visit your site: `https://yourdomain.com/`
2. Access admin panel: `https://yourdomain.com/admin/`
3. Default login: **username:** `admin` **password:** `password`

### Step 5: Secure Your Admin
1. **IMPORTANT:** Change the default admin password immediately!
2. Consider adding additional admin users if needed

## 🔗 External Links Setup

In `Config/config.php`, update these constants with your actual URLs:

```php
define('LINKEDIN_URL', 'https://linkedin.com/in/your-profile');
define('LINKTREE_URL', 'https://linktr.ee/your-username');  
define('RESUME_URL', '/path/to/your-resume.pdf');
```

## 🎯 Features Included

### Public Site Features:
- ✅ Responsive Bootstrap design with purple gradient theme
- ✅ Navigation with Projects and Portfolio pages
- ✅ External links to LinkedIn, Linktree, and Resume
- ✅ Dynamic content pulled from database
- ✅ Professional project cards with tags and descriptions
- ✅ Portfolio sections for Websites, Digital, and Films

### Admin Panel Features:
- ✅ Secure authentication with session management
- ✅ Dashboard overview with statistics
- ✅ Project management (max 10 projects)
- ✅ Portfolio management (max 10 per category)
- ✅ Active/inactive status controls
- ✅ Display order management
- ✅ Tag system for categorization

## 🛡️ Security Features

- Password hashing with PHP's `password_hash()`
- SQL injection protection with prepared statements
- Session timeout (1 hour)
- Input sanitization and validation
- CSRF protection through form tokens

## 📊 Content Limits

- **Projects:** Maximum 10 total
- **Portfolio Items:** Maximum 10 per category (30 total)
- **Tags:** Unlimited, stored as JSON
- **Images:** URLs only (no file upload)

## 🎨 Customization

### Changing Colors
The gradient theme uses these CSS variables:
```css
/* Primary gradient */
background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
```

### Adding New Portfolio Categories
Edit the database enum in `portfolio_items` table:
```sql
ALTER TABLE portfolio_items 
MODIFY COLUMN category ENUM('websites', 'digital', 'films', 'new_category');
```

## 🔧 Troubleshooting

### Database Connection Issues
- Check your database credentials in `Config/config.php`
- Ensure MySQL server is running
- Verify database name exists

### Admin Login Problems
- Default credentials: `admin` / `password`
- Check if `admin_users` table exists
- Verify session support is enabled in PHP

### File Permissions
- Directories: `chmod 755`
- PHP files: `chmod 644`
- Ensure web server can read files

## 📞 Support

### Default Admin Credentials
- **Username:** `admin`
- **Password:** `password`
- **⚠️ Change these immediately after first login!**

### Database Schema
The system automatically creates sample data including:
- 6 sample projects with different icons and tags
- 3 sample portfolio items (one per category)
- Default admin user

### Version Info
- **PHP:** Requires 7.4 or higher
- **MySQL:** Requires 5.7 or higher  
- **Bootstrap:** 5.3.0 (loaded via CDN)
- **Font Awesome:** 6.4.0 (loaded via CDN)

## 🎉 You're Ready!

Your professional portfolio system is now ready to use. Start by:

1. Logging into the admin panel
2. Changing the default password
3. Adding your real projects and portfolio items
4. Updating your external links

The system is designed to be simple, secure, and professional. Enjoy managing your portfolio!