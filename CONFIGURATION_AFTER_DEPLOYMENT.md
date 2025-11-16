# Configuration After Deployment

This document explains how to properly configure the application after deployment to ensure that existing data appears in the dashboard and management file views.

## Issue Description

After deployment, existing data from the old database was not appearing in:
- `app/views/dashboard.php` (Admin Dashboard)
- `app/views/management_file.php` (Management File)

## Root Cause

The issue was caused by hardcoded database configuration in the `Database.php` class that was not matching the actual database where the existing data was stored.

## Solution

The application has been updated to support configuration-based database connections. Follow the steps below to configure the application after deployment.

## Configuration Steps

### Step 1: Create Configuration File

After deploying the application, you need to create a configuration file with your database credentials. You have two options:

#### Option A: Using config_cpanel.php (Recommended for cPanel deployments)

Create a file named `config_cpanel.php` in the root directory with your database credentials:

```php
<?php
return [
    'db' => [
        'host' => 'localhost',                    // Usually localhost in cPanel
        'dbname' => 'your_actual_database_name',  // Replace with your database name
        'username' => 'your_db_username',         // Replace with your database username
        'password' => 'your_db_password',         // Replace with your database password
        'charset' => 'utf8mb4'
    ],
    'mail' => [
        'host' => 'mail.yourdomain.com',
        'port' => 587,
        'username' => 'noreply@yourdomain.com',
        'password' => 'your-email-password',
        'from_address' => 'noreply@yourdomain.com',
        'from_name' => 'Sistem Pengumpulan Skripsi',
        'admin_email' => 'admin@yourdomain.com'
    ],
    'base_path' => '/sitasi' // Change to your subdirectory name if different
];
```

#### Option B: Update config.php

Alternatively, you can update the existing `config.php` file to include database configuration:

```php
<?php

return [
    'db' => [
        'host' => 'localhost',                    // Your database host
        'dbname' => 'your_actual_database_name',  // Your actual database name
        'username' => 'your_db_username',         // Your database username
        'password' => 'your_db_password',         // Your database password
        'charset' => 'utf8mb4'
    ],
    'mail' => [
        'host' => 'smtp.gmail.com',
        'port' => 587,
        'username' => 'repository@stainkepri.ac.id',
        'password' => 'uwsu tmvm iojh nyie',
        'from_address' => 'repository@stainkepri.ac.id',
        'from_name' => 'Pustaka STAIN SAR',
        'admin_email' => 'repository@stainkepri.ac.id'
    ],
    'base_path' => '/sitasi'
];
```

### Step 2: Verify Database Connection

After creating your configuration file, you can test the database connection using the provided test script:

```bash
php test_db_connection.php
```

This script will:
- Test the database connection
- Verify that the submissions table exists
- Count the number of records in each table

### Step 3: Verify Data Display

You can also verify that data is properly retrieved and displayed:

```bash
php verify_data_display.php
```

This script will:
- Test retrieval of all types of submissions
- Show counts of different submission types
- Display sample data to confirm proper retrieval

### Step 4: Update Database Name

Make sure the database name in your configuration file matches the name of the database where your existing data is stored. Common database names in this application include:
- `skripsi_db`
- `lib_skripsi_db`
- Your cPanel database name (usually in the format `username_database_name`)

## Troubleshooting

If data still doesn't appear after configuration:

1. **Check Database Name**: Ensure the database name in your config file matches where your data is stored
2. **Verify Credentials**: Make sure your database username and password are correct
3. **Test Connection**: Run `php test_db_connection.php` to verify the connection
4. **Check Table Names**: Ensure the database contains the required tables (`submissions`, `submission_files`, `admins`)
5. **Clear Cache**: If you're still having issues, try clearing any application cache

## Environment Variables (Alternative)

You can also use environment variables for configuration:

```bash
export DB_HOST=localhost
export DB_NAME=your_database_name
export DB_USERNAME=your_username
export DB_PASSWORD=your_password
```

The application will automatically use these environment variables if configuration files are not found.

## Verification

After proper configuration, you should see:
- Existing submissions in the dashboard view
- All files associated with submissions
- Proper counts of pending, approved, and journal submissions
- Correct data in the management file view