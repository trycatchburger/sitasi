# Database Update Instructions

This guide explains how to update an existing database to match the latest schema for the Thesis Submission System.

## Purpose

Use this update script when you already have the basic database structure but need to apply recent updates like:
- New columns (submission_type, abstract, serial_number, user_id)
- Journal submission support
- User account management tables
- Performance indexes
- Foreign key constraints

## Prerequisites

- XAMPP (or similar local server environment with PHP and MySQL)
- PHP 7.4 or higher
- MySQL 5.7 or higher
- An existing database with basic tables (admins, submissions, submission_files)

## Update Instructions

### Method 1: Using Command Line (Recommended)

1. Make sure your XAMPP Apache and MySQL services are running
2. Open Command Prompt or Terminal
3. Navigate to the project directory:
   ```cmd
   cd C:\xampp\htdocs\sitasi
   ```
4. Run the update script:
   ```cmd
   php update_database_to_latest.php
   ```

### Method 2: Using Web Browser

1. Make sure your XAMPP Apache and MySQL services are running
2. Place the project folder in your `C:\xampp\htdocs\` directory
3. Open your web browser
4. Navigate to: `http://localhost/sitasi/update_database_to_latest.php`

## Configuration

The update script uses the following default configuration:

- **Database Host**: localhost
- **Database Name**: skripsi_db
- **Database Username**: root
- **Database Password**: (empty)
- **Database Charset**: utf8mb4

If you need to change these settings, edit the `$config` array in `update_database_to_latest.php`.

## What This Script Updates

The script will:

1. **Create users table** if it doesn't exist (for user account management)
2. **Add submission_type column** to support journal submissions
3. **Add abstract column** for journal abstracts
4. **Add serial_number column** for document tracking
5. **Add user_id column** to submissions table for user linking
6. **Make NIM nullable** to support journal submissions without student IDs
7. **Add performance indexes** for faster queries
8. **Add foreign key constraints** for data integrity

## Safe Operation

- The script checks if each table/column/index exists before creating it
- It will not overwrite existing data
- It only adds missing elements to bring your database up to date
- If your database is already up to date, the script will confirm this

## Troubleshooting

### Common Issues:

1. **Connection Error**: Make sure XAMPP MySQL service is running
2. **Access Denied**: Verify your MySQL username and password (default is root with no password)
3. **Database Not Found**: Ensure the database name matches your existing database

### If you get "Access denied" errors:

- Check if MySQL is running in XAMPP Control Panel
- Verify your MySQL username and password (default is root with no password)
- Make sure no other application is using the same port

## After Update

Once the database is updated successfully, you can:

1. Access the application at `http://localhost/sitasi/`
2. Use all the latest features including journal submissions and user accounts
3. The application will now have all the functionality of the latest version