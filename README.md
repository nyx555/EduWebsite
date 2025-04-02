# CodeMaster Academy Website

A modern educational website with a lead capture form and admin dashboard for managing student applications.

## Features

### For Students
- Modern, responsive landing page
- Lead capture form for course inquiries
- Course information display
- Contact information

### For Administrators
- Secure admin login
- Dashboard to manage student applications
- Action system for handling leads:
  - Contact: Mark leads as contacted
  - Enroll: Approve and send welcome email
  - Reject: Mark as not interested
- Status tracking (New, Contacted, Enrolled, Not Interested)
- Notes system for each action
- Pagination for large lists
- Status filtering

## Setup Instructions

1. **Database Setup**
   ```sql
   # Import the database structure
   mysql -u root < database/setup.sql
   ```

2. **Configuration**
   - Copy `config.example.php` to `config.php`
   - Update database credentials
   - Configure SMTP settings for email notifications

3. **Composer Dependencies**
   ```bash
   composer install
   ```

4. **SMTP Configuration**
   - Use Gmail SMTP
   - Enable 2-Step Verification in Gmail
   - Generate App Password
   - Update SMTP settings in `config.php`

5. **Default Admin Login**
   - Username: admin
   - Password: admin123

## Directory Structure

```
EduWebsite/
├── admin/
│   ├── dashboard.php    # Admin dashboard
│   └── login.php        # Admin login page
├── api/
│   └── submit.php       # Form submission handler
├── includes/
│   ├── db.php          # Database connection
│   └── mailer.php      # Email functionality
├── database/
│   └── setup.sql       # Database structure
├── config.php          # Configuration settings
└── index.php          # Main landing page
```

## Requirements

- PHP 7.4 or higher
- MySQL 5.7 or higher
- Composer
- Gmail account for SMTP

## Security Features

- SQL injection prevention
- XSS protection
- CSRF protection
- Secure password hashing
- Input validation
- CORS configuration

## Email Notifications

When a lead is enrolled:
1. Status is updated to "enrolled"
2. Welcome email is sent automatically
3. Email includes:
   - Personalized greeting
   - Course confirmation
   - Next steps
   - Support contact information

## Contributing

1. Fork the repository
2. Create your feature branch
3. Commit your changes
4. Push to the branch
5. Create a new Pull Request

## License

This project is licensed under the MIT License. 
