# TastyBook Installation Guide

## Quick Start

### 1. Prerequisites
- XAMPP, WAMP, or LAMP installed
- PHP 7.4 or higher
- MySQL 5.7 or higher
- Web browser

### 2. Installation Steps

#### Step 1: Download and Extract
1. Download the TastyBook project files
2. Extract to your web server directory:
   - **XAMPP**: `C:\xampp\htdocs\TastyBook\`
   - **WAMP**: `C:\wamp64\www\TastyBook\`
   - **Linux**: `/var/www/html/TastyBook/`

#### Step 2: Start Services
1. Start Apache and MySQL services
2. Open phpMyAdmin (usually at `http://localhost/phpmyadmin`)

#### Step 3: Database Setup
1. Open your browser and go to: `http://localhost/TastyBook/setup.php`
2. The setup script will automatically:
   - Create the `tastybook_db` database
   - Create all necessary tables
   - Insert sample data
   - Create an admin account

#### Step 4: Access the Application
1. Go to: `http://localhost/TastyBook/`
2. You should see the TastyBook homepage

### 3. Default Login Credentials

**Admin Account:**
- Username: `admin`
- Email: `admin@tastybook.com`
- Password: `admin123`

**Important:** Change the admin password after first login!

### 4. First Steps

1. **Login as Admin:**
   - Click "Sign Up" in the top navigation
   - Use the admin credentials above

2. **Create Your Account:**
   - Register a new user account
   - Add your first recipe

3. **Explore Features:**
   - Browse existing recipes
   - Search and filter recipes
   - Add recipes to favorites
   - Rate and review recipes

### 5. Configuration

#### Database Settings
Edit `db/config.php` if you need to change database settings:

```php
define('DB_HOST', 'localhost');
define('DB_NAME', 'tastybook_db');
define('DB_USER', 'root');
define('DB_PASS', '');
```

#### File Upload Settings
The system allows image uploads up to 5MB. To change this limit, edit `.htaccess`:

```apache
php_value upload_max_filesize 10M
php_value post_max_size 10M
```

### 6. Troubleshooting

#### Common Issues

**Database Connection Error:**
- Check if MySQL is running
- Verify credentials in `db/config.php`
- Ensure database exists

**File Upload Not Working:**
- Check `uploads/` directory permissions
- Verify PHP upload settings
- Check file size limits

**Page Not Loading:**
- Check Apache is running
- Verify file permissions
- Check error logs

#### Error Logs
- **XAMPP**: `xampp/apache/logs/error.log`
- **WAMP**: `wamp/logs/apache_error.log`
- **Linux**: `/var/log/apache2/error.log`

### 7. Security Notes

1. **Change Default Passwords:**
   - Change admin password immediately
   - Use strong passwords for all accounts

2. **File Permissions:**
   - Set proper permissions on uploads directory
   - Restrict access to sensitive files

3. **Database Security:**
   - Use strong database passwords
   - Limit database user privileges

4. **Production Deployment:**
   - Disable error reporting in production
   - Use HTTPS
   - Regular backups
   - Keep software updated

### 8. Features Overview

**User Features:**
- User registration and authentication
- Add, edit, and delete recipes
- Search and filter recipes
- Favorite recipes
- Rate and review recipes
- User dashboard

**Admin Features:**
- Manage all recipes
- View user statistics
- Handle contact messages
- System administration

### 9. Support

If you encounter any issues:

1. Check the error logs
2. Verify all requirements are met
3. Check file permissions
4. Contact support: aryanparvani12@gmail.com

### 10. Next Steps

After successful installation:

1. Customize the design in CSS files
2. Add more recipe categories
3. Configure email settings for notifications
4. Set up regular backups
5. Consider SSL certificate for production

---

**Congratulations!** You now have a fully functional recipe management system. Enjoy sharing and discovering delicious recipes with TastyBook!
