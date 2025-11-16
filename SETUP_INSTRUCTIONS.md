# Database Setup Instructions

This guide explains how to set up the database for the Thesis Submission System.

## Prerequisites

- XAMPP (or similar local server environment with PHP and MySQL)
- PHP 7.4 or higher
- MySQL 5.7 or higher

## Setup Instructions

### Method 1: Using Command Line (Recommended)

1. Make sure your XAMPP Apache and MySQL services are running
2. Open Command Prompt or Terminal
3. Navigate to the project directory:
   ```cmd
   cd C:\xampp\htdocs\sitasi
   ```
4. Run the setup script:
   ```cmd
   php setup_database.php
   ```

### Method 2: Using Web Browser

1. Make sure your XAMPP Apache and MySQL services are running
2. Place the project folder in your `C:\xampp\htdocs\` directory
3. Open your web browser
4. Navigate to: `http://localhost/sitasi/setup_database.php`

## Configuration

The setup script uses the following default configuration:

- **Database Host**: localhost
- **Database Name**: skripsi_db
- **Database Username**: root
- **Database Password**: (empty)
- **Database Charset**: utf8mb4

If you need to change these settings, edit the `$config` array in `setup_database.php`.

## Default Login Credentials

After successful setup, you'll have the following default admin account:

- **Username**: admin
- **Password**: admin123

## Troubleshooting

### Common Issues:

1. **Connection Error**: Make sure XAMPP MySQL service is running
2. **Permission Error**: Ensure PHP has write permissions to the directory
3. **Database Already Exists**: The script will safely update existing tables without data loss

### If you get "Access denied" errors:

- Check if MySQL is running in XAMPP Control Panel
- Verify your MySQL username and password (default is root with no password)
- Make sure no other application is using the same port

## After Setup

Once the database is set up successfully, you can:

1. Access the application at `http://localhost/sitasi/`
2. Log in with the default admin credentials
3. Change the default admin password for security

## Security Note

For production use, make sure to:
- Change the default admin password immediately
- Use a strong database password
- Configure proper database user permissions
- Update the database credentials in your application configuration