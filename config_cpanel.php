<?php
// Configuration file for cPanel deployment
// Copy this file to config.php or config_cpanel.php to override default settings

return [
    'db' => [
        'host' => 'localhost',                  // Usually localhost in cPanel
        'dbname' => 'lib_skripsi_db',               // Database name for thesis submission system
        'username' => 'root',                   // Database username (adjust as needed)
        'password' => '',                       // Database password (adjust as needed)
        'charset' => 'utf8mb4'
    ],
    'mail' => [
        'host' => 'mail.yourdomain.com',        // Replace with SMTP server domain
        'port' => 587,                          // Port SMTP (587 for TLS)
        'username' => 'noreply@yourdomain.com', // Email from domain
        'password' => 'your-email-password',    // Password email or app password
        'from_address' => 'noreply@yourdomain.com',
        'from_name' => 'Sistem Pengumpulan Skripsi', // Name of your application
        'admin_email' => 'admin@yourdomain.com'       // Admin email
    ],
    'base_path' => '/sitasi', // Change to your subdirectory name if different
    'maintenance' => [
        'enabled' => false, // Set to true if you want maintenance mode
        'message' => 'System is under maintenance. Please come back later.',
        'allowed_ips' => [] // IPs allowed to access during maintenance
    ]
];