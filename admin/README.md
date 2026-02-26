# With Love For You - Admin Dashboard

A comprehensive admin dashboard for managing projects, content, and users for the With Love For You website.

## Features

### Authentication System
- **Secure Login**: Email/password authentication with session management
- **Forgot Password**: Email-based password reset functionality
- **Password Strength Indicator**: Real-time password strength validation
- **Remember Me**: Optional persistent login functionality

### Project Management
- **Multiple Image Upload**: Upload multiple images for each project
- **Project Categories**: Organize projects by categories (Education, Healthcare, etc.)
- **Status Management**: Draft, Published, and Archived status options
- **Rich Text Editing**: Full description support with HTML formatting
- **Image Gallery**: Organized image display with sorting capabilities

### Dashboard Features
- **Statistics Overview**: Real-time stats for projects, images, and content
- **Recent Projects**: Quick view of recently added/modified projects
- **Search & Filter**: Advanced search and filtering capabilities
- **Responsive Design**: Mobile-friendly interface
- **Modern UI**: Beautiful gradient-based design with smooth animations

## Installation

### 1. Database Setup
Import the database schema from `database_schema.sql`:
```sql
mysql -u root -p prayas_admin < database_schema.sql
```

### 2. Configure Database
Update database credentials in:
- `admin/api/auth.php`
- `admin/api/projects.php`

### 3. Set Permissions
Ensure the following directories are writable:
```
admin/uploads/projects/
```

### 4. Default Login
- **Email**: admin@wl.org
- **Password**: admin123

## File Structure

```
admin/
├── login.html                 # Login page
├── dashboard.html             # Main dashboard
├── reset-password.php         # Password reset page
├── database_schema.sql        # Database structure
├── api/
│   ├── auth.php              # Authentication endpoints
│   └── projects.php          # Project management endpoints
├── js/
│   └── dashboard.js          # Frontend JavaScript
└── uploads/
    └── projects/             # Project images storage
```

## API Endpoints

### Authentication (`api/auth.php`)
- `POST /api/auth.php`
  - `action=login` - User login
  - `action=forgot_password` - Request password reset
  - `action=reset_password` - Reset password with token
  - `action=logout` - User logout
  - `action=check_auth` - Check authentication status

### Projects (`api/projects.php`)
- `GET /api/projects.php`
  - `action=get_stats` - Get dashboard statistics
  - `action=get_recent_projects` - Get recent projects
  - `action=get_all_projects` - Get all projects with pagination
  - `action=get_project&id={id}` - Get specific project details

- `POST /api/projects.php`
  - `action=add_project` - Create new project
  - `action=update_project` - Update existing project
  - `action=delete_project` - Delete project

## Security Features

- **Password Hashing**: Uses PHP's `password_hash()` with bcrypt
- **Session Management**: Secure session handling with timeout
- **SQL Injection Prevention**: Prepared statements for all database queries
- **File Upload Security**: File type validation and secure storage
- **CSRF Protection**: Token-based request validation (can be enhanced)
- **Input Validation**: Server-side validation for all inputs

## Image Upload Features

- **Multiple File Support**: Upload multiple images simultaneously
- **File Type Validation**: Only allows image files (jpg, png, gif, webp)
- **Unique Filenames**: Prevents filename conflicts with UUID generation
- **Organized Storage**: Files stored in organized directory structure
- **Database Integration**: Image metadata stored in database with sorting

## Customization

### Styling
The dashboard uses modern CSS with:
- CSS custom properties for easy theming
- Gradient backgrounds and smooth animations
- Responsive grid layouts
- Bootstrap 5 components

### JavaScript Features
- jQuery for DOM manipulation
- AJAX for asynchronous operations
- Real-time form validation
- Dynamic content loading
- Interactive UI elements

## Browser Support

- Chrome 60+
- Firefox 55+
- Safari 12+
- Edge 79+

## Future Enhancements

- User role management
- Media library with drag-and-drop
- Advanced analytics dashboard
- Email template management
- SEO optimization tools
- Bulk operations
- Import/Export functionality
- API rate limiting
- Two-factor authentication

## Support

For issues or questions:
- **Email**: admin@wl.org
- Check the error logs in the browser console
- Verify database connections and permissions

---

**Note**: This admin dashboard is designed specifically for the With Love For You website and integrates seamlessly with the existing frontend structure.
