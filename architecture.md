# Skripsi Submission System - Architecture Documentation

## Overview
The Skripsi Submission System is a web-based application built with PHP that allows students to submit their thesis proposals and enables administrators to manage these submissions. The application follows an MVC (Model-View-Controller) architectural pattern with a simple routing mechanism.

## Technology Stack
- **Backend**: PHP 8.2+
- **Database**: MySQL/MariaDB
- **Frontend**: HTML, CSS (Tailwind CSS utility classes)
- **Dependency Management**: 
  - Composer for PHP dependencies
  - npm for JavaScript dependencies
- **Key Libraries**:
  - PHPMailer for email functionality
  - FPDF for PDF generation

## Project Structure
```
formskripsi/
├── app/
│   ├── Controllers/
│   ├── Models/
│   ├── views/
│   └── helpers/
├── public/
│   ├── css/
│   └── uploads/
├── src/
├── vendor/
├── .htaccess
├── config.php
├── database.sql
├── index.php
├── composer.json
└── package.json
```

## Architecture Components

### 1. Entry Point and Routing
The application uses a single entry point (`index.php`) that handles all incoming requests. The router parses the URL to determine which controller and method to invoke:

- Pattern: `/controller/method/param1/param2/...`
- Default controller: `submission`
- Default method: `index`

### 2. Controllers
Controllers handle user requests and coordinate between models and views:

- **Controller.php**: Abstract base controller with a `render()` method
- **AdminController.php**: Handles admin authentication and dashboard
- **SubmissionController.php**: Manages student submission forms and processing

### 3. Models
Models handle data logic and database interactions:

- **Database.php**: Singleton pattern for database connections
- **Admin.php**: Admin authentication and retrieval
- **Submission.php**: Submission creation, retrieval, and management
- **EmailService.php**: Email notification functionality using PHPMailer
- **PdfService.php**: PDF receipt generation using FPDF
- **ValidationService.php**: Form and file validation

### 4. Views
Views are PHP files that generate the HTML interface:

- **Main layout**: `main.php` provides the overall page structure
- **Home page**: `home.php` is the main landing page
- **Forms**: `new.php` for student submissions, `login.php` for admin authentication
- **Dashboards**: `dashboard.php` for admin submission management
- **Special pages**: `success.php` for submission confirmation, `404.php` for errors

### 5. Database Schema
The application uses three main tables:

1. **admins**: Stores administrator credentials
2. **submissions**: Contains thesis submission details
3. **submission_files**: Tracks uploaded files for each submission

## Data Flow

### Student Submission Process
1. Student accesses the submission form (`/submission/new`)
2. Student fills out the form and uploads required files
3. Form is submitted to `/submission/create`
4. SubmissionController validates data and files
5. SubmissionModel creates database entries and handles file uploads
6. PDFService generates a receipt
7. EmailService sends notifications to student and admin
8. Student is redirected to a success page

### Admin Management Process
1. Admin accesses login page (`/admin/login`)
2. Admin authenticates with credentials
3. Upon successful login, admin is redirected to dashboard (`/admin/dashboard`)
4. Dashboard displays all submissions with status information
5. Admin can view detailed submission information

## Security Considerations
- Password hashing using PHP's `password_hash()` function
- Prepared statements for all database queries to prevent SQL injection
- File upload validation and sanitization
- Session management for admin authentication
- XSS prevention through output escaping

## Configuration
The application is configured through `config.php`, which contains:
- Email (SMTP) settings
- Base path configuration

## File Storage
Uploaded files are stored in `public/uploads/` with unique filenames to prevent conflicts.

## Error Handling
The application includes basic error handling:
- 404 page for unmatched routes
- Exception handling in controllers
- Error logging for email failures