# Database Credentials Configuration Guide

## Where to Place Database Credentials

Database credentials should be placed in configuration files, **NOT** in the `app/Models/Database.php` file. The Database class is designed to read credentials from configuration files only.

### Configuration File Options

You can place your database credentials in one of these configuration files:

#### 1. config_cpanel.php (Recommended for cPanel deployments)
Create this file in the root directory:

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
    'base_path' => '/sitasi', // Change to your subdirectory name if different
    'maintenance' => [
        'enabled' => false,
        'message' => 'System is under maintenance. Please come back later.',
        'allowed_ips' => []
    ]
];
```

#### 2. config.php (Main configuration file)
Update the existing config.php file to include database configuration:

```php
<?php

// Load maintenance configuration
require_once __DIR__ . '/app/Utils/MaintenanceHelper.php';

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
    'base_path' => '/sitasi',
    'maintenance' => \App\Utils\MaintenanceHelper::getConfig()
];
```

#### 3. Environment Variables (Alternative)
You can also use environment variables:

```bash
export DB_HOST=localhost
export DB_NAME=your_database_name
export DB_USERNAME=your_username
export DB_PASSWORD=your_password
```

### How the Database Class Works

The `app/Models/Database.php` file contains logic to load credentials from configuration files in this order:

1. `config_cpanel.php`
2. `config.production.php`
3. `config.php`
4. Environment variables
5. Default fallback values

The Database class will automatically use the first configuration file it finds with valid database settings.

### Important Notes

- Never put credentials directly in `app/Models/Database.php`
- The Database class only reads from configuration files
- Make sure your database name in the config matches where your existing data is stored
- After deployment, create the appropriate config file with your actual database credentials