# Database Setup Instructions

This document provides instructions for setting up the database for the University Thesis Submission System.

## Files Included

### 1. `setup_database.php`
A complete PHP script that creates the entire database schema with all necessary tables, columns, indexes, and constraints.

**Features:**
- Creates the database if it doesn't exist
- Creates all required tables (admins, submissions, submission_files, users, user_references)
- Adds all necessary columns and indexes for optimal performance
- Sets up foreign key constraints for data integrity
- Inserts default admin account
- Handles existing database scenarios safely

### 2. `complete_database_setup.sql`
A complete SQL script that contains all database schema definitions in a single file.

**Features:**
- Complete database schema in standard SQL format
- Includes all tables, columns, indexes, and constraints
- Creates the same structure as the PHP setup script
- Can be imported directly using MySQL command line or GUI tools

## Database Configuration

The setup script uses these default settings:
- Host: localhost
- Database: skripsi_db
- Username: root
- Password: (empty)

These can be easily modified in the setup script if needed.

## Default Admin Account

After setup, the system includes:
- Username: admin
- Password: admin123

## Usage Options

### Option 1: Using PHP Script (Recommended)

Run the PHP setup script from the command line:
```bash
php setup_database.php
```

Or access it through a web browser if PHP is configured in your web server:
```
http://localhost/sitasi/setup_database.php
```

### Option 2: Using SQL Script

Import the SQL file directly using MySQL command line:
```bash
mysql -u root -p < complete_database_setup.sql
```

Or import it using a GUI tool like phpMyAdmin, MySQL Workbench, etc.

## Database Schema Includes

- **admins table**: Stores administrator login credentials
- **submissions table**: Core table for thesis/skripsi/journal submissions with support for different submission types
- **submission_files table**: Stores file paths and metadata for submitted documents
- **users table**: Stores user account information with library card numbers
- **user_references table**: Creates many-to-many relationship between users and submissions for reference purposes

## Important Notes

- The script is designed to be safe and will not overwrite existing data if tables already exist
- The submissions table includes support for different submission types (bachelor, master, journal)
- The user_id column in submissions allows NULL values to maintain backward compatibility
- Foreign key constraints ensure referential integrity with ON DELETE SET NULL and ON UPDATE CASCADE
- Library card numbers serve as the unique login identifier instead of usernames
- NIM column is now nullable to support journal submissions that don't have student numbers
- Serial number column has been added for unique identification of submissions
- Abstract column has been added to support journal submissions

## Sharing with Friends

To share the database setup with a friend:
1. Copy the entire project folder
2. Include the `setup_database.php` file and/or `complete_database_setup.sql`
3. Share this `DATABASE_SETUP_INSTRUCTIONS.md` file for setup guidance
4. Friend runs the setup script to create the same database structure