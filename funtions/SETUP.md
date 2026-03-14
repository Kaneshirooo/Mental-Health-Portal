# Mental Health Pre-Assessment System - Setup Guide

## Requirements
- PHP 7.4 or higher
- MySQL 5.7 or higher
- Web server (Apache, Nginx, etc.)
- Composer (optional, for autoloading)

## Installation Steps

### 1. Database Setup

1. Create a new MySQL database:
   ```sql
   CREATE DATABASE mental_health_db;
   ```

2. Import the database schema:
   - Open phpMyAdmin or your MySQL client
   - Select the `mental_health_db` database
   - Import the `database.sql` file
   - This will create all tables and insert sample data

### 2. Configuration

1. Open `config.php` and update the database credentials if needed:
   ```php
   define('DB_HOST', 'localhost');      // Your database host
   define('DB_USER', 'root');           // Your MySQL username
   define('DB_PASS', '');               // Your MySQL password
   define('DB_NAME', 'mental_health_db'); // Database name
   ```

2. Update the site URL if deploying on a server:
   ```php
   define('SITE_URL', 'http://yourdomain.com');
   ```

### 3. File Placement

1. Place all PHP files in your web server's document root:
   - Apache: `/var/www/html/` or your configured document root
   - Nginx: Configure your server block to point to the project directory
   - Local (XAMPP): `C:/xampp/htdocs/`

2. Ensure the `styles.css` file is in the same directory as the PHP files

### 4. File Permissions

Set proper permissions on the project directory:
```bash
chmod -R 755 /path/to/mental-health-portal
chmod -R 777 /path/to/mental-health-portal/uploads (if needed)
```

### 5. Start Using the System

#### Default Login Credentials:
- **Email:** admin@mentalhealthportal.com
- **Password:** admin123

#### Login as Different User Types:

1. **Admin** - Can manage users and view system statistics
2. **Counselor** - Can view student assessments and add notes
3. **Student** - Can take assessments and view results

### 6. Creating New Users

#### As Admin:
1. Login with admin credentials
2. Go to "Manage Users"
3. Fill in the form and add students/counselors

#### Student Self-Registration:
1. Click "Register here" on the login page
2. Fill in the registration form
3. Select "Student" as registration type
4. Login with new credentials

## File Structure

```
mental-health-portal/
├── config.php                 # Database configuration
├── database.sql              # Database schema
├── login.php                 # Login page
├── register.php              # Registration page
├── logout.php                # Logout handler
├── profile.php               # User profile management
│
├── Student Pages:
├── student_dashboard.php     # Student main dashboard
├── take_assessment.php       # Assessment questionnaire
├── assessment_complete.php   # Assessment results
├── my_reports.php            # View assessment history
├── view_report.php           # Detailed report view
│
├── Counselor Pages:
├── counselor_dashboard.php   # Counselor main dashboard
├── student_list.php          # List of all students
├── student_profile.php       # Individual student profile
│
├── Admin Pages:
├── admin_dashboard.php       # Admin main dashboard
├── manage_users.php          # User management
│
├── styles.css                # All CSS styling
├── SETUP.md                  # This file
└── README.md                 # Project documentation
```

## Features

### Student Features
- Register and login
- Take mental health assessment (15 questions)
- View assessment results with risk levels
- View assessment history
- Print assessment reports
- Manage profile

### Counselor Features
- View all students
- Filter students by risk level
- View student assessment history
- Add clinical notes for students
- Set follow-up dates
- View system statistics

### Admin Features
- Manage all users (create, view, delete)
- View system-wide statistics
- Monitor assessment distribution
- View risk level statistics
- View recent user registrations

## Assessment Categories

The system assesses three mental health areas:

1. **Depression** (5 questions)
   - Questions 1-5
   - Score: 0-20

2. **Anxiety** (5 questions)
   - Questions 6-10
   - Score: 0-20

3. **Stress** (5 questions)
   - Questions 11-15
   - Score: 0-20

### Risk Levels
- **Low:** 0-39 points
- **Moderate:** 40-59 points
- **High:** 60-79 points
- **Critical:** 80+ points

## Database Tables

### Users
- user_id, email, password, full_name, roll_number, user_type
- date_of_birth, gender, contact_number, department, semester

### Assessment Questions
- question_id, category, question_text, question_number

### Student Responses
- response_id, user_id, question_id, response_value, assessment_date

### Assessment Scores
- score_id, user_id, depression_score, anxiety_score, stress_score
- overall_score, risk_level, assessment_date

### Counselor Notes
- note_id, counselor_id, student_id, note_text, recommendation, follow_up_date

### Session Logs
- log_id, user_id, login_time, logout_time, activity

## Security Features

1. **Password Hashing:** Uses bcrypt for secure password storage
2. **SQL Injection Prevention:** Prepared statements with parameterized queries
3. **Session Management:** Secure PHP session handling
4. **Input Sanitization:** HTML escaping and database escaping
5. **Access Control:** Role-based access control (Student, Counselor, Admin)
6. **CSRF Prevention:** Session-based request validation

## Troubleshooting

### Database Connection Error
- Check database credentials in config.php
- Verify MySQL service is running
- Ensure database name is correct

### Session Not Working
- Check PHP session.save_path is writable
- Verify cookie settings in php.ini
- Clear browser cookies if needed

### Blank Pages
- Check PHP error logs
- Enable error reporting in config.php
- Verify all required files are present

### Password Issues
- Ensure password is at least 6 characters
- Try resetting password via database admin

## Support

For issues or questions:
1. Check the error messages carefully
2. Review the SETUP.md file
3. Check database logs
4. Verify file permissions

## License

This Mental Health Pre-Assessment System is provided as-is for educational purposes.

## Changes Made to Original Specification

This PHP implementation includes:
- Traditional LAMP stack (Linux, Apache/Nginx, MySQL, PHP)
- No external dependencies required
- Secure password hashing with bcrypt
- Session-based authentication
- Responsive CSS styling
- Mobile-friendly interface
- Role-based access control
- Comprehensive database schema
- Form validation and error handling

All core features from the specification have been implemented including:
✓ Student assessment and reporting
✓ Counselor monitoring dashboard
✓ Admin user management
✓ Risk level calculation
✓ Assessment history tracking
✓ Counselor notes system
✓ User authentication
✓ Data persistence in MySQL

## Next Steps

1. Customize the system branding (site title, colors)
2. Add your institution's logo
3. Modify assessment questions if needed
4. Set up regular database backups
5. Configure SSL/HTTPS for production
6. Implement email notifications
7. Add more detailed reporting features

---

**Installation Date:** [Your Date]
**Version:** 1.0
**Last Updated:** February 27, 2024
