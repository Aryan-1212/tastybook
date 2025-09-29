# TastyBook - Your Online Recipe Library

A complete recipe management system built with PHP and MySQL, featuring user authentication, recipe CRUD operations, search functionality, and user favorites.

## Features

### User Authentication
- User registration and login
- Secure password hashing
- Session management
- User profile management

### Recipe Management
- Add, edit, and delete recipes
- Image upload support
- Recipe categories and tags
- Difficulty levels and cooking times
- Ingredient lists and step-by-step instructions

### Search & Discovery
- Search recipes by title, ingredients, or description
- Filter by category and difficulty
- Sort by date, title, or rating
- Pagination support

### User Interaction
- Favorite recipes
- Rate and review recipes
- User dashboard
- Recipe statistics

### Security Features
- SQL injection prevention with prepared statements
- XSS protection
- CSRF token validation
- Input sanitization and validation
- Secure file uploads

## Requirements

- PHP 7.4 or higher
- MySQL 5.7 or higher
- Apache/Nginx web server
- XAMPP/WAMP/LAMP (for local development)

## Installation

### 1. Clone or Download
Download the project files to your web server directory (e.g., `htdocs` for XAMPP).

### 2. Database Setup
1. Start your MySQL server
2. Open your browser and navigate to `http://localhost/TastyBook/setup.php`
3. The setup script will create the database and insert sample data
4. Default admin credentials:
   - Username: `admin`
   - Email: `admin@tastybook.com`
   - Password: `admin123`

### 3. Configuration
Edit `db/config.php` if you need to change database settings:

```php
define('DB_HOST', 'localhost');
define('DB_NAME', 'tastybook_db');
define('DB_USER', 'root');
define('DB_PASS', '');
```

### 4. File Permissions
Make sure the `uploads/` directory is writable:

```bash
chmod 755 uploads/
```

## Usage

### For Users
1. **Register**: Create a new account
2. **Browse Recipes**: Search and filter recipes
3. **Add Recipes**: Share your favorite recipes
4. **Favorites**: Save recipes you love
5. **Reviews**: Rate and review recipes

### For Administrators
1. **Login**: Use admin credentials
2. **Manage Content**: Edit or delete any recipe
3. **User Management**: View user statistics
4. **Contact Messages**: Handle user inquiries

## File Structure

```
TastyBook/
├── auth/                   # Authentication pages
│   ├── login.php
│   ├── register.php
│   └── logout.php
├── recipes/               # Recipe management
│   ├── add-recipe.php
│   ├── recipe-details.php
│   └── recipes.php
├── db/                    # Database files
│   ├── config.php
│   ├── database.php
│   └── schema.sql
├── includes/              # Common includes
│   ├── functions.php
│   ├── header.php
│   └── footer.php
├── css/                   # Stylesheets
│   ├── style.css
│   ├── auth.css
│   └── dashboard.css
├── js/                    # JavaScript files
│   └── main.js
├── uploads/               # User uploaded images
├── assets/                # Static assets
├── index.php              # Homepage
├── about.php              # About page
├── contact.php            # Contact page
├── dashboard.php          # User dashboard
├── favorites.php          # User favorites
└── setup.php              # Database setup script
```

## Database Schema

### Tables
- **users**: User accounts and profiles
- **recipes**: Recipe data and metadata
- **categories**: Recipe categories
- **favorites**: User favorite recipes
- **reviews**: Recipe ratings and reviews
- **contact_messages**: Contact form submissions
- **recipe_views**: Recipe view analytics

## Security Features

### Input Validation
- Server-side validation for all forms
- Sanitization of user inputs
- File upload restrictions

### Authentication
- Password hashing with PHP's `password_hash()`
- Session security with proper configuration
- CSRF token protection

### Database Security
- Prepared statements prevent SQL injection
- Input sanitization prevents XSS
- File upload validation

## Customization

### Adding New Categories
Edit the categories in `db/schema.sql` and run the setup script again.

### Styling
Modify CSS files in the `css/` directory to match your brand.

### Features
Add new functionality by creating PHP files and updating the navigation in `includes/header.php`.

## Troubleshooting

### Common Issues

1. **Database Connection Error**
   - Check MySQL is running
   - Verify credentials in `db/config.php`
   - Ensure database exists

2. **File Upload Issues**
   - Check `uploads/` directory permissions
   - Verify PHP upload settings
   - Check file size limits

3. **Session Issues**
   - Ensure sessions are enabled in PHP
   - Check session directory permissions

### Error Logs
Check PHP error logs for detailed error information:
- XAMPP: `xampp/apache/logs/error.log`
- Linux: `/var/log/apache2/error.log`

## Contributing

1. Fork the repository
2. Create a feature branch
3. Make your changes
4. Test thoroughly
5. Submit a pull request

## License

This project is open source and available under the MIT License.

## Support

For support or questions:
- Email: aryanparvani12@gmail.com
- Phone: +91 6353953587

## Changelog

### Version 1.0.0
- Initial release
- User authentication system
- Recipe CRUD operations
- Search and filtering
- User favorites
- Rating and review system
- Responsive design
- Security features

---

**TastyBook** - Your ultimate destination for discovering and sharing delicious recipes from around the world!
